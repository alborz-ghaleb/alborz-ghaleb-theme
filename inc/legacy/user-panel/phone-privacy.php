<?php
/** User Panel — Phone Number Visibility Filter */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ════════════════════════════════════════
   3. کنترل نمایش شماره تماس
   ════════════════════════════════════════
   اگر آگهی فروخته یا منقضی شده، شماره نمایش داده نمی‌شود.
   تابع کمکی که در single-portfolio و داشبورد استفاده می‌شود.
*/
function glass_ad_phone_visible( $post_id ) {
    return 'active' === glass_get_ad_state( $post_id );
}

/* فیلتر روی متای شماره تماس — هرجا که get_post_meta('portfolio_phone') خوانده شود */
add_filter( 'get_post_metadata', 'glass_filter_sold_phone', 10, 4 );
function glass_filter_sold_phone( $value, $object_id, $meta_key, $single ) {
    if ( 'portfolio_phone' !== $meta_key ) {
        return $value;
    }
    // فقط در فرانت‌اند فیلتر کن، نه در پیشخوان مدیریت
    if ( is_admin() ) {
        return $value;
    }
    if ( ! glass_ad_phone_visible( $object_id ) ) {
        // رشته خالی برگردان تا شماره نمایش داده نشود
        return $single ? '' : [ '' ];
    }
    return $value;
}

