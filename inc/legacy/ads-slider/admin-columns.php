<?php
/** Ads Slider — Admin List Columns (preview, sortable) */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ════════════════════════════════════════
   3. ستون پیش‌نمایش در لیست اسلایدها
   ════════════════════════════════════════ */
add_filter( 'manage_glass_ad_posts_columns', 'glass_ad_columns' );
/**
 * ستون‌های لیست اسلایدها.
 *
 * @param array $columns ستون‌ها.
 * @return array
 */
function glass_ad_columns( $columns ) {
	$new = [];
	$new['cb']             = $columns['cb'] ?? '';
	$new['glass_ad_thumb'] = 'پیش‌نمایش';
	$new['title']          = 'عنوان';
	$new['glass_ad_link']  = 'لینک';
	$new['menu_order']     = 'ترتیب';
	$new['date']           = $columns['date'] ?? 'تاریخ';
	return $new;
}

add_action( 'manage_glass_ad_posts_custom_column', 'glass_ad_column_content', 10, 2 );
/**
 * محتوای ستون‌های سفارشی.
 *
 * @param string $column  نام ستون.
 * @param int    $post_id شناسهٔ پست.
 * @return void
 */
function glass_ad_column_content( $column, $post_id ) {
	if ( 'glass_ad_thumb' === $column ) {
		echo has_post_thumbnail( $post_id )
			? get_the_post_thumbnail( $post_id, [ 120, 48 ], [ 'style' => 'border-radius:6px;object-fit:cover;' ] )
			: '<span style="color:#334155;">بدون تصویر</span>';
	} elseif ( 'glass_ad_link' === $column ) {
		$link = get_post_meta( $post_id, '_glass_ad_link', true );
		echo $link ? '<a href="' . esc_url( $link ) . '" target="_blank" style="direction:ltr;display:inline-block;">' . esc_html( wp_trim_words( $link, 6, '…' ) ) . '</a>' : '—';
	} elseif ( 'menu_order' === $column ) {
		echo (int) get_post_field( 'menu_order', $post_id );
	}
}

add_filter( 'manage_edit-glass_ad_sortable_columns', function ( $cols ) {
	$cols['menu_order'] = 'menu_order';
	return $cols;
} );

