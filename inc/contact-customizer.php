<?php
/**
 * Contact Info Customizer — جایگزین تمام مقادیر hardcode (شماره، URL، شبکه‌ها).
 *
 * این فایل از نسخه 5.0.4 اضافه شده تا اطلاعات تماس و لینک‌های اجتماعی که
 * قبلاً در single.php / single-portfolio.php به‌صورت hardcode بودند،
 * به Customizer منتقل شوند و کاربر بتواند از پنل تنظیم کند.
 *
 * API استفاده (در template ها):
 *   glass_pro_contact( 'phone_sales_1', '0912 139 0683' )
 *   glass_pro_contact( 'whatsapp_catalog', '989121390683' )
 *   glass_pro_contact( 'telegram_url', 'https://t.me/alborzghaleb' )
 *
 * فیلتر سراسری:
 *   glass_pro/contact/{key}  →  override مقدار از کد سفارشی
 *
 * @package Alborz_Ghaleb
 * @since   5.0.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ────────────────────────────────────────
   مقادیر پیش‌فرض (حفظ سازگاری با سایت اصلی)
   ──────────────────────────────────────── */

/**
 * مقادیر پیش‌فرض برای اطلاعات تماس. این مقادیر فقط fallback هستند —
 * در صورت تنظیم در Customizer override می‌شوند.
 *
 * @return array<string,string>
 */
function glass_pro_contact_defaults(): array {
	// [PERF v5.0.5] static cache — جلوگیری از reconstruction آرایه در هر فراخوانی
	static $defaults = null;
	if ( null !== $defaults ) {
		return $defaults;
	}
	$defaults = [
		// شماره‌های موبایل
		'phone_sales_1'      => '0912 139 0683',
		'phone_sales_1_tel'  => '09121390683',
		'phone_sales_2'      => '0910 361 4454',
		'phone_sales_2_tel'  => '09103614454',
		'phone_support'      => '0935 139 0683',
		'phone_support_tel'  => '09351390683',
		// شماره‌های ثابت
		'phone_office_1'     => '026 3472 0146',
		'phone_office_1_tel' => '02634720146',
		'phone_office_2'     => '026 3472 0147',
		'phone_office_2_tel' => '02634720147',
		// واتساپ
		'whatsapp_number'    => '989121390683',
		'whatsapp_catalog'   => 'https://wa.me/c/989121390683',
		// شبکه‌های اجتماعی
		'telegram_url'       => 'https://t.me/alborzghaleb',
		'instagram_url'      => 'https://instagram.com/alborz_ghaleb',
		// ایمیل
		'email'              => '',
		// عناوین برچسب
		'lbl_sales'          => 'فروش',
		'lbl_support'        => 'پشتیبانی',
		// نام برند برای SEO/JSON-LD
		'brand_name'         => '',
		'brand_tagline'      => 'مرکز تخصصی قالب و تجهیزات بتن',
		// CTA متن‌ها
		'cta_price_call'     => 'برای قیمت تماس بگیرید',
	];
	return $defaults;
}

/* ────────────────────────────────────────
   API: glass_pro_contact()
   ──────────────────────────────────────── */

/**
 * دریافت یک مقدار اطلاعات تماس با fallback به پیش‌فرض.
 *
 * @param string      $key
 * @param string|null $default در صورت null، از glass_pro_contact_defaults() گرفته می‌شود.
 * @return string
 */
function glass_pro_contact( string $key, ?string $default = null ): string {
	// [PERF v5.0.5] Cache per-request برای جلوگیری از get_theme_mod تکراری.
	// در template ها (مثل single.php) چندین بار با key یکسان صدا زده می‌شود.
	static $cache = [];
	$cache_key = $key . '|' . (string) $default;
	if ( isset( $cache[ $cache_key ] ) ) {
		return $cache[ $cache_key ];
	}

	$defaults = glass_pro_contact_defaults();
	if ( null === $default ) {
		$default = $defaults[ $key ] ?? '';
	}
	$value = get_theme_mod( 'glass_pro_contact_' . $key, $default );
	/**
	 * فیلتر مقدار اطلاعات تماس.
	 *
	 * @param string $value
	 * @param string $key
	 * @param string $default
	 */
	$result = (string) apply_filters( 'glass_pro/contact/' . $key, $value, $key, $default );
	$cache[ $cache_key ] = $result;
	return $result;
}

/* ────────────────────────────────────────
   ثبت Section + Settings + Controls در Customizer
   ──────────────────────────────────────── */
add_action( 'customize_register', 'glass_pro_contact_customize_register', 30 );
/**
 * ثبت پنل اطلاعات تماس در Customizer.
 *
 * @param WP_Customize_Manager $wp_customize
 * @return void
 */
function glass_pro_contact_customize_register( $wp_customize ) {

	// [UX v5.0.5] Panel سراسری برای تجمیع همه‌ی تنظیمات قالب
	if ( ! $wp_customize->get_panel( 'glass_pro_panel' ) ) {
		$wp_customize->add_panel( 'glass_pro_panel', [
			'title'       => __( 'Alborz Ghaleb', 'glassmorphism-child-pro' ),
			'description' => __( 'تنظیمات قالب — اطلاعات تماس، پرداخت، پنل کاربری و غیره.', 'glassmorphism-child-pro' ),
			'priority'    => 25,
		] );
	}

	$wp_customize->add_section( 'glass_pro_contact', [
		'title'       => __( 'اطلاعات تماس و شبکه‌های اجتماعی', 'glassmorphism-child-pro' ),
		'description' => __( 'اطلاعاتی که در صفحات تک و سراسر سایت نمایش داده می‌شود.', 'glassmorphism-child-pro' ),
		'panel'       => 'glass_pro_panel',
		'priority'    => 10,
	] );

	$fields = [
		// موبایل فروش
		'phone_sales_1'     => [ 'label' => 'موبایل فروش ۱ (نمایش)',        'type' => 'text' ],
		'phone_sales_1_tel' => [ 'label' => 'موبایل فروش ۱ (لینک tel:)',    'type' => 'text' ],
		'phone_sales_2'     => [ 'label' => 'موبایل فروش ۲ (نمایش)',        'type' => 'text' ],
		'phone_sales_2_tel' => [ 'label' => 'موبایل فروش ۲ (لینک tel:)',    'type' => 'text' ],
		// پشتیبانی
		'phone_support'     => [ 'label' => 'موبایل پشتیبانی (نمایش)',      'type' => 'text' ],
		'phone_support_tel' => [ 'label' => 'موبایل پشتیبانی (لینک tel:)',  'type' => 'text' ],
		// دفتر
		'phone_office_1'     => [ 'label' => 'تلفن دفتر ۱ (نمایش)',         'type' => 'text' ],
		'phone_office_1_tel' => [ 'label' => 'تلفن دفتر ۱ (لینک tel:)',     'type' => 'text' ],
		'phone_office_2'     => [ 'label' => 'تلفن دفتر ۲ (نمایش)',         'type' => 'text' ],
		'phone_office_2_tel' => [ 'label' => 'تلفن دفتر ۲ (لینک tel:)',     'type' => 'text' ],
		// واتساپ
		'whatsapp_number'   => [ 'label' => 'شماره واتساپ (با کد کشور)',    'type' => 'text' ],
		'whatsapp_catalog'  => [ 'label' => 'لینک کاتالوگ واتساپ',          'type' => 'url'  ],
		// شبکه‌ها
		'telegram_url'      => [ 'label' => 'آدرس کانال تلگرام',            'type' => 'url'  ],
		'instagram_url'     => [ 'label' => 'آدرس پیج اینستاگرام',          'type' => 'url'  ],
		// ایمیل و برند
		'email'             => [ 'label' => 'ایمیل تماس',                   'type' => 'email' ],
		'brand_name'        => [ 'label' => 'نام برند (در صورت خالی: نام سایت)', 'type' => 'text' ],
		'brand_tagline'     => [ 'label' => 'شعار/زیرعنوان برند',           'type' => 'text' ],
		// متن‌های UI
		'lbl_sales'         => [ 'label' => 'برچسب فروش',                   'type' => 'text' ],
		'lbl_support'       => [ 'label' => 'برچسب پشتیبانی',               'type' => 'text' ],
		'cta_price_call'    => [ 'label' => 'متن CTA «برای قیمت تماس بگیرید»', 'type' => 'text' ],
	];

	$defaults = glass_pro_contact_defaults();

	/* Toggle های نمایش بخش‌های شرکت‌محور */
	$wp_customize->add_setting( 'glass_pro_single_cats_enabled', [
		'default'           => true,
		'sanitize_callback' => 'rest_sanitize_boolean',
		'transport'         => 'refresh',
		'capability'        => 'edit_theme_options',
	] );
	$wp_customize->add_control( 'glass_pro_single_cats_enabled', [
		'label'       => __( 'نمایش اسلایدر دسته‌بندی محصولات در پست‌های وبلاگ', 'glassmorphism-child-pro' ),
		'description' => __( 'برای پنهان کردن این بخش، تیک را بردارید.', 'glassmorphism-child-pro' ),
		'section'     => 'glass_pro_contact',
		'type'        => 'checkbox',
	] );

	$wp_customize->add_setting( 'glass_pro_portfolio_cats_enabled', [
		'default'           => true,
		'sanitize_callback' => 'rest_sanitize_boolean',
		'transport'         => 'refresh',
		'capability'        => 'edit_theme_options',
	] );
	$wp_customize->add_control( 'glass_pro_portfolio_cats_enabled', [
		'label'       => __( 'نمایش اسلایدر دسته‌بندی محصولات در صفحه آگهی‌ها', 'glassmorphism-child-pro' ),
		'description' => __( 'برای پنهان کردن این بخش، تیک را بردارید.', 'glassmorphism-child-pro' ),
		'section'     => 'glass_pro_contact',
		'type'        => 'checkbox',
	] );

	foreach ( $fields as $key => $cfg ) {
		$setting_id = 'glass_pro_contact_' . $key;
		$sanitize   = 'sanitize_text_field';
		if ( 'url' === $cfg['type'] ) {
			$sanitize = 'esc_url_raw';
		} elseif ( 'email' === $cfg['type'] ) {
			$sanitize = 'sanitize_email';
		}

		$wp_customize->add_setting( $setting_id, [
			'default'           => $defaults[ $key ] ?? '',
			'sanitize_callback' => $sanitize,
			'transport'         => 'refresh',
			'capability'        => 'edit_theme_options',
		] );

		$wp_customize->add_control( $setting_id, [
			'label'   => $cfg['label'],
			'section' => 'glass_pro_contact',
			'type'    => 'url' === $cfg['type'] ? 'url' : ( 'email' === $cfg['type'] ? 'email' : 'text' ),
		] );
	}
}
