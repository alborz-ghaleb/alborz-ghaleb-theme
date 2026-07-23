<?php
/**
 * Bot Detection — تشخیص ربات‌های شناخته‌شده با User-Agent.
 *
 * استفاده:
 *   if ( glass_pro_is_bot() ) { return; }  // skip view counter
 *
 * @package Alborz_Ghaleb
 * @since   5.0.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'glass_pro_is_bot' ) ) :
	/**
	 * تشخیص اینکه آیا request از سمت bot است.
	 *
	 * بر اساس User-Agent — لیست bot های شناخته‌شده.
	 * cached per-request تا چندبار stat نشود.
	 *
	 * @return bool
	 */
	function glass_pro_is_bot(): bool {
		static $cache = null;
		if ( null !== $cache ) {
			return $cache;
		}

		$ua = isset( $_SERVER['HTTP_USER_AGENT'] )
			? strtolower( (string) wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) )
			: '';

		if ( '' === $ua ) {
			$cache = true; // No UA = suspicious
			return $cache;
		}

		// لیست bot های رایج (search engines + monitoring + scrapers)
		$bot_patterns = [
			'bot', 'crawler', 'spider', 'slurp', 'scraper',
			'curl', 'wget', 'python-requests', 'go-http-client', 'java/',
			'okhttp', 'libwww-perl', 'mechanize', 'phantomjs', 'headless',
			'googlebot', 'bingbot', 'yandex', 'baiduspider', 'duckduckbot',
			'facebookexternalhit', 'twitterbot', 'linkedinbot', 'whatsapp',
			'telegrambot', 'discordbot', 'slackbot',
			'ahrefsbot', 'semrushbot', 'mj12bot', 'dotbot', 'rogerbot',
			'pingdom', 'gtmetrix', 'uptimerobot', 'newrelicpinger',
			'preview', 'archive.org_bot', 'ia_archiver',
		];

		/**
		 * Filter: glass_pro/bot_patterns — اضافه/حذف الگوهای bot
		 *
		 * @param array $bot_patterns
		 */
		$bot_patterns = (array) apply_filters( 'glass_pro/bot_patterns', $bot_patterns );

		foreach ( $bot_patterns as $pattern ) {
			if ( str_contains( $ua, (string) $pattern ) ) {
				$cache = true;
				return $cache;
			}
		}

		$cache = false;
		return $cache;
	}
endif;

if ( ! function_exists( 'glass_pro_should_count_view' ) ) :
	/**
	 * آیا باید بازدید را شمارش کرد؟
	 *
	 * @param int $post_id
	 * @return bool
	 */
	function glass_pro_should_count_view( int $post_id ): bool {
		// bot ها شمارش نشن
		if ( glass_pro_is_bot() ) {
			return false;
		}
		// ادمین/ویراستار شمارش نشه (preview)
		if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
			return false;
		}
		// preview mode
		if ( is_preview() ) {
			return false;
		}
		/**
		 * Filter: glass_pro/should_count_view — override نهایی
		 *
		 * @param bool $should
		 * @param int  $post_id
		 */
		return (bool) apply_filters( 'glass_pro/should_count_view', true, $post_id );
	}
endif;
