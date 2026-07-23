<?php
/**
 * User Panel Module — Bootstrapper
 *
 * این فایل از نسخه 5.0.3 صرفاً نقش راه‌انداز را دارد. منطق به ۹ فایل
 * تفکیک‌شده در پوشه `user-panel/` منتقل شده است:
 *
 *   user-panel/customizer.php     → Customizer settings (expiry, renew)
 *   user-panel/helpers.php        → URL helpers + action buttons + auth prompt
 *   user-panel/expiry.php         → Cron + توابع تشخیص انقضا/فروخته
 *   user-panel/phone-privacy.php  → فیلتر نمایش شماره تماس
 *   user-panel/captcha.php        → کپچای ساده ریاضی
 *   user-panel/auth-forms.php     → فرم‌های login/register/lostpass (shortcode)
 *   user-panel/ad-actions.php     → handler اکشن‌های آگهی + callback تمدید
 *   user-panel/dashboard.php      → داشبورد کاربر (shortcode)
 *   user-panel/assets.php         → enqueue شرطی CSS/JS
 *
 * @package Alborz_Ghaleb
 * @since   1.0.0
 * @version 5.0.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$glass_pro_panel_modules = [
	'/user-panel/customizer.php',
	'/user-panel/helpers.php',
	'/user-panel/expiry.php',
	'/user-panel/phone-privacy.php',
	'/user-panel/captcha.php',
	'/user-panel/auth-forms.php',
	'/user-panel/ad-actions.php',
	'/user-panel/dashboard.php',
	'/user-panel/assets.php',
];

foreach ( $glass_pro_panel_modules as $glass_pro_panel_file ) {
	$glass_pro_panel_path = __DIR__ . $glass_pro_panel_file;
	if ( is_readable( $glass_pro_panel_path ) ) {
		require_once $glass_pro_panel_path;
	}
}

unset( $glass_pro_panel_modules, $glass_pro_panel_file, $glass_pro_panel_path );
