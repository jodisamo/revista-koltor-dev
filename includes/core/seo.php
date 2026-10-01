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
 * Imprime un bloque JSON-LD. wp_json_encode() escapa las barras por defecto,
 * así que nada de los datos (un título con "</script>", por ejemplo) puede
 * cerrar la etiqueta; JSON_UNESCAPED_UNICODE solo evita "ñ" en vez de
 * "ñ" y no afecta a esa protección.
 *
 * @param array $data Datos schema.org.
 */
function kdv_print_json_ld( $data ) {
	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_UNICODE ) . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- JSON-LD, ver arriba.
}

/**
 * La publicación como Organization (nombre, web, logo y redes), con un @id
 * fijo para que los artículos y las reseñas la referencien.
 *
 * @return array
 */
function kdv_schema_publisher() {
	$org = [
		'@type' => 'Organization',
		'@id'   => home_url( '/#organization' ),
		'name'  => get_bloginfo( 'name' ),
		'url'   => home_url( '/' ),
	];
	$logo_id = (int) get_theme_mod( 'custom_logo' );
	if ( $logo_id ) {
		$logo = wp_get_attachment_image_src( $logo_id, 'full' );
		if ( $logo ) {
			$org['logo'] = [
				'@type'  => 'ImageObject',
				'url'    => $logo[0],
				'width'  => (int) $logo[1],
				'height' => (int) $logo[2],
			];
		}
	}
	$same_as = array_values( array_filter( wp_list_pluck( kdv_get_social_links(), 'url' ) ) );
	if ( $same_as ) {
		$org['sameAs'] = $same_as;
	}
	return $org;
}

/**
 * Autor como Person, con su página de autor.
 *
 * @param int $author_id Usuario.
 * @return array
 */
function kdv_schema_author( $author_id ) {
	$name = $author_id ? get_the_author_meta( 'display_name', $author_id ) : '';
	if ( '' === trim( (string) $name ) ) {
		// Sin autor (p. ej. contenido importado): Google exige uno, así que
		// firma la propia publicación en vez de un Person vacío.
		return [
			'@type' => 'Organization',
			'name'  => get_bloginfo( 'name' ),
			'url'   => home_url( '/' ),
		];
	}
	return [
		'@type' => 'Person',
		'name'  => $name,
		'url'   => get_author_posts_url( $author_id ),
	];
}

/**
 * Imágenes de la entrada para Google: la original y la de 1200px. Google
 * pide imágenes de al menos 1200px de ancho para los resultados destacados
 * y Discover.
 *
 * @param int $post_id Entrada.
 * @return string[]
 */
function kdv_schema_images( $post_id ) {
	if ( ! has_post_thumbnail( $post_id ) ) {
		return [];
	}
	$id     = get_post_thumbnail_id( $post_id );
	$images = [];
	foreach ( [ 'full', 'kdv-hero' ] as $size ) {
		$url = wp_get_attachment_image_url( $id, $size );
		if ( $url ) {
			$images[] = $url;
		}
	}
	return array_values( array_unique( $images ) );
}

/**
 * Tipo schema.org de un artículo según su categoría principal (la de más
 * arriba en el árbol): Noticias y Eventos son noticias (NewsArticle),
 * Opinión es OpinionNewsArticle y el resto (Reportajes, Avances…) Article.
 *
 * @param int $post_id Entrada.
 * @return string
 */
function kdv_get_article_schema_type( $post_id ) {
	$slugs = [];
	foreach ( get_the_category( $post_id ) as $cat ) {
		$slugs[] = $cat->slug;
		foreach ( get_ancestors( $cat->term_id, 'category', 'taxonomy' ) as $ancestor_id ) {
			$ancestor = get_term( $ancestor_id, 'category' );
			if ( $ancestor && ! is_wp_error( $ancestor ) ) {
				$slugs[] = $ancestor->slug;
			}
		}
	}
	if ( in_array( 'opinion', $slugs, true ) ) {
		return 'OpinionNewsArticle';
	}
	if ( array_intersect( [ 'noticias', 'eventos' ], $slugs ) ) {
		return 'NewsArticle';
	}
	return 'Article';
}

/**
 * Nombres de las plataformas de una entrada (las concretas: "PS5", no
 * "PlayStation"), para gamePlatform de las reseñas de videojuegos.
 *
 * @param int $post_id Entrada.
 * @return string[]
 */
function kdv_schema_platform_names( $post_id ) {
	$terms = get_the_terms( $post_id, 'kdv_plataforma' );
	return ( $terms && ! is_wp_error( $terms ) ) ? array_values( wp_list_pluck( $terms, 'name' ) ) : [];
}

/**
 * Outputs schema.org "Review" structured data (JSON-LD) for a single
 * Reseña with a final score. Skipped entirely when there's no score yet,
 * since reviewRating is a required field for the Review type — an unscored
 * reseña simply doesn't get a rich result until it's scored. Se imprime
 * también con un plugin de SEO activo: ninguno conoce la nota del tema.
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
	$item_type = kdv_get_review_schema_item_type( $tipo );

	$schema = [
		'@context'      => 'https://schema.org',
		'@type'         => 'Review',
		'@id'           => get_permalink( $post_id ) . '#review',
		'name'          => get_the_title( $post_id ),
		'itemReviewed'  => [
			'@type' => $item_type,
			// "Juego reseñado" de la ficha (el nombre del juego, no el titular
			// del artículo); sin él, el título de la reseña.
			'name'  => trim( (string) get_post_meta( $post_id, '_kdv_item_name', true ) ) ?: wp_strip_all_tags( get_the_title( $post_id ) ),
		],
		'reviewRating'  => [
			'@type'       => 'Rating',
			'ratingValue' => $score,
			'bestRating'  => 10,
			'worstRating' => 0,
		],
		'author'        => kdv_schema_author( $author_id ),
		'publisher'     => kdv_schema_publisher(),
		'datePublished' => get_the_date( 'c', $post_id ),
		'dateModified'  => get_the_modified_date( 'c', $post_id ),
		'url'           => get_permalink( $post_id ),
		'inLanguage'    => get_bloginfo( 'language' ),
	];

	$description = has_excerpt( $post_id ) ? get_the_excerpt( $post_id ) : '';
	if ( $description ) {
		$schema['reviewBody'] = wp_strip_all_tags( $description );
	}
	if ( has_post_thumbnail( $post_id ) ) {
		$schema['itemReviewed']['image'] = wp_get_attachment_image_url( get_post_thumbnail_id( $post_id ), 'kdv-square' );
	}
	// En un videojuego, las plataformas reseñadas (PS5, Switch 2…).
	$platforms = kdv_schema_platform_names( $post_id );
	if ( 'VideoGame' === $item_type && $platforms ) {
		$schema['itemReviewed']['gamePlatform'] = $platforms;
	}

	kdv_print_json_ld( $schema );
}
add_action( 'wp_head', 'kdv_render_review_schema' );

/**
 * NewsArticle / Article de cada entrada + sus migas de pan, en un @graph.
 * Es lo que usa Google para Noticias destacadas, Discover y los resultados
 * de artículo (titular, imagen grande, fecha, autor). Se omite si hay un
 * plugin de SEO activo: Yoast, Rank Math y compañía ya lo generan.
 */
function kdv_render_article_schema() {
	if ( ! is_singular( 'post' ) || kdv_seo_plugin_active() ) {
		return;
	}
	$post_id   = get_queried_object_id();
	$url       = get_permalink( $post_id );
	$author_id = (int) get_post_field( 'post_author', $post_id );
	$headline  = wp_strip_all_tags( get_the_title( $post_id ) );

	$article = [
		'@type'            => kdv_get_article_schema_type( $post_id ),
		'@id'              => $url . '#article',
		// Google recorta los titulares de más de 110 caracteres.
		'headline'         => mb_strlen( $headline ) > 110 ? mb_substr( $headline, 0, 109 ) . '…' : $headline,
		'mainEntityOfPage' => $url,
		'url'              => $url,
		'datePublished'    => get_the_date( 'c', $post_id ),
		'dateModified'     => get_the_modified_date( 'c', $post_id ),
		'author'           => [ kdv_schema_author( $author_id ) ],
		'publisher'        => [ '@id' => home_url( '/#organization' ) ],
		'inLanguage'       => get_bloginfo( 'language' ),
	];

	$images = kdv_schema_images( $post_id );
	if ( $images ) {
		$article['image'] = $images;
	}
	$description = kdv_get_seo_description();
	if ( $description ) {
		$article['description'] = $description;
	}
	$categories = get_the_category( $post_id );
	if ( $categories ) {
		$article['articleSection'] = $categories[0]->name;
	}
	$tags = get_the_tags( $post_id );
	if ( $tags && ! is_wp_error( $tags ) ) {
		$article['keywords'] = implode( ', ', wp_list_pluck( $tags, 'name' ) );
	}
	$platforms = kdv_schema_platform_names( $post_id );
	if ( $platforms ) {
		// Las plataformas de las que trata, como temas del artículo.
		$article['about'] = array_map( function( $name ) {
			return [ '@type' => 'Thing', 'name' => $name ];
		}, $platforms );
	}

	// Migas de pan: Inicio › Categoría (con su padre, si lo tiene) › Artículo.
	$crumbs = [ [ get_bloginfo( 'name' ), home_url( '/' ) ] ];
	if ( $categories ) {
		$trail = array_reverse( get_ancestors( $categories[0]->term_id, 'category', 'taxonomy' ) );
		$trail[] = $categories[0]->term_id;
		foreach ( $trail as $term_id ) {
			$crumbs[] = [ get_cat_name( $term_id ), get_category_link( $term_id ) ];
		}
	}
	$crumbs[] = [ $headline, $url ];
	$breadcrumb = [
		'@type'           => 'BreadcrumbList',
		'@id'             => $url . '#breadcrumb',
		'itemListElement' => [],
	];
	foreach ( $crumbs as $i => [ $name, $item ] ) {
		$breadcrumb['itemListElement'][] = [
			'@type'    => 'ListItem',
			'position' => $i + 1,
			'name'     => $name,
			'item'     => $item,
		];
	}

	kdv_print_json_ld( [
		'@context' => 'https://schema.org',
		'@graph'   => [ $article, $breadcrumb, kdv_schema_publisher() ],
	] );
}
add_action( 'wp_head', 'kdv_render_article_schema' );

/**
 * En la portada: la publicación (Organization, con logo y redes) y el sitio
 * (WebSite). Alimenta el panel de marca de Google. Se omite con un plugin
 * de SEO activo, que ya lo genera.
 */
function kdv_render_site_schema() {
	if ( ! is_front_page() || kdv_seo_plugin_active() ) {
		return;
	}
	$website = [
		'@type'      => 'WebSite',
		'@id'        => home_url( '/#website' ),
		'name'       => get_bloginfo( 'name' ),
		'url'        => home_url( '/' ),
		'publisher'  => [ '@id' => home_url( '/#organization' ) ],
		'inLanguage' => get_bloginfo( 'language' ),
	];
	$tagline = get_bloginfo( 'description' );
	if ( $tagline ) {
		$website['description'] = $tagline;
	}
	kdv_print_json_ld( [
		'@context' => 'https://schema.org',
		'@graph'   => [ $website, kdv_schema_publisher() ],
	] );
}
add_action( 'wp_head', 'kdv_render_site_schema' );

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
