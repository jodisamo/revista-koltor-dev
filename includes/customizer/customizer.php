<?php
/**
 * Native WordPress Customizer integration — no Kirki, no third-party
 * customizer frameworks. Just core WP_Customize_Manager + selective
 * refresh, so every option here shows the familiar blue-pencil live-edit
 * icons in Apariencia → Personalizar without adding a single dependency.
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function kdv_customize_register( WP_Customize_Manager $wp_customize ) {

	// Enable selective refresh for core Site Identity (title/tagline/logo)
	// — this is what makes the pencil icons appear over those elements.
	$wp_customize->selective_refresh->add_partial( 'blogname', [
		'selector'        => '.kdv-logo-text, .kdv-site-title',
		'render_callback' => function() {
			bloginfo( 'name' );
		},
	] );
	$wp_customize->selective_refresh->add_partial( 'blogdescription', [
		'selector'        => '.kdv-site-tagline',
		'render_callback' => function() {
			bloginfo( 'description' );
		},
	] );

	/* ---------------------------------------------------------------
	 * Panel: Revista Koltor Dev
	 * ------------------------------------------------------------- */
	$wp_customize->add_panel( 'kdv_panel', [
		'title'       => __( 'Revista Koltor Dev', 'revista-koltor-dev' ),
		'description' => __( 'Todas las opciones de marca, colores, tipografía, cabecera, redes sociales, publicidad, portada y pie de página del tema.', 'revista-koltor-dev' ),
		'priority'    => 30,
	] );

	/* ---------------------------------------------------------------
	 * Section: Modo construcción
	 *
	 * 'priority' bajo a propósito, para que aparezca siempre arriba del
	 * todo del panel -- es el ajuste que más importa mientras el sitio
	 * está vacío, y el que hay que recordar apagar al lanzar.
	 * ------------------------------------------------------------- */
	$wp_customize->add_section( 'kdv_section_maintenance', [
		'title'       => __( 'Modo construcción', 'revista-koltor-dev' ),
		'description' => __( 'Muestra una pantalla simple de "en construcción" a cualquier visitante mientras preparas el sitio. Quien tenga sesión iniciada con permiso de editar contenido sigue viendo el sitio real -- así el equipo puede trabajar en él sin apagar esto.', 'revista-koltor-dev' ),
		'panel'       => 'kdv_panel',
		'priority'    => 1,
	] );

	$wp_customize->add_setting( 'kdv_maintenance_enabled', [
		'default'           => false,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_maintenance_enabled', [
		'label'   => __( 'Activar modo construcción', 'revista-koltor-dev' ),
		'section' => 'kdv_section_maintenance',
		'type'    => 'checkbox',
	] );

	$wp_customize->add_setting( 'kdv_maintenance_message', [
		'default'           => __( 'Estamos preparando el sitio. Vuelve pronto.', 'revista-koltor-dev' ),
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_maintenance_message', [
		'label'   => __( 'Mensaje', 'revista-koltor-dev' ),
		'section' => 'kdv_section_maintenance',
		'type'    => 'text',
	] );

	$wp_customize->add_setting( 'kdv_maintenance_illustration', [
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'kdv_maintenance_illustration', [
		'label'       => __( 'Ilustración (opcional)', 'revista-koltor-dev' ),
		'description' => __( 'Si no subes ninguna, se usa la ilustración incluida en el tema. Es puramente decorativa -- el texto de al lado siempre es el mensaje de arriba, nunca texto dentro de la imagen.', 'revista-koltor-dev' ),
		'section'     => 'kdv_section_maintenance',
	] ) );

	/* ---------------------------------------------------------------
	 * Section: Colores
	 * ------------------------------------------------------------- */
	$wp_customize->add_section( 'kdv_section_colors', [
		'title' => __( 'Colores de marca', 'revista-koltor-dev' ),
		'panel' => 'kdv_panel',
	] );

	$color_fields = [
		'kdv_color_primary'   => [ __( 'Color primario', 'revista-koltor-dev' ), '#2E9BD6' ],
		'kdv_color_secondary' => [ __( 'Color secundario (Turquesa)', 'revista-koltor-dev' ), '#2BB3C0' ],
		'kdv_color_accent'    => [ __( 'Color de acento (badges, scores)', 'revista-koltor-dev' ), '#F5A623' ],
	];

	foreach ( $color_fields as $id => $field ) {
		[ $label, $default ] = $field;
		$wp_customize->add_setting( $id, [
			'default'           => $default,
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage',
		] );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $id, [
			'label'   => $label,
			'section' => 'kdv_section_colors',
		] ) );

		// Note: colour settings use 'postMessage' transport, live-updated by
		// assets/js/customizer-preview.js (CSS custom properties), so no
		// selective_refresh partial is registered here — the two mechanisms
		// are alternatives, not complements, and mixing them on the same
		// setting would fight over how the preview updates.
	}

	$wp_customize->add_setting( 'kdv_dark_mode_default', [
		'default'           => 'auto',
		'sanitize_callback' => 'kdv_sanitize_dark_mode_choice',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_dark_mode_default', [
		'label'   => __( 'Modo oscuro por defecto', 'revista-koltor-dev' ),
		'section' => 'kdv_section_colors',
		'type'    => 'select',
		'choices' => [
			'auto'  => __( 'Automático (según el sistema del visitante)', 'revista-koltor-dev' ),
			'light' => __( 'Siempre claro', 'revista-koltor-dev' ),
			'dark'  => __( 'Siempre oscuro', 'revista-koltor-dev' ),
		],
	] );

	/* ---------------------------------------------------------------
	 * Section: Tipografía
	 * ------------------------------------------------------------- */
	$wp_customize->add_section( 'kdv_section_typography', [
		'title'       => __( 'Tipografía', 'revista-koltor-dev' ),
		'panel'       => 'kdv_panel',
		'description' => __( 'Combinaciones de fuente ya emparejadas y probadas — cambia el estilo general del sitio sin arriesgar a mezclar fuentes que no combinan.', 'revista-koltor-dev' ),
	] );

	$typography_choices = [];
	foreach ( kdv_typography_pairings() as $key => $pairing ) {
		$typography_choices[ $key ] = $pairing['label'];
	}

	$wp_customize->add_setting( 'kdv_typography_pairing', [
		'default'           => 'redonda',
		'sanitize_callback' => function( $value ) {
			return array_key_exists( $value, kdv_typography_pairings() ) ? $value : 'redonda';
		},
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_typography_pairing', [
		'label'   => __( 'Combinación de fuentes', 'revista-koltor-dev' ),
		'section' => 'kdv_section_typography',
		'type'    => 'select',
		'choices' => $typography_choices,
	] );

	/* ---------------------------------------------------------------
	 * Section: Header
	 * ------------------------------------------------------------- */
	/* ---------------------------------------------------------------
	 * Section: Cinta de anuncios
	 *
	 * El encendido/apagado y la velocidad viven aquí; la lista de
	 * anuncios en sí (texto, fecha, enlace) se gestiona como contenido
	 * en el CPT "Cinta de anuncios" (ver includes/core/cpt-ticker.php),
	 * el mismo reparto de responsabilidades que Diapositivas/Hero.
	 * ------------------------------------------------------------- */
	$wp_customize->add_section( 'kdv_section_ticker', [
		'title'       => __( 'Cinta de anuncios', 'revista-koltor-dev' ),
		'description' => __( 'Franja horizontal con desplazamiento continuo, encima de la cabecera, para anuncios de próximos eventos (Nintendo Direct, State of Play...). Los anuncios en sí se añaden desde el menú "Cinta de anuncios" del escritorio, no aquí.', 'revista-koltor-dev' ),
		'panel'       => 'kdv_panel',
	] );

	$wp_customize->add_setting( 'kdv_ticker_enabled', [
		'default'           => false,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_ticker_enabled', [
		'label'   => __( 'Mostrar la cinta de anuncios', 'revista-koltor-dev' ),
		'section' => 'kdv_section_ticker',
		'type'    => 'checkbox',
	] );

	$wp_customize->add_setting( 'kdv_ticker_speed', [
		'default'           => 25,
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_ticker_speed', [
		'label'       => __( 'Velocidad (segundos por vuelta completa)', 'revista-koltor-dev' ),
		'description' => __( 'Un número más alto = desplazamiento más lento.', 'revista-koltor-dev' ),
		'section'     => 'kdv_section_ticker',
		'type'        => 'number',
		'input_attrs' => [ 'min' => 10, 'max' => 60 ],
	] );

	$wp_customize->add_section( 'kdv_section_header', [
		'title' => __( 'Cabecera', 'revista-koltor-dev' ),
		'panel' => 'kdv_panel',
	] );

	/* ---------------------------------------------------------------
	 * Section: Menús y efectos
	 *
	 * Todo lo que controla cómo se ve y reacciona la navegación: el
	 * estilo de resaltado del menú principal, el resalte al pasar el
	 * ratón (color, velocidad) y la barra de plataformas (fondo, efecto
	 * del icono), más la animación de los desplegables. Los cuatro ajustes
	 * de menú vivían antes en "Cabecera": se movieron aquí conservando sus
	 * IDs, así que lo ya guardado se mantiene. Acceso directo desde el
	 * escritorio: Revista Koltor Dev → Menús y efectos.
	 * ------------------------------------------------------------- */
	$wp_customize->add_section( 'kdv_section_menus', [
		'title'       => __( 'Menús y efectos', 'revista-koltor-dev' ),
		'description' => __( 'Cómo se resaltan el menú principal y la barra de plataformas al pasar el ratón (o al llegar con el teclado). Los cambios de color y velocidad se ven al instante en la vista previa: pasa el ratón por el menú para probarlos.', 'revista-koltor-dev' ),
		'panel'       => 'kdv_panel',
	] );

	$wp_customize->add_setting( 'kdv_hover_color', [
		'default'           => '',
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'postMessage',
	] );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'kdv_hover_color', [
		'label'       => __( 'Color del resalte', 'revista-koltor-dev' ),
		'description' => __( 'Color del texto, del subrayado y del fondo al pasar el ratón. Vacío = el color primario del tema.', 'revista-koltor-dev' ),
		'section'     => 'kdv_section_menus',
	] ) );

	$wp_customize->add_setting( 'kdv_hover_speed', [
		'default'           => 200,
		'sanitize_callback' => function( $value ) {
			return min( 600, absint( $value ) );
		},
		'transport'         => 'postMessage',
	] );
	$wp_customize->add_control( 'kdv_hover_speed', [
		'label'       => __( 'Velocidad del resalte (ms)', 'revista-koltor-dev' ),
		'description' => __( 'Cuánto tarda el cambio de color y el efecto del icono. 0 = instantáneo; 150-250 se siente natural.', 'revista-koltor-dev' ),
		'section'     => 'kdv_section_menus',
		'type'        => 'number',
		'input_attrs' => [ 'min' => 0, 'max' => 600, 'step' => 10 ],
	] );

	$wp_customize->add_setting( 'kdv_platform_hover_bg', [
		'default'           => 'suave',
		'sanitize_callback' => function( $value ) {
			return array_key_exists( $value, kdv_platform_hover_mixes() ) ? $value : 'suave';
		},
		'transport'         => 'postMessage',
	] );
	$wp_customize->add_control( 'kdv_platform_hover_bg', [
		'label'       => __( 'Barra de plataformas: fondo al pasar el ratón', 'revista-koltor-dev' ),
		'section'     => 'kdv_section_menus',
		'type'        => 'select',
		'choices'     => [
			'ninguno' => __( 'Sin fondo (solo color)', 'revista-koltor-dev' ),
			'suave'   => __( 'Suave (por defecto)', 'revista-koltor-dev' ),
			'intenso' => __( 'Intenso', 'revista-koltor-dev' ),
		],
	] );

	$wp_customize->add_setting( 'kdv_platform_icon_effect', [
		'default'           => 'lift',
		'sanitize_callback' => function( $value ) {
			return in_array( $value, [ 'lift', 'zoom', 'none' ], true ) ? $value : 'lift';
		},
		'transport'         => 'postMessage',
	] );
	$wp_customize->add_control( 'kdv_platform_icon_effect', [
		'label'       => __( 'Barra de plataformas: efecto del icono', 'revista-koltor-dev' ),
		'description' => __( 'Con "reducir movimiento" activado en el sistema del visitante, el icono no se mueve y solo cambian los colores.', 'revista-koltor-dev' ),
		'section'     => 'kdv_section_menus',
		'type'        => 'select',
		'choices'     => [
			'lift' => __( 'Levantarse (por defecto)', 'revista-koltor-dev' ),
			'zoom' => __( 'Agrandarse', 'revista-koltor-dev' ),
			'none' => __( 'Ninguno', 'revista-koltor-dev' ),
		],
	] );

	$wp_customize->add_setting( 'kdv_menu_style', [
		'default'           => 'underline',
		'sanitize_callback' => function( $value ) {
			return in_array( $value, [ 'color', 'underline', 'pill' ], true ) ? $value : 'underline';
		},
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_menu_style', [
		'label'       => __( 'Estilo de resaltado del menú', 'revista-koltor-dev' ),
		'description' => __( 'Cómo se destacan las categorías del menú principal (color al pasar el cursor / en la sección activa, o subrayado animado, o píldora de color). Solo afecta al nivel superior del menú, no a las subcategorías de los desplegables.', 'revista-koltor-dev' ),
		'section'     => 'kdv_section_menus',
		'type'        => 'select',
		'choices'     => [
			'color'     => __( 'Solo color (clásico)', 'revista-koltor-dev' ),
			'underline' => __( 'Subrayado animado (por defecto)', 'revista-koltor-dev' ),
			'pill'      => __( 'Píldora de color', 'revista-koltor-dev' ),
		],
	] );

	/*
	 * Velocidad, curva de easing y forma de aparecer de los DESPLEGABLES del
	 * menú de escritorio (las subcategorías, no el nivel superior). A
	 * propósito NO hay control por elemento de menú ni afecta al panel
	 * deslizante móvil, que usa clip-path y no transform por el motivo que
	 * explica assets/css/main.css junto a esa regla (ensanchaba la página en
	 * Chrome/Android en el tema hermano) -- introducir aquí una variante que
	 * pudiera acabar usando transform reabriría ese mismo bug.
	 */
	$wp_customize->add_setting( 'kdv_menu_transition_speed', [
		'default'           => 200,
		'sanitize_callback' => function( $value ) {
			$value = absint( $value );
			return max( 100, min( 500, $value ) );
		},
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_menu_transition_speed', [
		'label'       => __( 'Velocidad de los desplegables del menú (ms)', 'revista-koltor-dev' ),
		'description' => __( 'Duración de la animación al abrir/cerrar una subcategoría en el menú de escritorio. Se ignora automáticamente si el visitante tiene activado "reducir movimiento" en su sistema.', 'revista-koltor-dev' ),
		'section'     => 'kdv_section_menus',
		'type'        => 'number',
		'input_attrs' => [ 'min' => 100, 'max' => 500, 'step' => 10 ],
	] );

	$wp_customize->add_setting( 'kdv_menu_transition_easing', [
		'default'           => 'suave',
		'sanitize_callback' => function( $value ) {
			return in_array( $value, [ 'suave', 'lineal', 'rebote' ], true ) ? $value : 'suave';
		},
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_menu_transition_easing', [
		'label'   => __( 'Curva de la animación', 'revista-koltor-dev' ),
		'section' => 'kdv_section_menus',
		'type'    => 'select',
		'choices' => [
			'suave'  => __( 'Suave (por defecto)', 'revista-koltor-dev' ),
			'lineal' => __( 'Lineal', 'revista-koltor-dev' ),
			'rebote' => __( 'Rebote sutil', 'revista-koltor-dev' ),
		],
	] );

	$wp_customize->add_setting( 'kdv_submenu_reveal', [
		'default'           => 'fade',
		'sanitize_callback' => function( $value ) {
			return in_array( $value, [ 'fade', 'slide' ], true ) ? $value : 'fade';
		},
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_submenu_reveal', [
		'label'       => __( 'Aparición del submenú', 'revista-koltor-dev' ),
		'description' => __( 'Cómo entra la subcategoría: apareciendo en el sitio, o deslizándose ligeramente hacia abajo. Ambas usan solo "opacity" y "transform", las dos propiedades que el navegador puede animar sin recalcular el resto de la página.', 'revista-koltor-dev' ),
		'section'     => 'kdv_section_menus',
		'type'        => 'select',
		'choices'     => [
			'fade'  => __( 'Aparecer', 'revista-koltor-dev' ),
			'slide' => __( 'Deslizar hacia abajo', 'revista-koltor-dev' ),
		],
	] );

	$wp_customize->add_setting( 'kdv_header_show_search', [
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_header_show_search', [
		'label'   => __( 'Mostrar buscador en el header', 'revista-koltor-dev' ),
		'section' => 'kdv_section_header',
		'type'    => 'checkbox',
	] );

	$wp_customize->add_setting( 'kdv_header_show_dark_toggle', [
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_header_show_dark_toggle', [
		'label'   => __( 'Mostrar botón de modo oscuro', 'revista-koltor-dev' ),
		'section' => 'kdv_section_header',
		'type'    => 'checkbox',
	] );

	$wp_customize->add_setting( 'kdv_header_sticky', [
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_header_sticky', [
		'label'       => __( 'Cabecera fija al hacer scroll', 'revista-koltor-dev' ),
		'description' => __( 'Si lo desactivas, la cabecera se desplaza normalmente con el resto de la página.', 'revista-koltor-dev' ),
		'section'     => 'kdv_section_header',
		'type'        => 'checkbox',
	] );

	$wp_customize->add_setting( 'kdv_logo_height', [
		'default'           => 56,
		'sanitize_callback' => function( $value ) {
			$value = absint( $value );
			return max( 24, min( 140, $value ) );
		},
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_logo_height', [
		'label'       => __( 'Tamaño del logo', 'revista-koltor-dev' ),
		'description' => __( 'Altura en píxeles del logo (cabecera y pie de página). Solo afecta al logo subido en Identidad del sitio — si no has subido uno, no hay nada que redimensionar.', 'revista-koltor-dev' ),
		'section'     => 'kdv_section_header',
		'type'        => 'number',
		'input_attrs' => [ 'min' => 24, 'max' => 140, 'step' => 2 ],
	] );

	$wp_customize->add_setting( 'kdv_header_show_social', [
		'default'           => false,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_header_show_social', [
		'label'       => __( 'Mostrar iconos de redes sociales en la cabecera', 'revista-koltor-dev' ),
		'description' => __( 'Desactivado por defecto: junto a un menú con varias categorías, los iconos aprietan la barra superior y la descuadran. El pie de página los sigue mostrando igual, así que no se pierden.', 'revista-koltor-dev' ),
		'section'     => 'kdv_section_header',
		'type'        => 'checkbox',
	] );

	$wp_customize->add_setting( 'kdv_show_header_tagline', [
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_show_header_tagline', [
		'label'       => __( 'Mostrar descripción corta junto al logo', 'revista-koltor-dev' ),
		'description' => __( 'Desactívalo si tu logo ya incluye el nombre de la marca y prefieres no repetir la descripción del sitio en la cabecera. La "Descripción corta" (Ajustes → Generales) se sigue usando igual para el título de la pestaña del navegador y el SEO — esto solo oculta el texto visible junto al logo.', 'revista-koltor-dev' ),
		'section'     => 'kdv_section_header',
		'type'        => 'checkbox',
	] );

	$wp_customize->add_setting( 'kdv_header_logo_align', [
		'default'           => 'left',
		'sanitize_callback' => function( $value ) {
			return in_array( $value, [ 'left', 'center' ], true ) ? $value : 'left';
		},
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_header_logo_align', [
		'label'       => __( 'Posición del logo', 'revista-koltor-dev' ),
		'description' => __( 'Solo cambia el diseño en pantallas de escritorio; en móvil siempre se ve igual.', 'revista-koltor-dev' ),
		'section'     => 'kdv_section_header',
		'type'        => 'select',
		'choices'     => [
			'left'   => __( 'Izquierda (clásico)', 'revista-koltor-dev' ),
			'center' => __( 'Centrado, con menú a un lado', 'revista-koltor-dev' ),
		],
	] );

	$wp_customize->add_setting( 'kdv_show_platform_bar', [
		'default'           => false,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_show_platform_bar', [
		'label'       => __( 'Mostrar barra de plataformas', 'revista-koltor-dev' ),
		'description' => __( 'Fila de iconos (PC, PlayStation, Xbox...) debajo de la cabecera, cada uno enlazando a su archivo. Gestiona las plataformas y sube su icono desde el menú "Plataformas" del escritorio -- si ninguna tiene icono todavía, esta fila no se muestra aunque esté activada.', 'revista-koltor-dev' ),
		'section'     => 'kdv_section_header',
		'type'        => 'checkbox',
	] );

	/* ---------------------------------------------------------------
	 * Section: Redes sociales
	 * (Aparecen automáticamente tanto en la cabecera como en el pie de
	 * página — un solo lugar para configurarlas, sin duplicar campos.)
	 * ------------------------------------------------------------- */
	$wp_customize->add_section( 'kdv_section_social', [
		'title'       => __( 'Redes sociales', 'revista-koltor-dev' ),
		'panel'       => 'kdv_panel',
		'description' => __( 'Pega aquí el enlace a tu perfil de cada red. El icono correcto de cada una se muestra automáticamente — no hace falta configurarlo. Los que dejes vacíos simplemente no aparecen. Se muestran tanto en la cabecera como en el pie de página.', 'revista-koltor-dev' ),
	] );

	$social_platforms = [
		'facebook'  => [ __( 'Facebook', 'revista-koltor-dev' ), 'https://facebook.com/tupagina' ],
		'instagram' => [ __( 'Instagram', 'revista-koltor-dev' ), 'https://instagram.com/tuusuario' ],
		'x'         => [ __( 'X (Twitter)', 'revista-koltor-dev' ), 'https://x.com/tuusuario' ],
		'tiktok'    => [ __( 'TikTok', 'revista-koltor-dev' ), 'https://tiktok.com/@tuusuario' ],
		'youtube'   => [ __( 'YouTube', 'revista-koltor-dev' ), 'https://youtube.com/@tucanal' ],
		'discord'   => [ __( 'Discord', 'revista-koltor-dev' ), 'https://discord.gg/tuinvitacion' ],
		'whatsapp'  => [ __( 'WhatsApp', 'revista-koltor-dev' ), 'https://wa.me/50400000000' ],
	];

	foreach ( $social_platforms as $key => $field ) {
		[ $label, $placeholder ] = $field;
		$setting_id = 'kdv_social_' . $key;

		$wp_customize->add_setting( $setting_id, [
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'postMessage',
		] );
		$wp_customize->add_control( $setting_id, [
			'label'       => $label,
			'section'     => 'kdv_section_social',
			'type'        => 'url',
			'input_attrs' => [ 'placeholder' => $placeholder ],
		] );
		$wp_customize->selective_refresh->add_partial( $setting_id, [
			'selector'        => '.kdv-header-social',
			'render_callback' => 'kdv_render_social_links',
		] );
	}

	$wp_customize->add_setting( 'kdv_header_social_links', [
		'default'           => '',
		'sanitize_callback' => 'sanitize_textarea_field',
		'transport'         => 'postMessage',
	] );
	$wp_customize->add_control( 'kdv_header_social_links', [
		'label'       => __( 'Otras redes (opcional, una por línea: Nombre|URL)', 'revista-koltor-dev' ),
		'description' => __( 'Para cualquier red que no esté en la lista de arriba. Ejemplo: Letterboxd|https://letterboxd.com/koltordev', 'revista-koltor-dev' ),
		'section'     => 'kdv_section_social',
		'type'        => 'textarea',
	] );
	$wp_customize->selective_refresh->add_partial( 'kdv_header_social_links', [
		'selector'        => '.kdv-header-social',
		'render_callback' => 'kdv_render_social_links',
	] );

	$wp_customize->add_setting( 'kdv_show_share_buttons', [
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_show_share_buttons', [
		'label'       => __( 'Mostrar botones de compartir en artículos y reseñas', 'revista-koltor-dev' ),
		'description' => __( 'Facebook, X, WhatsApp, Telegram y copiar enlace.', 'revista-koltor-dev' ),
		'section'     => 'kdv_section_social',
		'type'        => 'checkbox',
	] );

	/* --- Barra flotante de redes (usa los mismos enlaces de arriba) --- */
	$wp_customize->add_setting( 'kdv_social_floating_enabled', [
		'default'           => false,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_social_floating_enabled', [
		'label'       => __( 'Mostrar barra flotante de redes sociales', 'revista-koltor-dev' ),
		'description' => __( 'Una columna fija de iconos pegada a un lateral de la pantalla, con el color de cada red. Usa los mismos enlaces de arriba: los que dejes vacíos no aparecen.', 'revista-koltor-dev' ),
		'section'     => 'kdv_section_social',
		'type'        => 'checkbox',
	] );

	$wp_customize->add_setting( 'kdv_social_floating_position', [
		'default'           => 'right',
		'sanitize_callback' => function( $value ) {
			return in_array( $value, [ 'right', 'left' ], true ) ? $value : 'right';
		},
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_social_floating_position', [
		'label'   => __( 'Lado de la pantalla', 'revista-koltor-dev' ),
		'section' => 'kdv_section_social',
		'type'    => 'select',
		'choices' => [
			'right' => __( 'Derecha', 'revista-koltor-dev' ),
			'left'  => __( 'Izquierda', 'revista-koltor-dev' ),
		],
	] );

	$wp_customize->add_setting( 'kdv_social_floating_mobile', [
		'default'           => false,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_social_floating_mobile', [
		'label'       => __( 'Mostrarla también en móviles', 'revista-koltor-dev' ),
		'description' => __( 'En móvil la barra pasa a ser horizontal, pegada abajo, y el botón "Volver arriba" se sube solo para no quedar encima. Desactivado por defecto: en pantallas pequeñas una barra fija se come espacio de lectura.', 'revista-koltor-dev' ),
		'section'     => 'kdv_section_social',
		'type'        => 'checkbox',
	] );

	/* ---------------------------------------------------------------
	 * Section: Publicidad
	 * (Campos de código confiable: solo un administrador con acceso al
	 * Personalizador puede guardarlos, igual que el panel nativo de "CSS
	 * adicional" de WordPress. Acepta HTML/JS tal cual — necesario para
	 * pegar un snippet de AdSense o de un banner de afiliados.)
	 * ------------------------------------------------------------- */
	$wp_customize->add_section( 'kdv_section_ads', [
		'title'       => __( 'Publicidad', 'revista-koltor-dev' ),
		'panel'       => 'kdv_panel',
		'description' => __( 'Pega aquí tu código de anuncios (Google AdSense, banners de afiliados, etc.) en cada espacio que quieras usar y actívalo con el interruptor. Si lo dejas apagado o vacío, simplemente no se muestra nada.', 'revista-koltor-dev' ),
	] );

	$ad_slots = [
		'header'  => __( 'Banner debajo de la cabecera', 'revista-koltor-dev' ),
		'sidebar' => __( 'Barra lateral', 'revista-koltor-dev' ),
		'content' => __( 'Dentro del artículo (a la mitad)', 'revista-koltor-dev' ),
		'footer'  => __( 'Antes del pie de página', 'revista-koltor-dev' ),
	];

	foreach ( $ad_slots as $slot => $label ) {
		$wp_customize->add_setting( "kdv_ad_{$slot}_enabled", [
			'default'           => false,
			'sanitize_callback' => 'wp_validate_boolean',
			'transport'         => 'refresh',
		] );
		$wp_customize->add_control( "kdv_ad_{$slot}_enabled", [
			/* translators: %s: ad slot location, e.g. "Barra lateral". */
			'label'   => sprintf( __( 'Activar: %s', 'revista-koltor-dev' ), $label ),
			'section' => 'kdv_section_ads',
			'type'    => 'checkbox',
		] );

		$wp_customize->add_setting( "kdv_ad_{$slot}_code", [
			'default'           => '',
			'sanitize_callback' => 'kdv_sanitize_ad_code',
			'transport'         => 'refresh',
		] );
		$wp_customize->add_control( "kdv_ad_{$slot}_code", [
			'label'   => __( 'Código (HTML / JavaScript del anunciante)', 'revista-koltor-dev' ),
			'section' => 'kdv_section_ads',
			'type'    => 'textarea',
		] );
	}

	/* ---------------------------------------------------------------
	 * Section: Portada (Hero)
	 * ------------------------------------------------------------- */
	$wp_customize->add_section( 'kdv_section_hero', [
		'title'       => __( 'Portada (Hero)', 'revista-koltor-dev' ),
		'panel'       => 'kdv_panel',
		'description' => __( 'Si creas una o más Diapositivas (menú "Diapositivas (Hero)" del escritorio), la portada se convierte automáticamente en un slider con esas imágenes. El título, subtítulo e imagen de aquí abajo solo se usan como respaldo mientras no exista ninguna diapositiva.', 'revista-koltor-dev' ),
	] );

	$wp_customize->add_setting( 'kdv_hero_title', [
		'default'           => __( 'Toda la actualidad, en un solo lugar', 'revista-koltor-dev' ),
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'postMessage',
	] );
	$wp_customize->add_control( 'kdv_hero_title', [
		'label'       => __( 'Título de portada (respaldo)', 'revista-koltor-dev' ),
		'description' => __( 'Se usa solo si no hay diapositivas creadas.', 'revista-koltor-dev' ),
		'section'     => 'kdv_section_hero',
		'type'        => 'text',
	] );
	// Note: postMessage transport only, live-updated by
	// assets/js/customizer-preview.js — no selective_refresh partial here,
	// same reasoning as the colour settings above (the two mechanisms would
	// otherwise both fire and race each other on every keystroke).

	$wp_customize->add_setting( 'kdv_hero_subtitle', [
		'default'           => __( 'Reseñas, novedades y análisis cada semana.', 'revista-koltor-dev' ),
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'postMessage',
	] );
	$wp_customize->add_control( 'kdv_hero_subtitle', [
		'label'   => __( 'Subtítulo de portada', 'revista-koltor-dev' ),
		'section' => 'kdv_section_hero',
		'type'    => 'text',
	] );
	// Note: postMessage transport only, live-updated by
	// assets/js/customizer-preview.js — see note above kdv_hero_title.

	$wp_customize->add_setting( 'kdv_hero_background', [
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'kdv_hero_background', [
		'label'       => __( 'Imagen de fondo de portada (respaldo)', 'revista-koltor-dev' ),
		'description' => __( 'Se usa solo si no hay diapositivas creadas.', 'revista-koltor-dev' ),
		'section'     => 'kdv_section_hero',
	] ) );

	// --- Opciones del slider (solo aplican cuando hay Diapositivas creadas) ---

	$wp_customize->add_setting( 'kdv_hero_effect', [
		'default'           => 'fade',
		'sanitize_callback' => function( $value ) {
			return in_array( $value, [ 'fade', 'slide' ], true ) ? $value : 'fade';
		},
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_hero_effect', [
		'label'   => __( 'Efecto de transición del slider', 'revista-koltor-dev' ),
		'section' => 'kdv_section_hero',
		'type'    => 'select',
		'choices' => [
			'fade'  => __( 'Desvanecido (fade)', 'revista-koltor-dev' ),
			'slide' => __( 'Deslizar', 'revista-koltor-dev' ),
		],
	] );

	$wp_customize->add_setting( 'kdv_hero_autoplay', [
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_hero_autoplay', [
		'label'   => __( 'Avance automático', 'revista-koltor-dev' ),
		'section' => 'kdv_section_hero',
		'type'    => 'checkbox',
	] );

	$wp_customize->add_setting( 'kdv_hero_autoplay_speed', [
		'default'           => 5,
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_hero_autoplay_speed', [
		'label'   => __( 'Segundos entre diapositivas', 'revista-koltor-dev' ),
		'section' => 'kdv_section_hero',
		'type'    => 'number',
		'input_attrs' => [ 'min' => 2, 'max' => 20 ],
	] );

	$wp_customize->add_setting( 'kdv_hero_show_arrows', [
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_hero_show_arrows', [
		'label'   => __( 'Mostrar flechas de navegación', 'revista-koltor-dev' ),
		'section' => 'kdv_section_hero',
		'type'    => 'checkbox',
	] );

	$wp_customize->add_setting( 'kdv_hero_show_dots', [
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_hero_show_dots', [
		'label'   => __( 'Mostrar puntos indicadores', 'revista-koltor-dev' ),
		'section' => 'kdv_section_hero',
		'type'    => 'checkbox',
	] );

	$wp_customize->add_setting( 'kdv_hero_height', [
		'default'           => 'normal',
		'sanitize_callback' => function( $value ) {
			return in_array( $value, [ 'compact', 'normal', 'tall' ], true ) ? $value : 'normal';
		},
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_hero_height', [
		'label'   => __( 'Altura de la portada', 'revista-koltor-dev' ),
		'section' => 'kdv_section_hero',
		'type'    => 'select',
		'choices' => [
			'compact' => __( 'Compacta', 'revista-koltor-dev' ),
			'normal'  => __( 'Normal', 'revista-koltor-dev' ),
			'tall'    => __( 'Alta', 'revista-koltor-dev' ),
		],
	] );

	$wp_customize->add_setting( 'kdv_hero_text_align', [
		'default'           => 'center',
		'sanitize_callback' => function( $value ) {
			return in_array( $value, [ 'center', 'left' ], true ) ? $value : 'center';
		},
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_hero_text_align', [
		'label'   => __( 'Alineación del texto', 'revista-koltor-dev' ),
		'section' => 'kdv_section_hero',
		'type'    => 'select',
		'choices' => [
			'center' => __( 'Centrado', 'revista-koltor-dev' ),
			'left'   => __( 'Izquierda', 'revista-koltor-dev' ),
		],
	] );

	$wp_customize->add_setting( 'kdv_hero_overlay_opacity', [
		'default'           => 70,
		'sanitize_callback' => function( $value ) {
			$value = absint( $value );
			return max( 0, min( 90, $value ) );
		},
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_hero_overlay_opacity', [
		'label'       => __( 'Oscurecido de la imagen (solo diapositivas)', 'revista-koltor-dev' ),
		'description' => __( 'Más alto = texto más legible pero imagen más oscura.', 'revista-koltor-dev' ),
		'section'     => 'kdv_section_hero',
		'type'        => 'number',
		'input_attrs' => [ 'min' => 0, 'max' => 90, 'step' => 5 ],
	] );

	/* ---------------------------------------------------------------
	 * Section: Secciones de la portada
	 * ------------------------------------------------------------- */
	$wp_customize->add_section( 'kdv_section_home', [
		'title'       => __( 'Secciones de la portada', 'revista-koltor-dev' ),
		'panel'       => 'kdv_panel',
		'description' => __( 'Qué bloques de contenido aparecen en la página de inicio (el layout de revista), y en qué orden.', 'revista-koltor-dev' ),
	] );

	$wp_customize->add_setting( 'kdv_home_show_popular', [
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_home_show_popular', [
		'label'       => __( 'Mostrar "Populares del mes"', 'revista-koltor-dev' ),
		'description' => __( 'Slider horizontal justo debajo de la portada con lo más leído del mes en curso (según un contador de vistas propio del tema, sin necesidad de Google Analytics). Se reinicia solo cada mes.', 'revista-koltor-dev' ),
		'section'     => 'kdv_section_home',
		'type'        => 'checkbox',
		'priority'    => 5,
	] );
	$wp_customize->add_setting( 'kdv_home_popular_count', [
		'default'           => 8,
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_home_popular_count', [
		'label'       => __( 'Cantidad de artículos en "Populares del mes"', 'revista-koltor-dev' ),
		'section'     => 'kdv_section_home',
		'type'        => 'number',
		'input_attrs' => [ 'min' => 4, 'max' => 12 ],
		'priority'    => 5,
	] );

	$wp_customize->add_setting( 'kdv_home_show_resenas', [
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_home_show_resenas', [
		'label'   => __( '1. Mostrar "Últimas reseñas"', 'revista-koltor-dev' ),
		'section' => 'kdv_section_home',
		'type'    => 'checkbox',
	] );
	$wp_customize->add_setting( 'kdv_home_resenas_count', [
		'default'           => 6,
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_home_resenas_count', [
		'label'       => __( 'Cantidad de reseñas a mostrar', 'revista-koltor-dev' ),
		'section'     => 'kdv_section_home',
		'type'        => 'number',
		'input_attrs' => [ 'min' => 2, 'max' => 12 ],
	] );

	$wp_customize->add_setting( 'kdv_home_show_latest', [
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_home_show_latest', [
		'label'   => __( '2. Mostrar "Últimas noticias"', 'revista-koltor-dev' ),
		'section' => 'kdv_section_home',
		'type'    => 'checkbox',
	] );
	$wp_customize->add_setting( 'kdv_home_latest_count', [
		'default'           => 6,
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_home_latest_count', [
		'label'       => __( 'Cantidad de noticias a mostrar', 'revista-koltor-dev' ),
		'section'     => 'kdv_section_home',
		'type'        => 'number',
		'input_attrs' => [ 'min' => 2, 'max' => 12 ],
	] );

	$categories       = get_categories( [ 'hide_empty' => false ] );
	$category_choices = [ 0 => __( '— Ninguna (ocultar esta sección) —', 'revista-koltor-dev' ) ];
	foreach ( $categories as $category ) {
		$category_choices[ $category->term_id ] = $category->name;
	}

	// Los valores por defecto salen de kdv_get_home_section_default_category()
	// (includes/core/template-tags.php) para que el Personalizador y
	// front-page.php usen exactamente el mismo default — ver el comentario
	// de esa función.
	foreach ( [ 3, 4, 5 ] as $slot_number ) {
		$wp_customize->add_setting( "kdv_home_section_{$slot_number}_category", [
			'default'           => kdv_get_home_section_default_category( $slot_number ),
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		] );
		$wp_customize->add_control( "kdv_home_section_{$slot_number}_category", [
			/* translators: %d: homepage section slot number. */
			'label'   => sprintf( __( '%d. Categoría', 'revista-koltor-dev' ), $slot_number ),
			'section' => 'kdv_section_home',
			'type'    => 'select',
			'choices' => $category_choices,
		] );

		$wp_customize->add_setting( "kdv_home_section_{$slot_number}_count", [
			'default'           => 3,
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		] );
		$wp_customize->add_control( "kdv_home_section_{$slot_number}_count", [
			'label'       => __( 'Cantidad de artículos', 'revista-koltor-dev' ),
			'section'     => 'kdv_section_home',
			'type'        => 'number',
			'input_attrs' => [ 'min' => 2, 'max' => 12 ],
		] );
	}

	/* ---------------------------------------------------------------
	 * Section: Tarjetas de contenido
	 * ------------------------------------------------------------- */
	$wp_customize->add_section( 'kdv_section_cards', [
		'title' => __( 'Tarjetas de contenido', 'revista-koltor-dev' ),
		'panel' => 'kdv_panel',
	] );

	$wp_customize->add_setting( 'kdv_card_excerpt_length', [
		'default'           => 16,
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_card_excerpt_length', [
		'label'       => __( 'Palabras del resumen', 'revista-koltor-dev' ),
		'section'     => 'kdv_section_cards',
		'type'        => 'number',
		'input_attrs' => [ 'min' => 8, 'max' => 40 ],
	] );

	$wp_customize->add_setting( 'kdv_card_show_author_avatar', [
		'default'           => false,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_card_show_author_avatar', [
		'label'   => __( 'Mostrar avatar del autor', 'revista-koltor-dev' ),
		'section' => 'kdv_section_cards',
		'type'    => 'checkbox',
	] );

	$wp_customize->add_setting( 'kdv_card_show_reading_time', [
		'default'           => false,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_card_show_reading_time', [
		'label'   => __( 'Mostrar tiempo de lectura estimado', 'revista-koltor-dev' ),
		'section' => 'kdv_section_cards',
		'type'    => 'checkbox',
	] );

	/* ---------------------------------------------------------------
	 * Section: Footer
	 * ------------------------------------------------------------- */
	/* ---------------------------------------------------------------
	 * Section: Reseñas
	 * ------------------------------------------------------------- */
	$wp_customize->add_section( 'kdv_section_reviews', [
		'title'       => __( 'Reseñas', 'revista-koltor-dev' ),
		'panel'       => 'kdv_panel',
		'description' => __( 'La puntuación de cada reseña se desglosa en cuatro apartados. Aquí decides cómo se llaman, para que el sistema encaje con lo que reseñas: videojuegos, cine, libros, series… Se usan tanto en la ficha de la reseña como en el formulario de edición.', 'revista-koltor-dev' ),
	] );

	$kdv_score_label_defaults = [
		1 => __( 'Jugabilidad', 'revista-koltor-dev' ),
		2 => __( 'Gráficos', 'revista-koltor-dev' ),
		3 => __( 'Sonido', 'revista-koltor-dev' ),
		4 => __( 'Historia', 'revista-koltor-dev' ),
	];
	foreach ( $kdv_score_label_defaults as $kdv_i => $kdv_default ) {
		$wp_customize->add_setting( "kdv_score_label_{$kdv_i}", [
			'default'           => $kdv_default,
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		] );
		$wp_customize->add_control( "kdv_score_label_{$kdv_i}", [
			/* translators: %d: número del apartado de puntuación. */
			'label'   => sprintf( __( 'Apartado %d', 'revista-koltor-dev' ), $kdv_i ),
			'section' => 'kdv_section_reviews',
			'type'    => 'text',
		] );
	}

	$wp_customize->add_section( 'kdv_section_footer', [
		'title'       => __( 'Pie de página', 'revista-koltor-dev' ),
		'panel'       => 'kdv_panel',
		/* translators: se muestra dentro del Personalizador para indicar dónde se editan los textos del pie. */
		'description' => __( 'Aquí se ajusta cómo se ve el pie de página. Los TEXTOS (presentación del sitio, contacto y aviso de propiedad intelectual) se escriben en el escritorio, en "Revista Koltor Dev → Información del sitio", donde hay más espacio para redactarlos.', 'revista-koltor-dev' ),
	] );

	$wp_customize->add_setting( 'kdv_footer_text', [
		/* translators: %1$s is replaced with the current year on the frontend (%year%), %2$s is the site name (Ajustes → Generales). */
		'default'           => sprintf( __( '© %1$s %2$s. Todos los derechos reservados.', 'revista-koltor-dev' ), '%year%', get_bloginfo( 'name' ) ),
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'postMessage',
	] );
	$wp_customize->add_control( 'kdv_footer_text', [
		'label'       => __( 'Texto de copyright', 'revista-koltor-dev' ),
		'description' => __( 'Usa %year% para insertar el año actual automáticamente.', 'revista-koltor-dev' ),
		'section'     => 'kdv_section_footer',
		'type'        => 'text',
	] );
	// Note: postMessage transport only, live-updated by
	// assets/js/customizer-preview.js — see note above kdv_hero_title.

	$wp_customize->add_setting( 'kdv_footer_widgets_enabled', [
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_footer_widgets_enabled', [
		'label'       => __( 'Mostrar widgets en el pie de página', 'revista-koltor-dev' ),
		'description' => __( 'Desactívalo para dejar un pie de página limpio (solo redes sociales, menú de enlaces y aviso de copyright). Los widgets que tengas asignados no se borran: simplemente dejan de mostrarse hasta que vuelvas a activarlo.', 'revista-koltor-dev' ),
		'section'     => 'kdv_section_footer',
		'type'        => 'checkbox',
	] );

	$wp_customize->add_setting( 'kdv_footer_columns', [
		'default'           => 3,
		'sanitize_callback' => function( $value ) {
			$value = absint( $value );
			return in_array( $value, [ 2, 3, 4 ], true ) ? $value : 3;
		},
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_footer_columns', [
		'label'       => __( 'Columnas de widgets en el pie de página', 'revista-koltor-dev' ),
		'description' => __( 'Cuántas zonas de widgets ("Pie de página - Columna 1, 2, 3…") quieres tener disponibles en Apariencia → Widgets. Las que dejes vacías no ocupan espacio. Cada widget se coloca solo en su propia celda de la cuadrícula, así que aunque los pongas todos en la misma zona no se apilan uno debajo de otro.', 'revista-koltor-dev' ),
		'section'     => 'kdv_section_footer',
		'type'        => 'select',
		'choices'     => [
			2 => '2',
			3 => '3',
			4 => '4',
		],
	] );

	$wp_customize->add_setting( 'kdv_footer_newsletter_enabled', [
		'default'           => false,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_footer_newsletter_enabled', [
		'label'   => __( 'Activar bloque de boletín en el pie de página', 'revista-koltor-dev' ),
		'section' => 'kdv_section_footer',
		'type'    => 'checkbox',
	] );

	$wp_customize->add_setting( 'kdv_footer_newsletter_title', [
		'default'           => __( 'Únete al boletín', 'revista-koltor-dev' ),
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_footer_newsletter_title', [
		'label'   => __( 'Título del bloque de boletín', 'revista-koltor-dev' ),
		'section' => 'kdv_section_footer',
		'type'    => 'text',
	] );

	$wp_customize->add_setting( 'kdv_footer_newsletter_code', [
		'default'           => '',
		'sanitize_callback' => 'kdv_sanitize_ad_code',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'kdv_footer_newsletter_code', [
		'label'       => __( 'Código de suscripción (Mailchimp, ConvertKit, etc.)', 'revista-koltor-dev' ),
		'description' => __( 'Pega aquí el formulario embebido que te da tu proveedor de boletín.', 'revista-koltor-dev' ),
		'section'     => 'kdv_section_footer',
		'type'        => 'textarea',
	] );
}
add_action( 'customize_register', 'kdv_customize_register' );

/**
 * Pass-through "sanitizer" for trusted, admin-only raw HTML/JS fields (ad
 * codes, newsletter embed snippets). Nothing is stripped — the same trust
 * model WordPress core uses for its own "CSS adicional" Customizer panel —
 * because the only person who can ever save these settings already has the
 * edit_theme_options capability the Customizer itself requires.
 */
/**
 * Los campos de código (publicidad y boletín) aceptan HTML/JavaScript tal
 * cual, porque para eso existen: hay que poder pegar un snippet de AdSense o
 * el formulario embebido de Mailchimp.
 *
 * El permiso para guardar código sin filtrar es el mismo que usa WordPress
 * en su propio widget "HTML personalizado": quien tiene la capacidad
 * "unfiltered_html" (un administrador de un sitio normal) guarda el código
 * intacto; cualquier otro perfil —un editor con acceso ampliado, un
 * administrador de sitio dentro de una red multisitio, o cualquier instalación
 * con DISALLOW_UNFILTERED_HTML activado— pasa por wp_kses_post(), que elimina
 * <script> y atributos de evento. Así el campo no se convierte en una vía para
 * inyectar JavaScript persistente en todas las páginas del sitio desde una
 * cuenta que no debería poder hacerlo.
 */
function kdv_sanitize_ad_code( $value ) {
	if ( ! is_string( $value ) ) {
		return '';
	}

	return current_user_can( 'unfiltered_html' ) ? $value : wp_kses_post( $value );
}

function kdv_sanitize_dark_mode_choice( $value ) {
	return in_array( $value, [ 'auto', 'light', 'dark' ], true ) ? $value : 'auto';
}

function kdv_render_footer_copyright() {
	$text = get_theme_mod( 'kdv_footer_text', sprintf( __( '© %1$s %2$s. Todos los derechos reservados.', 'revista-koltor-dev' ), '%year%', get_bloginfo( 'name' ) ) );
	echo esc_html( str_replace( '%year%', gmdate( 'Y' ), $text ) );
}

/**
 * Live-preview JS for postMessage-transport settings (colours, hero text,
 * footer text, social links) so changes appear instantly in the preview
 * pane without a full page reload — the "blue pencil" experience.
 */
function kdv_customize_preview_js() {
	wp_enqueue_script(
		'kdv-customizer-preview',
		KDV_ASSETS_URL . '/js/customizer-preview.js',
		[ 'customize-preview' ],
		KDV_THEME_VERSION,
		true
	);

	// Mismos porcentajes que usa el CSS dinámico (una sola fuente).
	wp_localize_script( 'kdv-customizer-preview', 'KdvPreview', [
		'platformHoverMixes' => kdv_platform_hover_mixes(),
	] );
}
add_action( 'customize_preview_init', 'kdv_customize_preview_js' );

/**
 * Customizer controls JS (adds live colour-swatch preview logic to controls
 * panel itself, optional nicety, keeps parity with core behaviour).
 */
function kdv_customize_controls_js() {
	wp_enqueue_script(
		'kdv-customizer-controls',
		KDV_ASSETS_URL . '/js/customizer-controls.js',
		[ 'customize-controls' ],
		KDV_THEME_VERSION,
		true
	);
}
add_action( 'customize_controls_enqueue_scripts', 'kdv_customize_controls_js' );
