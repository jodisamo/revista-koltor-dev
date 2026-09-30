<?php
/**
 * The header for our theme.
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php if ( is_singular( [ 'post', 'kdv_resena' ] ) ) : ?>
	<?php kdv_render_reading_progress_bar(); ?>
<?php endif; ?>

<a class="screen-reader-text" href="#kdv-content"><?php esc_html_e( 'Ir al contenido', 'revista-koltor-dev' ); ?></a>

<?php $kdv_header_align = get_theme_mod( 'kdv_header_logo_align', 'left' ); ?>
<header class="kdv-header" id="kdv-header">
	<div class="kdv-container kdv-header__bar<?php echo 'center' === $kdv_header_align ? ' kdv-header__bar--centered' : ''; ?>">

		<div class="kdv-header__brand">
			<?php kdv_site_branding(); ?>
			<?php if ( get_theme_mod( 'kdv_show_header_tagline', true ) && get_bloginfo( 'description' ) ) : ?>
				<span class="kdv-site-tagline"><?php bloginfo( 'description' ); ?></span>
			<?php endif; ?>
		</div>

		<?php
		/*
		 * Fondo oscuro del menú móvil. Existe siempre en el HTML pero solo se
		 * ve cuando el menú está abierto: tocarlo lo cierra, que es lo que
		 * cualquiera espera al tocar fuera de un panel.
		 */
		?>
		<div class="kdv-nav-backdrop" id="kdv-nav-backdrop" aria-hidden="true"></div>

		<nav class="kdv-header__nav" aria-label="<?php esc_attr_e( 'Menú principal', 'revista-koltor-dev' ); ?>">
			<button type="button" class="kdv-nav-close" id="kdv-nav-close" aria-label="<?php esc_attr_e( 'Cerrar menú', 'revista-koltor-dev' ); ?>">
				<span aria-hidden="true">&times;</span>
			</button>
			<?php
			$kdv_menu_style = get_theme_mod( 'kdv_menu_style', 'color' );
			wp_nav_menu( [
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'kdv-primary-menu kdv-primary-menu--' . esc_attr( $kdv_menu_style ),
				'fallback_cb'    => false,
			] );
			?>

			<?php if ( get_theme_mod( 'kdv_header_show_social', false ) && kdv_get_social_links() ) : ?>
				<div class="kdv-header-social">
					<?php kdv_render_social_links(); ?>
				</div>
			<?php endif; ?>
		</nav>

		<div class="kdv-header__actions">
			<?php if ( get_theme_mod( 'kdv_header_show_search', true ) ) : ?>
				<button type="button" class="kdv-icon-btn" id="kdv-search-toggle" aria-expanded="false" aria-controls="kdv-header-search" aria-label="<?php esc_attr_e( 'Buscar', 'revista-koltor-dev' ); ?>">
					<svg viewBox="0 0 24 24" width="19" height="19" aria-hidden="true" focusable="false"><circle cx="11" cy="11" r="6.5" fill="none" stroke="currentColor" stroke-width="2"/><path d="M20 20l-4.3-4.3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
				</button>
			<?php endif; ?>

			<?php kdv_dark_mode_toggle_button(); ?>

			<button type="button" class="kdv-icon-btn" id="kdv-menu-toggle" aria-expanded="false" aria-controls="kdv-header" aria-label="<?php esc_attr_e( 'Abrir menú', 'revista-koltor-dev' ); ?>" data-label-close="<?php esc_attr_e( 'Cerrar menú', 'revista-koltor-dev' ); ?>">
				<span class="kdv-menu-toggle__bar"></span>
				<span class="kdv-menu-toggle__bar"></span>
				<span class="kdv-menu-toggle__bar"></span>
			</button>
		</div>
	</div>

	<?php if ( get_theme_mod( 'kdv_header_show_search', true ) ) : ?>
		<div class="kdv-header-search" id="kdv-header-search">
			<?php get_search_form(); ?>
		</div>
	<?php endif; ?>
</header>

<?php if ( get_theme_mod( 'kdv_ad_header_enabled', false ) && trim( get_theme_mod( 'kdv_ad_header_code', '' ) ) ) : ?>
	<div class="kdv-container">
		<?php kdv_render_ad_slot( 'header' ); ?>
	</div>
<?php endif; ?>

<div id="kdv-content" class="kdv-site-content">
