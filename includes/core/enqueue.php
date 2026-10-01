<?php
/**
 * Enqueue styles and scripts.
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function kdv_enqueue_assets() {

	// Tipografías del emparejamiento elegido en Personalizar → Tipografía,
	// servidas desde el propio tema (assets/fonts/, ver kdv_get_fonts_css_url()).
	wp_enqueue_style( 'kdv-fonts', kdv_get_fonts_css_url(), [], KDV_THEME_VERSION );

	// Main stylesheet. El zip de producción trae además main.min.css (lo genera
	// scripts/build-zip.sh, ~40% menos); en el repositorio solo existe el
	// legible. Con SCRIPT_DEBUG se usa siempre el legible, para depurar.
	$kdv_css = ( ! ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) && file_exists( KDV_THEME_DIR . '/assets/css/main.min.css' ) ) ? 'main.min.css' : 'main.css';
	wp_enqueue_style( 'kdv-main', KDV_ASSETS_URL . '/css/' . $kdv_css, [], KDV_THEME_VERSION );

	// Dynamic CSS (colours coming from the Customizer).
	wp_add_inline_style( 'kdv-main', kdv_get_dynamic_css() );

	// Main script (menu toggle, header interactions).
	wp_enqueue_script( 'kdv-main', KDV_ASSETS_URL . '/js/main.js', [], KDV_THEME_VERSION, true );

	// Dark mode toggle script — tiny, dependency-free.
	wp_enqueue_script( 'kdv-dark-mode', KDV_ASSETS_URL . '/js/dark-mode.js', [], KDV_THEME_VERSION, true );

	wp_localize_script( 'kdv-main', 'KdvSettings', [
		'darkModeDefault' => get_theme_mod( 'kdv_dark_mode_default', 'auto' ),
	] );

	// Hero slider (Swiper.js, MIT) — only on the magazine homepage, and only
	// when at least one "Diapositiva" has been published. Keeps the library
	// out of every other page and out of sites that never use the feature.
	if ( is_front_page() && kdv_magazine_front_active() && kdv_get_hero_slides() ) {
		wp_enqueue_style( 'kdv-swiper', KDV_ASSETS_URL . '/lib/swiper/swiper.min.css', [], '11.0.3' );
		wp_enqueue_script( 'kdv-swiper', KDV_ASSETS_URL . '/lib/swiper/swiper.min.js', [], '11.0.3', true );
		wp_enqueue_script( 'kdv-hero-slider', KDV_ASSETS_URL . '/js/hero-slider.js', [ 'kdv-swiper' ], KDV_THEME_VERSION, true );

		wp_localize_script( 'kdv-hero-slider', 'KdvHeroSlider', [
			'effect'        => get_theme_mod( 'kdv_hero_effect', 'fade' ),
			'autoplay'      => (bool) get_theme_mod( 'kdv_hero_autoplay', true ),
			'autoplaySpeed' => absint( get_theme_mod( 'kdv_hero_autoplay_speed', 5 ) ) * 1000,
			'arrows'        => (bool) get_theme_mod( 'kdv_hero_show_arrows', true ),
			'dots'          => (bool) get_theme_mod( 'kdv_hero_show_dots', true ),
			'labelPause'    => __( 'Pausar el pase de diapositivas', 'revista-koltor-dev' ),
			'labelPlay'     => __( 'Reanudar el pase de diapositivas', 'revista-koltor-dev' ),
		] );
	}

	// Threaded comment reply script.
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'kdv_enqueue_assets' );

/**
 * URL del CSS de fuentes de un emparejamiento (por defecto, el activo).
 *
 * Las tipografías se sirven desde el propio tema y no desde Google Fonts:
 * con Google, antes de pintar el texto el navegador tenía que ir a
 * fonts.googleapis.com a por el CSS y de ahí a fonts.gstatic.com a por los
 * archivos -- dos servidores más, dos conexiones nuevas y la IP de cada
 * visitante enviada a Google (un problema conocido de privacidad, RGPD).
 *
 * @param string|null $key Clave del emparejamiento (redonda, elegant, modern).
 * @return string
 */
function kdv_get_fonts_css_url( $key = null ) {
	$pairings = kdv_typography_pairings();
	if ( null === $key || ! isset( $pairings[ $key ] ) ) {
		$key = get_theme_mod( 'kdv_typography_pairing', 'redonda' );
		$key = isset( $pairings[ $key ] ) ? $key : 'redonda';
	}
	return KDV_ASSETS_URL . '/fonts/' . $key . '.css';
}

/**
 * Precarga los archivos de fuente "latin" del emparejamiento activo (uno por
 * familia: títulos y texto), para que el navegador los pida en paralelo con
 * el CSS en vez de descubrirlos después. Se leen del propio CSS de fuentes.
 */
function kdv_preload_fonts() {
	$pairings = kdv_typography_pairings();
	$key      = get_theme_mod( 'kdv_typography_pairing', 'redonda' );
	$key      = isset( $pairings[ $key ] ) ? $key : 'redonda';
	$css_path = KDV_THEME_DIR . '/assets/fonts/' . $key . '.css';
	if ( ! is_readable( $css_path ) ) {
		return;
	}
	$css = (string) file_get_contents( $css_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions -- archivo local del propio tema.

	$files = [];
	if ( preg_match_all( "#/\* latin \*/\s*@font-face\s*\{[^}]*font-family:\s*'([^']+)'[^}]*url\(([^)]+\.woff2)\)#", $css, $m, PREG_SET_ORDER ) ) {
		foreach ( $m as [ , $family, $file ] ) {
			$files[ $family ] = $files[ $family ] ?? $file; // el primero de cada familia
		}
	}
	foreach ( $files as $file ) {
		printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( KDV_ASSETS_URL . '/fonts/' . $file ) );
	}
}
add_action( 'wp_head', 'kdv_preload_fonts', 2 );

/**
 * Block editor gets the same fonts + a lightweight editor stylesheet
 * (registered separately in theme-setup.php via add_editor_style()).
 */
function kdv_enqueue_editor_assets() {
	wp_enqueue_style( 'kdv-editor-fonts', kdv_get_fonts_css_url( 'redonda' ), [], KDV_THEME_VERSION );
}
add_action( 'enqueue_block_editor_assets', 'kdv_enqueue_editor_assets' );

/**
 * Intensidad del fondo al pasar el ratón por la barra de plataformas:
 * opción del Personalizador => % del color del resalte mezclado con
 * transparente (color-mix en main.css). La comparten el Personalizador
 * (lista de opciones), el CSS dinámico y la vista previa en vivo.
 *
 * @return int[]
 */
function kdv_platform_hover_mixes() {
	return [
		'ninguno' => 0,
		'suave'   => 12,
		'intenso' => 24,
	];
}

/**
 * Builds a small block of CSS custom-property overrides from the values the
 * site owner picks in Apariencia → Personalizar. Kept separate from
 * main.css so the stylesheet itself stays fully cacheable.
 */
function kdv_get_dynamic_css() {
	$primary   = get_theme_mod( 'kdv_color_primary', '#2E9BD6' );
	$secondary = get_theme_mod( 'kdv_color_secondary', '#2BB3C0' );
	$accent    = get_theme_mod( 'kdv_color_accent', '#F5A623' );
	$typo      = kdv_get_active_typography();

	$overlay_bottom = absint( get_theme_mod( 'kdv_hero_overlay_opacity', 70 ) ) / 100;
	$overlay_top    = round( $overlay_bottom * 0.35, 2 );

	$logo_height = absint( get_theme_mod( 'kdv_logo_height', 56 ) );

	/*
	 * Curvas de easing curadas para los desplegables del menú -- a
	 * propósito no es un campo de texto libre en el Personalizador: un
	 * cubic-bezier() mal formado ahí produciría un menú "raro" sin pista
	 * de por qué. Tres opciones probadas es más honesto que una infinita
	 * que casi nadie sabe ajustar a mano.
	 */
	$menu_easings = [
		'suave'  => 'cubic-bezier(.25, .1, .25, 1)',
		'lineal' => 'linear',
		'rebote' => 'cubic-bezier(.34, 1.4, .64, 1)',
	];
	$menu_easing_key = get_theme_mod( 'kdv_menu_transition_easing', 'suave' );
	$menu_easing     = $menu_easings[ $menu_easing_key ] ?? $menu_easings['suave'];
	$menu_speed_ms   = absint( get_theme_mod( 'kdv_menu_transition_speed', 200 ) );

	// Resalte al pasar el ratón (Personalizar → Menús y efectos). Sin color
	// propio, el resalte usa el color primario.
	$hover_color    = sanitize_hex_color( get_theme_mod( 'kdv_hover_color', '' ) );
	$hover_speed_ms = min( 600, absint( get_theme_mod( 'kdv_hover_speed', 200 ) ) );
	$hover_mixes    = kdv_platform_hover_mixes();
	$hover_mix      = $hover_mixes[ get_theme_mod( 'kdv_platform_hover_bg', 'suave' ) ] ?? $hover_mixes['suave'];

	$css = ":root {\n";
	$css .= '--kdv-primary: ' . esc_html( $primary ) . ";\n";
	$css .= '--kdv-secondary: ' . esc_html( $secondary ) . ";\n";
	$css .= '--kdv-accent: ' . esc_html( $accent ) . ";\n";
	$css .= '--kdv-font-heading: ' . esc_html( $typo['heading'] ) . ";\n";
	$css .= '--kdv-font-body: ' . esc_html( $typo['body'] ) . ";\n";
	$css .= '--kdv-hero-overlay-top: ' . esc_html( $overlay_top ) . ";\n";
	$css .= '--kdv-hero-overlay-bottom: ' . esc_html( $overlay_bottom ) . ";\n";
	$css .= '--kdv-logo-height: ' . $logo_height . "px;\n";
	$css .= '--kdv-menu-transition-duration: ' . $menu_speed_ms . "ms;\n";
	$css .= '--kdv-menu-transition-easing: ' . esc_html( $menu_easing ) . ";\n";
	if ( $hover_color ) {
		$css .= '--kdv-hover-color: ' . $hover_color . ";\n";
	}
	$css .= '--kdv-hover-duration: ' . $hover_speed_ms . "ms;\n";
	$css .= '--kdv-platform-hover-mix: ' . absint( $hover_mix ) . "%;\n";
	$css .= kdv_get_widget_css_vars();
	$css .= "}\n";

	return $css;
}
