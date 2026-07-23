<?php
/**
 * Footer Template v6.0
 * @package Glassmorphism-Child
 */
if (!defined('ABSPATH')) exit;

$footer_default_copyright = sprintf( '© %s All rights reserved.', wp_date( 'Y' ) );
$copyright = function_exists( 'glass_footer_theme_mod_i18n' ) ? glass_footer_theme_mod_i18n( 'fl_footer_copyright', $footer_default_copyright ) : get_theme_mod( 'fl_footer_copyright', $footer_default_copyright );
// توضیح فوتر: اولویت با متن سفارشی Customizer؛ در غیر این صورت متن چندزبانه‌ی هماهنگ با Polylang.
$desc = function_exists( 'glass_footer_theme_mod_i18n' ) ? glass_footer_theme_mod_i18n( 'fl_footer_desc', '' ) : get_theme_mod('fl_footer_desc', '');
if ( empty($desc) && function_exists('glass_t') ) {
    $desc = glass_t('footer_desc'); // بر اساس زبان فعال Polylang/سایت تغییر می‌کند
}
$address = function_exists( 'glass_footer_theme_mod_i18n' ) ? glass_footer_theme_mod_i18n( 'fl_footer_address', '' ) : get_theme_mod('fl_footer_address', '');
$phone = function_exists( 'glass_footer_theme_mod_i18n' ) ? glass_footer_theme_mod_i18n( 'fl_footer_phone', '' ) : get_theme_mod('fl_footer_phone', '');
$email = function_exists( 'glass_footer_theme_mod_i18n' ) ? glass_footer_theme_mod_i18n( 'fl_footer_email', '' ) : get_theme_mod('fl_footer_email', '');
$logo_id = (int) get_theme_mod('custom_logo');
$logo_url = '';
$logo_width = 0;
$logo_height = 0;
if ( $logo_id ) {
	$logo_data = wp_get_attachment_image_src( $logo_id, 'medium' );
	if ( $logo_data ) {
		$logo_url = $logo_data[0];
		$logo_width = (int) $logo_data[1];
		$logo_height = (int) $logo_data[2];
	} else {
		$logo_url = wp_get_attachment_image_url( $logo_id, 'full' );
	}
}

$site_name = get_bloginfo('name');
$whatsapp = get_theme_mod('fl_fab_whatsapp', '');
$telegram = get_theme_mod('fl_fab_telegram', '');
$instagram = get_theme_mod('fl_fab_instagram', '');
$has_socials = !empty($whatsapp) || !empty($telegram) || !empty($instagram);

/**
 * تیتر ستون فوتر — چندزبانه با Polylang/سیستم glass_t.
 * اگر glass_t موجود بود از آن (تغییر بر اساس زبان فعال)، وگرنه fallback ثابت.
 */
$fl_ft_title = function ($key, $fallback) {
    if (function_exists('glass_t')) {
        $val = glass_t($key);
        if (!empty($val) && $val !== $key) {
            return $val;
        }
    }
    return $fallback;
};

$fl_footer_menu_args = function ($location) {
    $args = [
        'theme_location' => $location,
        'container'      => false,
        'items_wrap'     => '<ul class="fl-ft-menu">%3$s</ul>',
        'depth'          => 1,
        'fallback_cb'    => false,
    ];
    if (class_exists('FL_Footer_Walker')) {
        $args['walker'] = new FL_Footer_Walker();
    }
    return $args;
};
?>

<div id="fl-footer-wrap">
    <footer class="fl-footer">
        <div class="fl-ft-wave">
            <svg viewBox="0 0 1440 70" preserveAspectRatio="none">
                <path d="M0,35 C360,70 720,0 1080,35 C1260,52 1380,18 1440,35 L1440,70 L0,70 Z"/>
            </svg>
        </div>

        <div class="fl-ft-bg">
            <div class="fl-ft-shape fl-ft-shape-1"></div>
            <div class="fl-ft-shape fl-ft-shape-2"></div>
            <div class="fl-ft-shape fl-ft-shape-3"></div>

            <div class="fl-ft-inner">
                <div class="fl-ft-col fl-ft-col--brand">
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="fl-ft-logo">
                        <?php if ($logo_url) : ?>
                            <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($site_name); ?>" <?php echo $logo_width ? 'width="'.esc_attr((string)$logo_width).'"' : ''; ?> <?php echo $logo_height ? 'height="'.esc_attr((string)$logo_height).'"' : ''; ?> loading="lazy" decoding="async" fetchpriority="low">
                        <?php endif; ?>
                        <span class="fl-ft-logo-name"><?php echo esc_html($site_name); ?></span>
                    </a>
                    <?php if (!empty($desc)) : ?>
                        <p class="fl-ft-desc"><?php echo esc_html($desc); ?></p>
                    <?php endif; ?>
                    <?php if ($has_socials) : ?>
                        <div class="fl-ft-socials">
                            <?php if ($whatsapp) : ?>
                                <a href="<?php echo esc_url($whatsapp); ?>" class="fl-ft-social fl-ft-social--whatsapp" target="_blank" rel="noopener" aria-label="WhatsApp">
                                    <?php echo function_exists('fl_h_icon') ? fl_h_icon('whatsapp') : ''; ?>
                                </a>
                            <?php endif; ?>
                            <?php if ($telegram) : ?>
                                <a href="<?php echo esc_url($telegram); ?>" class="fl-ft-social fl-ft-social--telegram" target="_blank" rel="noopener" aria-label="Telegram">
                                    <?php echo function_exists('fl_h_icon') ? fl_h_icon('telegram') : ''; ?>
                                </a>
                            <?php endif; ?>
                            <?php if ($instagram) : ?>
                                <a href="<?php echo esc_url($instagram); ?>" class="fl-ft-social fl-ft-social--instagram" target="_blank" rel="noopener" aria-label="Instagram">
                                    <?php echo function_exists('fl_h_icon') ? fl_h_icon('instagram') : ''; ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="fl-ft-col">
                    <p class="fl-ft-col-title" style="font-weight:700;"><?php echo esc_html( $fl_ft_title('footer_quick', __('دسترسی سریع', 'glassmorphism-child-pro')) ); ?></p>
                    <?php
                    if ( has_nav_menu( 'footer_1' ) ) {
                        wp_nav_menu( $fl_footer_menu_args('footer_1') );
                    } else {
                        // [FIX] لینک‌های پیش‌فرض در صورت عدم تنظیم منو
                        $login_url  = function_exists('glass_get_login_url') ? glass_get_login_url() : home_url('/ورود/');
                        $submit_url = function_exists('glass_get_submit_url') ? glass_get_submit_url() : home_url('/ثبت-آگهی/');
                        $dash_url   = function_exists('glass_get_dashboard_url') ? glass_get_dashboard_url() : home_url('/');
                        ?>
                        <ul class="fl-ft-menu">
                            <?php if ( is_user_logged_in() ) : ?>
                                <li class="fl-ft-menu-item"><a href="<?php echo esc_url( $dash_url ); ?>" class="fl-ft-link"><span class="fl-ft-link-dot"></span><span><?php echo esc_html( function_exists('glass_t') ? glass_t('dashboard') : __( 'داشبورد من', 'glassmorphism-child-pro' ) ); ?></span></a></li>
                                <li class="fl-ft-menu-item"><a href="<?php echo esc_url( $submit_url ); ?>" class="fl-ft-link"><span class="fl-ft-link-dot"></span><span><?php echo esc_html( function_exists('glass_t') ? glass_t('dash_new') : __( 'ثبت آگهی جدید', 'glassmorphism-child-pro' ) ); ?></span></a></li>
                                <li class="fl-ft-menu-item"><a href="<?php echo esc_url( wp_logout_url( home_url('/') ) ); ?>" class="fl-ft-link"><span class="fl-ft-link-dot"></span><span><?php echo esc_html( function_exists('glass_t') ? glass_t('dash_logout') : __( 'خروج', 'glassmorphism-child-pro' ) ); ?></span></a></li>
                            <?php else : ?>
                                <li class="fl-ft-menu-item"><a href="<?php echo esc_url( $login_url ); ?>" class="fl-ft-link"><span class="fl-ft-link-dot"></span><span><?php echo esc_html( function_exists('glass_t') ? glass_t('login_register') : __( 'ورود / ثبت‌نام', 'glassmorphism-child-pro' ) ); ?></span></a></li>
                                <li class="fl-ft-menu-item"><a href="<?php echo esc_url( $submit_url ); ?>" class="fl-ft-link"><span class="fl-ft-link-dot"></span><span><?php echo esc_html( function_exists('glass_t') ? glass_t('dash_new') : __( 'ثبت آگهی جدید', 'glassmorphism-child-pro' ) ); ?></span></a></li>
                            <?php endif; ?>
                        </ul>
                        <?php
                    }
                    ?>
                </div>

                <div class="fl-ft-col">
                    <p class="fl-ft-col-title" style="font-weight:700;"><?php echo esc_html( $fl_ft_title('footer_about', __('درباره ما', 'glassmorphism-child-pro')) ); ?></p>
                    <?php
                    if ( has_nav_menu( 'footer_2' ) ) {
                        wp_nav_menu( $fl_footer_menu_args('footer_2') );
                    } elseif ( current_user_can( 'edit_theme_options' ) ) {
                        // فقط برای مدیر: راهنمای اتصال منو (برای بازدیدکننده مخفی است)
                        printf(
                            '<p class="fl-ft-desc" style="opacity:.7;font-size:.82rem;">%s</p>',
                            esc_html__( 'برای نمایش این بخش، یک فهرست به مکان «فوتر: درباره ما» اختصاص دهید (نمایش → فهرست‌ها).', 'glassmorphism-child-pro' )
                        );
                    }
                    ?>
                    <!-- اینماد اعتماد الکترونیکی -->
                    <div class="fl-ft-enamad" style="text-align:center;margin-top:16px;">
                        <a referrerpolicy='origin' target='_blank' href='https://trustseal.enamad.ir/?id=715114&Code=zKlKlMuOcUQzf0UEd2a9VUlX1AdyXKcs'>
                            <img referrerpolicy='origin' src='https://trustseal.enamad.ir/logo.aspx?id=715114&Code=zKlKlMuOcUQzf0UEd2a9VUlX1AdyXKcs' alt='اینماد اعتماد الکترونیکی' style='cursor:pointer;max-width:120px;height:auto;' code='zKlKlMuOcUQzf0UEd2a9VUlX1AdyXKcs'>
                        </a>
                    </div>
                </div>

                <div class="fl-ft-col fl-ft-col--contact">
                    <p class="fl-ft-col-title" style="font-weight:700;"><?php echo esc_html( $fl_ft_title('footer_contact', __('ارتباط با ما', 'glassmorphism-child-pro')) ); ?></p>
                    <?php if ($phone || $email || $address) : ?>
                        <div class="fl-ft-contact-list">
                            <?php if ($phone) : ?>
                                <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9\+]/', '', $phone)); ?>" class="fl-ft-contact-item">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                    <span><?php echo esc_html($phone); ?></span>
                                </a>
                            <?php endif; ?>
                            <?php if ($email) : ?>
                                <a href="mailto:<?php echo esc_attr($email); ?>" class="fl-ft-contact-item">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                    <span><?php echo esc_html($email); ?></span>
                                </a>
                            <?php endif; ?>
                            <?php if ($address) : ?>
                                <div class="fl-ft-contact-item">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                    <span><?php echo esc_html($address); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="fl-ft-bottom">
                <div class="fl-ft-bottom-inner">
                    <span class="fl-ft-copy"><?php echo esc_html($copyright); ?><?php
                    $privacy_url = function_exists('get_privacy_policy_url') ? get_privacy_policy_url() : '';
                    if ( $privacy_url ) {
                        echo ' <span style="opacity:.5;margin:0 8px;">|</span> <a href="' . esc_url( $privacy_url ) . '" class="fl-ft-privacy-link" style="color:inherit;text-decoration:underline;">' . esc_html__( 'حریم خصوصی', 'glassmorphism-child-pro' ) . '</a>';
                    }
                    ?></span>
                    <button type="button" class="fl-ft-back-top" id="flBackTop" aria-label="بازگشت به بالا">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 15l-6-6-6 6"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </footer>
</div>

</div><!-- #page -->

<?php
/**
 * Hook: glass_pro/after_page
 *
 * Fires after closing #page wrapper, before wp_footer.
 * مناسب برای: floating widgets, chat bots, modal containers.
 */
do_action( 'glass_pro/after_page' );

wp_footer();
?>
</body>
</html>
