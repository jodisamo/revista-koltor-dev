<?php
/**
 * Native meta box for the "Reseña" (Review) post type.
 *
 * No ACF, no third-party field builders — plain WordPress meta box API,
 * nonces and sanitisation, same approach the theme's security review
 * recommended for the reference theme's AJAX endpoints.
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function kdv_add_resena_meta_box() {
	add_meta_box(
		'kdv_resena_ficha',
		__( 'Ficha técnica y puntuación', 'revista-koltor-dev' ),
		'kdv_render_resena_meta_box',
		'kdv_resena',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'kdv_add_resena_meta_box' );

function kdv_render_resena_meta_box( $post ) {
	wp_nonce_field( 'kdv_save_resena_meta', 'kdv_resena_nonce' );

	$item_name  = get_post_meta( $post->ID, '_kdv_item_name', true );
	$tipo       = get_post_meta( $post->ID, '_kdv_tipo', true ) ?: 'videojuego';
	$estado     = get_post_meta( $post->ID, '_kdv_estado', true ) ?: 'finalizado';
	$anio       = get_post_meta( $post->ID, '_kdv_anio', true );
	$entregas  = get_post_meta( $post->ID, '_kdv_entregas', true );
	$periodo  = get_post_meta( $post->ID, '_kdv_periodo', true );

	$score_1  = get_post_meta( $post->ID, '_kdv_score_1', true );
	$score_2 = get_post_meta( $post->ID, '_kdv_score_2', true );
	$score_3    = get_post_meta( $post->ID, '_kdv_score_3', true );
	$score_4 = get_post_meta( $post->ID, '_kdv_score_4', true );

	$score_labels = kdv_get_score_labels();

	$pros = get_post_meta( $post->ID, '_kdv_pros', true );
	$cons = get_post_meta( $post->ID, '_kdv_contras', true );
	?>
	<style>
		.kdv-mb-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 20px; }
		.kdv-mb-grid label { display:block; font-weight:600; margin-bottom:4px; }
		.kdv-mb-grid input, .kdv-mb-grid select { width:100%; }
		.kdv-mb-scores { display:grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom:20px; padding:16px; background:#f6f7f7; border-radius:6px; }
		.kdv-mb-scores label { display:block; font-weight:600; margin-bottom:4px; }
		.kdv-mb-prosconstext { display:grid; grid-template-columns: 1fr 1fr; gap:16px; }
		.kdv-mb-prosconstext textarea { width:100%; height:110px; }
		.kdv-mb-hint { color:#666; font-size:12px; margin-top:2px; }
		.kdv-mb-final-score { font-size:13px; color:#2271b1; margin-top:6px; }
	</style>

	<p style="margin:0 0 18px;">
		<label for="kdv_item_name" style="display:block;font-weight:600;margin-bottom:4px;"><?php esc_html_e( 'Juego reseñado', 'revista-koltor-dev' ); ?></label>
		<input type="text" name="kdv_item_name" id="kdv_item_name" value="<?php echo esc_attr( $item_name ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'Ej: The Legend of Zelda: Ocarina of Time', 'revista-koltor-dev' ); ?>" />
		<span class="kdv-mb-hint"><?php esc_html_e( 'Solo el nombre del juego (o de la película, el libro…), sin "Análisis de…". Es lo que Google asocia a la nota en sus resultados; si lo dejas vacío se usa el título de la reseña.', 'revista-koltor-dev' ); ?></span>
	</p>

	<div class="kdv-mb-grid">
		<div>
			<label for="kdv_tipo"><?php esc_html_e( 'Formato', 'revista-koltor-dev' ); ?></label>
			<select name="kdv_tipo" id="kdv_tipo">
				<option value="serie" <?php selected( $tipo, 'serie' ); ?>><?php esc_html_e( 'Serie', 'revista-koltor-dev' ); ?></option>
				<option value="pelicula" <?php selected( $tipo, 'pelicula' ); ?>><?php esc_html_e( 'Película', 'revista-koltor-dev' ); ?></option>
				<option value="videojuego" <?php selected( $tipo, 'videojuego' ); ?>><?php esc_html_e( 'Videojuego', 'revista-koltor-dev' ); ?></option>
				<option value="libro" <?php selected( $tipo, 'libro' ); ?>><?php esc_html_e( 'Libro', 'revista-koltor-dev' ); ?></option>
				<option value="otro" <?php selected( $tipo, 'otro' ); ?>><?php esc_html_e( 'Otro', 'revista-koltor-dev' ); ?></option>
			</select>
		</div>
		<div>
			<label for="kdv_estado"><?php esc_html_e( 'Estado', 'revista-koltor-dev' ); ?></label>
			<select name="kdv_estado" id="kdv_estado">
				<option value="en_curso" <?php selected( $estado, 'en_curso' ); ?>><?php esc_html_e( 'En curso', 'revista-koltor-dev' ); ?></option>
				<option value="finalizado" <?php selected( $estado, 'finalizado' ); ?>><?php esc_html_e( 'Finalizado', 'revista-koltor-dev' ); ?></option>
				<option value="anunciado" <?php selected( $estado, 'anunciado' ); ?>><?php esc_html_e( 'Anunciado', 'revista-koltor-dev' ); ?></option>
			</select>
		</div>
		<div>
			<label for="kdv_anio"><?php esc_html_e( 'Año', 'revista-koltor-dev' ); ?></label>
			<input type="number" min="1950" max="2100" name="kdv_anio" id="kdv_anio" value="<?php echo esc_attr( $anio ); ?>" />
		</div>
		<div>
			<label for="kdv_periodo"><?php esc_html_e( 'Periodo (ej. Q1 2027)', 'revista-koltor-dev' ); ?></label>
			<input type="text" name="kdv_periodo" id="kdv_periodo" value="<?php echo esc_attr( $periodo ); ?>" />
		</div>
		<div>
			<label for="kdv_entregas"><?php esc_html_e( 'Entregas / Episodios', 'revista-koltor-dev' ); ?></label>
			<input type="number" min="0" name="kdv_entregas" id="kdv_entregas" value="<?php echo esc_attr( $entregas ); ?>" />
		</div>
		<div>
			<span class="kdv-mb-hint"><?php esc_html_e( 'Usa las taxonomías "Género" y "Estudio" de la barra lateral para clasificar esta reseña.', 'revista-koltor-dev' ); ?></span>
		</div>
	</div>

	<h4><?php esc_html_e( 'Puntuación (0.0 – 10.0)', 'revista-koltor-dev' ); ?></h4>
	<div class="kdv-mb-scores">
		<div>
			<label for="kdv_score_1"><?php echo esc_html( $score_labels[0] ); ?></label>
			<input type="number" step="0.1" min="0" max="10" name="kdv_score_1" id="kdv_score_1" value="<?php echo esc_attr( $score_1 ); ?>" />
		</div>
		<div>
			<label for="kdv_score_2"><?php echo esc_html( $score_labels[1] ); ?></label>
			<input type="number" step="0.1" min="0" max="10" name="kdv_score_2" id="kdv_score_2" value="<?php echo esc_attr( $score_2 ); ?>" />
		</div>
		<div>
			<label for="kdv_score_3"><?php echo esc_html( $score_labels[2] ); ?></label>
			<input type="number" step="0.1" min="0" max="10" name="kdv_score_3" id="kdv_score_3" value="<?php echo esc_attr( $score_3 ); ?>" />
		</div>
		<div>
			<label for="kdv_score_4"><?php echo esc_html( $score_labels[3] ); ?></label>
			<input type="number" step="0.1" min="0" max="10" name="kdv_score_4" id="kdv_score_4" value="<?php echo esc_attr( $score_4 ); ?>" />
		</div>
	</div>
	<p class="kdv-mb-final-score">
		<?php esc_html_e( 'La puntuación final que se muestra en la tarjeta y el badge es el promedio de estos 4 valores.', 'revista-koltor-dev' ); ?>
	</p>

	<h4><?php esc_html_e( 'Pros y contras', 'revista-koltor-dev' ); ?></h4>
	<div class="kdv-mb-prosconstext">
		<div>
			<label for="kdv_pros"><?php esc_html_e( 'Pros (una línea por punto)', 'revista-koltor-dev' ); ?></label>
			<textarea name="kdv_pros" id="kdv_pros"><?php echo esc_textarea( $pros ); ?></textarea>
		</div>
		<div>
			<label for="kdv_contras"><?php esc_html_e( 'Contras (una línea por punto)', 'revista-koltor-dev' ); ?></label>
			<textarea name="kdv_contras" id="kdv_contras"><?php echo esc_textarea( $cons ); ?></textarea>
		</div>
	</div>
	<?php
}

function kdv_save_resena_meta( $post_id ) {

	if ( ! isset( $_POST['kdv_resena_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['kdv_resena_nonce'] ) ), 'kdv_save_resena_meta' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Whitelisted text/select fields.
	$text_fields = [
		'kdv_tipo'       => [ 'serie', 'pelicula', 'videojuego', 'libro', 'otro' ],
		'kdv_estado'     => [ 'en_curso', 'finalizado', 'anunciado' ],
	];

	foreach ( $text_fields as $field => $allowed ) {
		if ( isset( $_POST[ $field ] ) ) {
			$value = sanitize_text_field( wp_unslash( $_POST[ $field ] ) );
			if ( in_array( $value, $allowed, true ) ) {
				update_post_meta( $post_id, '_' . $field, $value );
			}
		}
	}

	if ( isset( $_POST['kdv_item_name'] ) ) {
		update_post_meta( $post_id, '_kdv_item_name', sanitize_text_field( wp_unslash( $_POST['kdv_item_name'] ) ) );
	}

	if ( isset( $_POST['kdv_periodo'] ) ) {
		update_post_meta( $post_id, '_kdv_periodo', sanitize_text_field( wp_unslash( $_POST['kdv_periodo'] ) ) );
	}

	// Numeric fields.
	$numeric_fields = [ 'kdv_anio', 'kdv_entregas' ];
	foreach ( $numeric_fields as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, '_' . $field, absint( $_POST[ $field ] ) );
		}
	}

	// Score fields (0.0 - 10.0).
	$score_fields = [ 'kdv_score_1', 'kdv_score_2', 'kdv_score_3', 'kdv_score_4' ];
	$scores       = [];
	foreach ( $score_fields as $field ) {
		if ( isset( $_POST[ $field ] ) && '' !== $_POST[ $field ] ) {
			$value = min( 10, max( 0, (float) $_POST[ $field ] ) );
			update_post_meta( $post_id, '_' . $field, $value );
			$scores[] = $value;
		} else {
			delete_post_meta( $post_id, '_' . $field );
		}
	}

	// Pre-calculate and store the average so templates don't need to do it on every load.
	if ( ! empty( $scores ) ) {
		update_post_meta( $post_id, '_kdv_score_final', round( array_sum( $scores ) / count( $scores ), 1 ) );
	} else {
		delete_post_meta( $post_id, '_kdv_score_final' );
	}

	// Pros / cons (plain textareas, one item per line).
	if ( isset( $_POST['kdv_pros'] ) ) {
		update_post_meta( $post_id, '_kdv_pros', sanitize_textarea_field( wp_unslash( $_POST['kdv_pros'] ) ) );
	}
	if ( isset( $_POST['kdv_contras'] ) ) {
		update_post_meta( $post_id, '_kdv_contras', sanitize_textarea_field( wp_unslash( $_POST['kdv_contras'] ) ) );
	}
}
add_action( 'save_post_kdv_resena', 'kdv_save_resena_meta' );
