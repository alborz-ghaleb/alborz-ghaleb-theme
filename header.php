<?php
/**
 * Header Template
 * Version: 3.2 (With Dark Mode Toggle)
 * 
 * @package Glassmorphism-Child
 */

if (!defined('ABSPATH')) {
    exit;
}

// ── Data ──
$show_topbar = get_theme_mod('fl_topbar_show', true);
$show_dark_toggle = get_theme_mod('glass_dark_mode_toggle_show', true);
$phone1 = fl_h_translate('fl_phone1', '+98 21 0000 0000');
$phone2 = fl_h_translate('fl_phone2', '');
$email = fl_h_translate('fl_email', '');
$address = fl_h_translate('fl_address', 'Tehran, Iran');
$menu_loc = fl_h_menu_location();
$is_rtl = fl_h_is_rtl();
$has_topbar = $show_topbar && (!empty($phone1) || !empty($address) || !empty($email));

// Socials
$socials = [];
$social_keys = [
    'fl_social_instagram' => 'instagram',
    'fl_social_telegram'  => 'telegram',
    'fl_social_whatsapp'  => 'whatsapp',
    'fl_social_linkedin'  => 'linkedin',
];
foreach ($social_keys as $mod => $icon) {
    $url = get_theme_mod($mod, '');
    if (!empty($url)) {
        $socials[] = ['url' => $url, 'icon' => $icon, 'name' => ucfirst($icon)];
    }
}

// Languages
$languages = fl_h_get_languages();
$current_lang = fl_h_current_language_data();
$has_langs = !empty($languages) && !empty($current_lang);

// Logo
$logo_id = get_theme_mod('custom_logo');
$logo_data = $logo_id ? wp_get_attachment_image_src( $logo_id, 'thumbnail' ) : false;
$logo_url  = $logo_data ? $logo_data[0] : '';
$logo_w    = $logo_data ? (int) $logo_data[1] : 0;
$logo_h    = $logo_data ? (int) $logo_data[2] : 0;
$site_name = get_bloginfo('name');
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php
/**
 * Hook: glass_pro/before_page
 *
 * Fires right after <body> tag (and after wp_body_open), before any visible markup.
 * مناسب برای: tracking pixels, GTM, banner های ضروری.
 */
do_action( 'glass_pro/before_page' );
?>

<noscript>
	<div role="alert" style="background:#fef3c7;border:1px solid #f59e0b;color:#92400e;padding:12px 16px;text-align:center;font-size:14px;line-height:1.6;">
		<?php esc_html_e( 'برای استفاده کامل از قابلیت‌های این سایت (گالری، حالت تاریک، فرم‌ها)، لطفاً جاوااسکریپت را در مرورگر خود فعال کنید.', 'glassmorphism-child-pro' ); ?>
	</div>
</noscript>

<div id="page" class="site">

<!-- ═══════════ HEADER GROUP ═══════════ -->
<div class="fl-header-group" id="flHeaderGroup">

    <!-- ─── INFO BAR ─── -->
    <?php if ($has_topbar) : ?>
    <div class="fl-info" id="flInfo">
        <div class="fl-info-inner">

            <!-- اطلاعات تماس -->
            <div class="fl-info-group">

                <?php if (!empty($phone1)) : ?>
                <a class="fl-info-chip" href="tel:<?php echo esc_attr(fl_h_tel($phone1)); ?>">
                    <?php echo fl_h_icon('phone'); ?>
                    <span><?php echo esc_html($phone1); ?></span>
                </a>
                <?php endif; ?>

                <?php if (!empty($phone2)) : ?>
                <span class="fl-info-sep"></span>
                <a class="fl-info-chip fl-info-chip--hide-sm" href="tel:<?php echo esc_attr(fl_h_tel($phone2)); ?>">
                    <?php echo fl_h_icon('phone'); ?>
                    <span><?php echo esc_html($phone2); ?></span>
                </a>
                <?php endif; ?>

                <?php if (!empty($email)) : ?>
                <span class="fl-info-sep"></span>
                <a class="fl-info-chip fl-info-chip--hide-sm" href="mailto:<?php echo esc_attr($email); ?>">
                    <?php echo fl_h_icon('email'); ?>
                    <span><?php echo esc_html($email); ?></span>
                </a>
                <?php endif; ?>

                <?php if (!empty($address)) : ?>
                <span class="fl-info-sep"></span>
                <span class="fl-info-chip fl-info-chip--hide-sm">
                    <?php echo fl_h_icon('map'); ?>
                    <span><?php echo esc_html($address); ?></span>
                </span>
                <?php endif; ?>

            </div>

            <!-- شبکه‌ها + دکمه‌ها + زبان + Dark Mode -->
            <div class="fl-info-group">

                <?php if (!empty($socials)) : ?>

                <div class="fl-info-socials">
                    <?php foreach ($socials as $s) : ?>
                    <a class="fl-info-social" href="<?php echo esc_url($s['url']); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr($s['name']); ?>">
                        <?php echo fl_h_icon($s['icon']); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- [FIX] دکمه ثبت آگهی در نوار info -->
                <?php if (!empty($socials)) : ?><span class="fl-info-sep"></span><?php endif; ?>
                <?php
                $glass_submit_link = function_exists('glass_get_submit_url') ? glass_get_submit_url() : home_url('/ثبت-آگهی/');
                $glass_submit_txt  = function_exists('glass_t') ? glass_t('submit_ad') : 'ثبت آگهی';
                $glass_is_logged   = is_user_logged_in();
                ?>
                <a class="fl-info-submit-ad" href="<?php echo esc_url($glass_submit_link); ?>" aria-label="<?php echo esc_attr( $glass_submit_txt ); ?>" title="<?php echo esc_attr( $glass_submit_txt ); ?>">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span><?php echo esc_html($glass_submit_txt); ?></span>
                </a>

                <!-- [FIX] دکمه داشبورد — فقط برای کاربران لاگین‌شده -->
                <?php if ( $glass_is_logged ) :
                    $glass_dash_url  = function_exists('glass_get_dashboard_url') ? glass_get_dashboard_url() : home_url('/');
                    $glass_dash_txt  = function_exists('glass_t') ? glass_t('my_ads') : 'آگهی‌های من';
                ?>
                <a class="fl-info-dash-link" href="<?php echo esc_url($glass_dash_url); ?>" title="<?php echo esc_attr($glass_dash_txt); ?>">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
                    <span><?php echo esc_html($glass_dash_txt); ?></span>
                </a>
                <?php endif; ?>

                <!-- [FIX] زبان کنار دارک مود -->
                <?php if ($has_langs) : ?>
                <span class="fl-info-sep"></span>
                <div class="fl-lang-wrap" id="flLangWrap">
                    <button class="fl-lang-toggle" id="flLangToggle" type="button" aria-expanded="false" aria-controls="flLangDrop">
                        <?php if (!empty($current_lang['flag'])) : ?>
                            <img class="fl-lang-flag" src="<?php echo esc_url($current_lang['flag']); ?>" alt="<?php echo esc_attr( sprintf( __( 'پرچم %s', 'glassmorphism-child-pro' ), $current_lang['name'] ?? strtoupper( $current_lang['slug'] ?? '' ) ) ); ?>" width="20" height="14">
                        <?php endif; ?>
                        <span class="fl-lang-label"><?php echo esc_html(strtoupper($current_lang['slug'])); ?></span>
                        <svg class="fl-lang-arr" viewBox="0 0 12 12"><path d="M3 4.5l3 3 3-3" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>

                    <div class="fl-lang-drop" id="flLangDrop" aria-hidden="true">
                        <?php foreach ($languages as $lang) :
                            if (!empty($lang['current_lang'])) continue;
                        ?>
                        <a class="fl-lang-drop-item" href="<?php echo esc_url($lang['url']); ?>" lang="<?php echo esc_attr($lang['slug']); ?>">
                            <?php if (!empty($lang['flag'])) : ?>
                            <img class="fl-lang-drop-flag" src="<?php echo esc_url($lang['flag']); ?>" alt="<?php echo esc_attr( sprintf( __( 'پرچم %s', 'glassmorphism-child-pro' ), $lang['name'] ?? strtoupper( $lang['slug'] ?? '' ) ) ); ?>" width="20" height="14">
                            <?php endif; ?>
                            <span class="fl-lang-drop-name"><?php echo esc_html($lang['name']); ?></span>
                            <span class="fl-lang-drop-slug"><?php echo esc_html(strtoupper($lang['slug'])); ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($show_dark_toggle) : ?>
                <button class="fl-dark-toggle" id="flDarkToggle" type="button" aria-label="<?php esc_attr_e('Toggle Dark Mode', 'glassmorphism-child-pro'); ?>">
                    <svg class="fl-dark-icon-sun" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="5"/>
                        <line x1="12" y1="1" x2="12" y2="3"/>
                        <line x1="12" y1="21" x2="12" y2="23"/>
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                        <line x1="1" y1="12" x2="3" y2="12"/>
                        <line x1="21" y1="12" x2="23" y2="12"/>
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                    </svg>
                    <svg class="fl-dark-icon-moon" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                    </svg>
                </button>
                <?php endif; ?>

            </div>

        </div>
    </div>
    <?php endif; ?>


    <!-- ─── MAIN HEADER ─── -->
    <div class="fl-header" id="flHeader">
        <div class="fl-bar">

            <!-- لوگو -->
            <a class="fl-logo" href="<?php echo esc_url(home_url('/')); ?>" rel="home">
                <?php if (!empty($logo_url)) : ?>
                <span class="fl-logo-mark">
                    <img src="<?php echo esc_url($logo_url); ?>" alt="لوگوی البرز قالب" aria-hidden="true" width="<?php echo esc_attr( $logo_w ?: 53 ); ?>" height="<?php echo esc_attr( $logo_h ?: 32 ); ?>" decoding="async">
                </span>
                <?php endif; ?>
                <span class="fl-logo-txt">
                    <span class="fl-logo-name"><?php echo esc_html($site_name); ?></span>
                </span>
            </a>

            <!-- ناوبری دسکتاپ -->
            <nav class="fl-nav" aria-label="<?php esc_attr_e('Primary Navigation', 'glassmorphism-child-pro'); ?>">
                <?php
                if ($menu_loc) {
                    wp_nav_menu([
                        'theme_location' => $menu_loc,
                        'container'      => false,
                        'items_wrap'     => '%3$s',
                        'walker'         => new FL_Desktop_Walker(),
                        'depth'          => 2,
                        'fallback_cb'    => false,
                    ]);
                }
                ?>
            </nav>

            <!-- اکشن‌ها -->
            <div class="fl-actions">

                <!-- جستجو -->
                <div class="fl-search-wrap">
                    <button class="fl-icon-btn fl-search-toggle-btn" type="button" aria-label="<?php esc_attr_e('Search', 'glassmorphism-child-pro'); ?>">
                        <?php echo fl_h_icon('search'); ?>
                    </button>
                    <div class="fl-search-pop">
                        <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                            <label class="screen-reader-text" for="flHeaderSearchInput"><?php esc_html_e( 'Search', 'glassmorphism-child-pro' ); ?></label>
                            <div class="fl-search-field">
                                <?php echo fl_h_icon('search'); ?>
                                <input id="flHeaderSearchInput" type="search" name="s" placeholder="<?php esc_attr_e('Search...', 'glassmorphism-child-pro'); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" autocomplete="off">
                            </div>
                        </form>
                    </div>
                </div>

                <!-- همبرگر -->
                <button class="fl-burger" type="button" aria-label="<?php esc_attr_e('Open menu', 'glassmorphism-child-pro'); ?>">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

            </div>

        </div>
    </div>

</div>
<!-- ═══════════ END HEADER GROUP ═══════════ -->


<!-- Overlay -->
<div class="fl-overlay"></div>


<!-- ═══════════ DRAWER ═══════════ -->
<aside class="fl-drawer" id="flDrawer" aria-hidden="true" inert role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Navigation', 'glassmorphism-child-pro'); ?>">

    <!-- Head -->
    <div class="fl-drawer-head">
        <a class="fl-logo" href="<?php echo esc_url(home_url('/')); ?>" rel="home">
            <?php if (!empty($logo_url)) : ?>
            <span class="fl-logo-mark" style="width:36px;height:36px;border-radius:11px;">
                <img src="<?php echo esc_url($logo_url); ?>" alt="لوگوی البرز قالب" aria-hidden="true" width="<?php echo esc_attr( $logo_w ?: 53 ); ?>" height="<?php echo esc_attr( $logo_h ?: 32 ); ?>" decoding="async">
            </span>
            <?php endif; ?>
            <span class="fl-logo-txt">
                <span class="fl-logo-name" style="font-size:.92rem;"><?php echo esc_html($site_name); ?></span>
            </span>
        </a>
        <button class="fl-drawer-close" type="button" aria-label="<?php esc_attr_e('Close', 'glassmorphism-child-pro'); ?>">
            <?php echo fl_h_icon('close'); ?>
        </button>
    </div>

    <!-- Nav -->
    <div class="fl-drawer-nav">
        <?php
        if ($menu_loc) {
            wp_nav_menu([
                'theme_location' => $menu_loc,
                'container'      => false,
                'items_wrap'     => '%3$s',
                'walker'         => new FL_Drawer_Walker(),
                'depth'          => 2,
                'fallback_cb'    => false,
            ]);
        }
        ?>
    </div>

    <!-- Footer -->
    <div class="fl-drawer-foot">

        <!-- اطلاعات تماس -->
        <div class="fl-drawer-info">
            <?php if (!empty($phone1)) : ?>
            <a class="fl-drawer-info-item" href="tel:<?php echo esc_attr(fl_h_tel($phone1)); ?>">
                <?php echo fl_h_icon('phone'); ?>
                <span><?php echo esc_html($phone1); ?></span>
            </a>
            <?php endif; ?>

            <?php if (!empty($phone2)) : ?>
            <a class="fl-drawer-info-item" href="tel:<?php echo esc_attr(fl_h_tel($phone2)); ?>">
                <?php echo fl_h_icon('phone'); ?>
                <span><?php echo esc_html($phone2); ?></span>
            </a>
            <?php endif; ?>

            <?php if (!empty($email)) : ?>
            <a class="fl-drawer-info-item" href="mailto:<?php echo esc_attr($email); ?>">
                <?php echo fl_h_icon('email'); ?>
                <span><?php echo esc_html($email); ?></span>
            </a>
            <?php endif; ?>

            <?php if (!empty($address)) : ?>
            <span class="fl-drawer-info-item">
                <?php echo fl_h_icon('map'); ?>
                <span><?php echo esc_html($address); ?></span>
            </span>
            <?php endif; ?>
        </div>

        <!-- جستجو -->
        <form class="fl-drawer-search" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
            <label class="screen-reader-text" for="flDrawerSearchInput"><?php esc_html_e( 'Search', 'glassmorphism-child-pro' ); ?></label>
            <?php echo fl_h_icon('search'); ?>
            <input id="flDrawerSearchInput" type="search" name="s" placeholder="<?php esc_attr_e('Search...', 'glassmorphism-child-pro'); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" autocomplete="off">
        </form>

    </div>

</aside>


<!-- ═══════════ SPACER ═══════════ -->
<div class="fl-spacer"></div>

<!-- ═══════════ BREADCRUMB — SEO & Accessible ═══════════ -->
<!-- visually hidden but accessible: موتور جستجو و اسکرین‌ریدر می‌بینند -->
<?php if ( function_exists('fl_bc_render') && !is_front_page() && !is_home() ) : ?>
<div class="fl-bc-seo-wrap screen-reader-text">
    <?php fl_bc_render(); ?>
</div>
<?php endif; ?>
