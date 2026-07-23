<?php
/** Ads Slider — Rich Meta Box (settings + image picker + save handler) */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ════════════════════════════════════════
   2. متاباکس غنی هر اسلاید
   ════════════════════════════════════════ */
add_action( 'add_meta_boxes', 'glass_ad_add_meta_box' );
/**
 * افزودن متاباکس تنظیمات اسلاید.
 *
 * @return void
 */
function glass_ad_add_meta_box() {
	add_meta_box(
		'glass_ad_link_box',
		esc_html__( 'تنظیمات اسلاید تبلیغی', 'glassmorphism-child-pro' ),
		'glass_ad_render_meta_box',
		'glass_ad',
		'normal',
		'high'
	);
}

/**
 * رندر فرم متاباکس.
 *
 * @param WP_Post $post پست جاری.
 * @return void
 */
function glass_ad_render_meta_box( $post ) {
	wp_nonce_field( 'glass_ad_save_meta', 'glass_ad_nonce' );

	$link      = get_post_meta( $post->ID, '_glass_ad_link', true );
	$new_tab   = get_post_meta( $post->ID, '_glass_ad_new_tab', true );
	$show_ct   = get_post_meta( $post->ID, '_glass_ad_show_content', true );
	$subtitle  = get_post_meta( $post->ID, '_glass_ad_subtitle', true );
	$desc      = get_post_meta( $post->ID, '_glass_ad_desc', true );
	$btn_text  = get_post_meta( $post->ID, '_glass_ad_btn_text', true );
	$position  = get_post_meta( $post->ID, '_glass_ad_position', true ) ?: 'center';
	$text_col  = get_post_meta( $post->ID, '_glass_ad_text_color', true ) ?: '#ffffff';
	$overlay   = get_post_meta( $post->ID, '_glass_ad_overlay', true );
	$has_thumb = has_post_thumbnail( $post->ID );

	$positions = [
		'center'       => 'وسط',
		'center-left'  => 'وسطِ راست (RTL: راست)',
		'center-right' => 'وسطِ چپ (RTL: چپ)',
		'bottom-left'  => 'پایین راست',
		'bottom-right' => 'پایین چپ',
		'top-left'     => 'بالا راست',
		'top-right'    => 'بالا چپ',
	];
	?>
	<style>
		.glass-ad-mb label{font-weight:600;display:block;margin:14px 0 5px;}
		.glass-ad-mb input[type=url],.glass-ad-mb input[type=text],.glass-ad-mb textarea,.glass-ad-mb select{width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:6px;}
		.glass-ad-mb .row{display:flex;gap:16px;flex-wrap:wrap;}
		.glass-ad-mb .row>div{flex:1;min-width:200px;}
		.glass-ad-mb .hint{color:#334155;font-size:12px;margin-top:4px;}
		.glass-ad-content-fields{margin-top:6px;padding:14px;background:#f6f7f9;border-radius:8px;border:1px solid #e3e6ea;}
		.glass-ad-image-preview{margin:8px 0;}
		.glass-ad-image-preview img{max-width:340px;width:100%;height:auto;border-radius:8px;border:1px solid #ddd;box-shadow:0 2px 8px rgba(0,0,0,.08);}
	</style>
	<?php
	// تصویر اسلاید: از متای اختصاصی یا Featured Image (سازگاری)
	$img_id  = (int) get_post_meta( $post->ID, '_glass_ad_image_id', true );
	if ( ! $img_id && $has_thumb ) {
		$img_id = (int) get_post_thumbnail_id( $post->ID );
	}
	$img_url = $img_id ? wp_get_attachment_image_url( $img_id, 'medium' ) : '';
	?>
	<div class="glass-ad-mb">

		<label>🖼️ تصویر اسلاید:</label>
		<div class="glass-ad-image-field">
			<div class="glass-ad-image-preview" id="glassAdImgPreview" style="<?php echo $img_url ? '' : 'display:none;'; ?>">
				<img src="<?php echo esc_url( $img_url ); ?>" alt="">
			</div>
			<input type="hidden" name="glass_ad_image_id" id="glassAdImageId" value="<?php echo esc_attr( $img_id ); ?>">
			<p>
				<button type="button" class="button button-primary" id="glassAdUploadBtn">📤 انتخاب / آپلود تصویر</button>
				<button type="button" class="button" id="glassAdRemoveBtn" style="<?php echo $img_url ? '' : 'display:none;'; ?>">حذف تصویر</button>
			</p>
			<p class="hint">می‌توانید مستقیم از اینجا تصویر آپلود کنید. (این تصویر به‌عنوان «تصویر شاخص» هم ذخیره می‌شود.)</p>
		</div>

		<hr style="margin:16px 0;">

		<label>🔗 آدرس لینک اسلاید (اختیاری):</label>
		<input type="url" name="glass_ad_link" value="<?php echo esc_attr( $link ); ?>" placeholder="https://example.com/promo" style="direction:ltr;text-align:left;">
		<p class="hint">اگر خالی باشد و دکمه هم نداشته باشد، اسلاید قابل کلیک نخواهد بود.</p>

		<p style="margin-top:10px;">
			<label style="display:inline;font-weight:400;">
				<input type="checkbox" name="glass_ad_new_tab" value="1" <?php checked( $new_tab, '1' ); ?>> باز شدن لینک در تب جدید
			</label>
		</p>

		<hr style="margin:16px 0;">

		<p>
			<label style="display:inline;font-weight:600;">
				<input type="checkbox" id="glassAdShowContent" name="glass_ad_show_content" value="1" <?php checked( $show_ct, '1' ); ?>>
				✍️ نمایش محتوای متنی روی تصویر (عنوان/متن/دکمه)
			</label>
			<span class="hint">اگر فعال نباشد، اسلاید فقط تصویری خواهد بود (مثل بنر).</span>
		</p>

		<div class="glass-ad-content-fields" id="glassAdContentFields" style="<?php echo $show_ct ? '' : 'display:none;'; ?>">
			<div class="row">
				<div>
					<label>عنوان (از فیلد «عنوان» بالای صفحه استفاده می‌شود)</label>
					<input type="text" value="<?php echo esc_attr( get_the_title( $post ) ); ?>" disabled style="background:#eee;">
					<p class="hint">عنوان همان «عنوان اسلاید» در بالای صفحه است.</p>
				</div>
				<div>
					<label>زیرعنوان</label>
					<input type="text" name="glass_ad_subtitle" value="<?php echo esc_attr( $subtitle ); ?>" placeholder="مثلاً: فروش ویژه تابستان">
				</div>
			</div>

			<label>توضیح کوتاه</label>
			<textarea name="glass_ad_desc" rows="2" placeholder="یک یا دو خط توضیح..."><?php echo esc_textarea( $desc ); ?></textarea>

			<div class="row">
				<div>
					<label>متن دکمه (CTA)</label>
					<input type="text" name="glass_ad_btn_text" value="<?php echo esc_attr( $btn_text ); ?>" placeholder="مثلاً: مشاهده / خرید">
					<p class="hint">لینک دکمه همان «آدرس لینک» بالاست.</p>
				</div>
				<div>
					<label>موقعیت متن روی تصویر</label>
					<select name="glass_ad_position">
						<?php foreach ( $positions as $val => $lbl ) : ?>
							<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $position, $val ); ?>><?php echo esc_html( $lbl ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>

			<div class="row">
				<div>
					<label>رنگ متن</label>
					<input type="text" name="glass_ad_text_color" value="<?php echo esc_attr( $text_col ); ?>" placeholder="#ffffff" style="direction:ltr;">
				</div>
				<div>
					<label>شدت تیرگی روی تصویر (0 تا 100)</label>
					<input type="text" name="glass_ad_overlay" value="<?php echo esc_attr( $overlay !== '' ? $overlay : '35' ); ?>" placeholder="35" style="direction:ltr;">
					<p class="hint">برای خوانایی متن روی تصویر. پیشنهاد: ۳۰ تا ۵۰.</p>
				</div>
			</div>
		</div>

		<hr style="margin:16px 0;">
		<p class="hint" style="line-height:1.8;">
			📐 <strong>اندازهٔ پیشنهادی تصویر شاخص:</strong> دسکتاپ حدود <strong>۱۲۰۰×۴۸۰</strong> (نسبت ۲٫۵:۱).
			<?php if ( ! $has_thumb ) : ?>
				<br><span style="color:#c00;" aria-label="<?php esc_attr_e( 'هشدار', 'glassmorphism-child-pro' ); ?>">⚠</span> <span style="color:#c00;"><?php esc_html_e( 'هنوز «تصویر شاخص» تنظیم نشده است (پنل سمت چپ → تصویر شاخص).', 'glassmorphism-child-pro' ); ?></span>
			<?php endif; ?>
		</p>
	</div>

	<script <?php if ( function_exists( 'glass_pro_csp_nonce_attr' ) ) { glass_pro_csp_nonce_attr(); } ?>>
	(function(){
		// toggle بخش محتوا
		var cb = document.getElementById('glassAdShowContent');
		var box = document.getElementById('glassAdContentFields');
		if(cb && box){ cb.addEventListener('change', function(){ box.style.display = cb.checked ? '' : 'none'; }); }

		// Media Uploader برای تصویر اسلاید
		var frame;
		var uploadBtn = document.getElementById('glassAdUploadBtn');
		var removeBtn = document.getElementById('glassAdRemoveBtn');
		var idField   = document.getElementById('glassAdImageId');
		var preview   = document.getElementById('glassAdImgPreview');

		if (uploadBtn) {
			uploadBtn.addEventListener('click', function(e){
				e.preventDefault();
				// بررسی wp.media داخل هندلر (نه بیرون) تا اگر دیرتر لود شد هم کار کند.
				if (!window.wp || !window.wp.media) {
					alert('کتابخانهٔ رسانهٔ وردپرس بارگذاری نشده است. لطفاً صفحه را رفرش کنید.');
					return;
				}
				if (frame) { frame.open(); return; }
				frame = wp.media({
					title: 'انتخاب یا آپلود تصویر اسلاید',
					button: { text: 'استفاده از این تصویر' },
					library: { type: 'image' },
					multiple: false
				});
				frame.on('select', function(){
					var att = frame.state().get('selection').first().toJSON();
					if (idField) idField.value = att.id;
					var url = (att.sizes && att.sizes.medium) ? att.sizes.medium.url : att.url;
					if (preview) {
						var img = preview.querySelector('img');
						if (img) img.src = url;
						preview.style.display = '';
					}
					if (removeBtn) removeBtn.style.display = '';
				});
				frame.open();
			});
		}
		if (removeBtn) {
			removeBtn.addEventListener('click', function(e){
				e.preventDefault();
				if (idField) idField.value = '';
				if (preview) preview.style.display = 'none';
				removeBtn.style.display = 'none';
			});
		}
	})();
	</script>
	<?php
}

add_action( 'save_post_glass_ad', 'glass_ad_save_meta' );
/**
 * ذخیرهٔ متای اسلاید.
 *
 * @param int $post_id شناسهٔ پست.
 * @return void
 */
function glass_ad_save_meta( $post_id ) {
	if ( ! isset( $_POST['glass_ad_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['glass_ad_nonce'] ) ), 'glass_ad_save_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// تصویر اسلاید (آپلود مستقیم) — ذخیرهٔ متا + ست به‌عنوان Featured Image
	if ( isset( $_POST['glass_ad_image_id'] ) ) {
		$img_id = (int) $_POST['glass_ad_image_id'];
		if ( $img_id > 0 ) {
			update_post_meta( $post_id, '_glass_ad_image_id', $img_id );
			set_post_thumbnail( $post_id, $img_id ); // سازگاری: Featured Image هم همین می‌شود
		} else {
			delete_post_meta( $post_id, '_glass_ad_image_id' );
		}
	}

	// لینک + تب جدید (سازگاری قبلی)
	if ( isset( $_POST['glass_ad_link'] ) ) {
		update_post_meta( $post_id, '_glass_ad_link', esc_url_raw( trim( wp_unslash( $_POST['glass_ad_link'] ) ) ) );
	}
	update_post_meta( $post_id, '_glass_ad_new_tab', isset( $_POST['glass_ad_new_tab'] ) ? '1' : '' );

	// محتوای غنی
	update_post_meta( $post_id, '_glass_ad_show_content', isset( $_POST['glass_ad_show_content'] ) ? '1' : '' );
	update_post_meta( $post_id, '_glass_ad_subtitle', isset( $_POST['glass_ad_subtitle'] ) ? sanitize_text_field( wp_unslash( $_POST['glass_ad_subtitle'] ) ) : '' );
	update_post_meta( $post_id, '_glass_ad_desc', isset( $_POST['glass_ad_desc'] ) ? sanitize_textarea_field( wp_unslash( $_POST['glass_ad_desc'] ) ) : '' );
	update_post_meta( $post_id, '_glass_ad_btn_text', isset( $_POST['glass_ad_btn_text'] ) ? sanitize_text_field( wp_unslash( $_POST['glass_ad_btn_text'] ) ) : '' );

	$allowed_pos = [ 'center', 'center-left', 'center-right', 'bottom-left', 'bottom-right', 'top-left', 'top-right' ];
	$pos = isset( $_POST['glass_ad_position'] ) ? sanitize_text_field( wp_unslash( $_POST['glass_ad_position'] ) ) : 'center';
	update_post_meta( $post_id, '_glass_ad_position', in_array( $pos, $allowed_pos, true ) ? $pos : 'center' );

	$color = isset( $_POST['glass_ad_text_color'] ) ? sanitize_text_field( wp_unslash( $_POST['glass_ad_text_color'] ) ) : '#ffffff';
	update_post_meta( $post_id, '_glass_ad_text_color', $color );

	$overlay = isset( $_POST['glass_ad_overlay'] ) ? (int) $_POST['glass_ad_overlay'] : 35;
	$overlay = max( 0, min( 100, $overlay ) );
	update_post_meta( $post_id, '_glass_ad_overlay', (string) $overlay );
}

