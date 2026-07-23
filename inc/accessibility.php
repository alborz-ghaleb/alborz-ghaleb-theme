<?php
/**
 * Accessibility (WCAG 2.2 AA)
 *
 * skip-link، پشتیبانی prefers-reduced-motion، aria-hidden روی آیکون‌های تزئینی.
 *
 * @package Alborz_Ghaleb
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ────────────────────────────────────────
   Skip to content link
   درست بعد از باز شدن body چاپ می‌شود.
   ──────────────────────────────────────── */
add_action( 'wp_body_open', 'glass_pro_skip_link', 1 );
/**
 * چاپ لینک «پرش به محتوا».
 *
 * @return void
 */
function glass_pro_skip_link(): void {
	$part = GLASS_PRO_DIR . '/template-parts/header/skip-link.php';
	if ( is_readable( $part ) ) {
		require $part;
		return;
	}
	printf(
		'<a class="glass-skip-link screen-reader-text" href="#main-content">%s</a>',
		esc_html__( 'پرش به محتوای اصلی', 'glassmorphism-child-pro' )
	);
}

/* ────────────────────────────────────────
   استایل‌های a11y inline سبک (skip-link + reduced-motion + focus)
   ──────────────────────────────────────── */
add_action( 'wp_head', 'glass_pro_a11y_inline_css', 20 );
/**
 * چاپ CSS کوچک دسترس‌پذیری.
 *
 * @return void
 */
function glass_pro_a11y_inline_css(): void {
	?>
<style id="glass-pro-a11y">
.glass-skip-link{position:absolute;top:-100px;inset-inline-start:8px;z-index:100000;background:var(--fl-primary,#2D5F93);color:#fff;padding:10px 18px;border-radius:8px;font-weight:700;transition:top .2s ease}
.glass-skip-link:focus{top:8px;outline:3px solid var(--fl-accent,#A4B400);outline-offset:2px}
@media (prefers-reduced-motion: reduce){
  *,*::before,*::after{animation-duration:.001ms!important;animation-iteration-count:1!important;transition-duration:.001ms!important;scroll-behavior:auto!important}
}
.gh-feature-tagline,.gh-sec-eyebrow{color:#1B4A73!important;font-weight:400!important}.dark-mode .gh-feature-tagline,.dark-mode .gh-sec-eyebrow{color:#1B4A73!important}.gh-blog-readmore{color:#1B4A73!important;font-weight:400!important}.dark-mode .gh-blog-readmore{color:#1B4A73!important}
</style>
	<?php
}
