<?php
/** Ads Slider — Shortcode [glass_ads_slider] */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ════════════════════════════════════════
   6. شورت‌کد [glass_ads_slider]
   ════════════════════════════════════════ */
add_shortcode( 'glass_ads_slider', 'glass_ads_slider_shortcode' );
/**
 * هندلر شورت‌کد.
 *
 * @param array $atts ویژگی‌ها.
 * @return string
 */
function glass_ads_slider_shortcode( $atts ) {
	$atts = shortcode_atts( [
		'count'       => -1,
		'group'       => '',
		'autoplay'    => 5000,
		'loop'        => 1,
		'effect'      => 'slide',
		'arrows'      => 1,
		'dots'        => 1,
		'progress'    => 1,
		'pause_hover' => 1,
		'per_desktop' => 2,
		'per_tablet'  => 1,
		'per_mobile'  => 1,
		'gap'         => 24,
		'ratio'       => '2.5',
	], $atts, 'glass_ads_slider' );

	return glass_ads_render( $atts );
}

