<?php
/**
 * Theme constants.
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'KDV_THEME_VERSION', wp_get_theme()->get( 'Version' ) );
define( 'KDV_THEME_DIR', get_template_directory() );
define( 'KDV_THEME_URL', get_template_directory_uri() );
define( 'KDV_INCLUDES_DIR', KDV_THEME_DIR . '/includes' );
define( 'KDV_ASSETS_URL', KDV_THEME_URL . '/assets' );
