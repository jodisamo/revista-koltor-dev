<?php
/**
 * Card for a "Reseña" (kdv_resena) post — used in grids/archives and the review-grid block.
 *
 * @param array $args { show_score: bool }
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$show_score = ! isset( $args['show_score'] ) || $args['show_score'];
$tipo       = get_post_meta( get_the_ID(), '_kdv_tipo', true );
?>
<article <?php post_class( 'kdv-card' ); ?>>
	<a href="<?php the_permalink(); ?>" class="kdv-card__thumb">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'kdv-card' ); ?>
		<?php endif; ?>

		<?php if ( $tipo ) : ?>
			<span class="kdv-card__type"><?php echo esc_html( kdv_get_tipo_label( $tipo ) ); ?></span>
		<?php endif; ?>

		<?php if ( $show_score ) : ?>
			<span class="kdv-card__score">
				<?php kdv_score_badge( get_the_ID(), 'md' ); ?>
			</span>
		<?php endif; ?>
	</a>

	<div class="kdv-card__body">
		<div class="kdv-card__labels">
			<?php
			$terms = get_the_terms( get_the_ID(), 'kdv_genero' );
			if ( $terms && ! is_wp_error( $terms ) ) :
				?>
				<a href="<?php echo esc_url( get_term_link( $terms[0] ) ); ?>" class="kdv-card__category"><?php echo esc_html( $terms[0]->name ); ?></a>
			<?php endif; ?>
			<?php kdv_render_platform_badges(); ?>
		</div>

		<h3 class="kdv-card__title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h3>

		<p class="kdv-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), absint( get_theme_mod( 'kdv_card_excerpt_length', 16 ) ) ) ); ?></p>

		<div class="kdv-card__meta">
			<?php
			$estado = get_post_meta( get_the_ID(), '_kdv_estado', true );
			$anio   = get_post_meta( get_the_ID(), '_kdv_anio', true );
			echo esc_html( trim( implode( ' · ', array_filter( [ kdv_get_estado_label( $estado ), $anio ] ) ) ) );
			?>
		</div>
	</div>
</article>
