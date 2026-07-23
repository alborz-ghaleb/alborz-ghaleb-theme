<?php
/**
 * Portfolio Submit — Zarinpal Payment Callback Handler
 *
 * @package Alborz_Ghaleb
 * @since   5.0.3
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'template_redirect', 'glass_zarinpal_payment_callback_handler' );
function glass_zarinpal_payment_callback_handler() {

    if ( ! isset( $_GET['glass_zp_callback'], $_GET['ad_id'], $_GET['Authority'] ) ) {
        return;
    }

    $post_id     = absint( wp_unslash( $_GET['ad_id'] ) );
    $authority   = sanitize_text_field( wp_unslash( $_GET['Authority'] ) );
    $status      = isset( $_GET['Status'] ) ? sanitize_text_field( wp_unslash( $_GET['Status'] ) ) : '';
    $merchant_id = sanitize_text_field( get_theme_mod( 'glass_pf_zarinpal_merchant', '' ) );

    $post = $post_id > 0 ? get_post( $post_id ) : null;
    if ( ! $post || 'portfolio' !== $post->post_type ) {
        wp_safe_redirect( add_query_arg( 'payment', 'invalid', home_url( '/' ) ) );
        exit;
    }

    $saved_authority = (string) get_post_meta( $post_id, 'portfolio_payment_authority', true );
    $payment_status  = (string) get_post_meta( $post_id, 'portfolio_payment_status', true );
    $pay_amount      = absint( get_post_meta( $post_id, 'portfolio_payment_amount', true ) );
    $saved_hmac      = (string) get_post_meta( $post_id, 'portfolio_payment_hmac', true );

    // [SEC-FIX v5.15.21] HMAC verification – جلوگیری از tampering amount/ad_id
    $calc_hmac = hash_hmac( 'sha256', $post_id . '|' . $pay_amount . '|' . $saved_authority, wp_salt( 'auth' ) );
    $hmac_valid = empty( $saved_hmac ) || hash_equals( $saved_hmac, $calc_hmac );

    $is_valid_payment_context = (
        'unpaid' === $payment_status
        && ! empty( $saved_authority )
        && ! empty( $authority )
        && hash_equals( $saved_authority, $authority )
        && ! empty( $merchant_id )
        && $pay_amount > 0
        && $hmac_valid
    );

    /*
     * [SECURITY] Idempotency / replay & race protection.
     *
     * The previous logic relied solely on a read-then-write of the
     * `portfolio_payment_status` meta. Two concurrent callback hits (double
     * click, browser pre-fetch, or a deliberate replay of the callback URL)
     * could both pass the `'unpaid'` check, both call verify, and both fire
     * `glass_pro/payment/paid` — causing the ad to be published/credited twice.
     * Because ZarinPal returns code 101 ("already verified") as a SUCCESS, a
     * replayed callback would otherwise re-trigger fulfilment for free.
     *
     * We claim an atomic lock with add_post_meta(): it only succeeds for the
     * FIRST request that reaches this point. All later/concurrent requests get
     * `false` and are short-circuited as "already processing".
     */
    if ( $is_valid_payment_context && 'OK' === $status ) {
        $lock_claimed = add_post_meta( $post_id, '_portfolio_payment_lock', time(), true );
        if ( false === $lock_claimed ) {
            // Another request already owns this verification.
            $is_valid_payment_context = false;
        }
    }

    if ( function_exists( 'glass_pro_transaction_log' ) ) {
        glass_pro_transaction_log( [
            'post_id'   => $post_id,
            'gateway'   => 'zarinpal',
            'type'      => 'submit_callback',
            'status'    => $is_valid_payment_context ? $status : 'invalid_context',
            'amount'    => $pay_amount,
            'authority' => $authority,
        ] );
    }

    get_header();
    ?>
    <div style="min-height: 60vh; padding: 60px 20px; direction: rtl; text-align: center; box-sizing: border-box;">
        <div class="glass-submit-form-wrap" style="max-width: 550px; margin: 0 auto; padding: 40px 30px;">
    <?php

    if ( 'OK' === $status && $is_valid_payment_context ) {
        $amount_in_rials = $pay_amount * 10;
        $data = [
            'merchant_id' => $merchant_id,
            'amount'      => $amount_in_rials,
            'authority'   => $authority,
        ];

        $result = glass_pro_zarinpal_request( 'verify', $data );

        // [SEC-FIX v5.15.21] تفکیک کد 100 (success) و 101 (verified_before / replay)
        $zp_code = isset( $result['data']['code'] ) ? (int) $result['data']['code'] : 0;
        $is_fresh_paid = ( 100 === $zp_code );
        $is_replay     = ( 101 === $zp_code );

        if ( $is_fresh_paid || $is_replay ) {
            $ref_id = sanitize_text_field( (string) ( $result['data']['ref_id'] ?? '' ) );

            if ( $is_replay ) {
                // Replay detected – لاگ ویژه، publish مجدد انجام نشود اگر قبلاً paid بوده
                if ( function_exists( 'glass_pro_transaction_log' ) ) {
                    glass_pro_transaction_log( [
                        'post_id'   => $post_id,
                        'gateway'   => 'zarinpal',
                        'type'      => 'submit_verify',
                        'status'    => 'replay_101',
                        'amount'    => $pay_amount,
                        'authority' => $authority,
                        'ref_id'    => $ref_id,
                        'payload'   => $result,
                    ] );
                }
            }

            // اگر قبلاً paid شده، فقط نمایش بده
            $already_paid = ( 'paid' === get_post_meta( $post_id, 'portfolio_payment_status', true ) );
            if ( ! $already_paid ) {
                update_post_meta( $post_id, 'portfolio_payment_status', 'paid' );
            }
            update_post_meta( $post_id, 'portfolio_payment_ref_id', $ref_id );
            delete_post_meta( $post_id, 'portfolio_payment_authority' );
            // Keep the lock in place permanently: status is now 'paid', so any
            // future replay also fails the 'unpaid' context check.

            wp_update_post([
                'ID'          => $post_id,
                'post_status' => 'pending',
            ]);

            do_action( 'glass_pro/payment/paid', $post_id, $ref_id, $pay_amount, $result );
            if ( function_exists( 'glass_pro_transaction_log' ) ) {
                glass_pro_transaction_log( [
                    'post_id'   => $post_id,
                    'gateway'   => 'zarinpal',
                    'type'      => 'submit_verify',
                    'status'    => 'paid',
                    'amount'    => $pay_amount,
                    'authority' => $authority,
                    'ref_id'    => $ref_id,
                    'payload'   => $result,
                ] );
            }
            ?>
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" style="margin-bottom: 20px;">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            <h2 style="color: #10b981; font-weight: 700; margin-bottom: 12px;">پرداخت با موفقیت انجام شد</h2>
            <p style="color: var(--fl-text); line-height: 1.6; margin-bottom: 24px;">آگهی شما ثبت شد و پس از بررسی و تایید نهایی مدیر منتشر خواهد شد.</p>

            <div style="background: rgba(255, 255, 255, 0.3); border-radius: 12px; padding: 15px; text-align: right; border: 1px solid rgba(0,0,0,0.05); margin-bottom: 24px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span style="color: var(--fl-text-light); font-size: 0.88rem;">کد رهگیری تراکنش:</span>
                    <strong style="color: var(--fl-text); font-family: monospace; font-size: 1rem;"><?php echo esc_html( $ref_id ); ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--fl-text-light); font-size: 0.88rem;">مبلغ پرداخت شده:</span>
                    <strong style="color: var(--fl-text);"><?php echo esc_html( number_format_i18n( $pay_amount ) ); ?> تومان</strong>
                </div>
            </div>
            <?php
        } else {
            update_post_meta( $post_id, 'portfolio_payment_status', 'verify_failed' );
            // Release the lock so the user can legitimately retry payment.
            delete_post_meta( $post_id, '_portfolio_payment_lock' );
            do_action( 'glass_pro/payment/failed', $post_id, 'verify_failed', $result );
            if ( function_exists( 'glass_pro_transaction_log' ) ) {
                glass_pro_transaction_log( [
                    'post_id'   => $post_id,
                    'gateway'   => 'zarinpal',
                    'type'      => 'submit_verify',
                    'status'    => 'verify_failed',
                    'amount'    => $pay_amount,
                    'authority' => $authority,
                    'payload'   => $result,
                ] );
            }
            ?>
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" style="margin-bottom: 20px;">
                <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
            <h2 style="color: #ef4444; font-weight: 700; margin-bottom: 12px;">خطا در تایید تراکنش</h2>
            <p style="color: var(--fl-text); line-height: 1.6; margin-bottom: 24px;">تراکنش شما توسط درگاه تایید نگردید یا بررسی آن با خطا روبه‌رو شد. اگر مبلغی از حساب شما کسر شده باشد، طبق روال درگاه بازگشت داده می‌شود.</p>
            <?php
        }
    } elseif ( ! $is_valid_payment_context ) {
        ?>
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" style="margin-bottom: 20px;">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
        </svg>
        <h2 style="color: #f59e0b; font-weight: 700; margin-bottom: 12px;">درخواست پرداخت نامعتبر است</h2>
        <p style="color: var(--fl-text); line-height: 1.6; margin-bottom: 24px;">اطلاعات بازگشتی از درگاه با آگهی ثبت‌شده همخوانی ندارد یا این پرداخت قبلاً پردازش شده است.</p>
        <?php
    } else {
        update_post_meta( $post_id, 'portfolio_payment_status', 'cancelled' );
        do_action( 'glass_pro/payment/failed', $post_id, 'cancelled', null );
        ?>
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" style="margin-bottom: 20px;">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
        </svg>
        <h2 style="color: #f59e0b; font-weight: 700; margin-bottom: 12px;">تراکنش لغو شد</h2>
        <p style="color: var(--fl-text); line-height: 1.6; margin-bottom: 24px;">پرداخت هزینه ثبت آگهی لغو شد. آگهی تا زمان پرداخت و تایید، منتشر نخواهد شد.</p>
        <?php
    }

    ?>
            <a href="<?php echo esc_url( ( function_exists('glass_get_submit_url') ? glass_get_submit_url() : home_url( '/ثبت-آگهی/' ) ) ); ?>" class="glass-btn-submit" style="text-decoration: none; display: inline-flex; width: auto; padding: 12px 30px; border-radius: 10px; background: var(--fl-primary); color: #fff; font-weight: bold; font-size: 0.9rem;">تلاش مجدد</a>
        </div>
    </div>
    <?php
    get_footer();
    exit;
}
