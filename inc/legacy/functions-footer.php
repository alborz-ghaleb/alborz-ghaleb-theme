<?php
/**
 * Footer Helper Functions
 * @package Alborz_Ghaleb
 */
if (!defined('ABSPATH')) exit;

add_action('after_setup_theme', 'fl_footer_setup', 25);
function fl_footer_setup() {
    /*
     * [FIX] لیبل‌های واضح و فارسی برای مکان‌های منوی فوتر.
     * پیش‌تر «Footer Column 1/2/3» بود که مشخص نمی‌کرد کدام مکان مربوط
     * به ستون «دسترسی سریع» یا «درباره ما» است. حالا نام‌ها دقیقاً با
     * عنوان ستون‌های نمایشی فوتر مطابقت دارند تا در پیشخوان →
     * نمایش → فهرست‌ها، انتخاب منو ساده و بدون ابهام باشد.
     * (footer_3 بلااستفاده بود و حذف شد.)
     */
    register_nav_menus([
        'footer_1' => __('فوتر: دسترسی سریع', 'glassmorphism-child-pro'),
        'footer_2' => __('فوتر: درباره ما', 'glassmorphism-child-pro'),
    ]);
}

add_action('customize_register', 'fl_footer_customizer', 25);
function fl_footer_customizer($wp_customize) {
    $wp_customize->add_section('fl_footer_section', ['title' => __('⬇ Footer Settings', 'glassmorphism-child-pro'), 'priority' => 35]);
    $wp_customize->add_setting('fl_footer_copyright', ['default' => sprintf( '© %s All rights reserved.', wp_date( 'Y' ) ), 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('fl_footer_copyright', ['label' => __('Copyright Text', 'glassmorphism-child-pro'), 'section' => 'fl_footer_section', 'type' => 'text']);
    $wp_customize->add_setting('fl_footer_desc', ['default' => '', 'sanitize_callback' => 'sanitize_textarea_field']);
    $wp_customize->add_control('fl_footer_desc', ['label' => __('Footer Description', 'glassmorphism-child-pro'), 'section' => 'fl_footer_section', 'type' => 'textarea']);
    $wp_customize->add_setting('fl_footer_phone', ['default' => '', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('fl_footer_phone', ['label' => __('Contact Phone', 'glassmorphism-child-pro'), 'section' => 'fl_footer_section', 'type' => 'text']);
    $wp_customize->add_setting('fl_footer_email', ['default' => '', 'sanitize_callback' => 'sanitize_email']);
    $wp_customize->add_control('fl_footer_email', ['label' => __('Contact Email', 'glassmorphism-child-pro'), 'section' => 'fl_footer_section', 'type' => 'email']);
    $wp_customize->add_setting('fl_footer_address', ['default' => '', 'sanitize_callback' => 'sanitize_textarea_field']);
    $wp_customize->add_control('fl_footer_address', ['label' => __('Address', 'glassmorphism-child-pro'), 'section' => 'fl_footer_section', 'type' => 'textarea']);
}

function fl_ft_icon($n) {
    $icons = [
        'phone' => '<svg viewBox="0 0 24 24" fill="none"><path d="M22 16.92v3" stroke="currentColor" stroke-width="2"/></svg>',
        'email' => '<svg viewBox="0 0 24 24" fill="none"><rect x="2" y="4" width="20" height="16" rx="3" stroke="currentColor" stroke-width="2"/></svg>',
        'map' => '<svg viewBox="0 0 24 24" fill="none"><path d="M12 21s7-4.35 7-11a7 7 0 10-14 0c0 6.65 7 11 7 11Z" stroke="currentColor" stroke-width="2"/></svg>',
        'arrow-up' => '<svg viewBox="0 0 24 24" fill="none"><path d="M18 15l-6-6-6 6" stroke="currentColor" stroke-width="2"/></svg>',
    ];
    return isset($icons[$n]) ? $icons[$n] : '';
}

class FL_Footer_Walker extends Walker_Nav_Menu {
    function start_lvl(&$o, $d = 0, $a = null) {}
    function end_lvl(&$o, $d = 0, $a = null) {}
    function start_el(&$o, $item, $d = 0, $a = null, $id = 0) {
        $url = !empty($item->url) ? $item->url : '#';
        $title = !empty($item->title) ? $item->title : '';
        $o .= '<li class="fl-ft-menu-item"><a href="' . esc_url($url) . '" class="fl-ft-link"><span class="fl-ft-link-dot"></span><span>' . esc_html($title) . '</span></a>';
    }
    function end_el(&$o, $item, $d = 0, $a = null) { $o .= '</li>'; }
}

/* ────────────────────────────────────────
   Polylang support for footer Customizer values
   ──────────────────────────────────────── */
add_action( 'init', 'glass_footer_register_polylang_strings', 30 );
function glass_footer_register_polylang_strings() {
    if ( ! function_exists( 'pll_register_string' ) ) {
        return;
    }
    $group = defined( 'GLASS_PRO_PLL_GROUP' ) ? GLASS_PRO_PLL_GROUP : 'Alborz Ghaleb';
    $items = [
        'footer_copyright' => get_theme_mod( 'fl_footer_copyright', sprintf( '© %s All rights reserved.', wp_date( 'Y' ) ) ),
        'footer_desc_customizer' => get_theme_mod( 'fl_footer_desc', '' ),
        'footer_phone' => get_theme_mod( 'fl_footer_phone', '' ),
        'footer_email' => get_theme_mod( 'fl_footer_email', '' ),
        'footer_address' => get_theme_mod( 'fl_footer_address', '' ),
    ];
    foreach ( $items as $name => $value ) {
        if ( '' === (string) $value ) {
            continue;
        }
        $multiline = in_array( $name, [ 'footer_desc_customizer', 'footer_address' ], true );
        pll_register_string( 'glass_' . $name, (string) $value, $group, $multiline );
    }
}

function glass_footer_translate_value( $value ) {
    $value = (string) $value;
    if ( '' === $value ) {
        return $value;
    }
    return function_exists( 'pll__' ) ? pll__( $value ) : $value;
}

function glass_footer_theme_mod_i18n( string $mod, string $default = '' ): string {
    return glass_footer_translate_value( get_theme_mod( $mod, $default ) );
}
