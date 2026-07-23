<?php
/**
 * Flavor Header Functions
 * Alborz Ghaleb | Polylang 6-Language | Dynamic Menus | Breadcrumb
 * @version 3.1
 */

if (!defined('ABSPATH')) exit;

/* ═══════════════════════════════════════
   1. SETUP — Logo & Dynamic Menus
   ═══════════════════════════════════════ */
add_action('after_setup_theme', 'fl_header_setup', 20);
function fl_header_setup() {
    // [v5.0.5] add_theme_support('custom-logo') از inc/setup.php صدا زده شده (تکراری حذف شد).

    register_nav_menus([
        'primary_default' => __('Primary - Default', 'glassmorphism-child-pro'),
        'primary_fa'      => __('Primary - فارسی', 'glassmorphism-child-pro'),
        'primary_en'      => __('Primary - English', 'glassmorphism-child-pro'),
        'primary_hy'      => __('Primary - Հայերեն', 'glassmorphism-child-pro'),
        'primary_ar'      => __('Primary - العربية', 'glassmorphism-child-pro'),
        'primary_tr'      => __('Primary - Türkçe', 'glassmorphism-child-pro'),
        'primary_ru'      => __('Primary - Русский', 'glassmorphism-child-pro'),
    ]);
}

/* ═══════════════════════════════════════
   2. ENQUEUE CSS & JS
   ═══════════════════════════════════════
   [REMOVED v3.1.3] تابع fl_header_enqueue() حذف شد چون:
   - hook اش از قبل کامنت شده بود (dead code)
   - enqueue هدر در functions.php به‌صورت مرکزی انجام می‌شود
   با handle 'glass-header' (نه 'fl-header')
   ═══════════════════════════════════════ */

/* ═══════════════════════════════════════
   3. CUSTOMIZER — Top Bar Settings
   ═══════════════════════════════════════ */
add_action('customize_register', 'fl_header_customizer', 20);
function fl_header_customizer($wp_customize) {
    $wp_customize->add_section('fl_topbar_section', [
        'title'    => __('⬆ Header Info Bar', 'glassmorphism-child-pro'),
        'priority' => 29,
    ]);

    // شماره تلفن ۱
    $wp_customize->add_setting('fl_phone1', [
        'default'           => '+98 21 0000 0000',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('fl_phone1', [
        'label'   => __('📞 Phone 1', 'glassmorphism-child-pro'),
        'section' => 'fl_topbar_section',
        'type'    => 'text',
    ]);

    // شماره تلفن ۲
    $wp_customize->add_setting('fl_phone2', [
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('fl_phone2', [
        'label'   => __('📞 Phone 2 (Optional)', 'glassmorphism-child-pro'),
        'section' => 'fl_topbar_section',
        'type'    => 'text',
    ]);

    // ایمیل
    $wp_customize->add_setting('fl_email', [
        'default'           => '',
        'sanitize_callback' => 'sanitize_email',
    ]);
    $wp_customize->add_control('fl_email', [
        'label'   => __('📧 Email (Optional)', 'glassmorphism-child-pro'),
        'section' => 'fl_topbar_section',
        'type'    => 'email',
    ]);

    // آدرس
    $wp_customize->add_setting('fl_address', [
        'default'           => 'Tehran, Iran',
        'sanitize_callback' => 'sanitize_textarea_field',
    ]);
    $wp_customize->add_control('fl_address', [
        'label'   => __('📍 Address', 'glassmorphism-child-pro'),
        'section' => 'fl_topbar_section',
        'type'    => 'textarea',
    ]);

    // نمایش
    $wp_customize->add_setting('fl_topbar_show', [
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ]);
    $wp_customize->add_control('fl_topbar_show', [
        'label'   => __('Show Info Bar', 'glassmorphism-child-pro'),
        'section' => 'fl_topbar_section',
        'type'    => 'checkbox',
    ]);

    // شبکه‌های اجتماعی
    $socials = [
        'fl_social_instagram' => '📸 Instagram URL',
        'fl_social_telegram'  => '✈ Telegram URL',
        'fl_social_whatsapp'  => '💬 WhatsApp URL',
        'fl_social_linkedin'  => '💼 LinkedIn URL',
    ];
    foreach ($socials as $key => $label) {
        $wp_customize->add_setting($key, [
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
        ]);
        $wp_customize->add_control($key, [
            'label'   => __($label, 'glassmorphism-child-pro'),
            'section' => 'fl_topbar_section',
            'type'    => 'url',
        ]);
    }
}

/* ═══════════════════════════════════════
   4. POLYLANG STRINGS
   ═══════════════════════════════════════
   [FIX v3.1.3] پارامتر چهارم pll_register_string ($multiline) بود
   ولی همه‌جا false گذاشته شده بود. برای آدرس که چندخطی است باید
   true باشد تا در صفحه String Translations مترجم بتواند چند خط
   وارد کند. الان فیلدهای multiline و single-line جدا شده‌اند.
   ═══════════════════════════════════════ */
add_action('init', 'fl_header_pll_strings', 20);
function fl_header_pll_strings() {
    if (!function_exists('pll_register_string')) return;

    // فیلدهای تک‌خطی
    $single_line = [
        'Phone 1' => get_theme_mod('fl_phone1', '+98 21 0000 0000'),
        'Phone 2' => get_theme_mod('fl_phone2', ''),
        'Email'   => get_theme_mod('fl_email', ''),
    ];
    foreach ($single_line as $name => $val) {
        if (!empty($val)) {
            pll_register_string($name, $val, 'Flavor Header', false);
        }
    }

    // فیلدهای چندخطی — multiline = true
    $multi_line = [
        'Address' => get_theme_mod('fl_address', 'Tehran, Iran'),
    ];
    foreach ($multi_line as $name => $val) {
        if (!empty($val)) {
            pll_register_string($name, $val, 'Flavor Header', true);
        }
    }
}

/* ═══════════════════════════════════════
   5. HELPERS
   ═══════════════════════════════════════ */
function fl_h_translate($mod, $default = '') {
    $v = get_theme_mod($mod, $default);
    return (function_exists('pll__') && !empty($v)) ? pll__($v) : $v;
}

function fl_h_lang() {
    if (function_exists('pll_current_language')) {
        $l = pll_current_language('slug');
        if (!empty($l)) return $l;
    }
    return strtolower(substr(get_locale(), 0, 2));
}

function fl_h_is_rtl() {
    return in_array(fl_h_lang(), ['fa', 'ar'], true);
}

function fl_h_menu_location() {
    $lang = fl_h_lang();
    $map  = [
        'fa' => 'primary_fa', 'en' => 'primary_en',
        'hy' => 'primary_hy', 'am' => 'primary_hy',
        'ar' => 'primary_ar', 'tr' => 'primary_tr',
        'ru' => 'primary_ru',
    ];
    $loc = isset($map[$lang]) ? $map[$lang] : 'primary_default';
    if (has_nav_menu($loc)) return $loc;
    return has_nav_menu('primary_default') ? 'primary_default' : '';
}

function fl_h_tel($phone) {
    return preg_replace('/[^0-9\+]/', '', $phone);
}

function fl_h_fallback($args) {
    $c = !empty($args['menu_class']) ? $args['menu_class'] : 'menu';
    echo '<ul class="' . esc_attr($c) . '">';
    wp_list_pages(['title_li' => '']);
    echo '</ul>';
}

/* ═══════════════════════════════════════
   6. CUSTOM WALKER — Desktop Nav
   ═══════════════════════════════════════ */
class FL_Desktop_Walker extends Walker_Nav_Menu {

    function start_lvl(&$output, $depth = 0, $args = null) {
        $cols = 'fl-sub-1col';
        $output .= '<div class="fl-submenu ' . $cols . '">';
    }

    function end_lvl(&$output, $depth = 0, $args = null) {
        $output .= '</div>';
    }

    function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $classes   = empty($item->classes) ? [] : (array) $item->classes;
        $has_child = in_array('menu-item-has-children', $classes);
        $is_active = in_array('current-menu-item', $classes) || in_array('current-menu-ancestor', $classes);
        $url       = !empty($item->url) ? $item->url : '#';
        $title     = !empty($item->title) ? $item->title : '';

        if ($depth === 0) {
            if ($has_child) {
                $output .= '<div class="fl-drop" data-drop>';
                $output .= '<div class="fl-drop-trigger">';
                $output .= '<a class="fl-drop-link' . ($is_active ? ' active' : '') . '" href="' . esc_url($url) . '">' . esc_html($title) . '</a>';
                $output .= '<button class="fl-drop-arrow-btn" data-drop-toggle type="button" aria-label="' . esc_attr__('Toggle submenu', 'glassmorphism-child-pro') . '">';
                $output .= '<svg class="fl-nav-arr" viewBox="0 0 16 16"><path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>';
                $output .= '</button>';
                $output .= '</div>';
            } else {
                $output .= '<a class="fl-nav-link' . ($is_active ? ' active' : '') . '" href="' . esc_url($url) . '">' . esc_html($title) . '</a>';
            }
        } else {
            $output .= '<a class="fl-submenu-link" href="' . esc_url($url) . '">';
            $output .= '<span class="fl-submenu-dot"></span>';
            $output .= esc_html($title);
            $output .= '</a>';
        }
    }

    function end_el(&$output, $item, $depth = 0, $args = null) {
        $classes   = empty($item->classes) ? [] : (array) $item->classes;
        $has_child = in_array('menu-item-has-children', $classes);
        if ($depth === 0 && $has_child) {
            $output .= '</div>'; // .fl-drop
        }
    }
}

/* ═══════════════════════════════════════
   7. CUSTOM WALKER — Drawer Nav
   ═══════════════════════════════════════ */
class FL_Drawer_Walker extends Walker_Nav_Menu {

    function start_lvl(&$output, $depth = 0, $args = null) {
        $output .= '<div class="fl-drawer-acc-body"><div class="fl-drawer-subs">';
    }

    function end_lvl(&$output, $depth = 0, $args = null) {
        $output .= '</div></div>';
    }

    function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $classes   = empty($item->classes) ? [] : (array) $item->classes;
        $has_child = in_array('menu-item-has-children', $classes);
        $is_active = in_array('current-menu-item', $classes) || in_array('current-menu-ancestor', $classes);
        $url       = !empty($item->url) ? $item->url : '#';
        $title     = !empty($item->title) ? $item->title : '';

        if ($depth === 0) {
            if ($has_child) {
                $output .= '<div class="fl-drawer-acc">';
                $output .= '<div class="fl-drawer-acc-trigger">';
                $output .= '<a class="fl-drawer-acc-link' . ($is_active ? ' active' : '') . '" href="' . esc_url($url) . '">' . esc_html($title) . '</a>';
                $output .= '<span class="fl-drawer-acc-spacer"></span>';
                $output .= '<button class="fl-drawer-acc-toggle" type="button" aria-label="' . esc_attr__('Toggle', 'glassmorphism-child-pro') . '">';
                $output .= '<svg class="fl-drawer-acc-arr" viewBox="0 0 16 16"><path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>';
                $output .= '</button>';
                $output .= '</div>';
            } else {
                $output .= '<a class="fl-drawer-link' . ($is_active ? ' active' : '') . '" href="' . esc_url($url) . '">' . esc_html($title) . '</a>';
            }
        } else {
            $output .= '<a class="fl-drawer-sub" href="' . esc_url($url) . '">';
            $output .= '<span class="fl-drawer-sub-dot"></span>';
            $output .= esc_html($title);
            $output .= '</a>';
        }
    }

    function end_el(&$output, $item, $depth = 0, $args = null) {
        $classes   = empty($item->classes) ? [] : (array) $item->classes;
        $has_child = in_array('menu-item-has-children', $classes);
        if ($depth === 0 && $has_child) {
            $output .= '</div>'; // .fl-drawer-acc
        }
    }
}

/* ═══════════════════════════════════════
   8. LANGUAGE HELPERS
   ═══════════════════════════════════════ */
function fl_h_get_languages() {
    if (!function_exists('pll_the_languages')) return [];
    return pll_the_languages([
        'raw'           => 1,
        'hide_if_empty' => 0,
        'hide_current'  => 0,
    ]);
}

function fl_h_current_language_data() {
    $langs = fl_h_get_languages();
    if (empty($langs)) return null;
    foreach ($langs as $l) {
        if (!empty($l['current_lang'])) return $l;
    }
    return null;
}

/* ═══════════════════════════════════════
   9. SVG ICONS
   ═══════════════════════════════════════ */
function fl_h_icon($n = 'phone') {
    $i = [
        'phone'     => '<svg viewBox="0 0 24 24" fill="none"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.86 19.86 0 01-8.63-3.07 19.5 19.5 0 01-6-6A19.86 19.86 0 012.12 4.18 2 2 0 014.11 2h3a2 2 0 012 1.72c.12.9.34 1.78.65 2.62a2 2 0 01-.45 2.11L8.04 9.96a16 16 0 006 6l1.51-1.27a2 2 0 012.11-.45c.84.31 1.72.53 2.62.65A2 2 0 0122 16.92Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'email'     => '<svg viewBox="0 0 24 24" fill="none"><rect x="2" y="4" width="20" height="16" rx="3" stroke="currentColor" stroke-width="2"/><path d="m2 7 8.165 5.715a3 3 0 003.67 0L22 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'map'       => '<svg viewBox="0 0 24 24" fill="none"><path d="M12 21s7-4.35 7-11a7 7 0 10-14 0c0 6.65 7 11 7 11Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="10" r="2.5" stroke="currentColor" stroke-width="2"/></svg>',
        'close'     => '<svg viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'search'    => '<svg viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/><path d="m16 16 5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
        'globe'     => '<svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M2 12h20M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10A15.3 15.3 0 0112 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'instagram' => '<svg viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z" fill="currentColor"/></svg>',
        'telegram'  => '<svg viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 000 12a12 12 0 0012 12 12 12 0 0012-12A12 12 0 0012 0h-.056zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 01.171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.479.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z" fill="currentColor"/></svg>',
        'whatsapp'  => '<svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" fill="currentColor"/></svg>',
        'linkedin'  => '<svg viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" fill="currentColor"/></svg>',
        'check'     => '<svg viewBox="0 0 16 16"><path d="M13.5 4.5l-7 7L3 8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    ];
    return isset($i[$n]) ? $i[$n] : '';
}


/* ═══════════════════════════════════════
   10. BREADCRUMB — حذف شد (dead code)
   ═══════════════════════════════════════
   [FIX] کل توابع breadcrumb این فایل حذف شد چون:
   - منطق breadcrumb در inc/functions-breadcrumb.php پیاده شده
     (fl_bc_render, fl_bc_get_items, ...)
   - header.php از fl_bc_render() استفاده می‌کند، نه از این توابع
   - این توابع dead code بودند: fl_breadcrumb_texts, fl_get_breadcrumb_items,
     fl_render_breadcrumb_markup, fl_breadcrumb_shortcode,
     fl_auto_breadcrumb_in_content
   - shortcode [fl_breadcrumb] و hook fl_after_header در هیچ‌جای قالب
     استفاده نمی‌شدند
   ─────────────────────────────────────────
   اگر در آینده به shortcode breadcrumb نیاز داشتید، می‌توانید از این
   استفاده کنید (همان منطق fl_bc_render در یک shortcode wrapper):

   add_shortcode('fl_breadcrumb', function() {
       ob_start();
       if (function_exists('fl_bc_render')) fl_bc_render();
       return ob_get_clean();
   });
   ═══════════════════════════════════════ */
