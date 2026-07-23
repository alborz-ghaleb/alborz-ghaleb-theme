<?php
/**
 * Desktop Inner Content Spacing — independently removable.
 * CSS is delivered through the shared layout bundle; this module only enables
 * its scoped section with a body class.
 *
 * Rollback: remove '/inc/desktop-inner-spacing.php' from functions.php.
 *
 * @package Alborz_Ghaleb
 * @since   8.8.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_filter( 'body_class', 'glass_desktop_inner_spacing_body_class' );
function glass_desktop_inner_spacing_body_class( array $classes ): array {
	if ( apply_filters( 'glass_pro/desktop_inner_spacing/enabled', true ) ) {
		$classes[] = 'glass-feature-desktop-inner';
	}
	return $classes;
}
