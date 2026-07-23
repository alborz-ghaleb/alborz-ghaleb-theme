<?php
/**
 * Shared Layout Optimizations Bundle
 *
 * Delivers all optional layout CSS sections in one cacheable request. Each
 * section is scoped to a body class supplied by its own removable module.
 *
 * @package Alborz_Ghaleb
 * @since   8.8.3
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'wp_enqueue_scripts', 'glass_layout_optimizations_bundle', 37 );
function glass_layout_optimizations_bundle(): void {
	$relative = 'assets/css/layout-optimizations.css';
	if ( ! is_readable( GLASS_PRO_DIR . '/' . $relative ) ) {
		return;
	}
	wp_enqueue_style(
		'glass-layout-optimizations',
		GLASS_PRO_URI . '/' . $relative,
		[ 'glass-dark-mode' ],
		GLASS_PRO_VERSION
	);
}
