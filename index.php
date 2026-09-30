<?php
/**
 * Main blog listing template (fallback for post archives).
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
			<?php if ( is_home() && ! is_front_page() ) : ?>
				<h1 class="kdv-section__title"><?php single_post_title(); ?></h1>
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
					<p><?php esc_html_e( 'Vuelve pronto, estamos preparando contenido nuevo.', 'revista-koltor-dev' ); ?></p>
				</div>
			<?php endif; ?>
		</main>

		<?php get_sidebar(); ?>
	</div>
</div>
<?php get_footer(); ?>
