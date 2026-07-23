<?php
/** User Panel — Customizer Settings */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ════════════════════════════════════════
   1. تنظیمات Customizer (انقضا و تمدید)
   ════════════════════════════════════════ */

add_action( 'customize_register', 'glass_user_panel_customizer', 27 );
function glass_user_panel_customizer( $wp_customize ) {

    $wp_customize->add_section( 'glass_user_panel_section', [
        'title'    => __( 'پنل کاربری و آگهی‌ها', 'glassmorphism-child-pro' ),
        'panel'    => 'glass_pro_panel',
        'priority' => 30,
    ] );

    // تعداد روز اعتبار آگهی
    $wp_customize->add_setting( 'glass_pf_expiry_days', [
        'default'           => 30,
        'sanitize_callback' => 'absint',
    ] );
    $wp_customize->add_control( 'glass_pf_expiry_days', [
        'label'       => 'تعداد روز اعتبار هر آگهی',
        'description' => 'پس از این مدت، آگهی منقضی می‌شود و شماره تماس مخفی می‌گردد.',
        'section'     => 'glass_user_panel_section',
        'type'        => 'number',
    ] );

    // مبلغ تمدید (تومان)
    $wp_customize->add_setting( 'glass_pf_renew_amount', [
        'default'           => 50000,
        'sanitize_callback' => 'absint',
    ] );
    $wp_customize->add_control( 'glass_pf_renew_amount', [
        'label'       => 'مبلغ تمدید آگهی (تومان)',
        'description' => 'برای تمدید از درگاه زرین‌پال (همان مرچنت ثبت آگهی) استفاده می‌شود.',
        'section'     => 'glass_user_panel_section',
        'type'        => 'number',
    ] );

    // فعال‌سازی پرداخت برای تمدید
    $wp_customize->add_setting( 'glass_pf_renew_pay_enabled', [
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ] );
    $wp_customize->add_control( 'glass_pf_renew_pay_enabled', [
        'label'   => 'تمدید آگهی نیازمند پرداخت باشد',
        'section' => 'glass_user_panel_section',
        'type'    => 'checkbox',
    ] );

    // برگه داشبورد (برای ریدایرکت بعد از لاگین)
    $wp_customize->add_setting( 'glass_pf_dashboard_url', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ] );
    $wp_customize->add_control( 'glass_pf_dashboard_url', [
        'label'       => 'آدرس برگه داشبورد کاربر',
        'description' => 'برگه‌ای که شورت‌کد [glass_user_dashboard] در آن قرار دارد. برای ریدایرکت پس از ورود.',
        'section'     => 'glass_user_panel_section',
        'type'        => 'url',
    ] );

    // برگه ثبت آگهی (برای دکمه «ثبت آگهی جدید» در داشبورد)
    $wp_customize->add_setting( 'glass_pf_submit_url', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ] );
    $wp_customize->add_control( 'glass_pf_submit_url', [
        'label'       => 'آدرس برگه ثبت آگهی',
        'description' => 'برگه‌ای که شورت‌کد [glass_submit_portfolio] در آن قرار دارد. برای دکمه «ثبت آگهی جدید» در داشبورد.',
        'section'     => 'glass_user_panel_section',
        'type'        => 'url',
    ] );

    // برگه ورود/ثبت‌نام (برای دکمه‌های «ورود / ثبت‌نام»)
    $wp_customize->add_setting( 'glass_pf_login_url', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ] );
    $wp_customize->add_control( 'glass_pf_login_url', [
        'label'       => 'آدرس برگه ورود / ثبت‌نام',
        'description' => 'برگه‌ای که شورت‌کد [glass_user_login] در آن قرار دارد.',
        'section'     => 'glass_user_panel_section',
        'type'        => 'url',
    ] );
}

