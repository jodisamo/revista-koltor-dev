=== Revista Koltor Dev ===
Contributors: koltordev
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.15.0
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
* Slider nativo de portada ("Diapositivas"): imágenes con título, subtítulo, botón y etiquetas de plataforma opcionales, efecto fade o deslizar, autoplay configurable — sin plugins de slider.
* Sección "Populares del mes" en la portada: slider horizontal con lo más leído del mes, basado en un contador de vistas propio del tema (sin Google Analytics), que descarta rastreadores y se reinicia solo cada mes.
* Redes sociales con icono correcto por plataforma (Facebook, Instagram, X, TikTok, YouTube, Discord, WhatsApp) más un campo libre, y barra flotante opcional con el color de cada marca.
* Portada propia por plataforma (/plataforma/playstation/…): últimas noticias, reseñas, avances, reportajes y eventos de esa plataforma, con acceso a sus subplataformas.
* Widgets propios: Entradas recientes y Comentarios recientes con miniatura, y Categorías con el estilo del tema.
* Espacios de publicidad configurables (cabecera, barra lateral, dentro del artículo y pie de página).
* Botones de compartir, artículos relacionados, caja de autor y plantilla "Ranking de Reseñas".
* SEO: datos estructurados schema.org (NewsArticle/Article con migas de pan en los artículos, Review con plataformas en las reseñas, Organization y WebSite en la portada) y etiquetas Open Graph de respaldo. Todo salvo la reseña se desactiva solo si detecta Yoast, Rank Math o similares.
* Sin dependencias de plugins de terceros para su funcionamiento base.

== Estructura de contenido ==

* "Reseñas" (menú propio en el admin): Custom Post Type con puntuación y ficha técnica. Se clasifican con las taxonomías Género y Estudio.
* "Diapositivas (Hero)" (menú propio en el admin): Custom Post Type para el slider de portada. Cada diapositiva usa el título de la entrada, una Imagen destacada obligatoria, y un subtítulo y botón opcionales. Sin diapositivas publicadas, la portada usa el título e imagen de respaldo de Personalizar.
* "Entradas" normales, organizadas por tipo de contenido en las categorías Noticias (Lanzamientos, Industria, Esports), Avances, Reportajes (Opinión, Especiales) y Eventos, creadas automáticamente al activar el tema solo si no existen. La categoría dice QUÉ es el contenido; la plataforma dice PARA QUÉ es.
* "Plataformas" (PlayStation → PS5/PS4, Nintendo → Switch 2/Switch, Xbox → Series X|S/One, PC → Steam, Android): taxonomía aparte, compartida por Entradas y Reseñas; se navega con la barra de iconos de la cabecera.

== Instalación ==

1. Sube el .zip desde Apariencia → Temas → Añadir nuevo → Subir tema, y actívalo.
2. Ajustes → Generales: pon el nombre y la descripción corta de tu publicación.
3. Apariencia → Personalizar → panel del tema (o el acceso directo "Revista Koltor Dev → Personalizar el tema" del escritorio): sube el logo y ajusta colores, tipografía, menús y efectos, portada y pie de página.
4. Personalizar → Reseñas: cambia los nombres de los cuatro apartados de puntuación por los que encajen con tu temática.
5. Apariencia → Menús: crea el "Menú principal" y, si quieres, el "Menú de pie de página" y el "Menú legal (pie de página)".
6. Revista Koltor Dev → Información del sitio: escribe la presentación y los datos de contacto que salen en el pie, y los créditos de los recursos que uses (por ejemplo, los iconos de plataformas de Icons8, cuya licencia gratuita exige un enlace).
7. Entradas → Plataformas: sube el icono de cada plataforma principal y activa la barra en Personalizar → Cabecera.
8. (Opcional) Crea Diapositivas para activar el slider de portada, y una página con la plantilla "Ranking de Reseñas".

== Licencia ==

Revista Koltor Dev es software libre y se distribuye bajo los términos de la GNU General Public License v2 o posterior.

Tipografías: "Baloo 2", "Noto Sans", "Playfair Display", "Lora", "Poppins" e "Inter" (Google Fonts, licencia SIL Open Font License 1.1), incluidas en assets/fonts/ y servidas desde el propio sitio; el texto de sus licencias está en assets/fonts/OFL.txt.

Swiper.js (assets/lib/swiper/), usado para el slider nativo de portada, licencia MIT. https://swiperjs.com/

Iconos de redes sociales: Simple Icons (https://simpleicons.org), licencia CC0-1.0 (dominio público), incrustados como SVG en includes/core/template-tags.php. Los logotipos siguen siendo marcas de sus respectivos dueños.

== Changelog ==

= 1.15.0 =
* Nuevo: "Revista Koltor Dev → Exportar / Importar". Lleva la configuración de un sitio a otro (por ejemplo, del sitio local de pruebas a producción) sin tocar el contenido: categorías y subcategorías, plataformas con sus iconos (dentro del archivo) y colores, géneros, menús, la página Tops, la barra lateral, los ajustes del Personalizador e Información del sitio. Importar es en dos pasos (vista previa de cada cambio y confirmación); los slugs de versiones anteriores se renombran en vez de duplicarse (Novedades → Noticias, Análisis → Reportajes, Nintendo Switch → Nintendo, Móvil → Android), así que las entradas siguen en su categoría. Nunca borra nada, no copia el modo construcción y guarda una copia de los ajustes para el botón "Restaurar". Cada ajuste pasa por el mismo saneado que en el Personalizador y los iconos se validan como imagen por su contenido.
* Nuevo: etiquetas de plataforma en las diapositivas de portada. Encima del título se muestra el icono y el nombre de cada plataforma sobre el color de su marca. Se marcan en la propia diapositiva (Plataformas) o, si no se marca ninguna y el botón enlaza a una entrada o reseña del sitio, se usan las de esa entrada.

= 1.14.0 =
* Datos estructurados para Google Noticias, Discover y los resultados de artículo: cada entrada lleva NewsArticle (Noticias, Eventos), OpinionNewsArticle (Opinión) o Article (Reportajes, Avances…) con titular, imágenes (original y 1200px), fechas de publicación y modificación, autor con su página, editor, sección, etiquetas y plataformas; más sus migas de pan (BreadcrumbList).
* Portada: Organization (nombre, logo y redes sociales) y WebSite, que alimentan el panel de marca de Google.
* Reseñas: el Review incluye las plataformas del juego (gamePlatform), la página del autor, el logo del editor, la fecha de modificación y el idioma. Nuevo campo "Juego reseñado" en la ficha, para que Google asocie la nota al juego y no al titular del artículo.
* Si una entrada no tiene autor, firma la publicación (Google exige autor en los artículos).
* Con un plugin de SEO activo (Yoast, Rank Math…), el tema no duplica los datos de artículo ni de portada; solo mantiene el de las reseñas, que esos plugins no conocen. Con el modo construcción activo no se imprime ninguno.

= 1.13.0 =
Rendimiento (medido en la portada emulando un móvil con 4G: de 705 KB a 145 KB descargados, -80%, y ninguna petición a servidores externos):
* El logo se descargaba en su copia de 1536px (646 KB, el 92% de la portada) porque WordPress anunciaba que ocupaba todo el ancho de la pantalla. Ahora "sizes" lleva su ancho real en la cabecera y hay un tamaño propio para el logo (kdv-logo, 180px de alto): 27 KB.
* Nuevo: las copias de las imágenes que se suban se generan en WebP (Personalizar → Revista Koltor Dev → Rendimiento, activado por defecto). El archivo subido se conserva como imagen original. Para las imágenes ya subidas, regenera las miniaturas.
* Tipografías servidas desde el propio tema (assets/fonts/) en vez de Google Fonts: sin las dos conexiones a fonts.googleapis.com y fonts.gstatic.com antes de pintar el texto, sin enviar la IP de cada visitante a Google (RGPD), y con precarga de las dos fuentes principales. Solo alfabetos latin y latin-ext.
* El zip de producción incluye main.min.css (97 KB -> 60 KB; 25 -> 11 KB comprimido), generado al empaquetar con scripts/minify-css.py y verificado regla a regla; en el repositorio se sigue editando main.css.
* Las diapositivas de portada usan la copia de 1200px (kdv-hero) como fondo en vez de la imagen a tamaño completo.
* "Populares del mes" se completa con las entradas más recientes cuando las que tienen visitas este mes no llenan el carrusel (antes, con una sola entrada con visitas, la sección mostraba solo esa).

= 1.12.3 =
Revisión del móvil (auditoría medida con Chrome emulando un teléfono de 390px en portada, artículo, reseña, portada de plataforma, categoría, reseñas, búsqueda, 404 y Tops; ninguna página se desborda en horizontal):
* Corregido: el buscador de la 404 y de "Sin resultados" salía con el aspecto por defecto del navegador (campo y botón de 24px). Ahora tiene el estilo del tema, con 46px de alto.
* Toda la tarjeta lleva al artículo, no solo la línea del título (unos 19px en el móvil); la etiqueta de tipo y los distintivos de plataforma siguen siendo enlaces propios por encima. Lo mismo en las filas de "Lo último" de la barra lateral.
* Reseñas sin imagen destacada: fondo con el color y el icono de su plataforma en vez de un recuadro vacío (300px en el móvil).
* Distintivos de plataforma con el icono real en vez de una silueta (el de PC quedaba como un cuadrado relleno).
* Objetivos de toque de 44px: botones de compartir, "Ver todas" y accesos de la portada de plataforma; distintivos de 32px en el móvil.
* Texto mínimo de 12px: etiquetas de tipo y fechas de la barra lateral.
* Móvil: menos espacio vertical entre secciones y en la portada, y sin el lema junto al logo (salía cortado con "…").
* Corregido: la fecha de la tarjeta terminaba en "·" cuando la entrada no tenía autor.

= 1.12.2 =
Correcciones de seguridad (auditoría con ataques simulados sobre el sitio local):
* Corregido: "?plataforma[]=x" (el parámetro como lista en vez de texto) provocaba un error fatal de PHP (500) en cualquier página del sitio, porque la barra de plataformas lee ese parámetro en todas.
* Corregido: con el modo construcción activo, al pedir la URL de un artículo la pantalla de mantenimiento incluía en <head> su título, canonical, enlace corto con el ID y enlaces de feed, oEmbed y API: se podía confirmar que una reseña con embargo existía y leer su título. Ahora la consulta se vacía y esas etiquetas no se imprimen.
* Corregido: el widget "Koltor Dev: Comentarios recientes" mostraba el texto de comentarios de entradas protegidas con contraseña a cualquier visitante.
* Corregido: un autor (o un colaborador, como borrador) podía crear Diapositivas de portada y anuncios de la Cinta, que salen en todo el sitio. Ahora requieren permisos de página (editor o administrador).
* Contador de "Populares del mes": una visita por persona y artículo cada 6 horas (hash de la IP con sal secreta, nunca la IP en claro), para que no se pueda subir un artículo al ranking con un bucle de peticiones; y suma atómica en la base de datos (con visitas simultáneas se perdía la mitad).
* Caja de Reseña: nota limitada a 0-10 y sin <img src=""> cuando la URL de la imagen no es válida.

= 1.12.1 =
* Corregido (redes sociales): los campos de Personalizar → Redes sociales solo funcionaban con la URL completa. "@usuario", el usuario a secas o un número de WhatsApp se guardaban como enlaces rotos ("http://@entrepixeles", "http://573001234567"). Ahora cada campo acepta URL (con o sin https://), @usuario, usuario o, en WhatsApp, el número con código de país, y arma el enlace correcto en https (x.com/…, wa.me/…, tiktok.com/@…). Los valores ya guardados rotos se reparan solos al mostrarse. El campo libre "otras redes" pasa por la misma normalización.
* Corregido: con muchas redes (7 o más), la barra flotante, centrada en la ventana, subía hasta esconder su primer icono detrás de la cabecera fija. Ahora se centra en el espacio bajo la cabecera y, si no caben todas, la lista se desplaza.
* Iconos de redes reconocibles: los logotipos de Simple Icons (CC0) sustituyen a los trazos dibujados a mano (la "X" era una cruz que parecía un botón de cerrar; TikTok y Discord apenas se reconocían). "Copiar enlace" usa ahora una cadena en vez de un círculo con "+".

= 1.12.0 =
* Nueva sección Personalizar → Revista Koltor Dev → "Barra lateral y widgets": en qué páginas se muestra (artículos, reseñas, archivos, búsqueda), barra fija al hacer scroll, caja de los widgets (tarjeta / borde / plano), estilo, tamaño y color de los títulos, esquinas y separadores; todo con vista previa en vivo.
* Nueva pantalla "Revista Koltor Dev → Barra lateral" en el escritorio: muestra los widgets actuales y monta en un clic la barra recomendada (buscador, Lo último, Publicidad, Secciones, Comentarios recientes, en español). Los widgets anteriores pasan a "Widgets inactivos", no se borran.
* Nuevo widget "Koltor Dev: Publicidad": un anuncio en cualquier posición de la barra lateral (arriba, entre dos widgets…), repetible, con el código de Personalizar → Publicidad o uno propio. Si hay uno en la barra, el anuncio fijo de arriba se omite para que no salga dos veces.
* Los widgets de bloque de WordPress (Buscar, Entradas y Comentarios recientes, Archivos, Categorías) toman el estilo del tema: títulos del tamaño de widget (antes salían como títulos de artículo), listas numeradas sin sangría, buscador sin la etiqueta suelta y con el botón del color del sitio.
* La barra lateral fija solo se fija si cabe en la ventana: una barra más alta dejaba sus últimos widgets inalcanzables.
* Sin barra lateral (vacía o desactivada), el contenido se centra al ancho de lectura en vez de reservar una columna vacía de 320px.
* Corregido: en el formulario de comentarios, la casilla "Guarda mi nombre…" salía centrada sola en su línea y el botón "Publicar el comentario" (un input type="submit") salía con aspecto de campo de texto.
* Corregido: "Secciones" (widget de categorías) con el relleno duplicado, y el extracto de "Comentarios recientes" en negrita.

= 1.11.0 =
* Cabecera fija como un solo bloque: logo, menú y barra de plataformas van juntos dentro de <header>. Antes la barra de plataformas quedaba fuera, pasaba por encima de la cabecera fija al hacer scroll y tapaba el lema.
* Al hacer scroll la cabecera se compacta (logo algo más pequeño, sin el lema, menos relleno) y recupera su tamaño al volver arriba, con histéresis para que no "tiemble" en el límite.
* Corregido: con sesión iniciada, la cabecera fija quedaba debajo de la barra de administración de WordPress y el logo salía cortado. Ahora se coloca debajo de ella (32px; 46px en pantallas medianas).
* Corregido: en el móvil, tras hacer scroll, el panel del menú se abría recortado a la altura de la cabecera (el efecto cristal, backdrop-filter, cambia la referencia de los elementos fijos). Con el menú abierto el efecto se desactiva.
* Móvil: la barra de plataformas muestra solo los iconos, en una fila; los nombres siguen disponibles para lectores de pantalla y en el desplegable, que en los iconos de la derecha se abre hacia la izquierda.
* "Ver todas" / "Ver más" pasan a ser botones en píldora con el color de la sección (el de la marca en la portada de plataforma) y una flecha que avanza al pasar el ratón; en modo oscuro el texto del botón activo es oscuro para que se lea.

= 1.10.1 =
* Corregido: la portada de plataforma y el archivo de Reseñas usaban el ancho de lectura de 780px (pensado para páginas de texto); ahora ocupan todo el contenedor, como la portada, y la rejilla muestra sus tres columnas.
* Portada de plataforma: cabecera como banda ancha con el icono y el título más grandes (sin la barra lateral de los títulos, el icono ya identifica la plataforma); las barras de los títulos de sección y los "Ver todas" usan el color de la marca.
* Modo oscuro: el botón "Tops de …" pasa a translúcido con borde y texto en el color de la marca, para que destaque sobre el fondo oscuro.

= 1.10.0 =
* Color de marca por plataforma (Entradas → Plataformas → Color, con el selector de color de WordPress). De partida: PlayStation #0070D1, Nintendo #E60012, Xbox #107C10, Android #3DDC84, Steam #66C0F4; PC usa el color del resalte. Una subplataforma sin color usa el de su principal.
* Barra de plataformas: cada plataforma se resalta con su color (fondo, texto y el propio icono teñido con máscara CSS), un 15% más grande, con un indicador que se desliza bajo el elemento señalado y toma su color, y la plataforma de la página actual encendida (portada de la plataforma, sus subplataformas o un listado con ?plataforma=).
* Nuevo "Inicio" al principio de la barra: casa dibujada por el tema cuyo tejado se levanta y cuya ventana se enciende al pasar el ratón; activa en la portada.
* Distintivos de plataforma en las tarjetas: junto a la etiqueta de tipo (que sigue diciendo QUÉ es), el icono de cada plataforma principal en su color, enlazando a su portada; "PlayStation (PS5)" al pasar el ratón; como mucho 3 y "+N".
* Portada de plataforma con franja y velo del color de la marca.
* Contraste: los colores de marca se oscurecen (modo claro) o aclaran (modo oscuro) para el texto; todos superan 4,5:1 en ambos modos.
* Corregido: el botón "Tops de …" de la portada de plataforma salía sin su color de acento (lo pisaba el estilo base de las píldoras).

= 1.9.0 =
* Nueva sección Personalizar → Revista Koltor Dev → "Menús y efectos": color y velocidad del resalte al pasar el ratón, fondo de la barra de plataformas (sin fondo / suave / intenso) y efecto del icono (levantarse / agrandarse / ninguno), con vista previa en vivo. Los ajustes de menú que estaban en "Cabecera" (estilo de resaltado, velocidad, curva y aparición de los desplegables) se movieron aquí conservando lo ya guardado.
* Resalte más visible: la barra de plataformas usa un toque del color del resalte como fondo (antes un gris casi invisible) y su icono reacciona; el mismo efecto se ve al llegar con el teclado, y con "reducir movimiento" solo cambian los colores.
* El menú principal usa por defecto el subrayado animado (antes "solo color"). Los sitios que ya habían elegido un estilo lo conservan.
* Accesos directos en el escritorio, menú "Revista Koltor Dev": Información del sitio, Personalizar el tema, Menús y efectos, Menús de navegación y Plataformas.

= 1.8.0 =
* Nuevo campo "Créditos" en Revista Koltor Dev → Información del sitio: una línea pequeña bajo el copyright para agradecer los recursos que usa el sitio (iconos, fotos, tipografías). Admite enlaces, que es lo que exigen licencias gratuitas como la de Icons8. Solo se permiten enlaces y énfasis (strong/em); cualquier otra etiqueta o atributo se elimina al guardar y al mostrar.

= 1.7.2 =
* La plataforma "Móvil" pasa a llamarse "Android" (slug android), a juego con su icono. Barra: PlayStation · Nintendo · Xbox · PC · Android.

= 1.7.1 =
* Orden de la barra de plataformas: PlayStation · Nintendo · Xbox · PC · Móvil.
* Steam como subplataforma de PC (no como categoría ni como icono propio de la barra): filtrar por PC incluye lo marcado como Steam, y Steam tiene su propia portada con enlace a "Todo PC".
* Los iconos de plataforma se invierten a blanco en modo oscuro (son glifos monocromos oscuros que desaparecían sobre el fondo oscuro).
* En la portada de plataforma el icono nunca se amplía por encima de su tamaño real (hasta 88px), para que un icono pequeño no se vea borroso.

= 1.7.0 =
* Nueva portada por plataforma (/plataforma/playstation/, /plataforma/ps5/…): cabecera con el icono y el nombre, accesos a sus subplataformas y a sus Tops, y un bloque por tipo de contenido (Noticias, Reseñas, Avances, Reportajes, Eventos) con sus 3 últimas piezas y "Ver todas →" hacia el archivo ya filtrado. Incluye lo marcado en sus subplataformas; los bloques vacíos no se muestran.
* El desplegable de cada icono de la barra empieza por "Todo PlayStation" (la portada de esa plataforma).
* Retirados los iconos de categoría (selector en Entradas → Categorías y librería SVG): en Entre Píxeles los iconos son de las plataformas y tener los dos confundía. El widget "Koltor Dev: Categorías" se conserva, sin icono.

= 1.6.0 =
* Navegación de portal de noticias: la categoría dice QUÉ es el contenido y la plataforma PARA QUÉ es, sin categorías por plataforma. Categorías de partida: Noticias (Lanzamientos, Industria, Esports), Avances, Reportajes (Opinión, Especiales) y Eventos. Se retira Guías.
* La taxonomía Plataforma admite subplataformas (PlayStation → PS5, PS4; Xbox → Series X|S, One; Nintendo → Switch 2, Switch). La barra solo muestra las principales, en el orden PlayStation · Xbox · Nintendo · PC · Móvil, y filtrar por una principal incluye sus subplataformas.
* El desplegable de cada plataforma lleva a Noticias, Avances, Reseñas y Tops, con el nombre actual de cada categoría.
* El tema busca sus categorías por slug (noticias, avances, reportajes) en vez de por nombre: renombrarlas en el escritorio ya no rompe la barra ni las secciones de portada.
* Secciones 3-5 de la portada por defecto: Noticias, Reportajes, Avances.
* 12 géneros de partida para las reseñas (Acción, Aventura, RPG, Shooter, Estrategia, Deportes, Carreras, Lucha, Plataformas, Terror, Simulación, Indie).
* Redirección 301 de las direcciones antiguas /category/novedades/ → /category/noticias/ y /category/analisis/ → /category/reportajes/, conservando paginación y filtros.

= 1.5.0 =
* Pantalla de acceso (wp-login.php) con la marca del sitio: el logo de Personalizar → Identidad del sitio en lugar de la "W" de WordPress (o el nombre del sitio si no hay logo), el botón "Acceder" con el color primario y el logo enlazando a la portada.
* Corregido (SEO): las entradas, páginas y reseñas tenían dos etiquetas canonical (la de WordPress y la del tema). Ahora el tema solo la añade donde WordPress no la pone, y ninguna en búsquedas ni en la 404 (antes la de la búsqueda apuntaba a la portada).
* Corregido: el aviso "Filtrado por: X — quitar filtro" no salía en los archivos de categoría (Novedades, Análisis, Guías), justo los destinos de la barra de plataformas.
* Corregido: la portada pisaba la variable global $posts de WordPress.
* Corregido: una red social en dropbox.com, netflix.com o similares salía con el icono de X.
* Corregido: el tiempo de lectura contaba cada palabra con tilde o ñ como dos y salía inflado en español.
* Accesibilidad: el enlace "Ir al contenido" ahora se ve al recibir el foco; el slider de portada tiene botón de pausa y arranca detenido con "reducir movimiento"; la copia oculta de la cinta de anuncios ya no se recorre con el tabulador; el buscador ya no repite id cuando sale dos veces; un solo <h1> en el slider; la hamburguesa apunta a su <nav>; el botón de modo oscuro anuncia su estado (aria-pressed).
* Restos de la temática anterior: el formato por defecto de una reseña nueva es "Videojuego", la ficha dice "entregas" en vez de "eps." y los estilos del editor usan la paleta actual.

= 1.4.0 =
* Cada icono de la barra de plataformas ahora abre un desplegable propio hacia Novedades, Análisis, Guías, Reseñas y Tops (Ranking), filtrados por esa plataforma (?plataforma=slug sobre las categorías y consultas ya existentes). Deliberadamente NO se crean categorías ni términos nuevos por plataforma -- el filtro cruza kdv_plataforma con la categoría de cada archivo ya existente, así que cada artículo se etiqueta una sola vez, no dos. Un enlace se omite en silencio si su destino no existe todavía (p. ej. "Tops" antes de crear la página de Ranking).
* Aviso "Filtrado por: X — quitar filtro" en los archivos de categoría, el de Reseñas y el Ranking cuando el filtro de plataforma está activo.
* Desplegable con JS propio (independiente del menú principal y del buscador), vanilla, con cierre al hacer clic fuera o pulsar Escape.

= 1.3.0 =
* Nueva taxonomía "Plataforma" (PC, PlayStation, Xbox, Nintendo Switch, Móvil, con 5 de partida), compartida entre Reseñas y Entradas -- un juego puede tener varias a la vez. Icono por plataforma subido por quien administra el sitio desde su propia biblioteca de medios (menú "Plataformas" del escritorio); el tema no incluye ni genera logos de marcas ajenas.
* Nueva barra de plataformas (Personalizar → Cabecera → "Mostrar barra de plataformas"), fila de iconos enlazados debajo de la cabecera. Deliberadamente independiente del menú principal, sin tocar su CSS ni su JS. Se oculta sola si ninguna plataforma tiene icono subido.

= 1.2.1 =
* Corregido (seguridad): el modo construcción no bloqueaba la API REST (/wp-json/...). Un visitante veía la pantalla de "en construcción" en el navegador, pero cualquiera podía seguir pidiendo /wp-json/wp/v2/posts, /wp-json/wp/v2/kdv_resena, /wp-json/wp/v2/kdv_slide, etc. y recibir el contenido real completo en JSON, sin pasar por el bloqueo. Ahora se filtra también a través de "rest_authentication_errors" con el mismo criterio que la pantalla normal (503, y quien tenga sesión iniciada con permiso de editar contenido sigue pasando).

= 1.2.0 =
* Cinta de anuncios configurable (Personalizar → Cinta de anuncios + menú "Cinta de anuncios" del escritorio): franja horizontal con desplazamiento continuo sobre la cabecera, para anuncios de eventos próximos. Respeta prefers-reduced-motion y se pausa al pasar el cursor o tabular dentro.
* Modo construcción (Personalizar → Modo construcción): pantalla de "en construcción" para todo el frontend mientras el sitio no está listo, con mensaje e ilustración configurables. Devuelve 503 + Retry-After; quien tenga sesión iniciada con permiso de editar sigue viendo el sitio real.
* Menú de escritorio: velocidad, curva de easing (3 opciones curadas) y forma de aparición (aparecer / deslizar) configurables desde Personalizar → Cabecera. El panel móvil no cambia -- sigue con clip-path a propósito.
* Librería de iconos de categoría retematizada a videojuegos (13 etiquetas reconvertidas + 4 iconos nuevos genuinos: ajedrez, diana, pieza de construcción, monedas), manteniendo los slugs para no romper iconos ya asignados a categorías.
* Corregido: la página 404 y un icono de categoría conservaban una referencia temática de anime ("isekai") que había sobrevivido al retiro de identificadores documentado en la 1.0.0.
* Corregido: los 4 nombres de puntuación por defecto seguían siendo los del tema original (Historia/Guion, Apartado visual, Sonido, Personajes) en vez de los genéricos para videojuegos (Jugabilidad, Gráficos, Sonido, Historia).
* Corregido: el título "Sobre el sitio" y el texto de copyright del pie tenían el nombre del tema fijo en vez de tomar el nombre real del sitio (Ajustes → Generales), así que aparecía "Revista Koltor Dev" en el pie de cualquier sitio que usara esta base sin cambiarlo a mano.

= 1.1.0 =
* Puesta al día con las correcciones acumuladas en el tema de revista del que se derivó esta base, publicadas después de que esta copia se archivara: el desbordamiento horizontal en móvil (causado por bloques de código o tablas anchas dentro de artículos, y por el panel del menú), los desplegables de escritorio que no cabían en pantalla (el tercer nivel se salía a cualquier ancho), los objetivos de toque de la cabecera (38→44px), la trampa de foco de teclado en el menú móvil, la marca que no podía encogerse en pantallas pequeñas, y el solape entre la flecha del slider y la barra flotante de redes.
* Sin cambios de comportamiento nuevos: es la misma base reutilizable, ahora sin la deuda de las correcciones que se hicieron después de archivarla.

= 1.0.0 =
* Primera versión. Base reutilizable derivada de un tema de revista ya en producción, con la temática original retirada: identificadores, textos, iconos, taxonomías, categorías y paleta neutros y listos para adaptar.
* La puntuación de las reseñas pasa a tener cuatro apartados con nombre configurable desde Personalizar → Reseñas, en vez de nombres fijos.
* Ficha técnica de reseñas con campos genéricos: Formato (serie, película, videojuego, libro, otro), Estado (en curso, finalizado, anunciado), Año, Periodo y Entregas.
* Paleta azul de marca por defecto, en claro y oscuro.
