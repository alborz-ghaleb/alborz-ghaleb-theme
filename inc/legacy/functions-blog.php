<?php
/**
 * Blog Archive — Alborz Ghaleb v6.7.0
 * Clean solid design, sidebar, category chips
 * Compatible with Polylang 6-language blog categories
 */

if (!defined('ABSPATH')) {
    exit;
}

/* ── Template Loader ── */
if (!function_exists('replywp_ultra_blog_template')) {
    function replywp_ultra_blog_template($template) {
        if (is_search()) {
            return $template;
        }
        if (!is_home() && !is_category() && !is_tag()) {
            return $template;
        }
        replywp_render_ultra_blog();
        exit;
    }
}
add_filter('template_include', 'replywp_ultra_blog_template', 999);

/* ── Search for all content types ── */
add_filter('pre_get_posts', function($query) {
    if (!is_admin() && $query->is_main_query() && $query->is_search()) {
        $query->set('post_type', array('post', 'page', 'portfolio'));
    }
    return $query;
});

/* ── Main Template ── */
if (!function_exists('replywp_render_ultra_blog')) {
function replywp_render_ultra_blog() {

get_header();

$page_title = __('وبلاگ', 'glassmorphism-child-pro');
if (is_category()) {
    $term = get_queried_object();
    if ($term && !is_wp_error($term)) { $page_title = $term->name; }
}
if (is_tag()) {
    $term = get_queried_object();
    if ($term && !is_wp_error($term)) { $page_title = __('برچسب:', 'glassmorphism-child-pro') . ' ' . $term->name; }
}
if (is_search()) {
    $page_title = __('نتایج جستجو برای:', 'glassmorphism-child-pro') . ' ' . get_search_query();
}

$total_posts = wp_count_posts('post');
$total = $total_posts ? (int)$total_posts->publish : 0;
$current_cat = get_query_var('cat');
?>


<div class="fl-archive">
    <div class="fl-arch-hero">
        <h1><?php echo esc_html($page_title); ?></h1>
    </div>

    <div class="fl-arch-layout">
        <div class="fl-arch-main">

            <?php if (have_posts()) : ?>
            <div class="fl-arch-grid">
            <?php while (have_posts()) : the_post(); ?>
            <article class="fl-arch-card">
                <div class="fl-arch-card-img">
                    <a href="<?php the_permalink(); ?>">
                        <?php if (has_post_thumbnail()) { the_post_thumbnail('medium_large', array('loading' => 'lazy')); } ?>
                    </a>
                </div>
                <div class="fl-arch-card-body">
                    <h3 class="fl-arch-card-title">
                        <a href="<?php the_permalink(); ?>"><?php echo esc_html(replywp_clean_plain_text(get_the_title())); ?></a>
                    </h3>
                    <p class="fl-arch-card-excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 18)); ?></p>
                    <?php
                    $fl_blog_title = function_exists('replywp_clean_plain_text') ? replywp_clean_plain_text(get_the_title()) : get_the_title();
                    $fl_blog_title_short = mb_substr($fl_blog_title, 0, 40);
                    ?>
                    <a href="<?php the_permalink(); ?>" class="fl-arch-card-more" aria-label="<?php echo esc_attr( sprintf( __( 'ادامه مطلب: %s', 'glassmorphism-child-pro' ), $fl_blog_title ) ); ?>" title="<?php echo esc_attr( $fl_blog_title ); ?>">
                        <?php echo esc_html( sprintf( __( 'ادامه مطلب: %s', 'glassmorphism-child-pro' ), $fl_blog_title_short ) ); ?>
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </article>
            <?php endwhile; ?>
            </div>

            <div class="fl-arch-pagination">
                <?php
                echo paginate_links(array(
                    'prev_text' => '→',
                    'next_text' => '←',
                    'mid_size'  => 2,
                    'prev_next' => true,
                ));
                ?>
            </div>

            <?php else : ?>
            <div class="fl-arch-empty">
                <?php
                if (is_search()) {
                    printf(__('نتیجه‌ای برای جستجوی "%s" یافت نشد.', 'glassmorphism-child-pro'), esc_html(get_search_query()));
                } elseif (is_tag()) {
                    esc_html_e('هیچ پستی با این برچسب یافت نشد.', 'glassmorphism-child-pro');
                } else {
                    esc_html_e('هیچ مقاله‌ای یافت نشد.', 'glassmorphism-child-pro');
                }
                ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <aside class="fl-arch-sidebar">
            <div class="fl-arch-widget">
                <h4 class="fl-arch-widget-title"><?php esc_html_e('دسته‌بندی‌ها', 'glassmorphism-child-pro'); ?></h4>
                <ul class="fl-arch-widget-cats">
                    <?php
                    $sidebar_cats = get_categories(array('hide_empty' => true, 'parent' => 0));
                    foreach ($sidebar_cats as $scat) :
                    ?>
                    <li<?php echo ($current_cat == $scat->term_id) ? ' class="current-cat"' : ''; ?>>
                        <a href="<?php echo esc_url(get_category_link($scat->term_id)); ?>">
                            <?php echo esc_html($scat->name); ?>
                            <span class="cat-count"><?php echo (int)$scat->count; ?></span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <?php
            // Popular posts widget — نتیجهٔ مرتب‌سازی متا یک ساعت cache می‌شود.
            $popular_args = [
                'post_type'              => 'post',
                'post_status'            => 'publish',
                'posts_per_page'         => 5,
                'meta_key'               => 'post_views_count',
                'orderby'                => 'meta_value_num',
                'order'                  => 'DESC',
                'ignore_sticky_posts'    => true,
                'no_found_rows'          => true,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
                'suppress_filters'       => false,
            ];

            // Polylang: هر زبان فقط پربازدیدترین نوشته‌های همان زبان را می‌بیند.
            $popular_lang = '';
            if ( function_exists( 'pll_current_language' ) ) {
                $popular_lang = (string) pll_current_language( 'slug' );
                if ( '' !== $popular_lang ) {
                    $popular_args['lang'] = $popular_lang;
                }
            }
            if ( '' === $popular_lang ) {
                $popular_lang = strtolower( substr( (string) get_locale(), 0, 2 ) );
            }
            $popular_cache_group = 'popular_posts_' . sanitize_key( $popular_lang ?: 'default' );

            $popular_posts = function_exists( 'glass_pro_cached_query' )
                ? glass_pro_cached_query( $popular_args, $popular_cache_group, HOUR_IN_SECONDS )
                : get_posts( $popular_args );
            if ( ! empty( $popular_posts ) ) :
                global $post;
            ?>
            <div class="fl-arch-widget">
                <h4 class="fl-arch-widget-title"><?php esc_html_e('محبوب‌ترین مقالات', 'glassmorphism-child-pro'); ?></h4>
                <ul class="fl-arch-popular">
                    <?php foreach ( $popular_posts as $post ) : setup_postdata( $post ); ?>
                    <li>
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        <div class="fl-arch-popular-date"><?php echo get_the_date(); ?></div>
                    </li>
                    <?php endforeach; wp_reset_postdata(); ?>
                </ul>
            </div>
            <?php endif; ?>
        </aside>
    </div>
</div>

<?php
get_footer();
}
}
