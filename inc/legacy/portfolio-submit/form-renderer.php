<?php
/**
 * Portfolio Submit — Frontend Form Renderer (shortcode [glass_submit_portfolio])
 *
 * @package Alborz_Ghaleb
 * @since   5.0.3
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }


if ( ! function_exists( 'glass_pro_submit_category_options' ) ) {
    /**
     * Render submit-form category options for current Polylang language.
     * Persian/default language is limited to the original 5 ad-submit categories;
     * other languages show the Polylang translations created via the + button.
     *
     * @param int|string $selected_id Selected term id.
     * @return string
     */
    function glass_pro_submit_category_options( $selected_id = 0 ) {
        $selected_id  = absint( $selected_id );
        $current_lang = function_exists( 'pll_current_language' ) ? (string) pll_current_language( 'slug' ) : '';
        $default_lang = function_exists( 'pll_default_language' ) ? (string) pll_default_language( 'slug' ) : 'fa';
        $display_terms = [];

        $required_cat_names = (array) apply_filters( 'glass_pro/submit/reference_category_names', [
            'قیمت قالب دست دوم خرید و فروش',
            'قیچی میلگرد دست دوم خرید و فروش',
            'داربست مدولار دست دوم',
            'جک سقفی ساختمانی دست دوم صلیبی',
            'ماشین الات دست دوم ارماتوربندی فروش و خرید',
        ] );

        foreach ( $required_cat_names as $fa_name ) {
            $source_term = get_term_by( 'name', (string) $fa_name, 'themsah_theme_type' );
            if ( ! $source_term || is_wp_error( $source_term ) ) {
                continue;
            }

            $target_id = $source_term->term_id;
            if ( $current_lang && $current_lang !== $default_lang && function_exists( 'pll_get_term' ) ) {
                $translated_id = pll_get_term( $source_term->term_id, $current_lang );
                if ( $translated_id ) {
                    $target_id = $translated_id;
                } else {
                    continue; // در زبان غیرپیش‌فرض فقط ترجمه‌های وصل‌شده با + نمایش داده شوند.
                }
            }

            $target_term = get_term( $target_id, 'themsah_theme_type' );
            if ( $target_term && ! is_wp_error( $target_term ) ) {
                // اگر Polylang زبان term را می‌داند، مطمئن شو term با زبان فعلی یکی است.
                if ( $current_lang && function_exists( 'pll_get_term_language' ) ) {
                    $term_lang = pll_get_term_language( $target_term->term_id );
                    if ( $term_lang && $term_lang !== $current_lang ) {
                        continue;
                    }
                }
                $display_terms[ $target_term->term_id ] = $target_term;
            }
        }

        // fallback فقط برای زبان‌های غیرپیش‌فرض: اگر ترجمه‌ها پیدا نشدند، termهای همان زبان را نشان بده تا فرم خالی نشود.
        if ( empty( $display_terms ) && $current_lang && $current_lang !== $default_lang ) {
            $terms = get_terms( [
                'taxonomy'   => 'themsah_theme_type',
                'hide_empty' => false,
                'orderby'    => 'name',
                'order'      => 'ASC',
                'lang'       => $current_lang,
            ] );
            if ( ! is_wp_error( $terms ) ) {
                foreach ( (array) $terms as $term ) {
                    if ( function_exists( 'pll_get_term_language' ) ) {
                        $term_lang = pll_get_term_language( $term->term_id );
                        if ( $term_lang && $term_lang !== $current_lang ) {
                            continue;
                        }
                    }
                    $display_terms[ $term->term_id ] = $term;
                }
            }
        }

        $display_terms = (array) apply_filters( 'glass_pro/submit/category_terms', $display_terms, $current_lang );
        $html = '';
        foreach ( $display_terms as $term ) {
            if ( ! is_object( $term ) || empty( $term->term_id ) ) {
                continue;
            }
            $html .= sprintf(
                '<option value="%1$s" %2$s>%3$s</option>',
                esc_attr( $term->term_id ),
                selected( $selected_id, (int) $term->term_id, false ),
                esc_html( $term->name )
            );
        }
        return $html;
    }
}

/* ════════════════════════════════════════
   ۵. شورت‌کد نمایش فرم ثبت آگهی دست‌دوم
   ════════════════════════════════════════ */
add_shortcode( 'glass_submit_portfolio', 'glass_render_portfolio_submission_form' );
function glass_render_portfolio_submission_form() {
    global $glass_pf_errors, $glass_pf_success, $glass_pf_saved_fields, $glass_pf_is_crypto_tx, $glass_pf_edit_id;

    $current_lang = function_exists('pll_current_language') ? pll_current_language('slug') : 'fa';

    $pay_enabled    = (bool) get_theme_mod( 'glass_pf_pay_enabled', false );
    $pay_amount     = absint( get_theme_mod( 'glass_pf_pay_amount', 50000 ) );
    $crypto_enabled = (bool) get_theme_mod( 'glass_pf_crypto_enabled', false );
    $crypto_amount  = absint( get_theme_mod( 'glass_pf_crypto_amount', 5 ) );
    $crypto_wallet  = sanitize_text_field( get_theme_mod( 'glass_pf_crypto_wallet', '' ) );

    // Frontend edit mode: /submit-page/?glass_edit_ad=ID&glass_edit_nonce=...
    $glass_pf_edit_id = 0;
    if ( isset( $_GET['glass_edit_ad'], $_GET['glass_edit_nonce'] ) && is_user_logged_in() ) {
        $maybe_edit_id = absint( wp_unslash( $_GET['glass_edit_ad'] ) );
        $nonce_ok = wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['glass_edit_nonce'] ) ), 'glass_edit_ad_' . $maybe_edit_id );
        $edit_post = $maybe_edit_id ? get_post( $maybe_edit_id ) : null;
        if ( $nonce_ok && $edit_post && 'portfolio' === $edit_post->post_type && ( (int) $edit_post->post_author === get_current_user_id() || current_user_can( 'manage_options' ) ) ) {
            $glass_pf_edit_id = $maybe_edit_id;
            $terms = get_the_terms( $maybe_edit_id, 'themsah_theme_type' );
            $glass_pf_saved_fields = [
                'name'             => get_post_meta( $maybe_edit_id, 'portfolio_author_name', true ),
                'title'            => get_the_title( $maybe_edit_id ),
                'category'         => ( $terms && ! is_wp_error( $terms ) ) ? (int) $terms[0]->term_id : 0,
                'city'             => get_post_meta( $maybe_edit_id, 'portfolio_city', true ),
                'excerpt'          => $edit_post->post_excerpt,
                'content'          => $edit_post->post_content,
                'phone'            => get_post_meta( $maybe_edit_id, 'portfolio_phone', true ),
                'crypto_txid'      => get_post_meta( $maybe_edit_id, 'portfolio_crypto_txid', true ),
                'price'            => get_post_meta( $maybe_edit_id, 'portfolio_price', true ),
                'old_price'        => get_post_meta( $maybe_edit_id, 'portfolio_old_price', true ),
                'discount_percent' => get_post_meta( $maybe_edit_id, 'portfolio_discount_percent', true ),
            ];
        }
    }

    $glass_pf_saved_fields = wp_parse_args( (array) $glass_pf_saved_fields, [
        'name' => '', 'title' => '', 'category' => '', 'city' => '', 'excerpt' => '', 'content' => '', 'phone' => '', 'crypto_txid' => '',
        'price' => '', 'old_price' => '', 'discount_percent' => '',
    ] );

    ob_start();
    ?>

    <style>
        .glass-submit-form-wrap {
            max-width: 700px;
            margin: 40px auto;
            padding: 32px;
            background: var(--fl-glass-bg, #ffffff);
            border: 1px solid var(--fl-glass-border, rgba(0,0,0,0.07));
            border-radius: var(--fl-radius, 20px);
            box-shadow: var(--fl-shadow, 0 12px 40px rgba(0, 0, 0, 0.06));
            direction: rtl;
            text-align: right;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }


        .glass-upload-overlay{display:none;position:fixed;inset:0;z-index:99999;align-items:center;justify-content:center;padding:20px;background:rgba(15,23,42,.72);backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px)}
        .glass-upload-overlay.is-active{display:flex}
        .glass-upload-overlay[aria-hidden="true"]{visibility:hidden;pointer-events:none}
        .glass-upload-overlay[aria-hidden="false"]{visibility:visible;pointer-events:auto}
        .glass-upload-card{width:min(420px,100%);background:#ffffff;border:1px solid #ffffff;border-radius:22px;padding:28px 24px;text-align:center;box-shadow:0 24px 80px rgba(0,0,0,.28);color:#17212B}
        .glass-upload-spinner{width:58px;height:58px;border-radius:999px;border:5px solid rgba(45,95,147,.16);border-top-color:var(--fl-primary,#2D5F93);margin:0 auto 18px;animation:glassUploadSpin .85s linear infinite}
        .glass-upload-title{font-size:1rem;font-weight:600;margin-bottom:8px}.glass-upload-desc{font-size:.84rem;color:#334155;line-height:1.7;margin:0 0 16px}.glass-upload-bar{height:8px;background:#e2e8f0;border-radius:999px;overflow:hidden}.glass-upload-bar span{display:block;height:100%;width:45%;background:linear-gradient(90deg,var(--fl-primary,#2D5F93),var(--fl-accent,#A4B400));border-radius:999px;animation:glassUploadBar 1.15s ease-in-out infinite}
        .glass-btn-submit[disabled]{opacity:.62;cursor:not-allowed;filter:grayscale(.2)}
        @keyframes glassUploadSpin{to{transform:rotate(360deg)}}@keyframes glassUploadBar{0%{transform:translateX(130%)}100%{transform:translateX(-240%)}}

        .dark-mode .glass-submit-form-wrap {
            background: rgba(15, 23, 36, 0.92);
            border-color: rgba(255,255,255,0.08);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
        }

        .glass-form-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--fl-text, #17212B);
            margin-bottom: 8px;
            text-align: center;
        }

        .dark-mode .glass-form-title {
            color: var(--fl-white, #FFFFFF);
        }

        .glass-form-desc {
            font-size: 0.9rem;
            color: #334155;
            text-align: center;
            margin-bottom: 28px;
        }

        .glass-form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .glass-form-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--fl-text, #17212B);
            margin-bottom: 8px;
        }

        .dark-mode .glass-form-label {
            color: #ffffff;
        }

        .glass-form-label svg {
            color: var(--fl-primary, #2D5F93);
        }

        .glass-form-input, 
        .glass-form-textarea {
            width: 100%;
            padding: 12px 16px;
            font-size: 0.95rem;
            font-family: inherit;
            color: var(--fl-text, #17212B);
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.12);
            border-radius: 12px;
            box-sizing: border-box;
            outline: none;
            transition: all 0.25s ease;
        }

        .dark-mode .glass-form-input,
        .dark-mode .glass-form-textarea {
            background: #0F1724;
            border-color: rgba(255,255,255,0.10);
            color: #e2e8f0;
        }

        /* استایل اختصاصی منوهای کشویی در حالت دارک مود برای خوانایی ۱۰۰٪ متن‌ها */
        .dark-mode select.glass-form-input {
            background-color: #0F1724 !important;
            color: #e2e8f0 !important;
        }
        .dark-mode select.glass-form-input option {
            background-color: #0F1724 !important;
            color: #e2e8f0 !important;
        }

        .glass-form-input:focus, 
        .glass-form-textarea:focus {
            border-color: var(--fl-primary, #2D5F93);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(45, 95, 147, 0.15);
        }

        .dark-mode .glass-form-input:focus, 
        .dark-mode .glass-form-textarea:focus {
            background: #0F1724;
            border-color: #2D5F93;
            box-shadow: 0 0 0 3px rgba(45, 95, 147, 0.25);
        }

        .glass-form-textarea {
            resize: vertical;
            min-height: 120px;
        }

        .glass-form-hint {
            font-size: 0.78rem;
            color: #334155;
            margin-top: 5px;
        }

        .glass-file-upload-box {
            border: 2px dashed rgba(45, 95, 147, 0.3);
            background: #ffffff;
            border-radius: 14px;
            padding: 24px;
            text-align: center;
            cursor: pointer;
            transition: all 0.25s ease;
        }

        .dark-mode .glass-file-upload-box {
            border-color: rgba(255,255,255,0.10);
            background: #162B3F;
        }

        .glass-file-upload-box:hover {
            border-color: var(--fl-primary, #2D5F93);
            background: #ffffff;
        }

        .dark-mode .glass-file-upload-box:hover {
            border-color: #2D5F93;
            background: #1F3D57;
        }

        .glass-file-upload-box svg {
            color: var(--fl-primary, #2D5F93);
            margin-bottom: 10px;
        }

        .dark-mode .glass-file-upload-box svg {
            color: #7FB3E8;
        }

        .glass-file-upload-box p {
            margin: 0;
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--fl-text, #17212B);
        }

        .dark-mode .glass-file-upload-box p {
            color: #e2e8f0;
        }

        .glass-file-input {
            display: none;
        }

        .glass-file-list {
            margin-top: 12px;
            font-size: 0.8rem;
            color: #334155;
            list-style: none;
            padding: 0;
        }

        .glass-file-list li {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 4px;
        }

        .glass-file-list li::before {
            content: '✓';
            color: #17212b;
            font-weight: bold;
        }

        .glass-btn-submit {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 14px;
            font-size: 1rem;
            font-weight: 700;
            color: #fff;
            background: var(--fl-primary, #2D5F93);
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.25s ease;
            box-shadow: 0 4px 12px rgba(45, 95, 147, 0.2);
        }

        .glass-btn-submit:hover {
            background: var(--fl-primary-dark, #1B4A73);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(45, 95, 147, 0.3);
        }

        .glass-alert {
            padding: 14px 18px;
            border-radius: 12px;
            font-size: 0.9rem;
            margin-bottom: 24px;
            line-height: 1.6;
        }

        .glass-alert-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #334155;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .glass-alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: #17212b;
            border: 1px solid rgba(16, 185, 129, 0.2);
            text-align: center;
        }

        .glass-alert-success svg {
            margin: 0 auto 12px;
            display: block;
            color: #17212b;
        }

        .glass-required {
            color: #334155;
            margin-right: 3px;
        }

        .glass-payment-badge {
            background: rgba(45, 95, 147, 0.1);
            border: 1px solid rgba(45, 95, 147, 0.2);
            color: #1e40af;
            padding: 12px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 20px;
        }

        .dark-mode .glass-payment-badge {
            background: rgba(45, 95, 147, 0.15);
            border-color: rgba(45, 95, 147, 0.3);
            color: #7FB3E8;
        }
    </style>

    <div class="glass-submit-form-wrap">
        
        <?php if ( $glass_pf_success ) : ?>
            <div class="glass-alert glass-alert-success">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                <?php if ( $glass_pf_is_crypto_tx ) : ?>
                    <h3><?php _e( 'Your ad has been submitted successfully!', 'glassmorphism-child-pro' ); ?></h3>
                    <p style="margin-top: 10px; line-height: 1.7;"><?php _e( 'We have received your payment transaction ID. Our administrator is verifying your transaction. Your ad will be published as soon as the transaction is confirmed!', 'glassmorphism-child-pro' ); ?></p>
                <?php else : ?>
                    <h3><?php echo esc_html( glass_t('ok_title') ); ?></h3>
                    <p><?php echo esc_html( glass_t('ok_desc') ); ?></p>
                <?php endif; ?>
            </div>
        <?php else : ?>

            <h2 class="glass-form-title"><?php echo esc_html( ! empty( $glass_pf_edit_id ) ? __( 'ویرایش آگهی', 'glassmorphism-child-pro' ) : glass_t('form_title') ); ?></h2>
            <p class="glass-form-desc"><?php echo esc_html( glass_t('form_desc') ); ?></p>

            <?php
            // ── جعبه هوشمند ورود/ثبت‌نام (همه‌ی زبان‌ها — بدون لاگین هم می‌توان آگهی ثبت کرد) ──
            if ( function_exists( 'glass_render_auth_prompt' ) ) {
                echo glass_render_auth_prompt();
            }
            ?>

            <?php if ( $current_lang === 'fa' && $pay_enabled ) : ?>
                <div class="glass-payment-badge">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-left: 5px;"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                    <?php printf( esc_html( glass_t('pay_badge') ), number_format($pay_amount) ); ?>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $glass_pf_errors ) ) : ?>
                <div class="glass-alert glass-alert-danger">
                    <ul style="margin: 0; padding-right: 20px;">
                        <?php foreach ( $glass_pf_errors as $error ) : ?>
                            <li><?php echo esc_html( $error ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="" method="post" enctype="multipart/form-data">
                <?php wp_nonce_field( 'glass_submit_portfolio_action', 'glass_pf_nonce' ); ?>
                <?php if ( ! empty( $glass_pf_edit_id ) ) : ?><input type="hidden" name="glass_edit_ad_id" value="<?php echo esc_attr( $glass_pf_edit_id ); ?>"><?php endif; ?>

                <!-- نام و نام خانوادگی -->
                <div class="glass-form-group">
                    <label class="glass-form-label" for="pf_name">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <?php echo esc_html( glass_t('f_name') ); ?><span class="glass-required">*</span>
                    </label>
                    <input type="text" id="pf_name" name="pf_name" class="glass-form-input" placeholder="<?php echo esc_attr( glass_t('f_name_ph') ); ?>" value="<?php echo esc_attr( $glass_pf_saved_fields['name'] ); ?>" required />
                </div>

                <!-- عنوان آگهی -->
                <div class="glass-form-group">
                    <label class="glass-form-label" for="pf_title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                        <?php echo esc_html( glass_t('f_title') ); ?><span class="glass-required">*</span>
                    </label>
                    <input type="text" id="pf_title" name="pf_title" class="glass-form-input" placeholder="<?php echo esc_attr( glass_t('f_title_ph') ); ?>" value="<?php echo esc_attr( $glass_pf_saved_fields['title'] ); ?>" required />
                </div>

                <!-- قیمت اختیاری و تخفیف -->
                <div class="glass-form-group">
                    <label class="glass-form-label" for="pf_price">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1v22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6"/></svg>
                        <?php echo esc_html( function_exists('glass_ui_extra_t') ? glass_ui_extra_t('price_label') : __( 'قیمت آگهی', 'glassmorphism-child-pro' ) ); ?> <small style="font-weight:400;color:var(--fl-text-light)"><?php echo esc_html( function_exists('glass_ui_extra_t') ? '(' . glass_ui_extra_t('optional') . ')' : __( '(اختیاری)', 'glassmorphism-child-pro' ) ); ?></small>
                    </label>
                    <input type="number" min="0" step="1000" id="pf_price" name="pf_price" class="glass-form-input" placeholder="<?php echo esc_attr( function_exists('glass_ui_extra_t') ? glass_ui_extra_t('price_placeholder') : __( 'مثلاً 2500000 — خالی یعنی توافقی', 'glassmorphism-child-pro' ) ); ?>" value="<?php echo esc_attr( $glass_pf_saved_fields['price'] ); ?>" />
                    <p class="glass-form-hint"><?php echo esc_html( function_exists('glass_ui_extra_t') ? glass_ui_extra_t('price_hint') : __( 'اگر قیمت را وارد نکنید، در آگهی «توافقی» نمایش داده می‌شود.', 'glassmorphism-child-pro' ) ); ?></p>
                </div>

                <div class="glass-form-group" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label class="glass-form-label" for="pf_old_price"><?php echo esc_html( function_exists('glass_ui_extra_t') ? glass_ui_extra_t('old_price') : __( 'قیمت قبل تخفیف', 'glassmorphism-child-pro' ) ); ?> <small style="font-weight:400;color:var(--fl-text-light)"><?php echo esc_html( function_exists('glass_ui_extra_t') ? '(' . glass_ui_extra_t('optional') . ')' : __( '(اختیاری)', 'glassmorphism-child-pro' ) ); ?></small></label>
                        <input type="number" min="0" step="1000" id="pf_old_price" name="pf_old_price" class="glass-form-input" value="<?php echo esc_attr( $glass_pf_saved_fields['old_price'] ); ?>" />
                    </div>
                    <div>
                        <label class="glass-form-label" for="pf_discount_percent"><?php echo esc_html( function_exists('glass_ui_extra_t') ? glass_ui_extra_t('discount_percent') : __( 'درصد تخفیف ویژه', 'glassmorphism-child-pro' ) ); ?> <small style="font-weight:400;color:var(--fl-text-light)"><?php echo esc_html( function_exists('glass_ui_extra_t') ? '(' . glass_ui_extra_t('optional') . ')' : __( '(اختیاری)', 'glassmorphism-child-pro' ) ); ?></small></label>
                        <input type="number" min="0" max="99" step="1" id="pf_discount_percent" name="pf_discount_percent" class="glass-form-input" value="<?php echo esc_attr( $glass_pf_saved_fields['discount_percent'] ); ?>" />
                    </div>
                </div>

                <!-- انتخاب دسته‌بندی آگهی -->
                <div class="glass-form-group">
                    <label class="glass-form-label" for="pf_category">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                        <?php echo esc_html( glass_t('f_category') ); ?><span class="glass-required">*</span>
                    </label>
                    <select id="pf_category" name="pf_category" class="glass-form-input" required style="appearance: auto; padding: 10px 16px; background-color: #ffffff; cursor: pointer;">
                        <option value=""><?php echo esc_html( glass_t('f_category_ph') ); ?></option>
                        <?php echo glass_pro_submit_category_options( $glass_pf_saved_fields['category'] ); ?>
                    </select>
                </div>

                <!-- شهر آگهی (کادر متنی ساده) -->
                <div class="glass-form-group">
                    <label class="glass-form-label" for="pf_city">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <?php echo esc_html( glass_t('f_city') ); ?><span class="glass-required">*</span>
                    </label>
                    <input type="text" id="pf_city" name="pf_city" class="glass-form-input"
                        placeholder="<?php echo esc_attr( glass_t('f_city_ph') ); ?>"
                        value="<?php echo esc_attr( $glass_pf_saved_fields['city'] ); ?>"
                        maxlength="50" required />
                </div>

                <!-- توضیح کوتاه -->
                <div class="glass-form-group">
                    <label class="glass-form-label" for="pf_excerpt">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        <?php echo esc_html( glass_t('f_excerpt') ); ?>
                    </label>
                    <textarea id="pf_excerpt" name="pf_excerpt" class="glass-form-textarea" placeholder="<?php echo esc_attr( glass_t('f_excerpt_ph') ); ?>" maxlength="160"><?php echo esc_textarea( $glass_pf_saved_fields['excerpt'] ); ?></textarea>
                    <p class="glass-form-hint"><?php echo esc_html( glass_t('f_excerpt_hint') ); ?></p>
                </div>

                <!-- متن آگهی -->
                <div class="glass-form-group">
                    <label class="glass-form-label" for="pf_content">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="21" y1="10" x2="3" y2="10"/><line x1="21" y1="6" x2="3" y2="6"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="21" y1="18" x2="3" y2="18"/></svg>
                        <?php echo esc_html( glass_t('f_content') ); ?><span class="glass-required">*</span>
                    </label>
                    <textarea id="pf_content" name="pf_content" class="glass-form-textarea" style="min-height: 180px;" placeholder="<?php echo esc_attr( glass_t('f_content_ph') ); ?>" required><?php echo esc_textarea( $glass_pf_saved_fields['content'] ); ?></textarea>
                </div>

                <!-- آپلود تصاویر -->
                <div class="glass-form-group">
                    <label class="glass-form-label">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        <?php echo esc_html( glass_t('f_images') ); ?>
                    </label>
                    <div class="glass-file-upload-box" onclick="document.getElementById('pf_images').click();">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="17 8 12 3 7 8"/>
                            <line x1="12" y1="3" x2="12" y2="15"/>
                        </svg>
                        <p><?php echo esc_html( glass_t('f_images_click') ); ?></p>
                        <span style="font-size:0.72rem; color:var(--fl-text-light); margin-top:5px; display:block;"><?php echo esc_html( glass_t('f_images_hint') ); ?></span>
                    </div>
                    <input type="file" id="pf_images" name="pf_images[]" class="glass-file-input" multiple accept=".jpg,.jpeg,.png" onchange="glassDisplayFileNames();" />
                    <input type="hidden" id="pf_uploaded_image_ids" name="pf_uploaded_image_ids" value="" />
                    <div id="glass_upload_status" class="glass-form-hint" style="display:none;margin-top:8px;font-weight:700;color:var(--fl-primary,#2D5F93);"></div>
                    <ul class="glass-file-list" id="glass_file_list"></ul>
                </div>

                <script <?php if ( function_exists( 'glass_pro_csp_nonce_attr' ) ) { glass_pro_csp_nonce_attr(); } ?>>
                    window.glassPfUpload = {
                        ajaxUrl: <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>,
                        nonce: <?php echo wp_json_encode( wp_create_nonce( 'glass_pf_upload_images' ) ); ?>,
                        uploading: false,
                        uploadedIds: []
                    };
                    function glassSetSubmitDisabled(disabled) {
                        var form = document.querySelector('.glass-submit-form-wrap form[enctype="multipart/form-data"]');
                        if (!form) return;
                        form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(function(btn){
                            btn.disabled = !!disabled;
                            if (disabled) btn.setAttribute('aria-disabled','true'); else btn.removeAttribute('aria-disabled');
                        });
                    }
                    function glassDisplayFileNames() {
                        const input = document.getElementById('pf_images');
                        const list = document.getElementById('glass_file_list');
                        const hidden = document.getElementById('pf_uploaded_image_ids');
                        const status = document.getElementById('glass_upload_status');
                        if (!input || !list) return;
                        list.innerHTML = '';
                        if (hidden) hidden.value = '';
                        window.glassPfUpload.uploadedIds = [];

                        if (input.files.length > 5) {
                            alert('شما مجاز به انتخاب حداکثر ۵ تصویر هستید.');
                            input.value = '';
                            return;
                        }

                        const MAX_UPLOAD_MB = 8;
                        const MAX_UPLOAD_BYTES = MAX_UPLOAD_MB * 1024 * 1024;
                        for (let i = 0; i < input.files.length; i++) {
                            const file = input.files[i];
                            if (file.size > MAX_UPLOAD_BYTES) {
                                alert(`فایل "${file.name}" بزرگتر از ${MAX_UPLOAD_MB} مگابایت است و حذف گردید.`);
                                input.value = '';
                                list.innerHTML = '';
                                return;
                            }
                            const li = document.createElement('li');
                            li.textContent = `${file.name} (${(file.size / 1024 / 1024).toFixed(2)} مگابایت) — در انتظار آپلود`;
                            list.appendChild(li);
                        }
                        if (input.files.length > 0) {
                            glassUploadSelectedImages();
                        }
                    }
                    function glassUploadSelectedImages() {
                        var input = document.getElementById('pf_images');
                        var hidden = document.getElementById('pf_uploaded_image_ids');
                        var list = document.getElementById('glass_file_list');
                        var status = document.getElementById('glass_upload_status');
                        if (!input || !input.files || input.files.length === 0 || !window.FormData || !window.XMLHttpRequest) return;
                        var fd = new FormData();
                        fd.append('action', 'glass_pf_upload_images');
                        fd.append('nonce', window.glassPfUpload.nonce);
                        for (var i=0; i<input.files.length; i++) fd.append('pf_images_ajax[]', input.files[i], input.files[i].name);
                        var xhr = new XMLHttpRequest();
                        window.glassPfUpload.uploading = true;
                        glassSetSubmitDisabled(true);
                        if (status) { status.style.display='block'; status.textContent='در حال آپلود تصاویر... 0٪'; }
                        xhr.upload.onprogress = function(e){
                            if (e.lengthComputable && status) {
                                var pct = Math.round((e.loaded/e.total)*100);
                                status.textContent = 'در حال آپلود تصاویر... ' + pct + '٪';
                            }
                        };
                        xhr.onreadystatechange = function(){
                            if (xhr.readyState !== 4) return;
                            window.glassPfUpload.uploading = false;
                            glassSetSubmitDisabled(false);
                            try {
                                var res = JSON.parse(xhr.responseText || '{}');
                                if (xhr.status >= 200 && xhr.status < 300 && res.success && res.data && res.data.images) {
                                    var ids = res.data.images.map(function(img){ return img.id; });
                                    window.glassPfUpload.uploadedIds = ids;
                                    if (hidden) hidden.value = ids.join(',');
                                    if (list) {
                                        list.innerHTML='';
                                        res.data.images.forEach(function(img){
                                            var li=document.createElement('li');
                                            li.textContent = img.name + ' — آپلود شد';
                                            list.appendChild(li);
                                        });
                                    }
                                    if (status) { status.textContent='تصاویر با موفقیت آپلود شدند. اکنون می‌توانید آگهی را ثبت کنید.'; }
                                    input.value = ''; // prevent duplicate fallback upload
                                } else {
                                    var msg = (res.data && res.data.message) ? res.data.message : 'آپلود تصاویر ناموفق بود.';
                                    if (status) { status.textContent = msg; status.style.color = '#ef4444'; }
                                    alert(msg);
                                }
                            } catch(err) {
                                if (status) { status.textContent='پاسخ آپلود نامعتبر بود.'; status.style.color='#ef4444'; }
                            }
                        };
                        xhr.open('POST', window.glassPfUpload.ajaxUrl, true);
                        xhr.send(fd);
                    }
                    document.addEventListener('DOMContentLoaded', function () {
                        var form = document.querySelector('.glass-submit-form-wrap form[enctype="multipart/form-data"]');
                        var overlay = document.getElementById('glassUploadOverlay');
                        if (!form) return;
                        form.addEventListener('submit', function (e) {
                            if (window.glassPfUpload && window.glassPfUpload.uploading) {
                                e.preventDefault();
                                alert('لطفاً تا پایان آپلود تصاویر صبر کنید.');
                                return false;
                            }
                            if (form.dataset.submitting === '1') { e.preventDefault(); return false; }
                            if (typeof form.checkValidity === 'function' && !form.checkValidity()) return true;
                            form.dataset.submitting = '1';
                            glassSetSubmitDisabled(true);
                            if (overlay) {
                                // Show the blocking progress layer for every
                                // valid submit, not only after AJAX images were
                                // uploaded. Direct uploads and payment requests
                                // also need feedback while the page is waiting.
                                overlay.classList.add('is-active');
                                overlay.setAttribute('aria-hidden', 'false');
                                form.setAttribute('aria-busy', 'true');
                            }
                            return true;
                        });
                    });
                </script>

                <!-- شماره تماس -->
                <div class="glass-form-group">
                    <label class="glass-form-label" for="pf_phone">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        <?php echo esc_html( glass_t('f_phone') ); ?><span class="glass-required">*</span>
                    </label>
                    <input type="tel" id="pf_phone" name="pf_phone" class="glass-form-input" style="text-align: left; direction: ltr;" placeholder="09121234567" value="<?php echo esc_attr( $glass_pf_saved_fields['phone'] ); ?>" required />
                    <p class="glass-form-hint"><?php echo esc_html( glass_t('f_phone_hint') ); ?></p>
                </div>

                <!-- ─── درگاه پرداخت کریپتو تتر (مخصوص صفحات خارجی) ─── -->
                <?php if ( $current_lang !== 'fa' && $crypto_enabled && ! empty( $crypto_wallet ) ) : ?>
                    <div class="glass-payment-badge" style="text-align: left; padding: 24px; background: rgba(56, 189, 248, 0.08); border-color: rgba(56, 189, 248, 0.2); color: #fff;">
                        <div style="display: flex; align-items: center; gap: 8px; color: #38bdf8; font-size: 1.05rem; font-weight: 700; margin-bottom: 12px;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 6v12M9 9h6M9 15h6"/></svg>
                            Classified Ad Submission Fee (Crypto USDT TRC-20)
                        </div>
                        <p style="font-size: 0.84rem; line-height: 1.6; margin-bottom: 15px; color: #ffffff;">
                            Submission fee for each classified ad on our English site is <strong><?php echo esc_html($crypto_amount); ?> USDT (TRC-20)</strong>. 
                            Please transfer the exact amount to our official wallet address below, copy your transaction hash (TxID), and paste it in the field below.
                        </p>
                        
                        <!-- آدرس ولت تتر با دکمه کپی فوری -->
                        <div style="background: rgba(0, 0, 0, 0.25); border-radius: 10px; padding: 12px; display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 18px; border: 1px solid rgba(0,0,0,0.07);">
                            <span id="glassCryptoWallet" style="font-family: monospace; font-size: 0.88rem; color: #38bdf8; overflow-wrap: anywhere; word-break: break-all; letter-spacing: 0.5px;"><?php echo esc_html($crypto_wallet); ?></span>
                            <button type="button" onclick="glassCopyCryptoWallet()" style="background: #38bdf8; color: #000; border: none; border-radius: 6px; padding: 6px 12px; font-size: 0.76rem; font-weight: 700; cursor: pointer; transition: 0.2s; white-space: nowrap;">Copy Wallet</button>
                        </div>

                        <!-- فیلد کد هش تراکنش -->
                        <div class="glass-form-group" style="margin-bottom: 0;">
                            <label class="glass-form-label" for="pf_crypto_txid" style="color: #fff; font-size: 0.84rem;">
                                <?php _e( 'Paste Your Transaction Hash (TxID) Here', 'glassmorphism-child-pro' ); ?><span class="glass-required">*</span>
                            </label>
                            <input type="text" id="pf_crypto_txid" name="pf_crypto_txid" class="glass-form-input" style="background: rgba(0,0,0,0.2); border-color: rgba(56,189,248,0.3); color: #fff; font-family: monospace;" placeholder="e.g. 5c2a12ff32bb872fa692c..." value="<?php echo esc_attr( $glass_pf_saved_fields['crypto_txid'] ); ?>" required />
                        </div>
                    </div>

                    <script <?php if ( function_exists( 'glass_pro_csp_nonce_attr' ) ) { glass_pro_csp_nonce_attr(); } ?>>
                        function glassCopyCryptoWallet() {
                            const walletText = document.getElementById('glassCryptoWallet').textContent;
                            navigator.clipboard.writeText(walletText).then(() => {
                                alert('Tether USDT (TRC-20) wallet address copied to clipboard!');
                            }).catch(err => {
                                console.error('Could not copy wallet address:', err);
                            });
                        }
                    </script>
                <?php endif; ?>

                <!-- دکمه ارسال -->
                <button type="submit" name="glass_pf_submit" class="glass-btn-submit" style="margin-top: 10px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    <?php 
                    if ( $current_lang === 'fa' ) {
                        echo $pay_enabled ? glass_t('btn_submit_pay') : glass_t('btn_submit');
                    } else {
                        echo $crypto_enabled ? __( 'Submit Ad & Verify Payment', 'glassmorphism-child-pro' ) : __( 'Submit Classified Ad', 'glassmorphism-child-pro' ); 
                    }
                    ?>
                </button>
                <div class="glass-upload-overlay" id="glassUploadOverlay" aria-hidden="true" role="status" aria-live="polite">
                    <div class="glass-upload-card">
                        <div class="glass-upload-spinner" aria-hidden="true"></div>
                        <div class="glass-upload-title"><?php esc_html_e( 'در حال بارگذاری تصاویر و ثبت آگهی...', 'glassmorphism-child-pro' ); ?></div>
                        <p class="glass-upload-desc"><?php esc_html_e( 'لطفاً تا پایان آپلود تصاویر این صفحه را نبندید و دکمه بازگشت را نزنید.', 'glassmorphism-child-pro' ); ?></p>
                        <div class="glass-upload-bar"><span></span></div>
                    </div>
                </div>
            </form>

        <?php endif; ?>

    </div>

    <?php
    return ob_get_clean();
}

