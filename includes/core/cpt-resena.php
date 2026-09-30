<?php
/**
 * Custom Post Type "Reseña" + taxonomías "Género" y "Estudio".
 *
 * Modelo de contenido mixto:
 * - "Reseñas" (kdv_resena) es un CPT propio, con ficha técnica y puntuación.
 * - Novedades, Análisis y Guías siguen siendo categorías normales
 *   de "Entradas" (post), para no complicar el modelo de datos.
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function kdv_register_cpt_resena() {

	$labels = [
		'name'                  => _x( 'Reseñas', 'Post type general name', 'revista-koltor-dev' ),
		'singular_name'         => _x( 'Reseña', 'Post type singular name', 'revista-koltor-dev' ),
		'menu_name'             => _x( 'Reseñas', 'Admin Menu text', 'revista-koltor-dev' ),
		'add_new_item'          => __( 'Añadir nueva reseña', 'revista-koltor-dev' ),
		'edit_item'             => __( 'Editar reseña', 'revista-koltor-dev' ),
		'new_item'              => __( 'Nueva reseña', 'revista-koltor-dev' ),
		'view_item'             => __( 'Ver reseña', 'revista-koltor-dev' ),
		'view_items'            => __( 'Ver reseñas', 'revista-koltor-dev' ),
		'search_items'          => __( 'Buscar reseñas', 'revista-koltor-dev' ),
		'not_found'             => __( 'No se encontraron reseñas.', 'revista-koltor-dev' ),
		'not_found_in_trash'    => __( 'No hay reseñas en la papelera.', 'revista-koltor-dev' ),
		'all_items'             => __( 'Todas las reseñas', 'revista-koltor-dev' ),
		'archives'              => __( 'Archivo de reseñas', 'revista-koltor-dev' ),
		'featured_image'        => __( 'Portada / imagen principal', 'revista-koltor-dev' ),
		'set_featured_image'    => __( 'Establecer portada', 'revista-koltor-dev' ),
		'remove_featured_image' => __( 'Quitar portada', 'revista-koltor-dev' ),
	];

	register_post_type( 'kdv_resena', [
		'labels'        => $labels,
		'public'        => true,
		'has_archive'   => 'resenas',
		'rewrite'       => [ 'slug' => 'resenas', 'with_front' => false ],
		'menu_icon'     => 'dashicons-star-filled',
		'menu_position' => 5,
		'supports'      => [ 'title', 'editor', 'thumbnail', 'excerpt', 'comments', 'revisions', 'custom-fields' ],
		'show_in_rest'  => true, // Editor de bloques + REST API.
		'taxonomies'    => [ 'kdv_genero', 'kdv_estudio' ],
	] );
}
add_action( 'init', 'kdv_register_cpt_resena' );

function kdv_register_taxonomies() {

	// Género: compartido entre Reseñas y Entradas estándar,
	// así un artículo de una categoría y una reseña pueden compartir género.
	register_taxonomy( 'kdv_genero', [ 'kdv_resena', 'post' ], [
		'labels' => [
			'name'          => __( 'Géneros', 'revista-koltor-dev' ),
			'singular_name' => __( 'Género', 'revista-koltor-dev' ),
			'search_items'  => __( 'Buscar géneros', 'revista-koltor-dev' ),
			'all_items'     => __( 'Todos los géneros', 'revista-koltor-dev' ),
			'edit_item'     => __( 'Editar género', 'revista-koltor-dev' ),
			'add_new_item'  => __( 'Añadir nuevo género', 'revista-koltor-dev' ),
			'menu_name'     => __( 'Géneros', 'revista-koltor-dev' ),
		],
		'hierarchical' => true,
		'public'       => true,
		'show_in_rest' => true,
		'rewrite'      => [ 'slug' => 'genero' ],
	] );

	// Estudio / desarrollador / editorial — solo aplica a Reseñas.
	register_taxonomy( 'kdv_estudio', [ 'kdv_resena' ], [
		'labels' => [
			'name'          => __( 'Estudios', 'revista-koltor-dev' ),
			'singular_name' => __( 'Estudio', 'revista-koltor-dev' ),
			'search_items'  => __( 'Buscar estudios', 'revista-koltor-dev' ),
			'all_items'     => __( 'Todos los estudios', 'revista-koltor-dev' ),
			'edit_item'     => __( 'Editar estudio', 'revista-koltor-dev' ),
			'add_new_item'  => __( 'Añadir nuevo estudio', 'revista-koltor-dev' ),
			'menu_name'     => __( 'Estudios', 'revista-koltor-dev' ),
		],
		'hierarchical' => false,
		'public'       => true,
		'show_in_rest' => true,
		'rewrite'      => [ 'slug' => 'estudio' ],
	] );

	// Etiquetas: reutiliza la taxonomía nativa "post_tag" de WordPress (la
	// misma que ya usan las Entradas normales) para que las Reseñas también
	// puedan llevar sus propias etiquetas, sin crear una taxonomía nueva.
	// Se registra aquí (no en 'taxonomies' de register_post_type) porque
	// post_tag ya existe — solo hace falta sumar 'kdv_resena' a sus tipos.
	register_taxonomy_for_object_type( 'post_tag', 'kdv_resena' );
}
add_action( 'init', 'kdv_register_taxonomies' );

/**
 * WordPress solo incluye el post type "post" en los archivos de etiqueta por
 * defecto, aunque post_tag esté registrada para otros tipos. Sin este ajuste,
 * las etiquetas de una Reseña "existirían" pero su enlace nunca mostraría
 * otras Reseñas con la misma etiqueta (solo Entradas).
 */
function kdv_include_resenas_in_tag_archive( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->is_tag() ) {
		$query->set( 'post_type', [ 'post', 'kdv_resena' ] );
	}
}
add_action( 'pre_get_posts', 'kdv_include_resenas_in_tag_archive' );

/**
 * Categorías sugeridas para las Entradas estándar.
 * Se crean solo una vez, al activar el tema, y no se vuelven a tocar después
 * (para no pisar cambios que el usuario haga en Categorías).
 */
function kdv_create_default_categories() {
	if ( get_option( 'kdv_default_categories_created' ) ) {
		return;
	}

	$categories = [ 'Novedades', 'Análisis', 'Guías' ];

	foreach ( $categories as $cat_name ) {
		if ( ! term_exists( $cat_name, 'category' ) ) {
			wp_insert_term( $cat_name, 'category' );
		}
	}

	update_option( 'kdv_default_categories_created', 1 );
}
add_action( 'after_switch_theme', 'kdv_create_default_categories' );

/**
 * Flush rewrite rules on theme activation/deactivation so /resenas/ works immediately.
 */
add_action( 'after_switch_theme', function() {
	kdv_register_cpt_resena();
	kdv_register_taxonomies();
	flush_rewrite_rules();
} );

add_action( 'switch_theme', function() {
	flush_rewrite_rules();
} );
