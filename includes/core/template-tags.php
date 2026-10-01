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
 * Curated font pairings for Personalizar → Tipografía. Los archivos de cada
 * uno viven en assets/fonts/<clave>.css (scripts/fetch-fonts.py); la clave
 * 'google' queda como referencia de lo que se descargó. Only the frontend
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
	// str_word_count() no entiende UTF-8: partía cada palabra con tilde o
	// ñ en dos ("análisis" contaba como 2) e inflaba el tiempo en español.
	$text    = trim( wp_strip_all_tags( strip_shortcodes( $content ) ) );
	$words   = '' === $text ? 0 : count( preg_split( '/\s+/u', $text ) );
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
 * Categorías de contenido del tema, por slug (ver kdv_get_default_categories()
 * en cpt-resena.php). Se buscan por slug y NO por nombre: el nombre es lo
 * que se ve y cualquiera puede cambiarlo en Entradas → Categorías (así se
 * pasó de "Novedades" a "Noticias"); el slug es el identificador estable.
 *
 * @param string $slug Slug de la categoría (noticias, avances, reportajes…).
 * @return int ID de la categoría, o 0 si no existe.
 */
function kdv_get_category_id_by_slug( $slug ) {
	$term = get_term_by( 'slug', $slug, 'category' );
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
		/* translators: %s: nombre del sitio (Ajustes → Generales → Título del sitio). */
		'about_title'    => sprintf( __( 'Sobre %s', 'revista-koltor-dev' ), get_bloginfo( 'name' ) ),
		'about_text'     => __( 'Describe aquí tu publicación en dos o tres frases: de qué trata, para quién es y con qué frecuencia publicas. Este texto aparece en el pie de página.', 'revista-koltor-dev' ),

		'show_explore'   => true,
		'explore_title'  => __( 'Explorar', 'revista-koltor-dev' ),

		'show_legal'     => true,
		'legal_title'    => __( 'Legal', 'revista-koltor-dev' ),

		'show_contact'   => true,
		'contact_title'  => __( 'Contacto', 'revista-koltor-dev' ),
		'contact_text'   => __( '¿Tienes una noticia, una corrección o quieres colaborar con nosotros? Escríbenos.', 'revista-koltor-dev' ),
		'contact_email'  => '',

		// Créditos (iconos, fotos, fuentes…) en una línea pequeña junto al
		// copyright. Admite enlaces: muchas licencias gratuitas (Icons8,
		// por ejemplo) exigen enlazar al autor.
		'credits'        => '',
	];
}

/**
 * Etiquetas permitidas en el campo "Créditos": solo texto con enlaces y
 * énfasis. La misma lista sirve al guardar (site-info-page.php) y al
 * imprimir, así que nada fuera de esto llega nunca al HTML.
 *
 * @return array
 */
function kdv_get_credits_allowed_html() {
	return [
		'a'      => [
			'href'   => true,
			'title'  => true,
			'target' => true,
			'rel'    => true,
		],
		'strong' => [],
		'em'     => [],
	];
}

/**
 * Línea de créditos del pie de página (Revista Koltor Dev → Información del
 * sitio → Créditos). No imprime nada si el campo está vacío.
 */
function kdv_render_footer_credits() {
	$credits = trim( (string) kdv_get_site_info( 'credits' ) );
	if ( '' === $credits ) {
		return;
	}
	echo '<span class="kdv-footer__credits">' . wp_kses( $credits, kdv_get_credits_allowed_html() ) . '</span>';
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
		3 => 'noticias',
		4 => 'reportajes',
		5 => 'avances',
	];
	$slot = absint( $slot );

	return isset( $defaults[ $slot ] ) ? kdv_get_category_id_by_slug( $defaults[ $slot ] ) : 0;
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
 * Anuncios publicados para la cinta sobre la cabecera (CPT kdv_ticker_item),
 * en el orden fijado en "Atributos de página". No comprueba aquí el
 * interruptor del Personalizador — eso lo hace kdv_render_ticker().
 */
function kdv_get_ticker_items() {
	return get_posts( [
		'post_type'      => 'kdv_ticker_item',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
		'post_status'    => 'publish',
		'no_found_rows'  => true,
	] );
}

/**
 * Pinta la cinta de anuncios horizontal (eventos próximos: Nintendo Direct,
 * State of Play, etc.), si está activada en Personalizar → Cinta de
 * anuncios y hay al menos un anuncio publicado. Se llama desde header.php,
 * antes de la etiqueta <header>.
 *
 * El contenido se repite dos veces (misma lista, la segunda con
 * aria-hidden) para que el bucle de la animación CSS sea continuo sin
 * salto visible; @media (prefers-reduced-motion: reduce) desactiva el
 * movimiento y dispone los anuncios en una fila estática.
 */
function kdv_render_ticker() {
	if ( ! get_theme_mod( 'kdv_ticker_enabled', false ) ) {
		return;
	}

	$items = kdv_get_ticker_items();
	if ( empty( $items ) ) {
		return;
	}

	$speed = absint( get_theme_mod( 'kdv_ticker_speed', 25 ) );
	$speed = max( 10, min( 60, $speed ) );

	$render_item = static function( $post ) {
		$date = get_post_meta( $post->ID, '_kdv_ticker_date', true );
		$url  = get_post_meta( $post->ID, '_kdv_ticker_url', true );

		$inner  = '';
		if ( $date ) {
			$inner .= '<span class="kdv-ticker__date">' . esc_html( $date ) . '</span>';
		}
		$inner .= '<span class="kdv-ticker__text">' . esc_html( get_the_title( $post ) ) . '</span>';

		if ( $url ) {
			return '<a class="kdv-ticker__item" href="' . esc_url( $url ) . '">' . $inner . '</a>';
		}
		return '<span class="kdv-ticker__item">' . $inner . '</span>';
	};

	$html_items = array_map( $render_item, $items );
	$group      = implode( '<span class="kdv-ticker__sep" aria-hidden="true">·</span>', $html_items );

	// La copia solo existe para el bucle visual: ni el lector de pantalla
	// (aria-hidden) ni el tabulador (inert + tabindex, este último para
	// navegadores sin inert) deben recorrer sus enlaces dos veces.
	$group_copy = str_replace( '<a class="kdv-ticker__item"', '<a tabindex="-1" class="kdv-ticker__item"', $group );
	?>
	<div class="kdv-ticker" role="region" aria-label="<?php esc_attr_e( 'Próximos eventos', 'revista-koltor-dev' ); ?>">
		<div class="kdv-ticker__track" style="--kdv-ticker-duration: <?php echo esc_attr( $speed ); ?>s">
			<span class="kdv-ticker__group"><?php echo $group; // phpcs:ignore WordPress.Security.EscapeOutput -- cada trozo ya se escapó en $render_item. ?></span>
			<span class="kdv-ticker__group" aria-hidden="true" inert><?php echo $group_copy; // phpcs:ignore WordPress.Security.EscapeOutput -- idem, duplicado para el bucle continuo. ?></span>
		</div>
	</div>
	<?php
}

/**
 * Plataformas PRINCIPALES (kdv_plataforma sin padre: PlayStation, Xbox…)
 * que tienen un icono subido, en el orden en que se crearon -- las
 * subplataformas (PS5, PS4…) no salen en la barra. Esta taxonomía no tiene
 * orden manual todavía; si hace falta reordenar, la forma más simple por
 * ahora es borrar y crear de nuevo el término en el orden deseado.
 */
function kdv_get_platforms_with_icon() {
	$terms = get_terms( [
		'taxonomy'   => 'kdv_plataforma',
		'parent'     => 0,
		'hide_empty' => false,
		'orderby'    => 'term_id',
		'order'      => 'ASC',
	] );

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return [];
	}

	// Orden de la barra: el de kdv_get_default_platforms() (PlayStation,
	// Nintendo, Xbox, PC, Android); las que se añadan después, al final en
	// orden de creación.
	$order = array_flip( array_keys( kdv_get_default_platforms() ) );
	usort( $terms, function( $a, $b ) use ( $order ) {
		return ( ( $order[ $a->slug ] ?? PHP_INT_MAX ) <=> ( $order[ $b->slug ] ?? PHP_INT_MAX ) )
			?: ( $a->term_id <=> $b->term_id );
	} );

	$with_icon = [];
	foreach ( $terms as $term ) {
		$icon_id = absint( get_term_meta( $term->term_id, 'kdv_platform_icon', true ) );
		if ( $icon_id ) {
			$with_icon[] = [
				'term'    => $term,
				'icon_id' => $icon_id,
			];
		}
	}
	return $with_icon;
}

/**
 * Pinta la barra de plataformas (PC, PlayStation, Xbox...) como fila
 * propia debajo de la cabecera -- deliberadamente NO integrada en
 * .kdv-primary-menu ni sus desplegables: ese es el subsistema del tema con
 * más historial de bugs (ver §9 del manual), y esto es un elemento
 * aparte, no un nivel más del menú. Solo se pinta si está activada en
 * Personalizar → Cabecera y hay al menos una plataforma con icono subido
 * -- si no, no se muestra nada, nunca una fila vacía o con huecos rotos.
 *
 * Cada plataforma es un botón con un pequeño desplegable propio (JS en
 * assets/js/main.js, sin relación con el del menú principal) hacia sus
 * secciones ya existentes -- Noticias y Avances (categorías nativas, por
 * slug), Reseñas y Tops (la plantilla de Ranking) -- filtradas por esa
 * plataforma vía ?plataforma=slug. A propósito NO se crean términos ni
 * categorías nuevas por plataforma: el filtro cruza dos taxonomías que ya
 * existen (kdv_plataforma + category) en la URL, así que cada artículo se
 * sigue etiquetando una sola vez. Un enlace se omite en silencio si su
 * destino no existe todavía (p. ej. "Tops" antes de crear la página de
 * Ranking, o una categoría nativa que se haya borrado).
 */
function kdv_render_platform_bar() {
	if ( ! get_theme_mod( 'kdv_show_platform_bar', false ) ) {
		return;
	}

	$platforms = kdv_get_platforms_with_icon();
	if ( empty( $platforms ) ) {
		return;
	}

	// Etiqueta => URL base. Las categorías muestran su nombre actual (si se
	// renombra en el escritorio, el desplegable lo refleja solo).
	$sections = [];
	foreach ( [ 'noticias', 'avances' ] as $cat_slug ) {
		$cat_id = kdv_get_category_id_by_slug( $cat_slug );
		if ( $cat_id ) {
			$sections[ get_cat_name( $cat_id ) ] = get_category_link( $cat_id );
		}
	}
	$sections[ __( 'Reseñas', 'revista-koltor-dev' ) ] = get_post_type_archive_link( 'kdv_resena' );
	$sections[ __( 'Tops', 'revista-koltor-dev' ) ]    = kdv_get_ranking_page_url();

	$current_slug = kdv_get_current_platform_slug();
	$is_home      = is_front_page();
	?>
	<nav class="kdv-platform-bar" aria-label="<?php esc_attr_e( 'Plataformas', 'revista-koltor-dev' ); ?>">
		<div class="kdv-container kdv-platform-bar__inner">

			<?php
			/*
			 * "Inicio": casa dibujada por el propio tema (SVG en línea, sin
			 * marcas de terceros). Al pasar el ratón el tejado se levanta y
			 * la ventana se enciende (main.css). En la portada queda activa.
			 */
			?>
			<div class="kdv-platform-bar__item kdv-platform-bar__item--home<?php echo $is_home ? ' is-current' : ''; ?>">
				<a class="kdv-platform-bar__toggle kdv-platform-bar__home" href="<?php echo esc_url( home_url( '/' ) ); ?>"<?php echo $is_home ? ' aria-current="page"' : ''; ?>>
					<span class="kdv-platform-bar__iconwrap">
						<svg class="kdv-platform-bar__house" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false">
							<path class="kdv-house__roof" d="M3 11.2 12 4l9 7.2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							<path class="kdv-house__body" d="M5.5 10v9.5h13V10" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
							<rect class="kdv-house__window" x="10" y="13" width="4" height="4" rx=".8"/>
						</svg>
					</span>
					<span class="kdv-platform-bar__label"><?php esc_html_e( 'Inicio', 'revista-koltor-dev' ); ?></span>
				</a>
			</div>

			<?php foreach ( $platforms as $p ) : ?>
				<?php
				$slug     = $p['term']->slug;
				$menu_id  = 'kdv-platform-menu-' . sanitize_html_class( $slug );
				$color    = kdv_get_platform_color( $p['term'] );
				$icon_url = wp_get_attachment_image_url( $p['icon_id'], 'thumbnail' );
				$current  = $slug === $current_slug;

				// Color de marca e icono como variables CSS del elemento: el
				// fondo, el texto, el icono teñido y el indicador los leen.
				$style = '';
				if ( $color ) {
					$style .= '--kdv-pc:' . $color . ';';
				}
				if ( $icon_url ) {
					$style .= '--kdv-icon-url:url(' . esc_url( $icon_url ) . ');';
				}
				?>
				<div class="kdv-platform-bar__item<?php echo $current ? ' is-current' : ''; ?>"<?php echo $style ? ' style="' . esc_attr( $style ) . '"' : ''; ?>>
					<button type="button" class="kdv-platform-bar__toggle" aria-expanded="false" aria-controls="<?php echo esc_attr( $menu_id ); ?>"<?php echo $current ? ' aria-current="true"' : ''; ?>>
						<span class="kdv-platform-bar__iconwrap">
							<?php
							echo wp_get_attachment_image(
								$p['icon_id'],
								'thumbnail',
								false,
								[
									'class' => 'kdv-platform-bar__icon',
									'alt'   => '',
								]
							);
							?>
							<?php // El mismo icono pintado del color de la marca (máscara CSS). ?>
							<span class="kdv-platform-bar__icon-tint" aria-hidden="true"></span>
						</span>
						<span class="kdv-platform-bar__label"><?php echo esc_html( $p['term']->name ); ?></span>
					</button>
					<ul class="kdv-platform-bar__menu" id="<?php echo esc_attr( $menu_id ); ?>">
						<?php // Primero, la portada de la plataforma (taxonomy-kdv_plataforma.php). ?>
						<li class="kdv-platform-bar__menu-hub">
							<a href="<?php echo esc_url( get_term_link( $p['term'] ) ); ?>">
								<?php
								/* translators: %s: nombre de la plataforma (ej. PlayStation). */
								printf( esc_html__( 'Todo %s', 'revista-koltor-dev' ), esc_html( $p['term']->name ) );
								?>
							</a>
						</li>
						<?php foreach ( $sections as $label => $base_url ) : ?>
							<?php if ( $base_url ) : ?>
								<li><a href="<?php echo esc_url( add_query_arg( 'plataforma', $slug, $base_url ) ); ?>"><?php echo esc_html( $label ); ?></a></li>
							<?php endif; ?>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endforeach; ?>

			<?php // Línea que se desliza bajo el elemento señalado (JS en main.js). ?>
			<span class="kdv-platform-bar__indicator" aria-hidden="true"></span>
		</div>
	</nav>
	<?php
}

/**
 * Slug de la plataforma PRINCIPAL que corresponde a la página actual, para
 * marcarla como activa en la barra: la portada de una plataforma
 * (/plataforma/ps5/ → playstation) o un listado filtrado con ?plataforma=.
 * Cadena vacía si la página no es de ninguna plataforma.
 *
 * @return string
 */
function kdv_get_current_platform_slug() {
	$term = is_tax( 'kdv_plataforma' ) ? get_queried_object() : kdv_get_platform_filter_term();
	if ( ! $term instanceof WP_Term ) {
		return '';
	}
	$ancestors = get_ancestors( $term->term_id, 'kdv_plataforma', 'taxonomy' );
	if ( $ancestors ) {
		$top = get_term( end( $ancestors ), 'kdv_plataforma' );
		return ( $top && ! is_wp_error( $top ) ) ? $top->slug : '';
	}
	return $term->slug;
}

/**
 * Plataformas principales de una entrada o reseña, sin repetir: marcada
 * "PS5" y "PS4" da una sola PlayStation. Cada elemento: [ 'term' =>
 * plataforma principal, 'names' => nombres concretos marcados ].
 *
 * @param int $post_id Entrada.
 * @return array[]
 */
function kdv_get_post_top_platforms( $post_id ) {
	$terms = get_the_terms( $post_id, 'kdv_plataforma' );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return [];
	}
	$tops = [];
	foreach ( $terms as $term ) {
		$ancestors = get_ancestors( $term->term_id, 'kdv_plataforma', 'taxonomy' );
		$top       = $ancestors ? get_term( end( $ancestors ), 'kdv_plataforma' ) : $term;
		if ( ! $top || is_wp_error( $top ) ) {
			continue;
		}
		if ( ! isset( $tops[ $top->term_id ] ) ) {
			$tops[ $top->term_id ] = [ 'term' => $top, 'names' => [] ];
		}
		$tops[ $top->term_id ]['names'][] = $term->name;
	}

	// Mismo orden que la barra (PlayStation, Nintendo, Xbox, PC, Android).
	$order = array_flip( array_keys( kdv_get_default_platforms() ) );
	uasort( $tops, function( $a, $b ) use ( $order ) {
		return ( $order[ $a['term']->slug ] ?? PHP_INT_MAX ) <=> ( $order[ $b['term']->slug ] ?? PHP_INT_MAX );
	} );
	return array_values( $tops );
}

/**
 * Plataformas principales de una diapositiva de portada: las marcadas en la
 * propia diapositiva o, si no tiene ninguna y su botón enlaza a una entrada
 * o reseña del sitio, las de esa entrada (así no hay que marcarlas dos
 * veces).
 *
 * @param WP_Post $slide Diapositiva (kdv_slide).
 * @return array[] Como kdv_get_post_top_platforms().
 */
function kdv_get_slide_platforms( $slide ) {
	$platforms = kdv_get_post_top_platforms( $slide->ID );
	if ( $platforms ) {
		return $platforms;
	}
	$button_url = (string) get_post_meta( $slide->ID, '_kdv_slide_button_url', true );
	$linked_id  = $button_url ? url_to_postid( $button_url ) : 0;
	return $linked_id ? kdv_get_post_top_platforms( $linked_id ) : [];
}

/**
 * Posiciones posibles de las etiquetas de plataforma de las diapositivas
 * (Personalizar → Portada).
 *
 * @return string[]
 */
function kdv_hero_platform_positions() {
	return [
		'bottom-right' => __( 'Abajo a la derecha (por defecto)', 'revista-koltor-dev' ),
		'bottom-left'  => __( 'Abajo a la izquierda', 'revista-koltor-dev' ),
		'top-right'    => __( 'Arriba a la derecha', 'revista-koltor-dev' ),
		'top-left'     => __( 'Arriba a la izquierda', 'revista-koltor-dev' ),
		'above-title'  => __( 'Encima del título, centradas', 'revista-koltor-dev' ),
	];
}

/**
 * Etiquetas de plataforma de una diapositiva: icono y nombre (o solo el
 * icono) sobre el color de la marca, para que se vea de un vistazo de qué
 * plataforma trata lo que anuncia la portada. La posición y el estilo se
 * eligen en Personalizar → Portada.
 *
 * Con $position = 'mobile' pinta la variante del móvil: solo iconos y
 * DENTRO del contenido, encima del título (main.css la muestra solo por
 * debajo de 640px y oculta allí la de la esquina). En el móvil el título y
 * el subtítulo llenan casi toda la diapositiva y una etiqueta en una
 * esquina acababa encima del botón; dentro del contenido no puede chocar.
 *
 * @param WP_Post $slide    Diapositiva.
 * @param string  $position Una de kdv_hero_platform_positions(), o 'mobile'.
 */
function kdv_render_slide_platforms( $slide, $position = 'above-title' ) {
	$platforms = kdv_get_slide_platforms( $slide );
	if ( ! $platforms ) {
		return;
	}
	$mobile    = 'mobile' === $position;
	$icon_only = $mobile || 'icon' === get_theme_mod( 'kdv_hero_platform_style', 'full' );
	$corner    = ! $mobile && 'above-title' !== $position;

	// En una esquina va en su propia capa, alineada con el contenedor.
	if ( $corner ) {
		echo '<div class="kdv-hero-slider__corner kdv-hero-slider__corner--' . esc_attr( $position ) . '"><div class="kdv-container">';
	}
	echo '<ul class="kdv-hero-slider__platforms' . ( $icon_only ? ' kdv-hero-slider__platforms--icon' : '' ) . ( $mobile ? ' kdv-hero-slider__platforms--mobile' : '' ) . '"' . '>';
	foreach ( array_slice( $platforms, 0, 4 ) as $p ) {
		$term     = $p['term'];
		$color    = kdv_get_platform_color( $term );
		$icon_id  = absint( get_term_meta( $term->term_id, 'kdv_platform_icon', true ) );
		$icon_url = $icon_id ? wp_get_attachment_image_url( $icon_id, 'thumbnail' ) : '';
		// Solo icono: el nombre sigue para lectores de pantalla y como título
		// al pasar el ratón. Sin icono subido, siempre con nombre.
		$hide_name = $icon_only && $icon_url;
		printf(
			'<li class="kdv-hero-chip"%1$s%4$s>%2$s<span%5$s>%3$s</span></li>',
			$color ? ' style="' . esc_attr( '--kdv-pc:' . $color ) . '"' : '',
			$icon_url ? '<img class="kdv-hero-chip__icon" src="' . esc_url( $icon_url ) . '" alt="" width="20" height="20" />' : '',
			esc_html( $term->name ),
			$hide_name ? ' title="' . esc_attr( $term->name ) . '"' : '',
			$hide_name ? ' class="screen-reader-text"' : ''
		);
	}
	echo '</ul>';
	if ( $corner ) {
		echo '</div></div>';
	}
}

/**
 * Distintivos de plataforma para las tarjetas: el icono de cada plataforma
 * principal, pintado de su color de marca, enlazando a su portada. La
 * etiqueta de la tarjeta sigue diciendo QUÉ es (Noticias, Avances…); esto
 * dice PARA QUÉ es. Como mucho 3 y "+N" si hay más. Sin icono subido, se
 * muestra el nombre abreviado.
 *
 * @param int|null $post_id Entrada; por defecto la actual del bucle.
 */
function kdv_render_platform_badges( $post_id = null ) {
	$platforms = kdv_get_post_top_platforms( $post_id ?: get_the_ID() );
	if ( ! $platforms ) {
		return;
	}
	$max   = 3;
	$extra = count( $platforms ) - $max;

	echo '<ul class="kdv-platform-badges">';
	foreach ( array_slice( $platforms, 0, $max ) as $p ) {
		$term     = $p['term'];
		$color    = kdv_get_platform_color( $term );
		$icon_id  = absint( get_term_meta( $term->term_id, 'kdv_platform_icon', true ) );
		$icon_url = $icon_id ? wp_get_attachment_image_url( $icon_id, 'thumbnail' ) : '';
		$style    = ( $color ? '--kdv-pc:' . $color . ';' : '' ) . ( $icon_url ? '--kdv-icon-url:url(' . esc_url( $icon_url ) . ');' : '' );
		// "PlayStation (PS5, PS4)" cuando se marcaron plataformas concretas.
		$specific = array_diff( $p['names'], [ $term->name ] );
		$title    = $specific ? $term->name . ' (' . implode( ', ', $specific ) . ')' : $term->name;

		printf(
			'<li><a class="kdv-platform-badge%1$s" href="%2$s" title="%3$s"%4$s>%5$s<span class="screen-reader-text">%6$s</span></a></li>',
			$icon_url ? '' : ' kdv-platform-badge--text',
			esc_url( get_term_link( $term ) ),
			esc_attr( $title ),
			$style ? ' style="' . esc_attr( $style ) . '"' : '',
			$icon_url ? '<img class="kdv-platform-badge__img" src="' . esc_url( $icon_url ) . '" alt="" width="16" height="16" loading="lazy" decoding="async" />' : '<span aria-hidden="true">' . esc_html( mb_substr( $term->name, 0, 3 ) ) . '</span>',
			esc_html( $title )
		);
	}
	if ( $extra > 0 ) {
		/* translators: %d: número de plataformas no mostradas. */
		printf( '<li class="kdv-platform-badges__more" title="%1$s">+%2$d</li>', esc_attr( sprintf( _n( '%d plataforma más', '%d plataformas más', $extra, 'revista-koltor-dev' ), $extra ) ), absint( $extra ) );
	}
	echo '</ul>';
}

/**
 * Plataforma pedida por query string (?plataforma=slug), ya validada
 * contra un término real de kdv_plataforma -- nunca se confía en el
 * parámetro tal cual. Se usa tanto para filtrar la consulta principal
 * (kdv_maybe_filter_by_platform()) como para pintar el aviso de "filtrado
 * por..." en cada plantilla de archivo.
 *
 * @return WP_Term|null
 */
function kdv_get_platform_filter_term() {
	// Solo texto: "?plataforma[]=x" llega como array y sanitize_title() con
	// un array lanza un TypeError (error 500 en cualquier página, porque la
	// barra de plataformas lee este parámetro en todas).
	if ( empty( $_GET['plataforma'] ) || ! is_string( $_GET['plataforma'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- filtro público de solo lectura.
		return null;
	}
	$slug = sanitize_title( wp_unslash( $_GET['plataforma'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
	$term = get_term_by( 'slug', $slug, 'kdv_plataforma' );
	return ( $term && ! is_wp_error( $term ) ) ? $term : null;
}

/**
 * Añade el filtro de plataforma a la consulta PRINCIPAL de los archivos
 * que pueden recibir ?plataforma=... desde el desplegable de la barra de
 * plataformas (categoría, etiqueta, archivo de Reseñas). La plantilla de
 * Ranking NO pasa por aquí -- arma su propio WP_Query aparte (ver
 * page-templates/ranking.php), así que el filtro se le añade ahí
 * directamente en sus argumentos, no con este hook.
 */
function kdv_maybe_filter_by_platform( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( ! ( $query->is_category() || $query->is_tag() || $query->is_post_type_archive( 'kdv_resena' ) ) ) {
		return;
	}
	$term = kdv_get_platform_filter_term();
	if ( ! $term ) {
		return;
	}
	$tax_query   = (array) $query->get( 'tax_query' );
	$tax_query[] = [
		'taxonomy' => 'kdv_plataforma',
		'field'    => 'term_id',
		'terms'    => $term->term_id,
	];
	$query->set( 'tax_query', $tax_query );
}
add_action( 'pre_get_posts', 'kdv_maybe_filter_by_platform' );

/**
 * Aviso "filtrado por: PlayStation" + enlace para quitar el filtro,
 * pensado para pintarse justo debajo del título en cualquier plantilla de
 * archivo que pueda recibir ?plataforma=... (archive.php,
 * archive-kdv_resena.php, page-templates/ranking.php). No hace nada si no
 * hay ningún filtro de plataforma activo en la URL actual.
 */
function kdv_render_platform_filter_notice() {
	$term = kdv_get_platform_filter_term();
	if ( ! $term ) {
		return;
	}
	$remove_url = remove_query_arg( 'plataforma' );
	?>
	<p class="kdv-platform-filter-notice">
		<?php
		printf(
			/* translators: %s: nombre de la plataforma (ej. PlayStation). */
			esc_html__( 'Filtrado por: %s', 'revista-koltor-dev' ),
			'<strong>' . esc_html( $term->name ) . '</strong>'
		);
		?>
		— <a href="<?php echo esc_url( $remove_url ); ?>"><?php esc_html_e( 'quitar filtro', 'revista-koltor-dev' ); ?></a>
	</p>
	<?php
}

/**
 * URL de la primera página que use la plantilla "Ranking de Reseñas", o
 * cadena vacía si todavía no se ha creado ninguna (ver §8 del manual: la
 * plantilla se asigna a mano desde "Atributos de página"). Resultado
 * cacheado en memoria -- se llama una vez por plataforma en la barra, no
 * hace falta repetir la consulta cada vez.
 */
function kdv_get_ranking_page_url() {
	static $url = null;
	if ( null !== $url ) {
		return $url;
	}
	$pages = get_posts( [
		'post_type'      => 'page',
		'posts_per_page' => 1,
		'post_status'    => 'publish',
		'no_found_rows'  => true,
		'meta_key'       => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery -- una sola vez por carga, con posts_per_page 1.
		'meta_value'     => 'page-templates/ranking.php', // phpcs:ignore WordPress.DB.SlowDBQuery
	] );
	$url = $pages ? get_permalink( $pages[0] ) : '';
	return $url;
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
		get_theme_mod( 'kdv_score_label_1', __( 'Jugabilidad', 'revista-koltor-dev' ) ),
		get_theme_mod( 'kdv_score_label_2', __( 'Gráficos', 'revista-koltor-dev' ) ),
		get_theme_mod( 'kdv_score_label_3', __( 'Sonido', 'revista-koltor-dev' ) ),
		get_theme_mod( 'kdv_score_label_4', __( 'Historia', 'revista-koltor-dev' ) ),
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
 * Iconos de redes sociales: SVG de 24x24 en currentColor, uno por red, más
 * dos genéricos dibujados por el tema ("link" para redes desconocidas y
 * "copy" para "Copiar enlace"). Una sola fuente para la cabecera, el pie,
 * la barra flotante y los botones de compartir.
 *
 * Los logotipos de marca son de Simple Icons (https://simpleicons.org,
 * v16.33.0), con licencia CC0-1.0 (dominio público). Sustituyen a los
 * trazos dibujados a mano de versiones anteriores, que no se reconocían
 * (la "X" era una cruz que parecía un botón de cerrar).
 */
function kdv_social_icon_paths() {
	return [
		'x'          => '<path fill="currentColor" d="M14.234 10.162 22.977 0h-2.072l-7.591 8.824L7.251 0H.258l9.168 13.343L.258 24H2.33l8.016-9.318L16.749 24h6.993zm-2.837 3.299-.929-1.329L3.076 1.56h3.182l5.965 8.532.929 1.329 7.754 11.09h-3.182z"/>',
		'instagram'  => '<path fill="currentColor" d="M7.0301.084c-1.2768.0602-2.1487.264-2.911.5634-.7888.3075-1.4575.72-2.1228 1.3877-.6652.6677-1.075 1.3368-1.3802 2.127-.2954.7638-.4956 1.6365-.552 2.914-.0564 1.2775-.0689 1.6882-.0626 4.947.0062 3.2586.0206 3.6671.0825 4.9473.061 1.2765.264 2.1482.5635 2.9107.308.7889.72 1.4573 1.388 2.1228.6679.6655 1.3365 1.0743 2.1285 1.38.7632.295 1.6361.4961 2.9134.552 1.2773.056 1.6884.069 4.9462.0627 3.2578-.0062 3.668-.0207 4.9478-.0814 1.28-.0607 2.147-.2652 2.9098-.5633.7889-.3086 1.4578-.72 2.1228-1.3881.665-.6682 1.0745-1.3378 1.3795-2.1284.2957-.7632.4966-1.636.552-2.9124.056-1.2809.0692-1.6898.063-4.948-.0063-3.2583-.021-3.6668-.0817-4.9465-.0607-1.2797-.264-2.1487-.5633-2.9117-.3084-.7889-.72-1.4568-1.3876-2.1228C21.2982 1.33 20.628.9208 19.8378.6165 19.074.321 18.2017.1197 16.9244.0645 15.6471.0093 15.236-.005 11.977.0014 8.718.0076 8.31.0215 7.0301.0839m.1402 21.6932c-1.17-.0509-1.8053-.2453-2.2287-.408-.5606-.216-.96-.4771-1.3819-.895-.422-.4178-.6811-.8186-.9-1.378-.1644-.4234-.3624-1.058-.4171-2.228-.0595-1.2645-.072-1.6442-.079-4.848-.007-3.2037.0053-3.583.0607-4.848.05-1.169.2456-1.805.408-2.2282.216-.5613.4762-.96.895-1.3816.4188-.4217.8184-.6814 1.3783-.9003.423-.1651 1.0575-.3614 2.227-.4171 1.2655-.06 1.6447-.072 4.848-.079 3.2033-.007 3.5835.005 4.8495.0608 1.169.0508 1.8053.2445 2.228.408.5608.216.96.4754 1.3816.895.4217.4194.6816.8176.9005 1.3787.1653.4217.3617 1.056.4169 2.2263.0602 1.2655.0739 1.645.0796 4.848.0058 3.203-.0055 3.5834-.061 4.848-.051 1.17-.245 1.8055-.408 2.2294-.216.5604-.4763.96-.8954 1.3814-.419.4215-.8181.6811-1.3783.9-.4224.1649-1.0577.3617-2.2262.4174-1.2656.0595-1.6448.072-4.8493.079-3.2045.007-3.5825-.006-4.848-.0608M16.953 5.5864A1.44 1.44 0 1 0 18.39 4.144a1.44 1.44 0 0 0-1.437 1.4424M5.8385 12.012c.0067 3.4032 2.7706 6.1557 6.173 6.1493 3.4026-.0065 6.157-2.7701 6.1506-6.1733-.0065-3.4032-2.771-6.1565-6.174-6.1498-3.403.0067-6.156 2.771-6.1496 6.1738M8 12.0077a4 4 0 1 1 4.008 3.9921A3.9996 3.9996 0 0 1 8 12.0077"/>',
		'facebook'   => '<path fill="currentColor" d="M9.101 23.691v-7.98H6.627v-3.667h2.474v-1.58c0-4.085 1.848-5.978 5.858-5.978.401 0 .955.042 1.468.103a8.68 8.68 0 0 1 1.141.195v3.325a8.623 8.623 0 0 0-.653-.036 26.805 26.805 0 0 0-.733-.009c-.707 0-1.259.096-1.675.309a1.686 1.686 0 0 0-.679.622c-.258.42-.374.995-.374 1.752v1.297h3.919l-.386 2.103-.287 1.564h-3.246v8.245C19.396 23.238 24 18.179 24 12.044c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.628 3.874 10.35 9.101 11.647Z"/>',
		'youtube'    => '<path fill="currentColor" d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>',
		'tiktok'     => '<path fill="currentColor" d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>',
		'discord'    => '<path fill="currentColor" d="M20.317 4.3698a19.7913 19.7913 0 00-4.8851-1.5152.0741.0741 0 00-.0785.0371c-.211.3753-.4447.8648-.6083 1.2495-1.8447-.2762-3.68-.2762-5.4868 0-.1636-.3933-.4058-.8742-.6177-1.2495a.077.077 0 00-.0785-.037 19.7363 19.7363 0 00-4.8852 1.515.0699.0699 0 00-.0321.0277C.5334 9.0458-.319 13.5799.0992 18.0578a.0824.0824 0 00.0312.0561c2.0528 1.5076 4.0413 2.4228 5.9929 3.0294a.0777.0777 0 00.0842-.0276c.4616-.6304.8731-1.2952 1.226-1.9942a.076.076 0 00-.0416-.1057c-.6528-.2476-1.2743-.5495-1.8722-.8923a.077.077 0 01-.0076-.1277c.1258-.0943.2517-.1923.3718-.2914a.0743.0743 0 01.0776-.0105c3.9278 1.7933 8.18 1.7933 12.0614 0a.0739.0739 0 01.0785.0095c.1202.099.246.1981.3728.2924a.077.077 0 01-.0066.1276 12.2986 12.2986 0 01-1.873.8914.0766.0766 0 00-.0407.1067c.3604.698.7719 1.3628 1.225 1.9932a.076.076 0 00.0842.0286c1.961-.6067 3.9495-1.5219 6.0023-3.0294a.077.077 0 00.0313-.0552c.5004-5.177-.8382-9.6739-3.5485-13.6604a.061.061 0 00-.0312-.0286zM8.02 15.3312c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9555-2.4189 2.157-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.9555 2.4189-2.1569 2.4189zm7.9748 0c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9554-2.4189 2.1569-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.946 2.4189-2.1568 2.4189Z"/>',
		'whatsapp'   => '<path fill="currentColor" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>',
		'telegram'   => '<path fill="currentColor" d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>',
		'link'      => '<circle cx="12" cy="12" r="8.5" fill="none" stroke="currentColor" stroke-width="2"/><path d="M3.5 12h17M12 3.5c2.4 2.3 3.6 5.1 3.6 8.5s-1.2 6.2-3.6 8.5c-2.4-2.3-3.6-5.1-3.6-8.5S9.6 5.8 12 3.5Z" fill="none" stroke="currentColor" stroke-width="1.6"/>',
		'copy'      => '<path d="M10 14a4.5 4.5 0 0 0 6.4 0l3-3a4.5 4.5 0 0 0-6.4-6.4l-1.2 1.2M14 10a4.5 4.5 0 0 0-6.4 0l-3 3a4.5 4.5 0 0 0 6.4 6.4l1.2-1.2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
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

	// Coincidencia exacta con el dominio o con un subdominio suyo
	// (www.x.com, m.facebook.com…) -- un strpos() suelto confundía
	// dropbox.com o netflix.com con x.com.
	$platform = 'link';
	foreach ( $domain_map as $domain => $key ) {
		if ( $host === $domain || '.' . $domain === substr( $host, -strlen( '.' . $domain ) ) ) {
			$platform = $key;
			break;
		}
	}

	return kdv_get_platform_icon_svg( $platform );
}

/**
 * Convierte lo que se escriba en un campo de red social en una URL https
 * válida. Acepta la URL completa, la URL sin "https://", "@usuario", el
 * usuario a secas o, en WhatsApp, el número con o sin "+", espacios y
 * guiones. Antes solo valía la URL completa: "@entrepixeles" se guardaba
 * como "http://@entrepixeles" y un número de WhatsApp como
 * "http://573001234567", enlaces rotos.
 *
 * Se usa al guardar (Personalizador) y también al mostrar, así que los
 * valores que ya se hubieran guardado rotos se reparan solos.
 *
 * @param string $value    Lo escrito en el campo (o lo guardado).
 * @param string $platform facebook|instagram|x|tiktok|youtube|discord|whatsapp.
 * @return string URL https o '' si no hay nada utilizable.
 */
function kdv_normalize_social_url( $value, $platform ) {
	$value = trim( (string) $value );
	if ( '' === $value ) {
		return '';
	}

	// Valores guardados rotos por versiones anteriores: "http://@usuario",
	// "http://573001234567", "http://usuario" (sin punto en el dominio).
	if ( preg_match( '#^https?://([^/]+)/?$#i', $value, $m ) && false === strpos( $m[1], '.' ) ) {
		$value = rawurldecode( $m[1] );
	}

	$bases = [
		'facebook'  => 'https://www.facebook.com/',
		'instagram' => 'https://www.instagram.com/',
		'x'         => 'https://x.com/',
		'tiktok'    => 'https://www.tiktok.com/@',
		'youtube'   => 'https://www.youtube.com/@',
		'discord'   => 'https://discord.gg/',
		'whatsapp'  => 'https://wa.me/',
	];

	// WhatsApp: un número de teléfono (con o sin +, espacios, guiones o
	// paréntesis) se convierte en wa.me/<solo dígitos>.
	if ( 'whatsapp' === $platform && preg_match( '/^\+?[\d\s().-]{6,}$/', $value ) ) {
		return $bases['whatsapp'] . preg_replace( '/\D/', '', $value );
	}

	// Ya es una URL (con o sin protocolo): se respeta, siempre en https.
	if ( preg_match( '#^https?://#i', $value ) || preg_match( '#^[a-z0-9-]+(\.[a-z0-9-]+)+(/|$)#i', $value ) ) {
		$url = preg_replace( '#^http://#i', 'https://', $value );
		if ( ! preg_match( '#^https://#i', $url ) ) {
			$url = 'https://' . $url;
		}
		return esc_url_raw( $url );
	}

	// Un usuario ("@entrepixeles" o "entrepixeles"): perfil de esa red.
	$handle = ltrim( $value, '@' );
	if ( isset( $bases[ $platform ] ) && preg_match( '/^[\w.\-]+$/u', $handle ) ) {
		return esc_url_raw( $bases[ $platform ] . $handle );
	}

	return esc_url_raw( $value );
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
		$url = kdv_normalize_social_url( get_theme_mod( 'kdv_social_' . $key, '' ), $key );
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
		$custom_url = kdv_normalize_social_url( $parts[1], 'custom' );
		if ( ! $custom_url ) {
			continue;
		}
		$links[] = [
			'url'   => $custom_url,
			'label' => $parts[0],
			'key'   => 'custom',
			'icon'  => kdv_get_social_icon_svg( $custom_url ),
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
					<?php echo kdv_get_platform_icon_svg( 'copy' ); // phpcs:ignore -- fixed SVG markup from kdv_social_icon_paths(), no user data interpolated. ?>
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
