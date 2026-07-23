<?php
/**
 * Critical CSS — inline استایل‌های above-the-fold برای بهبود LCP/FCP.
 *
 * [IMPROVEMENT v5.14.0] از فایل جداگانه‌ی critical.min.css استفاده می‌کند
 * تا کد تمیزتر و مدیریت‌پذیرتر باشد.
 *
 * @package Alborz_Ghaleb
 * @since   5.0.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_head', 'glass_pro_inline_critical_css', 2 );
/**
 * چاپ critical CSS در head.
 *
 * @return void
 */
function glass_pro_inline_critical_css(): void {
	if ( ! apply_filters( 'glass_pro/critical_css/enabled', true ) ) {
		return;
	}

	// [v5.14.0] استفاده از فایل جداگانه critical.min.css
	$critical_file = GLASS_PRO_DIR . '/assets/css/critical.min.css';
	if ( is_readable( $critical_file ) ) {
		$css = (string) file_get_contents( $critical_file );
	} else {
		// Fallback به CSS توکار (برای backward compatibility)
		$css = ':root{--fl-primary:#2D5F93;--fl-primary-dark:#1B4A73;--fl-accent:#A4B400;--fl-text:#17212B;--fl-text-light:#64748B;--fl-white:#FFFFFF;--fl-glass-bg:#ffffff;--fl-glass-border:rgba(0,0,0,0.07);--fl-glass-blur:0px}'
			 . '*,*::before,*::after{box-sizing:border-box}'
			 . 'html{-webkit-text-size-adjust:100%;scroll-behavior:smooth}'
			 . 'body{margin:0;font-family:"Vazirmatn",-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;font-size:16px;line-height:1.7;color:var(--fl-text);background:#f8fafc;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}'
			 . 'img,picture,video,iframe{max-width:100%;height:auto;display:block}'
			 . 'a{color:var(--fl-primary);text-decoration:none}';
	}

	/**
	 * Filter: glass_pro/critical_css — تغییر critical CSS
	 *
	 * @param string $css
	 */
	$css = (string) apply_filters( 'glass_pro/critical_css', $css );

	if ( '' === $css ) {
		return;
	}

	echo "<style id=\"glass-pro-critical\">{$css}</style>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

add_action( 'wp_head', 'glass_pro_lazy_render_css', 3 );
/**
 * چاپ CSS lazy-render برای بهبود رندر موبایل.
 * content-visibility: auto به مرورگر می‌گوید اجزای خارج viewport را
 * تا زمان مشاهده render نکند → صرفه‌جویی چشمگیر در زمان paint.
 *
 * @return void
 */
function glass_pro_lazy_render_css(): void {
	if ( ! apply_filters( 'glass_pro/lazy_render/enabled', true ) ) {
		return;
	}
	echo "<style id=\"glass-pro-lazy-render\">"
		// related posts، comments، footer widgets — خارج viewport هستن
		. ".fl-sb-rel,.gc-section,#secondary,.fl-sb-vip-channel,.fl-sp-rel,.glass-dash-empty,.glass-no-content"
		// [v5.14.0] اضافه شدن فوتر و سایدبار به lazy render
		. ",.fl-ft-col,.fl-ft-bottom,.fl-sb-vip-card"
		. "{content-visibility:auto;contain-intrinsic-size:auto 500px}"
		// ads slider که scroll می‌شه
		. ".glass-ads-viewport{content-visibility:auto;contain-intrinsic-size:auto 300px}"
		. "</style>\n";
}
