<?php
/**
 * Theme setup: supports, menus, sidebars, image sizes.
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function kdv_setup() {

	// Translations.
	load_theme_textdomain( 'revista-koltor-dev', KDV_THEME_DIR . '/languages' );

	// Core theme supports.
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ] );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'responsive-embeds' );

	// Gutenberg / block editor support.
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor-style.css' );

	// theme.json already declares the palette/typography, but we also
	// disable the custom color picker so brand consistency isn't broken,
	// while still allowing the site owner to add colours later if desired.
	add_theme_support( 'custom-line-height' );
	add_theme_support( 'custom-spacing' );

	// Custom logo (shows up natively in Customizer > Site Identity).
	add_theme_support( 'custom-logo', [
		'height'      => 80,
		'width'       => 240,
		'flex-height' => true,
		'flex-width'  => true,
	] );

	// Post formats used by standard posts.
	add_theme_support( 'post-formats', [ 'gallery', 'video', 'quote' ] );

	// Nav menus.
	register_nav_menus( [
		'primary'      => esc_html__( 'Menú principal', 'revista-koltor-dev' ),
		'footer'       => esc_html__( 'Menú de pie de página', 'revista-koltor-dev' ),
		// Ubicación aparte para las políticas y el contacto: así los enlaces
		// legales no se mezclan con las secciones de contenido ni recargan
		// el menú principal, que es donde el lector busca categorías.
		'footer_legal' => esc_html__( 'Menú legal (pie de página)', 'revista-koltor-dev' ),
	] );

	// Image sizes used across cards/grids/hero.
	add_image_size( 'kdv-card', 480, 320, true );
	add_image_size( 'kdv-hero', 1200, 720, true );
	add_image_size( 'kdv-square', 320, 320, true );
}
add_action( 'after_setup_theme', 'kdv_setup' );

/**
 * Widget areas (sidebar + footer columns).
 */
function kdv_widgets_init() {
	register_sidebar( [
		'name'          => esc_html__( 'Barra lateral', 'revista-koltor-dev' ),
		'id'            => 'sidebar-main',
		'before_widget' => '<section id="%1$s" class="kdv-widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="kdv-widget-title">',
		'after_title'   => '</h3>',
	] );

	// Always register 4 footer widget areas — "Columnas de widgets en el pie
	// de página" in Personalizar → Pie de página only controls how many of
	// them footer.php actually loops through/displays, so widgets already
	// placed in a hidden column aren't lost if the count is lowered later.
	for ( $i = 1; $i <= 4; $i++ ) {
		register_sidebar( [
			/* translators: %d: footer column number. */
			'name'          => sprintf( esc_html__( 'Pie de página - Columna %d', 'revista-koltor-dev' ), $i ),
			'id'            => 'footer-' . $i,
			'before_widget' => '<section id="%1$s" class="kdv-widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h4 class="kdv-widget-title">',
			'after_title'   => '</h4>',
		] );
	}
}
add_action( 'widgets_init', 'kdv_widgets_init' );

/**
 * Adjust excerpt length and "read more" marker to fit magazine cards. Reads
 * the same "Palabras del resumen" theme_mod the card templates trim to
 * (Personalizar → Tarjetas de contenido) — otherwise this filter would cap
 * every auto-generated excerpt at a fixed word count before the cards ever
 * get a chance to honour a higher configured value.
 */
add_filter( 'excerpt_length', function() {
	return absint( get_theme_mod( 'kdv_card_excerpt_length', 16 ) );
}, 999 );
add_filter( 'excerpt_more', function() { return '&hellip;'; } );

/**
 * "Cabecera fija al hacer scroll" (Personalizar → Cabecera): toggled with a
 * body class instead of a theme_mod check in the CSS itself, since core
 * WordPress classes like this are the simplest way to make a whole style
 * block conditional without inline <style> tags.
 */
add_filter( 'body_class', function( $classes ) {
	if ( get_theme_mod( 'kdv_header_sticky', true ) ) {
		$classes[] = 'kdv-header-sticky';
	}

	/*
	 * La barra flotante de redes se dibuja en el pie, pero hay elementos en lo
	 * alto de la página que necesitan saber que está ahí para no quedar
	 * debajo — la flecha "siguiente" del slider de portada, sobre todo, que
	 * Swiper coloca pegada al borde de la pantalla y a media altura, o sea
	 * justo donde vive la barra. Desde el pie no se puede seleccionar hacia
	 * arriba con CSS, así que el aviso viaja como clase en el <body>.
	 */
	if ( get_theme_mod( 'kdv_social_floating_enabled', false ) && kdv_get_social_links() ) {
		$classes[] = 'left' === get_theme_mod( 'kdv_social_floating_position', 'right' )
			? 'kdv-has-social-bar-left'
			: 'kdv-has-social-bar-right';
	}

	return $classes;
} );

/**
 * Oculta de cualquier menú de navegación los enlaces a categorías que no
 * tengan ninguna entrada publicada — sin tocar la categoría en sí, que
 * sigue existiendo en Entradas → Categorías y reaparece sola en el menú en
 * cuanto se le asigna la primera entrada. Si una categoría "padre" está
 * vacía pero alguna de sus subcategorías sí tiene entradas, el padre se
 * mantiene visible (para poder llegar a esa subcategoría); solo se oculta
 * cuando ni ella ni ninguna de sus subcategorías tienen contenido.
 *
 * No afecta a la pantalla Apariencia → Menús (ahí se ven y editan todos los
 * elementos siempre, estén vacíos o no).
 */
function kdv_hide_empty_category_menu_items( $items, $args ) {
	if ( is_admin() ) {
		return $items;
	}

	$keep = [];

	foreach ( $items as $item ) {
		if ( 'taxonomy' !== $item->type || 'category' !== $item->object ) {
			$keep[ $item->ID ] = true;
			continue;
		}

		$term = get_term( $item->object_id, 'category' );

		if ( ! $term || is_wp_error( $term ) ) {
			$keep[ $item->ID ] = true; // No tocar si algo no cuadra — mejor mostrar de más que ocultar por error.
			continue;
		}

		if ( $term->count > 0 ) {
			$keep[ $item->ID ] = true;
			continue;
		}

		$has_content_child = false;
		$children           = get_term_children( $term->term_id, 'category' );

		if ( ! is_wp_error( $children ) ) {
			foreach ( $children as $child_id ) {
				$child = get_term( $child_id, 'category' );
				if ( $child && ! is_wp_error( $child ) && $child->count > 0 ) {
					$has_content_child = true;
					break;
				}
			}
		}

		$keep[ $item->ID ] = $has_content_child;
	}

	return array_values(
		array_filter(
			$items,
			function( $item ) use ( $keep ) {
				return ! empty( $keep[ $item->ID ] );
			}
		)
	);
}
add_filter( 'wp_nav_menu_objects', 'kdv_hide_empty_category_menu_items', 10, 2 );
