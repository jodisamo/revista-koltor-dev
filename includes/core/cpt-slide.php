<?php
/**
 * Custom Post Type "Diapositiva" (kdv_slide) — the images for the native
 * hero slider on the homepage. Not a page builder: just a normal WordPress
 * list where each slide is a post with a featured image, an optional
 * subtitle and an optional button, ordered with the native "Atributos de
 * página" order field (drag-and-drop is not built into core, but typing
 * 0, 1, 2... in that box works the same way).
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function kdv_register_cpt_slide() {

	$labels = [
		'name'                  => _x( 'Diapositivas', 'Post type general name', 'revista-koltor-dev' ),
		'singular_name'         => _x( 'Diapositiva', 'Post type singular name', 'revista-koltor-dev' ),
		'menu_name'             => _x( 'Diapositivas (Hero)', 'Admin Menu text', 'revista-koltor-dev' ),
		'add_new_item'          => __( 'Añadir nueva diapositiva', 'revista-koltor-dev' ),
		'edit_item'             => __( 'Editar diapositiva', 'revista-koltor-dev' ),
		'new_item'              => __( 'Nueva diapositiva', 'revista-koltor-dev' ),
		'view_item'             => __( 'Ver diapositiva', 'revista-koltor-dev' ),
		'view_items'            => __( 'Ver diapositivas', 'revista-koltor-dev' ),
		'search_items'          => __( 'Buscar diapositivas', 'revista-koltor-dev' ),
		'not_found'             => __( 'No se encontraron diapositivas.', 'revista-koltor-dev' ),
		'not_found_in_trash'    => __( 'No hay diapositivas en la papelera.', 'revista-koltor-dev' ),
		'all_items'             => __( 'Todas las diapositivas', 'revista-koltor-dev' ),
		'featured_image'        => __( 'Imagen de la diapositiva', 'revista-koltor-dev' ),
		'set_featured_image'    => __( 'Establecer imagen', 'revista-koltor-dev' ),
		'remove_featured_image' => __( 'Quitar imagen', 'revista-koltor-dev' ),
	];

	register_post_type( 'kdv_slide', [
		'labels'        => $labels,
		'public'        => false,
		'show_ui'       => true,
		'show_in_menu'  => true,
		'menu_icon'     => 'dashicons-images-alt2',
		'menu_position' => 6,
		// Permisos de "página" (editor o administrador), no de "entrada": con
		// los de entrada, cualquier autor podía publicar en la portada o en la
		// cinta que sale en todo el sitio.
		'capability_type' => 'page',
		'map_meta_cap'    => true,
		'supports'      => [ 'title', 'thumbnail', 'page-attributes' ],
		'show_in_rest'  => true,
	] );
}
add_action( 'init', 'kdv_register_cpt_slide' );

add_action( 'after_switch_theme', function() {
	kdv_register_cpt_slide();
	flush_rewrite_rules();
} );

/* ---------------------------------------------------------------------
 * Meta box: subtítulo + botón opcional
 * ------------------------------------------------------------------- */

function kdv_add_slide_meta_box() {
	add_meta_box(
		'kdv_slide_details',
		__( 'Texto y botón de la diapositiva', 'revista-koltor-dev' ),
		'kdv_render_slide_meta_box',
		'kdv_slide',
		'normal',
		'high'
	);
	add_meta_box(
		'kdv_slide_mobile_image',
		__( 'Imagen para móvil (opcional)', 'revista-koltor-dev' ),
		'kdv_render_slide_mobile_image_meta_box',
		'kdv_slide',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'kdv_add_slide_meta_box' );

/**
 * Loads the WP media uploader only on the "Diapositiva" edit screen — no
 * extra plugin, this is the same picker core uses for the Featured Image.
 */
function kdv_enqueue_slide_media_uploader( $hook ) {
	if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
		return;
	}
	if ( 'kdv_slide' !== get_current_screen()->post_type ) {
		return;
	}
	wp_enqueue_media();
}
add_action( 'admin_enqueue_scripts', 'kdv_enqueue_slide_media_uploader' );

function kdv_render_slide_meta_box( $post ) {
	wp_nonce_field( 'kdv_save_slide_meta', 'kdv_slide_nonce' );

	$subtitle     = get_post_meta( $post->ID, '_kdv_slide_subtitle', true );
	$button_text  = get_post_meta( $post->ID, '_kdv_slide_button_text', true );
	$button_url   = get_post_meta( $post->ID, '_kdv_slide_button_url', true );
	?>
	<style>
		.kdv-slide-mb p { margin: 0 0 14px; }
		.kdv-slide-mb label { display: block; font-weight: 600; margin-bottom: 4px; }
		.kdv-slide-mb input, .kdv-slide-mb textarea { width: 100%; }
		.kdv-slide-mb .kdv-slide-hint { color: #666; font-size: 12px; margin-top: 4px; }
	</style>
	<div class="kdv-slide-mb">
		<p>
			<label for="kdv_slide_subtitle"><?php esc_html_e( 'Subtítulo', 'revista-koltor-dev' ); ?></label>
			<textarea name="kdv_slide_subtitle" id="kdv_slide_subtitle" rows="2"><?php echo esc_textarea( $subtitle ); ?></textarea>
			<span class="kdv-slide-hint"><?php esc_html_e( 'El título de la diapositiva es el título de esta entrada, arriba.', 'revista-koltor-dev' ); ?></span>
		</p>
		<p>
			<label for="kdv_slide_button_text"><?php esc_html_e( 'Texto del botón (opcional)', 'revista-koltor-dev' ); ?></label>
			<input type="text" name="kdv_slide_button_text" id="kdv_slide_button_text" value="<?php echo esc_attr( $button_text ); ?>" placeholder="<?php esc_attr_e( 'Ej: Ver reseñas', 'revista-koltor-dev' ); ?>" />
		</p>
		<p>
			<label for="kdv_slide_button_url"><?php esc_html_e( 'Enlace del botón', 'revista-koltor-dev' ); ?></label>
			<input type="url" name="kdv_slide_button_url" id="kdv_slide_button_url" value="<?php echo esc_attr( $button_url ); ?>" placeholder="https://" />
			<span class="kdv-slide-hint"><?php esc_html_e( 'No pongas nada de sub-botón si no quieres que la diapositiva tenga botón.', 'revista-koltor-dev' ); ?></span>
		</p>
		<p class="kdv-slide-hint">
			<?php esc_html_e( 'No olvides asignar una Imagen destacada a esta diapositiva (barra lateral derecha) — sin ella no se mostrará. El orden entre diapositivas se controla en "Atributos de página" (barra lateral), con números: 0, 1, 2…', 'revista-koltor-dev' ); ?>
			<br><?php esc_html_e( 'Plataformas (barra lateral): las que marques se muestran como etiqueta con icono encima de la imagen. Si no marcas ninguna y el botón enlaza a un artículo o reseña del sitio, se usan las de ese artículo.', 'revista-koltor-dev' ); ?>
		</p>
	</div>
	<?php
}

/**
 * Renders the optional "mobile image" picker: same Featured-Image-style
 * uploader, but stored separately (_kdv_slide_image_mobile) so a slide can
 * show a tighter portrait crop on small screens instead of the desktop
 * background stretched/cropped by CSS. If left empty, the desktop image is
 * used everywhere — this is purely an optional refinement.
 */
function kdv_render_slide_mobile_image_meta_box( $post ) {
	$image_id  = (int) get_post_meta( $post->ID, '_kdv_slide_image_mobile', true );
	$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : '';
	?>
	<p class="kdv-slide-hint" style="margin-top:0;">
		<?php esc_html_e( 'Si la imagen principal se ve mal recortada en pantallas de celular, sube aquí una versión vertical solo para móvil. Si la dejas vacía, se usa la misma imagen de siempre.', 'revista-koltor-dev' ); ?>
	</p>
	<div id="kdv-slide-mobile-image-preview" style="margin-bottom:10px;<?php echo $image_url ? '' : 'display:none;'; ?>">
		<img src="<?php echo esc_url( $image_url ); ?>" style="max-width:100%;height:auto;border-radius:4px;" />
	</div>
	<input type="hidden" name="kdv_slide_image_mobile_id" id="kdv-slide-mobile-image-id" value="<?php echo esc_attr( $image_id ); ?>" />
	<button type="button" class="button" id="kdv-slide-mobile-image-select"><?php esc_html_e( 'Seleccionar imagen', 'revista-koltor-dev' ); ?></button>
	<button type="button" class="button" id="kdv-slide-mobile-image-remove" style="<?php echo $image_url ? '' : 'display:none;'; ?>"><?php esc_html_e( 'Quitar', 'revista-koltor-dev' ); ?></button>
	<script>
	( function() {
		var frame;
		var selectBtn  = document.getElementById( 'kdv-slide-mobile-image-select' );
		var removeBtn  = document.getElementById( 'kdv-slide-mobile-image-remove' );
		var input      = document.getElementById( 'kdv-slide-mobile-image-id' );
		var preview    = document.getElementById( 'kdv-slide-mobile-image-preview' );

		if ( ! selectBtn ) {
			return;
		}

		selectBtn.addEventListener( 'click', function( e ) {
			e.preventDefault();
			if ( frame ) {
				frame.open();
				return;
			}
			frame = wp.media( {
				title: <?php echo wp_json_encode( __( 'Selecciona la imagen para móvil', 'revista-koltor-dev' ) ); ?>,
				library: { type: 'image' },
				multiple: false,
			} );
			frame.on( 'select', function() {
				var attachment = frame.state().get( 'selection' ).first().toJSON();
				var src = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;
				input.value = attachment.id;
				preview.querySelector( 'img' ).src = src;
				preview.style.display = '';
				removeBtn.style.display = '';
			} );
			frame.open();
		} );

		removeBtn.addEventListener( 'click', function( e ) {
			e.preventDefault();
			input.value = '';
			preview.style.display = 'none';
			removeBtn.style.display = 'none';
		} );
	}() );
	</script>
	<?php
}

function kdv_save_slide_meta( $post_id ) {

	if ( ! isset( $_POST['kdv_slide_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['kdv_slide_nonce'] ) ), 'kdv_save_slide_meta' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['kdv_slide_subtitle'] ) ) {
		update_post_meta( $post_id, '_kdv_slide_subtitle', sanitize_textarea_field( wp_unslash( $_POST['kdv_slide_subtitle'] ) ) );
	}
	if ( isset( $_POST['kdv_slide_button_text'] ) ) {
		update_post_meta( $post_id, '_kdv_slide_button_text', sanitize_text_field( wp_unslash( $_POST['kdv_slide_button_text'] ) ) );
	}
	if ( isset( $_POST['kdv_slide_button_url'] ) ) {
		update_post_meta( $post_id, '_kdv_slide_button_url', esc_url_raw( wp_unslash( $_POST['kdv_slide_button_url'] ) ) );
	}
	if ( isset( $_POST['kdv_slide_image_mobile_id'] ) ) {
		$mobile_id = absint( $_POST['kdv_slide_image_mobile_id'] );
		if ( $mobile_id && wp_attachment_is_image( $mobile_id ) ) {
			update_post_meta( $post_id, '_kdv_slide_image_mobile', $mobile_id );
		} else {
			delete_post_meta( $post_id, '_kdv_slide_image_mobile' );
		}
	}
}
add_action( 'save_post_kdv_slide', 'kdv_save_slide_meta' );
