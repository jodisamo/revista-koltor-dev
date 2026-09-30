<?php
/**
 * Librería de iconos propia del tema — trazos limpios estilo "outline"
 * (línea fina, 2px, esquinas redondeadas), incrustados directamente aquí
 * como SVG en vez de depender de una fuente de iconos o un plugin externo.
 *
 * Los trazos SVG (el atributo "d" de cada icono) provienen del set de
 * iconos Tabler Icons (https://tabler.io/icons), publicado bajo licencia
 * MIT — de uso y redistribución libres, incluso comercial, sin necesidad
 * de atribución. La mayoría son trazos genéricos retematizados con
 * etiquetas en español pensadas para las categorías típicas de un portal
 * de videojuegos (novedades, análisis, guías, eSports, retro,
 * multijugador...); un pequeño grupo (chess, target-arrow, lego, coins)
 * son formas con significado propio de videojuegos, no genéricas.
 * Disponibles en el selector de Entradas → Categorías.
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Devuelve la librería completa de iconos disponibles: slug => [label, svg].
 * "svg" es el contenido interior de un <svg viewBox="0 0 24 24">...</svg>
 * (sin la etiqueta <svg> en sí, para poder controlar el tamaño al imprimirlo).
 *
 * @return array<string, array{label: string, svg: string}>
 */
function kdv_get_icon_library() {
	static $icons = null;

	if ( null !== $icons ) {
		return $icons;
	}

	$icons = [
		'tag'              => [
			'label' => __( 'Etiqueta (genérico)', 'revista-koltor-dev' ),
			'svg'   => '<g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M6.5 7.5a1 1 0 1 0 2 0a1 1 0 1 0-2 0"/><path d="M3 6v5.172a2 2 0 0 0 .586 1.414l7.71 7.71a2.41 2.41 0 0 0 3.408 0l5.592-5.592a2.41 2.41 0 0 0 0-3.408l-7.71-7.71A2 2 0 0 0 11.172 3H6a3 3 0 0 0-3 3"/></g>',
		],
		'star'             => [
			'label' => __( 'Destacado / Reseñas', 'revista-koltor-dev' ),
			'svg'   => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m12 17.75l-6.172 3.245l1.179-6.873l-5-4.867l6.9-1l3.086-6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z"/>',
		],
		'movie'            => [
			'label' => __( 'Adaptaciones (cine y series)', 'revista-koltor-dev' ),
			'svg'   => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2zm4-2v16m8-16v16M4 8h4m-4 8h4m-4-4h16m-4-4h4m-4 8h4"/>',
		],
		'book-2'           => [
			'label' => __( 'Guías', 'revista-koltor-dev' ),
			'svg'   => '<g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M19 4v16H7a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/><path d="M19 16H7a2 2 0 0 0-2 2M9 8h6"/></g>',
		],
		'book'             => [
			'label' => __( 'Análisis', 'revista-koltor-dev' ),
			'svg'   => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19a9 9 0 0 1 9 0a9 9 0 0 1 9 0M3 6a9 9 0 0 1 9 0a9 9 0 0 1 9 0M3 6v13m9-13v13m9-13v13"/>',
		],
		'news'             => [
			'label' => __( 'Noticias', 'revista-koltor-dev' ),
			'svg'   => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 6h3a1 1 0 0 1 1 1v11a2 2 0 0 1-4 0V5a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v12a3 3 0 0 0 3 3h11M8 8h4m-4 4h4m-4 4h4"/>',
		],
		'device-gamepad-2' => [
			'label' => __( 'Videojuegos', 'revista-koltor-dev' ),
			'svg'   => '<g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M12 5h3.5a5 5 0 0 1 0 10H10l-4.015 4.227a2.3 2.3 0 0 1-3.923-2.035l1.634-8.173A5 5 0 0 1 8.6 5z"/><path d="m14 15l4.07 4.284a2.3 2.3 0 0 0 3.925-2.023l-1.6-8.232M8 9v2m-1-1h2m5 0h2"/></g>',
		],
		'music'            => [
			'label' => __( 'Banda sonora / OST', 'revista-koltor-dev' ),
			'svg'   => '<g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M3 17a3 3 0 1 0 6 0a3 3 0 0 0-6 0m10 0a3 3 0 1 0 6 0a3 3 0 0 0-6 0"/><path d="M9 17V4h10v13M9 8h10"/></g>',
		],
		'headphones'       => [
			'label' => __( 'Podcast', 'revista-koltor-dev' ),
			'svg'   => '<g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M4 15a2 2 0 0 1 2-2h1a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2zm11 0a2 2 0 0 1 2-2h1a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2z"/><path d="M4 15v-3a8 8 0 0 1 16 0v3"/></g>',
		],
		'palette'          => [
			'label' => __( 'Arte conceptual', 'revista-koltor-dev' ),
			'svg'   => '<g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M12 21a9 9 0 0 1 0-18c4.97 0 9 3.582 9 8c0 1.06-.474 2.078-1.318 2.828S17.693 15 16.5 15H14a2 2 0 0 0-1 3.75A1.3 1.3 0 0 1 12 21"/><path d="M7.5 10.5a1 1 0 1 0 2 0a1 1 0 1 0-2 0m4-3a1 1 0 1 0 2 0a1 1 0 1 0-2 0m4 3a1 1 0 1 0 2 0a1 1 0 1 0-2 0"/></g>',
		],
		'calendar-event'   => [
			'label' => __( 'Eventos', 'revista-koltor-dev' ),
			'svg'   => '<g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M4 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2zm12-4v4M8 3v4m-4 4h16"/><path d="M8 15h2v2H8z"/></g>',
		],
		'users'            => [
			'label' => __( 'Comunidad', 'revista-koltor-dev' ),
			'svg'   => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 7a4 4 0 1 0 8 0a4 4 0 1 0-8 0M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2m1-17.87a4 4 0 0 1 0 7.75M21 21v-2a4 4 0 0 0-3-3.85"/>',
		],
		'camera'           => [
			'label' => __( 'Capturas de pantalla', 'revista-koltor-dev' ),
			'svg'   => '<g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M5 7h1a2 2 0 0 0 2-2a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1a2 2 0 0 0 2 2h1a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2"/><path d="M9 13a3 3 0 1 0 6 0a3 3 0 0 0-6 0"/></g>',
		],
		'video'            => [
			'label' => __( 'Vídeo / Trailers', 'revista-koltor-dev' ),
			'svg'   => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 10l4.553-2.276A1 1 0 0 1 21 8.618v6.764a1 1 0 0 1-1.447.894L15 14zM3 8a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>',
		],
		'trophy'           => [
			'label' => __( 'Logros / Trofeos', 'revista-koltor-dev' ),
			'svg'   => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 21h8m-4-4v4M7 4h10m0 0v8a5 5 0 0 1-10 0V4M3 9a2 2 0 1 0 4 0a2 2 0 1 0-4 0m14 0a2 2 0 1 0 4 0a2 2 0 1 0-4 0"/>',
		],
		'building'         => [
			'label' => __( 'Empresas / Estudios', 'revista-koltor-dev' ),
			'svg'   => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M9 8h1m-1 4h1m-1 4h1m4-8h1m-1 4h1m-1 4h1M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"/>',
		],
		'sparkles'         => [
			'label' => __( 'Novedades', 'revista-koltor-dev' ),
			'svg'   => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 18a2 2 0 0 1 2 2a2 2 0 0 1 2-2a2 2 0 0 1-2-2a2 2 0 0 1-2 2m0-12a2 2 0 0 1 2 2a2 2 0 0 1 2-2a2 2 0 0 1-2-2a2 2 0 0 1-2 2M9 18a6 6 0 0 1 6-6a6 6 0 0 1-6-6a6 6 0 0 1-6 6a6 6 0 0 1 6 6"/>',
		],
		'bulb'             => [
			'label' => __( 'Curiosidades', 'revista-koltor-dev' ),
			'svg'   => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12h1m8-9v1m8 8h1M5.6 5.6l.7.7m12.1-.7l-.7.7M9 16a5 5 0 1 1 6 0a3.5 3.5 0 0 0-1 3a2 2 0 0 1-4 0a3.5 3.5 0 0 0-1-3m.7 1h4.6"/>',
		],
		'message-circle'   => [
			'label' => __( 'Opinión', 'revista-koltor-dev' ),
			'svg'   => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m3 20l1.3-3.9C1.976 12.663 2.874 8.228 6.4 5.726c3.526-2.501 8.59-2.296 11.845.48c3.255 2.777 3.695 7.266 1.029 10.501S11.659 20.922 7.7 19z"/>',
		],
		'pencil'           => [
			'label' => __( 'Editorial', 'revista-koltor-dev' ),
			'svg'   => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 20h4L18.5 9.5a2.828 2.828 0 1 0-4-4L4 16zm9.5-13.5l4 4"/>',
		],
		'device-tv'        => [
			'label' => __( 'Streaming', 'revista-koltor-dev' ),
			'svg'   => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2zm13-6l-4 4l-4-4"/>',
		],
		'microphone-2'     => [
			'label' => __( 'Entrevistas', 'revista-koltor-dev' ),
			'svg'   => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12.9A5 5 0 1 0 11.098 9M15 12.9l-3.902-3.899l-7.513 8.584a2 2 0 1 0 2.827 2.83z"/>',
		],
		'clock'            => [
			'label' => __( 'Historia / Retro', 'revista-koltor-dev' ),
			'svg'   => '<g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0-18 0"/><path d="M12 7v5l3 3"/></g>',
		],
		'device-laptop'    => [
			'label' => __( 'PC / Tecnología', 'revista-koltor-dev' ),
			'svg'   => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19h18M5 7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1z"/>',
		],
		'heart'            => [
			'label' => __( 'Favoritos', 'revista-koltor-dev' ),
			'svg'   => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 12.572L12 20l-7.5-7.428A5 5 0 1 1 12 6.006a5 5 0 1 1 7.5 6.572"/>',
		],
		'mood-smile'       => [
			'label' => __( 'Humor', 'revista-koltor-dev' ),
			'svg'   => '<g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M3 12a9 9 0 1 0 18 0a9 9 0 1 0-18 0m6-2h.01M15 10h.01"/><path d="M9.5 15a3.5 3.5 0 0 0 5 0"/></g>',
		],
		'bolt'             => [
			'label' => __( 'Acción', 'revista-koltor-dev' ),
			'svg'   => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 3v7h6l-8 11v-7H5z"/>',
		],
		'wand'             => [
			'label' => __( 'Fantasía', 'revista-koltor-dev' ),
			'svg'   => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 21L21 6l-3-3L3 18zm9-15l3 3M9 3a2 2 0 0 0 2 2a2 2 0 0 0-2 2a2 2 0 0 0-2-2a2 2 0 0 0 2-2m10 10a2 2 0 0 0 2 2a2 2 0 0 0-2 2a2 2 0 0 0-2-2a2 2 0 0 0 2-2"/>',
		],
		'ghost'            => [
			'label' => __( 'Terror', 'revista-koltor-dev' ),
			'svg'   => '<g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M5 11a7 7 0 0 1 14 0v7a1.78 1.78 0 0 1-3.1 1.4a1.65 1.65 0 0 0-2.6 0a1.65 1.65 0 0 1-2.6 0a1.65 1.65 0 0 0-2.6 0A1.78 1.78 0 0 1 5 18zm5-1h.01M14 10h.01"/><path d="M10 14a3.5 3.5 0 0 0 4 0"/></g>',
		],
		'shopping-bag'     => [
			'label' => __( 'Tienda / Merchandising', 'revista-koltor-dev' ),
			'svg'   => '<g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M6.331 8H17.67a2 2 0 0 1 1.977 2.304l-1.255 8.152A3 3 0 0 1 15.426 21H8.574a3 3 0 0 1-2.965-2.544l-1.255-8.152A2 2 0 0 1 6.331 8"/><path d="M9 11V6a3 3 0 0 1 6 0v5"/></g>',
		],
		'world'            => [
			'label' => __( 'Multijugador online', 'revista-koltor-dev' ),
			'svg'   => '<g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0-18 0m.6-3h16.8M3.6 15h16.8"/><path d="M11.5 3a17 17 0 0 0 0 18m1-18a17 17 0 0 1 0 18"/></g>',
		],
		'flag'             => [
			'label' => __( 'Competición / eSports', 'revista-koltor-dev' ),
			'svg'   => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a5 5 0 0 1 7 0a5 5 0 0 0 7 0v9a5 5 0 0 1-7 0a5 5 0 0 0-7 0zm0 16v-7"/>',
		],
		'crown'            => [
			'label' => __( 'Top / Lo mejor', 'revista-koltor-dev' ),
			'svg'   => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m12 6l4 6l5-4l-2 10H5L3 8l5 4z"/>',
		],
		'flame'            => [
			'label' => __( 'Tendencia', 'revista-koltor-dev' ),
			'svg'   => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10.941c2.333-3.308.167-7.823-1-8.941c0 3.395-2.235 5.299-3.667 6.706C5.903 10.114 5 12 5 14.294C5 17.998 8.134 21 12 21s7-3.002 7-6.706c0-1.712-1.232-4.403-2.333-5.588c-2.084 3.353-3.257 3.353-4.667 2.235"/>',
		],
		'brush'            => [
			'label' => __( 'Fan art', 'revista-koltor-dev' ),
			'svg'   => '<g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M3 21v-4a4 4 0 1 1 4 4z"/><path d="M21 3A16 16 0 0 0 8.2 13.2M21 3a16 16 0 0 1-10.2 12.8"/><path d="M10.6 9a9 9 0 0 1 4.4 4.4"/></g>',
		],
		'sword'            => [
			'label' => __( 'Aventura', 'revista-koltor-dev' ),
			'svg'   => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 4v5l-9 7l-4 4l-3-3l4-4l7-9zM6.5 11.5l6 6"/>',
		],
		'robot'            => [
			'label' => __( 'Robótica / Sci-fi', 'revista-koltor-dev' ),
			'svg'   => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2zm6-4v2m-3 8v9m6-9v9M5 16l4-2m6 0l4 2M9 18h6M10 8v.01M14 8v.01"/>',
		],

		'ufo'              => [
			'label' => __( 'Ciencia ficción / Alienígenas', 'revista-koltor-dev' ),
			'svg'   => '<g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M16.95 9.01c3.02.739 5.05 2.123 5.05 3.714C22 15.091 17.52 17 12 17S2 15.091 2 12.724C2 11.134 4.04 9.739 7.07 9"/><path d="M7 9c0 1.105 2.239 2 5 2s5-.895 5-2v-.035C17 6.223 14.761 4 12 4S7 6.223 7 8.965zm8 8l2 3m-8.5-3L7 20m5-6h.01M7 13h.01M17 13h.01"/></g>',
		],
		'rocket'           => [
			'label' => __( 'Espacio / Sci-fi', 'revista-koltor-dev' ),
			'svg'   => '<g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M4 13a8 8 0 0 1 7 7a6 6 0 0 0 3-5a9 9 0 0 0 6-8a3 3 0 0 0-3-3a9 9 0 0 0-8 6a6 6 0 0 0-5 3"/><path d="M7 14a6 6 0 0 0-3 6a6 6 0 0 0 6-3m4-8a1 1 0 1 0 2 0a1 1 0 1 0-2 0"/></g>',
		],
		'swords'           => [
			'label' => __( 'Batalla / Duelo', 'revista-koltor-dev' ),
			'svg'   => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 3v5l-11 9l-4 4l-3-3l4-4l9-11zM5 13l6 6m3.32-1.68L18 21l3-3l-3.365-3.365M10 5.5L8 3H3v5l3 2.5"/>',
		],
		'confetti'         => [
			'label' => __( 'Lanzamientos', 'revista-koltor-dev' ),
			'svg'   => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5h2M5 4v2m6.5-2L11 6m7-1h2m-1-1v2m-4 3l-1 1m4 3l2-.5M18 19h2m-1-1v2m-5-3.482L7.482 10l-4.39 9.58a1 1 0 0 0 1.329 1.329z"/>',
		],

		/*
		 * Los 4 iconos siguientes NO son relabels de la librería original:
		 * son iconos nuevos, tomados literalmente de Tabler Icons (tabler.io),
		 * misma licencia MIT que el resto de este archivo, elegidos por su
		 * significado propio de videojuegos (no genérico de revista).
		 */
		'chess'            => [
			'label' => __( 'Estrategia', 'revista-koltor-dev' ),
			'svg'   => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3a3 3 0 0 1 3 3c0 1.113 -.6 2.482 -1.5 3l1.5 7h-6l1.5 -7c-.9 -.518 -1.5 -1.887 -1.5 -3a3 3 0 0 1 3 -3"/><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9h8"/><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.684 16.772a1 1 0 0 0 -.684 .949v1.279a1 1 0 0 0 1 1h10a1 1 0 0 0 1 -1v-1.28a1 1 0 0 0 -.684 -.948l-2.316 -.772h-6l-2.316 .772"/>',
		],
		'target-arrow'     => [
			'label' => __( 'Disparos / FPS', 'revista-koltor-dev' ),
			'svg'   => '<g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"/><path d="M12 7a5 5 0 1 0 5 5"/><path d="M13 3.055a9 9 0 1 0 7.941 7.945"/><path d="M15 6v3h3l3 -3h-3v-3z"/><path d="M15 9l-3 3"/></g>',
		],
		'lego'             => [
			'label' => __( 'Construcción / Sandbox', 'revista-koltor-dev' ),
			'svg'   => '<g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M9.5 11l.01 0"/><path d="M14.5 11l.01 0"/><path d="M9.5 15a3.5 3.5 0 0 0 5 0"/><path d="M7 5h1v-2h8v2h1a3 3 0 0 1 3 3v9a3 3 0 0 1 -3 3v1h-10v-1a3 3 0 0 1 -3 -3v-9a3 3 0 0 1 3 -3"/></g>',
		],
		'coins'            => [
			'label' => __( 'Economía / Moneda del juego', 'revista-koltor-dev' ),
			'svg'   => '<g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M9 14c0 1.657 2.686 3 6 3s6 -1.343 6 -3s-2.686 -3 -6 -3s-6 1.343 -6 3"/><path d="M9 14v4c0 1.656 2.686 3 6 3s6 -1.344 6 -3v-4"/><path d="M3 6c0 1.072 1.144 2.062 3 2.598s4.144 .536 6 0c1.856 -.536 3 -1.526 3 -2.598c0 -1.072 -1.144 -2.062 -3 -2.598s-4.144 -.536 -6 0c-1.856 .536 -3 1.526 -3 2.598"/><path d="M3 6v10c0 .888 .772 1.45 2 2"/><path d="M3 11c0 .888 .772 1.45 2 2"/></g>',
		],
	];

	return $icons;
}

/**
 * Imprime un icono de la librería como SVG en línea.
 *
 * @param string $slug Clave del icono (ver kdv_get_icon_library()). Si no
 *                      existe en la librería, cae de vuelta al icono
 *                      genérico "tag" para no dejar nada roto/vacío.
 * @param int    $size Ancho/alto en píxeles.
 */
function kdv_render_icon_svg( $slug, $size = 20 ) {
	$library = kdv_get_icon_library();
	if ( ! isset( $library[ $slug ] ) ) {
		$slug = 'tag';
	}
	printf(
		'<svg class="kdv-icon" width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">%2$s</svg>',
		absint( $size ),
		$library[ $slug ]['svg'] // phpcs:ignore WordPress.Security.EscapeOutput -- trusted, hardcoded SVG markup from the theme's own icon library (kdv_get_icon_library()), not user input.
	);
}
