<?php
/**
 * Template Name: Ranking de Reseñas
 * Template Post Type: page
 *
 * Ranking page: lists every "Reseña" (kdv_resena) that has a final score,
 * ordered from the highest puntuación to the lowest. Create a Page in
 * WordPress and assign this template from "Atributos de página" in the
 * editor sidebar to use it — no other setup needed.
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$kdv_per_page = 10;
$kdv_paged    = max( 1, absint( get_query_var( 'paged' ) ) );

$kdv_ranking = new WP_Query( [
	'post_type'      => 'kdv_resena',
	'posts_per_page' => $kdv_per_page,
	'paged'          => $kdv_paged,
	'meta_key'       => '_kdv_score_final', // phpcs:ignore WordPress.DB.SlowDBQuery -- required for orderby=meta_value_num; also naturally excludes unscored reseñas from the ranking.
	'orderby'        => 'meta_value_num',
	'order'          => 'DESC',
] );
?>
<div class="kdv-container">
	<div class="kdv-content kdv-content__grid--full">
		<header class="kdv-section__head">
			<h1 class="kdv-section__title"><?php the_title(); ?></h1>
		</header>

		<?php if ( $kdv_ranking->have_posts() ) : ?>
			<ol class="kdv-ranking-list">
				<?php
				$kdv_rank = ( $kdv_paged - 1 ) * $kdv_per_page;
				while ( $kdv_ranking->have_posts() ) :
					$kdv_ranking->the_post();
					$kdv_rank++;
					?>
					<li class="kdv-ranking-item">
						<span class="kdv-ranking-item__rank">#<?php echo esc_html( $kdv_rank ); ?></span>
						<div class="kdv-ranking-item__card">
							<?php get_template_part( 'template-parts/content/review-card' ); ?>
						</div>
					</li>
				<?php endwhile; ?>
			</ol>
			<?php
			$kdv_pagination_links = paginate_links( [
				'total'   => $kdv_ranking->max_num_pages,
				'current' => $kdv_paged,
				'type'    => 'array',
			] );
			if ( $kdv_pagination_links ) :
				?>
				<nav class="kdv-pagination" aria-label="<?php esc_attr_e( 'Paginación del ranking', 'revista-koltor-dev' ); ?>">
					<?php echo implode( '', $kdv_pagination_links ); // phpcs:ignore WordPress.Security.EscapeOutput -- paginate_links() output is already escaped. ?>
				</nav>
				<?php
			endif;
			wp_reset_postdata();
			?>
		<?php else : ?>
			<div class="kdv-empty-state">
				<h1><?php esc_html_e( 'Todavía no hay reseñas con puntuación', 'revista-koltor-dev' ); ?></h1>
			</div>
		<?php endif; ?>
	</div>
</div>
<?php get_footer(); ?>
