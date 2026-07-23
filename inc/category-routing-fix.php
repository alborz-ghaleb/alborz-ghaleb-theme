<?php
/**
 * Category Routing Fix
 *
 * Polylang's Persian translated category base may legitimately be emitted as
 * /دسته%2520بندی/. The old global double-decoding SEO redirect converted it to
 * the non-existent /دسته-بندی/ path. This module bypasses that mutation only
 * for the built-in post category taxonomy.
 *
 * @package Alborz_Ghaleb
 * @since   8.8.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * No global SEO hook is removed here. The existing redirect/canonical helpers
 * call glass_category_route_is_request() and skip only post-category URLs.
 */

/** Decode at most three layers without touching query parameters. */
function glass_category_route_decoded_path( string $uri ): string {
	$path = (string) wp_parse_url( $uri, PHP_URL_PATH );
	$last = '';
	$step = 0;
	while ( $path !== $last && $step < 3 && preg_match( '/%[0-9a-f]{2}/i', $path ) ) {
		$last = $path;
		$path = rawurldecode( $path );
		$step++;
	}
	return trim( $path, '/' );
}

/** Identify all known category bases, including the problematic Persian base. */
function glass_category_route_is_base( string $segment ): bool {
	$normalized = mb_strtolower( trim( rawurldecode( $segment ) ) );
	$normalized = str_replace( [ ' ', '-', '_', "\xE2\x80\x8C" ], '', $normalized );
	$bases = [
		'دستهبندی', // دسته بندی / دسته‌بندی / دسته-بندی
		'category',
		'категория',
		'الفئة',
		'խմբավորում',
	];
	return in_array( $normalized, $bases, true );
}


/** True only for WordPress post-category routes, never pages/CPT taxonomies. */
function glass_category_route_is_request( string $uri = '' ): bool {
	if ( function_exists( 'is_category' ) && is_category() ) {
		return true;
	}
	if ( '' === $uri ) {
		$uri = (string) ( $_SERVER['REQUEST_URI'] ?? '' );
	}
	$segments = array_values( array_filter( explode( '/', glass_category_route_decoded_path( $uri ) ), 'strlen' ) );
	foreach ( $segments as $segment ) {
		if ( glass_category_route_is_base( (string) $segment ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Resolve malformed/double-encoded Persian category requests directly to the
 * real term before WordPress decides the request is a page or a 404.
 */
add_filter( 'request', 'glass_category_route_resolve_request', 1 );
function glass_category_route_resolve_request( array $query_vars ): array {
	$uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	if ( '' === $uri ) {
		return $query_vars;
	}

	$path = glass_category_route_decoded_path( $uri );
	if ( '' === $path ) {
		return $query_vars;
	}
	$segments = array_values( array_filter( explode( '/', $path ), 'strlen' ) );
	$base_at  = -1;
	foreach ( $segments as $index => $segment ) {
		if ( glass_category_route_is_base( (string) $segment ) ) {
			$base_at = (int) $index;
			break;
		}
	}
	if ( $base_at < 0 || empty( $segments[ $base_at + 1 ] ) ) {
		return $query_vars;
	}

	$slug = sanitize_title( rawurldecode( (string) $segments[ $base_at + 1 ] ) );
	if ( '' === $slug ) {
		return $query_vars;
	}

	$term = get_term_by( 'slug', $slug, 'category' );
	if ( ! $term || is_wp_error( $term ) ) {
		// Some migrated Persian terms keep a decoded slug/name in the database.
		$term = get_term_by( 'name', rawurldecode( (string) $segments[ $base_at + 1 ] ), 'category' );
	}
	if ( ! $term || is_wp_error( $term ) ) {
		return $query_vars;
	}

	// Remove page/singular guesses produced by a failed rewrite match.
	foreach ( [ 'error', 'pagename', 'page', 'name', 'attachment', 'attachment_id' ] as $key ) {
		unset( $query_vars[ $key ] );
	}
	$query_vars['cat'] = (int) $term->term_id;

	// Support both /page/2/ and /صفحه/2/ without redirecting either form.
	$tail = array_slice( $segments, $base_at + 2 );
	if ( count( $tail ) >= 2 ) {
		$page_base = mb_strtolower( rawurldecode( (string) $tail[0] ) );
		if ( in_array( $page_base, [ 'page', 'صفحه' ], true ) && ctype_digit( (string) $tail[1] ) ) {
			$query_vars['paged'] = max( 1, (int) $tail[1] );
		}
	}

	return $query_vars;
}

/**
 * Never let redirect_canonical send a category-base request to an unrelated
 * page/home. Rank Math still prints its canonical tag normally.
 */
add_filter( 'redirect_canonical', 'glass_category_route_prevent_wrong_redirect', 99, 2 );
function glass_category_route_prevent_wrong_redirect( $redirect_url, $requested_url ) {
	$uri = is_string( $requested_url ) && '' !== $requested_url
		? $requested_url
		: (string) ( $_SERVER['REQUEST_URI'] ?? '' );
	$segments = array_values( array_filter( explode( '/', glass_category_route_decoded_path( $uri ) ), 'strlen' ) );
	foreach ( $segments as $segment ) {
		if ( glass_category_route_is_base( (string) $segment ) ) {
			return false;
		}
	}
	return $redirect_url;
}

/** One safe rewrite refresh after updating the theme (same-theme updates included). */
add_action( 'admin_init', 'glass_category_route_flush_once' );
function glass_category_route_flush_once(): void {
	$flag = 'glass_category_route_fixed_8_8_5';
	if ( get_option( $flag ) ) {
		return;
	}
	flush_rewrite_rules( false );
	update_option( $flag, time(), false );
}
