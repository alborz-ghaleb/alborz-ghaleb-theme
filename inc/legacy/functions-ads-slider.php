<?php
/**
 * Glass Ads Slider PRO — Bootstrapper
 *
 * این فایل از نسخه 5.0.3 صرفاً نقش راه‌انداز را دارد. منطق به ۷ فایل
 * تفکیک‌شده در پوشه `ads-slider/` منتقل شده است:
 *
 *   ads-slider/post-type.php      → CPT + Taxonomy + Media Loader
 *   ads-slider/meta-box.php       → متاباکس غنی + save handler
 *   ads-slider/admin-columns.php  → ستون پیش‌نمایش
 *   ads-slider/data.php           → جمع‌آوری اسلایدها
 *   ads-slider/renderer.php       → رندر HTML
 *   ads-slider/shortcode.php      → شورت‌کد [glass_ads_slider]
 *   ads-slider/assets.php         → enqueue شرطی assets
 *
 * @package Alborz_Ghaleb
 * @since   4.3.0
 * @version 5.0.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$glass_pro_ads_modules = [
	'/ads-slider/post-type.php',
	'/ads-slider/meta-box.php',
	'/ads-slider/admin-columns.php',
	'/ads-slider/data.php',
	'/ads-slider/renderer.php',
	'/ads-slider/shortcode.php',
	'/ads-slider/assets.php',
];

foreach ( $glass_pro_ads_modules as $glass_pro_ads_file ) {
	$glass_pro_ads_path = __DIR__ . $glass_pro_ads_file;
	if ( is_readable( $glass_pro_ads_path ) ) {
		require_once $glass_pro_ads_path;
	}
}

unset( $glass_pro_ads_modules, $glass_pro_ads_file, $glass_pro_ads_path );
