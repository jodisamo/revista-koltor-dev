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
	?>
	<div class="kdv-platform-bar">
		<div class="kdv-container kdv-platform-bar__inner">
			<?php foreach ( $platforms as $p ) : ?>
				<?php
				$slug      = $p['term']->slug;
				$menu_id   = 'kdv-platform-menu-' . sanitize_html_class( $slug );
				?>
				<div class="kdv-platform-bar__item">
					<button type="button" class="kdv-platform-bar__toggle" aria-expanded="false" aria-controls="<?php echo esc_attr( $menu_id ); ?>">
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
		</div>
	</div>
	<?php
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
	if ( empty( $_GET['plataforma'] ) ) {
		return null;
	}
	$slug = sanitize_title( wp_unslash( $_GET['plataforma'] ) );
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
