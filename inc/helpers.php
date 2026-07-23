<?php
/**
 * Helper Functions
 *
 * توابع کمکی مشترک + مدیریت کش (Transient) برای داده‌های گران.
 *
 * @package Alborz_Ghaleb
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ────────────────────────────────────────
   تشخیص افزونه‌های فعال
   ──────────────────────────────────────── */

/**
 * آیا یک افزونه‌ی SEO که Schema/Meta تولید می‌کند فعال است؟
 * (Rank Math / Yoast / SEOPress / All in One SEO)
 *
 * @return bool
 */
function glass_pro_has_seo_plugin(): bool {
	return (
		class_exists( 'RankMath' ) ||
		defined( 'WPSEO_VERSION' ) ||
		defined( 'SEOPRESS_VERSION' ) ||
		defined( 'AIOSEO_VERSION' ) ||
		function_exists( 'aioseo' )
	);
}

/**
 * آیا Polylang فعال است؟
 *
 * @return bool
 */
function glass_pro_has_polylang(): bool {
	return function_exists( 'pll_current_language' );
}

/* ────────────────────────────────────────
   پاکسازی کش URLها (Transient)
   مرتبط با [PRO-FIX #1] در helper داشبورد.
   ──────────────────────────────────────── */

/**
 * پاکسازی کش آدرس‌های کش‌شده‌ی تم.
 *
 * @return void
 */
function glass_pro_flush_url_cache(): void {
	delete_transient( 'glass_pro_dashboard_url' );
	foreach ( [ 'fa', 'en', 'ar', 'tr', 'ru', 'hy' ] as $lang ) {
		delete_transient( 'glass_pro_submit_url_' . $lang );
	}
	delete_transient( 'glass_pro_submit_url_' . get_locale() );
}
add_action( 'switch_theme', 'glass_pro_flush_url_cache' );
add_action( 'save_post_page', 'glass_pro_flush_url_cache' );
add_action( 'customize_save_after', 'glass_pro_flush_url_cache' );

/* ────────────────────────────────────────
   پاکسازی متن (از نسخه قبل — حفظ‌شده)
   ──────────────────────────────────────── */
if ( ! function_exists( 'replywp_clean_plain_text' ) ) {
	/**
	 * تبدیل HTML/شورت‌کد به متن ساده‌ی تمیز.
	 *
	 * @param string $text متن ورودی.
	 * @return string متن پاک‌سازی‌شده.
	 */
	function replywp_clean_plain_text( $text ) {
		if ( empty( $text ) ) {
			return '';
		}
		$text = wp_specialchars_decode( $text, ENT_QUOTES );
		$text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$text = strip_shortcodes( $text );
		$text = wp_strip_all_tags( $text, true );
		$text = preg_replace( '/\s+/u', ' ', $text );
		return trim( $text );
	}
}

/* ────────────────────────────────────────
   [SEO] پر کردن alt خالی تصاویر با عنوان پست (حفظ‌شده)
   ──────────────────────────────────────── */
add_filter( 'the_content', 'glass_pro_fix_image_alt' );
/**
 * جایگزینی alt خالی تصاویر محتوا با عنوان نوشته.
 *
 * @param string $content محتوای پست.
 * @return string
 */
function glass_pro_fix_image_alt( string $content ): string {
	if ( false === strpos( $content, '<img' ) ) {
		return $content;
	}
	return preg_replace_callback(
		'/(<img[^>]+alt=([\'"]))\2/',
		static function ( $matches ) {
			return $matches[1] . esc_attr( get_the_title() ) . $matches[2];
		},
		$content
	);
}

/* ────────────────────────────────────────
   [PERF] lazy-load تصاویر ضمیمه (حفظ‌شده)
   ──────────────────────────────────────── */
add_filter( 'wp_get_attachment_image_attributes', 'glass_pro_lazy_load_images' );
/**
 * افزودن loading=lazy و decoding=async به تصاویر.
 *
 * @param array $attr ویژگی‌های تصویر.
 * @return array
 */
function glass_pro_lazy_load_images( array $attr ): array {
	// فقط اولین wp-post-image صفحه می‌تواند LCP باشد. در نسخه قبل تمام تصاویر
	// مرتبط نیز eager/high می‌شدند و هم‌زمان پهنای باند موبایل را مصرف می‌کردند.
	static $lcp_image_claimed = false;
	$is_post_image = ! empty( $attr['class'] ) && false !== strpos( (string) $attr['class'], 'wp-post-image' );
	$is_likely_lcp = is_singular() && $is_post_image && ! $lcp_image_claimed;

	if ( $is_likely_lcp ) {
		$lcp_image_claimed      = true;
		$attr['loading']        = 'eager';
		$attr['fetchpriority']  = 'high';
	} else {
		if ( ! isset( $attr['loading'] ) || 'eager' === $attr['loading'] ) {
			$attr['loading'] = 'lazy';
		}
		if ( isset( $attr['fetchpriority'] ) && 'high' === $attr['fetchpriority'] ) {
			unset( $attr['fetchpriority'] );
		}
	}
	if ( ! isset( $attr['decoding'] ) ) {
		$attr['decoding'] = 'async';
	}
	return $attr;
}

/* ────────────────────────────────────────
   [حفظ‌شده] ریدایرکت پس از لاگین به داشبورد کاربری
   ──────────────────────────────────────── */
add_filter( 'login_redirect', 'glass_login_redirect_to_dashboard', 10, 3 );
/**
 * هدایت کاربران عادی به داشبورد پس از ورود؛ مدیرها به مسیر درخواستی.
 *
 * @param string           $redirect_to مقصد فعلی.
 * @param string           $requested   مقصد درخواستی.
 * @param WP_User|WP_Error $user        کاربر.
 * @return string
 */
function glass_login_redirect_to_dashboard( string $redirect_to, string $requested, $user ): string {
	if ( is_wp_error( $user ) ) {
		return $redirect_to;
	}
	if ( user_can( $user, 'manage_options' ) ) {
		return $redirect_to;
	}
	return function_exists( 'glass_get_dashboard_url' ) ? glass_get_dashboard_url() : home_url( '/' );
}

/* ────────────────────────────────────────
   [حفظ‌شده] مخفی‌سازی نوار ادمین برای غیرمدیرها
   ──────────────────────────────────────── */
add_filter( 'show_admin_bar', 'glass_hide_admin_bar_for_non_admins' );
/**
 * نمایش نوار ادمین فقط برای مدیران سایت.
 *
 * @param bool $show وضعیت فعلی.
 * @return bool
 */
function glass_hide_admin_bar_for_non_admins( bool $show ): bool {
	return current_user_can( 'manage_options' ) ? $show : false;
}

/* ────────────────────────────────────────
   [v5.14.0] تشخیص صفحه داشبورد کاربری
   ──────────────────────────────────────── */
if ( ! function_exists( 'glass_is_dashboard_page' ) ) :
	/**
	 * آیا صفحه جاری صفحه داشبورد کاربری است؟
	 *
	 * @return bool
	 */
	function glass_is_dashboard_page(): bool {
		$dash_url = function_exists( 'glass_get_dashboard_url' ) ? glass_get_dashboard_url() : home_url( '/' );
		$dash_path = (string) wp_parse_url( $dash_url, PHP_URL_PATH );
		$current_path = (string) wp_parse_url( ( is_ssl() ? 'https://' : 'http://' ) . ( $_SERVER['HTTP_HOST'] ?? '' ) . ( $_SERVER['REQUEST_URI'] ?? '/' ), PHP_URL_PATH );
		return trailingslashit( $current_path ) === trailingslashit( $dash_path );
	}
endif;
