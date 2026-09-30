=== Revista Koltor Dev ===
Contributors: koltordev
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.1.0
Tags: blog, news, entertainment, two-columns, grid-layout, custom-logo, custom-menu, custom-colors, editor-style, featured-images, threaded-comments, translation-ready, block-styles, wide-blocks, dark-mode

== Description ==

Revista Koltor Dev es un tema de WordPress para revistas digitales, medios de nicho y blogs con vocación editorial: noticias, análisis, guías y reseñas con puntuación.

Está pensado como base reutilizable: instalas, cambias cuatro cosas en Personalizar y ya tienes una publicación con identidad propia. Nada de contenido de demostración que haya que borrar después.

Características principales:

* Custom Post Type "Reseña" con ficha técnica (formato, estado, año, periodo, entregas) y taxonomías propias de Género y Estudio.
* Sistema de puntuación nativo (0-10) con desglose en cuatro apartados **cuyos nombres se configuran desde Personalizar → Reseñas**: así el mismo tema sirve para videojuegos (Jugabilidad / Gráficos / Sonido / Historia), cine, libros o series, sin tocar código.
* Modo oscuro nativo con selector persistente, sin parpadeo al cargar la página, y modo por defecto configurable (auto / claro / oscuro).
* Panel completo en Apariencia → Personalizar: colores de marca con vista previa en vivo, tipografía, cabecera, portada, tarjetas, publicidad y pie de página.
* Pantalla propia "Revista Koltor Dev → Información del sitio" en el escritorio para los textos del pie de página (presentación y contacto), con columnas de Explorar / Legal / Contacto que no dependen de widgets.
* Dos bloques de Gutenberg propios, sin compilar nada: "Caja de Reseña" y "Grid de Reseñas".
* Compatible con el editor de bloques: theme.json con paleta y tipografías de marca, y estilos de editor a juego con el frontend.
* Slider nativo de portada ("Diapositivas"): imágenes con título, subtítulo y botón opcionales, efecto fade o deslizar, autoplay configurable — sin plugins de slider.
* Sección "Populares del mes" en la portada: slider horizontal con lo más leído del mes, basado en un contador de vistas propio del tema (sin Google Analytics), que descarta rastreadores y se reinicia solo cada mes.
* Redes sociales con icono correcto por plataforma (Facebook, Instagram, X, TikTok, YouTube, Discord, WhatsApp) más un campo libre, y barra flotante opcional con el color de cada marca.
* Selector visual de icono por categoría: 41 iconos SVG de línea fina incrustados en el tema, sin librerías externas.
* Widgets propios con miniatura: Entradas recientes, Comentarios recientes y Categorías con icono.
* Espacios de publicidad configurables (cabecera, barra lateral, dentro del artículo y pie de página).
* Botones de compartir, artículos relacionados, caja de autor y plantilla "Ranking de Reseñas".
* SEO: datos estructurados schema.org para las reseñas y etiquetas Open Graph de respaldo, que se desactivan solas si detectan Yoast, Rank Math o similares.
* Sin dependencias de plugins de terceros para su funcionamiento base.

== Estructura de contenido ==

* "Reseñas" (menú propio en el admin): Custom Post Type con puntuación y ficha técnica. Se clasifican con las taxonomías Género y Estudio.
* "Diapositivas (Hero)" (menú propio en el admin): Custom Post Type para el slider de portada. Cada diapositiva usa el título de la entrada, una Imagen destacada obligatoria, y un subtítulo y botón opcionales. Sin diapositivas publicadas, la portada usa el título e imagen de respaldo de Personalizar.
* "Entradas" normales, organizadas en las categorías Novedades, Análisis y Guías (creadas automáticamente al activar el tema, solo si no existen).

== Instalación ==

1. Sube el .zip desde Apariencia → Temas → Añadir nuevo → Subir tema, y actívalo.
2. Ajustes → Generales: pon el nombre y la descripción corta de tu publicación.
3. Apariencia → Personalizar → panel del tema: sube el logo y ajusta colores, tipografía, portada y pie de página.
4. Personalizar → Reseñas: cambia los nombres de los cuatro apartados de puntuación por los que encajen con tu temática.
5. Apariencia → Menús: crea el "Menú principal" y, si quieres, el "Menú de pie de página" y el "Menú legal (pie de página)".
6. Revista Koltor Dev → Información del sitio: escribe la presentación y los datos de contacto que salen en el pie.
7. Entradas → Categorías: asigna un icono a cada categoría (campo "Icono") si vas a usar el widget de Categorías.
8. (Opcional) Crea Diapositivas para activar el slider de portada, y una página con la plantilla "Ranking de Reseñas".

== Licencia ==

Revista Koltor Dev es software libre y se distribuye bajo los términos de la GNU General Public License v2 o posterior.

Tipografías: "Baloo 2" y "Noto Sans" (Google Fonts, licencia SIL Open Font License), cargadas desde fonts.googleapis.com.

Swiper.js (assets/lib/swiper/), usado para el slider nativo de portada, licencia MIT. https://swiperjs.com/

Iconos: subconjunto de Tabler Icons (https://tabler.io/icons), licencia MIT, incrustados como SVG en el propio tema.

== Changelog ==

= 1.1.0 =
* Puesta al día con las correcciones acumuladas en el tema de revista del que se derivó esta base, publicadas después de que esta copia se archivara: el desbordamiento horizontal en móvil (causado por bloques de código o tablas anchas dentro de artículos, y por el panel del menú), los desplegables de escritorio que no cabían en pantalla (el tercer nivel se salía a cualquier ancho), los objetivos de toque de la cabecera (38→44px), la trampa de foco de teclado en el menú móvil, la marca que no podía encogerse en pantallas pequeñas, y el solape entre la flecha del slider y la barra flotante de redes.
* Sin cambios de comportamiento nuevos: es la misma base reutilizable, ahora sin la deuda de las correcciones que se hicieron después de archivarla.

= 1.0.0 =
* Primera versión. Base reutilizable derivada de un tema de revista ya en producción, con la temática original retirada: identificadores, textos, iconos, taxonomías, categorías y paleta neutros y listos para adaptar.
* La puntuación de las reseñas pasa a tener cuatro apartados con nombre configurable desde Personalizar → Reseñas, en vez de nombres fijos.
* Ficha técnica de reseñas con campos genéricos: Formato (serie, película, videojuego, libro, otro), Estado (en curso, finalizado, anunciado), Año, Periodo y Entregas.
* Paleta azul de marca por defecto, en claro y oscuro.
