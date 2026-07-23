<?php
/**
 * Page Template — Alborz Ghaleb Style
 * @package Alborz_Ghaleb
 */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();
?>

<?php do_action( 'glass_pro/before_main', 'page' ); ?>
<main id="main-content" class="gl-page-wrap">

    <?php while ( have_posts() ) : the_post();
        // [FIX] محاسبه تاریخ آخرین به‌روزرسانی
        $modified   = get_the_modified_time( 'U' );
        $published  = get_the_time( 'U' );
        $is_updated = ( $modified - $published ) > DAY_IN_SECONDS;
        // نمایش تصویر شاخص در همه برگه‌ها، به‌جز خانه و ترجمه‌های صفحهٔ خانه.
        $home_ids = array_filter( [ (int) get_option( 'page_on_front' ) ] );
        if ( function_exists( 'pll_get_post_translations' ) && ! empty( $home_ids[0] ) ) {
            $home_ids = array_merge( $home_ids, array_values( (array) pll_get_post_translations( $home_ids[0] ) ) );
        }
        $home_ids = array_unique( array_map( 'intval', $home_ids ) );
        $home_slugs = apply_filters( 'glass_pro/page_home_slugs', [ 'home', 'خانه' ] );
        $is_language_home = is_front_page()
            || in_array( (int) get_queried_object_id(), $home_ids, true )
            || is_page( $home_slugs );
        $has_thumb = has_post_thumbnail() && ! $is_language_home;
    ?>

    <!-- ── HERO CARD (Title + Featured Image) ── -->
    <div class="gl-page-hero-wrap">
        <article class="gl-page-hero-card <?php echo $has_thumb ? 'has-thumb' : 'no-thumb'; ?>">

            <?php if ( $has_thumb ) : ?>
            <!-- تصویر شاخص عریض در بالا -->
            <div class="gl-page-hero-thumb">
                <?php the_post_thumbnail( 'large', [ 'alt' => get_the_title() ] ); ?>
            </div>
            <?php endif; ?>

            <!-- عنوان زیر تصویر -->
            <div class="gl-page-hero-text">
                <h1 class="gl-page-title"><?php the_title(); ?></h1>
            </div>

        </article>
    </div>

    <!-- ── CONTENT WITH TOC ── -->
    <?php
    $toc_enabled = (bool) get_option( 'glass_pro_toc_enabled', true );
    $toc_on_pages = (bool) get_option( 'glass_pro_toc_on_pages', false );
    $show_toc    = $toc_enabled && $toc_on_pages;
    $show_toc    = apply_filters( 'glass_pro/toc/show_page', $show_toc, get_the_ID() );
    // [تغییر] عدم نمایش فهرست مطالب در صفحه اصلی (Home / خانه) و برگه تماس با ما
    // از همان قرارداد قالب برای شناسایی برگه تماس استفاده شد (inc/enqueue.php)
    // — شامل صفحه اصلی و برگه تماس هر زبان.
    // برای افزودن اسلاگ/آی‌دی برگه تماس دلخواه از فیلتر زیر استفاده کنید:
    //   add_filter( 'glass_pro/toc/contact_slugs', fn( $slugs ) => array_merge( $slugs, [ 'تماس-با-ما', 42 ] ) );
    $toc_contact_slugs = apply_filters( 'glass_pro/toc/contact_slugs', [ 'contact' ] );
    if ( is_front_page() || is_page( $toc_contact_slugs ) || is_page_template( 'page-contact.php' ) ) {
        $show_toc = false;
    }
    ?>
    <div class="gl-page-content-wrap">
        <div class="fl-sb-content-layout<?php echo $show_toc ? '' : ' fl-sb-no-toc'; ?>">
            <!-- فهرست مطالب (Sticky Sidebar) -->
            <?php if ( $show_toc ) : ?>
            <aside class="fl-sb-toc" id="flSbToc">
                <div class="fl-sb-toc-inner">
                    <div class="fl-sb-toc-header">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                        <span><?php echo esc_html( function_exists('glass_ui_blog_t') ? glass_ui_blog_t('toc') : 'فهرست مطالب' ); ?></span>
                    </div>
                    <ul class="fl-sb-toc-list" id="flSbTocList">
                        <!-- JS پر می‌کند -->
                    </ul>
                </div>
            </aside>
            <?php endif; ?>

            <!-- محتوای اصلی برگه -->
            <div class="gl-page-content-col">
                <article class="gl-page-card">
                    <div class="gl-page-content entry-content">
                        <?php the_content(); ?>
                    </div>

                    <?php
                    wp_link_pages( [
                        'before' => '<div class="gl-page-pagination">',
                        'after'  => '</div>',
                        'link_before' => '<span>',
                        'link_after'  => '</span>',
                    ] );
                    ?>

                    <!-- ── Last Update — bottom of content card ── -->
                    <div class="gl-page-meta">
                        <span class="gl-meta-item">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <?php if ( $is_updated ) : ?>
                                <?php echo esc_html( function_exists('glass_ui_blog_t') ? glass_ui_blog_t('last_updated') : __( 'آخرین به‌روزرسانی:', 'glassmorphism-child-pro' ) ); ?>
                                <time datetime="<?php echo esc_attr( get_the_modified_date( 'c' ) ); ?>">
                                    <?php echo esc_html( get_the_modified_date() ); ?>
                                </time>
                            <?php else : ?>
                                <?php echo esc_html( function_exists('glass_ui_blog_t') ? glass_ui_blog_t('published') : __( 'منتشر شده:', 'glassmorphism-child-pro' ) ); ?>
                                <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                                    <?php echo esc_html( get_the_date() ); ?>
                                </time>
                            <?php endif; ?>
                        </span>
                    </div>
                </article>
            </div>
        </div>
    </div>

    <?php
    // [FIX] لود بخش کامنت‌ها
    if ( comments_open() || get_comments_number() ) {
        comments_template();
    }
    ?>

    <?php endwhile; ?>

</main>
<?php do_action( 'glass_pro/after_main', 'page' ); ?>



<?php get_footer(); ?>
