<?php
/**
 * Archive template for the "Reseña" custom post type.
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="kdv-container">
	<div class="kdv-content kdv-content__grid--wide">
		<header class="kdv-section__head">
			<h1 class="kdv-section__title"><?php esc_html_e( 'Todas las reseñas', 'revista-koltor-dev' ); ?></h1>
		</header>

		<?php kdv_render_platform_filter_notice(); ?>

		<?php if ( have_posts() ) : ?>
			<div class="kdv-cards-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content/review-card' );
				endwhile;
				?>
			</div>
			<?php the_posts_pagination( [ 'class' => 'kdv-pagination' ] ); ?>
		<?php else : ?>
			<div class="kdv-empty-state">
				<h1><?php esc_html_e( 'Todavía no hay reseñas publicadas', 'revista-koltor-dev' ); ?></h1>
			</div>
		<?php endif; ?>
	</div>
</div>
<?php get_footer(); ?>
