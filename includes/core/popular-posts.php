<?php
/**
 * "Populares del mes" — contador de vistas 100% nativo (sin Google Analytics
 * ni ningún plugin de terceros) usado para armar la sección/slider de
 * populares de la portada.
 *
 * Cómo funciona: cada vez que alguien visita una entrada o reseña se suma 1
 * a un contador guardado como metadato del post, con una clave distinta
 * para cada mes (p. ej. "_kdv_views_202608"). Al pedir "lo más popular del
 * mes" simplemente ordenamos por el contador del mes actual — así el
 * ranking se reinicia solo cada mes, sin tener que borrar ni archivar nada
 * a mano. No sustituye a una herramienta de analítica de verdad (no filtra
 * bots ni deduplica visitas repetidas de la misma persona), pero para
 * ordenar "qué se está leyendo más esta semana" en la portada es más que
 * suficiente y no depende de ningún servicio externo.
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Suma 1 al contador de vistas del mes en curso para la entrada/reseña que
 * se está viendo. No cuenta vistas en el admin, en vistas previas, ni las
 * del propio staff (cualquiera con permiso de editar entradas) para que
 * revisar o corregir un artículo no infle su propio contador.
 */
function kdv_track_post_view() {
	if ( is_admin() || is_preview() || ! is_singular( [ 'post', 'kdv_resena' ] ) ) {
		return;
	}
	if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
		return;
	}

	// Los rastreadores no son lectores: contarlos infla el ranking y, sobre
	// todo, convierte cada visita de un bot en una escritura en la base de
	// datos. Este filtro es deliberadamente simple (los bots pueden mentir en
	// su identificación), pero descarta de un plumazo el grueso del tráfico
	// automatizado honesto: buscadores, previsualizaciones de enlaces, etc.
	$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] )
		? strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) )
		: '';
	if ( '' === $user_agent ) {
		return;
	}
	foreach ( [ 'bot', 'crawl', 'spider', 'slurp', 'preview', 'facebookexternalhit', 'headless', 'python-requests', 'curl/', 'wget' ] as $needle ) {
		if ( false !== strpos( $user_agent, $needle ) ) {
			return;
		}
	}

	$post_id = get_queried_object_id();
	if ( ! $post_id ) {
		return;
	}

	$meta_key = '_kdv_views_' . gmdate( 'Ym' );
	$current  = get_post_meta( $post_id, $meta_key, true );

	if ( '' === $current ) {
		add_post_meta( $post_id, $meta_key, 1, true );
	} else {
		update_post_meta( $post_id, $meta_key, absint( $current ) + 1 );
	}
}
add_action( 'template_redirect', 'kdv_track_post_view' );

/**
 * Devuelve un WP_Query con las entradas/reseñas más vistas este mes.
 *
 * Si el sitio es nuevo y todavía no hay datos de vistas del mes en curso
 * (p. ej. recién instalado el tema), cae de vuelta a mostrar las entradas
 * más recientes en su lugar, para que la sección nunca se vea vacía o rota.
 *
 * @param int $number Cuántas entradas devolver.
 * @return WP_Query
 */
function kdv_get_popular_posts_this_month( $number = 8 ) {
	$number   = max( 2, absint( $number ) );
	$meta_key = '_kdv_views_' . gmdate( 'Ym' );

	$query = new WP_Query( [
		'post_type'           => [ 'post', 'kdv_resena' ],
		'post_status'         => 'publish',
		'posts_per_page'      => $number,
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
		'meta_key'            => $meta_key,
		'orderby'             => 'meta_value_num',
		'order'               => 'DESC',
	] );

	if ( $query->have_posts() ) {
		return $query;
	}

	return new WP_Query( [
		'post_type'           => [ 'post', 'kdv_resena' ],
		'post_status'         => 'publish',
		'posts_per_page'      => $number,
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
	] );
}
