<?php
/**
 * WebP/AVIF Support
 *
 * این فایل از وردپرس می‌خواهد که در صورت موجود بودن نسخه WebP/AVIF،
 * آن را به جای JPG/PNG ارسال کند (با Accept header مرورگر).
 *
 * نکته: WordPress 6.2+ به طور بومی از WebP پشتیبانی می‌کند.
 * این فایل پشتیبانی AVIF را اضافه می‌کند + رفع چند bug.
 *
 * @package Alborz_Ghaleb
 * @since   5.0.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * افزودن AVIF به فرمت‌های مجاز آپلود.
 */
add_filter( 'upload_mimes', 'glass_pro_allow_avif_upload' );
/**
 * @param array $mimes
 * @return array
 */
function glass_pro_allow_avif_upload( $mimes ): array {
	if ( current_user_can( 'upload_files' ) ) {
		$mimes['avif'] = 'image/avif';
		$mimes['webp'] = 'image/webp';
	}
	return $mimes;
}

/**
 * تشخیص فرمت‌های image AVIF/WebP در wp_check_filetype_and_ext.
 */
add_filter( 'wp_check_filetype_and_ext', 'glass_pro_check_avif_filetype', 10, 4 );
/**
 * @param array  $data
 * @param string $file
 * @param string $filename
 * @param array  $mimes
 * @return array
 */
function glass_pro_check_avif_filetype( $data, $file, $filename, $mimes ): array {
	if ( ! empty( $data['type'] ) ) {
		return $data;
	}
	$ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
	if ( 'avif' === $ext ) {
		$data['ext']  = 'avif';
		$data['type'] = 'image/avif';
	} elseif ( 'webp' === $ext ) {
		$data['ext']  = 'webp';
		$data['type'] = 'image/webp';
	}
	return $data;
}

/**
 * Filter: جایگزینی URL تصاویر با نسخه WebP اگر در دسترس باشد.
 * این filter روی wp_get_attachment_image_src اعمال می‌شود.
 */
add_filter( 'wp_get_attachment_image_src', 'glass_pro_maybe_use_webp', 10, 4 );
/**
 * @param array|false $image
 * @param int         $attachment_id
 * @param string|array $size
 * @param bool        $icon
 * @return array|false
 */
function glass_pro_maybe_use_webp( $image, $attachment_id, $size, $icon ) {
	if ( ! $image || ! is_array( $image ) || ! isset( $image[0] ) ) {
		return $image;
	}
	if ( is_admin() ) { return $image; }
	static $accepts_webp_cached = null;
	if ( null === $accepts_webp_cached ) {
		$accept = isset( $_SERVER['HTTP_ACCEPT'] ) ? (string) wp_unslash( $_SERVER['HTTP_ACCEPT'] ) : '';
		$accepts_webp_cached = ( false !== stripos( $accept, 'image/webp' ) );
	}
	$accepts_webp = $accepts_webp_cached;

	/**
	 * Filter: glass_pro/webp/enabled — غیرفعال‌سازی این رفتار
	 */
	if ( ! $accepts_webp || ! apply_filters( 'glass_pro/webp/enabled', true ) ) {
		return $image;
	}

	$original_url = $image[0];
	// اگر URL پسوند jpg/png/jpeg دارد، نسخه webp را چک کنیم
	if ( ! preg_match( '/\.(jpe?g|png)$/i', $original_url ) ) {
		return $image;
	}

	$webp_url = preg_replace( '/\.(jpe?g|png)$/i', '.webp', $original_url );
	if ( ! $webp_url ) {
		return $image;
	}

	// [PERF v5.9.1] wp_upload_dir و is_readable را برای هر URL در همان request cache کنیم.
	static $upload_dir = null;
	static $exists_cache = [];
	if ( null === $upload_dir ) {
		$upload_dir = wp_upload_dir();
	}
	if ( ! empty( $upload_dir['error'] ) || empty( $upload_dir['baseurl'] ) || empty( $upload_dir['basedir'] ) || strpos( $webp_url, $upload_dir['baseurl'] ) !== 0 ) {
		return $image;
	}

	$webp_path = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $webp_url );
	if ( ! array_key_exists( $webp_path, $exists_cache ) ) {
		$exists_cache[ $webp_path ] = is_readable( $webp_path );
	}
	if ( $exists_cache[ $webp_path ] ) {
		$image[0] = $webp_url;
	}

	return $image;
}
