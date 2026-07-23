<?php
/**
 * Performance Cache Helper
 * Stage 34: Group Incrementor
 * @package Alborz_Ghaleb
 * @version 8.5.4
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
function glass_pro_get_cache_group_version( string $cache_group ): int {
	$cache_group = sanitize_key( $cache_group );
	if ( '' === $cache_group ) { return 1; }
	$opt_name = 'gpcq_inc_' . $cache_group;
	$version = get_option( $opt_name, 1 );
	return (int) $version;
}
function glass_pro_cached_query( array $args, string $cache_group = 'default', int $ttl = HOUR_IN_SECONDS ): array {
	if ( empty( $args['paged'] ) && ! isset( $args['no_found_rows'] ) ) { $args['no_found_rows'] = true; }
	if ( ! isset( $args['cache_results'] ) ) { $args['cache_results'] = true; }
	$cache_group = sanitize_key( $cache_group );
	$group_version = glass_pro_get_cache_group_version( $cache_group );
	$cache_key = 'gpcq_' . $cache_group . '_v' . $group_version . '_' . md5( wp_json_encode( $args ) );
	$cached = get_transient( $cache_key );
	if ( false !== $cached && is_array( $cached ) ) { return $cached; }
	$query = new WP_Query( $args );
	$posts = $query->posts ?: [];
	set_transient( $cache_key, $posts, $ttl );
	return $posts;
}
function glass_pro_clear_cache_group( string $cache_group ): void {
	global $wpdb;
	$cache_group = sanitize_key( $cache_group );
	if ( '' === $cache_group ) { return; }
	$opt_name = 'gpcq_inc_' . $cache_group;
	$current = (int) get_option( $opt_name, 1 );
	update_option( $opt_name, $current + 1, false );
	foreach ( [ '_transient_gpcq_', '_transient_timeout_gpcq_' ] as $prefix ) {
		$like_patterns = [$prefix . $cache_group . '_%', $prefix . $cache_group . '_v%'];
		foreach ( $like_patterns as $like ) {
			$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",$like));
		}
	}
}
function glass_pro_clear_user_ad_cache( int $user_id ): void {
	glass_pro_clear_cache_group( 'user_ads_' . $user_id );
	do_action( 'glass_pro/cache/cleared', 'user_ads', $user_id );
}
add_action( 'glass_pro/portfolio/submitted', function( $post_id, $user_id ) {
	if ( $user_id ) { glass_pro_clear_user_ad_cache( (int) $user_id ); }
}, 10, 2 );
add_action( 'save_post_portfolio', function( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) return;
	$author_id = (int) get_post_field( 'post_author', $post_id );
	if ( $author_id ) { glass_pro_clear_user_ad_cache( $author_id ); }
});
