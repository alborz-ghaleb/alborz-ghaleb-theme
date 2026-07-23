<?php
/**
 * Post Types, Taxonomies & Rewrite Rules
 *
 * 🚨 قانون قرمز: این فایل عیناً از نسخه‌ی قبل منتقل شده تا
 * هیچ permalink / slug / rewrite rule تغییر نکند.
 * (CPT دیگر «glass_ad» و taxonomy «portfolio_city» در ماژول‌های legacy ثبت می‌شوند.)
 *
 * @package Alborz_Ghaleb
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ────────────────────────────────────────
   CPT: portfolio (دست دوم)  — slug فارسی «دست-دوم» حفظ‌شده
   ──────────────────────────────────────── */
add_action( 'init', 'glass_register_post_types' );
/**
 * ثبت نوع پست portfolio.
 *
 * @return void
 */
function glass_register_post_types() {
	if ( post_type_exists( 'portfolio' ) ) {
		return;
	}

	register_post_type( 'portfolio', [
		'label'        => __( 'دست دوم', 'glassmorphism-child-pro' ),
		'labels'       => [
			'name'          => __( 'دست دوم', 'glassmorphism-child-pro' ),
			'singular_name' => __( 'آگهی', 'glassmorphism-child-pro' ),
			'menu_name'     => __( 'دست دوم', 'glassmorphism-child-pro' ),
			'add_new_item'  => __( 'افزودن آگهی جدید', 'glassmorphism-child-pro' ),
			'edit_item'     => __( 'ویرایش آگهی', 'glassmorphism-child-pro' ),
			'view_item'     => __( 'مشاهده آگهی', 'glassmorphism-child-pro' ),
			'all_items'     => __( 'همه آگهی‌ها', 'glassmorphism-child-pro' ),
			'search_items'  => __( 'جستجو در آگهی‌ها', 'glassmorphism-child-pro' ),
			'not_found'     => __( 'آگهی‌ای یافت نشد', 'glassmorphism-child-pro' ),
		],
		'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ],
		'public'       => true,
		'show_ui'      => true,
		'show_in_rest' => true,
		'menu_icon'    => 'dashicons-layout',
		'has_archive'  => true,
		'rewrite'      => [ 'slug' => 'دست-دوم', 'with_front' => false ],
		'pll_translatable' => true,
	] );
}

/* ────────────────────────────────────────
   Taxonomy: themsah_theme_type (دسته‌بندی‌ها) — slug «portfolio_cat» حفظ‌شده
   ──────────────────────────────────────── */
add_action( 'init', 'glass_register_taxonomies', 0 );
/**
 * ثبت تاکسونومی دسته‌بندی آگهی.
 *
 * @return void
 */
function glass_register_taxonomies() {
	if ( ! taxonomy_exists( 'themsah_theme_type' ) ) {
		register_taxonomy( 'themsah_theme_type', [ 'portfolio' ], [
			'labels'       => [ 'name' => __( 'دسته‌بندی‌ها', 'glassmorphism-child-pro' ), 'singular_name' => __( 'دسته‌بندی', 'glassmorphism-child-pro' ) ],
			'public'       => true,
			'hierarchical' => true,
			'show_ui'      => true,
			'show_in_rest' => true,
			'rewrite'      => [ 'slug' => 'portfolio_cat', 'with_front' => false ],
			'pll_translatable' => true,
		] );
	}
}

/* ────────────────────────────────────────
   Rewrite: aliasهای صفحه‌ی تماس (حفظ‌شده عیناً)
   ──────────────────────────────────────── */
add_action( 'init', 'glass_contact_rewrite_rules' );
/**
 * ثبت قواعد بازنویسی برای آدرس‌های مستعار صفحه‌ی تماس.
 *
 * @return void
 */
function glass_contact_rewrite_rules() {
	$aliases = [ 'contact-us', 'iletisim', 'connection', 'teams', 'support', 'help' ];
	foreach ( $aliases as $alias ) {
		add_rewrite_rule( '^' . $alias . '/?$', 'index.php?pagename=contact', 'top' );
	}
}

/* ────────────────────────────────────────
   Flush فقط هنگام فعال‌سازی تم (نه روی هر بارگذاری)
   ──────────────────────────────────────── */
add_action( 'after_switch_theme', 'glass_flush_rewrite_rules' );
/**
 * پاکسازی و بازسازی قواعد بازنویسی پس از فعال‌سازی تم.
 *
 * @return void
 */
function glass_flush_rewrite_rules() {
	glass_register_post_types();
	glass_register_taxonomies();
	glass_contact_rewrite_rules();
	flush_rewrite_rules();
}
