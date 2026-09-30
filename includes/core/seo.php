<?php
/**
 * Lightweight, SEO-plugin-aware structured data and fallback meta tags.
 *
 * Two independent pieces:
 * 1. Review/AggregateRating schema.org markup for Reseñas (kdv_resena) —
 *    always on, regardless of any SEO plugin, because it's built from the
 *    theme's own custom score field (_kdv_score_final), which a generic SEO
 *    plugin has no way of knowing about. This is what makes the star rating
 *    eligible to show up in Google search results for a review.
 * 2. A minimal Open Graph / Twitter Card / meta description / canonical
 *    fallback — but ONLY when no dedicated SEO plugin (Yoast, Rank Math,
 *    All in One SEO, SEOPress, The SEO Framework) is active. Those plugins
 *    already generate this exact set of tags once configured; having the
 *    theme output its own copies at the same time would create duplicate
 *    meta/og tags, which confuses link-preview crawlers and search engines
 *    instead of helping. The moment a real SEO plugin is active, this
 *    backs off automatically — no setting to remember to turn off.
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Maps the Reseña "tipo" field to the closest schema.org type Google's
 * review rich-result documentation lists as supported for itemReviewed
 * (falls back to the generic "CreativeWork" for anything unrecognised).
 */
function kdv_get_review_schema_item_type( $tipo ) {
	$map = [
		'serie'      => 'TVSeries',
		'pelicula'   => 'Movie',
		'videojuego' => 'VideoGame',
		'libro'      => 'Book',
	];
	return $map[ $tipo ] ?? 'CreativeWork';
}

/**
 * Outputs schema.org "Review" structured data (JSON-LD) for a single
 * Reseña with a final score. Skipped entirely when there's no score yet,
 * since reviewRating is a required field for the Review type — an unscored
 * reseña simply doesn't get a rich result until it's scored.
 */
function kdv_render_review_schema() {
	if ( ! is_singular( 'kdv_resena' ) ) {
		return;
	}
	$post_id = get_the_ID();
	$score   = kdv_get_score( $post_id );
	if ( null === $score ) {
		return;
	}

	$tipo      = get_post_meta( $post_id, '_kdv_tipo', true );
	$author_id = (int) get_post_field( 'post_author', $post_id );

	$schema = [
		'@context'      => 'https://schema.org',
		'@type'         => 'Review',
		'itemReviewed'  => [
			'@type' => kdv_get_review_schema_item_type( $tipo ),
			'name'  => get_the_title( $post_id ),
		],
		'reviewRating'  => [
			'@type'       => 'Rating',
			'ratingValue' => $score,
			'bestRating'  => 10,
			'worstRating' => 0,
		],
		'author'        => [
			'@type' => 'Person',
			'name'  => get_the_author_meta( 'display_name', $author_id ),
		],
		'publisher'     => [
			'@type' => 'Organization',
			'name'  => get_bloginfo( 'name' ),
		],
		'datePublished' => get_the_date( 'c', $post_id ),
		'url'           => get_permalink( $post_id ),
	];

	if ( has_post_thumbnail( $post_id ) ) {
		$schema['itemReviewed']['image'] = wp_get_attachment_image_url( get_post_thumbnail_id( $post_id ), 'kdv-square' );
	}

	echo '<script type="application/ld+json">' . wp_json_encode( $schema ) . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- JSON-LD, not HTML; wp_json_encode() escapes forward slashes by default, so nothing in the data (e.g. a title containing "</script>") can break out of the tag.
}
add_action( 'wp_head', 'kdv_render_review_schema' );

/**
 * True when a known SEO plugin is active. Filterable so a site owner using
 * a plugin not in this list can still tell the theme to back off — just
 * add `add_filter( 'kdv_seo_plugin_active', '__return_true' );` in a child
 * theme or an mu-plugin.
 */
function kdv_seo_plugin_active() {
	$active = defined( 'WPSEO_VERSION' )           // Yoast SEO
		|| class_exists( 'RankMath' )              // Rank Math
		|| function_exists( 'aioseo' )             // All in One SEO
		|| defined( 'SEOPRESS_VERSION' )           // SEOPress
		|| function_exists( 'the_seo_framework' ); // The SEO Framework

	return (bool) apply_filters( 'kdv_seo_plugin_active', $active );
}

/**
 * Best-effort canonical URL for the fallback meta tags — exact permalink on
 * a single post/page/reseña, current request path everywhere else (front
 * page, archives, search...).
 */
function kdv_get_fallback_canonical_url() {
	if ( is_singular() ) {
		return get_permalink();
	}
	global $wp;
	return home_url( trailingslashit( $wp->request ) );
}

/**
 * Short meta description for the current view: the excerpt on a single
 * post/reseña, the site tagline everywhere else.
 */
function kdv_get_seo_description() {
	if ( is_singular() ) {
		$excerpt = get_the_excerpt();
		if ( $excerpt ) {
			return wp_strip_all_tags( wp_trim_words( $excerpt, 30, '…' ) );
		}
	}
	$tagline = get_bloginfo( 'description' );
	return $tagline ? wp_strip_all_tags( $tagline ) : '';
}

/**
 * Best available image URL for link previews: the featured image on a
 * single post/reseña, the portada (hero) background, or the custom logo —
 * in that order. Returns '' (no og:image/twitter:image printed at all)
 * rather than pointing at a placeholder that doesn't represent the page.
 */
function kdv_get_seo_image() {
	if ( is_singular() && has_post_thumbnail() ) {
		return get_the_post_thumbnail_url( get_the_ID(), 'kdv-hero' );
	}
	$hero_bg = get_theme_mod( 'kdv_hero_background', '' );
	if ( $hero_bg ) {
		return $hero_bg;
	}
	if ( has_custom_logo() ) {
		$logo = wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ), 'full' );
		if ( $logo ) {
			return $logo[0];
		}
	}
	return '';
}

/**
 * Fallback Open Graph / Twitter Card / meta description / canonical tags —
 * see the file docblock. Only printed when kdv_seo_plugin_active() is false.
 */
function kdv_render_fallback_seo_meta() {
	if ( kdv_seo_plugin_active() ) {
		return;
	}

	$description = kdv_get_seo_description();
	$image       = kdv_get_seo_image();
	$title       = wp_get_document_title();
	$url         = kdv_get_fallback_canonical_url();
	$type        = is_singular( [ 'post', 'kdv_resena' ] ) ? 'article' : 'website';

	/*
	 * En entradas/páginas/reseñas el canonical ya lo imprime WordPress
	 * (rel_canonical() en wp_head): repetirlo aquí dejaba dos etiquetas
	 * canonical por página. En búsquedas y 404 no se imprime ninguno --
	 * $wp->request viene vacío con ?s=, así que apuntaba a la portada.
	 */
	if ( ! is_singular() && ! is_search() && ! is_404() ) :
		?>
		<link rel="canonical" href="<?php echo esc_url( $url ); ?>" />
		<?php
	endif;
	?>
	<?php if ( $description ) : ?>
		<meta name="description" content="<?php echo esc_attr( $description ); ?>" />
	<?php endif; ?>
	<meta property="og:type" content="<?php echo esc_attr( $type ); ?>" />
	<meta property="og:site_name" content="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
	<meta property="og:title" content="<?php echo esc_attr( $title ); ?>" />
	<?php if ( $description ) : ?>
		<meta property="og:description" content="<?php echo esc_attr( $description ); ?>" />
	<?php endif; ?>
	<meta property="og:url" content="<?php echo esc_url( $url ); ?>" />
	<?php if ( $image ) : ?>
		<meta property="og:image" content="<?php echo esc_url( $image ); ?>" />
	<?php endif; ?>
	<meta name="twitter:card" content="<?php echo esc_attr( $image ? 'summary_large_image' : 'summary' ); ?>" />
	<meta name="twitter:title" content="<?php echo esc_attr( $title ); ?>" />
	<?php if ( $description ) : ?>
		<meta name="twitter:description" content="<?php echo esc_attr( $description ); ?>" />
	<?php endif; ?>
	<?php if ( $image ) : ?>
		<meta name="twitter:image" content="<?php echo esc_url( $image ); ?>" />
	<?php endif; ?>
	<?php
}
add_action( 'wp_head', 'kdv_render_fallback_seo_meta', 1 );
