<?php
/**
 * Portfolio — Cities Taxonomy، Menu Location، Template، City Menu Renderer
 *
 * این فایل بخشی از تقسیم functions-portfolio.php است (split & include refactor).
 * تمام توابع با همان نام و سیگنیچر اصلی نگهداری شده‌اند.
 *
 * @package Alborz_Ghaleb
 * @since   5.0.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ════════════════════════════════════════
   تکسونومی شهر
════════════════════════════════════════ */

if (!function_exists('replywp_register_city_taxonomy')) {
    function replywp_register_city_taxonomy() {
        // Phase 2 compatibility: اگر پلاگین Core taxonomy را ثبت کرده، قالب فقط fallback است.
        if ( taxonomy_exists( 'portfolio_city' ) ) {
            return;
        }
        register_taxonomy('portfolio_city', array('portfolio'), array(
            'labels' => array(
                'name'          => 'شهرها',
                'singular_name' => 'شهر',
                'menu_name'     => 'شهرها',
                'all_items'     => 'همه شهرها',
                'edit_item'     => 'ویرایش شهر',
                'add_new_item'  => 'افزودن شهر',
                'search_items'  => 'جستجوی شهر',
                'parent_item'   => 'استان',
            ),
            'public'             => true,
            'publicly_queryable' => true,
            'hierarchical'       => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_nav_menus'  => true,
            'show_admin_column'  => true,
            'rewrite'            => array(
                'slug'       => 'city',
                'with_front' => false,
            ),
        ));
    }
}
add_action('init', 'replywp_register_city_taxonomy', 0);

/* ════════════════════════════════════════
   محل فهرست شهرها
════════════════════════════════════════ */

if (!function_exists('replywp_register_city_menu_location')) {
    function replywp_register_city_menu_location() {
        register_nav_menus(array(
            'portfolio_city_menu' => 'منوی شهرها (صفحه دست دوم)',
        ));
    }
}
add_action('after_setup_theme', 'replywp_register_city_menu_location');

/* ════════════════════════════════════════
   تمپلیت آرشیو
════════════════════════════════════════ */

if (!function_exists('replywp_portfolio_template')) {
    function replywp_portfolio_template($template) {
        if (
            !is_post_type_archive('portfolio') &&
            !is_tax('themsah_theme_type') &&
            !is_tax('portfolio_city')
        ) {
            return $template;
        }
        replywp_render_portfolio_page();
        exit;
    }
}
add_filter('template_include', 'replywp_portfolio_template', 999);

/* ════════════════════════════════════════
   متن دکمه شهر
════════════════════════════════════════ */

if (!function_exists('replywp_get_city_button_text')) {
    function replywp_get_city_button_text() {
        if (is_tax('portfolio_city')) {
            $term = get_queried_object();
            if ($term && !is_wp_error($term) && !empty($term->name)) {
                return 'شهر: ' . replywp_clean_plain_text($term->name);
            }
        }
        return 'انتخاب شهر';
    }
}

/* ════════════════════════════════════════
   رندر منوی شهر
════════════════════════════════════════ */

if (!function_exists('replywp_render_city_menu')) {
    function replywp_render_city_menu($class = 'fl-city-list') {
        if (has_nav_menu('portfolio_city_menu')) {
            wp_nav_menu(array(
                'theme_location' => 'portfolio_city_menu',
                'container'      => false,
                'menu_class'     => $class,
                'depth'          => 2,
                'fallback_cb'    => false,
            ));
        } else {
            $cities = get_terms(array(
                'taxonomy'   => 'portfolio_city',
                'hide_empty' => false,
                'parent'     => 0,
            ));
            if (!empty($cities) && !is_wp_error($cities)) {
                echo '<ul class="' . esc_attr($class) . '" role="list" aria-label="' . esc_attr__( 'فهرست شهرها', 'glassmorphism-child-pro' ) . '">';
                foreach ($cities as $city) {
                    $active = is_tax('portfolio_city', $city->term_id) ? ' current-menu-item' : '';
                    echo '<li class="' . esc_attr(trim($active)) . '">';
                    echo '<a href="' . esc_url(get_term_link($city)) . '">' . esc_html($city->name) . '</a>';
                    echo '</li>';
                }
                echo '</ul>';
            } else {
                echo '<ul class="' . esc_attr($class) . '" role="list" aria-label="' . esc_attr__( 'فهرست شهرها', 'glassmorphism-child-pro' ) . '">';
                echo '<li><span style="padding:14px 20px;display:block;color:#94a3b8;font-size:.82rem">' . esc_html__( 'ابتدا شهرها را اضافه کنید', 'glassmorphism-child-pro' ) . '</span></li>';
                echo '</ul>';
            }
        }
    }
}

