<?php
/**
 * Portfolio Submit — Customizer Settings (zarinpal + crypto)
 *
 * @package Alborz_Ghaleb
 * @since   5.0.3
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ════════════════════════════════════════
   ۱. تنظیمات سفارشی‌ساز (Customizer) برای درگاه ریالی و ارز دیجیتال
   ════════════════════════════════════════ */
add_action( 'customize_register', 'glass_portfolio_payment_customizer' );
function glass_portfolio_payment_customizer( $wp_customize ) {

    $wp_customize->add_section( 'glass_pf_payment_section', [
        'title'    => __( 'درگاه پرداخت آگهی (ریالی و کریپتو)', 'glassmorphism-child-pro' ),
        'panel'    => 'glass_pro_panel',
        'priority' => 20,
    ] );

    // ── تنظیمات زرین‌پال (زبان فارسی) ──
    $wp_customize->add_setting( 'glass_pf_pay_enabled', [
        'default'           => false,
        'sanitize_callback' => 'wp_validate_boolean',
    ] );
    $wp_customize->add_control( 'glass_pf_pay_enabled', [
        'label'       => __( 'فعال‌سازی درگاه زرین‌پال (فارسی)', 'glassmorphism-child-pro' ),
        'section'     => 'glass_pf_payment_section',
        'type'        => 'checkbox',
    ] );

    $wp_customize->add_setting( 'glass_pf_pay_amount', [
        'default'           => '50000',
        'sanitize_callback' => 'absint',
    ] );
    $wp_customize->add_control( 'glass_pf_pay_amount', [
        'label'       => __( 'مبلغ ثبت آگهی زرین‌پال (تومان)', 'glassmorphism-child-pro' ),
        'section'     => 'glass_pf_payment_section',
        'type'        => 'number',
    ] );

    $wp_customize->add_setting( 'glass_pf_zarinpal_merchant', [
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ] );
    $wp_customize->add_control( 'glass_pf_zarinpal_merchant', [
        'label'       => __( 'کد مرچنت زرین‌پال (Merchant ID)', 'glassmorphism-child-pro' ),
        'section'     => 'glass_pf_payment_section',
        'type'        => 'text',
    ] );

    // ── تنظیمات کریپتو تتر (سایر زبان‌ها) ──
    $wp_customize->add_setting( 'glass_pf_crypto_enabled', [
        'default'           => false,
        'sanitize_callback' => 'wp_validate_boolean',
    ] );
    $wp_customize->add_control( 'glass_pf_crypto_enabled', [
        'label'       => __( 'فعال‌سازی پرداخت کریپتو (سایر زبان‌ها)', 'glassmorphism-child-pro' ),
        'description' => __( 'برای آگهی‌هایی که در صفحات غیرفارسی ثبت می‌شوند.', 'glassmorphism-child-pro' ),
        'section'     => 'glass_pf_payment_section',
        'type'        => 'checkbox',
    ] );

    $wp_customize->add_setting( 'glass_pf_crypto_amount', [
        'default'           => '5',
        'sanitize_callback' => 'absint',
    ] );
    $wp_customize->add_control( 'glass_pf_crypto_amount', [
        'label'       => __( 'مبلغ ثبت آگهی کریپتو (USDT)', 'glassmorphism-child-pro' ),
        'section'     => 'glass_pf_payment_section',
        'type'        => 'number',
    ] );

    $wp_customize->add_setting( 'glass_pf_crypto_wallet', [
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ] );
    $wp_customize->add_control( 'glass_pf_crypto_wallet', [
        'label'       => __( 'آدرس ولت تتر (Tether USDT - TRC20)', 'glassmorphism-child-pro' ),
        'description' => __( 'آدرس کیف پول تتر شبکه TRC20 جهت واریز هزینه آگهی.', 'glassmorphism-child-pro' ),
        'section'     => 'glass_pf_payment_section',
        'type'        => 'text',
    ] );
}

