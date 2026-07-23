<?php
/**
 * Mobile Home AW Layout — independently removable.
 * CSS is delivered through the shared layout bundle; this module only enables
 * its scoped section with a body class.
 *
 * Rollback: remove '/inc/mobile-home-aw.php' from functions.php.
 *
 * @package Alborz_Ghaleb
 * @since   8.7.9
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_filter( 'body_class', 'glass_mobile_home_aw_body_class' );
function glass_mobile_home_aw_body_class( array $classes ): array {
	if ( apply_filters( 'glass_pro/mobile_home_aw/enabled', true ) ) {
		$classes[] = 'glass-feature-home-aw';
	}
	return $classes;
}
