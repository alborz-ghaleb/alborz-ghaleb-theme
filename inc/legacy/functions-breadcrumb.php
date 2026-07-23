<?php
/**
 * Flavor Premium Breadcrumb
 * نسخه نهایی - تک چاپ - بدون HTML در عنوان
 */

if (!defined('ABSPATH')) {
    exit;
}

/* ═══════════════════════════════════════
   جلوگیری از دوبار هوک شدن
═══════════════════════════════════════ */

remove_action('wp_body_open', 'fl_bc_output_after_header', 20);

// Backward-compatible no-op: CSS اکنون از assets/css/breadcrumb.css لود می‌شود.
function fl_bc_inline_css() {}

/* ═══════════════════════════════════════
   2. LANGUAGE
═══════════════════════════════════════ */

function fl_bc_get_lang() {

    if (function_exists('pll_current_language')) {
        $lang = pll_current_language('slug');

        if (!empty($lang)) {
            return $lang;
        }
    }

    return strtolower(substr(get_locale(), 0, 2));
}

/* ═══════════════════════════════════════
   RTL CHECK
═══════════════════════════════════════ */

function fl_bc_is_rtl() {
    return in_array(
        fl_bc_get_lang(),
        array('fa','ar'),
        true
    );
}

/* ═══════════════════════════════════════
   CLEAN TITLES
═══════════════════════════════════════ */

function fl_bc_clean_title($title) {

    if (empty($title)) {
        return '';
    }

    $title = html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $title = strip_shortcodes($title);
    $title = wp_strip_all_tags($title, true);
    $title = preg_replace('/<[^>]*>/', '', $title);
    $title = preg_replace('/&lt;[^&gt;]*&gt;/', '', $title);
    $title = trim($title);

    return $title;
}

/* ═══════════════════════════════════════
   3. ITEMS
═══════════════════════════════════════ */

function fl_bc_get_items() {

    if (is_front_page() || is_home()) {
        return array();
    }

    $lang = fl_bc_get_lang();

    $home_texts = array(
        'fa' => 'خانه',
        'en' => 'Home',
        'ar' => 'الرئيسية',
        'tr' => 'Ana Sayfa',
        'ru' => 'Главная',
        'hy' => 'Գլխավոր',
    );

    $search_texts = array(
        'fa' => 'جستجو',
        'en' => 'Search',
        'ar' => 'بحث',
        'tr' => 'Arama',
        'ru' => 'Поиск',
        'hy' => 'Որոնում',
    );

    $home_title   = $home_texts[$lang] ?? 'Home';
    $search_title = $search_texts[$lang] ?? 'Search';

    $items = array();

    $items[] = array(
        'title' => fl_bc_clean_title($home_title),
        'url'   => home_url('/'),
        'home'  => true
    );

    if (is_singular()) {

        $post = get_queried_object();

        if (is_single() && get_post_type() === 'post') {
            $cats = get_the_category($post->ID);

            if (!empty($cats)) {
                $items[] = array(
                    'title' => fl_bc_clean_title($cats[0]->name),
                    'url'   => get_category_link($cats[0]->term_id)
                );
            }
        }

        if (is_page() && !empty($post->post_parent)) {
            $ancestors = array_reverse(get_post_ancestors($post->ID));

            foreach ($ancestors as $ancestor_id) {
                $items[] = array(
                    'title' => fl_bc_clean_title(get_the_title($ancestor_id)),
                    'url'   => get_permalink($ancestor_id)
                );
            }
        }

        $post_type = get_post_type($post);

        if ($post_type && !in_array($post_type, array('post','page'), true)) {
            $pt_obj = get_post_type_object($post_type);

            if ($pt_obj && !empty($pt_obj->has_archive)) {
                $items[] = array(
                    'title' => fl_bc_clean_title($pt_obj->labels->name),
                    'url'   => get_post_type_archive_link($post_type)
                );
            }
        }

        $items[] = array(
            'title' => fl_bc_clean_title(get_the_title($post->ID)),
            'url'   => ''
        );
    }

    elseif (is_category()) {

        $cat = get_queried_object();

        if (!empty($cat->parent)) {
            $parent = get_category($cat->parent);

            if ($parent && !is_wp_error($parent)) {
                $items[] = array(
                    'title' => fl_bc_clean_title($parent->name),
                    'url'   => get_category_link($parent->term_id)
                );
            }
        }

        $items[] = array(
            'title' => fl_bc_clean_title(single_cat_title('', false)),
            'url'   => ''
        );
    }

    elseif (is_tag()) {

        $items[] = array(
            'title' => fl_bc_clean_title(single_tag_title('', false)),
            'url'   => ''
        );
    }

    elseif (is_tax()) {

        $term = get_queried_object();

        $items[] = array(
            'title' => fl_bc_clean_title($term->name),
            'url'   => ''
        );
    }

    elseif (is_post_type_archive()) {

        $items[] = array(
            'title' => fl_bc_clean_title(post_type_archive_title('', false)),
            'url'   => ''
        );
    }

    elseif (is_archive()) {

        $items[] = array(
            'title' => fl_bc_clean_title(get_the_archive_title()),
            'url'   => ''
        );
    }

    elseif (is_search()) {

        $items[] = array(
            'title' => fl_bc_clean_title($search_title . ': ' . get_search_query()),
            'url'   => ''
        );
    }

    elseif (is_404()) {

        $items[] = array(
            'title' => '404',
            'url'   => ''
        );
    }

    return $items;
}

/* ═══════════════════════════════════════
   4. RENDER
═══════════════════════════════════════ */

function fl_bc_render() {

    static $rendered = false;

    if ($rendered) {
        return;
    }

    $items = fl_bc_get_items();

    if (empty($items)) {
        return;
    }

    $rendered = true;

    $is_rtl = fl_bc_is_rtl();

    echo '<div class="fl-breadcrumb-wrap" id="fl-custom-breadcrumb">';
    echo '<nav class="fl-breadcrumb" aria-label="Breadcrumb">';
    echo '<ol class="fl-breadcrumb-inner" itemscope itemtype="https://schema.org/BreadcrumbList">';

    $total = count($items);

    foreach ($items as $i => $item) {

        $last = ($i === $total - 1);

        echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';

        if (!$last && !empty($item['url'])) {

            $classes = 'fl-bc-item';

            if (!empty($item['home'])) {
                $classes .= ' fl-bc-home';
            }

            echo '<a href="' . esc_url($item['url']) . '" class="' . esc_attr($classes) . '" itemprop="item">';

            if (!empty($item['home'])) {
                echo '<svg viewBox="0 0 24 24">';
                echo '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke-linecap="round" stroke-linejoin="round"/>';
                echo '<polyline points="9 22 9 12 15 12 15 22" stroke-linecap="round" stroke-linejoin="round"/>';
                echo '</svg>';
            }

            echo '<span itemprop="name">';
            echo esc_html($item['title']);
            echo '</span>';

            echo '</a>';

        } else {

            echo '<span class="fl-bc-item fl-bc-current" itemprop="name">';
            echo esc_html($item['title']);
            echo '</span>';
        }

        echo '<meta itemprop="position" content="' . esc_attr( $i + 1 ) . '">';
        echo '</li>';

        if (!$last) {
            echo '<li class="fl-bc-sep" aria-hidden="true">';
            echo '<svg viewBox="0 0 24 24">';

            if ($is_rtl) {
                echo '<polyline points="15 18 9 12 15 6" stroke-linecap="round" stroke-linejoin="round"/>';
            } else {
                echo '<polyline points="9 18 15 12 9 6" stroke-linecap="round" stroke-linejoin="round"/>';
            }

            echo '</svg>';
            echo '</li>';
        }
    }

    echo '</ol>';
    echo '</nav>';
    echo '</div>';
}

/* ═══════════════════════════════════════
   5. OUTPUT
═══════════════════════════════════════ */

// [FIX v3.2] breadcrumb مستقیم در header.php بعد از spacer رندر می‌شه
// add_action('wp_body_open', 'fl_bc_output_after_header', 20);

function fl_bc_output_after_header() {

    static $printed = false;

    if ($printed) {
        return;
    }

    if (is_front_page() || is_home()) {
        return;
    }

    $printed = true;
    fl_bc_render();
}