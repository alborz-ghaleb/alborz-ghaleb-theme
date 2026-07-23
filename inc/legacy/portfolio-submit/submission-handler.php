<?php
/**
 * Portfolio Submit — Form Submission Handler (server-side)
 *
 * @package Alborz_Ghaleb
 * @since   5.0.3
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ════════════════════════════════════════
   ۴. پردازش مرکزی فرم قبل از ارسال هدرها (ریالی و کریپتو)
   ════════════════════════════════════════ */
add_action( 'template_redirect', 'glass_process_portfolio_form_submission' );
function glass_process_portfolio_form_submission() {
    global $glass_pf_errors, $glass_pf_success, $glass_pf_saved_fields, $glass_pf_is_crypto_tx;

    if ( ! isset( $_POST['glass_pf_submit'] ) ) {
        return;
    }

    if ( ! isset( $_POST['glass_pf_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['glass_pf_nonce'] ) ), 'glass_submit_portfolio_action' ) ) {
        $glass_pf_errors[] = __( 'خطای امنیتی رخ داده است. لطفاً صفحه را رفرش کرده و دوباره تلاش کنید.', 'glassmorphism-child-pro' );
        return;
    }

    // تشخیص زبان صفحه جاری جهت سوئیچ درگاه پرداخت
    $current_lang = function_exists('pll_current_language') ? pll_current_language('slug') : 'fa';

    $name     = isset( $_POST['pf_name'] ) ? sanitize_text_field( wp_unslash( $_POST['pf_name'] ) ) : '';
    $title    = isset( $_POST['pf_title'] ) ? sanitize_text_field( wp_unslash( $_POST['pf_title'] ) ) : '';
    $category = isset( $_POST['pf_category'] ) ? absint( wp_unslash( $_POST['pf_category'] ) ) : 0;
    $city     = isset( $_POST['pf_city'] ) ? sanitize_text_field( wp_unslash( $_POST['pf_city'] ) ) : '';
    $excerpt  = isset( $_POST['pf_excerpt'] ) ? sanitize_textarea_field( wp_unslash( $_POST['pf_excerpt'] ) ) : '';
    $content  = isset( $_POST['pf_content'] ) ? wp_kses_post( wp_unslash( $_POST['pf_content'] ) ) : '';
    $phone    = isset( $_POST['pf_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['pf_phone'] ) ) : '';
    $crypto_txid = isset( $_POST['pf_crypto_txid'] ) ? sanitize_text_field( wp_unslash( $_POST['pf_crypto_txid'] ) ) : '';
    $price    = isset( $_POST['pf_price'] ) ? absint( wp_unslash( $_POST['pf_price'] ) ) : 0;
    $old_price = isset( $_POST['pf_old_price'] ) ? absint( wp_unslash( $_POST['pf_old_price'] ) ) : 0;
    $discount_percent = isset( $_POST['pf_discount_percent'] ) ? min( 99, absint( wp_unslash( $_POST['pf_discount_percent'] ) ) ) : 0;

    $glass_pf_saved_fields = [
        'name'        => $name,
        'title'       => $title,
        'category'    => $category,
        'city'        => $city,
        'excerpt'     => $excerpt,
        'content'     => $content,
        'phone'       => $phone,
        'crypto_txid' => $crypto_txid,
        'price' => $price ?: '',
        'old_price' => $old_price ?: '',
        'discount_percent' => $discount_percent ?: '',
    ];

    if ( empty( $name ) ) {
        $glass_pf_errors[] = __( 'نام و نام خانوادگی نمی‌تواند خالی باشد.', 'glassmorphism-child-pro' );
    }
    if ( empty( $title ) ) {
        $glass_pf_errors[] = __( 'عنوان آگهی نمی‌تواند خالی باشد.', 'glassmorphism-child-pro' );
    }
    if ( $category <= 0 ) {
        $glass_pf_errors[] = __( 'لطفاً دسته‌بندی آگهی را انتخاب کنید.', 'glassmorphism-child-pro' );
    }
    if ( empty( $city ) ) {
        $glass_pf_errors[] = __( 'لطفاً نام شهر آگهی را وارد کنید.', 'glassmorphism-child-pro' );
    }
    if ( empty( $content ) ) {
        $glass_pf_errors[] = __( 'متن آگهی نمی‌تواند خالی باشد.', 'glassmorphism-child-pro' );
    }
    if ( empty( $phone ) ) {
        $glass_pf_errors[] = __( 'شماره تماس الزامی است.', 'glassmorphism-child-pro' );
    } elseif ( ! preg_match( '/^0[0-9]{10}$/', preg_replace( '/\s+/', '', $phone ) ) ) {
        $glass_pf_errors[] = __( 'شماره تماس وارد شده معتبر نیست. لطفاً یک شماره ۱۱ رقمی معتبر وارد کنید (مثال: 09121234567).', 'glassmorphism-child-pro' );
    }

    // بررسی فیلد TxID کریپتو در صفحات غیرفارسی
    // [SEC-FIX v5.15.21] اعتبارسنجی سخت‌گیرانه TxID
    if ( ! function_exists( 'glass_pro_validate_crypto_txid' ) ) {
        /**
         * اعتبارسنجی TxID کریپتو – TRON / ETH / generic
         */
        function glass_pro_validate_crypto_txid( string $txid ): bool {
            $txid = trim( $txid );
            if ( '' === $txid ) { return false; }
            $patterns = [
                // Ethereum / BSC / Polygon Tx hash
                '/^0x[a-fA-F0-9]{64}$/',
                // TRON tx (hex 64)
                '/^[a-fA-F0-9]{64}$/',
                // TRON address (برای مواقعی که اشتباهی آدرس می‌دهند – ریجکت)
                // USDT TRC20 T-address – ما قبول نمی‌کنیم، فقط tx hash
                // Generic safe fallback: 32-128 alphanumeric
                '/^[A-Za-z0-9]{32,128}$/',
            ];
            foreach ( $patterns as $p ) {
                if ( preg_match( $p, $txid ) ) {
                    // رد کردن موارد بیش از حد ساده مثل all-zeros
                    if ( preg_match( '/^(0+|f+)$/i', $txid ) ) {
                        continue;
                    }
                    return true;
                }
            }
            return false;
        }
    }

    $crypto_enabled = (bool) get_theme_mod( 'glass_pf_crypto_enabled', false );
    if ( $current_lang !== 'fa' && $crypto_enabled ) {
        if ( empty( $crypto_txid ) ) {
            $glass_pf_errors[] = __( 'Please enter the transaction hash (TxID) of your USDT payment.', 'glassmorphism-child-pro' );
        } elseif ( ! glass_pro_validate_crypto_txid( $crypto_txid ) ) {
            $glass_pf_errors[] = __( 'The transaction hash (TxID) entered is invalid. Expected: 0x…64 hex (ETH) or 64 hex (TRON).', 'glassmorphism-child-pro' );
        }
    }

    if ( glass_contains_links_or_html( $name ) || glass_contains_links_or_html( $title ) || glass_contains_links_or_html( $excerpt ) || glass_contains_links_or_html( $content ) || glass_contains_links_or_html( $crypto_txid ) ) {
        $glass_pf_errors[] = __( 'درج هرگونه آدرس اینترنتی (لینک، دامنه‌ مانند com. یا ir.، وب‌سایت شروع‌شونده با www یا http) و همچنین کدهای HTML و اسکریپت در فیلدهای آگهی مجاز نیست.', 'glassmorphism-child-pro' );
    }

    if ( ! empty( $glass_pf_errors ) ) {
        return;
    }

    $edit_post_id = isset( $_POST['glass_edit_ad_id'] ) ? absint( wp_unslash( $_POST['glass_edit_ad_id'] ) ) : 0;
    $is_edit_mode = false;
    $post_id      = 0;

    if ( $edit_post_id > 0 ) {
        if ( ! is_user_logged_in() ) {
            $glass_pf_errors[] = __( 'برای ویرایش آگهی باید وارد حساب کاربری شوید.', 'glassmorphism-child-pro' );
            return;
        }
        $edit_post = get_post( $edit_post_id );
        if ( ! $edit_post || 'portfolio' !== $edit_post->post_type || ( (int) $edit_post->post_author !== get_current_user_id() && ! current_user_can( 'manage_options' ) ) ) {
            $glass_pf_errors[] = __( 'شما اجازه ویرایش این آگهی را ندارید.', 'glassmorphism-child-pro' );
            return;
        }

        $updated = wp_update_post( [
            'ID'           => $edit_post_id,
            'post_title'   => $title,
            'post_content' => $content,
            'post_excerpt' => $excerpt,
        ], true );

        if ( is_wp_error( $updated ) ) {
            $glass_pf_errors[] = __( 'خطا در ویرایش آگهی. لطفاً دوباره تلاش کنید.', 'glassmorphism-child-pro' );
            return;
        }

        $post_id      = $edit_post_id;
        $is_edit_mode = true;
    }

    // ثبت اولیه پست در دیتابیس
    $pay_enabled  = (bool) get_theme_mod( 'glass_pf_pay_enabled', false );
    $pay_amount   = absint( get_theme_mod( 'glass_pf_pay_amount', 50000 ) );
    $merchant_id  = sanitize_text_field( get_theme_mod( 'glass_pf_zarinpal_merchant', '' ) );

    // اگر کریپتو فعال باشد و زبان غیرفارسی باشد، مستقیم وضعیت pending می‌گیرد چون تراکنش دستی تایید می‌شود
    $is_crypto_payment = ( $current_lang !== 'fa' && $crypto_enabled );
    $initial_status = ( $pay_enabled && $current_lang === 'fa' ) ? 'draft' : 'pending';

    if ( ! $is_edit_mode ) {
        $post_data = [
            'post_title'   => $title,
            'post_content' => $content,
            'post_excerpt' => $excerpt,
            'post_type'    => 'portfolio',
            'post_status'  => $initial_status,
            'post_author'  => is_user_logged_in() ? get_current_user_id() : 1,
        ];

        $post_id = wp_insert_post( $post_data );

        if ( is_wp_error( $post_id ) || $post_id <= 0 ) {
            $glass_pf_errors[] = __( 'متأسفانه در ثبت آگهی مشکلی پیش آمد. لطفا مجدداً تلاش کنید.', 'glassmorphism-child-pro' );
            return;
        }
    }

    // ذخیره اطلاعات مالک آگهی
    update_post_meta( $post_id, 'user_submitted', 'yes' );
    update_post_meta( $post_id, 'portfolio_author_name', $name );
    update_post_meta( $post_id, 'portfolio_phone', $phone );
    update_post_meta( $post_id, 'portfolio_price', $price );
    update_post_meta( $post_id, 'portfolio_old_price', $old_price );
    update_post_meta( $post_id, 'portfolio_discount_percent', $discount_percent );

    // [تغییر] شهر به‌صورت متای ساده ذخیره می‌شود (دیگر تاکسونومی نیست)
    update_post_meta( $post_id, 'portfolio_city', $city );
    // انتساب دسته‌بندی به شکل بومی
    wp_set_post_terms( $post_id, [ $category ], 'themsah_theme_type' );

    // [جدید] ثبت تاریخ انقضای ۳۰ روزه و وضعیت آگهی — فقط هنگام ثبت جدید، نه ویرایش.
    if ( ! $is_edit_mode ) {
        $expiry_days = absint( get_theme_mod( 'glass_pf_expiry_days', 30 ) );
        update_post_meta( $post_id, 'portfolio_expiry', time() + ( $expiry_days * DAY_IN_SECONDS ) );
        update_post_meta( $post_id, 'portfolio_ad_state', 'active' ); // active | sold | expired
    }

    if ( function_exists( 'pll_set_post_language' ) ) {
        $current_lang = pll_current_language( 'slug' );
        if ( ! empty( $current_lang ) ) {
            pll_set_post_language( $post_id, $current_lang );
        }
    }

    // آپلود و پردازش رسانه
    require_once( ABSPATH . 'wp-admin/includes/image.php' );
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
    require_once( ABSPATH . 'wp-admin/includes/media.php' );

    // تصاویر آپلودشده از طریق API فرانت‌اند را به آگهی وصل کن.
    // فقط attachmentهایی که همین فرم/API برای همین کاربر ساخته است پذیرفته می‌شوند.
    // ارسال دستی یک attachment ID نباید امکان تغییر parent یا thumbnail را بدهد.
    $ajax_image_ids = [];
    if ( ! empty( $_POST['pf_uploaded_image_ids'] ) ) {
        $ajax_image_ids = array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_POST['pf_uploaded_image_ids'] ) ) ) ) );
        $ajax_image_ids = array_slice( array_unique( $ajax_image_ids ), 0, 5 );
    }
    if ( ! empty( $ajax_image_ids ) ) {
        $attached_ids = [];
        $current_user_id = get_current_user_id();
        $pending_max_age = 12 * HOUR_IN_SECONDS;

        foreach ( $ajax_image_ids as $idx => $attach_id ) {
            $att = get_post( $attach_id );
            if ( ! $att || 'attachment' !== $att->post_type || 'inherit' !== $att->post_status || 0 !== strpos( (string) $att->post_mime_type, 'image/' ) ) {
                continue;
            }

            $pending_time = (int) get_post_meta( $attach_id, '_glass_pending_portfolio_upload', true );
            $pending_user = (int) get_post_meta( $attach_id, '_glass_pending_upload_user', true );

            // AJAX uploads must have a live pending marker and belong to the
            // current authenticated user. Guest binding stays disabled even if
            // the upload endpoint is re-enabled through its filter.
            if ( $pending_time <= 0 || ( time() - $pending_time ) > $pending_max_age ) {
                continue;
            }
            if ( ! is_user_logged_in() || $pending_user <= 0 || $pending_user !== $current_user_id ) {
                continue;
            }

            // Do not steal an attachment already owned by another post.
            $parent_id = (int) $att->post_parent;
            if ( $parent_id > 0 && $parent_id !== (int) $post_id ) {
                continue;
            }

            $updated = wp_update_post(
                [
                    'ID'          => $attach_id,
                    'post_parent' => $post_id,
                ],
                true
            );
            if ( is_wp_error( $updated ) ) {
                continue;
            }

            delete_post_meta( $attach_id, '_glass_pending_portfolio_upload' );
            delete_post_meta( $attach_id, '_glass_pending_upload_user' );
            $attached_ids[] = $attach_id;
            if ( 0 === $idx && ! has_post_thumbnail( $post_id ) ) {
                set_post_thumbnail( $post_id, $attach_id );
            }
        }
        if ( ! empty( $attached_ids ) ) {
            update_post_meta( $post_id, 'portfolio_image_ids', $attached_ids );
        }
    }

    if ( isset( $_FILES['pf_images'] ) && ! empty( $_FILES['pf_images']['name'][0] ) ) {
        $files = $_FILES['pf_images'];
        $uploaded_count = 0;
        $limit = min( 5, count( $files['name'] ) );
        
        for ( $i = 0; $i < $limit; $i++ ) {
            if ( $files['error'][$i] !== UPLOAD_ERR_OK ) {
                continue;
            }
            
            $file_name = $files['name'][$i];
            $file_tmp  = $files['tmp_name'][$i];
            $file_size = $files['size'][$i];

            // [UX v5.0.5] سقف ۸MB (قبلاً 1MB بسیار محدود بود). compress خودکار در glass_process_compress_and_watermark_image.
            $max_upload_size = (int) apply_filters( 'glass_pro/portfolio/max_upload_size', 8 * 1024 * 1024 );
            if ( $file_size > $max_upload_size ) {
                $glass_pf_errors[] = sprintf(
                    /* translators: 1: file name, 2: max size in MB */
                    __( 'فایل %1$s بزرگتر از %2$d مگابایت است.', 'glassmorphism-child-pro' ),
                    esc_html( $file_name ),
                    (int) ( $max_upload_size / 1024 / 1024 )
                );
                continue;
            }

            // [PRO-FIX #2] اعتبارسنجی نوع واقعی فایل (نه MIME قابل‌جعلِ مرورگر):
            // ۱) بررسی پسوند+محتوا با تابع امن وردپرس، ۲) تأیید اینکه واقعاً تصویر است.
            $allowed_types = [ 'image/jpeg', 'image/png' ];
            $checked = wp_check_filetype_and_ext( $file_tmp, $file_name, [
                'jpg|jpeg' => 'image/jpeg',
                'png'      => 'image/png',
            ] );
            $real_mime = ! empty( $checked['type'] ) ? $checked['type'] : '';

            // تأیید نهایی با getimagesize روی فایل موقت (محتوای واقعی تصویر)
            $img_info = @getimagesize( $file_tmp );
            if ( false === $img_info || empty( $img_info['mime'] ) ) {
                $real_mime = '';
            } elseif ( $real_mime === '' ) {
                $real_mime = $img_info['mime'];
            }

            if ( ! in_array( $real_mime, $allowed_types, true ) ) {
                $glass_pf_errors[] = sprintf( __( 'فرمت فایل %s معتبر نیست. فقط JPG, JPEG, PNG مجاز هستند.', 'glassmorphism-child-pro' ), esc_html( $file_name ) );
                continue;
            }

            // Image-bomb guard: محدودیت ابعاد واقعی تصویر قبل از پردازش GD.
            $max_pixels = (int) apply_filters( 'glass_pro/portfolio/max_image_pixels', 24_000_000 );
            if ( ! empty( $img_info[0] ) && ! empty( $img_info[1] ) && ( (int) $img_info[0] * (int) $img_info[1] ) > $max_pixels ) {
                $glass_pf_errors[] = sprintf( __( 'ابعاد فایل %s بیش از حد مجاز است.', 'glassmorphism-child-pro' ), esc_html( $file_name ) );
                continue;
            }
            $file_type = $real_mime;
            
            $processed_file = glass_process_compress_and_watermark_image( $file_tmp, $file_type );
            
            if ( $processed_file ) {
                $upload_dir = wp_upload_dir();
                $unique_name = wp_unique_filename( $upload_dir['path'], 'ad_' . $post_id . '_' . $i . '.jpg' );
                $new_file_path = $upload_dir['path'] . '/' . $unique_name;
                
                if ( rename( $processed_file, $new_file_path ) ) {
                    $attachment = [
                        'guid'           => $upload_dir['url'] . '/' . $unique_name,
                        'post_mime_type' => 'image/jpeg',
                        'post_title'     => preg_replace( '/\.[^.]+$/', '', $unique_name ),
                        'post_content'   => '',
                        'post_status'    => 'inherit',
                        'post_parent'    => $post_id
                    ];

                    // Do not leave a file behind when the attachment insert fails.
                    $attach_id = wp_insert_attachment( $attachment, $new_file_path, $post_id, true );
                    if ( is_wp_error( $attach_id ) || ! $attach_id ) {
                        @unlink( $new_file_path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors, WordPress.WP.AlternativeFunctions
                        continue;
                    }

                    $attach_data = wp_generate_attachment_metadata( $attach_id, $new_file_path );
                    if ( is_wp_error( $attach_data ) || empty( $attach_data ) ) {
                        wp_delete_attachment( $attach_id, true );
                        if ( file_exists( $new_file_path ) ) {
                            @unlink( $new_file_path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors, WordPress.WP.AlternativeFunctions
                        }
                        continue;
                    }
                    wp_update_attachment_metadata( $attach_id, $attach_data );

                    if ( $uploaded_count === 0 ) {
                        set_post_thumbnail( $post_id, $attach_id );
                    }
                    $uploaded_count++;
                } else {
                    // rename() failed; remove the temporary processed file.
                    @unlink( $processed_file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors, WordPress.WP.AlternativeFunctions
                }
            }
        }
    }

    if ( $is_edit_mode ) {
        $glass_pf_success = true;
        $glass_pf_saved_fields = [
            'name' => '', 'title' => '', 'category' => '', 'city' => '', 'excerpt' => '', 'content' => '', 'phone' => '', 'crypto_txid' => '', 'price' => '', 'old_price' => '', 'discount_percent' => ''
        ];
        return;
    }

    // پرداخت ارز دیجیتال (USDT)
    if ( $is_crypto_payment ) {
        $crypto_amount = absint( get_theme_mod( 'glass_pf_crypto_amount', 5 ) );
        update_post_meta( $post_id, 'portfolio_payment_status', 'pending_verification' );
        update_post_meta( $post_id, 'portfolio_crypto_txid', $crypto_txid );
        update_post_meta( $post_id, 'portfolio_payment_amount_usdt', $crypto_amount );

        $glass_pf_success = true;
        $glass_pf_is_crypto_tx = true;
        
        $glass_pf_saved_fields = [
            'name'     => '', 'title' => '', 'category' => '', 'city' => '', 'excerpt' => '', 'content' => '', 'phone' => '', 'crypto_txid' => '', 'price' => '', 'old_price' => '', 'discount_percent' => ''
        ];
        return;
    }

    // پرداخت ریالی (زرین‌پال)
    if ( $current_lang === 'fa' && $pay_enabled && ! empty( $merchant_id ) ) {
        update_post_meta( $post_id, 'portfolio_payment_status', 'unpaid' );
        update_post_meta( $post_id, 'portfolio_payment_amount', $pay_amount );

        $callback_url = add_query_arg( [
            'glass_zp_callback' => '1',
            'ad_id'             => $post_id
        ], home_url('/') );

        $amount_in_rials = $pay_amount * 10; 

        $data = [
            'merchant_id' => $merchant_id,
            'amount'      => $amount_in_rials,
            'currency'    => 'IRR', // amount is in Rial — pin explicitly.
            'callback_url'=> $callback_url,
            'description' => 'هزینه ثبت آگهی ' . $title,
            'metadata'    => [ 'mobile' => $phone ],
        ];

        // [SEC v5.0.5] استفاده از helper امن (SSL verify + timeout + error log)
        $result    = glass_pro_zarinpal_request( 'request', $data );
        $authority = glass_pro_zarinpal_get_authority( $result );

        if ( ! empty( $authority ) && preg_match( '/^[A-Za-z0-9_-]{10,128}$/', $authority ) ) {
            update_post_meta( $post_id, 'portfolio_payment_authority', $authority );
            // [SEC-FIX v5.15.21] HMAC برای جلوگیری از tampering ad_id/amount
            $pay_hmac = hash_hmac( 'sha256', $post_id . '|' . $pay_amount . '|' . $authority, wp_salt( 'auth' ) );
            update_post_meta( $post_id, 'portfolio_payment_hmac', $pay_hmac );
            if ( function_exists( 'glass_pro_transaction_log' ) ) {
                glass_pro_transaction_log( [
                    'post_id'   => $post_id,
                    'gateway'   => 'zarinpal',
                    'type'      => 'submit_request',
                    'status'    => 'redirected',
                    'amount'    => $pay_amount,
                    'authority' => $authority,
                    'payload'   => $result,
                ] );
            }
            $payment_url = function_exists( 'glass_core_payment_start_url' )
                ? glass_core_payment_start_url( 'zarinpal', $authority )
                : esc_url_raw( 'https://www.zarinpal.com/pg/StartPay/' . rawurlencode( $authority ) );
            if ( is_wp_error( $payment_url ) ) {
                $glass_pf_errors[] = __( 'آدرس پرداخت نامعتبر است.', 'glassmorphism-child-pro' );
                return;
            }
            wp_redirect( esc_url_raw( $payment_url ) );
            exit;
        } else {
            $glass_pf_errors[] = __( 'خطا در اتصال به درگاه زرین‌پال. آگهی شما ذخیره نشد.', 'glassmorphism-child-pro' );
            update_post_meta( $post_id, 'portfolio_payment_status', 'request_failed' );
            wp_update_post( [ 'ID' => $post_id, 'post_status' => 'draft' ] );
        }
    } else {
        $glass_pf_success = true;
        $glass_pf_saved_fields = [
            'name'     => '', 'title' => '', 'category' => '', 'city' => '', 'excerpt' => '', 'content' => '', 'phone' => '', 'crypto_txid' => '', 'price' => '', 'old_price' => '', 'discount_percent' => ''
        ];
    }
}

