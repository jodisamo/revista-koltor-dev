<?php
/**
 * Elementor / Elementor Pro compatibility.
 *
 * The theme works fully without Elementor (that's the default magazine
 * homepage and templates), but since Elementor Pro is already installed on
 * this site, these hooks make sure the page builder can take over any page
 * — including the homepage, via Ajustes → Lectura → Página estática — with
 * full-width sections that aren't squeezed by the theme's content container.
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * True when Elementor (free or Pro) is active AND the given post was
 * actually built with it (as opposed to just being a page on a site that
 * happens to have the plugin installed).
 */
function kdv_is_built_with_elementor( $post_id ) {
	if ( ! did_action( 'elementor/loaded' ) || ! class_exists( '\Elementor\Plugin' ) ) {
		return false;
	}

	$document = \Elementor\Plugin::$instance->documents->get( $post_id );

	return $document && $document->is_built_with_elementor();
}

/**
 * Elementor uses the global $content_width to size embeds/images when it
 * doesn't have an explicit value from the theme.
 */
function kdv_set_content_width() {
	if ( ! isset( $GLOBALS['content_width'] ) ) {
		$GLOBALS['content_width'] = 1200;
	}
}
add_action( 'after_setup_theme', 'kdv_set_content_width', 20 );

/*
 * Note: we deliberately do NOT register Elementor "Theme Locations" (the
 * Theme Builder module that lets Elementor replace the header/footer/single
 * template site-wide). Header and footer are meant to stay driven by
 * Apariencia → Personalizar — registering locations would let an Elementor
 * Pro header/footer template silently override that without a clear signal.
 * Editing an individual page's content with Elementor (the normal "Editar
 * con Elementor" button) already works out of the box via the_content(),
 * no location registration needed for that.
 */
