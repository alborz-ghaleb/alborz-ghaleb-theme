<?php
/**
 * Content Security Policy Helper
 *
 * این فایل بستر CSP nonce-based را برای inline script ها فراهم می‌کند.
 * به‌صورت پیش‌فرض غیرفعال است (برای backward-compat).
 *
 * فعال‌سازی در wp-config.php یا کد سفارشی:
 *   add_filter( 'glass_pro/csp/enabled', '__return_true' );
 *
 * در template ها به جای <script> از <script <?php glass_pro_csp_nonce_attr(); ?>>
 * استفاده کنید.
 *
 * @package Alborz_Ghaleb
 * @since   5.0.6
 * @fix     v5.13.5 — حذف 'unsafe-inline' از script-src برای امنیت واقعی
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'glass_pro_csp_nonce' ) ) :
	/**
	 * تولید یا بازیابی nonce CSP برای request جاری (cached per-request).
	 *
	 * @return string  base64 nonce — همان مقدار برای تمام inline scripts همان صفحه
	 */
	function glass_pro_csp_nonce(): string {
		static $nonce = null;
		if ( null === $nonce ) {
			// 16 بایت random → base64 (سازگار با CSP spec)
			$nonce = rtrim( strtr( base64_encode( random_bytes( 16 ) ), '+/', '-_' ), '=' );
		}
		return $nonce;
	}
endif;

if ( ! function_exists( 'glass_pro_csp_nonce_attr' ) ) :
	/**
	 * چاپ attribute nonce برای inline <script>.
	 * فقط در صورت فعال بودن CSP خروجی دارد.
	 *
	 * Usage:
	 *   <script <?php glass_pro_csp_nonce_attr(); ?>>...</script>
	 *
	 * @return void
	 */
	function glass_pro_csp_nonce_attr(): void {
		if ( ! apply_filters( 'glass_pro/csp/enabled', false ) ) {
			return;
		}
		printf( 'nonce="%s"', esc_attr( glass_pro_csp_nonce() ) );
	}
endif;

/**
 * افزودن CSP header در صورت فعال بودن.
 */
add_action( 'send_headers', 'glass_pro_send_csp_header' );
/**
 * @return void
 */
function glass_pro_send_csp_header(): void {
	if ( ! apply_filters( 'glass_pro/csp/enabled', false ) ) {
		return;
	}
	if ( headers_sent() || is_admin() ) {
		return;
	}

	$nonce = glass_pro_csp_nonce();

	// [FIX v5.13.5] 'unsafe-inline' از script-src حذف شد.
	// nonce به تنهایی برای inline scripts کافی است.
	// script-src-attr همچنان 'unsafe-inline' دارد برای هندلرهای قدیمی (onclick, onload).
	// style-src 'unsafe-inline' باقی می‌ماند چون وردپرس و افزونه‌ها
	// به طور گسترده از inline style استفاده می‌کنند.
	$directives = [
		"default-src 'self'",
		"script-src 'self' 'nonce-{$nonce}' 'strict-dynamic'",
		"script-src-elem 'self' 'nonce-{$nonce}'",
		"script-src-attr 'unsafe-inline'",
		"style-src 'self' 'unsafe-inline'",
		"img-src 'self' data: https:",
		"font-src 'self' data:",
		"connect-src 'self' https://api.zarinpal.com",
		"frame-ancestors 'self'",
		"base-uri 'self'",
		"form-action 'self' https://www.zarinpal.com",
	];

	/**
	 * Filter: glass_pro/csp/directives — تنظیم directives سفارشی
	 *
	 * @param array $directives
	 */
	$directives = (array) apply_filters( 'glass_pro/csp/directives', $directives );

	header( 'Content-Security-Policy: ' . implode( '; ', $directives ) );
}
