<?php
/**
 * Portfolio Module — Bootstrapper
 *
 * این فایل از نسخه 5.0.3 صرفاً نقش راه‌انداز را دارد و منطق به فایل‌های
 * تفکیک‌شده در پوشه `portfolio/` منتقل شده است (بدون هیچ تغییر در توابع):
 *
 *   portfolio/cities.php           → تکسونومی شهر، menu location، template، رندر منوی شهر
 *   portfolio/archive-renderer.php → رندر صفحه آرشیو پورتفولیو (replywp_render_portfolio_page)
 *   portfolio/admin-approval.php   → پنل تایید آگهی‌ها، default terms، city options
 *
 * @package Alborz_Ghaleb
 * @since   1.0.0
 * @version 5.0.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$glass_pro_portfolio_modules = [
	'/portfolio/cities.php',
	'/portfolio/archive-renderer.php',
	'/portfolio/admin-approval.php',
];

foreach ( $glass_pro_portfolio_modules as $glass_pro_portfolio_file ) {
	$glass_pro_portfolio_path = __DIR__ . $glass_pro_portfolio_file;
	if ( is_readable( $glass_pro_portfolio_path ) ) {
		require_once $glass_pro_portfolio_path;
	}
}

unset( $glass_pro_portfolio_modules, $glass_pro_portfolio_file, $glass_pro_portfolio_path );
