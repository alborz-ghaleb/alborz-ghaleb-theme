<?php
/**
 * Archive Template (Standalone fallback, Alborz Ghaleb)
 *
 * بیشترِ آرشیوها (وبلاگ، دسته، برچسب، آرشیو portfolio) توسط
 * template_include در ماژول‌های legacy رندر می‌شوند. این قالب یک
 * fallback تمیز و مستقل برای سایر آرشیوهاست (نویسنده، تاریخ،
 * تکسونومی‌های سفارشی) تا دیگر به index.php ساده تکیه نکنیم.
 *
 * @package Alborz_Ghaleb
 * @version 5.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Stage 42: Enqueue external CSS به جای inline — بهبود CSP و کش
wp_enqueue_style( 'glass-archive-fallback', GLASS_PRO_URI . '/assets/css/archive-fallback.css', [ 'glass-style' ], GLASS_PRO_VERSION );

get_header();
?>

<?php do_action( 'glass_pro/before_main', 'archive' ); ?>
<main id="main-content" class="gl-archive-wrap">
	<div class="container">

		<header class="gl-archive-head">
			<h1 class="gl-archive-title"><?php the_archive_title(); ?></h1>
			<?php
			$gl_desc = get_the_archive_description();
			if ( $gl_desc ) {
				echo '<div class="gl-archive-desc">' . wp_kses_post( $gl_desc ) . '</div>';
			}
			?>
		</header>

		<?php if ( have_posts() ) : ?>

			<div class="gl-archive-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article <?php post_class( 'gl-archive-card' ); ?>>
						<a class="gl-archive-card__link" href="<?php the_permalink(); ?>">
							<div class="gl-archive-card__thumb">
								<?php
								if ( has_post_thumbnail() ) {
							the_post_thumbnail(
								'glass-card',
								[
									'loading' => 'lazy',
								]
							);
								}
								?>
							</div>
							<div class="gl-archive-card__body">
								<span class="gl-archive-card__date"><?php echo esc_html( get_the_date() ); ?></span>
								<h2 class="gl-archive-card__title" style="font-weight:700;"><?php the_title(); ?></h2>
								<p class="gl-archive-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
							</div>
						</a>
					</article>
					<?php
				endwhile;
				?>
			</div>

			<div class="gl-archive-pagination">
				<?php
				the_posts_pagination(
					[
						'mid_size'  => 1,
						'prev_text' => sprintf( esc_html__( 'قبلی: صفحه %d', 'glassmorphism-child-pro' ), max(1, get_query_var('paged')) - 1 ),
						'next_text' => sprintf( esc_html__( 'بعدی: صفحه %d', 'glassmorphism-child-pro' ), max(1, get_query_var('paged')) + 1 ),
					]
				);
				?>
			</div>

		<?php else : ?>

			<?php get_template_part( 'template-parts/content/content', 'none' ); ?>

		<?php endif; ?>

	</div>
</main>
<?php do_action( 'glass_pro/after_main', 'archive' ); ?>

<?php
get_footer();
