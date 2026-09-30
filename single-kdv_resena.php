<?php
/**
 * Single template for the "Reseña" custom post type.
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$post_id    = get_the_ID();
	$tipo       = get_post_meta( $post_id, '_kdv_tipo', true );
	$estado     = get_post_meta( $post_id, '_kdv_estado', true );
	$anio       = get_post_meta( $post_id, '_kdv_anio', true );
	$periodo  = get_post_meta( $post_id, '_kdv_periodo', true );
	$entregas  = get_post_meta( $post_id, '_kdv_entregas', true );
	$score      = kdv_get_score( $post_id );

	$score_labels = kdv_get_score_labels();
	$breakdown    = [
		$score_labels[0] => get_post_meta( $post_id, '_kdv_score_1', true ),
		$score_labels[1] => get_post_meta( $post_id, '_kdv_score_2', true ),
		$score_labels[2] => get_post_meta( $post_id, '_kdv_score_3', true ),
		$score_labels[3] => get_post_meta( $post_id, '_kdv_score_4', true ),
	];

	$pros    = kdv_lines_to_array( get_post_meta( $post_id, '_kdv_pros', true ) );
	$contras = kdv_lines_to_array( get_post_meta( $post_id, '_kdv_contras', true ) );

	$generos  = get_the_terms( $post_id, 'kdv_genero' );
	$estudios = get_the_terms( $post_id, 'kdv_estudio' );
	?>

	<div class="kdv-container">

		<section class="kdv-review-header">
			<div class="kdv-review-header__grid">

				<?php if ( has_post_thumbnail() ) : ?>
					<div class="kdv-review-header__poster">
						<?php the_post_thumbnail( 'kdv-square' ); ?>
					</div>
				<?php endif; ?>

				<div>
					<div class="kdv-review-header__meta">
						<?php if ( $tipo ) : ?><span class="kdv-pill"><?php echo esc_html( kdv_get_tipo_label( $tipo ) ); ?></span><?php endif; ?>
						<?php if ( $estado ) : ?><span class="kdv-pill"><?php echo esc_html( kdv_get_estado_label( $estado ) ); ?></span><?php endif; ?>
						<?php if ( $anio ) : ?><span class="kdv-pill"><?php echo esc_html( $anio ); ?></span><?php endif; ?>
						<?php if ( $periodo ) : ?><span class="kdv-pill"><?php echo esc_html( $periodo ); ?></span><?php endif; ?>
						<?php if ( $entregas ) : ?><span class="kdv-pill"><?php echo esc_html( $entregas ); ?> <?php esc_html_e( 'entregas', 'revista-koltor-dev' ); ?></span><?php endif; ?>
						<?php if ( $estudios && ! is_wp_error( $estudios ) ) : ?>
							<span class="kdv-pill"><?php echo esc_html( implode( ', ', wp_list_pluck( $estudios, 'name' ) ) ); ?></span>
						<?php endif; ?>
					</div>

					<h1 class="kdv-review-header__title"><?php the_title(); ?></h1>

					<?php if ( $generos && ! is_wp_error( $generos ) ) : ?>
						<div class="kdv-category-list">
							<?php foreach ( $generos as $genero ) : ?>
								<a href="<?php echo esc_url( get_term_link( $genero ) ); ?>" class="kdv-card__category"><?php echo esc_html( $genero->name ); ?></a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<?php if ( null !== $score ) : ?>
						<div class="kdv-review-header__score">
							<?php kdv_score_badge( $post_id, 'lg' ); ?>
							<span class="kdv-review-header__score-label"><?php esc_html_e( 'Puntuación de la redacción', 'revista-koltor-dev' ); ?></span>
						</div>
					<?php endif; ?>

					<?php
					$has_breakdown = array_filter( $breakdown, function( $v ) { return '' !== $v; } );
					if ( $has_breakdown ) :
						?>
						<div class="kdv-score-breakdown">
							<?php foreach ( $breakdown as $label => $value ) : ?>
								<?php if ( '' === $value ) { continue; } ?>
								<div class="kdv-score-breakdown__row">
									<span><?php echo esc_html( $label ); ?></span>
									<span class="kdv-score-breakdown__bar"><span class="kdv-score-breakdown__fill" style="width:<?php echo esc_attr( $value * 10 ); ?>%"></span></span>
									<span><?php echo esc_html( number_format_i18n( $value, 1 ) ); ?></span>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</section>

		<div class="kdv-content kdv-content__grid">
			<main>
				<div class="kdv-post__body">
					<?php the_content(); ?>
				</div>

				<?php if ( $pros || $contras ) : ?>
					<div class="kdv-proscons">
						<?php if ( $pros ) : ?>
							<div class="kdv-proscons__col kdv-proscons__col--pros">
								<h4><?php esc_html_e( 'Pros', 'revista-koltor-dev' ); ?></h4>
								<ul><?php foreach ( $pros as $item ) : ?><li><?php echo esc_html( $item ); ?></li><?php endforeach; ?></ul>
							</div>
						<?php endif; ?>
						<?php if ( $contras ) : ?>
							<div class="kdv-proscons__col kdv-proscons__col--contras">
								<h4><?php esc_html_e( 'Contras', 'revista-koltor-dev' ); ?></h4>
								<ul><?php foreach ( $contras as $item ) : ?><li><?php echo esc_html( $item ); ?></li><?php endforeach; ?></ul>
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php kdv_render_post_tags( $post_id ); ?>

				<?php kdv_render_share_buttons( $post_id ); ?>

				<?php kdv_render_author_box( $post_id ); ?>

				<?php if ( comments_open() || get_comments_number() ) : ?>
					<?php comments_template(); ?>
				<?php endif; ?>
			</main>

			<?php get_sidebar(); ?>
		</div>

		<?php
		// Related reviews: same primary genre, excluding this one.
		if ( $generos && ! is_wp_error( $generos ) ) :
			$related = new WP_Query( [
				'post_type'      => 'kdv_resena',
				'posts_per_page' => 3,
				'post__not_in'   => [ $post_id ],
				'no_found_rows'  => true,
				'tax_query'      => [
					[
						'taxonomy' => 'kdv_genero',
						'field'    => 'term_id',
						'terms'    => $generos[0]->term_id,
					],
				],
			] );
			if ( $related->have_posts() ) :
				?>
				<section class="kdv-section">
					<div class="kdv-section__head">
						<h2 class="kdv-section__title"><?php esc_html_e( 'Reseñas relacionadas', 'revista-koltor-dev' ); ?></h2>
					</div>
					<div class="kdv-cards-grid">
						<?php
						while ( $related->have_posts() ) :
							$related->the_post();
							get_template_part( 'template-parts/content/review-card' );
						endwhile;
						wp_reset_postdata();
						?>
					</div>
				</section>
				<?php
			endif;
		endif;
		?>
	</div>

<?php endwhile; ?>

<?php get_footer(); ?>
