<?php
/**
 * Static page template.
 *
 * If the page was built with Elementor (or Elementor Pro), its content
 * renders full-width, without the theme's title/thumbnail chrome or the
 * narrow 780px reading-width wrapper — so Elementor's own "stretch
 * section" / full-width layouts aren't squeezed by the theme. The header
 * and footer (driven by Apariencia → Personalizar) still wrap around it.
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$is_elementor_page = kdv_is_built_with_elementor( get_queried_object_id() );
?>

<?php if ( $is_elementor_page ) : ?>

	<?php while ( have_posts() ) : the_post(); ?>
		<?php the_content(); ?>
	<?php endwhile; ?>

<?php else : ?>

	<div class="kdv-container">
		<div class="kdv-content kdv-content__grid--full">
			<?php while ( have_posts() ) : the_post(); ?>
				<article <?php post_class( 'kdv-post' ); ?>>
					<h1 class="kdv-post__title"><?php the_title(); ?></h1>

					<?php if ( has_post_thumbnail() ) : ?>
						<div class="kdv-post__thumbnail"><?php the_post_thumbnail( 'kdv-hero' ); ?></div>
					<?php endif; ?>

					<div class="kdv-post__body">
						<?php the_content(); ?>
					</div>
				</article>

				<?php if ( comments_open() || get_comments_number() ) : ?>
					<?php comments_template(); ?>
				<?php endif; ?>
			<?php endwhile; ?>
		</div>
	</div>

<?php endif; ?>

<?php get_footer(); ?>
