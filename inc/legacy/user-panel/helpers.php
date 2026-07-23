<?php
/** User Panel — URL helpers + Action buttons + Auth prompt */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ════════════════════════════════════════
   1.5. توابع کمکی URL و جعبه‌ها
   ════════════════════════════════════════ */

/* آدرس برگه ثبت آگهی (Polylang-aware با fallback) */
function glass_get_submit_url() {
    $u = get_theme_mod( 'glass_pf_submit_url', '' );

    // اگر در Customizer یک URL فارسی ذخیره شده، تلاش کن صفحه متناظر همان زبان فعلی را برگردانی.
    if ( $u ) {
        $translated = glass_translate_url_to_current_lang( $u );
        if ( $translated ) {
            return $translated;
        }
        return $u;
    }

    // fallback هوشمند: پیدا کردن برگه حاوی shortcode فرم ثبت آگهی.
    $cache_key = 'glass_pro_submit_url_' . ( function_exists( 'glass_current_lang' ) ? glass_current_lang() : get_locale() );
    $cached = get_transient( $cache_key );
    if ( false !== $cached ) {
        return $cached;
    }

    $pages = get_posts( [
        'post_type'      => 'page',
        'posts_per_page' => 1,
        's'              => '[glass_submit_portfolio]',
        'post_status'    => 'publish',
        'no_found_rows'  => true,
    ] );

    if ( ! empty( $pages ) ) {
        $url = get_permalink( $pages[0] );
        $translated = glass_translate_url_to_current_lang( $url );
        $url = $translated ?: $url;
    } else {
        $url = home_url( '/ثبت-آگهی/' );
        $translated = glass_translate_url_to_current_lang( $url );
        $url = $translated ?: $url;
    }

    set_transient( $cache_key, $url, DAY_IN_SECONDS );
    return $url;
}

if ( ! function_exists( 'glass_translate_url_to_current_lang' ) ) {
    function glass_translate_url_to_current_lang( $url ) {
        if ( ! $url || ! function_exists( 'pll_current_language' ) || ! function_exists( 'pll_get_post' ) ) {
            return '';
        }
        $lang = pll_current_language( 'slug' );
        if ( ! $lang ) {
            return '';
        }
        $post_id = url_to_postid( $url );
        if ( ! $post_id ) {
            $path = trim( (string) wp_parse_url( $url, PHP_URL_PATH ), '/' );
            if ( $path ) {
                $page = get_page_by_path( urldecode( basename( $path ) ) );
                if ( $page ) {
                    $post_id = (int) $page->ID;
                }
            }
        }
        if ( ! $post_id ) {
            return '';
        }
        $translated_id = pll_get_post( $post_id, $lang );
        if ( $translated_id ) {
            $translated_url = get_permalink( $translated_id );
            return $translated_url ? $translated_url : '';
        }
        return '';
    }
}

/* آدرس برگه ورود/ثبت‌نام (با fallback) */
function glass_get_login_url() {
    $u = get_theme_mod( 'glass_pf_login_url', '' );
    return $u ? $u : home_url( '/ورود/' );
}

/* آدرس داشبورد (با fallback)
 * [PRO-FIX #1] کش با Transient — جلوگیری از کوئری LIKE گران روی هر بارگذاری صفحه.
 * Transient در save_post/switch_theme پاک می‌شود (glass_pro_flush_url_cache).
 */
function glass_get_dashboard_url() {
    $u = get_theme_mod( 'glass_pf_dashboard_url', '' );
    if ( $u ) return $u;

    $cached = get_transient( 'glass_pro_dashboard_url' );
    if ( false !== $cached ) {
        return $cached;
    }

    // [FIX] Fallback هوشمند: جستجوی برگه حاوی شورت‌کد داشبورد (فقط یک‌بار، سپس کش)
    $pages = get_posts( [
        'post_type'      => 'page',
        'posts_per_page' => 1,
        's'              => '[glass_user_dashboard]',
        'post_status'    => 'publish',
        'no_found_rows'  => true,
    ] );

    $url = ! empty( $pages ) ? get_permalink( $pages[0] ) : home_url( '/' );

    set_transient( 'glass_pro_dashboard_url', $url, DAY_IN_SECONDS );
    return $url;
}

/* جفت دکمه: «ثبت آگهی» + «ورود/ثبت‌نام» یا «داشبورد»
   $context : 'compact' (کنار هم) | 'block' (تمام‌عرض)
*/
function glass_action_buttons( $context = 'compact' ) {
    $submit = esc_url( glass_get_submit_url() );
    $logged = is_user_logged_in();

    $cls = ( 'block' === $context ) ? 'glass-cta-buttons glass-cta-buttons--block' : 'glass-cta-buttons';
    $t = function_exists( 'glass_t' ) ? 'glass_t' : null;
    $L = function ( $k, $fb ) use ( $t ) { return $t ? $t( $k ) : $fb; };

    ob_start();
    ?>
    <div class="<?php echo esc_attr( $cls ); ?>">
        <a href="<?php echo esc_url( $submit ); ?>" class="glass-cta-btn glass-cta-btn--primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <?php echo esc_html( $L( 'submit_ad', 'ثبت آگهی' ) ); ?>
        </a>
        <?php if ( $logged ) : ?>
            <a href="<?php echo esc_url( glass_get_dashboard_url() ); ?>" class="glass-cta-btn glass-cta-btn--ghost">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <?php echo esc_html( $L( 'dashboard', 'داشبورد' ) ); ?>
            </a>
        <?php else : ?>
            <a href="<?php echo esc_url( glass_get_login_url() ); ?>" class="glass-cta-btn glass-cta-btn--ghost">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                <?php echo esc_html( $L( 'login_register', 'ورود / ثبت‌نام' ) ); ?>
            </a>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

/* شورت‌کد مستقل برای جفت دکمه: [glass_action_buttons] */
add_shortcode( 'glass_action_buttons', function ( $atts ) {
    $atts = shortcode_atts( [ 'style' => 'compact' ], $atts, 'glass_action_buttons' );
    return glass_action_buttons( $atts['style'] === 'block' ? 'block' : 'compact' );
} );

/* جعبه هوشمند بالای فرم ثبت آگهی:
   - مهمان: تشویق به ورود/ثبت‌نام (ولی ثبت آگهی بدون لاگین هم آزاد است)
   - کاربر لاگین‌شده: خوش‌آمد + لینک داشبورد
*/
function glass_render_auth_prompt() {
    $rtl = function_exists( 'glass_lang_is_rtl' ) ? glass_lang_is_rtl() : true;
    $dir = $rtl ? 'rtl' : 'ltr';
    $align = $rtl ? 'right' : 'left';
    ob_start();
    if ( is_user_logged_in() ) {
        $u = wp_get_current_user();
        ?>
        <div class="glass-auth-prompt glass-auth-prompt--in" style="direction: <?php echo esc_attr( $dir ); ?>; text-align: <?php echo esc_attr( $align ); ?>;">
            <div class="glass-auth-prompt-ico">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
            <div class="glass-auth-prompt-txt">
                <strong><?php printf( esc_html( glass_t( 'prompt_user_title' ) ), esc_html( $u->display_name ) ); ?></strong>
                <span><?php echo wp_kses_post( glass_t( 'prompt_user_desc' ) ); ?></span>
            </div>
            <a href="<?php echo esc_url( glass_get_dashboard_url() ); ?>" class="glass-cta-btn glass-cta-btn--ghost glass-auth-prompt-btn"><?php echo esc_html( glass_t( 'dashboard' ) ); ?></a>
        </div>
        <?php
    } else {
        ?>
        <div class="glass-auth-prompt glass-auth-prompt--out" style="direction: <?php echo esc_attr( $dir ); ?>; text-align: <?php echo esc_attr( $align ); ?>;">
            <div class="glass-auth-prompt-ico">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg>
            </div>
            <div class="glass-auth-prompt-txt">
                <strong><?php echo esc_html( glass_t( 'prompt_guest_title' ) ); ?></strong>
                <span><?php echo wp_kses_post( glass_t( 'prompt_guest_desc' ) ); ?></span>
            </div>
            <a href="<?php echo esc_url( glass_get_login_url() ); ?>" class="glass-cta-btn glass-cta-btn--primary glass-auth-prompt-btn">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                <?php echo esc_html( glass_t( 'login_register' ) ); ?>
            </a>
        </div>
        <?php
    }
    return ob_get_clean();
}

