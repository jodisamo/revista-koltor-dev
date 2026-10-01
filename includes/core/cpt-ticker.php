<?php
/**
 * Custom Post Type "Anuncio de cinta" (kdv_ticker_item) — los elementos de
 * la cinta de anuncios horizontal que se muestra encima de la cabecera
 * (eventos próximos: Nintendo Direct, State of Play, etc.). Mismo patrón
 * que "Diapositivas" (kdv_slide, ver cpt-slide.php): cada anuncio es un
 * post normal, ordenado con "Atributos de página" (0, 1, 2…), sin plugin
 * de terceros ni framework de campos.
 *
 * El interruptor de encendido/apagado y la velocidad de la cinta viven en
 * el Personalizador (sección "Cinta de anuncios"), no aquí — este archivo
 * solo gestiona la lista de anuncios en sí.
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function kdv_register_cpt_ticker_item() {

	$labels = [
		'name'               => _x( 'Anuncios de la cinta', 'Post type general name', 'revista-koltor-dev' ),
		'singular_name'      => _x( 'Anuncio', 'Post type singular name', 'revista-koltor-dev' ),
		'menu_name'          => _x( 'Cinta de anuncios', 'Admin Menu text', 'revista-koltor-dev' ),
		'add_new_item'       => __( 'Añadir nuevo anuncio', 'revista-koltor-dev' ),
		'edit_item'          => __( 'Editar anuncio', 'revista-koltor-dev' ),
		'new_item'           => __( 'Nuevo anuncio', 'revista-koltor-dev' ),
		'view_item'          => __( 'Ver anuncio', 'revista-koltor-dev' ),
		'view_items'         => __( 'Ver anuncios', 'revista-koltor-dev' ),
		'search_items'       => __( 'Buscar anuncios', 'revista-koltor-dev' ),
		'not_found'          => __( 'No se encontraron anuncios.', 'revista-koltor-dev' ),
		'not_found_in_trash' => __( 'No hay anuncios en la papelera.', 'revista-koltor-dev' ),
		'all_items'          => __( 'Todos los anuncios', 'revista-koltor-dev' ),
	];

	register_post_type( 'kdv_ticker_item', [
		'labels'        => $labels,
		'public'        => false,
		'show_ui'       => true,
		'show_in_menu'  => true,
		'menu_icon'     => 'dashicons-megaphone',
		'menu_position' => 7,
		// Permisos de "página" (editor o administrador), no de "entrada": con
		// los de entrada, cualquier autor podía publicar en la portada o en la
		// cinta que sale en todo el sitio.
		'capability_type' => 'page',
		'map_meta_cap'    => true,
		'supports'      => [ 'title', 'page-attributes' ],
		'show_in_rest'  => true,
	] );
}
add_action( 'init', 'kdv_register_cpt_ticker_item' );

add_action( 'after_switch_theme', function() {
	kdv_register_cpt_ticker_item();
	flush_rewrite_rules();
} );

/* ---------------------------------------------------------------------
 * Meta box: fecha corta (opcional) + enlace (opcional)
 * ------------------------------------------------------------------- */

function kdv_add_ticker_item_meta_box() {
	add_meta_box(
		'kdv_ticker_item_details',
		__( 'Fecha y enlace del anuncio', 'revista-koltor-dev' ),
		'kdv_render_ticker_item_meta_box',
		'kdv_ticker_item',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'kdv_add_ticker_item_meta_box' );

function kdv_render_ticker_item_meta_box( $post ) {
	wp_nonce_field( 'kdv_save_ticker_item_meta', 'kdv_ticker_item_nonce' );

	$date = get_post_meta( $post->ID, '_kdv_ticker_date', true );
	$url  = get_post_meta( $post->ID, '_kdv_ticker_url', true );
	?>
	<style>
		.kdv-ticker-mb p { margin: 0 0 14px; }
		.kdv-ticker-mb label { display: block; font-weight: 600; margin-bottom: 4px; }
		.kdv-ticker-mb input { width: 100%; }
		.kdv-ticker-mb .kdv-ticker-hint { color: #666; font-size: 12px; margin-top: 4px; }
	</style>
	<div class="kdv-ticker-mb">
		<p>
			<label for="kdv_ticker_date"><?php esc_html_e( 'Fecha corta (opcional)', 'revista-koltor-dev' ); ?></label>
			<input type="text" name="kdv_ticker_date" id="kdv_ticker_date" value="<?php echo esc_attr( $date ); ?>" placeholder="<?php esc_attr_e( 'Ej: 12 sept · 23:00', 'revista-koltor-dev' ); ?>" />
			<span class="kdv-ticker-hint"><?php esc_html_e( 'Texto libre, se muestra antes del título del anuncio. Déjalo vacío si no aplica.', 'revista-koltor-dev' ); ?></span>
		</p>
		<p>
			<label for="kdv_ticker_url"><?php esc_html_e( 'Enlace (opcional)', 'revista-koltor-dev' ); ?></label>
			<input type="url" name="kdv_ticker_url" id="kdv_ticker_url" value="<?php echo esc_attr( $url ); ?>" placeholder="https://" />
			<span class="kdv-ticker-hint"><?php esc_html_e( 'Si lo rellenas, todo el anuncio será un enlace (por ejemplo, a la noticia del evento). Si lo dejas vacío, el anuncio se muestra como texto simple.', 'revista-koltor-dev' ); ?></span>
		</p>
		<p class="kdv-ticker-hint">
			<?php esc_html_e( 'El título de arriba es el texto principal del anuncio (ej: "Nintendo Direct"). El orden entre anuncios se controla en "Atributos de página" (barra lateral), con números: 0, 1, 2…', 'revista-koltor-dev' ); ?>
		</p>
	</div>
	<?php
}

function kdv_save_ticker_item_meta( $post_id ) {

	if ( ! isset( $_POST['kdv_ticker_item_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['kdv_ticker_item_nonce'] ) ), 'kdv_save_ticker_item_meta' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['kdv_ticker_date'] ) ) {
		update_post_meta( $post_id, '_kdv_ticker_date', sanitize_text_field( wp_unslash( $_POST['kdv_ticker_date'] ) ) );
	}
	if ( isset( $_POST['kdv_ticker_url'] ) ) {
		update_post_meta( $post_id, '_kdv_ticker_url', esc_url_raw( wp_unslash( $_POST['kdv_ticker_url'] ) ) );
	}
}
add_action( 'save_post_kdv_ticker_item', 'kdv_save_ticker_item_meta' );
