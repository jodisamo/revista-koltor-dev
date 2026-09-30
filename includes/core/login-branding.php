<?php
/**
 * Marca propia en la pantalla de acceso (wp-login.php): el logo de
 * Personalizar → Identidad del sitio en lugar de la "W" de WordPress, el
 * nombre del sitio si todavía no hay logo, y el botón "Acceder" con el
 * color primario del tema (Personalizar → Colores).
 *
 * A propósito no hay ningún ajuste nuevo: reutiliza el logo y el color
 * que ya se configuran para el frontend, así que el acceso queda con la
 * marca del sitio sin tener que acordarse de nada más.
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function kdv_login_branding_styles() {
	$primary = sanitize_hex_color( get_theme_mod( 'kdv_color_primary', '#2E9BD6' ) ) ?: '#2E9BD6';
	$logo    = has_custom_logo() ? wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ), 'medium' ) : false;

	$css = '';

	if ( $logo ) {
		// La caja del formulario mide 320px: el logo se ajusta a ese ancho
		// conservando su proporción, con un tope de alto para que un logo
		// muy vertical no empuje el formulario fuera de la pantalla.
		[ $src, $width, $height ] = $logo;
		$box_height = $width ? min( 120, (int) round( 320 * $height / $width ) ) : 84;

		$css .= '.login h1 a{'
			. 'background-image:url(' . esc_url( $src ) . ');'
			. 'background-size:contain;background-position:center;'
			. 'width:100%;max-width:320px;height:' . absint( $box_height ) . 'px;'
			. '}';
	} else {
		// Sin logo: el nombre del sitio como texto, en vez de la "W".
		$css .= '.login h1 a{'
			. 'background:none;width:auto;height:auto;text-indent:0;overflow:visible;'
			. 'font-size:26px;font-weight:700;line-height:1.3;color:#1d2327;text-decoration:none;'
			. '}';
	}

	$css .= '.wp-core-ui .button-primary{background:' . $primary . ';border-color:' . $primary . ';}'
		. '.wp-core-ui .button-primary:hover,.wp-core-ui .button-primary:focus{background:' . $primary . ';border-color:' . $primary . ';filter:brightness(.9);}'
		. '.login #nav a:hover,.login #backtoblog a:hover{color:' . $primary . ';}'
		. '.login input:focus{border-color:' . $primary . ';box-shadow:0 0 0 1px ' . $primary . ';}';

	echo '<style id="kdv-login-branding">' . $css . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- CSS armado arriba con valores ya saneados (esc_url, absint, sanitize_hex_color).
}
add_action( 'login_enqueue_scripts', 'kdv_login_branding_styles' );

// El logo lleva a la portada del sitio, no a wordpress.org.
add_filter( 'login_headerurl', function() {
	return home_url( '/' );
} );

// Texto del enlace del logo: el nombre del sitio (es lo que se ve cuando
// no hay logo y lo que anuncia un lector de pantalla cuando sí lo hay).
add_filter( 'login_headertext', function() {
	return get_bloginfo( 'name' );
} );
