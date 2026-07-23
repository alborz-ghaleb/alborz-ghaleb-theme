<?php
/**
 * Uninstall script — اجرا می‌شود وقتی قالب از داشبورد حذف می‌شود.
 *
 * این فایل تمام داده‌های persistent که قالب در دیتابیس ایجاد کرده
 * را پاکسازی می‌کند: cron events، transients، theme mods، و options.
 *
 * نکته: WordPress این فایل را وقتی فعال است که از طریق
 * "Delete Theme" در پنل مدیریت اجرا شود.
 * Stage 33: لیست options تکمیل شد — 12+ گزینه
 *
 * @package Alborz_Ghaleb
 * @since   5.0.4
 * @version 8.5.3
 */

// محافظت در برابر دسترسی مستقیم.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) && ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ────────────────────────────────────────
   ۱) Cron Events
   ──────────────────────────────────────── */
$glass_pro_cron_hooks = [
	'glass_check_expired_ads',
	'glass_pro_gc_uploads', // garbage collector uploads (portfolio-upload-api)
];
foreach ( $glass_pro_cron_hooks as $hook ) {
	$timestamp = wp_next_scheduled( $hook );
	while ( false !== $timestamp ) {
		wp_unschedule_event( $timestamp, $hook );
		$timestamp = wp_next_scheduled( $hook );
	}
	wp_clear_scheduled_hook( $hook );
}

/* ────────────────────────────────────────
   ۲) Transients (پاکسازی الگوهای rate-limit و cache قالب)
   ──────────────────────────────────────── */
global $wpdb;

$transient_patterns = [
	'_transient_glass_pro_%',
	'_transient_timeout_glass_pro_%',
	'_transient_glass_%',
	'_transient_timeout_glass_%',
	'_transient_gpcq_%',
	'_transient_timeout_gpcq_%',
];
foreach ( $transient_patterns as $pattern ) {
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
			$pattern
		)
	);
}

/* ────────────────────────────────────────
   ۳) Options ایجاد شده توسط قالب — Stage 33 تکمیل
   ──────────────────────────────────────── */
$glass_pro_options = [
	// Core
	'glass_pro_pll_cpt_fixed_v2',
	'glass_pro_transactions_installed',
	// Admin Settings (admin-settings.php)
	'glass_pro_default_theme_mode',
	'glass_pro_enable_csp',
	'glass_pro_require_email_verification',
	'glass_pro_notify_admin_ads',
	'glass_pro_notify_admin_payments',
	'glass_pro_show_header_dark_toggle',
	'glass_pro_toc_enabled',
	'glass_pro_toc_on_pages',
	'glass_pro_toc_on_posts',
	// Welcome wizard, health-check, settings-export
	'glass_pro_wizard_completed',
	'glass_pro_health_check_dismissed',
];
foreach ( $glass_pro_options as $opt ) {
	delete_option( $opt );
}

// Delete pattern options: glass_pro_pll_imported_*
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
		'glass_pro_pll_imported_%'
	)
);
// Incrementor options for cache groups if exist (Stage 34 prep)
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
		'gpcq_inc_%'
	)
);

/* ────────────────────────────────────────
   ۴) Theme Mods (همه‌ی تنظیمات Customizer قالب)
   ──────────────────────────────────────── */
// remove_theme_mods همه‌ی theme_mods_{slug} را پاک می‌کند.
remove_theme_mods();

/* ────────────────────────────────────────
   ۵) User Meta: dismiss نوتیس‌ها
   ──────────────────────────────────────── */
$user_meta_keys = [
	'glass_pro_elementor_notice_dismissed',
	'glass_pro_wizard_dismissed',
];
foreach ( $user_meta_keys as $meta_key ) {
	delete_metadata( 'user', 0, $meta_key, '', true );
}

/* ────────────────────────────────────────
   نکته‌ی مهم: داده‌های محتوایی (CPT portfolio, glass_ad,
   taxonomy ها) عمداً حذف نمی‌شوند تا کاربر اطلاعات کسب‌وکار
   خود را از دست ندهد. برای حذف کامل با ابزار مدیریت دیتابیس اقدام کنید.
   ──────────────────────────────────────── */
