<?php
/**
 * Search form template.
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="kdv-search-field"><?php esc_html_e( 'Buscar:', 'revista-koltor-dev' ); ?></label>
	<input
		type="search"
		id="kdv-search-field"
		name="s"
		placeholder="<?php esc_attr_e( 'Buscar artículos, reseñas…', 'revista-koltor-dev' ); ?>"
		value="<?php echo get_search_query(); ?>"
	/>
	<button type="submit"><?php esc_html_e( 'Buscar', 'revista-koltor-dev' ); ?></button>
</form>
