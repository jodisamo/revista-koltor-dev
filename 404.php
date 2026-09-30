<?php
/**
 * 404 template.
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="kdv-container">
	<div class="kdv-empty-state">
		<h1><?php esc_html_e( '¡Ups! Esta página se perdió en el isekai 🌀', 'revista-koltor-dev' ); ?></h1>
		<p><?php esc_html_e( 'No encontramos lo que buscabas. Prueba buscando algo distinto.', 'revista-koltor-dev' ); ?></p>
		<div style="max-width:420px;margin:24px auto;">
			<?php get_search_form(); ?>
		</div>
		<a class="kdv-btn" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Volver al inicio', 'revista-koltor-dev' ); ?></a>
	</div>
</div>
<?php get_footer(); ?>
