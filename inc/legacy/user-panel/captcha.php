<?php
/** User Panel — Simple Math Captcha (signed token, no plugin) */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ════════════════════════════════════════
   3.5. کپچای ساده ریاضی (بدون افزونه/کلید)
   ════════════════════════════════════════
   - سوال تصادفی جمع/ضرب
   - پاسخ به‌صورت hash امضا‌شده در فیلد مخفی نگه‌داری می‌شود
     (پاسخ خام هرگز در HTML نمی‌آید → غیرقابل تقلب آسان)
*/

/* تولید فیلدهای کپچا (HTML) — [SEC v5.0.5] بهبود با honeypot + time-based protection */
function glass_captcha_fields() {
    $a  = wp_rand( 10, 49 );  // [SEC v5.0.5] اعداد بزرگ‌تر — کاهش شانس bot
    $b  = wp_rand( 10, 49 );
    $op = ( wp_rand( 0, 1 ) === 1 ) ? '+' : '−';
    $answer = ( $op === '+' ) ? ( $a + $b ) : ( $a - $b );

    // [PRO-FIX #3] امضای پاسخ به‌همراه «پنجره زمانی» تا توکن قابل بازپخش نامحدود نباشد.
    $slot  = (int) floor( time() / 300 );
    $token = $slot . ':' . wp_hash( $answer . '|' . $slot . '|glass_captcha' );

    // [SEC v5.0.5] time-based field: زمان نمایش فرم — bot ها معمولاً خیلی سریع submit می‌کنند
    $render_time = time();

    ob_start();
    ?>
    <div class="glass-auth-field glass-captcha-field">
        <label><?php echo esc_html( glass_t('captcha_q') ); ?> <strong><?php echo esc_html( $a . ' ' . $op . ' ' . $b ); ?> = ؟</strong></label>
        <input type="number" name="glass_captcha" inputmode="numeric" required autocomplete="off" placeholder="<?php echo esc_attr( glass_t('captcha_ph') ); ?>">
        <input type="hidden" name="glass_captcha_token" value="<?php echo esc_attr( $token ); ?>">
        <input type="hidden" name="glass_captcha_time" value="<?php echo esc_attr( $render_time ); ?>">
    </div>
    <?php /* [SEC v5.0.5] Honeypot field — bot ها این فیلد رو پر می‌کنند، کاربر واقعی نه */ ?>
    <div style="position:absolute;left:-9999px;top:-9999px;width:1px;height:1px;overflow:hidden;" aria-hidden="true">
        <label for="glass_hp_email">Leave this field empty</label>
        <input type="text" name="glass_hp_email" id="glass_hp_email" value="" tabindex="-1" autocomplete="off">
    </div>
    <?php
    return ob_get_clean();
}

/* اعتبارسنجی کپچا — true یعنی درست
 * [PRO-FIX #3] پذیرش فقط در پنجره ۵ دقیقه‌ای (slot جاری یا یکی قبل) → جلوگیری از replay طولانی‌مدت.
 */
function glass_captcha_verify() {
    // [SEC v5.0.5] چک honeypot: اگر پر شده، حتماً bot است
    $honeypot = isset( $_POST['glass_hp_email'] ) ? trim( (string) wp_unslash( $_POST['glass_hp_email'] ) ) : '';
    if ( $honeypot !== '' ) {
        return false;
    }

    // [SEC v5.0.5] چک time-based: bot ها معمولاً در < 2 ثانیه submit می‌کنند
    $render_time = isset( $_POST['glass_captcha_time'] ) ? (int) wp_unslash( $_POST['glass_captcha_time'] ) : 0;
    if ( $render_time > 0 ) {
        $elapsed = time() - $render_time;
        if ( $elapsed < 2 ) {
            return false; // submit بیش از حد سریع — مشکوک به bot
        }
        if ( $elapsed > 1800 ) {
            return false; // form بیش از 30 دقیقه قدیمی
        }
    }

    $user_answer = isset( $_POST['glass_captcha'] ) ? trim( (string) wp_unslash( $_POST['glass_captcha'] ) ) : '';
    $token       = isset( $_POST['glass_captcha_token'] ) ? sanitize_text_field( wp_unslash( $_POST['glass_captcha_token'] ) ) : '';

    // فرمت توکن: "slot:hash"
    if ( $user_answer !== '' && ! empty( $token ) && strpos( $token, ':' ) !== false ) {
        list( $slot, $hash ) = explode( ':', $token, 2 );
        $slot = (int) $slot;
        $now  = (int) floor( time() / 300 );
        if ( $slot === $now || $slot === ( $now - 1 ) ) {
            $expected = wp_hash( intval( $user_answer ) . '|' . $slot . '|glass_captcha' );
            return hash_equals( $hash, $expected );
        }
        return false; // توکن منقضی شده
    }

    if ( $user_answer === '' || empty( $token ) ) {
        return false;
    }
    // مقایسه امن hash پاسخ کاربر با token
    $expected = wp_hash( intval( $user_answer ) . '|glass_captcha' );
    return hash_equals( $token, $expected );
}

