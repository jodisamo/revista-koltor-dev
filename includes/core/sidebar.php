<?php
/**
 * Barra lateral: en qué páginas se muestra y cómo se ven sus widgets
 * (Personalizar → Revista Koltor Dev → Barra lateral y widgets).
 *
 * El aspecto se controla con clases en <body> y variables CSS (ver el
 * bloque "Sidebar / widgets" de assets/css/main.css), así que la vista
 * previa del Personalizador se actualiza en vivo sin recargar.
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Opciones de estilo de los widgets, compartidas por el Personalizador (lista
 * de opciones y saneado), el CSS dinámico y las clases de <body>.
 *
 * @return array
 */
function kdv_widget_style_options() {
	return [
		'box'         => [
			'card'   => __( 'Tarjeta con sombra (por defecto)', 'revista-koltor-dev' ),
			'border' => __( 'Con borde, sin sombra', 'revista-koltor-dev' ),
			'flat'   => __( 'Plano, sin caja', 'revista-koltor-dev' ),
		],
		'title_style' => [
			'bar'       => __( 'Barra lateral de color (por defecto)', 'revista-koltor-dev' ),
			'underline' => __( 'Subrayado de color', 'revista-koltor-dev' ),
			'plain'     => __( 'Solo texto', 'revista-koltor-dev' ),
		],
		// Clave => tamaño en rem.
		'title_size'  => [
			'small'  => .92,
			'normal' => 1.02,
			'large'  => 1.2,
		],
		// Clave => radio de las esquinas en px.
		'radius'      => [
			'square' => 6,
			'soft'   => 16,
			'round'  => 24,
		],
	];
}

/**
 * ¿Se muestra la barra lateral en la página actual? Según Personalizar →
 * Barra lateral y widgets → "Mostrar la barra lateral en…".
 *
 * @return bool
 */
function kdv_sidebar_enabled_here() {
	if ( is_singular( 'kdv_resena' ) ) {
		$enabled = get_theme_mod( 'kdv_sidebar_on_resenas', true );
	} elseif ( is_singular() ) {
		$enabled = get_theme_mod( 'kdv_sidebar_on_single', true );
	} elseif ( is_search() ) {
		$enabled = get_theme_mod( 'kdv_sidebar_on_search', true );
	} else {
		$enabled = get_theme_mod( 'kdv_sidebar_on_archives', true );
	}
	return (bool) apply_filters( 'kdv_sidebar_enabled_here', $enabled );
}

/**
 * Clases de <body> para el aspecto de los widgets y la barra fija.
 */
add_filter( 'body_class', function( $classes ) {
	$options = kdv_widget_style_options();

	$box = get_theme_mod( 'kdv_widget_box', 'card' );
	$classes[] = 'kdv-widgets-' . ( isset( $options['box'][ $box ] ) ? $box : 'card' );

	$title = get_theme_mod( 'kdv_widget_title_style', 'bar' );
	$classes[] = 'kdv-widget-title-' . ( isset( $options['title_style'][ $title ] ) ? $title : 'bar' );

	if ( ! get_theme_mod( 'kdv_widget_separators', true ) ) {
		$classes[] = 'kdv-widget-no-separators';
	}
	if ( get_theme_mod( 'kdv_sidebar_sticky', true ) ) {
		$classes[] = 'kdv-sidebar-sticky';
	}
	return $classes;
} );

/**
 * Variables CSS de los widgets (se añaden al CSS dinámico del tema, ver
 * kdv_get_dynamic_css() en enqueue.php).
 *
 * @return string Declaraciones para dentro de :root { … }.
 */
function kdv_get_widget_css_vars() {
	$options = kdv_widget_style_options();

	$size   = $options['title_size'][ get_theme_mod( 'kdv_widget_title_size', 'normal' ) ] ?? $options['title_size']['normal'];
	$radius = $options['radius'][ get_theme_mod( 'kdv_widget_radius', 'soft' ) ] ?? $options['radius']['soft'];
	$accent = sanitize_hex_color( get_theme_mod( 'kdv_widget_accent', '' ) );

	$css  = '--kdv-widget-title-size: ' . (float) $size . "rem;\n";
	$css .= '--kdv-widget-radius: ' . absint( $radius ) . "px;\n";
	if ( $accent ) {
		$css .= '--kdv-widget-accent: ' . $accent . ";\n";
	}
	return $css;
}
