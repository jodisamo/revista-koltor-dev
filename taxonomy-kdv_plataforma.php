<?php
/**
 * Portada de una plataforma (/plataforma/playstation/, /plataforma/ps5/…).
 *
 * No es un listado plano: reúne lo último de esa plataforma por tipo de
 * contenido (Noticias, Reseñas, Avances, Reportajes, Eventos), cada bloque
 * con su "Ver todas →" hacia el archivo completo ya filtrado
 * (?plataforma=slug). Así la plataforma se siente como una sección propia
 * sin que exista una categoría por plataforma: cada artículo sigue llevando
 * una sola categoría (qué es) y sus plataformas (para qué es).
 *
 * Las consultas incluyen las subplataformas (include_children): la portada
 * de PlayStation muestra también lo marcado solo como PS5 o PS4.
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$kdv_platform = get_queried_object();
// Color de marca: el de la propia plataforma o, en una subplataforma sin
// color (PS5), el de su principal (PlayStation).
$kdv_color    = kdv_get_platform_color( $kdv_platform );
if ( ! $kdv_color && $kdv_platform->parent ) {
	$kdv_color_parent = get_term( $kdv_platform->parent, 'kdv_plataforma' );
	$kdv_color        = ( $kdv_color_parent && ! is_wp_error( $kdv_color_parent ) ) ? kdv_get_platform_color( $kdv_color_parent ) : '';
}
$kdv_icon_id  = absint( get_term_meta( $kdv_platform->term_id, 'kdv_platform_icon', true ) );
$kdv_parent   = $kdv_platform->parent ? get_term( $kdv_platform->parent, 'kdv_plataforma' ) : null;
$kdv_children = get_terms( [
	'taxonomy'   => 'kdv_plataforma',
	'parent'     => $kdv_platform->term_id,
	'hide_empty' => true,
] );

$kdv_platform_query = [
	[
		'taxonomy'         => 'kdv_plataforma',
		'field'            => 'term_id',
		'terms'            => $kdv_platform->term_id,
		'include_children' => true,
	],
];

/*
 * Bloques de la portada, en orden. Cada uno: título, argumentos extra de la
 * consulta, plantilla de tarjeta y URL de "Ver todas" (sin filtrar todavía).
 * Una categoría que no exista (borrada o renombrada de slug) se salta sola.
 */
$kdv_blocks = [];
foreach ( [ 'noticias', 'resenas', 'avances', 'reportajes', 'eventos' ] as $kdv_key ) {
	if ( 'resenas' === $kdv_key ) {
		$kdv_blocks[] = [
			'title' => __( 'Reseñas', 'revista-koltor-dev' ),
			'args'  => [ 'post_type' => 'kdv_resena' ],
			'card'  => 'template-parts/content/review-card',
			'url'   => get_post_type_archive_link( 'kdv_resena' ),
		];
		continue;
	}
	$kdv_cat_id = kdv_get_category_id_by_slug( $kdv_key );
	if ( ! $kdv_cat_id ) {
		continue;
	}
	$kdv_blocks[] = [
		'title' => get_cat_name( $kdv_cat_id ),
		'args'  => [ 'post_type' => 'post', 'cat' => $kdv_cat_id ],
		'card'  => 'template-parts/content/post-card',
		'url'   => get_category_link( $kdv_cat_id ),
	];
}

$kdv_ranking_url = kdv_get_ranking_page_url();
$kdv_any_content = false;
?>
<div class="kdv-container">
	<div class="kdv-content kdv-content__grid--full">

		<header class="kdv-platform-hub__head"<?php echo $kdv_color ? ' style="' . esc_attr( '--kdv-pc:' . $kdv_color ) . '"' : ''; ?>>
			<?php if ( $kdv_icon_id ) : ?>
				<?php echo wp_get_attachment_image( $kdv_icon_id, 'thumbnail', false, [ 'class' => 'kdv-platform-hub__icon', 'alt' => '' ] ); ?>
			<?php endif; ?>
			<div class="kdv-platform-hub__intro">
				<?php if ( $kdv_parent && ! is_wp_error( $kdv_parent ) ) : ?>
					<a class="kdv-platform-hub__up" href="<?php echo esc_url( get_term_link( $kdv_parent ) ); ?>">
						<?php
						/* translators: %s: plataforma principal (ej. PlayStation). */
						printf( esc_html__( '← Todo %s', 'revista-koltor-dev' ), esc_html( $kdv_parent->name ) );
						?>
					</a>
				<?php endif; ?>
				<h1 class="kdv-section__title kdv-platform-hub__title"><?php echo esc_html( $kdv_platform->name ); ?></h1>
				<?php the_archive_description( '<div class="kdv-platform-hub__desc">', '</div>' ); ?>

				<?php if ( ( $kdv_children && ! is_wp_error( $kdv_children ) ) || $kdv_ranking_url ) : ?>
					<nav class="kdv-platform-hub__chips" aria-label="<?php esc_attr_e( 'Accesos de la plataforma', 'revista-koltor-dev' ); ?>">
						<?php if ( $kdv_children && ! is_wp_error( $kdv_children ) ) : ?>
							<?php foreach ( $kdv_children as $kdv_child ) : ?>
								<a class="kdv-pill" href="<?php echo esc_url( get_term_link( $kdv_child ) ); ?>"><?php echo esc_html( $kdv_child->name ); ?></a>
							<?php endforeach; ?>
						<?php endif; ?>
						<?php if ( $kdv_ranking_url ) : ?>
							<a class="kdv-pill kdv-pill--accent" href="<?php echo esc_url( add_query_arg( 'plataforma', $kdv_platform->slug, $kdv_ranking_url ) ); ?>">
								<?php
								/* translators: %s: nombre de la plataforma. */
								printf( esc_html__( 'Tops de %s', 'revista-koltor-dev' ), esc_html( $kdv_platform->name ) );
								?>
							</a>
						<?php endif; ?>
					</nav>
				<?php endif; ?>
			</div>
		</header>

		<?php foreach ( $kdv_blocks as $kdv_block ) : ?>
			<?php
			$kdv_block_query = new WP_Query(
				array_merge(
					$kdv_block['args'],
					[
						'posts_per_page'      => 3,
						'post_status'         => 'publish',
						'no_found_rows'       => true,
						'ignore_sticky_posts' => true,
						'tax_query'           => $kdv_platform_query, // phpcs:ignore WordPress.DB.SlowDBQuery -- es el filtro que define la página.
					]
				)
			);
			if ( ! $kdv_block_query->have_posts() ) {
				continue;
			}
			$kdv_any_content = true;
			?>
			<section class="kdv-section kdv-platform-hub__block">
				<div class="kdv-section__head">
					<h2 class="kdv-section__title"><?php echo esc_html( $kdv_block['title'] ); ?></h2>
					<a class="kdv-section__link" href="<?php echo esc_url( add_query_arg( 'plataforma', $kdv_platform->slug, $kdv_block['url'] ) ); ?>"><?php esc_html_e( 'Ver todas →', 'revista-koltor-dev' ); ?></a>
				</div>
				<div class="kdv-cards-grid kdv-cards-grid--cols-3">
					<?php
					while ( $kdv_block_query->have_posts() ) :
						$kdv_block_query->the_post();
						get_template_part( $kdv_block['card'] );
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</section>
		<?php endforeach; ?>

		<?php if ( ! $kdv_any_content ) : ?>
			<div class="kdv-empty-state">
				<h2>
					<?php
					/* translators: %s: nombre de la plataforma. */
					printf( esc_html__( 'Todavía no hay contenido de %s', 'revista-koltor-dev' ), esc_html( $kdv_platform->name ) );
					?>
				</h2>
				<p><?php esc_html_e( 'Vuelve pronto: estamos preparando noticias, reseñas y más.', 'revista-koltor-dev' ); ?></p>
			</div>
		<?php endif; ?>

	</div>
</div>
<?php get_footer(); ?>
