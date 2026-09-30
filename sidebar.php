<?php
/**
 * Sidebar template.
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Personalizar → Barra lateral y widgets → "Mostrar la barra lateral en…".
if ( ! kdv_sidebar_enabled_here() ) {
	return;
}

// El anuncio fijo arriba de la barra solo se pinta si no hay ningún widget
// "Koltor Dev: Publicidad" en ella: con el widget, la posición la elige quien
// administra el sitio (y así el mismo anuncio no sale dos veces).
$kdv_sidebar_has_ad = get_theme_mod( 'kdv_ad_sidebar_enabled', false )
	&& trim( get_theme_mod( 'kdv_ad_sidebar_code', '' ) )
	&& 'sidebar-main' !== is_active_widget( false, false, 'kdv_ad', true );

if ( ! is_active_sidebar( 'sidebar-main' ) && ! $kdv_sidebar_has_ad ) {
	return;
}
?>
<aside class="kdv-sidebar" aria-label="<?php esc_attr_e( 'Barra lateral', 'revista-koltor-dev' ); ?>">
	<?php if ( $kdv_sidebar_has_ad ) : ?>
		<?php kdv_render_ad_slot( 'sidebar' ); ?>
	<?php endif; ?>
	<?php if ( is_active_sidebar( 'sidebar-main' ) ) : ?>
		<?php dynamic_sidebar( 'sidebar-main' ); ?>
	<?php endif; ?>
</aside>
