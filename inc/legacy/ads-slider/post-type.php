<?php
/** Ads Slider — CPT + Taxonomy + Admin Media Loader */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ════════════════════════════════════════
   1. ثبت نوع پست «اسلاید تبلیغی»
   ════════════════════════════════════════ */
add_action( 'init', 'glass_register_ad_post_type' );
/**
 * ثبت CPT glass_ad.
 *
 * @return void
 */
function glass_register_ad_post_type() {
	if ( post_type_exists( 'glass_ad' ) ) {
		return;
	}
	register_post_type( 'glass_ad', [
		'labels' => [
			'name'          => 'اسلایدر تبلیغی',
			'singular_name' => 'اسلاید تبلیغی',
			'menu_name'     => 'اسلایدر تبلیغی',
			'add_new'       => 'افزودن اسلاید',
			'add_new_item'  => 'افزودن اسلاید تبلیغی جدید',
			'edit_item'     => 'ویرایش اسلاید',
			'new_item'      => 'اسلاید جدید',
			'view_item'     => 'مشاهده اسلاید',
			'all_items'     => 'همه اسلایدها',
			'search_items'  => 'جستجو در اسلایدها',
			'not_found'     => 'اسلایدی یافت نشد',
		],
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'has_archive'         => false,
		'hierarchical'        => false,
		'menu_icon'           => 'dashicons-images-alt2',
		'menu_position'       => 25,
		'supports'            => [ 'title', 'thumbnail', 'page-attributes' ],
	] );
}

/* ════════════════════════════════════════
   1.5. تاکسونومی «گروه اسلایدر» (برای چند اسلایدر مجزا)
   استفاده: [glass_ads_slider group="home-top"]
   ════════════════════════════════════════ */
add_action( 'init', 'glass_register_ad_group_taxonomy' );
/**
 * ثبت تاکسونومی glass_ad_group.
 *
 * @return void
 */
function glass_register_ad_group_taxonomy() {
	if ( taxonomy_exists( 'glass_ad_group' ) ) {
		return;
	}
	register_taxonomy( 'glass_ad_group', [ 'glass_ad' ], [
		'labels'            => [
			'name'              => 'گروه‌های اسلایدر',
			'singular_name'     => 'گروه اسلایدر',
			'menu_name'         => 'گروه‌های اسلایدر',
			'add_new_item'      => 'افزودن گروه جدید',
			'edit_item'         => 'ویرایش گروه',
			'all_items'         => 'همه گروه‌ها',
			'search_items'      => 'جستجوی گروه',
			'not_found'         => 'گروهی یافت نشد',
		],
		'public'            => false,
		'show_ui'           => true,
		'show_admin_column' => true,
		'hierarchical'      => true,   // مثل دسته‌بندی (چک‌باکس)
		'show_in_rest'      => true,
		'rewrite'           => false,
	] );
}

/* ════════════════════════════════════════
   1.6. بارگذاری Media Uploader در صفحهٔ ویرایش اسلاید
   ════════════════════════════════════════ */
add_action( 'admin_enqueue_scripts', 'glass_ad_admin_assets' );
/**
 * بارگذاری اسکریپت Media فقط در صفحهٔ ویرایش/افزودن glass_ad.
 *
 * @param string $hook صفحهٔ جاری.
 * @return void
 */
function glass_ad_admin_assets( $hook ) {
	if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( $screen && 'glass_ad' !== $screen->post_type ) {
		return;
	}
	wp_enqueue_media();
}

