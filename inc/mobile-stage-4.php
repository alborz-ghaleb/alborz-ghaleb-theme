<?php
/**
 * Performance Optimization — Stage 4: Font Request Reduction
 *
 * Weight 500 keeps its CSS semantics but reuses the Regular font file. The
 * browser downloads one URL for both 400 and 500 instead of fetching the
 * separate 51 KB Medium file. Original font files remain untouched.
 *
 * Rollback only this stage:
 * remove '/inc/mobile-stage-4.php' from $glass_pro_core in functions.php.
 * The original four-weight map is then restored automatically.
 *
 * @package Alborz_Ghaleb
 * @since   8.7.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'glass_pro/font_weights', 'glass_mobile_stage_4_font_weights', 10 );
function glass_mobile_stage_4_font_weights( array $weights ): array {
	if ( ! apply_filters( 'glass_pro/mobile_stage_4/enabled', true ) ) {
		return $weights;
	}

	return [
		400 => 'Regular',
		500 => 'Regular',
		600 => 'SemiBold',
		700 => 'Bold',
	];
}
