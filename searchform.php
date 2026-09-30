<?php
/**
 * Search form template.
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ID único por formulario: en search.php y 404.php el buscador sale dos
// veces (cabecera + cuerpo) y un id repetido rompe el <label for>.
$kdv_search_id = wp_unique_id( 'kdv-search-field-' );
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $kdv_search_id ); ?>"><?php esc_html_e( 'Buscar:', 'revista-koltor-dev' ); ?></label>
	<input
		type="search"
		id="<?php echo esc_attr( $kdv_search_id ); ?>"
		name="s"
		placeholder="<?php esc_attr_e( 'Buscar artículos, reseñas…', 'revista-koltor-dev' ); ?>"
		value="<?php echo get_search_query(); ?>"
	/>
	<button type="submit"><?php esc_html_e( 'Buscar', 'revista-koltor-dev' ); ?></button>
</form>
