<?php
/**
 * PWA Manifest Generator
 *
 * تولید پویای manifest.webmanifest برای پشتیبانی PWA با مقادیر از Customizer.
 * اگر پلاگین Alborz Ghaleb Core فعال باشد، manifest از Core ارائه می‌شود.
 *
 * @package Alborz_Ghaleb
 * @since   5.0.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * افزودن endpoint پویای manifest.
 */
add_action( 'init', 'glass_pro_pwa_rewrite_rules' );
/**
 * @return void
 */
function glass_pro_pwa_rewrite_rules(): void {
	add_rewrite_rule( '^manifest\.webmanifest$', 'index.php?glass_pro_manifest=1', 'top' );
}

add_filter( 'query_vars', 'glass_pro_pwa_query_vars' );
/**
 * @param array $vars
 * @return array
 */
function glass_pro_pwa_query_vars( $vars ): array {
	$vars[] = 'glass_pro_manifest';
	return $vars;
}

add_action( 'template_redirect', 'glass_pro_pwa_serve_manifest' );
/**
 * @return void
 */
function glass_pro_pwa_serve_manifest(): void {
	if ( ! get_query_var( 'glass_pro_manifest' ) ) {
		return;
	}

	// اگر Core فعال است، از manifest خودش را ارائه می‌دهد — تداخل نداشته باشیم.
	if ( defined( 'GLASS_CORE_VERSION' ) && function_exists( 'glass_core_app_enabled' ) && glass_core_app_enabled() ) {
		return;
	}

	$site_name   = get_bloginfo( 'name' );
	$site_desc   = get_bloginfo( 'description' );
	$theme_color = (string) apply_filters( 'glass_pro/pwa/theme_color', '#2D5F93' );
	$bg_color    = (string) apply_filters( 'glass_pro/pwa/background_color', '#f8fafc' );

	// آیکون واقعی با سایزهای مجزا
	$icon_192  = '';
	$icon_512  = '';
	$icon_id   = (int) get_theme_mod( 'site_icon', 0 );
	$icon_mime = $icon_id ? (string) get_post_mime_type( $icon_id ) : 'image/png';
	$allowed_icon_mimes = [ 'image/png', 'image/jpeg', 'image/webp' ];
	if ( ! in_array( $icon_mime, $allowed_icon_mimes, true ) ) {
		$icon_mime = 'image/png';
	}
	if ( $icon_id ) {
		$icon_192 = wp_get_attachment_image_url( $icon_id, array( 192, 192 ) );
		$icon_512 = wp_get_attachment_image_url( $icon_id, array( 512, 512 ) );
	}
	// Fallback به full size اگر سایز دقیق موجود نباشد
	if ( ! $icon_192 ) {
		$icon_192 = wp_get_attachment_image_url( $icon_id, 'full' );
	}
	if ( ! $icon_512 ) {
		$icon_512 = $icon_192;
	}

	$site_name_trimmed = trim( (string) $site_name );
	$short_name = $site_name_trimmed ? mb_substr( $site_name_trimmed, 0, 12 ) : '';
	$short_name = trim( $short_name );
	if ( '' === $short_name ) {
		$short_name = 'Alborz'; // Fallback امن برای manifest نامعتبر — Stage 40
	}

	$manifest = [
		'name'             => $site_name,
		'short_name'       => $short_name,
		'description'      => $site_desc,
		'start_url'        => home_url( '/' ),
		'display'          => 'standalone',
		'theme_color'      => $theme_color,
		'background_color' => $bg_color,
		'lang'             => str_replace( '_', '-', get_locale() ),
		'dir'              => is_rtl() ? 'rtl' : 'ltr',
		'icons'            => [],
	];

	if ( $icon_192 ) {
		$manifest['icons'][] = [
			'src'   => $icon_192,
			'sizes' => '192x192',
			'type'  => $icon_mime,
		];
	}
	if ( $icon_512 && $icon_512 !== $icon_192 ) {
		$manifest['icons'][] = [
			'src'   => $icon_512,
			'sizes' => '512x512',
			'type'  => $icon_mime,
			'purpose' => 'any maskable',
		];
	} elseif ( $icon_192 ) {
		// اگر فقط یک آیکون داریم، برای 512 هم از همان استفاده می‌کنیم
		$manifest['icons'][] = [
			'src'   => $icon_192,
			'sizes' => '512x512',
			'type'  => $icon_mime,
		];
	}

	/**
	 * Filter: glass_pro/pwa/manifest — override manifest کامل
	 *
	 * @param array $manifest
	 */
	$manifest = (array) apply_filters( 'glass_pro/pwa/manifest', $manifest );

	header( 'Content-Type: application/manifest+json; charset=UTF-8' );
	header( 'Cache-Control: public, max-age=3600' );
	echo wp_json_encode( $manifest, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
	exit;
}

/**
 * افزودن <link rel="manifest"> و meta theme-color به head.
 * فقط اگر Core فعال نباشد تا تداخل ایجاد نشود.
 */
add_action( 'wp_head', 'glass_pro_pwa_head_tags', 2 );
/**
 * @return void
 */
function glass_pro_pwa_head_tags(): void {
	// اگر Core App Layer فعال است، تگ‌ها توسط Core چاپ می‌شوند.
	if ( defined( 'GLASS_CORE_VERSION' ) && function_exists( 'glass_core_app_enabled' ) && glass_core_app_enabled() ) {
		return;
	}
	$enabled = (bool) apply_filters( 'glass_pro/pwa/enabled', true );
	if ( ! $enabled ) {
		return;
	}
	$theme_color = (string) apply_filters( 'glass_pro/pwa/theme_color', '#2D5F93' );

	echo '<link rel="manifest" href="' . esc_url( home_url( '/manifest.webmanifest' ) ) . '">' . "\n";
	echo '<meta name="theme-color" content="' . esc_attr( $theme_color ) . '">' . "\n";
	echo '<meta name="apple-mobile-web-app-capable" content="yes">' . "\n";
	echo '<meta name="mobile-web-app-capable" content="yes">' . "\n";
}

/**
 * Flush rewrite rules در فعال‌سازی قالب.
 */
add_action( 'after_switch_theme', 'glass_pro_pwa_flush_rewrite' );
/**
 * @return void
 */
function glass_pro_pwa_flush_rewrite(): void {
	glass_pro_pwa_rewrite_rules();
	flush_rewrite_rules();
}
