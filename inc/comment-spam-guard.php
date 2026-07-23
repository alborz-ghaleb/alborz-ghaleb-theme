<?php
/**
 * Comment Spam Guard
 *
 * - Max 2 regular comments per rolling 24 hours for both IP and identity.
 * - Privacy-safe hashed transient counters (no raw IP/email is stored).
 * - Signed form timestamp + minimum fill time.
 * - Honeypot field.
 * - Administrators/moderators and pingbacks/trackbacks are exempt.
 *
 * @package Alborz_Ghaleb
 * @since   8.8.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return a validated visitor IP. Cloudflare's header is preferred when present;
 * the result is only used after hashing and is filterable for custom proxies.
 */
function glass_comment_guard_ip(): string {
	// Reuse the hardened trusted-proxy implementation when available.
	if ( function_exists( 'glass_pro_get_client_ip' ) ) {
		$ip = glass_pro_get_client_ip();
		return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : 'unknown';
	}
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? trim( (string) wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : 'unknown';
}

/** Hash private identifiers with a WordPress salt. */
function glass_comment_guard_hash( string $value ): string {
	return substr( hash_hmac( 'sha256', strtolower( trim( $value ) ), wp_salt( 'nonce' ) ), 0, 32 );
}

function glass_comment_guard_identity( array $commentdata ): string {
	$user_id = isset( $commentdata['user_ID'] ) ? absint( $commentdata['user_ID'] ) : 0;
	if ( $user_id ) {
		return 'user:' . $user_id;
	}
	$email = isset( $commentdata['comment_author_email'] ) ? sanitize_email( $commentdata['comment_author_email'] ) : '';
	if ( $email ) {
		return 'email:' . strtolower( $email );
	}
	$name = isset( $commentdata['comment_author'] ) ? sanitize_text_field( $commentdata['comment_author'] ) : 'guest';
	return 'guest:' . strtolower( $name );
}

function glass_comment_guard_counter_key( string $type, string $value ): string {
	return 'gpcg_' . sanitize_key( $type ) . '_' . glass_comment_guard_hash( $value );
}

function glass_comment_guard_counter( string $key ): int {
	$value = get_transient( $key );
	return false === $value ? 0 : max( 0, (int) $value );
}

function glass_comment_guard_increment( string $key ): void {
	$count = glass_comment_guard_counter( $key );
	set_transient( $key, $count + 1, DAY_IN_SECONDS );
}

/** Moderators are not rate-limited while reviewing/testing comments. */
function glass_comment_guard_is_exempt(): bool {
	return is_user_logged_in() && current_user_can( 'moderate_comments' );
}

/**
 * Add signed anti-bot fields to both anonymous and logged-in native forms.
 */
function glass_comment_guard_form_fields(): void {
	if ( glass_comment_guard_is_exempt() ) {
		return;
	}
	$post_id = (int) get_the_ID();
	$started = time();
	$token   = hash_hmac( 'sha256', $post_id . '|' . $started, wp_salt( 'nonce' ) );
	?>
	<input type="hidden" name="glass_comment_guard" value="1">
	<input type="hidden" name="glass_comment_started" value="<?php echo esc_attr( (string) $started ); ?>">
	<input type="hidden" name="glass_comment_token" value="<?php echo esc_attr( $token ); ?>">
	<input type="hidden" class="glass-comment-js-check" name="glass_comment_js_check" value="0">
	<script <?php if ( function_exists( 'glass_pro_csp_nonce_attr' ) ) { glass_pro_csp_nonce_attr(); } ?>>(function(s){var f=s&&s.previousElementSibling;if(f&&f.classList.contains('glass-comment-js-check')){f.value='1';}})(document.currentScript);</script>
	<p class="glass-comment-hp" aria-hidden="true" style="position:absolute!important;inset-inline-start:-10000px!important;width:1px!important;height:1px!important;overflow:hidden!important;opacity:0!important;pointer-events:none!important;" tabindex="-1">
		<label for="glass-comment-website-confirm">این فیلد را خالی بگذارید</label>
		<input id="glass-comment-website-confirm" type="text" name="glass_comment_website_confirm" value="" tabindex="-1" autocomplete="off">
	</p>
	<?php
}
add_action( 'comment_form_after_fields', 'glass_comment_guard_form_fields' );
add_action( 'comment_form_logged_in_after', 'glass_comment_guard_form_fields' );

/** Display a localized blocking response with a safe back link. */
function glass_comment_guard_reject( string $message, int $status = 429 ): void {
	wp_die(
		esc_html( $message ),
		esc_html__( 'امکان ثبت دیدگاه وجود ندارد', 'glassmorphism-child-pro' ),
		[ 'response' => $status, 'back_link' => true ]
	);
}

/** Validate honeypot, timing signature and rolling counters before insertion. */
add_filter( 'preprocess_comment', 'glass_comment_guard_validate', 5 );
function glass_comment_guard_validate( array $commentdata ): array {
	$type = isset( $commentdata['comment_type'] ) ? (string) $commentdata['comment_type'] : '';
	if ( '' !== $type || glass_comment_guard_is_exempt() ) {
		return $commentdata;
	}

	$method = strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) );
	$is_native_form = 'POST' === $method && ! empty( $_POST['comment_post_ID'] );

	// Anonymous REST/custom/direct submissions are the main bypass used by bots.
	// Only the signed native form is accepted for non-moderators.
	if ( ! $is_native_form ) {
		glass_comment_guard_reject( 'ارسال دیدگاه فقط از فرم رسمی همین صفحه مجاز است.', 403 );
	}

	$honeypot = isset( $_POST['glass_comment_website_confirm'] )
		? trim( (string) wp_unslash( $_POST['glass_comment_website_confirm'] ) )
		: '';
	if ( '' !== $honeypot ) {
		glass_comment_guard_reject( 'ارسال دیدگاه به‌عنوان اسپم شناسایی شد.', 400 );
	}

	$marker  = isset( $_POST['glass_comment_guard'] ) ? sanitize_text_field( wp_unslash( $_POST['glass_comment_guard'] ) ) : '';
	$started = isset( $_POST['glass_comment_started'] ) ? absint( $_POST['glass_comment_started'] ) : 0;
	$token   = isset( $_POST['glass_comment_token'] ) ? sanitize_text_field( wp_unslash( $_POST['glass_comment_token'] ) ) : '';
	$post_id = absint( $_POST['comment_post_ID'] );
	$js_check = isset( $_POST['glass_comment_js_check'] ) ? sanitize_text_field( wp_unslash( $_POST['glass_comment_js_check'] ) ) : '';

	if ( '1' !== $js_check ) {
		glass_comment_guard_reject( 'اعتبار مرورگر برای ارسال دیدگاه تأیید نشد. JavaScript را فعال و صفحه را تازه‌سازی کنید.', 400 );
	}
	if ( '1' !== $marker || ! $started || ! $token ) {
		glass_comment_guard_reject( 'اعتبار فرم دیدگاه تأیید نشد. صفحه را تازه‌سازی و دوباره تلاش کنید.', 400 );
	}

	$expected = hash_hmac( 'sha256', $post_id . '|' . $started, wp_salt( 'nonce' ) );
	if ( ! hash_equals( $expected, $token ) ) {
		glass_comment_guard_reject( 'اعتبار فرم دیدگاه صحیح نیست. صفحه را تازه‌سازی کنید.', 400 );
	}

	$elapsed = time() - $started;
	$minimum = max( 2, (int) apply_filters( 'glass_pro/comment_guard/min_fill_seconds', 3 ) );
	$maximum = max( DAY_IN_SECONDS, (int) apply_filters( 'glass_pro/comment_guard/form_lifetime', 30 * DAY_IN_SECONDS ) );
	if ( $elapsed < $minimum ) {
		glass_comment_guard_reject( 'فرم دیدگاه بیش از حد سریع ارسال شد. چند ثانیه صبر کنید و دوباره بفرستید.', 429 );
	}
	if ( $elapsed > $maximum ) {
		glass_comment_guard_reject( 'فرم دیدگاه منقضی شده است. صفحه را تازه‌سازی کنید.', 400 );
	}

	if ( function_exists( 'glass_pro_is_bot' ) && glass_pro_is_bot() ) {
		glass_comment_guard_reject( 'ارسال خودکار دیدگاه مجاز نیست.', 403 );
	}

	$content = isset( $commentdata['comment_content'] ) ? trim( wp_strip_all_tags( (string) $commentdata['comment_content'] ) ) : '';
	$content_len = function_exists( 'mb_strlen' ) ? mb_strlen( $content ) : strlen( $content );
	$max_length = max( 500, (int) apply_filters( 'glass_pro/comment_guard/max_length', 2500 ) );
	if ( $content_len < 3 || $content_len > $max_length ) {
		glass_comment_guard_reject( 'طول متن دیدگاه معتبر نیست.', 400 );
	}
	if ( substr_count( $content, "\n" ) > 20 ) {
		glass_comment_guard_reject( 'تعداد خطوط دیدگاه بیش از حد مجاز است.', 400 );
	}

	$spam_pattern = (string) apply_filters(
		'glass_pro/comment_guard/spam_pattern',
		'/(?:viagra|casino|porn|sex\s*cam|payday\s*loan|crypto\s*(?:profit|investment)|guest\s*post|buy\s*backlinks?|seo\s*service|work\s*from\s*home|free\s*money)/iu'
	);
	if ( $spam_pattern && @preg_match( $spam_pattern, $content ) ) {
		glass_comment_guard_reject( 'محتوای دیدگاه به‌عنوان اسپم شناسایی شد.', 400 );
	}

	// Block exact repeated content across rotating IPs/emails for seven days.
	$content_lower = function_exists( 'mb_strtolower' ) ? mb_strtolower( $content ) : strtolower( $content );
	$normalized_content = preg_replace( '/\s+/u', ' ', $content_lower );
	$content_key = 'gpcg_content_' . glass_comment_guard_hash( $normalized_content );
	if ( $content_len >= 12 && get_transient( $content_key ) ) {
		glass_comment_guard_reject( 'این متن قبلاً ثبت شده است و امکان ارسال تکراری آن وجود ندارد.', 409 );
	}

	$limit        = max( 1, (int) apply_filters( 'glass_pro/comment_daily_limit', 2, $commentdata ) );
	$ip           = glass_comment_guard_ip();
	$ip_key       = 'unknown' !== $ip ? glass_comment_guard_counter_key( 'ip', $ip ) : '';
	$identity_key = glass_comment_guard_counter_key( 'identity', glass_comment_guard_identity( $commentdata ) );

	if ( $ip_key && glass_comment_guard_counter( $ip_key ) >= $limit ) {
		glass_comment_guard_reject( sprintf( 'از این اینترنت در ۲۴ ساعت گذشته حداکثر %d دیدگاه ثبت شده است. لطفاً بعداً تلاش کنید.', $limit ) );
	}
	if ( glass_comment_guard_counter( $identity_key ) >= $limit ) {
		glass_comment_guard_reject( sprintf( 'شما در ۲۴ ساعت گذشته حداکثر %d دیدگاه ثبت کرده‌اید. لطفاً بعداً تلاش کنید.', $limit ) );
	}

	// Temporary keys are carried only in memory until comment_post fires.
	$GLOBALS['glass_comment_guard_pending_keys'] = array_filter( [ $ip_key, $identity_key ] );
	$GLOBALS['glass_comment_guard_content_key']  = $content_len >= 12 ? $content_key : '';
	return $commentdata;
}

/** Increment counters only after WordPress successfully inserts the comment. */
add_action( 'comment_post', 'glass_comment_guard_record', 10, 3 );
function glass_comment_guard_record( int $comment_id, $approved, array $commentdata ): void {
	$type = isset( $commentdata['comment_type'] ) ? (string) $commentdata['comment_type'] : '';
	if ( '' !== $type || glass_comment_guard_is_exempt() ) {
		return;
	}
	$keys = $GLOBALS['glass_comment_guard_pending_keys'] ?? [];
	if ( empty( $keys ) ) {
		$ip = glass_comment_guard_ip();
		$keys = [
			'unknown' !== $ip ? glass_comment_guard_counter_key( 'ip', $ip ) : '',
			glass_comment_guard_counter_key( 'identity', glass_comment_guard_identity( $commentdata ) ),
		];
	}
	foreach ( array_unique( array_filter( $keys ) ) as $key ) {
		glass_comment_guard_increment( (string) $key );
	}
	$content_key = (string) ( $GLOBALS['glass_comment_guard_content_key'] ?? '' );
	if ( $content_key ) {
		set_transient( $content_key, 1, 7 * DAY_IN_SECONDS );
	}
	unset( $GLOBALS['glass_comment_guard_pending_keys'], $GLOBALS['glass_comment_guard_content_key'] );
}


/** Explicitly close the anonymous REST comment bypass. */
add_filter( 'rest_pre_insert_comment', 'glass_comment_guard_rest_block', 10, 2 );
function glass_comment_guard_rest_block( $prepared_comment, $request ) {
	if ( glass_comment_guard_is_exempt() ) {
		return $prepared_comment;
	}
	return new WP_Error(
		'glass_comment_rest_disabled',
		__( 'ثبت دیدگاه مهمان از REST API مجاز نیست؛ از فرم دیدگاه سایت استفاده کنید.', 'glassmorphism-child-pro' ),
		[ 'status' => 403 ]
	);
}
