<?php
/**
 * Modo construcción — sustituye TODO el frontend por una pantalla simple
 * mientras el sitio se prepara, sin plugin de terceros: un solo hook en
 * "template_redirect" que decide si deja pasar la petición o la corta ahí
 * mismo con una página propia.
 *
 * Decisiones deliberadas:
 * - Devuelve 503 (Servicio no disponible) + Retry-After, no 200 ni 404.
 *   Es lo que Google recomienda para "el sitio volverá pronto": un 200
 *   arriesga que el buscador indexe la pantalla de "en construcción" como
 *   si fuera contenido real; un 404 puede hacer que deje de rastrear la
 *   URL por completo. 503 dice explícitamente "temporal, vuelve luego".
 * - No toca wp-admin, REST, cron, AJAX, feeds ni la pantalla de login:
 *   "template_redirect" sencillamente no se dispara en ninguno de esos
 *   contextos, así que no hace falta comprobarlo a mano.
 * - Cualquiera con capacidad de editar contenido (current_user_can(
 *   'edit_posts' )) sigue viendo el sitio real al iniciar sesión — mismo
 *   criterio que ya usa el contador de vistas (popular-posts.php) para
 *   distinguir "visitante" de "alguien del equipo".
 * - Se desenganchan a mano los dos hooks de includes/core/seo.php: si no,
 *   el schema.org de una reseña real o el Open Graph de la página pedida
 *   se colarían por debajo de la pantalla de mantenimiento.
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return bool Si el modo construcción debe aplicarse a la petición actual.
 */
function kdv_is_maintenance_mode_active() {
	if ( ! get_theme_mod( 'kdv_maintenance_enabled', false ) ) {
		return false;
	}
	if ( current_user_can( 'edit_posts' ) ) {
		return false;
	}
	return true;
}

function kdv_maybe_show_maintenance_page() {
	if ( ! kdv_is_maintenance_mode_active() ) {
		return;
	}

	// Evita que el schema.org/Open Graph de la página realmente pedida
	// aparezca bajo la pantalla de mantenimiento (ver includes/core/seo.php).
	remove_action( 'wp_head', 'kdv_render_review_schema' );
	remove_action( 'wp_head', 'kdv_render_article_schema' );
	remove_action( 'wp_head', 'kdv_render_site_schema' );
	remove_action( 'wp_head', 'kdv_render_fallback_seo_meta', 1 );

	/*
	 * Lo mismo con lo que imprime el propio WordPress en <head>: sin esto,
	 * al pedir la URL de un artículo la pantalla de mantenimiento llevaba su
	 * título (un segundo <title>), su canonical, su enlace corto con el ID y
	 * sus enlaces de feed, oEmbed y API -- bastaba con probar URLs para
	 * confirmar que una reseña con embargo existe y leer su título.
	 */
	remove_action( 'wp_head', '_wp_render_title_tag', 1 );
	remove_action( 'wp_head', 'feed_links', 2 );
	remove_action( 'wp_head', 'feed_links_extra', 3 );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'rel_canonical' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );

	// Y la consulta principal se vacía: así tampoco un plugin (Yoast, Rank
	// Math…) que lea el artículo actual al pintar <head> encuentra nada.
	$GLOBALS['wp_query']     = new WP_Query();
	$GLOBALS['wp_the_query'] = $GLOBALS['wp_query'];
	unset( $GLOBALS['post'] );

	status_header( 503 );
	header( 'Retry-After: 3600' );

	kdv_render_maintenance_page();
	exit;
}
add_action( 'template_redirect', 'kdv_maybe_show_maintenance_page' );

/**
 * "template_redirect" solo se dispara al servir una página normal del
 * sitio -- la API REST (/wp-json/...) corre por su propio camino
 * ("rest_api_init") y NUNCA pasa por ahí. Sin este filtro, con el modo
 * construcción activado un visitante ve la pantalla de "en construcción"
 * en el navegador, pero cualquiera puede seguir pidiendo
 * /wp-json/wp/v2/posts, /wp-json/wp/v2/kdv_resena, /wp-json/wp/v2/kdv_slide,
 * etc. y recibir el contenido real completo en JSON -- el sitio "oculto"
 * seguiría totalmente visible por esa vía. Se usa el mismo criterio que la
 * pantalla normal (kdv_is_maintenance_mode_active(), que ya deja pasar a
 * quien tenga sesión iniciada con permiso de editar contenido).
 */
function kdv_maybe_block_rest_for_maintenance( $result ) {
	// Si ya hay un error de autenticación previo (credenciales inválidas,
	// etc.), no lo pisamos con el nuestro.
	if ( ! empty( $result ) ) {
		return $result;
	}
	if ( ! kdv_is_maintenance_mode_active() ) {
		return $result;
	}
	return new WP_Error(
		'kdv_maintenance_mode',
		__( 'El sitio está en modo construcción.', 'revista-koltor-dev' ),
		[ 'status' => 503 ]
	);
}
add_filter( 'rest_authentication_errors', 'kdv_maybe_block_rest_for_maintenance' );

/**
 * Pantalla de "en construcción". No pasa por header.php/footer.php a
 * propósito -- esos cargan menú, buscador, cinta de anuncios, etc., que no
 * pintan nada en un sitio todavía vacío. Sí llama a wp_head()/wp_footer()
 * para no romper analíticas u otros hooks que algo pueda añadir ahí.
 */
function kdv_render_maintenance_page() {
	$logo_id = get_theme_mod( 'custom_logo' );
	$message = get_theme_mod( 'kdv_maintenance_message', __( 'Estamos preparando el sitio. Vuelve pronto.', 'revista-koltor-dev' ) );
	?>
	<!doctype html>
	<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta name="robots" content="noindex, nofollow" />
		<title><?php echo esc_html( get_bloginfo( 'name' ) ); ?></title>
		<?php wp_head(); ?>
	</head>
	<body class="kdv-maintenance">
		<div class="kdv-maintenance__box">
			<div class="kdv-maintenance__text">
				<?php if ( $logo_id ) : ?>
					<div class="kdv-maintenance__logo">
						<?php echo wp_get_attachment_image( $logo_id, 'medium', false, [ 'style' => 'height:' . absint( get_theme_mod( 'kdv_logo_height', 56 ) ) . 'px;width:auto;' ] ); ?>
					</div>
				<?php else : ?>
					<p class="kdv-maintenance__sitename"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
				<?php endif; ?>

				<p class="kdv-maintenance__message"><?php echo esc_html( $message ); ?></p>

				<?php if ( function_exists( 'kdv_get_social_links' ) && kdv_get_social_links() ) : ?>
					<div class="kdv-header-social kdv-maintenance__social">
						<?php kdv_render_social_links(); ?>
					</div>
				<?php endif; ?>
			</div>

			<?php
			/*
			 * Ilustración decorativa, opcional -- puramente visual: nunca
			 * lleva el texto del anuncio ni el nombre del sitio, que son
			 * siempre HTML real (ver arriba) para que el campo "Mensaje"
			 * del Personalizador siga sirviendo para algo y un lector de
			 * pantalla pueda leerlo.
			 *
			 * Prioridad: imagen subida en Personalizar → Modo construcción
			 * -> si no hay ninguna, la incluida en el tema -> si tampoco
			 * existe (por ejemplo, al reutilizar esta base para otro sitio
			 * sin sustituirla todavía), no se muestra nada, nunca un icono
			 * roto.
			 */
			$illustration = get_theme_mod( 'kdv_maintenance_illustration', '' );
			$illustration_dims = '';
			if ( ! $illustration ) {
				$fallback_path = KDV_THEME_DIR . '/assets/images/maintenance-illustration.png';
				if ( file_exists( $fallback_path ) ) {
					$illustration = KDV_ASSETS_URL . '/images/maintenance-illustration.png';
					// Dimensiones conocidas solo para el respaldo del tema --
					// una imagen subida por el usuario puede ser cualquier
					// tamaño, así que ahí se deja que el navegador la mida.
					$illustration_dims = ' width="425" height="407"';
				}
			}
			if ( $illustration ) :
				?>
				<div class="kdv-maintenance__illustration">
					<img src="<?php echo esc_url( $illustration ); ?>" alt="" role="presentation"<?php echo $illustration_dims; // phpcs:ignore WordPress.Security.EscapeOutput -- cadena literal fija, ver arriba. ?> />
				</div>
			<?php endif; ?>
		</div>
		<?php wp_footer(); ?>
	</body>
	</html>
	<?php
}

/**
 * Aviso en el escritorio para quien tenga capacidad de administrar el
 * sitio: el modo construcción es fácil de olvidar encendido después de
 * lanzar. No usa is_admin() a secas para no repetirlo en cada pantalla del
 * escritorio salvo que de verdad haga falta -- aquí sí conviene que se vea
 * en todas, es justo el tipo de aviso que no debe pasar desapercibido.
 */
function kdv_maintenance_mode_admin_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( ! get_theme_mod( 'kdv_maintenance_enabled', false ) ) {
		return;
	}
	printf(
		'<div class="notice notice-warning"><p>%s</p></div>',
		wp_kses(
			sprintf(
				/* translators: %s: enlace a la sección del Personalizador donde se apaga el modo construcción. */
				__( '<strong>Modo construcción activado.</strong> El sitio muestra una pantalla de "en construcción" a cualquier visitante que no tenga sesión iniciada. Desactívalo desde %s cuando esté listo para publicarse.', 'revista-koltor-dev' ),
				'<a href="' . esc_url( admin_url( 'customize.php?autofocus[section]=kdv_section_maintenance' ) ) . '">' . esc_html__( 'Personalizar → Modo construcción', 'revista-koltor-dev' ) . '</a>'
			),
			[ 'strong' => [], 'a' => [ 'href' => [] ] ]
		)
	);
}
add_action( 'admin_notices', 'kdv_maintenance_mode_admin_notice' );
