<?php
/**
 * Revista Koltor Dev functions and definitions.
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Constants.
require_once __DIR__ . '/includes/core/constants.php';

// Theme setup (supports, menus, sidebars, image sizes).
require_once KDV_INCLUDES_DIR . '/core/theme-setup.php';

// Enqueue scripts and styles.
require_once KDV_INCLUDES_DIR . '/core/enqueue.php';

// Custom Post Type "Reseña" + taxonomía de géneros.
require_once KDV_INCLUDES_DIR . '/core/cpt-resena.php';

// Taxonomía "Plataforma" (PC, PlayStation, Xbox, Nintendo Switch, Móvil...).
require_once KDV_INCLUDES_DIR . '/core/taxonomy-plataforma.php';

// Custom Post Type "Diapositiva" (slider nativo del hero).
require_once KDV_INCLUDES_DIR . '/core/cpt-slide.php';

// Custom Post Type "Anuncio de cinta" (cinta de anuncios sobre la cabecera).
require_once KDV_INCLUDES_DIR . '/core/cpt-ticker.php';

// Modo construcción: pantalla de "en construcción" para todo el frontend
// mientras el sitio no está listo (ver includes/core/maintenance-mode.php).
require_once KDV_INCLUDES_DIR . '/core/maintenance-mode.php';

// Admin meta box for review fields (score, studio, year, pros/cons...).
require_once KDV_INCLUDES_DIR . '/admin/meta-box-resena.php';

// Helper/template functions (score badge, colour by score, etc.).
require_once KDV_INCLUDES_DIR . '/core/template-tags.php';

// Pantalla "Revista Koltor Dev → Información del sitio". Solo hace falta en el
// escritorio: los textos que se guardan ahí se leen en el frontend con
// kdv_get_site_info() (declarada en template-tags.php, siempre disponible).
if ( is_admin() ) {
	require_once KDV_INCLUDES_DIR . '/admin/site-info-page.php';
}

// Librería propia de iconos SVG (línea fina, licencia MIT) — usada por el
// selector de icono de categorías y por el widget de Categorías.
require_once KDV_INCLUDES_DIR . '/core/icon-library.php';

// Safety-net Spanish translation for stock WordPress widget titles.
require_once KDV_INCLUDES_DIR . '/core/widget-titles.php';
require_once KDV_INCLUDES_DIR . '/core/widgets.php';

// Selector visual de icono por categoría (usado por el widget de Categorías).
require_once KDV_INCLUDES_DIR . '/core/category-icons.php';

// Contador nativo de vistas + consulta de "Populares del mes".
require_once KDV_INCLUDES_DIR . '/core/popular-posts.php';

// Native Customizer (panel, sections, controls, selective refresh).
require_once KDV_INCLUDES_DIR . '/customizer/customizer.php';

// Dark mode toggle (script + body class + no-flash inline snippet).
require_once KDV_INCLUDES_DIR . '/darkmode/dark-mode.php';

// Gutenberg blocks (Caja de Reseña, Grid de Reseñas).
require_once KDV_INCLUDES_DIR . '/blocks/blocks.php';

// Elementor / Elementor Pro compatibility (full-width content, content_width).
require_once KDV_INCLUDES_DIR . '/core/elementor-compat.php';

// SEO: schema.org Review markup for reseñas + fallback meta/Open Graph tags
// (the fallback backs off automatically when Yoast/Rank Math/etc. is active).
require_once KDV_INCLUDES_DIR . '/core/seo.php';
