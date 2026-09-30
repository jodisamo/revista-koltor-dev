<?php
/**
 * Reusable template helpers.
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Curated font pairings for Personalizar → Tipografía. Only the frontend
 * stylesheet reads the active pairing (via CSS custom properties set in
 * includes/core/enqueue.php) — the block editor keeps the theme.json
 * defaults, which is an intentional, low-risk simplification.
 */
function kdv_typography_pairings() {
	return [
		'redonda'  => [
			'label'   => __( 'Redonda (Baloo 2 + Noto Sans) — por defecto', 'revista-koltor-dev' ),
			'heading' => "'Baloo 2', 'Segoe UI', sans-serif",
			'body'    => "'Noto Sans', system-ui, sans-serif",
			'google'  => 'family=Baloo+2:wght@500;600;700;800&family=Noto+Sans:wght@400;500;600;700',
		],
		'elegant' => [
			'label'   => __( 'Elegante (Playfair Display + Lora)', 'revista-koltor-dev' ),
			'heading' => "'Playfair Display', Georgia, serif",
			'body'    => "'Lora', Georgia, serif",
			'google'  => 'family=Playfair+Display:wght@600;700;800&family=Lora:wght@400;500;600',
		],
		'modern'  => [
			'label'   => __( 'Moderna (Poppins + Inter)', 'revista-koltor-dev' ),
			'heading' => "'Poppins', 'Segoe UI', sans-serif",
			'body'    => "'Inter', system-ui, sans-serif",
			'google'  => 'family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600;700',
		],
	];
}

/**
 * Returns the currently active pairing's data (heading/body font stacks +
 * Google Fonts query string), falling back to "redonda" for an unknown key.
 */
function kdv_get_active_typography() {
	$pairings = kdv_typography_pairings();
	$key      = get_theme_mod( 'kdv_typography_pairing', 'redonda' );
	return $pairings[ $key ] ?? $pairings['redonda'];
}

/**
 * Rough reading time estimate (Spanish-language average ~200 words/minute),
 * always at least 1 minute so short posts don't show "0 min".
 */
function kdv_get_reading_time( $post_id = null ) {
	$post_id = $post_id ?: get_the_ID();
	$content = get_post_field( 'post_content', $post_id );
	$words   = str_word_count( wp_strip_all_tags( strip_shortcodes( $content ) ) );
	return max( 1, (int) ceil( $words / 200 ) );
}

/**
 * Renders one ad slot (header / sidebar / content / footer) if the site
 * owner enabled it and pasted code into it from Personalizar → Publicidad.
 * The code is trusted, unescaped HTML/JS (e.g. an AdSense or affiliate
 * banner snippet) — same trust model as WordPress core's own "CSS
 * adicional" panel: only users who can already manage Customizer settings
 * (edit_theme_options) can save it.
 */
function kdv_render_ad_slot( $slot ) {
	if ( ! get_theme_mod( "kdv_ad_{$slot}_enabled", false ) ) {
		return;
	}
	$code = trim( get_theme_mod( "kdv_ad_{$slot}_code", '' ) );
	if ( '' === $code ) {
		return;
	}
	echo '<div class="kdv-ad-slot kdv-ad-slot--' . esc_attr( $slot ) . '">';
	echo '<span class="kdv-ad-slot__label">' . esc_html__( 'Publicidad', 'revista-koltor-dev' ) . '</span>';
	echo $code; // phpcs:ignore WordPress.Security.EscapeOutput -- trusted, admin-only raw ad code, see docblock above.
	echo '</div>';
}

/**
 * Inserts the "content" ad slot roughly halfway through a single post/
 * reseña's text — only on the main query, only when that slot is enabled
 * with code in Personalizar → Publicidad. Splits on closing </p> tags,
 * which is how wpautop() (and the block editor) render paragraphs.
 */
function kdv_maybe_insert_content_ad( $content ) {
	if ( is_admin() || ! in_the_loop() || ! is_main_query() || ! is_singular( [ 'post', 'kdv_resena' ] ) ) {
		return $content;
	}
	if ( ! get_theme_mod( 'kdv_ad_content_enabled', false ) ) {
		return $content;
	}
	$code = trim( get_theme_mod( 'kdv_ad_content_code', '' ) );
	if ( '' === $code ) {
		return $content;
	}

	$ad_html = '<div class="kdv-ad-slot kdv-ad-slot--content"><span class="kdv-ad-slot__label">'
		. esc_html__( 'Publicidad', 'revista-koltor-dev' ) . '</span>' . $code . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput -- trusted, admin-only raw ad code.

	$paragraph_count = substr_count( $content, '</p>' );
	if ( $paragraph_count < 1 ) {
		return $content . $ad_html;
	}
	$insert_after = max( 1, (int) ceil( $paragraph_count / 2 ) );

	$paragraphs = explode( '</p>', $content );
	$last_index = count( $paragraphs ) - 1;
	$output     = '';
	foreach ( $paragraphs as $index => $paragraph ) {
		if ( '' === trim( $paragraph ) ) {
			continue;
		}
		// Every fragment except the last one lost its closing tag to
		// explode() and needs it back; the last fragment is whatever came
		// after the final </p> in the original content (usually nothing —
		// but if it's a trailing shortcode or similar, it never had a
		// closing </p> to begin with, so don't invent one).
		$output .= $paragraph . ( $index === $last_index ? '' : '</p>' );
		if ( $index + 1 === $insert_after ) {
			$output .= $ad_html;
		}
	}
	return $output;
}
add_filter( 'the_content', 'kdv_maybe_insert_content_ad' );

/**
 * Resolves a category's term_id by name, or 0 if it doesn't exist yet.
 * Used only to compute sensible Customizer defaults for the configurable
 * homepage sections, matching the categories the readme asks users to
 * create (Novedades, Análisis, Guías) — if they're not there yet,
 * defaulting to 0 just leaves that section slot empty/disabled.
 */
function kdv_get_default_category_id( $name ) {
	$term = get_term_by( 'name', $name, 'category' );
	return ( $term && ! is_wp_error( $term ) ) ? (int) $term->term_id : 0;
}

/**
 * Valores por defecto de la información del sitio.
 *
 * @return array
 */
function kdv_get_site_info_defaults() {
	return [
		'show_about'     => true,
		'about_title'    => __( 'Sobre Revista Koltor Dev', 'revista-koltor-dev' ),
		'about_text'     => __( 'Describe aquí tu publicación en dos o tres frases: de qué trata, para quién es y con qué frecuencia publicas. Este texto aparece en el pie de página.', 'revista-koltor-dev' ),

		'show_explore'   => true,
		'explore_title'  => __( 'Explorar', 'revista-koltor-dev' ),

		'show_legal'     => true,
		'legal_title'    => __( 'Legal', 'revista-koltor-dev' ),

		'show_contact'   => true,
		'contact_title'  => __( 'Contacto', 'revista-koltor-dev' ),
		'contact_text'   => __( '¿Tienes una noticia, una corrección o quieres colaborar con nosotros? Escríbenos.', 'revista-koltor-dev' ),
		'contact_email'  => '',
	];
}

/**
 * Devuelve la información del sitio guardada, ya combinada con los valores
 * por defecto.
 *
 * @param string|null $key     Clave concreta, o null para el array completo.
 * @param mixed       $default Valor a devolver si la clave no existe.
 * @return mixed
 */
function kdv_get_site_info( $key = null, $default = '' ) {
	$stored = get_option( 'kdv_site_info', [] );
	$info   = wp_parse_args( is_array( $stored ) ? $stored : [], kdv_get_site_info_defaults() );

	if ( null === $key ) {
		return $info;
	}

	return array_key_exists( $key, $info ) ? $info[ $key ] : $default;
}

/**
 * Categoría por defecto de cada una de las secciones 3-5 de la portada.
 *
 * Vive aquí (y no dentro del Personalizador) porque tanto el Personalizador
 * como front-page.php tienen que usar EXACTAMENTE el mismo valor por
 * defecto. Si no, pasa justo lo que pasaba antes: WordPress aplica el
 * "default" registrado de un ajuste únicamente dentro de la vista previa
 * del Personalizador, así que las secciones se veían al personalizar pero
 * no en el sitio público mientras no se hubiera guardado el ajuste a mano.
 *
 * @param int $slot Número de sección (3, 4 o 5).
 * @return int ID de la categoría, o 0 si esa categoría no existe.
 */
function kdv_get_home_section_default_category( $slot ) {
	$defaults = [
		3 => 'Novedades',
		4 => 'Análisis',
		5 => 'Guías',
	];
	$slot = absint( $slot );

	return isset( $defaults[ $slot ] ) ? kdv_get_default_category_id( $defaults[ $slot ] ) : 0;
}

/**
 * ID de la categoría configurada para una sección de la portada, ya
 * resuelta contra el valor por defecto correcto. Devuelve 0 cuando la
 * sección está en "Ninguna (ocultar esta sección)".
 *
 * @param int $slot Número de sección (3, 4 o 5).
 * @return int
 */
function kdv_get_home_section_category_id( $slot ) {
	return absint(
		get_theme_mod(
			"kdv_home_section_{$slot}_category",
			kdv_get_home_section_default_category( $slot )
		)
	);
}

/**
 * Returns whether the "magazine" front page layout (front-page.php) is the
 * one actually being rendered, i.e. Ajustes → Lectura is NOT set to a
 * static page. Used to avoid loading the hero slider assets when a static
 * page (possibly built with Elementor) is the real homepage.
 */
function kdv_magazine_front_active() {
	return ! ( 'page' === get_option( 'show_on_front' ) && get_option( 'page_on_front' ) );
}

/**
 * Returns the published "Diapositiva" (kdv_slide) posts for the hero
 * slider, ordered with the native "Atributos de página" order field.
 * Empty array = no slides configured, so the fallback static hero renders.
 *
 * @return WP_Post[]
 */
function kdv_get_hero_slides() {
	return get_posts( [
		'post_type'      => 'kdv_slide',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
		'post_status'    => 'publish',
		'no_found_rows'  => true,
	] );
}

/**
 * Returns the final score (0-10) for a Reseña, or null if none was set.
 */
function kdv_get_score( $post_id ) {
	$score = get_post_meta( $post_id, '_kdv_score_final', true );
	return ( '' === $score ) ? null : (float) $score;
}

/**
 * Maps a 0-10 score to a semantic colour class, matching the traffic-light
 * convention used by most review sites (green = great,
 * yellow = mixed, red = poor).
 */
function kdv_get_score_class( $score ) {
	if ( null === $score ) {
		return 'kdv-score--neutral';
	}
	if ( $score >= 8 ) {
		return 'kdv-score--great';
	}
	if ( $score >= 6 ) {
		return 'kdv-score--good';
	}
	if ( $score >= 4 ) {
		return 'kdv-score--mixed';
	}
	return 'kdv-score--poor';
}

/**
 * Outputs the round score badge used on cards, archive grids and the single
 * template. Rendered as a ring filled proportionally to the score (0-10 →
 * 0-100%, passed as the --kdv-score-pct custom property that assets/css/
 * main.css's conic-gradient reads) instead of a flat coloured border.
 */
function kdv_score_badge( $post_id, $size = 'md' ) {
	$score = kdv_get_score( $post_id );
	if ( null === $score ) {
		return;
	}
	$percent = max( 0, min( 100, $score * 10 ) );
	printf(
		'<span class="kdv-score-badge kdv-score-badge--%1$s %2$s" style="--kdv-score-pct:%3$s" aria-label="%4$s"><span class="kdv-score-badge__value">%5$s</span></span>',
		esc_attr( $size ),
		esc_attr( kdv_get_score_class( $score ) ),
		esc_attr( $percent ),
		/* translators: %s: numeric score out of 10. */
		esc_attr( sprintf( __( 'Puntuación: %s de 10', 'revista-koltor-dev' ), $score ) ),
		esc_html( number_format_i18n( $score, 1 ) )
	);
}

/**
 * Splits a "one item per line" textarea meta value into a clean array.
 */
function kdv_lines_to_array( $text ) {
	if ( empty( $text ) ) {
		return [];
	}
	$lines = preg_split( '/\r\n|\r|\n/', $text );
	$lines = array_map( 'trim', $lines );
	return array_filter( $lines );
}

/**
 * Los cuatro apartados en los que se desglosa la puntuación de una reseña.
 *
 * Son editables desde Personalizar → Reseñas justamente para que el mismo
 * tema sirva igual a una revista de videojuegos (Jugabilidad / Gráficos /
 * Sonido / Historia), de cine o de libros, sin tocar una línea de código.
 *
 * @return string[] Cuatro etiquetas, en orden.
 */
function kdv_get_score_labels() {
	return [
		get_theme_mod( 'kdv_score_label_1', __( 'Historia / Guion', 'revista-koltor-dev' ) ),
		get_theme_mod( 'kdv_score_label_2', __( 'Apartado visual', 'revista-koltor-dev' ) ),
		get_theme_mod( 'kdv_score_label_3', __( 'Sonido', 'revista-koltor-dev' ) ),
		get_theme_mod( 'kdv_score_label_4', __( 'Personajes', 'revista-koltor-dev' ) ),
	];
}

/**
 * Human readable label for the "estado" (status) meta field.
 */
function kdv_get_estado_label( $estado ) {
	$labels = [
		'en_curso' => __( 'En curso', 'revista-koltor-dev' ),
		'finalizado' => __( 'Finalizado', 'revista-koltor-dev' ),
		'anunciado'  => __( 'Anunciado', 'revista-koltor-dev' ),
	];
	return $labels[ $estado ] ?? '';
}

/**
 * Human readable label for the "tipo" (type) meta field.
 */
function kdv_get_tipo_label( $tipo ) {
	$labels = [
		'serie'      => __( 'Serie', 'revista-koltor-dev' ),
		'pelicula'   => __( 'Película', 'revista-koltor-dev' ),
		'videojuego' => __( 'Videojuego', 'revista-koltor-dev' ),
		'libro'      => __( 'Libro', 'revista-koltor-dev' ),
		'otro'       => __( 'Otro', 'revista-koltor-dev' ),
	];
	return $labels[ $tipo ] ?? '';
}

/**
 * Central map of hand-authored 24x24 currentColor SVG paths, one per known
 * platform, plus a generic "link" glyph fallback. Keeping this as a single
 * source of truth means both the dedicated Customizer fields (which know
 * exactly which platform they are) and the freeform "otras redes" field
 * (which has to guess the platform from the URL's domain) always render
 * the same, correct brand glyph — no risk of the wrong icon showing up.
 */
function kdv_social_icon_paths() {
	return [
		'x'         => '<path d="M5 5l14 14M19 5L5 19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
		'instagram' => '<rect x="4" y="4" width="16" height="16" rx="5" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="16.5" cy="7.5" r="1.2" fill="currentColor"/>',
		'facebook'  => '<path d="M14 8.5h2V5.3c-.35-.05-1.5-.15-2.6-.15-2.6 0-4.2 1.6-4.2 4.4V12H6.5v3.2H9V21h3.3v-5.8h2.6l.5-3.2h-3.1V9.9c0-.9.3-1.4 1.7-1.4Z" fill="currentColor"/>',
		'youtube'   => '<rect x="3.5" y="6.5" width="17" height="11" rx="3" fill="none" stroke="currentColor" stroke-width="2"/><path d="M10.5 9.7v4.6l4-2.3-4-2.3Z" fill="currentColor"/>',
		'tiktok'    => '<path d="M14 4v9.2a2.6 2.6 0 1 1-2.2-2.6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M14 4c.3 2 1.8 3.5 3.8 3.8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
		'discord'   => '<rect x="4" y="7" width="16" height="10" rx="5" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="9.5" cy="12" r="1.3" fill="currentColor"/><circle cx="14.5" cy="12" r="1.3" fill="currentColor"/>',
		'whatsapp'  => '<path d="M12 4a8 8 0 0 0-6.9 12l-1 4 4.1-1A8 8 0 1 0 12 4Z" fill="none" stroke="currentColor" stroke-width="2"/><path d="M9 9.5c0 3.5 2 5.5 5.5 5.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
		'telegram'  => '<path d="M21 4 3 11l6 2.5M21 4 14 20l-5-6.5M21 4 9 13.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
		'link'      => '<circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="2"/><path d="M9 12h6M12 9v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
	];
}

/**
 * Returns the inline SVG markup for a known platform key (facebook,
 * instagram, x, youtube, tiktok, discord, whatsapp). Unknown keys fall back
 * to the generic "link" glyph.
 */
function kdv_get_platform_icon_svg( $platform ) {
	$paths = kdv_social_icon_paths();
	$path  = $paths[ $platform ] ?? $paths['link'];
	return '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false">' . $path . '</svg>';
}

/**
 * Returns a small inline SVG icon (24x24, currentColor) for a social URL,
 * guessed from its domain. Used only for the freeform "otras redes" field,
 * where we don't know the platform ahead of time. Falls back to a generic
 * "link" glyph for anything we don't recognise, so unknown platforms still
 * look intentional instead of showing plain text.
 */
function kdv_get_social_icon_svg( $url ) {
	$host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );

	$domain_map = [
		'x.com'         => 'x',
		'twitter.com'   => 'x',
		'instagram.com' => 'instagram',
		'facebook.com'  => 'facebook',
		'youtube.com'   => 'youtube',
		'tiktok.com'    => 'tiktok',
		'discord.com'   => 'discord',
		'discord.gg'    => 'discord',
		'wa.me'         => 'whatsapp',
	];

	$platform = 'link';
	foreach ( $domain_map as $domain => $key ) {
		if ( false !== strpos( $host, $domain ) ) {
			$platform = $key;
			break;
		}
	}

	return kdv_get_platform_icon_svg( $platform );
}

/**
 * Combines the dedicated per-platform Customizer fields (Facebook,
 * Instagram, X, YouTube, TikTok, Discord, WhatsApp — in this fixed order)
 * with any extra entries from the freeform "otras redes" field. Each item
 * is ['url' => ..., 'label' => ..., 'icon' => <svg markup>]. Used to render
 * the exact same list in both the header and the footer.
 */
function kdv_get_social_links() {
	$platforms = [
		'facebook'  => __( 'Facebook', 'revista-koltor-dev' ),
		'instagram' => __( 'Instagram', 'revista-koltor-dev' ),
		'x'         => __( 'X (Twitter)', 'revista-koltor-dev' ),
		'tiktok'    => __( 'TikTok', 'revista-koltor-dev' ),
		'youtube'   => __( 'YouTube', 'revista-koltor-dev' ),
		'discord'   => __( 'Discord', 'revista-koltor-dev' ),
		'whatsapp'  => __( 'WhatsApp', 'revista-koltor-dev' ),
	];

	$links = [];

	foreach ( $platforms as $key => $label ) {
		$url = get_theme_mod( 'kdv_social_' . $key, '' );
		if ( $url ) {
			$links[] = [
				'url'   => $url,
				'label' => $label,
				'key'   => $key,
				'icon'  => kdv_get_platform_icon_svg( $key ),
			];
		}
	}

	$raw = get_theme_mod( 'kdv_header_social_links', '' );
	foreach ( kdv_lines_to_array( $raw ) as $line ) {
		$parts = array_map( 'trim', explode( '|', $line, 2 ) );
		if ( count( $parts ) < 2 || empty( $parts[1] ) ) {
			continue;
		}
		$links[] = [
			'url'   => $parts[1],
			'label' => $parts[0],
			'key'   => 'custom',
			'icon'  => kdv_get_social_icon_svg( $parts[1] ),
		];
	}

	return $links;
}

/**
 * Renders the <ul> of social icons — identical output wherever it's called
 * from (header, footer), and the target of the selective_refresh partials
 * bound to every social Customizer setting.
 */
function kdv_render_social_links() {
	$links = kdv_get_social_links();
	if ( ! $links ) {
		return;
	}
	echo '<ul class="kdv-social-list">';
	foreach ( $links as $link ) {
		$key = isset( $link['key'] ) ? $link['key'] : 'custom';
		printf(
			'<li><a class="kdv-social--%4$s" href="%1$s" target="_blank" rel="noopener noreferrer" aria-label="%2$s">%3$s</a></li>',
			esc_url( $link['url'] ),
			esc_attr( $link['label'] ),
			$link['icon'], // phpcs:ignore -- fixed, hand-authored SVG markup, no user data interpolated.
			esc_attr( $key )
		);
	}
	echo '</ul>';
}

/**
 * Barra flotante de redes sociales, fija en un lateral de la pantalla.
 *
 * Reutiliza los mismos enlaces e iconos SVG de Personalizar → Redes
 * sociales (no hay una segunda lista que mantener), pero se pinta con el
 * color de cada marca. Se activa desde Personalizar → Redes sociales.
 */
function kdv_render_social_floating() {
	if ( ! get_theme_mod( 'kdv_social_floating_enabled', false ) ) {
		return;
	}

	$links = kdv_get_social_links();
	if ( ! $links ) {
		return;
	}

	$position = 'left' === get_theme_mod( 'kdv_social_floating_position', 'right' ) ? 'left' : 'right';
	$classes  = 'kdv-social-floating kdv-social-floating--' . $position;
	if ( get_theme_mod( 'kdv_social_floating_mobile', false ) ) {
		$classes .= ' kdv-social-floating--show-mobile';
	}

	printf(
		'<nav class="%1$s" aria-label="%2$s"><ul class="kdv-social-floating__list">',
		esc_attr( $classes ),
		esc_attr__( 'Redes sociales', 'revista-koltor-dev' )
	);

	foreach ( $links as $link ) {
		$key = isset( $link['key'] ) ? $link['key'] : 'custom';
		printf(
			'<li><a class="kdv-social-floating__link kdv-social--%4$s" href="%1$s" target="_blank" rel="noopener noreferrer" aria-label="%2$s"><span class="kdv-social-floating__icon">%3$s</span><span class="kdv-social-floating__label">%2$s</span></a></li>',
			esc_url( $link['url'] ),
			esc_attr( $link['label'] ),
			$link['icon'], // phpcs:ignore -- ídem: SVG fijo del propio tema.
			esc_attr( $key )
		);
	}

	echo '</ul></nav>';
}

/**
 * Prints the site logo, falling back to the site title as text when no
 * custom logo has been set in Customizer > Site Identity.
 */
function kdv_site_branding() {
	if ( has_custom_logo() ) {
		the_custom_logo();
		return;
	}
	?>
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="kdv-logo-text" rel="home">
		<?php bloginfo( 'name' ); ?>
	</a>
	<?php
}

/**
 * Prints a post's (or Reseña's) tags as small coloured pill links, each one
 * pointing to its tag archive (so visitors can find every other entry that
 * shares that tag). Colours come from a small fixed palette defined in
 * main.css — not from the site owner's Personalizar → Colores — so tags
 * always stay visually harmonious with the theme look even if the brand
 * colours are changed later, and a specific tag always renders in the same
 * colour (picked from its term ID), repeating once there are more tags than
 * palette entries.
 *
 * @param int|null $post_id Post ID. Defaults to the current post in the loop.
 */
function kdv_render_post_tags( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$tags    = get_the_tags( $post_id );

	if ( ! $tags || is_wp_error( $tags ) ) {
		return;
	}

	$palette_size = 8;

	echo '<div class="kdv-tags">';
	foreach ( $tags as $tag ) {
		$color = ( $tag->term_id % $palette_size ) + 1;
		printf(
			'<a href="%1$s" class="kdv-tag kdv-tag--%2$d" rel="tag">%3$s</a>',
			esc_url( get_tag_link( $tag ) ),
			absint( $color ),
			esc_html( $tag->name )
		);
	}
	echo '</div>';
}

/**
 * Share-link targets for a single post/reseña (Facebook, X, WhatsApp,
 * Telegram sharer URLs) — the "copy link" action itself has no URL and is
 * wired up client-side in assets/js/main.js via the data-copy-url attribute
 * on its button, since there's no sharer endpoint to link to.
 */
function kdv_get_share_links( $post_id = null ) {
	$post_id = $post_id ?: get_the_ID();
	$url     = rawurlencode( get_permalink( $post_id ) );
	$title   = rawurlencode( get_the_title( $post_id ) );

	return [
		[
			'key'   => 'facebook',
			'label' => __( 'Compartir en Facebook', 'revista-koltor-dev' ),
			'url'   => "https://www.facebook.com/sharer/sharer.php?u={$url}",
			'icon'  => kdv_get_platform_icon_svg( 'facebook' ),
		],
		[
			'key'   => 'x',
			'label' => __( 'Compartir en X', 'revista-koltor-dev' ),
			'url'   => "https://twitter.com/intent/tweet?url={$url}&text={$title}",
			'icon'  => kdv_get_platform_icon_svg( 'x' ),
		],
		[
			'key'   => 'whatsapp',
			'label' => __( 'Compartir en WhatsApp', 'revista-koltor-dev' ),
			'url'   => "https://wa.me/?text={$title}%20{$url}",
			'icon'  => kdv_get_platform_icon_svg( 'whatsapp' ),
		],
		[
			'key'   => 'telegram',
			'label' => __( 'Compartir en Telegram', 'revista-koltor-dev' ),
			'url'   => "https://t.me/share/url?url={$url}&text={$title}",
			'icon'  => kdv_get_platform_icon_svg( 'telegram' ),
		],
	];
}

/**
 * Renders the "Compartir" row on a single post/reseña: sharer links for
 * Facebook/X/WhatsApp/Telegram plus a "copy link" button (JS in main.js
 * copies the URL from its data-copy-url attribute to the clipboard). Hidden
 * entirely when "Mostrar botones de compartir" is off in Personalizar →
 * Redes sociales.
 */
function kdv_render_share_buttons( $post_id = null ) {
	if ( ! get_theme_mod( 'kdv_show_share_buttons', true ) ) {
		return;
	}
	$post_id = $post_id ?: get_the_ID();
	$links   = kdv_get_share_links( $post_id );
	?>
	<div class="kdv-share-buttons">
		<span class="kdv-share-buttons__label"><?php esc_html_e( 'Compartir:', 'revista-koltor-dev' ); ?></span>
		<ul class="kdv-share-buttons__list">
			<?php foreach ( $links as $link ) : ?>
				<li>
					<a href="<?php echo esc_url( $link['url'] ); ?>" class="kdv-share-btn" target="_blank" rel="noopener noreferrer nofollow" aria-label="<?php echo esc_attr( $link['label'] ); ?>">
						<?php echo $link['icon']; // phpcs:ignore -- fixed, hand-authored SVG markup, no user data interpolated. ?>
					</a>
				</li>
			<?php endforeach; ?>
			<li>
				<button type="button" class="kdv-share-btn kdv-share-btn--copy" data-copy-url="<?php echo esc_url( get_permalink( $post_id ) ); ?>" aria-label="<?php esc_attr_e( 'Copiar enlace', 'revista-koltor-dev' ); ?>">
					<?php echo kdv_get_platform_icon_svg( 'link' ); // phpcs:ignore -- fixed, hand-authored SVG markup, no user data interpolated. ?>
				</button>
			</li>
		</ul>
	</div>
	<?php
}

/**
 * Renders an author bio box (avatar, name, biographical info, website) at
 * the end of a single post/reseña. Pulls straight from the author's native
 * WordPress profile fields (Usuarios → Perfil → Biografía / Sitio web) — no
 * separate theme setting needed, and it's empty/hidden gracefully if the
 * author never filled those in.
 */
function kdv_render_author_box( $post_id = null ) {
	$post_id   = $post_id ?: get_the_ID();
	$author_id = (int) get_post_field( 'post_author', $post_id );
	if ( ! $author_id ) {
		return;
	}
	$bio     = trim( get_the_author_meta( 'description', $author_id ) );
	$website = get_the_author_meta( 'user_url', $author_id );
	?>
	<div class="kdv-author-box">
		<div class="kdv-author-box__avatar">
			<?php echo get_avatar( $author_id, 72 ); ?>
		</div>
		<div class="kdv-author-box__body">
			<span class="kdv-author-box__label"><?php esc_html_e( 'Escrito por', 'revista-koltor-dev' ); ?></span>
			<h3 class="kdv-author-box__name">
				<a href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>"><?php echo esc_html( get_the_author_meta( 'display_name', $author_id ) ); ?></a>
			</h3>
			<?php if ( $bio ) : ?>
				<p class="kdv-author-box__bio"><?php echo esc_html( $bio ); ?></p>
			<?php endif; ?>
			<?php if ( $website ) : ?>
				<a href="<?php echo esc_url( $website ); ?>" class="kdv-author-box__link" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Sitio web', 'revista-koltor-dev' ); ?> →</a>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

/**
 * Thin fixed bar at the very top of the viewport that fills as the visitor
 * scrolls through a single artículo/reseña — purely a visual nicety, its
 * width is updated client-side by assets/js/main.js. Printed conditionally
 * from header.php only on singular post/kdv_resena views.
 */
function kdv_render_reading_progress_bar() {
	echo '<div class="kdv-reading-progress" aria-hidden="true"><span class="kdv-reading-progress__bar"></span></div>';
}

/**
 * Franja de información del pie de página: presentación del sitio, redes,
 * secciones, enlaces legales y contacto.
 *
 * El contenido se edita en el escritorio, en "Revista Koltor Dev → Información del
 * sitio", y los enlaces salen de menús normales de WordPress — así que no
 * hace falta activar ningún widget para tener un pie de página completo.
 * Cada bloque desaparece solo si está desactivado o si no tiene contenido
 * que mostrar (por ejemplo, la columna legal no aparece hasta que asignes
 * un menú a "Menú legal (pie de página)").
 */
function kdv_render_footer_info() {
	$info   = kdv_get_site_info();
	$social = function_exists( 'kdv_get_social_links' ) ? kdv_get_social_links() : [];

	$show_about = ! empty( $info['show_about'] ) && ( '' !== trim( $info['about_text'] ) || '' !== trim( $info['about_title'] ) );

	// Bloque "Sobre el sitio" (con las redes debajo).
	if ( $show_about ) {
		echo '<div class="kdv-footer__info-col kdv-footer__info-col--about">';
		if ( '' !== trim( $info['about_title'] ) ) {
			echo '<h4 class="kdv-widget-title">' . esc_html( $info['about_title'] ) . '</h4>';
		}
		if ( '' !== trim( $info['about_text'] ) ) {
			echo '<p class="kdv-footer__about-text">' . nl2br( esc_html( $info['about_text'] ) ) . '</p>';
		}
		if ( $social ) {
			echo '<div class="kdv-header-social kdv-footer__social">';
			kdv_render_social_links();
			echo '</div>';
		}
		echo '</div>';
	} elseif ( $social ) {
		// Sin bloque de presentación, las redes siguen teniendo su sitio.
		echo '<div class="kdv-footer__info-col"><div class="kdv-header-social kdv-footer__social">';
		kdv_render_social_links();
		echo '</div></div>';
	}

	// Bloque "Explorar" (menú de pie de página de toda la vida).
	if ( ! empty( $info['show_explore'] ) && has_nav_menu( 'footer' ) ) {
		echo '<div class="kdv-footer__info-col">';
		if ( '' !== trim( $info['explore_title'] ) ) {
			echo '<h4 class="kdv-widget-title">' . esc_html( $info['explore_title'] ) . '</h4>';
		}
		wp_nav_menu( [
			'theme_location' => 'footer',
			'container'      => false,
			'menu_class'     => 'kdv-footer-menu',
			'fallback_cb'    => false,
		] );
		echo '</div>';
	}

	// Bloque "Legal" (ubicación de menú propia).
	if ( ! empty( $info['show_legal'] ) && has_nav_menu( 'footer_legal' ) ) {
		echo '<div class="kdv-footer__info-col">';
		if ( '' !== trim( $info['legal_title'] ) ) {
			echo '<h4 class="kdv-widget-title">' . esc_html( $info['legal_title'] ) . '</h4>';
		}
		wp_nav_menu( [
			'theme_location' => 'footer_legal',
			'container'      => false,
			'menu_class'     => 'kdv-footer-menu',
			'fallback_cb'    => false,
		] );
		echo '</div>';
	}

	// Bloque "Contacto".
	$contact_text  = trim( $info['contact_text'] );
	$contact_email = is_email( $info['contact_email'] ) ? $info['contact_email'] : '';
	if ( ! empty( $info['show_contact'] ) && ( '' !== $contact_text || '' !== $contact_email ) ) {
		echo '<div class="kdv-footer__info-col">';
		if ( '' !== trim( $info['contact_title'] ) ) {
			echo '<h4 class="kdv-widget-title">' . esc_html( $info['contact_title'] ) . '</h4>';
		}
		if ( '' !== $contact_text ) {
			echo '<p class="kdv-footer__about-text">' . nl2br( esc_html( $contact_text ) ) . '</p>';
		}
		if ( '' !== $contact_email ) {
			// antispambot() codifica el correo en entidades HTML: se lee
			// igual en pantalla, pero los robots que rastrean direcciones
			// para spam lo tienen bastante más difícil.
			printf(
				'<a class="kdv-footer__contact-mail" href="mailto:%1$s">%2$s</a>',
				antispambot( $contact_email, 1 ), // phpcs:ignore WordPress.Security.EscapeOutput -- dirección ya validada con is_email() y codificada por antispambot(); escaparla otra vez convertiría el "&" de las entidades en "&amp;" y el correo se leería como "nombre&#64;dominio".
				antispambot( $contact_email ) // phpcs:ignore WordPress.Security.EscapeOutput -- ídem.
			);
		}
		echo '</div>';
	}
}
