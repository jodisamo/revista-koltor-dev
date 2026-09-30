<?php
/**
 * Pantalla "Revista Koltor Dev → Información del sitio" en el menú lateral del
 * escritorio.
 *
 * Aquí vive el CONTENIDO del pie de página (texto de presentación, correo
 * de contacto, aviso de propiedad intelectual), separado a propósito del
 * Personalizador, que se queda con la APARIENCIA (colores, logo, columnas).
 * Son textos largos: se escriben mucho mejor en una pantalla de ancho
 * completo que en la columna estrecha del Personalizador, y esta pantalla
 * no carga una vista previa del sitio entero, así que va bastante más
 * ligera en equipos modestos.
 *
 * Todo se guarda en una sola opción de la base de datos (kdv_site_info),
 * usando la Settings API nativa de WordPress — sin librerías ni frameworks
 * de opciones de terceros.
 *
 * Los lectores de estos datos (kdv_get_site_info / kdv_get_site_info_defaults)
 * viven en includes/core/template-tags.php, porque el pie de página los
 * necesita también en el frontend, donde este archivo ni se carga.
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registra el ajuste (una sola opción con todo dentro).
 */
function kdv_register_site_info_setting() {
	register_setting(
		'kdv_site_info_group',
		'kdv_site_info',
		[
			'type'              => 'array',
			'sanitize_callback' => 'kdv_sanitize_site_info',
			'default'           => kdv_get_site_info_defaults(),
		]
	);
}
add_action( 'admin_init', 'kdv_register_site_info_setting' );

/**
 * Limpia lo que llega del formulario.
 *
 * Las casillas de verificación se escriben SIEMPRE de forma explícita
 * (true/false): si solo guardáramos las marcadas, wp_parse_args volvería a
 * aplicar el valor por defecto de las desmarcadas y no habría forma de
 * apagar un bloque.
 *
 * @param mixed $input Datos del formulario.
 * @return array
 */
function kdv_sanitize_site_info( $input ) {
	$input  = is_array( $input ) ? $input : [];
	$output = [];

	foreach ( [ 'show_about', 'show_explore', 'show_legal', 'show_contact' ] as $flag ) {
		$output[ $flag ] = ! empty( $input[ $flag ] );
	}

	foreach ( [ 'about_title', 'explore_title', 'legal_title', 'contact_title' ] as $title ) {
		$output[ $title ] = isset( $input[ $title ] ) ? sanitize_text_field( $input[ $title ] ) : '';
	}

	foreach ( [ 'about_text', 'contact_text' ] as $textarea ) {
		$output[ $textarea ] = isset( $input[ $textarea ] ) ? sanitize_textarea_field( $input[ $textarea ] ) : '';
	}

	$output['contact_email'] = isset( $input['contact_email'] ) ? sanitize_email( $input['contact_email'] ) : '';

	// Una sola línea: los saltos de línea se convierten en espacios.
	$output['credits'] = isset( $input['credits'] )
		? trim( preg_replace( '/\s+/', ' ', wp_kses( wp_unslash( $input['credits'] ), kdv_get_credits_allowed_html() ) ) )
		: '';

	return $output;
}

/**
 * Añade "Revista Koltor Dev" al menú lateral del escritorio.
 */
function kdv_register_site_info_page() {
	add_menu_page(
		__( 'Koltor Dev — Información del sitio', 'revista-koltor-dev' ),
		__( 'Revista Koltor Dev', 'revista-koltor-dev' ),
		'manage_options',
		'kdv-site-info',
		'kdv_render_site_info_page',
		'dashicons-star-filled',
		3
	);

	// El primer submenú repite el slug del menú padre: así la entrada se
	// llama "Información del sitio" en vez de repetir "Revista Koltor Dev".
	add_submenu_page(
		'kdv-site-info',
		__( 'Koltor Dev — Información del sitio', 'revista-koltor-dev' ),
		__( 'Información del sitio', 'revista-koltor-dev' ),
		'manage_options',
		'kdv-site-info',
		'kdv_render_site_info_page'
	);

	/*
	 * Accesos directos: submenús sin pantalla propia cuyo "slug" es la URL
	 * de destino (WordPress la usa tal cual como enlace cuando no hay
	 * función asociada). Llevan al Personalizador ya abierto en la sección
	 * correcta (autofocus) o a las pantallas nativas que más se usan con
	 * este tema, sin tener que pasar por Apariencia.
	 */
	$shortcuts = [
		[ __( 'Personalizar el tema', 'revista-koltor-dev' ), 'edit_theme_options', 'customize.php?autofocus[panel]=kdv_panel' ],
		[ __( 'Menús y efectos', 'revista-koltor-dev' ), 'edit_theme_options', 'customize.php?autofocus[section]=kdv_section_menus' ],
		[ __( 'Menús de navegación', 'revista-koltor-dev' ), 'edit_theme_options', 'nav-menus.php' ],
		[ __( 'Plataformas', 'revista-koltor-dev' ), 'manage_categories', 'edit-tags.php?taxonomy=kdv_plataforma' ],
	];
	foreach ( $shortcuts as [ $label, $capability, $url ] ) {
		add_submenu_page( 'kdv-site-info', $label, $label, $capability, $url );
	}
}
add_action( 'admin_menu', 'kdv_register_site_info_page' );

/**
 * Pinta la pantalla de ajustes.
 */
function kdv_render_site_info_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$info        = kdv_get_site_info();
	$menus_url   = admin_url( 'nav-menus.php' );
	$customize   = admin_url( 'customize.php' );
	$has_legal   = has_nav_menu( 'footer_legal' );
	$has_footer  = has_nav_menu( 'footer' );
	?>
	<div class="wrap kdv-site-info">
		<h1><?php esc_html_e( 'Información del sitio', 'revista-koltor-dev' ); ?></h1>
		<p class="description" style="max-width:820px;font-size:14px;">
			<?php esc_html_e( 'Todo lo que se escribe aquí sale en la franja de información del pie de página, sin necesidad de activar ningún widget. Los colores, el logo y las columnas se siguen ajustando en Apariencia → Personalizar.', 'revista-koltor-dev' ); ?>
		</p>

		<form method="post" action="options.php">
			<?php settings_fields( 'kdv_site_info_group' ); ?>

			<h2 class="title"><?php esc_html_e( 'Sobre el sitio', 'revista-koltor-dev' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Mostrar este bloque', 'revista-koltor-dev' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="kdv_site_info[show_about]" value="1" <?php checked( ! empty( $info['show_about'] ) ); ?>>
							<?php esc_html_e( 'Mostrar la presentación del sitio en el pie de página', 'revista-koltor-dev' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="kdv-about-title"><?php esc_html_e( 'Título', 'revista-koltor-dev' ); ?></label></th>
					<td><input type="text" class="regular-text" id="kdv-about-title" name="kdv_site_info[about_title]" value="<?php echo esc_attr( $info['about_title'] ); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="kdv-about-text"><?php esc_html_e( 'Texto de presentación', 'revista-koltor-dev' ); ?></label></th>
					<td>
						<textarea id="kdv-about-text" name="kdv_site_info[about_text]" rows="5" class="large-text"><?php echo esc_textarea( $info['about_text'] ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Dos o tres frases explicando qué es Revista Koltor Dev y para quién. Es lo primero que lee alguien que llega desde un buscador, y también lo que revisan plataformas de publicidad como Google AdSense para entender de qué trata el sitio.', 'revista-koltor-dev' ); ?></p>
					</td>
				</tr>
			</table>

			<h2 class="title"><?php esc_html_e( 'Explorar', 'revista-koltor-dev' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Mostrar este bloque', 'revista-koltor-dev' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="kdv_site_info[show_explore]" value="1" <?php checked( ! empty( $info['show_explore'] ) ); ?>>
							<?php esc_html_e( 'Mostrar la columna de secciones en el pie de página', 'revista-koltor-dev' ); ?>
						</label>
						<p class="description">
							<?php
							if ( $has_footer ) {
								printf(
									/* translators: %s: enlace a Apariencia → Menús. */
									esc_html__( 'Los enlaces salen del menú asignado a "Menú de pie de página" en %s.', 'revista-koltor-dev' ),
									'<a href="' . esc_url( $menus_url ) . '">' . esc_html__( 'Apariencia → Menús', 'revista-koltor-dev' ) . '</a>'
								);
							} else {
								printf(
									/* translators: %s: enlace a Apariencia → Menús. */
									esc_html__( 'Todavía no hay ningún menú asignado a la ubicación "Menú de pie de página". Créalo en %s y esta columna aparecerá sola.', 'revista-koltor-dev' ),
									'<a href="' . esc_url( $menus_url ) . '">' . esc_html__( 'Apariencia → Menús', 'revista-koltor-dev' ) . '</a>'
								);
							}
							?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="kdv-explore-title"><?php esc_html_e( 'Título', 'revista-koltor-dev' ); ?></label></th>
					<td><input type="text" class="regular-text" id="kdv-explore-title" name="kdv_site_info[explore_title]" value="<?php echo esc_attr( $info['explore_title'] ); ?>"></td>
				</tr>
			</table>

			<h2 class="title"><?php esc_html_e( 'Legal', 'revista-koltor-dev' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Mostrar este bloque', 'revista-koltor-dev' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="kdv_site_info[show_legal]" value="1" <?php checked( ! empty( $info['show_legal'] ) ); ?>>
							<?php esc_html_e( 'Mostrar la columna legal en el pie de página', 'revista-koltor-dev' ); ?>
						</label>
						<p class="description">
							<?php
							printf(
								/* translators: %s: enlace a Apariencia → Menús. */
								esc_html__( 'Esta columna usa su propia ubicación de menú, "Menú legal (pie de página)", para no mezclar las políticas con las secciones del sitio. Crea ahí un menú en %s con Política de Privacidad, Política de Cookies, Términos y Condiciones, Contacto y (si usas un plugin de cookies) Preferencias de cookies.', 'revista-koltor-dev' ),
								'<a href="' . esc_url( $menus_url ) . '">' . esc_html__( 'Apariencia → Menús', 'revista-koltor-dev' ) . '</a>'
							);
							?>
						</p>
						<?php if ( ! $has_legal ) : ?>
							<p class="description" style="color:#996800;">
								<strong><?php esc_html_e( 'Pendiente:', 'revista-koltor-dev' ); ?></strong>
								<?php esc_html_e( 'todavía no has asignado ningún menú a "Menú legal (pie de página)", así que esta columna no se muestra aún.', 'revista-koltor-dev' ); ?>
							</p>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="kdv-legal-title"><?php esc_html_e( 'Título', 'revista-koltor-dev' ); ?></label></th>
					<td><input type="text" class="regular-text" id="kdv-legal-title" name="kdv_site_info[legal_title]" value="<?php echo esc_attr( $info['legal_title'] ); ?>"></td>
				</tr>
			</table>

			<h2 class="title"><?php esc_html_e( 'Contacto', 'revista-koltor-dev' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Mostrar este bloque', 'revista-koltor-dev' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="kdv_site_info[show_contact]" value="1" <?php checked( ! empty( $info['show_contact'] ) ); ?>>
							<?php esc_html_e( 'Mostrar la columna de contacto en el pie de página', 'revista-koltor-dev' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="kdv-contact-title"><?php esc_html_e( 'Título', 'revista-koltor-dev' ); ?></label></th>
					<td><input type="text" class="regular-text" id="kdv-contact-title" name="kdv_site_info[contact_title]" value="<?php echo esc_attr( $info['contact_title'] ); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="kdv-contact-text"><?php esc_html_e( 'Texto', 'revista-koltor-dev' ); ?></label></th>
					<td><textarea id="kdv-contact-text" name="kdv_site_info[contact_text]" rows="3" class="large-text"><?php echo esc_textarea( $info['contact_text'] ); ?></textarea></td>
				</tr>
				<tr>
					<th scope="row"><label for="kdv-contact-email"><?php esc_html_e( 'Correo de contacto', 'revista-koltor-dev' ); ?></label></th>
					<td>
						<input type="email" class="regular-text" id="kdv-contact-email" name="kdv_site_info[contact_email]" value="<?php echo esc_attr( $info['contact_email'] ); ?>" placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>">
						<p class="description"><?php esc_html_e( 'Déjalo vacío si prefieres no publicar el correo y enlazar solo una página de Contacto desde el menú legal — es lo más recomendable para evitar spam automático.', 'revista-koltor-dev' ); ?></p>
					</td>
				</tr>
			</table>

			<h2 class="title"><?php esc_html_e( 'Créditos', 'revista-koltor-dev' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="kdv-credits"><?php esc_html_e( 'Línea de créditos', 'revista-koltor-dev' ); ?></label></th>
					<td>
						<textarea id="kdv-credits" name="kdv_site_info[credits]" rows="2" class="large-text code"><?php echo esc_textarea( $info['credits'] ); ?></textarea>
						<p class="description">
							<?php esc_html_e( 'Se muestra en una línea pequeña bajo el copyright. Úsala para agradecer los recursos que usa el sitio (iconos, fotos, tipografías): muchas licencias gratuitas exigen un enlace al autor. Admite enlaces, por ejemplo:', 'revista-koltor-dev' ); ?>
							<br><code>Iconos de plataformas: &lt;a href="https://icons8.com" target="_blank" rel="noopener"&gt;Icons8&lt;/a&gt;</code>
						</p>
					</td>
				</tr>
			</table>

			<?php submit_button(); ?>
		</form>

		<hr>
		<p class="description">
			<?php
			printf(
				/* translators: %s: enlace al Personalizador. */
				esc_html__( 'Los colores, el logo, el texto de copyright y las columnas del pie de página se ajustan en %s.', 'revista-koltor-dev' ),
				'<a href="' . esc_url( $customize ) . '">' . esc_html__( 'Apariencia → Personalizar', 'revista-koltor-dev' ) . '</a>'
			);
			?>
		</p>
	</div>
	<?php
}
