<?php
/** Ads Slider — HTML Renderer */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ════════════════════════════════════════
   5. رندر HTML اسلایدر (مشترک)
   ════════════════════════════════════════ */
/**
 * تولید HTML کامل اسلایدر.
 *
 * @param array $args پارامترهای پیکربندی.
 * @return string
 */
function glass_ads_render( $args = [] ) {
	$args = wp_parse_args( $args, [
		'count'        => -1,
		'group'        => '',
		'autoplay'     => 5000,
		'loop'         => 1,
		'effect'       => 'slide',   // slide | fade
		'arrows'       => 1,
		'dots'         => 1,
		'progress'     => 1,
		'pause_hover'  => 1,
		'per_desktop'  => 2,
		'per_tablet'   => 1,
		'per_mobile'   => 1,
		'gap'          => 24,
		'ratio'        => '2.5',     // نسبت عرض به ارتفاع
	] );

	$slides = glass_ads_get_slides( $args['count'], $args['group'] );
	if ( empty( $slides ) ) {
		return '';
	}

	$uid = 'glassAds' . wp_rand( 1000, 9999 );

	$data = [
		'autoplay'   => (int) $args['autoplay'],
		'loop'       => (int) $args['loop'],
		'effect'     => in_array( $args['effect'], [ 'slide', 'fade' ], true ) ? $args['effect'] : 'slide',
		'arrows'     => (int) $args['arrows'],
		'dots'       => (int) $args['dots'],
		'progress'   => (int) $args['progress'],
		'pauseHover' => (int) $args['pause_hover'],
		'perDesktop' => max( 1, (int) $args['per_desktop'] ),
		'perTablet'  => max( 1, (int) $args['per_tablet'] ),
		'perMobile'  => max( 1, (int) $args['per_mobile'] ),
		'gap'        => max( 0, (int) $args['gap'] ),
	];

	ob_start();
	?>
	<div class="glass-ads-slider glass-ads--<?php echo esc_attr( $data['effect'] ); ?>"
		id="<?php echo esc_attr( $uid ); ?>"
		style="--gads-gap:<?php echo (int) $data['gap']; ?>px;--gads-ratio:<?php echo esc_attr( $args['ratio'] ); ?>;"
		data-config='<?php echo esc_attr( wp_json_encode( $data ) ); ?>'
		role="region"
		aria-roledescription="carousel"
		aria-label="<?php esc_attr_e( 'تبلیغات', 'glassmorphism-child-pro' ); ?>">

		<div class="glass-ads-viewport">
			<div class="glass-ads-track" aria-live="polite">
				<?php foreach ( $slides as $i => $s ) :
					$has_link = ! empty( $s['link'] );
					$wrap_tag = ( $has_link && ! $s['show'] ) ? 'a' : 'div'; // در حالت محتوادار، لینک روی دکمه است
					// whitelist سخت‌گیرانه: فقط 'a' یا 'div' مجاز است
					$wrap_tag = in_array( $wrap_tag, [ 'a', 'div' ], true ) ? $wrap_tag : 'div';
					?>
					<<?php echo esc_html( $wrap_tag ); ?> class="glass-ads-slide"
						role="group" aria-roledescription="slide"
						aria-label="<?php echo esc_attr( ( $i + 1 ) . ' / ' . count( $slides ) ); ?>"
						<?php if ( 'a' === $wrap_tag ) : ?>
							href="<?php echo esc_url( $s['link'] ); ?>"
							<?php echo $s['new_tab'] ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>
						<?php endif; ?>>

						<img class="glass-ads-img" src="<?php echo esc_url( $s['img'] ); ?>"
							alt="<?php echo esc_attr( $s['alt'] ); ?>"
							loading="<?php echo 0 === $i ? 'eager' : 'lazy'; ?>"
							decoding="async">

						<?php if ( $s['show'] && ( $s['title'] || $s['subtitle'] || $s['desc'] || $s['btn_text'] ) ) : ?>
							<?php if ( $s['overlay'] > 0 ) : ?>
								<span class="glass-ads-overlay" style="opacity:<?php echo esc_attr( $s['overlay'] / 100 ); ?>"></span>
							<?php endif; ?>
							<div class="glass-ads-content glass-ads-pos-<?php echo esc_attr( $s['position'] ); ?>" style="color:<?php echo esc_attr( $s['color'] ); ?>">
								<?php if ( $s['subtitle'] ) : ?><span class="glass-ads-sub"><?php echo esc_html( $s['subtitle'] ); ?></span><?php endif; ?>
								<?php if ( $s['title'] ) : ?><h3 class="glass-ads-title"><?php echo esc_html( $s['title'] ); ?></h3><?php endif; ?>
								<?php if ( $s['desc'] ) : ?><p class="glass-ads-desc"><?php echo esc_html( $s['desc'] ); ?></p><?php endif; ?>
								<?php if ( $s['btn_text'] && $has_link ) : ?>
									<a class="glass-ads-btn" href="<?php echo esc_url( $s['link'] ); ?>" <?php echo $s['new_tab'] ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>><?php echo esc_html( $s['btn_text'] ); ?></a>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</<?php echo esc_html( $wrap_tag ); ?>>
				<?php endforeach; ?>
			</div>
		</div>

		<?php if ( $data['arrows'] ) : ?>
			<button class="glass-ads-arrow glass-ads-prev" type="button" aria-label="<?php esc_attr_e( 'قبلی', 'glassmorphism-child-pro' ); ?>">
				<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 6 15 12 9 18"/></svg>
			</button>
			<button class="glass-ads-arrow glass-ads-next" type="button" aria-label="<?php esc_attr_e( 'بعدی', 'glassmorphism-child-pro' ); ?>">
				<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 6 9 12 15 18"/></svg>
			</button>
		<?php endif; ?>

		<?php if ( $data['progress'] && $data['autoplay'] > 0 ) : ?>
			<div class="glass-ads-progress"><span class="glass-ads-progress-bar"></span></div>
		<?php endif; ?>

		<?php if ( $data['dots'] ) : ?>
			<div class="glass-ads-dots" role="tablist" aria-label="<?php esc_attr_e( 'انتخاب اسلاید', 'glassmorphism-child-pro' ); ?>"></div>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}

