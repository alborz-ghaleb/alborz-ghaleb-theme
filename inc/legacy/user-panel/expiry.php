<?php
/**
 * Ad expiry cron + helpers
 * [FIX v5.14.0] no_found_rows اضافه شد
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function glass_check_expired_ads(): void {
    $now = current_time( 'timestamp' );
    $q   = new WP_Query( [
        'post_type'      => 'portfolio',
        'post_status'    => 'publish',
        'posts_per_page' => 100,
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'meta_query'     => [
            'relation' => 'OR',
            [
                'key'     => 'portfolio_expiry',
                'value'   => $now,
                'compare' => '<=',
                'type'    => 'NUMERIC',
            ],
            [
                // Backward compatibility for ads created by older releases.
                'key'     => 'glass_ad_expiry',
                'value'   => $now,
                'compare' => '<=',
                'type'    => 'NUMERIC',
            ],
        ],
    ] );

    foreach ( $q->posts as $post_id ) {
        // A sold ad remains sold; it must not be overwritten by expiry.
        if ( 'sold' === glass_get_ad_state( (int) $post_id ) ) {
            continue;
        }

        update_post_meta( $post_id, 'portfolio_ad_state', 'expired' );
        wp_update_post( [ 'ID' => $post_id, 'post_status' => 'draft' ] );

        // Remove only the legacy key. Keep portfolio_expiry so the dashboard
        // and state helper retain the original expiry timestamp.
        delete_post_meta( $post_id, 'glass_ad_expiry' );
    }
}

/**
 * Resolve the canonical state of a portfolio ad.
 *
 * The current submit/dashboard flow stores `portfolio_ad_state` and
 * `portfolio_expiry`. The legacy keys are read as a backward-compatible
 * fallback so existing ads are not silently treated as active.
 *
 * @param int $post_id Portfolio post ID.
 * @return string `active`, `sold`, or `expired`.
 */
if ( ! function_exists( 'glass_get_ad_state' ) ) {
    function glass_get_ad_state( int $post_id ): string {
        $stored_state = sanitize_key( (string) get_post_meta( $post_id, 'portfolio_ad_state', true ) );

        // Sold is an explicit user action and takes priority over expiry.
        if ( 'sold' === $stored_state || '1' === (string) get_post_meta( $post_id, 'glass_ad_sold', true ) ) {
            return 'sold';
        }

        if ( 'expired' === $stored_state ) {
            return 'expired';
        }

        // Read the new key first, then the legacy key for existing content.
        $expiry = (int) get_post_meta( $post_id, 'portfolio_expiry', true );
        if ( $expiry <= 0 ) {
            $expiry = (int) get_post_meta( $post_id, 'glass_ad_expiry', true );
        }

        if ( $expiry > 0 && $expiry <= current_time( 'timestamp' ) ) {
            return 'expired';
        }

        return 'active';
    }
}

function glass_is_ad_expired( int $post_id ): bool {
    return 'expired' === glass_get_ad_state( $post_id );
}

function glass_is_user_submitted_ad( int $post_id, ?int $user_id = null ): bool {
    if ( null === $user_id ) {
        $user_id = get_current_user_id();
    }
    return $user_id > 0 && (int) get_post_field( 'post_author', $post_id ) === $user_id;
}

add_action( 'wp', 'glass_schedule_expiry_check' );
function glass_schedule_expiry_check(): void {
    if ( ! wp_next_scheduled( 'glass_check_expired_ads' ) ) {
        wp_schedule_event( time(), 'daily', 'glass_check_expired_ads' );
    }
}
add_action( 'glass_check_expired_ads', 'glass_check_expired_ads' );
