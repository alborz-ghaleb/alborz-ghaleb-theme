<?php
/**
 * Elementor Integration
 *
 * ثبت دسته‌بندی سفارشی + بارگذاری ویجت‌های اختصاصی تم.
 * فقط در صورت فعال بودن Elementor اجرا می‌شود.
 *
 * @package Alborz_Ghaleb
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ────────────────────────────────────────
   دسته‌بندی سفارشی ویجت‌ها
   ──────────────────────────────────────── */
add_action( 'elementor/elements/categories_registered', 'glass_pro_elementor_category' );
/**
 * ثبت دسته‌بندی «Alborz Ghaleb» در پنل Elementor.
 *
 * @param \Elementor\Elements_Manager $manager مدیر المان‌ها.
 * @return void
 */
function glass_pro_elementor_category( $manager ) {
	$manager->add_category(
		'glassmorphism-child-pro',
		[
			'title' => __( 'Alborz Ghaleb', 'glassmorphism-child-pro' ),
			'icon'  => 'eicon-stack',
		]
	);
}

/* ────────────────────────────────────────
   ثبت ویجت‌ها
   ──────────────────────────────────────── */
add_action( 'elementor/widgets/register', 'glass_pro_register_widgets' );
/**
 * بارگذاری و ثبت کلاس‌های ویجت سفارشی.
 *
 * @param \Elementor\Widgets_Manager $widgets_manager مدیر ویجت‌ها.
 * @return void
 */
function glass_pro_register_widgets( $widgets_manager ) {

	$widget_files = [
		'/inc/widgets/class-widget-glass-card.php'    => 'Glass_Pro_Widget_Card',
		'/inc/widgets/class-widget-glass-ad-grid.php' => 'Glass_Pro_Widget_Ad_Grid',
	];

	foreach ( $widget_files as $file => $class ) {
		$path = GLASS_PRO_DIR . $file;
		if ( is_readable( $path ) ) {
			require_once $path;
			if ( class_exists( $class ) ) {
				$widgets_manager->register( new $class() );
			}
		}
	}

	// ویجت‌های محتوایی شیشه‌ای (تیتر، پاراگراف، دکمه، جدول، FAQ) — همه در یک فایل.
	$content_widgets_path = GLASS_PRO_DIR . '/inc/widgets/class-widget-glass-content.php';
	if ( is_readable( $content_widgets_path ) ) {
		require_once $content_widgets_path;
		foreach ( [
			'Glass_CS_Widget_Heading',
			'Glass_CS_Widget_Paragraph',
			'Glass_CS_Widget_Button',
			'Glass_CS_Widget_Table',
			'Glass_CS_Widget_FAQ',
		] as $content_widget_class ) {
			if ( class_exists( $content_widget_class ) ) {
				$widgets_manager->register( new $content_widget_class() );
			}
		}
	}
}
