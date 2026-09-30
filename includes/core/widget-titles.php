<?php
/**
 * Safety-net translation for the classic core widgets (Entradas recientes,
 * Comentarios recientes, Archivo, Categorías…). WordPress core normally
 * translates these on its own when the site's language is Spanish, but if
 * for any reason the site ends up with the English defaults (missing
 * language pack, a "Legacy Widget" block left untitled by a plugin, etc.)
 * this catches the common ones so the sidebar doesn't mix languages.
 *
 * This does NOT touch widgets where you've typed your own custom title —
 * it only replaces exact matches against the stock WordPress defaults.
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function kdv_translate_default_widget_titles( $title ) {
	static $map = null;

	if ( null === $map ) {
		$map = [
			'Recent Posts'    => __( 'Entradas recientes', 'revista-koltor-dev' ),
			'Recent Comments' => __( 'Comentarios recientes', 'revista-koltor-dev' ),
			'Archives'        => __( 'Archivo', 'revista-koltor-dev' ),
			'Categories'      => __( 'Categorías', 'revista-koltor-dev' ),
			'Meta'            => __( 'Meta', 'revista-koltor-dev' ),
			'Pages'           => __( 'Páginas', 'revista-koltor-dev' ),
			'Search'          => __( 'Buscar', 'revista-koltor-dev' ),
			'Tags'            => __( 'Etiquetas', 'revista-koltor-dev' ),
			'Calendar'        => __( 'Calendario', 'revista-koltor-dev' ),
			'RSS'             => __( 'RSS', 'revista-koltor-dev' ),
		];
	}

	$trimmed = trim( (string) $title );

	return $map[ $trimmed ] ?? $title;
}
add_filter( 'widget_title', 'kdv_translate_default_widget_titles' );
