<?php
/** User Panel — Login / Register / Lost Password (shortcode [glass_user_login]) */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ════════════════════════════════════════
   4. لاگین / ثبت‌نام / فراموشی رمز
   ════════════════════════════════════════
   شورت‌کد: [glass_user_login]
*/

/**
 * Prevent login for newly registered users whose email verification state is
 * explicitly pending. Existing users without this meta remain compatible, and
 * administrators are not locked out if the setting is enabled on a live site.
 *
 * @param WP_User|WP_Error|null $user     Authentication result.
 * @param string               $username Submitted username.
 * @param string               $password Submitted password.
 * @return WP_User|WP_Error|null
 */
if ( ! function_exists( 'glass_pro_block_unverified_email_login' ) ) {
    add_filter( 'authenticate', 'glass_pro_block_unverified_email_login', 35, 3 );
    function glass_pro_block_unverified_email_login( $user, string $username, string $password ) {
        if ( is_wp_error( $user ) || ! $user instanceof WP_User ) {
            return $user;
        }

        $require_verify = (bool) apply_filters( 'glass_pro/register/require_email_verification', false );
        if ( ! $require_verify || user_can( $user, 'manage_options' ) ) {
            return $user;
        }

        // Only an explicit pending value is blocked; absent meta is treated as
        // verified for backward compatibility with existing accounts.
        $verified = get_user_meta( $user->ID, 'glass_pro_email_verified', true );
        if ( '0' !== (string) $verified ) {
            return $user;
        }

        return new WP_Error(
            'glass_pro_email_unverified',
            __( 'برای ورود ابتدا باید ایمیل خود را تأیید کنید.', 'glassmorphism-child-pro' )
        );
    }
}

add_shortcode( 'glass_user_login', 'glass_render_login_panel' );
function glass_render_login_panel() {

    // [FIX] اگر از قبل لاگین است → ریدایرکت مستقیم به داشبورد
    if ( is_user_logged_in() ) {
        $dash = function_exists( 'glass_get_dashboard_url' )
                ? glass_get_dashboard_url()
                : home_url( '/' );
        wp_safe_redirect( $dash );
        exit;
    }

    $notice = '';
    $notice_type = '';

    // Phase 5: اگر Core فعال است، پردازش login/register/lostpass توسط Core انجام می‌شود و قالب فقط UI را رندر می‌کند.
    $glass_core_auth_handled = false;
    if ( function_exists( 'glass_core_process_auth_forms' ) ) {
        $glass_core_auth_result = glass_core_process_auth_forms();
        if ( ! empty( $glass_core_auth_result['handled'] ) ) {
            $glass_core_auth_handled = true;
            $notice      = isset( $glass_core_auth_result['notice'] ) ? (string) $glass_core_auth_result['notice'] : '';
            $notice_type = isset( $glass_core_auth_result['notice_type'] ) ? (string) $glass_core_auth_result['notice_type'] : '';
        }
    }

    /* ── پردازش فرم‌ها ── */
    if ( ! $glass_core_auth_handled && $_SERVER['REQUEST_METHOD'] === 'POST' ) {

        // ورود
        if ( isset( $_POST['glass_login_submit'] ) && isset( $_POST['glass_login_nonce'] )
             && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['glass_login_nonce'] ) ), 'glass_login_action' ) ) {

            if ( ! glass_captcha_verify() ) {
                $notice = glass_t('msg_captcha');
                $notice_type = 'error';
            } else {
            $creds = [
                'user_login'    => sanitize_text_field( wp_unslash( $_POST['glass_log'] ?? '' ) ),
                'user_password' => wp_unslash( $_POST['glass_pwd'] ?? '' ),
                'remember'      => ! empty( $_POST['glass_remember'] ),
            ];
            $user = wp_signon( $creds, is_ssl() );
            if ( is_wp_error( $user ) ) {
                $notice = 'glass_pro_email_unverified' === $user->get_error_code()
                    ? __( 'برای ورود ابتدا باید ایمیل خود را تأیید کنید.', 'glassmorphism-child-pro' )
                    : glass_t('msg_login_err');
                $notice_type = 'error';
            } else {
                $dash = function_exists( 'glass_get_dashboard_url' )
                        ? glass_get_dashboard_url()
                        : home_url( '/' );
                wp_safe_redirect( $dash );
                exit;
            }
            } // پایان else کپچا
        }

        // ثبت‌نام
        if ( isset( $_POST['glass_register_submit'] ) && isset( $_POST['glass_register_nonce'] )
             && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['glass_register_nonce'] ) ), 'glass_register_action' ) ) {

            // Rate-limit: حداکثر ۳ ثبت‌نام موفق/ناموفق از یک IP در ۱۵ دقیقه (جلوگیری از اسپم حساب).
            if ( function_exists( 'glass_pro_rate_limit_is_blocked' )
                 && glass_pro_rate_limit_is_blocked( 'register', 3, 15 * MINUTE_IN_SECONDS ) ) {
                $notice      = esc_html__( 'تعداد تلاش‌های ثبت‌نام بیش از حد مجاز است. لطفاً چند دقیقه بعد دوباره تلاش کنید.', 'glassmorphism-child-pro' );
                $notice_type = 'error';
            } else {
                $username = sanitize_user( wp_unslash( $_POST['glass_reg_user'] ?? '' ) );
                $email    = sanitize_email( wp_unslash( $_POST['glass_reg_email'] ?? '' ) );
                $pass     = wp_unslash( $_POST['glass_reg_pass'] ?? '' );

                if ( ! glass_captcha_verify() ) {
                    $notice = glass_t('msg_captcha');
                    $notice_type = 'error';
                } elseif ( empty( $username ) || empty( $email ) || empty( $pass ) ) {
                    $notice = glass_t('msg_fill_all');
                    $notice_type = 'error';
                } elseif ( ! is_email( $email ) ) {
                    $notice = glass_t('msg_email_inv');
                    $notice_type = 'error';
                } elseif ( username_exists( $username ) ) {
                    $notice = glass_t('msg_user_exists');
                    $notice_type = 'error';
                } elseif ( email_exists( $email ) ) {
                    $notice = glass_t('msg_email_exists');
                    $notice_type = 'error';
                } elseif ( strlen( $pass ) < 8 ) {
                    // [SEC v5.0.5] حداقل 8 کاراکتر (قبلاً 6) — مطابق NIST 800-63B modern guidelines
                    $notice = glass_t('msg_pass_short');
                    $notice_type = 'error';
                } elseif ( ! preg_match( '/[A-Za-z]/', $pass ) || ! preg_match( '/[0-9]/', $pass ) ) {
                    // [SEC v5.0.5] حداقل یک حرف + یک عدد — جلوگیری از پسوردهای ساده مثل 12345678
                    $notice = glass_t('msg_pass_weak') ?: esc_html__( 'پسورد باید شامل حداقل ۸ کاراکتر، یک حرف و یک عدد باشد.', 'glassmorphism-child-pro' );
                    $notice_type = 'error';
                } else {
                    $uid = wp_create_user( $username, $pass, $email );
                    if ( is_wp_error( $uid ) ) {
                        // افزایش شمارنده در صورت شکست واقعی (نه validation سمت سرور).
                        if ( function_exists( 'glass_pro_rate_limit_hit' ) ) {
                            glass_pro_rate_limit_hit( 'register', 15 * MINUTE_IN_SECONDS );
                        }
                        $notice = glass_t('msg_reg_err') . $uid->get_error_message();
                        $notice_type = 'error';
                    } else {
                        // [SEC v5.0.5] نقش صریح subscriber — جلوگیری از escalation اگر default role در WP تغییر کرده باشد
                        $new_user = new WP_User( $uid );
                        $new_user->set_role( apply_filters( 'glass_pro/register/default_role', 'subscriber' ) );
                        // ثبت‌نام موفق: شمارنده پاک شود.
                        if ( function_exists( 'glass_pro_rate_limit_clear' ) ) {
                            glass_pro_rate_limit_clear( 'register' );
                        }

                        /**
                         * [SEC v5.0.6] Email verification (optional — قابل غیرفعال‌سازی با فیلتر)
                         * اگر فعال باشد، کاربر باید ایمیل را تایید کند قبل از auto-login.
                         */
                        $require_verify = (bool) apply_filters( 'glass_pro/register/require_email_verification', false );
                        if ( $require_verify && function_exists( 'glass_pro_send_verification_email' ) ) {
                            glass_pro_send_verification_email( $uid );
                            update_user_meta( $uid, 'glass_pro_email_verified', 0 );
                            $notice = esc_html__( 'حساب کاربری شما ساخته شد. لطفاً ایمیل خود را برای فعال‌سازی بررسی کنید.', 'glassmorphism-child-pro' );
                            $notice_type = 'success';
                        } else {
                            // رفتار قدیمی: auto-login پس از ثبت‌نام
                            update_user_meta( $uid, 'glass_pro_email_verified', 1 );
                            wp_set_current_user( $uid );
                            wp_set_auth_cookie( $uid, true );

                            /**
                             * Action: glass_pro/user/registered — پس از ثبت‌نام موفق
                             */
                            do_action( 'glass_pro/user/registered', $uid );

                            $dash = function_exists( 'glass_get_dashboard_url' )
                                    ? glass_get_dashboard_url()
                                    : home_url( '/' );
                            wp_safe_redirect( $dash );
                            exit;
                        }
                    }
                }

                // در همه‌ی مسیرهای validation هم یک hit ثبت کنیم تا abuse فرم متوقف شود.
                if ( $notice_type === 'error' && function_exists( 'glass_pro_rate_limit_hit' ) ) {
                    glass_pro_rate_limit_hit( 'register', 15 * MINUTE_IN_SECONDS );
                }
            }
        }

        // فراموشی رمز
        if ( isset( $_POST['glass_lostpass_submit'] ) && isset( $_POST['glass_lostpass_nonce'] )
             && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['glass_lostpass_nonce'] ) ), 'glass_lostpass_action' ) ) {

            // Rate-limit: حداکثر ۳ درخواست بازیابی از یک IP در ۱۵ دقیقه (جلوگیری از اسپم ایمیل/enumeration).
            if ( function_exists( 'glass_pro_rate_limit_is_blocked' )
                 && glass_pro_rate_limit_is_blocked( 'lostpass', 3, 15 * MINUTE_IN_SECONDS ) ) {
                $notice      = esc_html__( 'تعداد درخواست‌های بازیابی رمز بیش از حد مجاز است. لطفاً چند دقیقه بعد دوباره تلاش کنید.', 'glassmorphism-child-pro' );
                $notice_type = 'error';
            } elseif ( ! glass_captcha_verify() ) {
                $notice = glass_t('msg_captcha');
                $notice_type = 'error';
            } else {
                // هر درخواست (موفق یا ناموفق در یافتن کاربر) یک hit حساب می‌شود.
                if ( function_exists( 'glass_pro_rate_limit_hit' ) ) {
                    glass_pro_rate_limit_hit( 'lostpass', 15 * MINUTE_IN_SECONDS );
                }

                $login = sanitize_text_field( wp_unslash( $_POST['glass_lost_login'] ?? '' ) );
                $user_data = is_email( $login ) ? get_user_by( 'email', $login ) : get_user_by( 'login', $login );

                if ( $user_data ) {
                    $key = get_password_reset_key( $user_data );
                    if ( ! is_wp_error( $key ) ) {
                        $reset_url = network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_data->user_login ), 'login' );

                        $site_name   = get_bloginfo( 'name' );
                        $site_domain = wp_parse_url( home_url(), PHP_URL_HOST );
                        /* translators: 1: site name, 2: reset URL */
                        $message_body = sprintf(
                            __( "سلام،\n\nشما درخواست بازنشانی رمز عبور خود را در %1\$s ارسال کرده‌اید.\nبرای ادامه روی لینک زیر کلیک کنید (تا ۲۴ ساعت معتبر است):\n\n%2\$s\n\nاگر این درخواست از طرف شما نبوده، می‌توانید این ایمیل را نادیده بگیرید.", 'glassmorphism-child-pro' ),
                            $site_name,
                            $reset_url
                        );
                        /* translators: %s: site name */
                        $subject = sprintf( __( 'بازنشانی رمز عبور — %s', 'glassmorphism-child-pro' ), $site_name );

                        $from_email = apply_filters(
                            'glass_pro/mail/from_address',
                            'noreply@' . preg_replace( '/^www\./i', '', (string) $site_domain )
                        );
                        $headers = [
                            'Content-Type: text/plain; charset=UTF-8',
                            sprintf( 'From: %s <%s>', $site_name, $from_email ),
                        ];

                        wp_mail( $user_data->user_email, $subject, $message_body, $headers );
                    }
                }
                // پیام یکسان (امنیت: لو ندادن وجود کاربر)
                $notice = glass_t('msg_lost_sent');
                $notice_type = 'success';
            } // پایان else کپچا/rate-limit
        }
    }

    ob_start();
    ?>
    <div class="glass-auth-wrap">
        <div class="glass-auth-card">

            <?php if ( $notice ) : ?>
                <div class="glass-auth-notice glass-auth-notice--<?php echo esc_attr( $notice_type ); ?>">
                    <?php echo esc_html( $notice ); ?>
                </div>
            <?php endif; ?>

            <!-- تب‌ها -->
            <div class="glass-auth-tabs">
                <button type="button" class="glass-auth-tab is-active" data-tab="login"><?php echo esc_html( glass_t('login_tab') ); ?></button>
                <button type="button" class="glass-auth-tab" data-tab="register"><?php echo esc_html( glass_t('register_tab') ); ?></button>
            </div>

            <!-- فرم ورود -->
            <form method="post" class="glass-auth-form" data-panel="login">
                <?php wp_nonce_field( 'glass_login_action', 'glass_login_nonce' ); ?>
                <h2 class="glass-auth-title"><?php echo esc_html( glass_t('login_h') ); ?></h2>

                <div class="glass-auth-field">
                    <label><?php echo esc_html( glass_t('fld_userlogin') ); ?></label>
                    <input type="text" name="glass_log" required autocomplete="username">
                </div>
                <div class="glass-auth-field">
                    <label><?php echo esc_html( glass_t('fld_password') ); ?></label>
                    <input type="password" name="glass_pwd" required autocomplete="current-password">
                </div>
                <label class="glass-auth-remember">
                    <input type="checkbox" name="glass_remember" value="1"> <?php echo esc_html( glass_t('remember') ); ?>
                </label>

                <?php echo glass_captcha_fields(); ?>

                <button type="submit" name="glass_login_submit" class="glass-auth-btn"><?php echo esc_html( glass_t('btn_login') ); ?></button>

                <button type="button" class="glass-auth-link" data-tab="lostpass"><?php echo esc_html( glass_t('link_lost') ); ?></button>
            </form>

            <!-- فرم ثبت‌نام -->
            <form method="post" class="glass-auth-form" data-panel="register" style="display:none;">
                <?php wp_nonce_field( 'glass_register_action', 'glass_register_nonce' ); ?>
                <h2 class="glass-auth-title"><?php echo esc_html( glass_t('register_h') ); ?></h2>

                <div class="glass-auth-field">
                    <label><?php echo esc_html( glass_t('fld_username') ); ?></label>
                    <input type="text" name="glass_reg_user" required autocomplete="username">
                </div>
                <div class="glass-auth-field">
                    <label><?php echo esc_html( glass_t('fld_email') ); ?></label>
                    <input type="email" name="glass_reg_email" required autocomplete="email">
                </div>
                <div class="glass-auth-field">
                    <label><?php echo esc_html( glass_t('fld_pass6') ); ?></label>
                    <input type="password" name="glass_reg_pass" required minlength="8" autocomplete="new-password" pattern="(?=.*[A-Za-z])(?=.*[0-9]).{8,}" title="<?php esc_attr_e( 'حداقل ۸ کاراکتر شامل حرف و عدد', 'glassmorphism-child-pro' ); ?>">
                </div>

                <?php echo glass_captcha_fields(); ?>

                <button type="submit" name="glass_register_submit" class="glass-auth-btn"><?php echo esc_html( glass_t('btn_register') ); ?></button>
            </form>

            <!-- فرم فراموشی رمز -->
            <form method="post" class="glass-auth-form" data-panel="lostpass" style="display:none;">
                <?php wp_nonce_field( 'glass_lostpass_action', 'glass_lostpass_nonce' ); ?>
                <h2 class="glass-auth-title"><?php echo esc_html( glass_t('lost_h') ); ?></h2>
                <p class="glass-auth-sub"><?php echo esc_html( glass_t('lost_sub') ); ?></p>

                <div class="glass-auth-field">
                    <label><?php echo esc_html( glass_t('fld_userlogin') ); ?></label>
                    <input type="text" name="glass_lost_login" required>
                </div>

                <?php echo glass_captcha_fields(); ?>

                <button type="submit" name="glass_lostpass_submit" class="glass-auth-btn"><?php echo esc_html( glass_t('btn_lost') ); ?></button>
                <button type="button" class="glass-auth-link" data-tab="login"><?php echo esc_html( glass_t('link_back') ); ?></button>
            </form>

        </div>
    </div>
    <?php
    return ob_get_clean();
}

