<?php
/**
 * Taxonomía "Plataforma" (kdv_plataforma) — PC, PlayStation, Xbox, Nintendo
 * Switch, Móvil... Plana y compartida entre Reseñas y Entradas, igual que
 * kdv_genero (ver includes/core/cpt-resena.php): un juego suele salir en
 * varias plataformas a la vez, así que no tiene sentido como categoría
 * única ni jerárquica.
 *
 * El icono de cada plataforma se sube como imagen normal de la biblioteca
 * de medios (term meta `kdv_platform_icon`, un ID de adjunto) -- a
 * propósito NO es un icono de la librería SVG del tema
 * (includes/core/icon-library.php). Esa librería es de trazos genéricos
 * bajo licencia MIT; los logos de PlayStation, Xbox o Nintendo son marcas
 * registradas de Sony/Microsoft/Nintendo, así que el tema no los incluye
 * ni los genera -- quien administra el sitio los sube él mismo (por
 * ejemplo, desde los kits de prensa oficiales de cada plataforma).
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function kdv_register_taxonomy_plataforma() {
	register_taxonomy( 'kdv_plataforma', [ 'kdv_resena', 'post' ], [
		'labels'       => [
			'name'          => __( 'Plataformas', 'revista-koltor-dev' ),
			'singular_name' => __( 'Plataforma', 'revista-koltor-dev' ),
			'search_items'  => __( 'Buscar plataformas', 'revista-koltor-dev' ),
			'all_items'     => __( 'Todas las plataformas', 'revista-koltor-dev' ),
			'edit_item'     => __( 'Editar plataforma', 'revista-koltor-dev' ),
			'add_new_item'  => __( 'Añadir nueva plataforma', 'revista-koltor-dev' ),
			'menu_name'     => __( 'Plataformas', 'revista-koltor-dev' ),
		],
		'hierarchical' => false,
		'public'       => true,
		'show_in_rest' => true,
		'rewrite'      => [ 'slug' => 'plataforma' ],
	] );
}
add_action( 'init', 'kdv_register_taxonomy_plataforma' );

/**
 * Cinco plataformas de partida, una sola vez -- mismo patrón exacto que
 * kdv_create_default_categories() en cpt-resena.php: si se borran o
 * renombran después, no se vuelven a crear solas.
 */
function kdv_create_default_platforms() {
	if ( get_option( 'kdv_default_platforms_created' ) ) {
		return;
	}

	$platforms = [ 'PC', 'PlayStation', 'Xbox', 'Nintendo Switch', 'Móvil' ];

	foreach ( $platforms as $name ) {
		if ( ! term_exists( $name, 'kdv_plataforma' ) ) {
			wp_insert_term( $name, 'kdv_plataforma' );
		}
	}

	update_option( 'kdv_default_platforms_created', 1 );
}
add_action( 'after_switch_theme', 'kdv_create_default_platforms' );

add_action( 'after_switch_theme', function() {
	kdv_register_taxonomy_plataforma();
	flush_rewrite_rules();
} );

/* ---------------------------------------------------------------------
 * Icono de plataforma: subida vía el selector de medios nativo de
 * WordPress (wp.media), no un plugin de terceros. Solo se carga el JS de
 * medios en las pantallas de esta taxonomía concreta, no en todo el admin.
 * ------------------------------------------------------------------- */

function kdv_platform_icon_enqueue_media() {
	$screen = get_current_screen();
	if ( $screen && 'kdv_plataforma' === $screen->taxonomy ) {
		wp_enqueue_media();
	}
}
add_action( 'admin_enqueue_scripts', 'kdv_platform_icon_enqueue_media' );

/**
 * El HTML del campo es igual en el formulario de "añadir" y en el de
 * "editar"; solo cambia si ya trae un valor guardado o no.
 *
 * @param int $current_id ID de adjunto ya guardado, o 0 si no hay ninguno.
 */
function kdv_render_platform_icon_field( $current_id ) {
	$current_id = absint( $current_id );
	$url        = $current_id ? wp_get_attachment_image_url( $current_id, 'thumbnail' ) : '';
	?>
	<div class="kdv-platform-icon-field">
		<input type="hidden" name="kdv_platform_icon" class="kdv-platform-icon-input" value="<?php echo esc_attr( $current_id ); ?>" />
		<div class="kdv-platform-icon-preview">
			<?php if ( $url ) : ?>
				<img src="<?php echo esc_url( $url ); ?>" style="max-width:80px;height:auto;display:block;margin-bottom:8px;" />
			<?php endif; ?>
		</div>
		<button type="button" class="button kdv-platform-icon-select"><?php esc_html_e( 'Seleccionar imagen', 'revista-koltor-dev' ); ?></button>
		<button type="button" class="button kdv-platform-icon-remove" <?php echo $current_id ? '' : 'style="display:none"'; ?>><?php esc_html_e( 'Quitar', 'revista-koltor-dev' ); ?></button>
	</div>
	<?php
	kdv_print_platform_icon_field_script();
}

function kdv_platform_icon_field_add() {
	?>
	<div class="form-field">
		<label><?php esc_html_e( 'Icono', 'revista-koltor-dev' ); ?></label>
		<?php kdv_render_platform_icon_field( 0 ); ?>
		<p><?php esc_html_e( 'Opcional: el logo de esta plataforma. Súbelo tú mismo (por ejemplo, desde el kit de prensa oficial correspondiente) -- el tema no incluye logos de marcas ajenas.', 'revista-koltor-dev' ); ?></p>
	</div>
	<?php
}
add_action( 'kdv_plataforma_add_form_fields', 'kdv_platform_icon_field_add' );

function kdv_platform_icon_field_edit( $term ) {
	$current_id = get_term_meta( $term->term_id, 'kdv_platform_icon', true );
	?>
	<tr class="form-field">
		<th scope="row"><label><?php esc_html_e( 'Icono', 'revista-koltor-dev' ); ?></label></th>
		<td>
			<?php kdv_render_platform_icon_field( $current_id ); ?>
			<p class="description"><?php esc_html_e( 'Opcional: el logo de esta plataforma. Súbelo tú mismo (por ejemplo, desde el kit de prensa oficial correspondiente) -- el tema no incluye logos de marcas ajenas.', 'revista-koltor-dev' ); ?></p>
		</td>
	</tr>
	<?php
}
add_action( 'kdv_plataforma_edit_form_fields', 'kdv_platform_icon_field_edit' );

/**
 * JS del botón "Seleccionar imagen" -- vanilla JS, sin jQuery (principio
 * de diseño no negociable del tema, ver §1 del manual técnico), aunque
 * wp.media() internamente dependa de Backbone/jQuery: eso ya lo carga
 * wp_enqueue_media() por su cuenta, no es una dependencia que añada este
 * archivo.
 */
function kdv_print_platform_icon_field_script() {
	static $printed = false;
	if ( $printed ) {
		return;
	}
	$printed = true;
	?>
	<script>
	( function() {
		function wireField( field ) {
			if ( field.dataset.kdvWired ) {
				return;
			}
			field.dataset.kdvWired = '1';

			var input     = field.querySelector( '.kdv-platform-icon-input' );
			var preview   = field.querySelector( '.kdv-platform-icon-preview' );
			var selectBtn = field.querySelector( '.kdv-platform-icon-select' );
			var removeBtn = field.querySelector( '.kdv-platform-icon-remove' );
			var frame;

			selectBtn.addEventListener( 'click', function( e ) {
				e.preventDefault();
				if ( ! frame ) {
					frame = wp.media( {
						title: <?php echo wp_json_encode( __( 'Seleccionar icono de plataforma', 'revista-koltor-dev' ) ); ?>,
						multiple: false,
						library: { type: 'image' }
					} );
					frame.on( 'select', function() {
						var attachment = frame.state().get( 'selection' ).first().toJSON();
						input.value = attachment.id;
						preview.innerHTML = '';
						var img = document.createElement( 'img' );
						img.src = attachment.url;
						img.style.maxWidth = '80px';
						img.style.height = 'auto';
						img.style.display = 'block';
						img.style.marginBottom = '8px';
						preview.appendChild( img );
						removeBtn.style.display = '';
					} );
				}
				frame.open();
			} );

			removeBtn.addEventListener( 'click', function( e ) {
				e.preventDefault();
				input.value = '';
				preview.innerHTML = '';
				removeBtn.style.display = 'none';
			} );
		}

		document.querySelectorAll( '.kdv-platform-icon-field' ).forEach( wireField );
	} )();
	</script>
	<?php
}

/**
 * Guarda el ID de adjunto al crear/editar el término. Comprueba que de
 * verdad es una imagen antes de confiar en él -- a diferencia del selector
 * de iconos de categoría (que solo acepta un slug de una lista fija), aquí
 * el valor lo escoge quien administra el sitio desde su propia biblioteca
 * de medios, así que conviene esta validación extra.
 */
function kdv_platform_icon_field_save( $term_id ) {
	if ( ! isset( $_POST['kdv_platform_icon'] ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_term', $term_id ) ) {
		return;
	}

	$attachment_id = absint( wp_unslash( $_POST['kdv_platform_icon'] ) );

	if ( $attachment_id && wp_attachment_is_image( $attachment_id ) ) {
		update_term_meta( $term_id, 'kdv_platform_icon', $attachment_id );
	} else {
		delete_term_meta( $term_id, 'kdv_platform_icon' );
	}
}
add_action( 'created_kdv_plataforma', 'kdv_platform_icon_field_save' );
add_action( 'edited_kdv_plataforma', 'kdv_platform_icon_field_save' );
