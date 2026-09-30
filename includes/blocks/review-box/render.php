<?php
/**
 * Server-side render for revista-koltor-dev/review-box.
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function kdv_render_review_box_block( $attributes ) {
	$titulo     = $attributes['titulo'] ?? '';
	$puntuacion = isset( $attributes['puntuacion'] ) ? (float) $attributes['puntuacion'] : null;
	$imagen_url = $attributes['imagenUrl'] ?? '';
	$resumen    = $attributes['resumen'] ?? '';
	$pros       = kdv_lines_to_array( $attributes['pros'] ?? '' );
	$contras    = kdv_lines_to_array( $attributes['contras'] ?? '' );

	if ( empty( $titulo ) ) {
		return '';
	}

	ob_start();
	?>
	<div class="kdv-review-box">
		<?php if ( $imagen_url ) : ?>
			<div class="kdv-review-box__image">
				<img src="<?php echo esc_url( $imagen_url ); ?>" alt="<?php echo esc_attr( $titulo ); ?>" loading="lazy" />
			</div>
		<?php endif; ?>

		<div class="kdv-review-box__body">
			<div class="kdv-review-box__head">
				<h3 class="kdv-review-box__title"><?php echo esc_html( $titulo ); ?></h3>
				<?php if ( null !== $puntuacion ) : ?>
					<span class="kdv-score-badge kdv-score-badge--md <?php echo esc_attr( kdv_get_score_class( $puntuacion ) ); ?>">
						<span class="kdv-score-badge__value"><?php echo esc_html( number_format_i18n( $puntuacion, 1 ) ); ?></span>
					</span>
				<?php endif; ?>
			</div>

			<?php if ( $resumen ) : ?>
				<p class="kdv-review-box__summary"><?php echo esc_html( $resumen ); ?></p>
			<?php endif; ?>

			<?php if ( $pros || $contras ) : ?>
				<div class="kdv-review-box__proscons">
					<?php if ( $pros ) : ?>
						<div class="kdv-proscons__col kdv-proscons__col--pros">
							<h4><?php esc_html_e( 'Pros', 'revista-koltor-dev' ); ?></h4>
							<ul>
								<?php foreach ( $pros as $item ) : ?>
									<li><?php echo esc_html( $item ); ?></li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>
					<?php if ( $contras ) : ?>
						<div class="kdv-proscons__col kdv-proscons__col--contras">
							<h4><?php esc_html_e( 'Contras', 'revista-koltor-dev' ); ?></h4>
							<ul>
								<?php foreach ( $contras as $item ) : ?>
									<li><?php echo esc_html( $item ); ?></li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
