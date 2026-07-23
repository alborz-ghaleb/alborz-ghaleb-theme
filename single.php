<?php
/**
 * Single Blog Post Template
 * صفحه تکی نوشته‌ها - گلس‌مورفیسم - مثل پورتفولیو
 * v2.0 - اسلایدر بالا + VIP تماس + بدون تاریخ/نویسنده/دسته
 *
 * @package Alborz_Ghaleb
 * @version 4.0.0
 */

if (!defined('ABSPATH')) exit;

/* ════════════════════════════════════════════════════════════════
   [FIX v3.1.3] شمارنده بازدید — قبل از get_header()

   مشکل قبلی:
   1. شمارش داخل حلقه the_post() بعد از get_header() انجام می‌شد
      → هر back/refresh یک بازدید جدید ثبت می‌شد (cookie نبود)
   2. اگر full-page caching فعال بود، شمارش اصلاً اجرا نمی‌شد چون
      صفحه کش‌شده برمی‌گشت
   3. ادمین لاگین‌شده با edit_posts درست skip می‌شد ولی AJAX/bot نه

   راه‌حل: همان الگوی single-portfolio.php
   - شمارش قبل از get_header() (هنوز header HTTP ارسال نشده)
   - Cookie 24 ساعته برای جلوگیری از شمارش تکراری
   - Skip ادمین، AJAX، و درخواست‌های REST
   ════════════════════════════════════════════════════════════════ */

if ( is_singular('post') && have_posts() ) {
    $pv_post_id   = (int) get_queried_object_id();
    $pv_views_key = 'post_views_count';

    // جلوگیری از شمارش مدیر / AJAX / REST / bots
    $pv_should_count = true;

    if ( is_user_logged_in() && current_user_can('edit_posts') ) {
        $pv_should_count = false;
    }
    if ( defined('DOING_AJAX') && DOING_AJAX ) {
        $pv_should_count = false;
    }
    if ( defined('REST_REQUEST') && REST_REQUEST ) {
        $pv_should_count = false;
    }
    // [v5.0.6] Bot detection — جلوگیری از inflate شمارش توسط crawler ها
    if ( function_exists( 'glass_pro_is_bot' ) && glass_pro_is_bot() ) {
        $pv_should_count = false;
    }

    // Cookie برای جلوگیری از شمارش تکراری (back/refresh/reopen)
    $pv_cookie_name = 'pv_viewed_' . $pv_post_id;
    if ( $pv_should_count && ! isset($_COOKIE[$pv_cookie_name]) ) {
        $pv_views = (int) get_post_meta($pv_post_id, $pv_views_key, true) + 1;
        update_post_meta($pv_post_id, $pv_views_key, $pv_views);

        // setcookie ایمن: SameSite=Lax + Secure روی HTTPS + HttpOnly (PHP 7.3+ option array)
        if ( ! headers_sent() ) {
            setcookie( $pv_cookie_name, '1', [
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
    $thumb_url = $thumbnail ? wp_get_attachment_image_url($thumbnail, 'large') : '';

    // گالری تصاویر
    $all_images = [];
    if ($thumbnail) {
        $all_images[] = wp_get_attachment_image_url($thumbnail, 'large');
    }
    // ACF gallery
    if (function_exists('get_field')) {
        $acf_gallery = get_field('gallery', $post_id);
        if (!empty($acf_gallery) && is_array($acf_gallery)) {
            foreach ($acf_gallery as $img) {
                $img_id = is_array($img) ? $img['ID'] : $img;
                $url = wp_get_attachment_image_url($img_id, 'large');
                if ($url) $all_images[] = $url;
            }
        }
    }
    // تصاویر اتچ شده
    if (count($all_images) <= 1) {
        $attached = get_attached_media('image', $post_id);
        if (!empty($attached)) {
            foreach ($attached as $att) {
                if ($att->ID == $thumbnail) continue;
                $url = wp_get_attachment_image_url($att->ID, 'large');
                if ($url) $all_images[] = $url;
            }
        }
    }

    // زبان
    $lang   = function_exists('pll_current_language') ? pll_current_language('slug') : 'fa';
    if (empty($lang)) $lang = 'fa';
    $is_rtl = in_array($lang, ['fa', 'ar'], true);

    // تگ‌ها
    $tags = get_the_tags($post_id);

    // زمان مطالعه
    $content_raw  = get_the_content();
    $content_text = strip_tags($content_raw);
    $word_count   = str_word_count($content_text);
    if ($word_count < 10) {
        $word_count = count(preg_split('/\s+/u', trim($content_text), -1, PREG_SPLIT_NO_EMPTY));
    }
    $read_time = max(1, ceil($word_count / 200));

    // [FIX v3.1.3] شمارش بازدید — فقط خواندن (نوشتن قبل از header انجام شد)
    $views = (int) get_post_meta($post_id, 'post_views_count', true);

    // نوشته‌های مرتبط
    $categories = get_the_category($post_id);
    $related_args = [
        'post_type'      => 'post',
        'posts_per_page' => 6, // [FIX] 6 پست تا دسکتاپ 2x3 و موبایل 2x2 نمایش بشه (در موبایل 4 تای اول)
        'post__not_in'         => [ $post_id ],
        'post_status'          => 'publish',
        'orderby'              => 'date', // سریع‌تر و cache-friendly؛ اجتناب از ORDER BY RAND()
        'order'                => 'DESC',
        'ignore_sticky_posts'  => true,
        'no_found_rows'        => true,
        'update_post_term_cache' => false,
    ];
    if (!empty($categories)) {
        $related_args['category__in'] = [$categories[0]->term_id];
    }
    if (function_exists('pll_current_language')) {
        $related_args['lang'] = pll_current_language('slug');
    }
    $related = new WP_Query($related_args);

    // تماس
    $phone    = get_theme_mod('fl_footer_phone', '');
    $whatsapp = get_theme_mod('fl_fab_whatsapp', '');
?>



<!-- ═══ SLIDER دسته‌بندی — چندزبانه ═══ -->
<?php
// اسلایدر بر اساس زبان فعلی
$fl_slider_items = [
    'fa' => [
        ['url' => '/قالب-بتن-مدولار/', 'img' => '/wp-content/uploads/2021/04/Modular-formwork-1-300x200.jpeg', 'name' => 'قالب بتن'],
        ['url' => '/انواع-داربست-مدولار/', 'img' => '/wp-content/uploads/2022/10/3-2-768x768-1-300x300.jpg', 'name' => 'داربست مدولار'],
        ['url' => '/جک-سقفی-جک-صلیبی/', 'img' => '/wp-content/uploads/2023/08/photo_2023-08-05_11-57-10-300x225.jpg', 'name' => 'جک سقفی'],
        ['url' => '/قیچی-میلگرد/', 'img' => '/wp-content/uploads/2023/11/تعمیر-قیچی-میلگرد-300x225.jpg', 'name' => 'ماشین آلات'],
        ['url' => '/تجهیزات-قالب-بندی/', 'img' => '/wp-content/uploads/2020/11/pingove.png', 'name' => 'تجهیزات قالب بندی'],
        ['url' => '/قیمت-والپست/', 'img' => '/wp-content/uploads/2026/04/بارگیری-و-ارسال-سفارش-والپست-برای-یکی-از-پروژه-های-ساختمانی-در-تهران-278x300.jpg', 'name' => 'والپست'],
    ],
    'en' => [
        ['url' => '/concrete-formwork/', 'img' => '/wp-content/uploads/2021/06/Easy-installation-of-Modular-plastic-formwork-Alborzpanel-300x187.jpg', 'name' => 'Concrete Formwork'],
        ['url' => '/system-scaffolding/', 'img' => '/wp-content/uploads/2022/10/cuplok-300x166.jpg', 'name' => 'Scaffolding'],
        ['url' => '/prop-jacks-for-construction/', 'img' => '/wp-content/uploads/2023/05/prop-jacks-300x300.jpg', 'name' => 'Ceiling Jack'],
        ['url' => '/rebar-cutters/', 'img' => '/wp-content/uploads/2024/02/Electric-Rebar-Cutter-300x200.jpg', 'name' => 'Machinery'],
        ['url' => '/formwork-accessories/', 'img' => '/wp-content/uploads/2020/11/pingove.png', 'name' => 'Equipment'],
    ],
    'ar' => [
        ['url' => '/قوالب-الخرسانة/', 'img' => '/wp-content/uploads/2024/05/قوالب-صب-الخرسانة--300x200.jpg', 'name' => 'قوالب الخرسانة'],
        ['url' => '/أنواعها-ومكوناتها-السقالات/', 'img' => '/wp-content/uploads/2024/05/السقالات-300x200.jpg', 'name' => 'السقالات'],
        ['url' => '/الدعامة-جاك-الثقيلة-الصلب-أجوستابل-دع/', 'img' => '/wp-content/uploads/2024/04/الدعامة-جاك-الثقيلة-300x200.jpg', 'name' => 'رافعة السقف'],
        ['url' => '/آلات-حديد-التسليح/', 'img' => '/wp-content/uploads/2024/05/قطع-ثني-حديد-التسليح-أحادي-الطور-300x200.jpg', 'name' => 'آلات البناء'],
        ['url' => '/ملحقات-القوالب-الخرسانية/', 'img' => '/wp-content/uploads/2020/11/pingove.png', 'name' => 'معدات القوالب'],
    ],
    'tr' => [
        ['url' => '/beton-kaliplari/', 'img' => '/wp-content/uploads/2024/08/Metal-Insaat-Kalip.jpg', 'name' => 'Beton Kalıp'],
        ['url' => '/kalip-alti-iskele-cesitleri/', 'img' => '/wp-content/uploads/2024/08/Kalip-Alti-Iskele-Cesitleri-300x200.jpg', 'name' => 'İskele'],
        ['url' => '/teleskopik-demir-direk/', 'img' => '/wp-content/uploads/2024/04/Teleskopik-Dire-300x200.jpg', 'name' => 'Tavan Krikosu'],
        ['url' => '/donati-makineleri/', 'img' => '/wp-content/uploads/2024/08/Tek-fazli-insaat-Demir-Kesme-300x200.jpg', 'name' => 'Makineler'],
        ['url' => '/formwork-accessories/', 'img' => '/wp-content/uploads/2020/11/pingove.png', 'name' => 'Ekipman'],
    ],
    'ru' => [
        ['url' => '/виды-бетонных-опалубок/', 'img' => '/wp-content/uploads/2023/04/Виды-Бетонных-Опалубок-300x200.jpg', 'name' => 'Опалубка'],
        ['url' => '/основные-виды-строительных-лесов/', 'img' => '/wp-content/uploads/2024/05/строительных-лесов-300x200.jpg', 'name' => 'Леса'],
        ['url' => '/купить-телескопические-стоики-по-дос/', 'img' => '/wp-content/uploads/2024/04/Стойки-телескопические-300x200.jpg', 'name' => 'Домкрат'],
        ['url' => '/станок-арматурнои/', 'img' => '/wp-content/uploads/2024/05/Резак-и-гибщик-арматурной-300x200.jpg', 'name' => 'Техника'],
        ['url' => ' ', 'img' => '/wp-content/uploads/2020/11/pingove.png', 'name' => 'Оборудование'],
    ],
    'hy' => [
        ['url' => '/կաղապարներ-բետոնի/', 'img' => '/wp-content/uploads/2024/09/կաղապարներ-բետոնի2.jpg', 'name' => 'Ձևափոխիչ'],
        ['url' => '/խառաչո/', 'img' => '/wp-content/uploads/2024/09/Խառաչո-300x200.jpg', 'name' => 'Փայտամած'],
        ['url' => '/շինարարական-հենակ/', 'img' => '/wp-content/uploads/2024/09/Շինարարական-հենակ-300x200.jpg', 'name' => 'Խաdelays'],
        ['url' => '/արմատուրի-ծռում-և-կտրում/', 'img' => '/wp-content/uploads/2024/09/ամրանային-կտրիչ1-300x298.jpg', 'name' => 'Մdelays'],
        ['url' => '', 'img' => '/wp-content/uploads/2020/11/pingove.png', 'name' => 'Սdelays'],
    ],
];

/**
 * فیلتر glass_pro/single/categories_slider — اجازه می‌دهد آرایه محصولات
 * را override کنید یا با return false کل بخش را غیرفعال کنید.
 * همچنین در Customizer می‌توان با تنظیم glass_pro_single_cats_enabled=false غیرفعال کرد.
 */
$fl_current_slider = isset($fl_slider_items[$lang]) ? $fl_slider_items[$lang] : $fl_slider_items['fa'];
$fl_current_slider = apply_filters( 'glass_pro/single/categories_slider', $fl_current_slider, $lang );
$fl_cats_enabled   = (bool) get_theme_mod( 'glass_pro_single_cats_enabled', true );

if ( $fl_cats_enabled && ! empty( $fl_current_slider ) ) :
?>
<div class="fl-sb-cats-outer">
    <div class="fl-sb-cats-wrap">
        <div class="fl-sb-cats-track" id="flSbCatsTrack">
            <?php foreach ($fl_current_slider as $sl_item) :
                $sl_url = trim( (string) ( $sl_item['url'] ?? '' ) );
                $sl_img = trim( (string) ( $sl_item['img'] ?? '' ) );
                if ( '' === $sl_url || '' === $sl_img ) {
                    continue;
                }
                if ( ! preg_match( '#^https?://#i', $sl_url ) ) {
                    $sl_url = home_url( '/' . ltrim( $sl_url, '/' ) );
                }
                if ( ! preg_match( '#^https?://#i', $sl_img ) ) {
                    $sl_img = home_url( '/' . ltrim( $sl_img, '/' ) );
                }
            ?>
                <a href="<?php echo esc_url( $sl_url ); ?>" class="fl-sb-cat-card">
                    <img src="<?php echo esc_url( $sl_img ); ?>" alt="<?php echo esc_attr($sl_item['name']); ?>" loading="lazy" decoding="async" width="300" height="200">
                    <span class="fl-sb-cat-name"><?php echo esc_html($sl_item['name']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>



<div id="main-content" class="fl-sp-blog" role="main">
    <div class="fl-sb-stack">

        <!-- ═══ TITLE بالای تصویر ═══ -->
        <div class="fl-sb-title-wrap">
            <h1 class="fl-sb-title"><?php echo esc_html($title); ?></h1>
            <div class="fl-sb-title-meta">
                <span class="fl-sb-title-meta-item">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <?php printf( esc_html( function_exists('glass_ui_blog_t') ? glass_ui_blog_t('read_min') : '%s دقیقه مطالعه' ), esc_html( (int) $read_time ) ); ?>
                </span>
                <span class="fl-sb-title-meta-item">
                    <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <?php printf( esc_html( function_exists('glass_ui_blog_t') ? glass_ui_blog_t('views') : '%s بازدید' ), esc_html( number_format_i18n($views) ) ); ?>
                </span>
            </div>
        </div>

        <!-- ═══ IMAGE + GALLERY ═══ -->
        <div class="fl-sb-glass">
            <?php if (!empty($all_images)) : ?>
                <div class="fl-sb-image" id="flSbImageMain">
                    <img id="flSbMainImg" src="<?php echo esc_url($all_images[0]); ?>" alt="<?php echo esc_attr($title); ?>" fetchpriority="high" decoding="async" width="800" height="450">
                </div>

                <?php if (count($all_images) > 1) : ?>
                    <div class="fl-sb-gallery-thumbs">
                        <?php foreach ($all_images as $i => $img_url) : ?>
                            <div class="fl-sb-gallery-thumb <?php echo $i === 0 ? 'active' : ''; ?>"
                                 data-src="<?php echo esc_url($img_url); ?>"
                                 onclick="flSbChangeImg(this)">
                                <img src="<?php echo esc_url($img_url); ?>" alt="تصویر <?php echo $i + 1; ?>" loading="lazy">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                <?php endif; ?>
            <?php else : ?>
                <div class="fl-sb-no-thumb"><span><?php echo esc_html( function_exists('glass_ui_blog_t') ? glass_ui_blog_t('no_image') : 'بدون تصویر' ); ?></span></div>
            <?php endif; ?>
        </div>

        <!-- ═══ CONTENT WITH TOC ═══ -->
        <?php
        $toc_enabled  = (bool) get_option( 'glass_pro_toc_enabled', true );
        $toc_on_posts = (bool) get_option( 'glass_pro_toc_on_posts', true );
        $show_toc     = $toc_enabled && $toc_on_posts;
        $show_toc     = apply_filters( 'glass_pro/toc/show', $show_toc, get_the_ID() );
        ?>
        <div class="fl-sb-content-layout<?php echo $show_toc ? '' : ' fl-sb-no-toc'; ?>">
            <!-- فهرست مطالب (Sticky Sidebar) -->
            <?php if ( $show_toc ) : ?>
            <aside class="fl-sb-toc" id="flSbToc" role="navigation" aria-label="<?php echo esc_attr( function_exists('glass_ui_blog_t') ? glass_ui_blog_t('toc') : 'فهرست مطالب' ); ?>" aria-labelledby="flSbTocHeading">
                <div class="fl-sb-toc-inner">
                    <div class="fl-sb-toc-header" id="flSbTocHeading">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                        <span><?php echo esc_html( function_exists('glass_ui_blog_t') ? glass_ui_blog_t('toc') : 'فهرست مطالب' ); ?></span>
                    </div>
                    <ul class="fl-sb-toc-list" id="flSbTocList" role="list" aria-label="فهرست بخش‌ها">
                        <!-- JS پر می‌کند -->
                    </ul>
                </div>
            </aside>
            <?php endif; ?>

            <!-- محتوای اصلی -->
            <div class="fl-sb-glass fl-sb-main-col">
            <div class="fl-sb-content"><?php the_content(); ?></div>

            <?php if (!empty($tags)) : ?>
                <div class="fl-sb-footer-meta">
                    <?php foreach ($tags as $tag) : ?>
                        <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>" class="fl-sb-tag"><?php echo esc_html($tag->name); ?></a>
                    <?php endforeach; ?>
                    <span class="fl-sb-views-chip">
                        <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <?php printf( esc_html( function_exists('glass_ui_blog_t') ? glass_ui_blog_t('views') : '%s بازدید' ), esc_html( number_format_i18n($views) ) ); ?>
                    </span>
                </div>
            <?php endif; ?>

            <!-- Share + Nav -->
            <div class="fl-sb-share-nav">
                <div class="fl-sb-share">
                    <span class="fl-sb-share-label"><?php echo esc_html( function_exists('glass_ui_blog_t') ? glass_ui_blog_t('share') : 'اشتراک:' ); ?></span>
                    <a href="https://wa.me/?text=<?php echo urlencode($title . ' ' . get_permalink()); ?>" target="_blank" rel="noopener" class="fl-sb-share-btn fl-sb-share-btn--wa"><svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg></a>
                    <a href="https://t.me/share/url?url=<?php echo urlencode(get_permalink()); ?>&text=<?php echo urlencode($title); ?>" target="_blank" rel="noopener" class="fl-sb-share-btn fl-sb-share-btn--tg"><svg viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 000 12a12 12 0 0012 12 12 12 0 0012-12A12 12 0 0012 0h-.056zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 01.171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.479.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg></a>
                    <button onclick="navigator.clipboard.writeText('<?php echo esc_url(get_permalink()); ?>');this.style.background='var(--fl-accent)';this.querySelector('svg').style.stroke='#fff';setTimeout(()=>{this.style.background='';this.querySelector('svg').style.stroke=''},1500)" class="fl-sb-share-btn fl-sb-share-btn--cp" title="کپی لینک"><svg viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg></button>
                </div>
                <div class="fl-sb-post-nav">
                    <?php $prev = get_previous_post(); if ($prev) : ?>
                        <a href="<?php echo esc_url( get_permalink( $prev ) ); ?>" class="fl-sb-nav-btn"><svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg><?php echo esc_html( function_exists('glass_ui_blog_t') ? glass_ui_blog_t('prev') : 'قبلی' ); ?></a>
                    <?php endif; ?>
                    <?php $next = get_next_post(); if ($next) : ?>
                        <a href="<?php echo esc_url( get_permalink( $next ) ); ?>" class="fl-sb-nav-btn"><?php echo esc_html( function_exists('glass_ui_blog_t') ? glass_ui_blog_t('next') : 'بعدی' ); ?><svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        </div><!-- /.fl-sb-content-layout -->

        <!-- ═══ VIP CONTACT — مثل پورتفولیو ═══ -->
        <div class="fl-sb-vip">
            <div class="fl-sb-vip-header">
                <h3 class="fl-sb-vip-title"><?php echo esc_html( function_exists('glass_ui_extra_t') ? glass_ui_extra_t('vip_title') : 'برای قیمت تماس بگیرید' ); ?></h3>
                <div class="fl-sb-vip-sub"><?php echo esc_html( function_exists('glass_ui_extra_t') ? glass_ui_extra_t('vip_subtitle') : glass_pro_contact( 'brand_tagline' ) ); ?></div>
            </div>
            <div class="fl-sb-vip-grid">
                <div class="fl-sb-vip-col-phones">
                    <div class="fl-sb-vip-box">
                        <div class="fl-sb-vip-box-head">📱 <?php echo esc_html( function_exists('glass_ui_contact_t') ? glass_ui_contact_t('mobile_phones') : 'شماره‌های همراه' ); ?></div>
                        <a class="fl-sb-vip-phone" href="tel:<?php echo esc_attr( glass_pro_contact( 'phone_sales_1_tel' ) ); ?>"><span class="fl-sb-vip-phone-lbl"><?php echo esc_html( function_exists('glass_ui_contact_t') ? glass_ui_contact_t('sales') : glass_pro_contact( 'lbl_sales' ) ); ?></span><span class="fl-sb-vip-phone-num"><?php echo esc_html( glass_pro_contact( 'phone_sales_1' ) ); ?></span></a>
                        <a class="fl-sb-vip-phone" href="tel:<?php echo esc_attr( glass_pro_contact( 'phone_sales_2_tel' ) ); ?>"><span class="fl-sb-vip-phone-lbl"><?php echo esc_html( function_exists('glass_ui_contact_t') ? glass_ui_contact_t('sales') : glass_pro_contact( 'lbl_sales' ) ); ?></span><span class="fl-sb-vip-phone-num"><?php echo esc_html( glass_pro_contact( 'phone_sales_2' ) ); ?></span></a>
                        <a class="fl-sb-vip-phone" href="tel:<?php echo esc_attr( glass_pro_contact( 'phone_support_tel' ) ); ?>"><span class="fl-sb-vip-phone-lbl"><?php echo esc_html( function_exists('glass_ui_contact_t') ? glass_ui_contact_t('support') : glass_pro_contact( 'lbl_support' ) ); ?></span><span class="fl-sb-vip-phone-num"><?php echo esc_html( glass_pro_contact( 'phone_support' ) ); ?></span></a>
                    </div>
                    <div class="fl-sb-vip-box">
                        <div class="fl-sb-vip-box-head">☎️ <?php echo esc_html( function_exists('glass_ui_contact_t') ? glass_ui_contact_t('office_phones') : 'تلفن‌های دفتر' ); ?></div>
                        <div style="display:flex;justify-content:space-between;align-items:center">
                            <a class="fl-sb-vip-phone-num" style="text-decoration:none;" href="tel:<?php echo esc_attr( glass_pro_contact( 'phone_office_1_tel' ) ); ?>"><?php echo esc_html( glass_pro_contact( 'phone_office_1' ) ); ?></a>
                            <span style="color:rgba(255,255,255,.2)">|</span>
                            <a class="fl-sb-vip-phone-num" style="text-decoration:none;" href="tel:<?php echo esc_attr( glass_pro_contact( 'phone_office_2_tel' ) ); ?>"><?php echo esc_html( glass_pro_contact( 'phone_office_2' ) ); ?></a>
                        </div>
                    </div>
                </div>
                <div class="fl-sb-vip-col-channels">
                    <a class="fl-sb-vip-chat" href="<?php echo esc_url( glass_pro_contact( 'whatsapp_catalog' ) ); ?>" target="_blank" rel="noopener">
                        <svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                        <?php echo esc_html( function_exists('glass_ui_blog_t') ? glass_ui_blog_t('online_chat') : 'شروع گفتگوی آنلاین' ); ?>
                    </a>
                    <a class="fl-sb-vip-channel" href="<?php echo esc_url( glass_pro_contact( 'whatsapp_catalog' ) ); ?>" target="_blank" rel="noopener">
                        <div class="fl-sb-vip-ch-icon bg-wa"><svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg></div>
                        <div class="fl-sb-vip-ch-info"><div class="fl-sb-vip-ch-name"><?php echo esc_html( function_exists('glass_ui_blog_t') ? glass_ui_blog_t('market') : 'بازار خرید و فروش' ); ?></div><div class="fl-sb-vip-ch-desc"><?php echo esc_html( function_exists('glass_ui_extra_t') ? glass_ui_extra_t('market_desc') : 'آگهی‌های دست‌دوم و درخواست خرید' ); ?></div></div>
                    </a>
                    <a class="fl-sb-vip-channel" href="<?php echo esc_url( glass_pro_contact( 'telegram_url' ) ); ?>" target="_blank" rel="noopener">
                        <div class="fl-sb-vip-ch-icon bg-tg"><svg viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 000 12a12 12 0 0012 12 12 12 0 0012-12A12 12 0 0012 0h-.056zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 01.171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.479.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg></div>
                        <div class="fl-sb-vip-ch-info"><div class="fl-sb-vip-ch-name"><?php echo esc_html( function_exists('glass_ui_blog_t') ? glass_ui_blog_t('telegram') : 'کانال تلگرام' ); ?></div><div class="fl-sb-vip-ch-desc"><?php echo esc_html( function_exists('glass_ui_blog_t') ? glass_ui_blog_t('telegram_desc') : 'لیست قیمت و موجودی انبار' ); ?></div></div>
                    </a>
                    <a class="fl-sb-vip-channel" href="<?php echo esc_url( glass_pro_contact( 'instagram_url' ) ); ?>" target="_blank" rel="noopener">
                        <div class="fl-sb-vip-ch-icon bg-ig"><svg viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg></div>
                        <div class="fl-sb-vip-ch-info"><div class="fl-sb-vip-ch-name"><?php echo esc_html( function_exists('glass_ui_blog_t') ? glass_ui_blog_t('instagram') : 'گالری تولیدات' ); ?></div><div class="fl-sb-vip-ch-desc"><?php echo esc_html( function_exists('glass_ui_blog_t') ? glass_ui_blog_t('instagram_desc') : 'فیلم و عکس پروژه‌های اجرایی' ); ?></div></div>
                    </a>
                </div>
            </div>
        </div>

        <!-- ═══ RELATED ═══ -->
        <?php if ($related->have_posts()) : ?>
            <div class="fl-sb-glass">
                <div class="fl-sb-related-head">
                    <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                    <span><?php echo esc_html( function_exists('glass_ui_blog_t') ? glass_ui_blog_t('related') : 'مطالب مرتبط' ); ?></span>
                </div>
                <div class="fl-sb-related-grid">
                    <?php while ($related->have_posts()) : $related->the_post();
                        $rel_title = function_exists('replywp_clean_plain_text') ? replywp_clean_plain_text(get_the_title()) : get_the_title();
                    ?>
                        <a href="<?php the_permalink(); ?>" class="fl-sb-rel-card">
                            <div class="fl-sb-rel-thumb">
                                <?php if (has_post_thumbnail()) the_post_thumbnail('medium', ['loading' => 'lazy']); ?>
                            </div>
                            <div class="fl-sb-rel-body">
                                <h3 class="fl-sb-rel-title"><?php echo esc_html($rel_title); ?></h3>
                            </div>
                        </a>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php
// [FIX] لود بخش کامنت‌ها — قبلاً صدا زده نمی‌شد به همین خاطر کامنت اصلاً نمایش داده نمی‌شد.
if ( comments_open() || get_comments_number() ) {
    comments_template();
}
?>

<?php endwhile; get_footer(); ?>
