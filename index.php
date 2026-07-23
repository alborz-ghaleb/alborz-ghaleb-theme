<?php
/**
 * Main Template File (Standalone fallback)
 *
 * این فایل آخرین fallback سلسله‌مراتب قالب وردپرس است. بیشترِ نماها
 * (وبلاگ، آرشیو، تک‌نوشته، برگه، جستجو، ۴۰۴) قالب اختصاصی خود را دارند؛
 * این فایل برای پوشش کامل و استقلال قالب نگه داشته شده است.
 *
 * @package Alborz_Ghaleb
 * @version 5.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="main-content" class="site-main">
	<div class="container">
		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();

				if ( is_singular() ) {
					the_content();
				} else {
					?>
					<article <?php post_class( 'gl-archive-card' ); ?> style="margin-bottom:24px;">
						<a class="gl-archive-card__link" href="<?php the_permalink(); ?>">
							<?php if ( has_post_thumbnail() ) : ?>
								<div class="gl-archive-card__thumb">
									<?php the_post_thumbnail( 'glass-card', [ 'loading' => 'lazy' ] ); ?>
								</div>
							<?php endif; ?>
							<div class="gl-archive-card__body">
								<h2 class="gl-archive-card__title" style="font-weight:700;"><?php the_title(); ?></h2>
								<p class="gl-archive-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p>
							</div>
						</a>
					</article>
					<?php
				}
			endwhile;

			the_posts_pagination(
				[
					'mid_size'  => 1,
					'prev_text' => esc_html__( 'قبلی', 'glassmorphism-child-pro' ),
					'next_text' => esc_html__( 'بعدی', 'glassmorphism-child-pro' ),
				]
			);
		else :
			get_template_part( 'template-parts/content/content', 'none' );
		endif;
		?>
	</div>
</main>

<?php
get_footer();
