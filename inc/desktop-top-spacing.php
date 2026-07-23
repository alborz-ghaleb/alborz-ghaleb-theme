<?php
/**
 * Desktop Header-to-Content Spacing — independently removable.
 * CSS is delivered through the shared layout bundle; this module only enables
 * its scoped section with a body class.
 *
 * Rollback: remove '/inc/desktop-top-spacing.php' from functions.php.
 *
 * @package Alborz_Ghaleb
 * @since   8.8.1
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_filter( 'body_class', 'glass_desktop_top_spacing_body_class' );
function glass_desktop_top_spacing_body_class( array $classes ): array {
	if ( apply_filters( 'glass_pro/desktop_top_spacing/enabled', true ) ) {
		$classes[] = 'glass-feature-desktop-top';
	}
	return $classes;
}
