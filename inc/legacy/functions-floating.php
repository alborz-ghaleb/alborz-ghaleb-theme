<?php
/**
 * Flavor Floating Buttons
 * Sticky Call & Social Buttons
 * @version 3.0
 */

if (!defined('ABSPATH')) exit;

/* ═══════════════════════════════════════
   1. ENQUEUE
   ═══════════════════════════════════════
   [REMOVED v3.1.3] تابع fl_fab_enqueue() حذف شد چون:
   - hook اش از قبل کامنت شده بود (dead code)
   - enqueue floating در functions.php به‌صورت مرکزی انجام می‌شود
   با handle 'glass-floating' (نه 'fl-floating')
   ═══════════════════════════════════════ */

/* ═══════════════════════════════════════
   2. CUSTOMIZER
   ═══════════════════════════════════════ */

add_action('customize_register', 'fl_fab_customizer', 26);
function fl_fab_customizer($wp_customize) {

    $wp_customize->add_section('fl_fab_section', [
        'title'    => '⚡ Floating Buttons',
        'priority' => 36,
    ]);

    // Show/hide toggles
    $wp_customize->add_setting('fl_fab_call_show', [
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ]);
    $wp_customize->add_control('fl_fab_call_show', [
        'label'   => 'Show Call Button (Right)',
        'section' => 'fl_fab_section',
        'type'    => 'checkbox',
    ]);

    $wp_customize->add_setting('fl_fab_social_show', [
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ]);
    $wp_customize->add_control('fl_fab_social_show', [
        'label'   => 'Show Social Button (Left)',
        'section' => 'fl_fab_section',
        'type'    => 'checkbox',
    ]);

    // Phone for floating call
    $wp_customize->add_setting('fl_fab_phone', [
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('fl_fab_phone', [
        'label'       => '📞 Call Button Phone',
        'description' => 'اگر خالی باشد از شماره فوتر استفاده می‌شود',
        'section'     => 'fl_fab_section',
        'type'        => 'text',
    ]);

    // Social links
    $socials = [
        'fl_fab_whatsapp'  => '💬 WhatsApp URL',
        'fl_fab_telegram'  => '✈ Telegram URL',
        'fl_fab_instagram' => '📸 Instagram URL',
        'fl_fab_linkedin'  => '💼 LinkedIn URL',
        'fl_fab_email'     => '📧 Email',
    ];

    foreach ($socials as $key => $label) {
        $sanitize_func = ($key === 'fl_fab_email') ? 'sanitize_email' : 'esc_url_raw';
        $wp_customize->add_setting($key, [
            'default'           => '',
            'sanitize_callback' => $sanitize_func,
        ]);
        $wp_customize->add_control($key, [
            'label'   => $label,
            'section' => 'fl_fab_section',
            'type'    => ($key === 'fl_fab_email') ? 'email' : 'url',
        ]);
    }
}

/* ═══════════════════════════════════════
   3. HELPERS
   ═══════════════════════════════════════ */

function fl_fab_lang() {
    if (function_exists('pll_current_language')) {
        $l = pll_current_language('slug');
        if (!empty($l)) return $l;
    }
    return strtolower(substr(get_locale(), 0, 2));
}

function fl_fab_tel($phone) {
    return preg_replace('/[^0-9\+]/', '', $phone);
}

function fl_fab_texts() {
    $lang = fl_fab_lang();
    $texts = [
        'fa' => ['call' => 'تماس فوری', 'connect' => 'ارتباط با ما'],
        'en' => ['call' => 'Call Now', 'connect' => 'Connect Us'],
        'ar' => ['call' => 'اتصل الآن', 'connect' => 'تواصل معنا'],
        'tr' => ['call' => 'Hemen Ara', 'connect' => 'Bize Ulaşın'],
        'ru' => ['call' => 'Позвонить', 'connect' => 'Связаться'],
        'hy' => ['call' => 'Զանգահարել', 'connect' => 'Կապ մեզ հետ'],
    ];
    return isset($texts[$lang]) ? $texts[$lang] : $texts['en'];
}

function fl_fab_icon($n) {
    $icons = [
        'phone'    => '<svg viewBox="0 0 24 24" fill="none"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.86 19.86 0 01-8.63-3.07 19.5 19.5 0 01-6-6A19.86 19.86 0 012.12 4.18 2 2 0 014.11 2h3a2 2 0 012 1.72c.12.9.34 1.78.65 2.62a2 2 0 01-.45 2.11L8.04 9.96a16 16 0 006 6l1.51-1.27a2 2 0 012.11-.45c.84.31 1.72.53 2.62.65A2 2 0 0122 16.92Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'email'    => '<svg viewBox="0 0 24 24" fill="none"><rect x="2" y="4" width="20" height="16" rx="3" stroke="currentColor" stroke-width="2"/><path d="m2 7 8.165 5.715a3 3 0 003.67 0L22 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'chat'     => '<svg viewBox="0 0 24 24" fill="none"><path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'close'    => '<svg viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'whatsapp' => '<svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" fill="currentColor"/></svg>',
        'telegram' => '<svg viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 000 12a12 12 0 0012 12 12 12 0 0012-12A12 12 0 0012 0h-.056zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 01.171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.479.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z" fill="currentColor"/></svg>',
        'instagram'=> '<svg viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z" fill="currentColor"/></svg>',
        'linkedin' => '<svg viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" fill="currentColor"/></svg>',
    ];
    return isset($icons[$n]) ? $icons[$n] : '';
}

/* ═══════════════════════════════════════
   4. RENDER FLOATING BUTTONS
   ═══════════════════════════════════════ */

add_action('wp_footer', 'fl_render_floating', 10);
function fl_render_floating() {
    $ui = fl_fab_texts();

    $show_call   = (bool) get_theme_mod('fl_fab_call_show', true);
    $show_social = (bool) get_theme_mod('fl_fab_social_show', true);

    $phone = get_theme_mod('fl_fab_phone', '');
    if (empty($phone)) {
        $phone = get_theme_mod('fl_footer_phone', '');
    }

    $whatsapp  = get_theme_mod('fl_fab_whatsapp', '');
    $telegram  = get_theme_mod('fl_fab_telegram', '');
    $instagram = get_theme_mod('fl_fab_instagram', '');
    $linkedin  = get_theme_mod('fl_fab_linkedin', '');
    $email     = get_theme_mod('fl_fab_email', '');

    if (empty($email)) {
        $email = get_theme_mod('fl_footer_email', '');
    }

    $has_socials = !empty($whatsapp) || !empty($telegram) || !empty($instagram) || !empty($linkedin) || !empty($email);
    ?>

    <!-- CALL BUTTON (RIGHT - WhatsApp Green) -->
    <?php if ($show_call && !empty($phone)) : ?>
        <a href="tel:<?php echo esc_attr(fl_fab_tel($phone)); ?>"
           id="fl-fab-call"
           aria-label="<?php echo esc_attr($ui['call']); ?>">
            <?php echo fl_fab_icon('phone'); ?>
            <span class="fl-fab-label"><?php echo esc_html($ui['call']); ?></span>
        </a>
    <?php endif; ?>

    <!-- SOCIAL BUTTON (LEFT - Primary + Accent) -->
    <?php if ($show_social && $has_socials) : ?>
        <div id="fl-fab-social">
            <button type="button" id="fl-fab-social-btn" aria-label="<?php echo esc_attr($ui['connect']); ?>">
                <span class="fab-icon-chat"><?php echo fl_fab_icon('chat'); ?></span>
                <span class="fab-icon-close"><?php echo fl_fab_icon('close'); ?></span>
            </button>

            <div id="fl-fab-menu">
                <?php if ($whatsapp) : ?>
                    <a href="<?php echo esc_url($whatsapp); ?>" class="fl-fab-item fl-fab-item--whatsapp" target="_blank" rel="noopener">
                        <?php echo fl_fab_icon('whatsapp'); ?>
                        <span>WhatsApp</span>
                    </a>
                <?php endif; ?>
                <?php if ($telegram) : ?>
                    <a href="<?php echo esc_url($telegram); ?>" class="fl-fab-item fl-fab-item--telegram" target="_blank" rel="noopener">
                        <?php echo fl_fab_icon('telegram'); ?>
                        <span>Telegram</span>
                    </a>
                <?php endif; ?>
                <?php if ($instagram) : ?>
                    <a href="<?php echo esc_url($instagram); ?>" class="fl-fab-item fl-fab-item--instagram" target="_blank" rel="noopener">
                        <?php echo fl_fab_icon('instagram'); ?>
                        <span>Instagram</span>
                    </a>
                <?php endif; ?>
                <?php if ($linkedin) : ?>
                    <a href="<?php echo esc_url($linkedin); ?>" class="fl-fab-item fl-fab-item--linkedin" target="_blank" rel="noopener">
                        <?php echo fl_fab_icon('linkedin'); ?>
                        <span>LinkedIn</span>
                    </a>
                <?php endif; ?>
                <?php if ($email) : ?>
                    <a href="mailto:<?php echo esc_attr($email); ?>" class="fl-fab-item fl-fab-item--email">
                        <?php echo fl_fab_icon('email'); ?>
                        <span>Email</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php
}