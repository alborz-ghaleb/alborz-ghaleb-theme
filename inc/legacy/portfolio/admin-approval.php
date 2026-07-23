<?php
/**
 * Portfolio — Admin Approval System، Default Terms، City Options Helper
 *
 * این فایل بخشی از تقسیم functions-portfolio.php است.
 *
 * @package Alborz_Ghaleb
 * @since   5.0.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ════════════════════════════════════════
   پنل مدیریت: سیستم تایید سریع آگهی‌ها
   ════════════════════════════════════════ */

/**
 * اضافه کردن ستون وضعیت تایید به لیست آگهی‌ها در مدیریت
 */
add_filter( 'manage_portfolio_posts_columns', 'glass_add_portfolio_approval_column' );
function glass_add_portfolio_approval_column( $columns ) {
    $new_columns = [];
    foreach ( $columns as $key => $title ) {
        if ( $key === 'date' ) {
            $new_columns['portfolio_approval'] = __( 'وضعیت تایید آگهی', 'glassmorphism-child-pro' );
        }
        $new_columns[$key] = $title;
    }
    return $new_columns;
}

/**
 * رندر محتوای ستون وضعیت تایید آگهی
 */
add_action( 'manage_portfolio_posts_custom_column', 'glass_render_portfolio_approval_column', 10, 2 );
function glass_render_portfolio_approval_column( $column, $post_id ) {
    if ( $column !== 'portfolio_approval' ) {
        return;
    }

    $post_status = get_post_status( $post_id );
    
    // دریافت اطلاعات ولت و کد تراکنش در صورت وجود
    $pay_status      = get_post_meta( $post_id, 'portfolio_payment_status', true );
    $crypto_txid     = get_post_meta( $post_id, 'portfolio_crypto_txid', true );
    $pay_amount_usdt = get_post_meta( $post_id, 'portfolio_payment_amount_usdt', true );

    if ( $pay_status === 'pending_verification' && ! empty( $crypto_txid ) ) {
        $tronscan_url = 'https://tronscan.org/#/transaction/' . urlencode( $crypto_txid );
        echo '<div style="margin-bottom: 8px; background: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.2); padding: 6px 10px; border-radius: 6px; display: inline-block; text-align: right;">';
        echo '<span style="color: #0284c7; font-size: 11px; font-weight: bold; display: block; margin-bottom: 4px;">💎 پرداخت تتر (' . esc_html($pay_amount_usdt) . ' USDT)</span>';
        echo '<a href="' . esc_url($tronscan_url) . '" target="_blank" class="button button-small" style="font-size: 10px; font-family: inherit; background: #0284c7; border-color: #0284c7; color: #334155; text-shadow: none; display: inline-block;">بررسی در ترون‌اسکن ↗</a>';
        echo '</div><br>';
    }

    if ( $post_status === 'pending' ) {
        $approve_url = wp_nonce_url( 
            admin_url( 'edit.php?post_type=portfolio&glass_approve_ad=' . $post_id ), 
            'glass_approve_ad_nonce_' . $post_id 
        );
        
        echo '<a href="' . esc_url( $approve_url ) . '" class="button button-primary button-small" style="background: #10b981; border-color: #10b981; text-shadow: none;">تایید و انتشار سریع</a>';
    } elseif ( $post_status === 'publish' ) {
        echo '<span style="color: #10b981; font-weight: bold; display: inline-flex; align-items: center; gap: 4px;">
            <span style="width: 8px; height: 8px; background: #10b981; border-radius: 50%;"></span>
            تایید شده
        </span>';
    } elseif ( $post_status === 'draft' ) {
        echo '<span style="color: #f59e0b; font-weight: bold;">پیش‌نویس (پرداخت‌نشده)</span>';
    } else {
        echo '<span style="color: #64748B;">' . esc_html( get_post_status_object($post_status)->label ) . '</span>';
    }
}

/**
 * شنود اکشن تایید آگهی در پنل مدیریت و انتشار آن
 */
add_action( 'admin_init', 'glass_handle_portfolio_approval_action' );
function glass_handle_portfolio_approval_action() {
    // [SEC v5.0.5] capability قوی‌تر: فقط مدیر/ادیتور می‌توانند آگهی دیگران را تایید کنند.
    // قبلاً 'publish_posts' بود که برای Author هم وجود دارد → escalation امکان داشت.
    if ( ! isset( $_GET['glass_approve_ad'] ) || ! current_user_can( 'edit_others_posts' ) ) {
        return;
    }

    $post_id = intval( wp_unslash( $_GET['glass_approve_ad'] ) );

    if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'glass_approve_ad_nonce_' . $post_id ) ) {
        wp_die( esc_html__( 'خطای امنیتی رخ داده است.', 'glassmorphism-child-pro' ) );
    }

    wp_update_post([
        'ID'          => $post_id,
        'post_status' => 'publish'
    ]);

    $redirect_url = add_query_arg( [
        'post_type'             => 'portfolio',
        'glass_ad_approved_msg' => '1'
    ], admin_url( 'edit.php' ) );

    wp_safe_redirect( $redirect_url );
    exit;
}

/**
 * نمایش پیام موفقیت پس از تایید آگهی در پنل مدیریت
 */
add_action( 'admin_notices', 'glass_portfolio_approval_admin_notice' );
function glass_portfolio_approval_admin_notice() {
    global $pagenow;
    
    if ( $pagenow === 'edit.php' && isset( $_GET['post_type'] ) && $_GET['post_type'] === 'portfolio' && isset( $_GET['glass_ad_approved_msg'] ) ) {
        echo '<div class="notice notice-success is-dismissible">
            <p>' . esc_html__( 'آگهی با موفقیت تایید و در سایت منتشر شد.', 'glassmorphism-child-pro' ) . '</p>
        </div>';
    }
}

/**
 * ثبت خودکار دسته‌بندی‌ها و شهرها در دیتابیس (فقط ایجاد در صورت عدم وجود جهت حفظ کامل آدرس‌ها و سئوی سایت)
 */
add_action( 'init', 'glass_auto_insert_default_terms', 15 );
function glass_auto_insert_default_terms() {
    if ( get_option('glass_default_terms_inserted') ) {
        return;
    }

    // ۱. ۵ دسته‌بندی اصلی و دقیق طبق درخواست کاربر
    $default_cats = [
        'قیمت قالب دست دوم خرید و فروش',
        'قیچی میلگرد دست دوم خرید و فروش',
        'داربست مدولار دست دوم',
        'جک سقفی ساختمانی دست دوم صلیبی',
        'ماشین الات دست دوم ارماتوربندی فروش و خرید'
    ];

    if ( taxonomy_exists( 'themsah_theme_type' ) ) {
        // غیرفعال کردن موقت فیلترهای Polylang تا بررسی دقیق انجام شود
        $pll_active = function_exists('pll_get_term');
        
        foreach ( $default_cats as $cat_name ) {
            // بررسی مستقیم دیتابیس برای جلوگیری از خطای Polylang در زبان‌های دیگر
            $term_exists = term_exists( $cat_name, 'themsah_theme_type' );
            if ( ! $term_exists ) {
                $inserted = wp_insert_term( $cat_name, 'themsah_theme_type' );
                if ( $pll_active && ! is_wp_error( $inserted ) && function_exists('pll_set_term_language') ) {
                    // اختصاص زبان فارسی به عنوان پیش‌فرض به این دسته‌ها
                    pll_set_term_language( $inserted['term_id'], 'fa' );
                }
            }
        }
        update_option('glass_default_terms_inserted', 1);
    }
}

/**
 * تابع کمکی برای دریافت لیست شهرها جهت استفاده در Select Box فرم‌ها
 * این تابع دقیقاً از همان منبع replywp_render_city_menu استفاده می‌کند
 * (اول منوی سفارشی، اگر نبود تکسونومی شهرها)
 */
function replywp_get_city_options() {
    $options = [];

    // ۱. اولویت با منوی سفارشی شهرها (همان منطق replywp_render_city_menu)
    if ( has_nav_menu( 'portfolio_city_menu' ) ) {
        $menu_items = wp_get_nav_menu_items( 'portfolio_city_menu' );
        if ( ! empty( $menu_items ) ) {
            foreach ( $menu_items as $item ) {
                $term_id = $item->object_id;
                $term = get_term( $term_id, 'portfolio_city' );
                if ( $term && ! is_wp_error( $term ) ) {
                    $options[ $term_id ] = $term->name;
                }
            }
        }
    }

    // ۲. اگر منویی نبود یا خالی بود، از تکسونومی شهرها استفاده کن
    if ( empty( $options ) ) {
        $cities = get_terms( [
            'taxonomy'   => 'portfolio_city',
            'hide_empty' => false,
            'parent'     => 0,
        ] );
        if ( ! empty( $cities ) && ! is_wp_error( $cities ) ) {
            foreach ( $cities as $city ) {
                $options[ $city->term_id ] = $city->name;
            }
        }
    }

    return $options;
}
