<?php
/**
 * Selector visual de icono por categoría, guardado como metadato del
 * término. Se usa en el widget "Koltor Dev: Categorías" para mostrar un
 * icono junto a cada categoría en la barra lateral. Los iconos vienen de
 * la librería propia del tema (includes/core/icon-library.php, SVG en
 * línea, licencia MIT) — nada de fuentes de iconos externas ni plugins.
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Imprime el CSS del selector de iconos (una sola vez). Es un cuadro de
 * "radio buttons" nativos de HTML disfrazados de casillas visuales: la
 * casilla marcada se resalta con un simple selector CSS de hermano
 * adyacente (input:checked + span), sin necesitar JavaScript.
 */
function kdv_print_icon_picker_styles() {
	static $printed = false;
	if ( $printed ) {
		return;
	}
	$printed = true;
	?>
	<style>
		.kdv-icon-picker { display: flex; flex-wrap: wrap; gap: 6px; max-width: 460px; }
		.kdv-icon-picker__option { position: relative; cursor: pointer; }
		.kdv-icon-picker__option input { position: absolute; opacity: 0; width: 1px; height: 1px; }
		.kdv-icon-picker__swatch {
			display: flex; align-items: center; justify-content: center;
			width: 34px; height: 34px; border-radius: 8px;
			border: 1px solid #dcdcde; background: #fff; color: #50575e;
			transition: border-color .15s ease, background .15s ease, color .15s ease;
		}
		.kdv-icon-picker__option:hover .kdv-icon-picker__swatch { border-color: #2e9bd6; }
		.kdv-icon-picker__option input:checked + .kdv-icon-picker__swatch {
			border-color: #2e9bd6; background: #e8f4fb; color: #2e9bd6;
			box-shadow: 0 0 0 1px #2e9bd6 inset;
		}
		.kdv-icon-picker__option input:focus-visible + .kdv-icon-picker__swatch { outline: 2px solid #2e9bd6; outline-offset: 1px; }
		.kdv-icon-picker__none { font-size: .82rem; font-weight: 600; }
	</style>
	<?php
}

/**
 * Imprime el HTML del selector (una grilla de iconos con radio buttons).
 *
 * @param int|string $current Slug del icono actualmente asignado (o '').
 */
function kdv_render_icon_picker( $current ) {
	kdv_print_icon_picker_styles();
	$library = kdv_get_icon_library();
	?>
	<div class="kdv-icon-picker">
		<label class="kdv-icon-picker__option" title="<?php esc_attr_e( 'Sin icono (usa el icono genérico de respaldo)', 'revista-koltor-dev' ); ?>">
			<input type="radio" name="kdv_category_icon" value="" <?php checked( '', $current ); ?>>
			<span class="kdv-icon-picker__swatch kdv-icon-picker__none">—</span>
		</label>
		<?php foreach ( $library as $slug => $icon ) : ?>
			<label class="kdv-icon-picker__option" title="<?php echo esc_attr( $icon['label'] ); ?>">
				<input type="radio" name="kdv_category_icon" value="<?php echo esc_attr( $slug ); ?>" <?php checked( $slug, $current ); ?>>
				<span class="kdv-icon-picker__swatch"><?php kdv_render_icon_svg( $slug, 18 ); ?></span>
			</label>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Campo en el formulario "Añadir nueva categoría".
 */
function kdv_category_icon_field_add() {
	?>
	<div class="form-field">
		<label><?php esc_html_e( 'Icono', 'revista-koltor-dev' ); ?></label>
		<?php kdv_render_icon_picker( '' ); ?>
		<p><?php esc_html_e( 'Opcional: se muestra junto al nombre de esta categoría en el widget "Koltor Dev: Categorías" de la barra lateral.', 'revista-koltor-dev' ); ?></p>
	</div>
	<?php
}
add_action( 'category_add_form_fields', 'kdv_category_icon_field_add' );

/**
 * Campo en el formulario "Editar categoría".
 */
function kdv_category_icon_field_edit( $term ) {
	$current = get_term_meta( $term->term_id, 'kdv_category_icon', true );
	?>
	<tr class="form-field">
		<th scope="row"><label><?php esc_html_e( 'Icono', 'revista-koltor-dev' ); ?></label></th>
		<td>
			<?php kdv_render_icon_picker( $current ); ?>
			<p class="description"><?php esc_html_e( 'Opcional: se muestra junto al nombre de esta categoría en el widget "Koltor Dev: Categorías" de la barra lateral.', 'revista-koltor-dev' ); ?></p>
		</td>
	</tr>
	<?php
}
add_action( 'category_edit_form_fields', 'kdv_category_icon_field_edit' );

/**
 * Guarda el campo al crear/editar una categoría. Solo acepta un slug que
 * exista en la librería de iconos del tema (o vacío, para "sin icono") —
 * al ser un selector de radio buttons y no texto libre, esto es más una
 * comprobación de integridad que una defensa activa, pero mantiene el
 * metadato siempre limpio ante cualquier envío manual del formulario.
 */
function kdv_category_icon_field_save( $term_id ) {
	if ( ! isset( $_POST['kdv_category_icon'] ) ) {
		return;
	}
	// Los hooks created_category/edited_category se disparan desde
	// wp_insert_term()/wp_update_term(), que también puede llamar otro plugin
	// en otro contexto. Comprobamos el permiso sobre este término concreto en
	// vez de dar por hecho que venimos del formulario de categorías.
	if ( ! current_user_can( 'edit_term', $term_id ) ) {
		return;
	}
	// El nonce de este formulario ya lo valida WordPress core antes de
	// disparar los hooks created_category/edited_category en los que se
	// llama esta función (edit-tags.php / term.php), igual que con los
	// demás campos nativos de categoría.
	$icon    = sanitize_key( wp_unslash( $_POST['kdv_category_icon'] ) );
	$library = kdv_get_icon_library();

	if ( '' === $icon || ! isset( $library[ $icon ] ) ) {
		delete_term_meta( $term_id, 'kdv_category_icon' );
	} else {
		update_term_meta( $term_id, 'kdv_category_icon', $icon );
	}
}
add_action( 'created_category', 'kdv_category_icon_field_save' );
add_action( 'edited_category', 'kdv_category_icon_field_save' );
