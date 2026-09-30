<?php
/**
 * Generic archive template — covers taxonomies (Género, Estudio), tags, author, dates.
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
				<h1 class="kdv-section__title"><?php the_archive_title(); ?></h1>
			</header>

			<?php kdv_render_platform_filter_notice(); ?>

			<?php the_archive_description( '<div class="kdv-card__excerpt">', '</div>' ); ?>

			<?php if ( have_posts() ) : ?>
				<div class="kdv-cards-grid kdv-cards-grid--cols-3">
					<?php
					while ( have_posts() ) :
						the_post();
						if ( 'kdv_resena' === get_post_type() ) {
							get_template_part( 'template-parts/content/review-card' );
						} else {
							get_template_part( 'template-parts/content/post-card' );
						}
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
