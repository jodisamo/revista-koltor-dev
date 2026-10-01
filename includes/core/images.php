<?php
/**
 * Imágenes ligeras: WebP para las copias reducidas y logo del tamaño justo.
 *
 * Origen: en la auditoría de rendimiento móvil (1.13.0), el logo pesaba
 * 646 KB y se descargaba en TODAS las páginas -- el 92% de lo que bajaba la
 * portada. Dos causas, dos arreglos:
 *
 * 1. WordPress le decía al navegador que el logo ocupaba todo el ancho de
 *    la pantalla (sizes="(max-width: 1600px) 100vw"), así que un móvil de
 *    alta densidad elegía la copia de 1536px para pintar ~168px. Ahora
 *    "sizes" lleva el ancho real del logo en la cabecera.
 * 2. Las copias de un PNG (o JPEG) se generan en WebP, que pesa una
 *    fracción con la misma calidad -- también una copia WebP de tamaño
 *    completo, que es la que se muestra. El archivo subido se conserva tal
 *    cual en el servidor (WordPress lo guarda como "imagen original").
 *    Solo afecta a las imágenes que se suban a partir de ahora; para las ya
 *    subidas hay que regenerar las miniaturas (por ejemplo con el plugin
 *    "Regenerate Thumbnails" o "wp media regenerate").
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tamaño propio para el logo: 180px de alto y el ancho que corresponda.
 * Cubre el logo de la cabecera (hasta 60px de alto) en pantallas de
 * densidad 3x, que de otro modo saltaban a la copia de 768px o más.
 */
add_action( 'after_setup_theme', function() {
	add_image_size( 'kdv-logo', 0, 180 );
}, 11 );

/**
 * Copias reducidas en WebP (Personalizar → Revista Koltor Dev → Rendimiento
 * → "Generar las imágenes en WebP", activado por defecto). Solo si el
 * servidor sabe generar WebP: si no, WordPress sigue como siempre.
 */
add_filter( 'image_editor_output_format', function( $formats ) {
	if ( ! get_theme_mod( 'kdv_webp_images', true ) ) {
		return $formats;
	}
	if ( ! wp_image_editor_supports( [ 'mime_type' => 'image/webp' ] ) ) {
		return $formats;
	}
	$formats['image/jpeg'] = 'image/webp';
	$formats['image/png']  = 'image/webp';
	return $formats;
} );

/**
 * "sizes" del logo con su ancho real en la cabecera (alto configurado en
 * Personalizar × proporción de la imagen), para que el navegador elija la
 * copia más pequeña que se vea nítida.
 */
add_filter( 'get_custom_logo_image_attributes', function( $attr, $logo_id ) {
	$meta = wp_get_attachment_metadata( $logo_id );
	if ( empty( $meta['width'] ) || empty( $meta['height'] ) ) {
		return $attr;
	}
	$height        = absint( get_theme_mod( 'kdv_logo_height', 56 ) );
	$display_width = (int) ceil( $height * $meta['width'] / $meta['height'] );

	$attr['sizes']         = $display_width . 'px';
	$attr['fetchpriority'] = 'high'; // Está arriba del todo: que no espere.
	$attr['decoding']      = 'async';
	return $attr;
}, 10, 2 );
