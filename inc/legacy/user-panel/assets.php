<?php
/** User Panel — Conditional CSS/JS Enqueue */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ════════════════════════════════════════
   7. بارگذاری CSS/JS پنل (هنگام وجود شورت‌کد)
   ════════════════════════════════════════ */

add_action( 'wp_enqueue_scripts', 'glass_user_panel_assets', 26 );
function glass_user_panel_assets() {
    if ( ! is_singular() ) {
        return;
    }
    $post = get_post();
    if ( ! $post ) {
        return;
    }
    $has = has_shortcode( $post->post_content, 'glass_user_login' )
        || has_shortcode( $post->post_content, 'glass_user_dashboard' );
    if ( ! $has ) {
        return;
    }

    $css = GLASS_DIR . '/assets/css/user-panel.css';
    if ( file_exists( $css ) ) {
        wp_enqueue_style( 'glass-user-panel', GLASS_URI . '/assets/css/user-panel.css', [ 'glass-style' ], GLASS_VERSION );
    }
    $js = GLASS_DIR . '/assets/js/user-panel.js';
    if ( file_exists( $js ) ) {
        wp_enqueue_script( 'glass-user-panel', GLASS_URI . '/assets/js/user-panel.js', [], GLASS_VERSION, true );
    }
}
