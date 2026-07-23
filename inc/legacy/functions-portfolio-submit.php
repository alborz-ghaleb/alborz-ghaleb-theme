<?php
/**
 * Glass Portfolio Frontend Submission & Dual Gateway — Bootstrapper
 *
 * این فایل از نسخه 5.0.3 صرفاً نقش راه‌انداز را دارد. منطق به فایل‌های
 * تفکیک‌شده در پوشه `portfolio-submit/` منتقل شده است (split & include refactor)
 * بدون هیچ تغییری در نام یا سیگنیچر توابع:
 *
 *   portfolio-submit/customizer.php           → تنظیمات Customizer (zarinpal + crypto)
 *   portfolio-submit/helpers.php              → link/HTML detection + image compress/watermark
 *   portfolio-submit/submission-handler.php   → پردازش POST فرم (template_redirect)
 *   portfolio-submit/form-renderer.php        → رندر فرم frontend (shortcode)
 *   portfolio-submit/payment-callback.php     → callback زرین‌پال
 *
 * @package Alborz_Ghaleb
 * @since   1.0.0
 * @version 5.0.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$glass_pro_submit_modules = [
	'/portfolio-submit/customizer.php',
	'/portfolio-submit/helpers.php',
	'/portfolio-submit/submission-handler.php',
	'/portfolio-submit/form-renderer.php',
	'/portfolio-submit/payment-callback.php',
];

foreach ( $glass_pro_submit_modules as $glass_pro_submit_file ) {
	$glass_pro_submit_path = __DIR__ . $glass_pro_submit_file;
	if ( is_readable( $glass_pro_submit_path ) ) {
		require_once $glass_pro_submit_path;
	}
}

unset( $glass_pro_submit_modules, $glass_pro_submit_file, $glass_pro_submit_path );
