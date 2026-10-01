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
 * a mano. No sustituye a una herramienta de analítica de verdad (filtra los
 * bots que se identifican como tales y cuenta una visita por persona y
 * artículo cada 6 horas), pero para
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

	/*
	 * Una visita por persona y artículo cada 6 horas. Sin esto, cualquiera
	 * con un bucle de peticiones (y un User-Agent de navegador normal, que
	 * el filtro de arriba no puede distinguir) subía un artículo a "Populares
	 * del mes" a voluntad. La persona se identifica con un hash de su IP y
	 * una sal secreta del sitio: nunca se guarda la IP en claro.
	 *
	 * Solo REMOTE_ADDR: cabeceras como X-Forwarded-For las inventa el propio
	 * cliente. Detrás de un proxy o CDN que oculte la IP real, el filtro
	 * kdv_view_client_ip permite devolver la buena.
	 */
	$ip = (string) apply_filters( 'kdv_view_client_ip', isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' );
	$seen_key = 'kdv_seen_' . substr( hash_hmac( 'sha256', $post_id . '|' . $ip, wp_salt( 'nonce' ) ), 0, 32 );
	if ( get_transient( $seen_key ) ) {
		return;
	}
	set_transient( $seen_key, 1, 6 * HOUR_IN_SECONDS );

	kdv_increment_post_views( $post_id, '_kdv_views_' . gmdate( 'Ym' ) );
}

/**
 * Suma 1 al contador de forma ATÓMICA: una sola sentencia UPDATE que hace
 * la suma en la base de datos. Antes se leía el número, se sumaba en PHP y
 * se escribía; con varias visitas a la vez, unas pisaban a otras (en una
 * prueba con 60 visitas simultáneas el contador solo subió 30).
 *
 * @param int    $post_id  Entrada.
 * @param string $meta_key Clave del contador del mes.
 */
function kdv_increment_post_views( $post_id, $meta_key ) {
	global $wpdb;

	$updated = $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- incremento atómico; las funciones de metadatos no lo permiten.
		$wpdb->prepare(
			"UPDATE {$wpdb->postmeta} SET meta_value = meta_value + 1 WHERE post_id = %d AND meta_key = %s",
			$post_id,
			$meta_key
		)
	);

	if ( ! $updated ) {
		// Primera visita del mes: crea el contador (único por entrada y clave).
		add_post_meta( $post_id, $meta_key, 1, true );
	}

	wp_cache_delete( $post_id, 'post_meta' );
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
