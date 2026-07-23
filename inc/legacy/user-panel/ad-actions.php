<?php
/** User Panel — Ad Actions Handler (sold / renew / delete) + Renew Payment Callback */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ════════════════════════════════════════
   5. پردازش اکشن‌های آگهی (فروخته/تمدید/حذف)
   ════════════════════════════════════════ */

add_action( 'template_redirect', 'glass_handle_ad_actions' );
function glass_handle_ad_actions() {

    // Phase 4: اگر پلاگین Core فعال است، action handler مرکزی Core مسئول پردازش است.
    if ( function_exists( 'glass_core_handle_ad_actions' ) ) {
        return;
    }

    if ( ! isset( $_POST['glass_ad_action'] ) || ! is_user_logged_in() ) {
        return;
    }
    if ( ! isset( $_POST['glass_ad_action_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['glass_ad_action_nonce'] ) ), 'glass_ad_action' ) ) {
        return;
    }

    $action  = sanitize_key( $_POST['glass_ad_action'] );
    $post_id = isset( $_POST['glass_ad_id'] ) ? intval( $_POST['glass_ad_id'] ) : 0;
    $user_id = get_current_user_id();

    if ( $post_id <= 0 ) {
        return;
    }

    // مالکیت: فقط صاحب آگهی (یا مدیر) اجازه دارد
    $post = get_post( $post_id );
    if ( ! $post || 'portfolio' !== $post->post_type ) {
        return;
    }
    if ( (int) $post->post_author !== $user_id && ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $redirect = get_theme_mod( 'glass_pf_dashboard_url', '' );
    $redirect = $redirect ? $redirect : home_url('/');

    switch ( $action ) {

        // ── فروخته شد ──
        case 'mark_sold':
            update_post_meta( $post_id, 'portfolio_ad_state', 'sold' );
            update_post_meta( $post_id, 'portfolio_sold_date', time() );
            wp_safe_redirect( add_query_arg( 'glass_msg', 'sold', $redirect ) );
            exit;

        // ── بازگرداندن به فروش (لغو فروخته شد) ──
        case 'unmark_sold':
            // فقط اگر هنوز منقضی نشده
            $expiry = (int) get_post_meta( $post_id, 'portfolio_expiry', true );
            $new_state = ( $expiry > 0 && time() > $expiry ) ? 'expired' : 'active';
            update_post_meta( $post_id, 'portfolio_ad_state', $new_state );
            delete_post_meta( $post_id, 'portfolio_sold_date' );
            wp_safe_redirect( add_query_arg( 'glass_msg', 'restored', $redirect ) );
            exit;

        // ── حذف آگهی ──
        case 'delete':
            wp_trash_post( $post_id );
            wp_safe_redirect( add_query_arg( 'glass_msg', 'deleted', $redirect ) );
            exit;

        // ── تمدید ──
        case 'renew':
            $pay_enabled = (bool) get_theme_mod( 'glass_pf_renew_pay_enabled', true );
            $merchant    = sanitize_text_field( get_theme_mod( 'glass_pf_zarinpal_merchant', '' ) );

            if ( $pay_enabled && ! empty( $merchant ) ) {
                // ارسال به درگاه پرداخت
                glass_start_renew_payment( $post_id, $redirect );
                exit;
            } else {
                // تمدید رایگان
                glass_do_renew( $post_id );
                wp_safe_redirect( add_query_arg( 'glass_msg', 'renewed', $redirect ) );
                exit;
            }
    }
}

/* اعمال تمدید: +۳۰ روز و به‌روزرسانی تاریخ + فعال‌سازی مجدد */
function glass_do_renew( $post_id ) {
    $expiry_days = absint( get_theme_mod( 'glass_pf_expiry_days', 30 ) );
    // مبنای تمدید: از الان (نه از تاریخ انقضای قبلی) تا کاربر سود کند
    update_post_meta( $post_id, 'portfolio_expiry', time() + ( $expiry_days * DAY_IN_SECONDS ) );
    update_post_meta( $post_id, 'portfolio_ad_state', 'active' );
    delete_post_meta( $post_id, 'portfolio_sold_date' );

    // به‌روزرسانی تاریخ آگهی تا بالای لیست بیاید (bump)
    wp_update_post( [
        'ID'            => $post_id,
        'post_date'     => current_time( 'mysql' ),
        'post_date_gmt' => current_time( 'mysql', 1 ),
    ] );
}

/* شروع پرداخت تمدید از طریق زرین‌پال */
function glass_start_renew_payment( $post_id, $redirect ) {
    $merchant = sanitize_text_field( get_theme_mod( 'glass_pf_zarinpal_merchant', '' ) );
    $amount   = absint( get_theme_mod( 'glass_pf_renew_amount', 50000 ) );
    $rials    = $amount * 10;

    $callback = add_query_arg( [
        'glass_renew_callback' => 1,
        'ad_id'                => $post_id,
    ], $redirect );

    $data = [
        'merchant_id'  => $merchant,
        'amount'       => $rials,
        'currency'     => 'IRR', // amount is in Rial — pin explicitly.
        'description'  => 'تمدید آگهی شماره ' . $post_id,
        'callback_url' => $callback,
    ];

    // [SEC v5.0.5] استفاده از helper امن با SSL verify + timeout + error logging
    $res       = glass_pro_zarinpal_request( 'request', $data );
    $authority = glass_pro_zarinpal_get_authority( $res );

    if ( ! empty( $authority ) && preg_match( '/^[A-Za-z0-9_-]{10,128}$/', $authority ) ) {
        update_post_meta( $post_id, 'portfolio_renew_authority', $authority );
        update_post_meta( $post_id, 'portfolio_renew_amount', $amount );
        if ( function_exists( 'glass_pro_transaction_log' ) ) {
            glass_pro_transaction_log( [
                'post_id'   => $post_id,
                'gateway'   => 'zarinpal',
                'type'      => 'renew_request',
                'status'    => 'redirected',
                'amount'    => $amount,
                'authority' => $authority,
                'payload'   => $res,
            ] );
        }
        $payment_url = function_exists( 'glass_core_payment_start_url' )
            ? glass_core_payment_start_url( 'zarinpal', $authority )
            : esc_url_raw( 'https://www.zarinpal.com/pg/StartPay/' . rawurlencode( $authority ) );
        if ( is_wp_error( $payment_url ) ) {
            wp_safe_redirect( add_query_arg( 'glass_msg', 'renew_failed', $redirect ) );
            exit;
        }
        wp_redirect( esc_url_raw( $payment_url ) );
        exit;
    }

    wp_safe_redirect( add_query_arg( 'glass_msg', 'renew_failed', $redirect ) );
    exit;
}

/* تایید بازگشت از درگاه تمدید */
add_action( 'template_redirect', 'glass_renew_payment_callback' );
function glass_renew_payment_callback() {
    // Phase 4: اگر Core فعال است، callback تمدید توسط Core پردازش می‌شود.
    if ( function_exists( 'glass_core_renew_payment_callback' ) ) {
        return;
    }

    if ( ! isset( $_GET['glass_renew_callback'] ) || ! isset( $_GET['ad_id'] ) || ! isset( $_GET['Authority'] ) ) {
        return;
    }

    $post_id   = absint( wp_unslash( $_GET['ad_id'] ) );
    $authority = sanitize_text_field( wp_unslash( $_GET['Authority'] ) );
    $status    = sanitize_text_field( wp_unslash( $_GET['Status'] ?? '' ) );
    $merchant  = sanitize_text_field( get_theme_mod( 'glass_pf_zarinpal_merchant', '' ) );
    $amount    = absint( get_post_meta( $post_id, 'portfolio_renew_amount', true ) );
    $saved     = (string) get_post_meta( $post_id, 'portfolio_renew_authority', true );

    $redirect = get_theme_mod( 'glass_pf_dashboard_url', '' );
    $redirect = $redirect ? $redirect : home_url('/');

    $post = $post_id > 0 ? get_post( $post_id ) : null;
    if ( ! $post || 'portfolio' !== $post->post_type || empty( $saved ) || empty( $authority ) || ! hash_equals( $saved, $authority ) || $amount <= 0 ) {
        if ( function_exists( 'glass_pro_transaction_log' ) ) {
            glass_pro_transaction_log( [
                'post_id'   => $post_id,
                'gateway'   => 'zarinpal',
                'type'      => 'renew_callback',
                'status'    => 'invalid_context',
                'amount'    => $amount,
                'authority' => $authority,
            ] );
        }
        wp_safe_redirect( add_query_arg( 'glass_msg', 'renew_failed', $redirect ) );
        exit;
    }

    if ( 'OK' === $status && ! empty( $merchant ) ) {
        $data = [
            'merchant_id' => $merchant,
            'amount'      => $amount * 10,
            'authority'   => $authority,
        ];
        // [SEC v5.0.5] verify امن با SSL + timeout + strict === comparison
        $res = glass_pro_zarinpal_request( 'verify', $data );

        if ( glass_pro_zarinpal_is_success( $res ) ) {
            glass_do_renew( $post_id );
            delete_post_meta( $post_id, 'portfolio_renew_authority' );
            wp_safe_redirect( add_query_arg( 'glass_msg', 'renewed', $redirect ) );
            exit;
        }
    }

    wp_safe_redirect( add_query_arg( 'glass_msg', 'renew_failed', $redirect ) );
    exit;
}

