<?php
/**
 * "Revista Koltor Dev → Barra lateral": estado de la barra lateral y un botón
 * para montar la barra recomendada en un clic.
 *
 * Por qué existe: al instalarse, WordPress llena la barra lateral con sus
 * widgets de bloque por defecto (Buscar, Entradas recientes, Comentarios
 * recientes, Archivos, Categorías), con títulos escritos en el idioma de ese
 * momento -- en un sitio instalado en inglés salen "Recent Posts",
 * "Archives"… para siempre. La barra recomendada usa los widgets del tema,
 * en español, y un anuncio en medio si hay código de publicidad.
 *
 * Nunca borra nada: los widgets que hubiera pasan a "Widgets inactivos"
 * (Apariencia → Widgets), de donde se recuperan arrastrándolos.
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function kdv_register_sidebar_tools_page() {
	add_submenu_page(
		'kdv-site-info',
		__( 'Koltor Dev — Barra lateral', 'revista-koltor-dev' ),
		__( 'Barra lateral', 'revista-koltor-dev' ),
		'edit_theme_options',
		'kdv-sidebar-tools',
		'kdv_render_sidebar_tools_page'
	);
}
// Prioridad 11: detrás de los submenús de site-info-page.php.
add_action( 'admin_menu', 'kdv_register_sidebar_tools_page', 11 );

/**
 * Widgets de la barra recomendada, en orden: [ id_base, ajustes ]. Los
 * bloques se guardan como widget "block" con su contenido.
 *
 * @return array[]
 */
function kdv_get_recommended_sidebar() {
	return [
		[ 'block', [ 'content' => '<!-- wp:search {"label":"Buscar","showLabel":false,"placeholder":"Buscar juegos, noticias, reseñas…","buttonText":"Buscar"} /-->' ] ],
		[ 'kdv_recent_posts', [ 'title' => __( 'Lo último', 'revista-koltor-dev' ), 'number' => 5 ] ],
		[ 'kdv_ad', [ 'source' => 'customizer', 'code' => '', 'show_label' => true ] ],
		[ 'kdv_categories', [ 'title' => __( 'Secciones', 'revista-koltor-dev' ), 'show_count' => false ] ],
		[ 'kdv_recent_comments', [ 'title' => __( 'Comentarios recientes', 'revista-koltor-dev' ), 'number' => 4 ] ],
	];
}

/**
 * Sustituye los widgets de la barra lateral principal por los recomendados.
 * Los anteriores pasan a "Widgets inactivos".
 *
 * @return int Número de widgets que se movieron a inactivos.
 */
function kdv_install_recommended_sidebar() {
	$sidebars = wp_get_sidebars_widgets();
	$previous = $sidebars['sidebar-main'] ?? [];

	$sidebars['wp_inactive_widgets'] = array_merge( $sidebars['wp_inactive_widgets'] ?? [], $previous );
	$sidebars['sidebar-main']        = [];

	foreach ( kdv_get_recommended_sidebar() as [ $id_base, $settings ] ) {
		$option    = 'widget_' . $id_base;
		$instances = get_option( $option, [] );
		if ( ! is_array( $instances ) ) {
			$instances = [];
		}
		// Siguiente número libre (las claves numéricas son las instancias).
		$numbers = array_filter( array_keys( $instances ), 'is_int' );
		$number  = $numbers ? max( $numbers ) + 1 : 2;

		$instances[ $number ]         = $settings;
		$instances['_multiwidget']    = 1;
		update_option( $option, $instances );

		$sidebars['sidebar-main'][] = $id_base . '-' . $number;
	}

	wp_set_sidebars_widgets( $sidebars );
	return count( $previous );
}

/**
 * Acción del botón (admin-post.php): nonce + permiso, y vuelta a la
 * pantalla con el resultado.
 */
function kdv_handle_install_recommended_sidebar() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'No tienes permiso para cambiar los widgets.', 'revista-koltor-dev' ), 403 );
	}
	check_admin_referer( 'kdv_install_recommended_sidebar' );

	$moved = kdv_install_recommended_sidebar();

	wp_safe_redirect( add_query_arg( [ 'page' => 'kdv-sidebar-tools', 'kdv-installed' => 1, 'kdv-moved' => $moved ], admin_url( 'admin.php' ) ) );
	exit;
}
add_action( 'admin_post_kdv_install_recommended_sidebar', 'kdv_handle_install_recommended_sidebar' );

function kdv_render_sidebar_tools_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	global $wp_registered_widgets;
	$current = wp_get_sidebars_widgets()['sidebar-main'] ?? [];
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Barra lateral', 'revista-koltor-dev' ); ?></h1>

		<?php if ( isset( $_GET['kdv-installed'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification -- solo muestra un aviso. ?>
			<div class="notice notice-success is-dismissible"><p>
				<?php
				$kdv_moved = absint( $_GET['kdv-moved'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification
				echo esc_html(
					$kdv_moved
						/* translators: %d: número de widgets movidos a inactivos. */
						? sprintf( _n( 'Barra lateral recomendada lista. %d widget anterior se movió a "Widgets inactivos".', 'Barra lateral recomendada lista. %d widgets anteriores se movieron a "Widgets inactivos".', $kdv_moved, 'revista-koltor-dev' ), $kdv_moved )
						: __( 'Barra lateral recomendada lista.', 'revista-koltor-dev' )
				);
				?>
			</p></div>
		<?php endif; ?>

		<p style="max-width:820px;font-size:14px;">
			<?php esc_html_e( 'La barra lateral aparece junto a los artículos, las reseñas, las categorías y la búsqueda. Qué widgets tiene y en qué orden se edita en Apariencia → Widgets; su aspecto (cajas, títulos, colores, dónde se muestra) en Personalizar → Barra lateral y widgets.', 'revista-koltor-dev' ); ?>
		</p>

		<h2 class="title"><?php esc_html_e( 'Ahora mismo tiene', 'revista-koltor-dev' ); ?></h2>
		<?php if ( $current ) : ?>
			<ol>
				<?php foreach ( $current as $kdv_widget_id ) : ?>
					<li><?php echo esc_html( isset( $wp_registered_widgets[ $kdv_widget_id ] ) ? $wp_registered_widgets[ $kdv_widget_id ]['name'] : $kdv_widget_id ); ?> <code><?php echo esc_html( $kdv_widget_id ); ?></code></li>
				<?php endforeach; ?>
			</ol>
		<?php else : ?>
			<p><?php esc_html_e( 'Ningún widget: la barra lateral no se muestra y el contenido ocupa el centro.', 'revista-koltor-dev' ); ?></p>
		<?php endif; ?>

		<h2 class="title"><?php esc_html_e( 'Barra lateral recomendada', 'revista-koltor-dev' ); ?></h2>
		<p style="max-width:820px;">
			<?php esc_html_e( 'Buscador · Lo último (con miniaturas) · Publicidad (solo si hay código en Personalizar → Publicidad → Barra lateral) · Secciones · Comentarios recientes. Todo en español y con el estilo del tema. Los widgets actuales no se borran: pasan a "Widgets inactivos", de donde puedes recuperarlos.', 'revista-koltor-dev' ); ?>
		</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm(<?php echo esc_attr( wp_json_encode( __( '¿Sustituir la barra lateral actual por la recomendada? Los widgets actuales pasarán a "Widgets inactivos".', 'revista-koltor-dev' ) ) ); ?>);">
			<input type="hidden" name="action" value="kdv_install_recommended_sidebar" />
			<?php wp_nonce_field( 'kdv_install_recommended_sidebar' ); ?>
			<?php submit_button( __( 'Poner la barra lateral recomendada', 'revista-koltor-dev' ), 'primary', 'submit', false ); ?>
		</form>

		<h2 class="title"><?php esc_html_e( 'Accesos', 'revista-koltor-dev' ); ?></h2>
		<p>
			<a class="button" href="<?php echo esc_url( admin_url( 'widgets.php' ) ); ?>"><?php esc_html_e( 'Editar los widgets', 'revista-koltor-dev' ); ?></a>
			<a class="button" href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=kdv_section_sidebar' ) ); ?>"><?php esc_html_e( 'Personalizar el aspecto', 'revista-koltor-dev' ); ?></a>
			<a class="button" href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=kdv_section_ads' ) ); ?>"><?php esc_html_e( 'Código de publicidad', 'revista-koltor-dev' ); ?></a>
		</p>
		<p class="description"><?php esc_html_e( 'Consejo: el widget "Koltor Dev: Publicidad" se puede arrastrar a cualquier posición de la barra lateral y repetir; cada uno puede usar el código de Personalizar o uno propio.', 'revista-koltor-dev' ); ?></p>
	</div>
	<?php
}
