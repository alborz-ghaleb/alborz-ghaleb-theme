<?php
/**
 * Single Portfolio Template
 * صفحه تکی دست دوم - گلس‌مورفیسم - سایدبار شهر - فرم تماس - شمارنده بازدید - گالری
 *
 * @package developer-jeison
 * @version 3.0.0
 */

if (!defined('ABSPATH')) exit;

/* ════════════════════════════════════════════════════════════════
   [FIX v3.2] فقط منطق header-critical (cookie) قبل از get_header().
   ─────────────────────────────────────────────────────────────────
   قبلاً کل loop داخل while قرار داشت و get_header() در میانه‌ی while
   صدا زده می‌شد. این الگو SEO plugin‌ها و the_post() hook ها را به‌هم
   می‌ریخت. الان:
   1. ابتدا cookie و counter را قبل از header می‌نویسیم (چون باید
      روی headers HTTP تأثیر بگذارد).
   2. سپس get_header().
   3. سپس loop استاندارد while/endwhile که داده‌ها را آماده و markup
      را چاپ می‌کند.
   ════════════════════════════════════════════════════════════════ */

if ( have_posts() ) {
    $pf_post_id    = (int) get_queried_object_id();
    $pf_views_key  = 'portfolio_views';

    // جلوگیری از شمارش مدیر / AJAX / REST / bots
    $pf_should_count = true;
    if ( is_user_logged_in() && current_user_can('edit_posts') ) {
        $pf_should_count = false;
    }
    if ( defined('DOING_AJAX') && DOING_AJAX ) {
        $pf_should_count = false;
    }
    if ( defined('REST_REQUEST') && REST_REQUEST ) {
        $pf_should_count = false;
    }
    // [v5.0.6] Bot detection
    if ( function_exists( 'glass_pro_is_bot' ) && glass_pro_is_bot() ) {
        $pf_should_count = false;
    }

    // کوکی برای جلوگیری از شمارش تکراری
    $pf_cookie_name = 'pf_viewed_' . $pf_post_id;
    if ( $pf_should_count && ! isset($_COOKIE[$pf_cookie_name]) ) {
        $pf_views = (int) get_post_meta($pf_post_id, $pf_views_key, true) + 1;
        update_post_meta($pf_post_id, $pf_views_key, $pf_views);

        // setcookie ایمن: SameSite=Lax + Secure روی HTTPS + HttpOnly (PHP 7.3+ option array)
        if ( ! headers_sent() ) {
            setcookie( $pf_cookie_name, '1', [
                'expires'  => time() + 86400,
                'path'     => '/',
                'domain'   => '',
                'secure'   => is_ssl(),
                'httponly' => true,
                'samesite' => 'Lax',
            ] );
        }
    }
}

get_header();

while (have_posts()) : the_post();

    $post_id   = get_the_ID();
    $title     = function_exists('replywp_clean_plain_text')
                 ? replywp_clean_plain_text(get_the_title())
                 : get_the_title();
    $thumbnail = get_post_thumbnail_id();

    // ═══ گالری تصاویر ═══
    $all_images = [];

    // 1. تصویر شاخص
    if ($thumbnail) {
        $all_images[] = [
            'full'  => wp_get_attachment_image_url($thumbnail, 'large'),
            'thumb' => wp_get_attachment_image_url($thumbnail, 'medium'),
        ];
    }

    // 2. گالری ACF
    if (function_exists('get_field')) {
        $acf_gallery = get_field('gallery', $post_id);
        if (!empty($acf_gallery) && is_array($acf_gallery)) {
            foreach ($acf_gallery as $img) {
                $img_id = is_array($img) ? $img['ID'] : $img;
                $full   = wp_get_attachment_image_url($img_id, 'large');
                $thumb  = wp_get_attachment_image_url($img_id, 'medium');
                if ($full) {
                    $all_images[] = ['full' => $full, 'thumb' => $thumb];
                }
            }
        }
    }

    // 3. تصاویر داخل محتوا (fallback)
    if (count($all_images) <= 1) {
        $attached = get_attached_media('image', $post_id);
        if (!empty($attached)) {
            foreach ($attached as $att) {
                if ($att->ID == $thumbnail) continue;
                $full  = wp_get_attachment_image_url($att->ID, 'large');
                $thumb = wp_get_attachment_image_url($att->ID, 'medium');
                if ($full) {
                    $all_images[] = ['full' => $full, 'thumb' => $thumb];
                }
            }
        }
    }

    // تکسونومی‌ها
    $cities     = get_the_terms($post_id, 'portfolio_city');
    $categories = get_the_terms($post_id, 'themsah_theme_type');
    $tags       = get_the_terms($post_id, 'post_tag');

    $city_name = '';
    $city_link = '';
    if (!empty($cities) && !is_wp_error($cities)) {
        $city_name = $cities[0]->name;
        $city_link = get_term_link($cities[0]);
    } else {
        // [جدید] آگهی‌های جدید: شهر به‌صورت متای ساده ذخیره می‌شود (بدون لینک تاکسونومی)
        $city_meta = get_post_meta($post_id, 'portfolio_city', true);
        if ( ! empty( $city_meta ) ) {
            $city_name = $city_meta;
            $city_link = '';
        }
    }

    $cat_name = '';
    $cat_link = '';
    if (!empty($categories) && !is_wp_error($categories)) {
        $cat_name = $categories[0]->name;
        $cat_link = get_term_link($categories[0]);
    }

    $city_button_text = $city_name
        ? sprintf( esc_html__( 'شهر: %s', 'glassmorphism-child-pro' ), $city_name )
        : ( function_exists('glass_ui_t') ? glass_ui_t('choose_city') : esc_html__( 'انتخاب شهر', 'glassmorphism-child-pro' ) );

    // ═══ شمارنده بازدید — فقط خواندن (نوشتن قبل از header انجام شد) ═══
    $views = (int) get_post_meta($post_id, 'portfolio_views', true);

    $ad_price = absint( get_post_meta( $post_id, 'portfolio_price', true ) );
    $ad_old_price = absint( get_post_meta( $post_id, 'portfolio_old_price', true ) );
    $ad_discount = min( 99, absint( get_post_meta( $post_id, 'portfolio_discount_percent', true ) ) );
    $ad_featured_until = (int) get_post_meta( $post_id, 'portfolio_featured_until', true );
    $ad_is_featured = $ad_featured_until > time();

    // محصولات مرتبط
    $related_args = [
        'post_type'      => 'portfolio',
        'posts_per_page' => 6, // [FIX] 6 پست تا دسکتاپ 2x3 و موبایل 3x2 نمایش بشه
        'post__not_in'        => [ $post_id ],
        'post_status'         => 'publish',
        'orderby'             => 'date', // سریع‌تر و قابل cache؛ بدون ORDER BY RAND()
        'order'               => 'DESC',
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    ];
    if (!empty($cities) && !is_wp_error($cities)) {
        $related_args['tax_query'] = [[
            'taxonomy' => 'portfolio_city',
            'field'    => 'term_id',
            'terms'    => $cities[0]->term_id,
        ]];
    } elseif (!empty($categories) && !is_wp_error($categories)) {
        $related_args['tax_query'] = [[
            'taxonomy' => 'themsah_theme_type',
            'field'    => 'term_id',
            'terms'    => $categories[0]->term_id,
        ]];
    }
    $related = new WP_Query($related_args);

    // تماس
    $phone    = get_theme_mod('fl_footer_phone', '');
    $is_user_submitted = ( get_post_meta($post_id, 'user_submitted', true) === 'yes' );

    // [جدید] وضعیت آگهی: active | sold | expired (سازگار با پنل کاربری)
    $ad_state = function_exists('glass_get_ad_state') ? glass_get_ad_state($post_id) : 'active';
    $is_expired = ( $ad_state === 'expired' );
    $is_sold    = ( $ad_state === 'sold' );

    // دریافت شماره تماس آگهی‌دهنده.
    // چون فیلتر فرانت‌اند شماره را برای آگهی فروخته/منقضی خالی می‌کند،
    // اینجا فقط در حالت «فعال» شماره را می‌خوانیم.
    if ( $ad_state === 'active' ) {
        // حذف موقت فیلتر برای خواندن مقدار خام، سپس بازگرداندن
        if ( function_exists('glass_filter_sold_phone') ) {
            remove_filter('get_post_metadata', 'glass_filter_sold_phone', 10);
            $custom_phone = get_post_meta($post_id, 'portfolio_phone', true);
            add_filter('get_post_metadata', 'glass_filter_sold_phone', 10, 4);
        } else {
            $custom_phone = get_post_meta($post_id, 'portfolio_phone', true);
        }
    } else {
        $custom_phone = '';
    }

    $wa_number = glass_pro_contact( 'whatsapp_number' ); // پیش‌فرض از Customizer
    $wa_is_catalog = true;
    if ( !empty($custom_phone) ) {
        $clean_phone = preg_replace('/[^0-9]/', '', $custom_phone);
        if ( substr($clean_phone, 0, 2) === '09' ) {
            $wa_number = '98' . substr($clean_phone, 1);
            $wa_is_catalog = false;
        } elseif ( substr($clean_phone, 0, 3) === '989' ) {
            $wa_number = $clean_phone;
            $wa_is_catalog = false;
        }
    }
    $wa_url = $wa_is_catalog ? "https://wa.me/c/" . $wa_number : "https://wa.me/" . $wa_number;

    $whatsapp = get_theme_mod('fl_fab_whatsapp', '');
    $email    = get_theme_mod('fl_footer_email', '');
?>

<!-- انتقال تصاویر گالری به جاوا اسکریپت به صورت کاملا استاندارد -->
<script <?php if ( function_exists( 'glass_pro_csp_nonce_attr' ) ) { glass_pro_csp_nonce_attr(); } ?>>
    window.glassSinglePortfolioImages = <?php echo wp_json_encode( array_map( static function( $img ) { return $img['full']; }, $all_images ) ); ?>;
</script>

<!-- ═══ CATEGORIES SLIDER (زیر هدر) ═══ -->
<?php
/**
 * فیلتر glass_pro/portfolio/categories_slider — اجازه‌ی override کامل آرایه‌ی
 * محصولات این بخش. برای غیرفعال کردن این بخش در Customizer از
 * glass_pro_portfolio_cats_enabled استفاده کنید.
 *
 * هر آیتم باید فرمت زیر را داشته باشد:
 *   [ 'url' => '/path/', 'img' => '/path/to/img.jpg', 'name' => 'نام' ]
 */
$fl_sp_default_cats = function_exists( 'glass_ui_localized_page_item' ) ? [
    glass_ui_localized_page_item( 'قالب-بتن-مدولار',     '/wp-content/uploads/2021/04/Modular-formwork-1-300x200.jpeg', 'slider_concrete',  __( 'قالب بتن', 'glassmorphism-child-pro' ) ),
    glass_ui_localized_page_item( 'انواع-داربست-مدولار', '/wp-content/uploads/2022/10/3-2-768x768-1-300x300.jpg',       'slider_scaffold',  __( 'داربست مدولار', 'glassmorphism-child-pro' ) ),
    glass_ui_localized_page_item( 'جک-سقفی-جک-صلیبی',    '/wp-content/uploads/2023/08/photo_2023-08-05_11-57-10-300x225.jpg', 'slider_jack', __( 'جک سقفی', 'glassmorphism-child-pro' ) ),
    glass_ui_localized_page_item( 'قیچی-میلگرد',         '/wp-content/uploads/2023/11/تعمیر-قیچی-میلگرد-300x225.jpg',  'slider_machinery', __( 'ماشین آلات', 'glassmorphism-child-pro' ) ),
    glass_ui_localized_page_item( 'تجهیزات-قالب-بندی',   '/wp-content/uploads/2020/11/pingove.png',                    'slider_equipment', __( 'تجهیزات قالب بندی', 'glassmorphism-child-pro' ) ),
    glass_ui_localized_page_item( 'قیمت-والپست',         '/wp-content/uploads/2026/04/بارگیری-و-ارسال-سفارش-والپست-برای-یکی-از-پروژه-های-ساختمانی-در-تهران-278x300.jpg', 'slider_wallpost', __( 'والپست', 'glassmorphism-child-pro' ) ),
] : [
    [ 'url' => '/قالب-بتن-مدولار/', 'img' => '/wp-content/uploads/2021/04/Modular-formwork-1-300x200.jpeg', 'name' => __( 'قالب بتن', 'glassmorphism-child-pro' ) ],
    [ 'url' => '/انواع-داربست-مدولار/', 'img' => '/wp-content/uploads/2022/10/3-2-768x768-1-300x300.jpg', 'name' => __( 'داربست مدولار', 'glassmorphism-child-pro' ) ],
    [ 'url' => '/جک-سقفی-جک-صلیبی/', 'img' => '/wp-content/uploads/2023/08/photo_2023-08-05_11-57-10-300x225.jpg', 'name' => __( 'جک سقفی', 'glassmorphism-child-pro' ) ],
    [ 'url' => '/قیچی-میلگرد/', 'img' => '/wp-content/uploads/2023/11/تعمیر-قیچی-میلگرد-300x225.jpg', 'name' => __( 'ماشین آلات', 'glassmorphism-child-pro' ) ],
    [ 'url' => '/تجهیزات-قالب-بندی/', 'img' => '/wp-content/uploads/2020/11/pingove.png', 'name' => __( 'تجهیزات قالب بندی', 'glassmorphism-child-pro' ) ],
    [ 'url' => '/قیمت-والپست/', 'img' => '/wp-content/uploads/2026/04/بارگیری-و-ارسال-سفارش-والپست-برای-یکی-از-پروژه-های-ساختمانی-در-تهران-278x300.jpg', 'name' => __( 'والپست', 'glassmorphism-child-pro' ) ],
];
$fl_sp_cats        = apply_filters( 'glass_pro/portfolio/categories_slider', $fl_sp_default_cats );
$fl_sp_cats_enabled = (bool) get_theme_mod( 'glass_pro_portfolio_cats_enabled', true );

if ( $fl_sp_cats_enabled && ! empty( $fl_sp_cats ) ) : ?>
<div class="fl-sp-cats-outer">
    <div class="fl-sp-cats-wrap">
        <div class="fl-sp-cats-track" id="flSpCatsTrack">
            <?php foreach ( $fl_sp_cats as $fl_sp_cat ) : ?>
                <a href="<?php echo esc_url( $fl_sp_cat['url'] ); ?>" class="fl-sp-cat-card">
                    <img src="<?php echo esc_url( $fl_sp_cat['img'] ); ?>" alt="<?php echo esc_attr( $fl_sp_cat['name'] ); ?>" loading="lazy" decoding="async" width="300" height="200">
                    <span class="fl-sp-cat-name"><?php echo esc_html( $fl_sp_cat['name'] ); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Breadcrumb hidden but SEO kept -->
<div style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap">
<?php
if (function_exists('fl_bc_render')) {
    fl_bc_render();
} elseif (function_exists('fl_render_breadcrumb_markup')) {
    fl_render_breadcrumb_markup();
}
?>
</div>

<div class="fl-sp-page">

    <!-- ═══ SIDEBAR ═══ -->
    <aside class="fl-sp-sidebar">
        <div class="fl-sp-panel">
            <div class="fl-sp-sidebar-head">
                <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="10" r="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span><?php echo esc_html( function_exists('glass_ui_t') ? glass_ui_t('choose_city') : __( 'انتخاب شهر', 'glassmorphism-child-pro' ) ); ?></span>
            </div>
            <?php if (function_exists('replywp_render_city_menu')) { replywp_render_city_menu('fl-sp-city-list'); } ?>
        </div>

        <!-- کارت تبلیغاتی ثبت آگهی جدید در سایدبار -->
        <div class="fl-sp-panel" style="margin-top: 20px; padding: 20px; text-align: center;">
            <div style="font-weight: 700; font-size: 1rem; margin-bottom: 8px; color: var(--fl-text);"><?php echo esc_html( function_exists('glass_ui_extra_t') ? glass_ui_extra_t('have_used_equipment') : __( 'قصد فروش تجهیزات خود را دارید؟', 'glassmorphism-child-pro' ) ); ?></div>
            <p style="font-size: 0.8rem; color: var(--fl-text-light); margin-bottom: 16px; line-height: 1.5;"><?php echo esc_html( function_exists('glass_ui_extra_t') ? glass_ui_extra_t('submit_free_desc') : __( 'آگهی خود را رایگان ثبت کنید تا هزاران خریدار آن را ببینند.', 'glassmorphism-child-pro' ) ); ?></p>
            <?php
            if ( function_exists( 'glass_action_buttons' ) ) {
                echo glass_action_buttons( 'block' );
            } else {
                echo '<a href="' . esc_url( ( function_exists('glass_get_submit_url') ? glass_get_submit_url() : home_url('/ثبت-آگهی/') ) ) . '" class="glass-btn-submit" style="display:inline-flex;text-decoration:none;width:100%;justify-content:center;align-items:center;gap:8px;padding:12px;border-radius:10px;background:var(--fl-primary);color:#fff;font-weight:400;font-size:0.88rem;">' . esc_html( function_exists('glass_ui_extra_t') ? glass_ui_extra_t('free_submit') : __( 'ثبت آگهی رایگان', 'glassmorphism-child-pro' ) ) . '</a>';
            }
            ?>
        </div>
    </aside>

    <!-- ═══ MAIN ═══ -->
    <main id="main-content" class="fl-sp-main">
        <h1 class="fl-sp-title fl-sp-title--desktop"><?php echo esc_html($title); ?></h1>

        <?php if ( $is_expired ) : ?>
            <!-- بنر هشدار انقضای آگهی -->
            <div style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.25); color: #ef4444; border-radius: 16px; padding: 14px 20px; margin-bottom: 24px; font-weight: bold; display: flex; align-items: center; gap: 12px; font-size: 0.88rem; direction: rtl; line-height: 1.6;">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" style="flex-shrink: 0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?php echo esc_html( function_exists('glass_ui_t') ? glass_ui_t('expired_banner') : ( function_exists('glass_t') ? glass_t('sp_expired_banner') : 'این آگهی منقضی شده است.' ) ); ?>
            </div>
        <?php endif; ?>

        <div class="fl-sp-content-area">

            <!-- ═══ GALLERY ═══ -->
            <div class="fl-sp-glass fl-sp-gallery-wrap">
                <?php if (!empty($all_images)) : ?>
                    <div class="fl-sp-gallery-main" id="flSpGalleryMain" onclick="flSpOpenLightbox(0)">
                        <img id="flSpMainImage" src="<?php echo esc_url($all_images[0]['full']); ?>" alt="<?php echo esc_attr($title); ?>" fetchpriority="high" decoding="async" width="800" height="600">

                        <!-- دکمه شهر: بالا چپ - هم موبایل هم دسکتاپ -->
                        <button class="fl-sp-city-overlay-btn" onclick="event.stopPropagation(); flSpOpenCityDrawer();">
                            <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="10" r="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <?php echo esc_html($city_button_text); ?>
                            <svg class="fl-sp-city-arr" viewBox="0 0 16 16"><path d="M4 6l4 4 4-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>

                        <!-- عنوان روی تصویر (فقط موبایل) -->
                        <div class="fl-sp-mob-overlay">
                            <p class="fl-sp-mob-title" style="font-weight:700;"><?php echo esc_html($title); ?></p>
                        </div>

                        <?php if ($city_name) : ?>
                            <span class="fl-sp-gallery-badge">
                                <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="10" r="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <?php echo esc_html($city_name); ?>
                            </span>
                        <?php endif; ?>
                        <?php if (count($all_images) > 1) : ?>
                            <span class="fl-sp-gallery-counter">
                                <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" ry="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="8.5" cy="8.5" r="1.5" stroke-linecap="round" stroke-linejoin="round"/><polyline points="21 15 16 10 5 21" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <span id="flSpCounterText">1 / <?php echo count($all_images); ?></span>
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if (count($all_images) > 1) : ?>
                        <div class="fl-sp-gallery-thumbs">
                            <?php foreach ($all_images as $i => $img) : ?>
                                <div class="fl-sp-gallery-thumb <?php echo $i === 0 ? 'active' : ''; ?>"
                                     data-index="<?php echo $i; ?>"
                                     data-full="<?php echo esc_url($img['full']); ?>"
                                     onclick="flSpSelectImage(this, <?php echo $i; ?>)">
                                    <img src="<?php echo esc_url($img['thumb']); ?>" alt="<?php echo esc_attr( sprintf( __( 'تصویر %d', 'glassmorphism-child-pro' ), $i + 1 ) ); ?>" loading="lazy">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php else : ?>
                    <div class="fl-sp-no-thumb"><span><?php esc_html_e( 'بدون تصویر', 'glassmorphism-child-pro' ); ?></span></div>
                <?php endif; ?>
            </div>

            <!-- دکمه ثبت آگهی جدید زیر اسلایدشو -->
            <div class="fl-sp-glass fl-sp-submit-wrap" style="margin-bottom: 20px; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; gap: 15px; flex-wrap: wrap; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
                <style>
                    @media (max-width: 768px) {
                        .fl-sp-submit-texts { display: none !important; }
                        .fl-sp-submit-wrap { justify-content: center !important; padding: 12px 16px !important; }
                        .fl-sp-submit-wrap a { width: 100% !important; justify-content: center !important; }
                    }
                </style>
                <div class="fl-sp-submit-texts" style="text-align: right;">
                    <h3 style="margin: 0 0 4px 0; font-size: 0.95rem; font-weight: 700; color: var(--fl-text);"><?php echo esc_html( function_exists('glass_ui_extra_t') ? glass_ui_extra_t('have_used_equipment') : __( 'آیا تجهیزات دست‌دوم برای فروش دارید؟', 'glassmorphism-child-pro' ) ); ?></h3>
                    <p style="margin: 0; font-size: 0.78rem; color: var(--fl-text-light);"><?php echo esc_html( function_exists('glass_ui_extra_t') ? glass_ui_extra_t('submit_free_desc') : __( 'آگهی خود را به صورت رایگان ثبت کنید تا هزاران خریدار آن را ببینند.', 'glassmorphism-child-pro' ) ); ?></p>
                </div>
                <?php
                if ( function_exists( 'glass_action_buttons' ) ) {
                    echo glass_action_buttons( 'compact' );
                } else {
                    echo '<a href="' . esc_url( ( function_exists('glass_get_submit_url') ? glass_get_submit_url() : home_url('/ثبت-آگهی/') ) ) . '" class="glass-btn-submit" style="text-decoration:none;padding:10px 18px;border-radius:10px;background:var(--fl-primary);color:#fff;font-weight:400;font-size:0.84rem;display:inline-flex;align-items:center;gap:6px;">' . esc_html( function_exists('glass_ui_extra_t') ? glass_ui_extra_t('submit_used_ad') : __( 'ثبت آگهی دست دوم', 'glassmorphism-child-pro' ) ) . '</a>';
                }
                ?>
            </div>

            <!-- ═══ CONTENT ═══ -->
            <div class="fl-sp-glass">
                <div class="fl-sp-header">
                    <div class="fl-sp-meta">
                        <?php if ($city_name && !is_wp_error($city_link)) : ?>
                            <a href="<?php echo esc_url($city_link); ?>" class="fl-sp-meta-item">
                                <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                <?php echo esc_html($city_name); ?>
                            </a>
                        <?php endif; ?>
                        <?php if ($cat_name && !is_wp_error($cat_link)) : ?>
                            <a href="<?php echo esc_url($cat_link); ?>" class="fl-sp-meta-item">
                                <svg viewBox="0 0 24 24"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                                <?php echo esc_html($cat_name); ?>
                            </a>
                        <?php endif; ?>
                        <span class="fl-sp-meta-item">
                            <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <?php
                            /* translators: %s: تعداد بازدید با اعداد محلی */
                            printf( esc_html__( '%s بازدید', 'glassmorphism-child-pro' ), esc_html( number_format_i18n( $views ) ) );
                            ?>
                        </span>
                        <?php if ( $ad_is_featured ) : ?>
                            <span class="fl-sp-meta-item" style="background:rgba(245,158,11,.12);color:#b45309;border-color:rgba(245,158,11,.25);">⭐ <?php esc_html_e( 'آگهی ویژه', 'glassmorphism-child-pro' ); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="fl-sp-price-box" style="margin:18px 0;padding:16px;border-radius:14px;background:rgba(45,95,147,.08);border:1px solid rgba(45,95,147,.16);display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                    <strong style="font-size:1rem;color:var(--fl-text);"><?php echo esc_html( function_exists('glass_ui_extra_t') ? glass_ui_extra_t('price_label') : __( 'قیمت آگهی', 'glassmorphism-child-pro' ) ); ?></strong>
                    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                        <?php if ( $ad_discount > 0 ) : ?><span style="background:#ef4444;color:#fff;border-radius:999px;padding:4px 10px;font-size:.78rem;font-weight:700;"><?php echo esc_html( $ad_discount ); ?>٪ <?php esc_html_e( 'تخفیف', 'glassmorphism-child-pro' ); ?></span><?php endif; ?>
                        <?php if ( $ad_old_price > 0 ) : ?><del style="color:var(--fl-text-light);"><?php echo esc_html( number_format_i18n( $ad_old_price ) ); ?></del><?php endif; ?>
                        <span style="font-size:1.1rem;font-weight:600;color:var(--fl-primary);"><?php echo $ad_price > 0 ? esc_html( function_exists('glass_ui_money') ? glass_ui_money( $ad_price ) : number_format_i18n( $ad_price ) ) : esc_html( function_exists('glass_ui_extra_t') ? glass_ui_extra_t('negotiable') : __( 'توافقی', 'glassmorphism-child-pro' ) ); ?></span>
                    </div>
                </div>
                <div class="fl-sp-body"><?php the_content(); ?></div>
                <!-- CTA removed - VIP box used -->
            </div>

            <!-- ═══ VIP CONTACT BOX ═══ -->
            <div class="ag-combined-box">
                <div class="ag-c-header">
                    <h3 class="ag-c-title"><?php echo esc_html( function_exists('glass_ui_extra_t') ? glass_ui_extra_t('vip_title') : glass_pro_contact( 'cta_price_call' ) ); ?></h3>
                    <div class="ag-c-sub"><?php echo esc_html( function_exists('glass_ui_extra_t') ? glass_ui_extra_t('vip_subtitle') : glass_pro_contact( 'brand_tagline' ) ); ?></div>
                </div>
                <div class="ag-c-grid">
                    <div class="col-phones">
                        <?php if ( !empty($custom_phone) ) : ?>
                            <div class="glass-box">
                                <div class="gb-head"><svg viewBox="0 0 24 24" width="18" height="18"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.86 19.86 0 01-8.63-3.07 19.5 19.5 0 01-6-6A19.86 19.86 0 012.12 4.18 2 2 0 014.11 2h3a2 2 0 012 1.72c.12.9.34 1.78.65 2.62a2 2 0 01-.45 2.11L8.04 9.96a16 16 0 006 6l1.51-1.27a2 2 0 012.11-.45c.84.31 1.72.53 2.62.65A2 2 0 0122 16.92Z" fill="var(--vip-highlight,#38bdf8)" stroke="none"/></svg> <?php echo esc_html( function_exists('glass_ui_t') ? glass_ui_t('owner_phone') : __( 'شماره تماس مالک آگهی', 'glassmorphism-child-pro' ) ); ?></div>
                                
                                <?php if ( $is_user_submitted ) : ?>
                                    <?php if ( $is_sold ) : ?>
                                        <div style="background: rgba(16, 185, 129, 0.15); color: #059669; border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 10px; padding: 12px; text-align: center; font-weight: bold; font-size: 0.88rem;">
                                            <?php echo esc_html( function_exists('glass_ui_t') ? glass_ui_t('sold') : ( function_exists('glass_t') ? glass_t('sp_sold') : '✅ این آگهی فروخته شد' ) ); ?>
                                        </div>
                                    <?php elseif ( $is_expired ) : ?>
                                        <div style="background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 10px; padding: 12px; text-align: center; font-weight: bold; font-size: 0.88rem;">
                                            <?php echo esc_html( function_exists('glass_ui_t') ? glass_ui_t('expired') : ( function_exists('glass_t') ? glass_t('sp_expired') : '❌ این آگهی منقضی شده است' ) ); ?>
                                        </div>
                                    <?php else : ?>
                                        <!-- دکمه اولیه جهت درخواست نمایش شماره با قفل (مخصوص آگهی کاربران) -->
                                        <button type="button" class="phone-row" id="glassShowPhoneBtn" style="background: linear-gradient(135deg, var(--fl-primary, #2D5F93), var(--fl-primary-dark, #1B4A73)); color: #fff; width: 100%; border: none; border-radius: 12px; padding: 12px; display: flex; align-items: center; justify-content: center; gap: 8px; font-weight: 400; cursor: pointer; transition: all 0.25s ease; box-shadow: 0 4px 12px rgba(45, 95, 147, 0.15); font-family: inherit;">
                                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--fl-accent, #A4B400);"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                            <?php echo esc_html( function_exists('glass_ui_t') ? glass_ui_t('show_owner_phone') : __( 'نمایش شماره تماس مالک', 'glassmorphism-child-pro' ) ); ?>
                                        </button>

                                        <!-- کادر شماره تماس واقعی (در ابتدا مخفی است و فقط بعد از تایید لود می‌شود) -->
                                        <a class="phone-row" id="glassRealPhoneBox" href="tel:<?php echo esc_attr(preg_replace('/[^0-9\+]/', '', $custom_phone)); ?>" style="display: none !important; transition: all 0.3s ease; background: #10b981; color: #fff !important; width: 100%; border: none; border-radius: 12px; padding: 12px; align-items: center; justify-content: center; gap: 8px; font-weight: 400; text-decoration: none !important; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2); font-family: monospace; font-size: 1.05rem; direction: ltr; margin-bottom: 0;">
                                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-inline-end: 5px; transform: scaleX(-1);"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                            <?php echo esc_html($custom_phone); ?>
                                        </a>
                                    <?php endif; ?>
                                <?php else : ?>
                                    <!-- نمایش مستقیم شماره برای آگهی‌های ادمین (بدون انقضا و قفل) -->
                                    <a class="phone-row" href="tel:<?php echo esc_attr(preg_replace('/[^0-9\+]/', '', $custom_phone)); ?>" style="background: #10b981; color: #fff !important; width: 100%; border: none; border-radius: 12px; padding: 12px; display: flex; align-items: center; justify-content: center; gap: 8px; font-weight: 400; text-decoration: none !important; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2); font-family: monospace; font-size: 1.05rem; direction: ltr; margin-bottom: 0;">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-inline-end: 5px; transform: scaleX(-1);"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                        <?php echo esc_html($custom_phone); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php else : ?>
                            <div class="glass-box">
                                <div class="gb-head"><svg viewBox="0 0 24 24" width="18" height="18"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.86 19.86 0 01-8.63-3.07 19.5 19.5 0 01-6-6A19.86 19.86 0 012.12 4.18 2 2 0 014.11 2h3a2 2 0 012 1.72c.12.9.34 1.78.65 2.62a2 2 0 01-.45 2.11L8.04 9.96a16 16 0 006 6l1.51-1.27a2 2 0 012.11-.45c.84.31 1.72.53 2.62.65A2 2 0 0122 16.92Z" fill="var(--vip-highlight,#38bdf8)" stroke="none"/></svg> <?php echo esc_html( function_exists('glass_ui_contact_t') ? glass_ui_contact_t('mobile_phones') : __( 'شماره‌های همراه', 'glassmorphism-child-pro' ) ); ?></div>
                                <a class="phone-row" href="tel:<?php echo esc_attr( glass_pro_contact( 'phone_sales_1_tel' ) ); ?>"><span class="ph-lbl"><?php echo esc_html( function_exists('glass_ui_contact_t') ? glass_ui_contact_t('sales') : glass_pro_contact( 'lbl_sales' ) ); ?></span><span class="ph-num"><?php echo esc_html( glass_pro_contact( 'phone_sales_1' ) ); ?></span></a>
                                <a class="phone-row" href="tel:<?php echo esc_attr( glass_pro_contact( 'phone_sales_2_tel' ) ); ?>"><span class="ph-lbl"><?php echo esc_html( function_exists('glass_ui_contact_t') ? glass_ui_contact_t('sales') : glass_pro_contact( 'lbl_sales' ) ); ?></span><span class="ph-num"><?php echo esc_html( glass_pro_contact( 'phone_sales_2' ) ); ?></span></a>
                                <a class="phone-row" href="tel:<?php echo esc_attr( glass_pro_contact( 'phone_support_tel' ) ); ?>"><span class="ph-lbl"><?php echo esc_html( function_exists('glass_ui_contact_t') ? glass_ui_contact_t('support') : glass_pro_contact( 'lbl_support' ) ); ?></span><span class="ph-num"><?php echo esc_html( glass_pro_contact( 'phone_support' ) ); ?></span></a>
                            </div>
                            <div class="glass-box">
                                <div class="gb-head"><svg viewBox="0 0 24 24" width="18" height="18"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.86 19.86 0 01-8.63-3.07 19.5 19.5 0 01-6-6A19.86 19.86 0 012.12 4.18 2 2 0 014.11 2h3a2 2 0 012 1.72c.12.9.34 1.78.65 2.62a2 2 0 01-.45 2.11L8.04 9.96a16 16 0 006 6l1.51-1.27a2 2 0 012.11-.45c.84.31 1.72.53 2.62.65A2 2 0 0122 16.92Z" fill="var(--vip-highlight,#38bdf8)" stroke="none"/></svg> <?php echo esc_html( function_exists('glass_ui_t') ? glass_ui_t('office_phones') : __( 'تلفن‌های دفتر', 'glassmorphism-child-pro' ) ); ?></div>
                                <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;"><a class="ph-num" style="text-decoration:none;color:#DCEAF7" href="tel:02634720146">026 3472 0146</a><span style="color:rgba(220,234,247,.3)">|</span><a class="ph-num" style="text-decoration:none;color:#DCEAF7" href="tel:02634720147">026 3472 0147</a></div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-channels">
                        <a class="btn-chat-vip" href="<?php echo esc_url($wa_url); ?>" target="_blank" rel="noopener">
                            <svg viewBox="0 0 24 24" width="20" height="20"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" fill="#4ade80" stroke="none"/></svg>
                            <?php echo esc_html( function_exists('glass_ui_t') ? glass_ui_t('online_chat') : __( 'شروع گفتگوی آنلاین (Chat)', 'glassmorphism-child-pro' ) ); ?>
                        </a>
                        <a class="channel-card-vip" href="<?php echo esc_url($wa_url); ?>" target="_blank" rel="noopener">
                            <div class="cc-icon-wrap bg-wa"><svg viewBox="0 0 24 24" width="24" height="24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z" fill="#fff" stroke="none"/></svg></div>
                            <div class="cc-info"><div class="cc-head"><?php echo esc_html( function_exists('glass_ui_t') ? glass_ui_t('market') : __( 'بازار خرید و فروش', 'glassmorphism-child-pro' ) ); ?> <span class="badge-vip"><?php echo esc_html( function_exists('glass_ui_t') ? glass_ui_t('active') : __( 'فعال', 'glassmorphism-child-pro' ) ); ?></span></div><div class="cc-desc"><?php echo esc_html( function_exists('glass_ui_t') ? glass_ui_t('market_desc') : __( 'آگهی‌های دست‌دوم و درخواست خرید', 'glassmorphism-child-pro' ) ); ?></div></div>
                        </a>
                        <a class="channel-card-vip" href="<?php echo esc_url( glass_pro_contact( 'telegram_url' ) ); ?>" target="_blank" rel="noopener">
                            <div class="cc-icon-wrap bg-tg"><svg viewBox="0 0 24 24" width="24" height="24"><path d="M11.944 0A12 12 0 000 12a12 12 0 0012 12 12 12 0 0012-12A12 12 0 0012 0h-.056zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 01.171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.479.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z" fill="#fff" stroke="none"/></svg></div>
                            <div class="cc-info"><div class="cc-head"><?php echo esc_html( function_exists('glass_ui_t') ? glass_ui_t('telegram') : __( 'کانال تلگرام', 'glassmorphism-child-pro' ) ); ?></div><div class="cc-desc"><?php echo esc_html( function_exists('glass_ui_t') ? glass_ui_t('telegram_desc') : __( 'لیست قیمت و موجودی انبار', 'glassmorphism-child-pro' ) ); ?></div></div>
                        </a>
                        <a class="channel-card-vip" href="<?php echo esc_url( glass_pro_contact( 'instagram_url' ) ); ?>" target="_blank" rel="noopener">
                            <div class="cc-icon-wrap bg-ig"><svg viewBox="0 0 24 24" width="24" height="24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z" fill="#fff" stroke="none"/></svg></div>
                            <div class="cc-info"><div class="cc-head"><?php echo esc_html( function_exists('glass_ui_extra_t') ? glass_ui_extra_t('instagram') : __( 'گالری تولیدات', 'glassmorphism-child-pro' ) ); ?></div><div class="cc-desc"><?php echo esc_html( function_exists('glass_ui_extra_t') ? glass_ui_extra_t('instagram_desc') : __( 'فیلم و عکس پروژه‌های اجرایی', 'glassmorphism-child-pro' ) ); ?></div></div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- ═══ RELATED ═══ -->
            <?php if ($related->have_posts()) : ?>
                <div class="fl-sp-glass">
                    <div class="fl-sp-related-head">
                        <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                        <span>موارد مشابه<?php echo $city_name ? ' در ' . esc_html($city_name) : ''; ?></span>
                    </div>
                    <div class="fl-sp-related-grid">
                        <?php while ($related->have_posts()) : $related->the_post();
                            $rel_cities = get_the_terms(get_the_ID(), 'portfolio_city');
                            $rel_city   = (!empty($rel_cities) && !is_wp_error($rel_cities)) ? $rel_cities[0]->name : '';
                            $rel_title  = function_exists('replywp_clean_plain_text') ? replywp_clean_plain_text(get_the_title()) : get_the_title();
                        ?>
                            <a href="<?php the_permalink(); ?>" class="fl-sp-rel-card">
                                <div class="fl-sp-rel-thumb">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <?php the_post_thumbnail('medium_large', ['loading' => 'lazy']); ?>
                                    <?php else : ?>
                                        <div class="fl-sp-no-thumb" style="min-height:100px"><span><?php esc_html_e( 'بدون تصویر', 'glassmorphism-child-pro' ); ?></span></div>
                                    <?php endif; ?>
                                    <?php if ($rel_city) : ?>
                                        <span class="fl-sp-rel-badge">
                                            <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="10" r="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            <?php echo esc_html($rel_city); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="fl-sp-rel-body">
                                    <p class="fl-sp-rel-title" style="font-weight:700;"><?php echo esc_html($rel_title); ?></p>
                                    <span class="fl-sp-rel-more"><?php echo esc_html( function_exists('glass_ui_misc_t') ? glass_ui_misc_t('view') : __( 'مشاهده', 'glassmorphism-child-pro' ) ); ?> <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                                </div>
                            </a>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </main>
</div>

<!-- ═══ LIGHTBOX ═══ -->
<div class="fl-sp-lightbox" id="flSpLightbox">
    <button class="fl-sp-lb-close" onclick="flSpCloseLightbox()">
        <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18" stroke-linecap="round" stroke-linejoin="round"/><line x1="6" y1="6" x2="18" y2="18" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>
    <button class="fl-sp-lb-nav fl-sp-lb-next" onclick="flSpLbNav(1)">
        <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>
    <img id="flSpLbImage" src="" alt="">
    <button class="fl-sp-lb-nav fl-sp-lb-prev" onclick="flSpLbNav(-1)">
        <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>
    <div class="fl-sp-lb-counter" id="flSpLbCounter"></div>
</div>

<!-- ═══ MOBILE CITY BUTTON ═══ -->
<button class="fl-pf-mob-btn" onclick="flSpOpenCityDrawer();" aria-label="<?php echo esc_attr($city_button_text); ?>">
    <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:var(--fl-primary,#2D5F93);fill:none;stroke-width:2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="10" r="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
    <?php echo esc_html($city_button_text); ?>
</button>
<div class="fl-pf-overlay"></div>
<div class="fl-pf-drawer">
    <div class="fl-pf-drawer-bar"><span></span></div>
    <div class="fl-pf-drawer-head">
        <div class="fl-pf-drawer-title">
            <svg viewBox="0 0 24 24" style="width:18px;height:18px;stroke:var(--fl-primary,#2D5F93);fill:none;stroke-width:2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="10" r="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span><?php echo esc_html( function_exists('glass_ui_t') ? glass_ui_t('choose_city') : __( 'انتخاب شهر', 'glassmorphism-child-pro' ) ); ?></span>
        </div>
        <button class="fl-pf-drawer-x" aria-label="<?php esc_attr_e( 'بستن', 'glassmorphism-child-pro' ); ?>">
            <svg viewBox="0 0 24 24" style="width:22px;height:22px;stroke:#fff;fill:none;stroke-width:2.5"><line x1="18" y1="6" x2="6" y2="18" stroke-linecap="round" stroke-linejoin="round"/><line x1="6" y1="6" x2="18" y2="18" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
    </div>
    <div class="fl-pf-drawer-search-wrap">
        <div class="fl-pf-drawer-search">
            <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:var(--fl-text-light,#64748B);fill:none;stroke-width:2"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" class="fl-pf-city-search-input" placeholder="<?php echo esc_attr( function_exists('glass_ui_t') ? glass_ui_t('city_search') : __( 'جستجوی شهر...', 'glassmorphism-child-pro' ) ); ?>" autocomplete="off">
        </div>
    </div>
    <?php if (function_exists('replywp_render_city_menu')) { replywp_render_city_menu('fl-drawer-list'); } ?>
    <div class="fl-pf-drawer-empty-search"><?php echo esc_html( function_exists('glass_ui_t') ? glass_ui_t('city_not_found') : __( 'شهری با این نام پیدا نشد.', 'glassmorphism-child-pro' ) ); ?></div>
</div>

<!-- ═══ پنجره تایید رفع مسئولیت و قوانین معامله (Disclaimer Modal) ═══ -->
<div class="fl-sp-lightbox" id="glassDisclaimerModal" style="z-index: 1000000; align-items: center; justify-content: center; background: rgba(0,0,0,0.85); backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px);">
    <div style="background: var(--fl-glass-bg); backdrop-filter: blur(40px) saturate(1.8); -webkit-backdrop-filter: blur(40px) saturate(1.8); border: 1px solid var(--fl-glass-border); border-radius: 20px; max-width: 500px; width: 90%; padding: 30px; box-sizing: border-box; text-align: right; box-shadow: 0 20px 50px rgba(0,0,0,0.3); animation: flSpFadeIn 0.35s ease; direction: rtl;">
        
        <div style="display: flex; align-items: center; gap: 10px; color: #f59e0b; margin-bottom: 18px; border-bottom: 1px solid var(--fl-glass-border); padding-bottom: 12px;">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 400; font-family: inherit;"><?php echo esc_html( function_exists('glass_ui_t') ? glass_ui_t('security_title') : __( 'هشدار امنیتی و رفع مسئولیت', 'glassmorphism-child-pro' ) ); ?></h3>
        </div>

        <p style="font-size: 0.88rem; color: var(--fl-text); line-height: 1.8; margin-bottom: 24px; font-family: inherit;">
            <?php
            echo wp_kses_post( function_exists('glass_ui_t') ? glass_ui_t('security_text') : __( 'کاربر گرامی، این آگهی توسط کاربران سایت ثبت شده است.', 'glassmorphism-child-pro' ) );
            ?>
        </p>

        <div style="display: flex; gap: 12px; justify-content: flex-end; font-family: inherit;">
            <button type="button" id="glassDisclaimerCancelBtn" style="border: 1px solid var(--fl-glass-border); background: rgba(0,0,0,0.05); color: var(--fl-text); padding: 10px 20px; border-radius: 10px; font-weight: 400; cursor: pointer; transition: all 0.25s ease; font-family: inherit; font-size: 0.84rem;"><?php echo esc_html( function_exists('glass_ui_t') ? glass_ui_t('cancel') : __( 'انصراف', 'glassmorphism-child-pro' ) ); ?></button>
            <button type="button" id="glassDisclaimerAgreeBtn" style="border: none; background: #10b981; color: #fff; padding: 10px 20px; border-radius: 10px; font-weight: 400; cursor: pointer; transition: all 0.25s ease; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25); font-family: inherit; font-size: 0.84rem;"><?php echo esc_html( function_exists('glass_ui_t') ? glass_ui_t('agree') : __( 'موافق و تأیید می‌کنم', 'glassmorphism-child-pro' ) ); ?></button>
        </div>

    </div>
</div>

<!-- اسکریپت تعاملی دکمه واترمرک و قوانین و دراور موبایل -->
<script <?php if ( function_exists( 'glass_pro_csp_nonce_attr' ) ) { glass_pro_csp_nonce_attr(); } ?>>
(function() {
    'use strict';
    document.addEventListener('DOMContentLoaded', function() {
        var showBtn   = document.getElementById('glassShowPhoneBtn');
        var realBox   = document.getElementById('glassRealPhoneBox');
        var modal     = document.getElementById('glassDisclaimerModal');
        var agreeBtn  = document.getElementById('glassDisclaimerAgreeBtn');
        var cancelBtn = document.getElementById('glassDisclaimerCancelBtn');

        if (showBtn && modal) {
            showBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                modal.classList.add('fl-on');
            });
        }

        if (cancelBtn && modal) {
            cancelBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                modal.classList.remove('fl-on');
            });
        }

        if (agreeBtn && modal && realBox && showBtn) {
            agreeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                modal.classList.remove('fl-on');
                showBtn.style.display = 'none';
                
                // افکت انیمیشن لود نرم شماره تماس
                realBox.style.display = 'flex';
                realBox.style.opacity = '0';
                setTimeout(function() {
                    realBox.style.opacity = '1';
                }, 50);
            });
        }

        // ۲. منطق کشویی شهرها در موبایل (بای پس ۱۰۰٪ کش مرورگر)
        var btn         = document.querySelector('.fl-pf-mob-btn');
        var drawer      = document.querySelector('.fl-pf-drawer');
        var overlay     = document.querySelector('.fl-pf-overlay');
        var closeX      = document.querySelector('.fl-pf-drawer-x');
        var searchInput = document.querySelector('.fl-pf-city-search-input');
        var drawerList  = document.querySelector('.fl-drawer-list');
        var emptySearch = document.querySelector('.fl-pf-drawer-empty-search');
        var headerGroup = document.getElementById('flHeaderGroup');
        var headerMain  = document.getElementById('flHeader');
        var infoBar     = headerGroup ? headerGroup.querySelector('.fl-info') : null;

        if (!btn) return;

        function updateBtnPos() {
            if (window.innerWidth > 768) {
                document.documentElement.style.removeProperty('--fl-city-btn-top');
                return;
            }
            var gap = 10, top = 88;
            if (headerGroup) {
                var isStuck  = headerGroup.classList.contains('stuck');
                var isHidden = headerMain && headerMain.classList.contains('h-hidden');
                if (isStuck && isHidden) {
                    top = infoBar ? Math.round(infoBar.getBoundingClientRect().bottom + gap) : 52 + gap;
                } else {
                    top = Math.round(headerGroup.getBoundingClientRect().bottom + gap);
                }
            }
            if (top < 50) top = 50;
            if (top > 160) top = 160;
            document.documentElement.style.setProperty('--fl-city-btn-top', top + 'px');
        }

        var raf = null;
        function reqUpdate() {
            if (raf) cancelAnimationFrame(raf);
            raf = requestAnimationFrame(updateBtnPos);
        }

        function resetCitySearch() {
            if (!drawerList) return;
            drawerList.querySelectorAll('li').forEach(function(li){ li.style.display = ''; });
            if (searchInput) searchInput.value = '';
            if (emptySearch) emptySearch.classList.remove('show');
        }

        function filterCityMenu(keyword) {
            if (!drawerList) return;
            var items = drawerList.children;
            var vc = 0;
            var q = (keyword || '').trim().toLowerCase();
            for (var i = 0; i < items.length; i++) {
                var item = items[i];
                if (!item || item.tagName.toLowerCase() !== 'li') continue;
                var text = (item.textContent || '').trim().toLowerCase();
                if (!q) { item.style.display = ''; vc++; }
                else if (text.indexOf(q) !== -1) { item.style.display = ''; vc++; }
                else { item.style.display = 'none'; }
            }
            if (emptySearch) {
                emptySearch.classList.toggle('show', q && vc === 0);
            }
        }

        function openDrawer() {
            if (!drawer || !overlay) return;
            overlay.classList.add('fl-on');
            drawer.classList.add('fl-on');
            document.body.style.overflow = 'hidden';
            document.documentElement.style.overflow = 'hidden';
            setTimeout(function() { if (searchInput) searchInput.focus(); }, 220);
        }

        function closeDrawer() {
            if (drawer) drawer.classList.remove('fl-on');
            if (overlay) overlay.classList.remove('fl-on');
            document.body.style.overflow = '';
            document.documentElement.style.overflow = '';
            resetCitySearch();
        }

        updateBtnPos();
        setTimeout(updateBtnPos, 150);
        setTimeout(updateBtnPos, 500);

        window.addEventListener('load', updateBtnPos);
        window.addEventListener('resize', function() {
            reqUpdate();
            if (window.innerWidth > 768) closeDrawer();
        });
        window.addEventListener('scroll', reqUpdate, { passive: true });

        if (headerGroup) {
            var mo = new MutationObserver(reqUpdate);
            mo.observe(headerGroup, { attributes: true, attributeFilter: ['class', 'style'] });
            if (headerMain) mo.observe(headerMain, { attributes: true, attributeFilter: ['class', 'style'] });
            if (infoBar) mo.observe(infoBar, { attributes: true, attributeFilter: ['class', 'style'] });
        }

        btn.addEventListener('click', function(e) {
            e.preventDefault(); e.stopPropagation();
            openDrawer();
        });

        // دکمه شهرهای روی تصویر شاخص
        window.flSpOpenCityDrawer = function() {
            openDrawer();
        };

        if (closeX) closeX.addEventListener('click', closeDrawer);
        if (overlay) overlay.addEventListener('click', closeDrawer);

        var sy = 0, cy = 0;
        if (drawer) {
            drawer.addEventListener('touchstart', function(e) { sy = e.touches[0].clientY; }, { passive: true });
            drawer.addEventListener('touchmove', function(e) { cy = e.touches[0].clientY; }, { passive: true });
            drawer.addEventListener('touchend', function() { if (cy - sy > 80) closeDrawer(); sy = 0; cy = 0; });
        }

        document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeDrawer(); });
        if (searchInput) searchInput.addEventListener('input', function() { filterCityMenu(this.value); });
    });
})();
</script>

<?php endwhile; get_footer(); ?>
