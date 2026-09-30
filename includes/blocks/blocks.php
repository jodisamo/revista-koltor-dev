<?php
/**
 * Gutenberg blocks — plain JS (wp.blocks / wp.element / wp.components),
 * no build step, no @wordpress/scripts required. Dynamic (server-rendered)
 * blocks so the same PHP that powers the classic templates also powers
 * the block output — one source of truth for markup.
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function kdv_register_block_category( $categories ) {
	return array_merge(
		[
			[
				'slug'  => 'revista-koltor-dev',
				'title' => __( 'Revista Koltor Dev', 'revista-koltor-dev' ),
				'icon'  => 'star-filled',
			],
		],
		$categories
	);
}
add_filter( 'block_categories_all', 'kdv_register_block_category' );

function kdv_register_blocks() {

	require_once __DIR__ . '/review-box/render.php';
	require_once __DIR__ . '/review-grid/render.php';

	wp_register_script(
		'kdv-block-review-box',
		KDV_ASSETS_URL . '/js/blocks/review-box.js',
		[ 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-media-utils' ],
		KDV_THEME_VERSION,
		true
	);

	wp_register_script(
		'kdv-block-review-grid',
		KDV_ASSETS_URL . '/js/blocks/review-grid.js',
		[ 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-server-side-render' ],
		KDV_THEME_VERSION,
		true
	);

	register_block_type( __DIR__ . '/review-box', [
		'render_callback' => 'kdv_render_review_box_block',
	] );

	register_block_type( __DIR__ . '/review-grid', [
		'render_callback' => 'kdv_render_review_grid_block',
	] );
}
add_action( 'init', 'kdv_register_blocks' );
