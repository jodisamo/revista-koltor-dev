<?php
/**
 * Server-side render for revista-koltor-dev/review-grid.
 * Queries the "Reseña" CPT directly — same data the archive template uses.
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function kdv_render_review_grid_block( $attributes ) {
	$cantidad    = ! empty( $attributes['cantidad'] ) ? absint( $attributes['cantidad'] ) : 6;
	$columnas    = ! empty( $attributes['columnas'] ) ? absint( $attributes['columnas'] ) : 3;
	$genero_slug = ! empty( $attributes['generoSlug'] ) ? sanitize_title( $attributes['generoSlug'] ) : '';
	$mostrar_pts = ! isset( $attributes['mostrarPuntuacion'] ) || $attributes['mostrarPuntuacion'];
	$titulo      = $attributes['titulo'] ?? '';

	$columnas = min( 4, max( 2, $columnas ) );

	$args = [
		'post_type'      => 'kdv_resena',
		'posts_per_page' => min( 12, max( 1, $cantidad ) ),
		'post_status'    => 'publish',
		'no_found_rows'  => true,
	];

	if ( $genero_slug ) {
		$args['tax_query'] = [
			[
				'taxonomy' => 'kdv_genero',
				'field'    => 'slug',
				'terms'    => $genero_slug,
			],
		];
	}

	$query = new WP_Query( $args );

	if ( ! $query->have_posts() ) {
		return '';
	}

	ob_start();
	?>
	<div class="kdv-review-grid">
		<?php if ( $titulo ) : ?>
			<h2 class="kdv-review-grid__title"><?php echo esc_html( $titulo ); ?></h2>
		<?php endif; ?>
		<div class="kdv-cards-grid kdv-cards-grid--cols-<?php echo esc_attr( $columnas ); ?>">
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();
				get_template_part( 'template-parts/content/review-card', null, [ 'show_score' => $mostrar_pts ] );
			endwhile;
			?>
		</div>
	</div>
	<?php
	wp_reset_postdata();

	return ob_get_clean();
}
