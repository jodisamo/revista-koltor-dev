<?php
/**
 * Category archive template.
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="kdv-container">
	<div class="kdv-content kdv-content__grid">
		<main>
			<header class="kdv-section__head">
				<h1 class="kdv-section__title"><?php single_cat_title(); ?></h1>
			</header>

			<?php if ( category_description() ) : ?>
				<div class="kdv-card__excerpt"><?php echo wp_kses_post( category_description() ); ?></div>
			<?php endif; ?>

			<?php if ( have_posts() ) : ?>
				<div class="kdv-cards-grid kdv-cards-grid--cols-3">
					<?php
					while ( have_posts() ) :
						the_post();
						get_template_part( 'template-parts/content/post-card' );
					endwhile;
					?>
				</div>
				<?php the_posts_pagination( [ 'class' => 'kdv-pagination' ] ); ?>
			<?php else : ?>
				<div class="kdv-empty-state">
					<h1><?php esc_html_e( 'Nada por aquí todavía', 'revista-koltor-dev' ); ?></h1>
				</div>
			<?php endif; ?>
		</main>

		<?php get_sidebar(); ?>
	</div>
</div>
<?php get_footer(); ?>
