<?php
/** Ads Slider — Conditional Asset Loading */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ════════════════════════════════════════
   7. بارگذاری assets (شورت‌کد یا ویجت Elementor)
   ════════════════════════════════════════ */
add_action( 'wp_enqueue_scripts', 'glass_ads_slider_register_assets', 5 );
/**
 * ثبت (register) فایل‌های اسلایدر برای enqueue شرطی.
 *
 * @return void
 */
function glass_ads_slider_register_assets() {
	$css = GLASS_DIR . '/assets/css/ads-slider.css';
	if ( file_exists( $css ) ) {
		wp_register_style( 'glass-ads-slider', GLASS_URI . '/assets/css/ads-slider.css', [ 'glass-style' ], GLASS_VERSION );
	}
	$js = GLASS_DIR . '/assets/js/ads-slider.js';
	if ( file_exists( $js ) ) {
		wp_register_script( 'glass-ads-slider', GLASS_URI . '/assets/js/ads-slider.js', [], GLASS_VERSION, true );
	}
}

add_action( 'wp_enqueue_scripts', 'glass_ads_slider_maybe_enqueue', 25 );
/**
 * enqueue خودکار در صفحاتی که شورت‌کد دارند.
 *
 * @return void
 */
function glass_ads_slider_maybe_enqueue() {
	if ( ! is_singular() ) {
		return;
	}
	$post = get_post();
	if ( $post && has_shortcode( $post->post_content, 'glass_ads_slider' ) ) {
		wp_enqueue_style( 'glass-ads-slider' );
		wp_enqueue_script( 'glass-ads-slider' );
	}
}

/* ════════════════════════════════════════
   8. ویجت Elementor «اسلایدر تبلیغی PRO»
   ════════════════════════════════════════ */
add_action( 'elementor/widgets/register', 'glass_ads_register_elementor_widget' );
/**
 * ثبت ویجت Elementor اسلایدر.
 *
 * @param object $widgets_manager مدیر ویجت‌ها.
 * @return void
 */
function glass_ads_register_elementor_widget( $widgets_manager ) {
	$path = GLASS_DIR . '/inc/widgets/class-widget-glass-ads-slider.php';
	if ( is_readable( $path ) ) {
		require_once $path;
		if ( class_exists( 'Glass_Pro_Widget_Ads_Slider' ) ) {
			$widgets_manager->register( new Glass_Pro_Widget_Ads_Slider() );
		}
	}
}
