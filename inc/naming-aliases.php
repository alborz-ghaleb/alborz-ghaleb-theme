<?php
/**
 * Naming Convention Aliases
 *
 * Explicit safe wrappers for legacy functions. No eval/dynamic code generation is used.
 *
 * @package Alborz_Ghaleb
 * @since   5.0.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * نگاشت alias های فعال.
 *
 * @return array<string,string>
 */
function glass_pro_naming_aliases(): array {
	return [
		'glass_pro_bc_render' => 'fl_bc_render',
		'glass_pro_bc_get_items' => 'fl_bc_get_items',
		'glass_pro_bc_get_lang' => 'fl_bc_get_lang',
		'glass_pro_bc_clean_title' => 'fl_bc_clean_title',
		'glass_pro_bc_is_rtl' => 'fl_bc_is_rtl',
		'glass_pro_bc_inline_css' => 'fl_bc_inline_css',
		'glass_pro_bc_output_after_header' => 'fl_bc_output_after_header',
		'glass_pro_fab_customizer' => 'fl_fab_customizer',
		'glass_pro_fab_icon' => 'fl_fab_icon',
		'glass_pro_fab_lang' => 'fl_fab_lang',
		'glass_pro_fab_tel' => 'fl_fab_tel',
		'glass_pro_fab_texts' => 'fl_fab_texts',
		'glass_pro_render_floating' => 'fl_render_floating',
		'glass_pro_portfolio_template' => 'replywp_portfolio_template',
		'glass_pro_ultra_blog_template' => 'replywp_ultra_blog_template',
		'glass_pro_render_ultra_blog' => 'replywp_render_ultra_blog',
		'glass_pro_render_portfolio_page' => 'replywp_render_portfolio_page',
		'glass_pro_render_city_menu' => 'replywp_render_city_menu',
		'glass_pro_get_city_options' => 'replywp_get_city_options',
		'glass_pro_get_city_button_text' => 'replywp_get_city_button_text',
		'glass_pro_clean_plain_text' => 'replywp_clean_plain_text',
		'glass_pro_register_city_taxonomy' => 'replywp_register_city_taxonomy',
		'glass_pro_register_city_menu_location' => 'replywp_register_city_menu_location',
		'glass_pro_action_buttons' => 'glass_action_buttons',
		'glass_pro_render_auth_prompt' => 'glass_render_auth_prompt',
		'glass_pro_render_login_panel' => 'glass_render_login_panel',
		'glass_pro_render_user_dashboard' => 'glass_render_user_dashboard',
		'glass_pro_get_dashboard_url' => 'glass_get_dashboard_url',
		'glass_pro_get_login_url' => 'glass_get_login_url',
		'glass_pro_get_submit_url' => 'glass_get_submit_url',
		'glass_pro_captcha_fields' => 'glass_captcha_fields',
		'glass_pro_captcha_verify' => 'glass_captcha_verify',
		'glass_pro_is_ad_expired' => 'glass_is_ad_expired',
		'glass_pro_is_user_submitted_ad' => 'glass_is_user_submitted_ad',
		'glass_pro_get_ad_state' => 'glass_get_ad_state',
		'glass_pro_ad_phone_visible' => 'glass_ad_phone_visible',
		'glass_pro_translate' => 'glass_t',
		'glass_pro_current_lang' => 'glass_current_lang',
		'glass_pro_lang_is_rtl' => 'glass_lang_is_rtl',
	];
}

add_action( 'after_setup_theme', 'glass_pro_register_naming_aliases', 100 );
/**
 * تعریف alias ها بعد از load شدن legacy functions.
 *
 * @return void
 */
function glass_pro_register_naming_aliases(): void {
	if ( function_exists( 'fl_bc_render' ) && ! function_exists( 'glass_pro_bc_render' ) ) {
		function glass_pro_bc_render() {
			return call_user_func_array( 'fl_bc_render', func_get_args() );
		}
	}

	if ( function_exists( 'fl_bc_get_items' ) && ! function_exists( 'glass_pro_bc_get_items' ) ) {
		function glass_pro_bc_get_items() {
			return call_user_func_array( 'fl_bc_get_items', func_get_args() );
		}
	}

	if ( function_exists( 'fl_bc_get_lang' ) && ! function_exists( 'glass_pro_bc_get_lang' ) ) {
		function glass_pro_bc_get_lang() {
			return call_user_func_array( 'fl_bc_get_lang', func_get_args() );
		}
	}

	if ( function_exists( 'fl_bc_clean_title' ) && ! function_exists( 'glass_pro_bc_clean_title' ) ) {
		function glass_pro_bc_clean_title() {
			return call_user_func_array( 'fl_bc_clean_title', func_get_args() );
		}
	}

	if ( function_exists( 'fl_bc_is_rtl' ) && ! function_exists( 'glass_pro_bc_is_rtl' ) ) {
		function glass_pro_bc_is_rtl() {
			return call_user_func_array( 'fl_bc_is_rtl', func_get_args() );
		}
	}

	if ( function_exists( 'fl_bc_inline_css' ) && ! function_exists( 'glass_pro_bc_inline_css' ) ) {
		function glass_pro_bc_inline_css() {
			return call_user_func_array( 'fl_bc_inline_css', func_get_args() );
		}
	}

	if ( function_exists( 'fl_bc_output_after_header' ) && ! function_exists( 'glass_pro_bc_output_after_header' ) ) {
		function glass_pro_bc_output_after_header() {
			return call_user_func_array( 'fl_bc_output_after_header', func_get_args() );
		}
	}

	if ( function_exists( 'fl_fab_customizer' ) && ! function_exists( 'glass_pro_fab_customizer' ) ) {
		function glass_pro_fab_customizer() {
			return call_user_func_array( 'fl_fab_customizer', func_get_args() );
		}
	}

	if ( function_exists( 'fl_fab_icon' ) && ! function_exists( 'glass_pro_fab_icon' ) ) {
		function glass_pro_fab_icon() {
			return call_user_func_array( 'fl_fab_icon', func_get_args() );
		}
	}

	if ( function_exists( 'fl_fab_lang' ) && ! function_exists( 'glass_pro_fab_lang' ) ) {
		function glass_pro_fab_lang() {
			return call_user_func_array( 'fl_fab_lang', func_get_args() );
		}
	}

	if ( function_exists( 'fl_fab_tel' ) && ! function_exists( 'glass_pro_fab_tel' ) ) {
		function glass_pro_fab_tel() {
			return call_user_func_array( 'fl_fab_tel', func_get_args() );
		}
	}

	if ( function_exists( 'fl_fab_texts' ) && ! function_exists( 'glass_pro_fab_texts' ) ) {
		function glass_pro_fab_texts() {
			return call_user_func_array( 'fl_fab_texts', func_get_args() );
		}
	}

	if ( function_exists( 'fl_render_floating' ) && ! function_exists( 'glass_pro_render_floating' ) ) {
		function glass_pro_render_floating() {
			return call_user_func_array( 'fl_render_floating', func_get_args() );
		}
	}

	if ( function_exists( 'replywp_portfolio_template' ) && ! function_exists( 'glass_pro_portfolio_template' ) ) {
		function glass_pro_portfolio_template() {
			return call_user_func_array( 'replywp_portfolio_template', func_get_args() );
		}
	}

	if ( function_exists( 'replywp_ultra_blog_template' ) && ! function_exists( 'glass_pro_ultra_blog_template' ) ) {
		function glass_pro_ultra_blog_template() {
			return call_user_func_array( 'replywp_ultra_blog_template', func_get_args() );
		}
	}

	if ( function_exists( 'replywp_render_ultra_blog' ) && ! function_exists( 'glass_pro_render_ultra_blog' ) ) {
		function glass_pro_render_ultra_blog() {
			return call_user_func_array( 'replywp_render_ultra_blog', func_get_args() );
		}
	}

	if ( function_exists( 'replywp_render_portfolio_page' ) && ! function_exists( 'glass_pro_render_portfolio_page' ) ) {
		function glass_pro_render_portfolio_page() {
			return call_user_func_array( 'replywp_render_portfolio_page', func_get_args() );
		}
	}

	if ( function_exists( 'replywp_render_city_menu' ) && ! function_exists( 'glass_pro_render_city_menu' ) ) {
		function glass_pro_render_city_menu() {
			return call_user_func_array( 'replywp_render_city_menu', func_get_args() );
		}
	}

	if ( function_exists( 'replywp_get_city_options' ) && ! function_exists( 'glass_pro_get_city_options' ) ) {
		function glass_pro_get_city_options() {
			return call_user_func_array( 'replywp_get_city_options', func_get_args() );
		}
	}

	if ( function_exists( 'replywp_get_city_button_text' ) && ! function_exists( 'glass_pro_get_city_button_text' ) ) {
		function glass_pro_get_city_button_text() {
			return call_user_func_array( 'replywp_get_city_button_text', func_get_args() );
		}
	}

	if ( function_exists( 'replywp_clean_plain_text' ) && ! function_exists( 'glass_pro_clean_plain_text' ) ) {
		function glass_pro_clean_plain_text() {
			return call_user_func_array( 'replywp_clean_plain_text', func_get_args() );
		}
	}

	if ( function_exists( 'replywp_register_city_taxonomy' ) && ! function_exists( 'glass_pro_register_city_taxonomy' ) ) {
		function glass_pro_register_city_taxonomy() {
			return call_user_func_array( 'replywp_register_city_taxonomy', func_get_args() );
		}
	}

	if ( function_exists( 'replywp_register_city_menu_location' ) && ! function_exists( 'glass_pro_register_city_menu_location' ) ) {
		function glass_pro_register_city_menu_location() {
			return call_user_func_array( 'replywp_register_city_menu_location', func_get_args() );
		}
	}

	if ( function_exists( 'glass_action_buttons' ) && ! function_exists( 'glass_pro_action_buttons' ) ) {
		function glass_pro_action_buttons() {
			return call_user_func_array( 'glass_action_buttons', func_get_args() );
		}
	}

	if ( function_exists( 'glass_render_auth_prompt' ) && ! function_exists( 'glass_pro_render_auth_prompt' ) ) {
		function glass_pro_render_auth_prompt() {
			return call_user_func_array( 'glass_render_auth_prompt', func_get_args() );
		}
	}

	if ( function_exists( 'glass_render_login_panel' ) && ! function_exists( 'glass_pro_render_login_panel' ) ) {
		function glass_pro_render_login_panel() {
			return call_user_func_array( 'glass_render_login_panel', func_get_args() );
		}
	}

	if ( function_exists( 'glass_render_user_dashboard' ) && ! function_exists( 'glass_pro_render_user_dashboard' ) ) {
		function glass_pro_render_user_dashboard() {
			return call_user_func_array( 'glass_render_user_dashboard', func_get_args() );
		}
	}

	if ( function_exists( 'glass_get_dashboard_url' ) && ! function_exists( 'glass_pro_get_dashboard_url' ) ) {
		function glass_pro_get_dashboard_url() {
			return call_user_func_array( 'glass_get_dashboard_url', func_get_args() );
		}
	}

	if ( function_exists( 'glass_get_login_url' ) && ! function_exists( 'glass_pro_get_login_url' ) ) {
		function glass_pro_get_login_url() {
			return call_user_func_array( 'glass_get_login_url', func_get_args() );
		}
	}

	if ( function_exists( 'glass_get_submit_url' ) && ! function_exists( 'glass_pro_get_submit_url' ) ) {
		function glass_pro_get_submit_url() {
			return call_user_func_array( 'glass_get_submit_url', func_get_args() );
		}
	}

	if ( function_exists( 'glass_captcha_fields' ) && ! function_exists( 'glass_pro_captcha_fields' ) ) {
		function glass_pro_captcha_fields() {
			return call_user_func_array( 'glass_captcha_fields', func_get_args() );
		}
	}

	if ( function_exists( 'glass_captcha_verify' ) && ! function_exists( 'glass_pro_captcha_verify' ) ) {
		function glass_pro_captcha_verify() {
			return call_user_func_array( 'glass_captcha_verify', func_get_args() );
		}
	}

	if ( function_exists( 'glass_is_ad_expired' ) && ! function_exists( 'glass_pro_is_ad_expired' ) ) {
		function glass_pro_is_ad_expired() {
			return call_user_func_array( 'glass_is_ad_expired', func_get_args() );
		}
	}

	if ( function_exists( 'glass_is_user_submitted_ad' ) && ! function_exists( 'glass_pro_is_user_submitted_ad' ) ) {
		function glass_pro_is_user_submitted_ad() {
			return call_user_func_array( 'glass_is_user_submitted_ad', func_get_args() );
		}
	}

	if ( function_exists( 'glass_get_ad_state' ) && ! function_exists( 'glass_pro_get_ad_state' ) ) {
		function glass_pro_get_ad_state() {
			return call_user_func_array( 'glass_get_ad_state', func_get_args() );
		}
	}

	if ( function_exists( 'glass_ad_phone_visible' ) && ! function_exists( 'glass_pro_ad_phone_visible' ) ) {
		function glass_pro_ad_phone_visible() {
			return call_user_func_array( 'glass_ad_phone_visible', func_get_args() );
		}
	}

	if ( function_exists( 'glass_t' ) && ! function_exists( 'glass_pro_translate' ) ) {
		function glass_pro_translate() {
			return call_user_func_array( 'glass_t', func_get_args() );
		}
	}

	if ( function_exists( 'glass_current_lang' ) && ! function_exists( 'glass_pro_current_lang' ) ) {
		function glass_pro_current_lang() {
			return call_user_func_array( 'glass_current_lang', func_get_args() );
		}
	}

	if ( function_exists( 'glass_lang_is_rtl' ) && ! function_exists( 'glass_pro_lang_is_rtl' ) ) {
		function glass_pro_lang_is_rtl() {
			return call_user_func_array( 'glass_lang_is_rtl', func_get_args() );
		}
	}
}
