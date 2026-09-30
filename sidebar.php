<?php
/**
 * Sidebar template.
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$kdv_sidebar_has_ad = get_theme_mod( 'kdv_ad_sidebar_enabled', false ) && trim( get_theme_mod( 'kdv_ad_sidebar_code', '' ) );

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
