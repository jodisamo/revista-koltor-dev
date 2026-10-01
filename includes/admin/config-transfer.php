<?php
/**
 * "Revista Koltor Dev → Exportar / Importar": lleva la CONFIGURACIÓN del sitio
 * de un WordPress a otro (del sitio local de desarrollo a producción) sin
 * tocar el contenido.
 *
 * El zip del tema solo lleva código; la configuración vive en la base de
 * datos: categorías, plataformas (con sus iconos y colores), géneros, menú
 * principal, páginas con plantilla del tema (Tops), barra lateral, ajustes
 * del Personalizador e Información del sitio. Copiar la base de datos entera
 * no sirve: borraría los artículos reales de producción.
 *
 * Qué NO viaja, a propósito: entradas, reseñas, comentarios, usuarios,
 * logo e imágenes de la biblioteca (salvo los iconos de plataforma), y el
 * interruptor del modo construcción (para no apagar ni encender un sitio
 * en producción por accidente).
 *
 * Importar es en dos pasos: subir el archivo muestra qué va a cambiar; solo
 * al confirmar se aplica. Antes de aplicar se guarda una copia de los
 * ajustes del Personalizador y de la Información del sitio, que se puede
 * restaurar con un botón.
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Versión del formato del archivo. */
const KDV_CONFIG_FORMAT_VERSION = 1;

/**
 * Renombres de slug de versiones anteriores del tema: si en el sitio de
 * destino existe el slug viejo y no el nuevo, se RENOMBRA el término (sus
 * entradas siguen en él) en vez de crear uno nuevo al lado.
 *
 * @return array
 */
function kdv_config_slug_renames() {
	return [
		'category'       => [
			'noticias'   => 'novedades',
			'reportajes' => 'analisis',
			'general'    => 'uncategorized',
		],
		'kdv_plataforma' => [
			'nintendo' => 'nintendo-switch',
			'android'  => 'movil',
		],
	];
}

/**
 * Ajustes del Personalizador que no viajan: dependen de archivos o IDs de
 * ese sitio, o son peligrosos de copiar (modo construcción).
 *
 * @return string[]
 */
function kdv_config_excluded_mods() {
	return [ 'kdv_maintenance_enabled', 'custom_logo', 'custom_css_post_id', 'nav_menu_locations', 'sidebars_widgets' ];
}

function kdv_register_config_transfer_page() {
	add_submenu_page(
		'kdv-site-info',
		__( 'Koltor Dev — Exportar / Importar', 'revista-koltor-dev' ),
		__( 'Exportar / Importar', 'revista-koltor-dev' ),
		'manage_options',
		'kdv-config-transfer',
		'kdv_render_config_transfer_page'
	);
}
add_action( 'admin_menu', 'kdv_register_config_transfer_page', 12 );

/* =====================================================================
 * EXPORTAR
 * =================================================================== */

/**
 * Árbol de una taxonomía como lista ordenada (padres antes que hijos).
 *
 * @param string $taxonomy Taxonomía.
 * @return array[]
 */
function kdv_config_export_terms( $taxonomy ) {
	$terms = get_terms( [ 'taxonomy' => $taxonomy, 'hide_empty' => false, 'orderby' => 'term_id' ] );
	if ( is_wp_error( $terms ) ) {
		return [];
	}
	$by_id = [];
	foreach ( $terms as $t ) {
		$by_id[ $t->term_id ] = $t;
	}
	$out = [];
	$add = function( $term ) use ( &$add, &$out, $by_id ) {
		if ( isset( $out[ $term->slug ] ) ) {
			return;
		}
		if ( $term->parent && isset( $by_id[ $term->parent ] ) ) {
			$add( $by_id[ $term->parent ] );
		}
		$out[ $term->slug ] = [
			'slug'        => $term->slug,
			'name'        => $term->name,
			'description' => $term->description,
			'parent'      => ( $term->parent && isset( $by_id[ $term->parent ] ) ) ? $by_id[ $term->parent ]->slug : '',
		];
	};
	foreach ( $terms as $t ) {
		$add( $t );
	}
	return array_values( $out );
}

/**
 * Todo el paquete de configuración del sitio actual.
 *
 * @return array
 */
function kdv_config_build_export() {
	// Ajustes del Personalizador del tema (salvo los excluidos y los que
	// apuntan a archivos de este sitio, que no existirían en el otro).
	$mods = [];
	foreach ( (array) get_theme_mods() as $key => $value ) {
		if ( in_array( $key, kdv_config_excluded_mods(), true ) || 0 !== strpos( $key, 'kdv_' ) ) {
			continue;
		}
		if ( is_string( $value ) && false !== strpos( $value, '/wp-content/uploads/' ) ) {
			continue;
		}
		$mods[ $key ] = $value;
	}

	// Plataformas, con color e icono (la imagen va dentro del archivo).
	$platforms = kdv_config_export_terms( 'kdv_plataforma' );
	foreach ( $platforms as &$p ) {
		$term       = get_term_by( 'slug', $p['slug'], 'kdv_plataforma' );
		$p['color'] = (string) get_term_meta( $term->term_id, 'kdv_platform_color', true );
		$icon_id    = absint( get_term_meta( $term->term_id, 'kdv_platform_icon', true ) );
		$file       = $icon_id ? wp_get_original_image_path( $icon_id ) : '';
		$file       = $file && is_readable( $file ) ? $file : ( $icon_id ? get_attached_file( $icon_id ) : '' );
		if ( $file && is_readable( $file ) && filesize( $file ) < 512 * KB_IN_BYTES ) {
			$p['icon'] = [
				'filename' => basename( $file ),
				'mime'     => wp_check_filetype( $file )['type'],
				'data'     => base64_encode( (string) file_get_contents( $file ) ), // phpcs:ignore WordPress.WP.AlternativeFunctions, WordPress.PHP.DiscouragedPHPFunctions -- icono local de la biblioteca de medios.
			];
		}
	}
	unset( $p );

	// Páginas que usan una plantilla del tema (p. ej. "Tops" con el Ranking).
	$pages = [];
	foreach ( get_pages( [ 'meta_key' => '_wp_page_template', 'meta_value' => 'page-templates/ranking.php' ] ) as $page ) { // phpcs:ignore WordPress.DB.SlowDBQuery
		$pages[] = [ 'slug' => $page->post_name, 'title' => $page->post_title, 'template' => 'page-templates/ranking.php' ];
	}

	// Menús asignados a las ubicaciones del tema.
	$menus     = [];
	$locations = get_nav_menu_locations();
	foreach ( [ 'primary', 'footer', 'footer_legal' ] as $location ) {
		if ( empty( $locations[ $location ] ) ) {
			continue;
		}
		$menu = wp_get_nav_menu_object( $locations[ $location ] );
		if ( ! $menu ) {
			continue;
		}
		$items    = wp_get_nav_menu_items( $menu->term_id ) ?: [];
		$index_of = [];
		$list     = [];
		foreach ( $items as $i => $item ) {
			$index_of[ $item->ID ] = $i;
			$entry = [ 'title' => $item->title, 'parent' => $item->menu_item_parent ? ( $index_of[ $item->menu_item_parent ] ?? null ) : null ];
			if ( 'taxonomy' === $item->type ) {
				$term  = get_term( $item->object_id, $item->object );
				$entry += [ 'kind' => 'term', 'taxonomy' => $item->object, 'slug' => ( $term && ! is_wp_error( $term ) ) ? $term->slug : '' ];
			} elseif ( 'post_type' === $item->type && 'page' === $item->object ) {
				$entry += [ 'kind' => 'page', 'slug' => get_post_field( 'post_name', $item->object_id ) ];
			} elseif ( 'post_type_archive' === $item->type ) {
				$entry += [ 'kind' => 'archive', 'post_type' => $item->object ];
			} else {
				$entry += [ 'kind' => 'custom', 'url' => $item->url ];
			}
			$list[] = $entry;
		}
		$menus[ $location ] = [ 'name' => $menu->name, 'items' => $list ];
	}

	// Barra lateral: widgets de la barra principal con sus ajustes.
	$sidebar = [];
	foreach ( wp_get_sidebars_widgets()['sidebar-main'] ?? [] as $widget_id ) {
		if ( ! preg_match( '/^(.+)-(\d+)$/', $widget_id, $m ) ) {
			continue;
		}
		$instances = get_option( 'widget_' . $m[1], [] );
		if ( isset( $instances[ (int) $m[2] ] ) ) {
			$sidebar[] = [ 'id_base' => $m[1], 'settings' => $instances[ (int) $m[2] ] ];
		}
	}

	$default_cat = get_term( (int) get_option( 'default_category' ), 'category' );

	return [
		'format'           => 'kdv-config',
		'format_version'   => KDV_CONFIG_FORMAT_VERSION,
		'theme_version'    => KDV_THEME_VERSION,
		'source'           => home_url( '/' ),
		'generated'        => gmdate( 'c' ),
		'theme_mods'       => $mods,
		'site_info'        => get_option( 'kdv_site_info', [] ),
		'categories'       => kdv_config_export_terms( 'category' ),
		'default_category' => ( $default_cat && ! is_wp_error( $default_cat ) ) ? $default_cat->slug : '',
		'genres'           => kdv_config_export_terms( 'kdv_genero' ),
		'platforms'        => $platforms,
		'pages'            => $pages,
		'menus'            => $menus,
		'sidebar'          => $sidebar,
	];
}

function kdv_handle_config_export() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'No tienes permiso para exportar la configuración.', 'revista-koltor-dev' ), 403 );
	}
	check_admin_referer( 'kdv_config_export' );

	$name = sanitize_file_name( wp_parse_url( home_url(), PHP_URL_HOST ) . '-configuracion-' . gmdate( 'Y-m-d' ) . '.json' );
	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . $name . '"' );
	echo wp_json_encode( kdv_config_build_export(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); // phpcs:ignore WordPress.Security.EscapeOutput -- descarga JSON, no HTML.
	exit;
}
add_action( 'admin_post_kdv_config_export', 'kdv_handle_config_export' );

/* =====================================================================
 * IMPORTAR
 * =================================================================== */

/**
 * Valida la estructura del archivo subido.
 *
 * @param mixed $data JSON decodificado.
 * @return true|WP_Error
 */
function kdv_config_validate( $data ) {
	if ( ! is_array( $data ) || ( $data['format'] ?? '' ) !== 'kdv-config' ) {
		return new WP_Error( 'kdv_config_format', __( 'El archivo no es una configuración exportada desde "Revista Koltor Dev → Exportar / Importar".', 'revista-koltor-dev' ) );
	}
	if ( (int) ( $data['format_version'] ?? 0 ) > KDV_CONFIG_FORMAT_VERSION ) {
		return new WP_Error( 'kdv_config_version', __( 'El archivo viene de una versión más nueva del tema. Actualiza el tema en este sitio antes de importarlo.', 'revista-koltor-dev' ) );
	}
	foreach ( [ 'theme_mods', 'site_info', 'categories', 'genres', 'platforms', 'pages', 'menus', 'sidebar' ] as $key ) {
		if ( isset( $data[ $key ] ) && ! is_array( $data[ $key ] ) ) {
			return new WP_Error( 'kdv_config_shape', __( 'El archivo está dañado o incompleto.', 'revista-koltor-dev' ) );
		}
	}
	return true;
}

/**
 * Término de destino para un slug: el que ya existe con ese slug, o el del
 * slug viejo que hay que renombrar. [ WP_Term|null, accion ].
 *
 * @param string $taxonomy Taxonomía.
 * @param string $slug     Slug buscado.
 * @return array
 */
function kdv_config_find_term( $taxonomy, $slug ) {
	$term = get_term_by( 'slug', $slug, $taxonomy );
	if ( $term ) {
		return [ $term, 'update' ];
	}
	$old = kdv_config_slug_renames()[ $taxonomy ][ $slug ] ?? '';
	if ( $old && ( $term = get_term_by( 'slug', $old, $taxonomy ) ) ) {
		return [ $term, 'rename' ];
	}
	return [ null, 'create' ];
}

/**
 * Aplica (o simula, con $dry_run) el paquete. Devuelve el registro de
 * acciones, en texto para la persona que importa.
 *
 * @param array $data    Paquete validado.
 * @param bool  $dry_run true = solo describir lo que se haría.
 * @return string[]
 */
function kdv_config_apply( array $data, $dry_run ) {
	$log = [];
	$src = untrailingslashit( (string) ( $data['source'] ?? '' ) );
	$dst = untrailingslashit( home_url( '/' ) );
	// Las URLs del sitio de origen pasan a ser del de destino.
	$localize = function( $value ) use ( &$localize, $src, $dst ) {
		if ( is_string( $value ) && $src && $src !== $dst ) {
			return str_replace( $src, $dst, $value );
		}
		return is_array( $value ) ? array_map( $localize, $value ) : $value;
	};

	// 1. Taxonomías: categorías, géneros y plataformas (padres primero).
	foreach ( [ 'category' => 'categories', 'kdv_genero' => 'genres', 'kdv_plataforma' => 'platforms' ] as $taxonomy => $key ) {
		$label = [ 'category' => __( 'Categoría', 'revista-koltor-dev' ), 'kdv_genero' => __( 'Género', 'revista-koltor-dev' ), 'kdv_plataforma' => __( 'Plataforma', 'revista-koltor-dev' ) ][ $taxonomy ];
		foreach ( (array) ( $data[ $key ] ?? [] ) as $t ) {
			$slug = sanitize_title( $t['slug'] ?? '' );
			$name = sanitize_text_field( $t['name'] ?? '' );
			if ( '' === $slug || '' === $name ) {
				continue;
			}
			[ $term, $action ] = kdv_config_find_term( $taxonomy, $slug );
			$parent_id = 0;
			if ( ! empty( $t['parent'] ) ) {
				[ $parent ] = kdv_config_find_term( $taxonomy, sanitize_title( $t['parent'] ) );
				$parent_id  = $parent ? $parent->term_id : 0;
			}
			$args = [ 'name' => $name, 'slug' => $slug, 'parent' => $parent_id, 'description' => wp_kses_post( $t['description'] ?? '' ) ];

			if ( 'rename' === $action ) {
				$log[] = sprintf(
					/* translators: 1: tipo, 2: nombre anterior, 3: nombre nuevo, 4: nº de entradas. */
					_n( '%1$s «%2$s» → se renombra a «%3$s» (su %4$d entrada se queda en ella).', '%1$s «%2$s» → se renombra a «%3$s» (sus %4$d entradas se quedan en ella).', $term->count, 'revista-koltor-dev' ),
					$label,
					$term->name,
					$name,
					$term->count
				);
			} elseif ( 'create' === $action ) {
				/* translators: 1: tipo, 2: nombre. */
				$log[] = sprintf( __( '%1$s «%2$s» → se crea.', 'revista-koltor-dev' ), $label, $name );
			} elseif ( $term->name !== $name || (int) $term->parent !== $parent_id ) {
				/* translators: 1: tipo, 2: nombre. */
				$log[] = sprintf( __( '%1$s «%2$s» → se actualiza (nombre o categoría padre).', 'revista-koltor-dev' ), $label, $name );
			}

			if ( $dry_run ) {
				if ( 'kdv_plataforma' === $taxonomy && ! empty( $t['icon']['data'] ) ) {
					/* translators: %s: plataforma. */
					$log[] = sprintf( __( 'Icono de «%s» → se sube a la biblioteca de medios.', 'revista-koltor-dev' ), $name );
				}
				continue;
			}

			$result  = $term ? wp_update_term( $term->term_id, $taxonomy, $args ) : wp_insert_term( $name, $taxonomy, $args );
			$term_id = is_wp_error( $result ) ? 0 : (int) $result['term_id'];
			if ( ! $term_id ) {
				/* translators: 1: nombre, 2: error. */
				$log[] = sprintf( __( 'ERROR con «%1$s»: %2$s', 'revista-koltor-dev' ), $name, $result->get_error_message() );
				continue;
			}

			if ( 'kdv_plataforma' === $taxonomy ) {
				$color = sanitize_hex_color( $t['color'] ?? '' );
				$color ? update_term_meta( $term_id, 'kdv_platform_color', $color ) : delete_term_meta( $term_id, 'kdv_platform_color' );
				if ( ! empty( $t['icon']['data'] ) ) {
					$icon_id = kdv_config_sideload_icon( $t['icon'], $name );
					if ( is_wp_error( $icon_id ) ) {
						/* translators: 1: plataforma, 2: error. */
						$log[] = sprintf( __( 'ERROR con el icono de «%1$s»: %2$s', 'revista-koltor-dev' ), $name, $icon_id->get_error_message() );
					} else {
						update_term_meta( $term_id, 'kdv_platform_icon', $icon_id );
					}
				}
			}
		}
	}

	if ( ! empty( $data['default_category'] ) ) {
		[ $default ] = kdv_config_find_term( 'category', sanitize_title( $data['default_category'] ) );
		if ( $dry_run ) {
			/* translators: %s: slug. */
			$log[] = sprintf( __( 'Categoría por defecto → «%s».', 'revista-koltor-dev' ), sanitize_title( $data['default_category'] ) );
		} elseif ( $default ) {
			update_option( 'default_category', $default->term_id );
		}
	}

	// 2. Páginas con plantilla del tema.
	foreach ( (array) ( $data['pages'] ?? [] ) as $page ) {
		$slug     = sanitize_title( $page['slug'] ?? '' );
		$template = 'page-templates/ranking.php' === ( $page['template'] ?? '' ) ? 'page-templates/ranking.php' : '';
		if ( '' === $slug || '' === $template ) {
			continue;
		}
		$existing = get_page_by_path( $slug );
		if ( $dry_run ) {
			/* translators: %s: título. */
			$log[] = $existing ? sprintf( __( 'Página «%s» → ya existe; se le asigna la plantilla.', 'revista-koltor-dev' ), $existing->post_title ) : sprintf( __( 'Página «%s» → se crea.', 'revista-koltor-dev' ), sanitize_text_field( $page['title'] ) );
			continue;
		}
		$page_id = $existing ? $existing->ID : wp_insert_post( [ 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => sanitize_text_field( $page['title'] ), 'post_name' => $slug ] );
		if ( $page_id && ! is_wp_error( $page_id ) ) {
			update_post_meta( $page_id, '_wp_page_template', $template );
		}
	}

	// 3. Menús.
	foreach ( (array) ( $data['menus'] ?? [] ) as $location => $menu ) {
		if ( ! in_array( $location, [ 'primary', 'footer', 'footer_legal' ], true ) || empty( $menu['items'] ) ) {
			continue;
		}
		$name = sanitize_text_field( $menu['name'] ?? $location );
		if ( $dry_run ) {
			/* translators: 1: menú, 2: elementos. */
			$log[] = sprintf( __( 'Menú «%1$s» → se monta con: %2$s.', 'revista-koltor-dev' ), $name, implode( ' · ', array_map( function( $i ) { return sanitize_text_field( $i['title'] ?? '' ); }, $menu['items'] ) ) );
			continue;
		}
		$existing = wp_get_nav_menu_object( $name );
		if ( $existing ) {
			foreach ( wp_get_nav_menu_items( $existing->term_id ) ?: [] as $old_item ) {
				wp_delete_post( $old_item->ID, true );
			}
			$menu_id = $existing->term_id;
		} else {
			$menu_id = wp_create_nav_menu( $name );
		}
		if ( is_wp_error( $menu_id ) ) {
			continue;
		}
		$created = [];
		foreach ( $menu['items'] as $i => $item ) {
			$args = [ 'menu-item-title' => sanitize_text_field( $item['title'] ?? '' ), 'menu-item-status' => 'publish' ];
			if ( isset( $item['parent'] ) && null !== $item['parent'] && isset( $created[ $item['parent'] ] ) ) {
				$args['menu-item-parent-id'] = $created[ $item['parent'] ];
			}
			$kind = $item['kind'] ?? 'custom';
			if ( 'term' === $kind && taxonomy_exists( $item['taxonomy'] ?? '' ) ) {
				[ $term ] = kdv_config_find_term( $item['taxonomy'], sanitize_title( $item['slug'] ?? '' ) );
				if ( ! $term ) {
					continue;
				}
				$args += [ 'menu-item-type' => 'taxonomy', 'menu-item-object' => $item['taxonomy'], 'menu-item-object-id' => $term->term_id ];
			} elseif ( 'page' === $kind ) {
				$page = get_page_by_path( sanitize_title( $item['slug'] ?? '' ) );
				if ( ! $page ) {
					continue;
				}
				$args += [ 'menu-item-type' => 'post_type', 'menu-item-object' => 'page', 'menu-item-object-id' => $page->ID ];
			} elseif ( 'archive' === $kind && post_type_exists( $item['post_type'] ?? '' ) ) {
				$args += [ 'menu-item-type' => 'post_type_archive', 'menu-item-object' => $item['post_type'] ];
			} else {
				$args += [ 'menu-item-type' => 'custom', 'menu-item-url' => esc_url_raw( $localize( $item['url'] ?? '' ) ) ];
			}
			$item_id = wp_update_nav_menu_item( $menu_id, 0, $args );
			if ( ! is_wp_error( $item_id ) ) {
				$created[ $i ] = $item_id;
			}
		}
		$locations              = get_nav_menu_locations();
		$locations[ $location ] = $menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );
	}

	// 4. Barra lateral (los widgets actuales pasan a "Widgets inactivos").
	if ( ! empty( $data['sidebar'] ) ) {
		if ( $dry_run ) {
			$log[] = sprintf(
				/* translators: %s: lista de widgets. */
				__( 'Barra lateral → se sustituye por: %s (los widgets actuales pasan a "Widgets inactivos").', 'revista-koltor-dev' ),
				implode( ' · ', array_map( function( $w ) { return sanitize_key( $w['id_base'] ?? '' ); }, $data['sidebar'] ) )
			);
		} else {
			$sidebars                        = wp_get_sidebars_widgets();
			$sidebars['wp_inactive_widgets'] = array_merge( $sidebars['wp_inactive_widgets'] ?? [], $sidebars['sidebar-main'] ?? [] );
			$sidebars['sidebar-main']        = [];
			$allowed                         = [ 'block', 'kdv_recent_posts', 'kdv_recent_comments', 'kdv_categories', 'kdv_ad', 'search', 'text', 'custom_html' ];
			foreach ( $data['sidebar'] as $w ) {
				$id_base = sanitize_key( $w['id_base'] ?? '' );
				if ( ! in_array( $id_base, $allowed, true ) || ! is_array( $w['settings'] ?? null ) ) {
					continue;
				}
				$settings = $localize( $w['settings'] );
				if ( 'block' === $id_base && ! current_user_can( 'unfiltered_html' ) ) {
					$settings['content'] = wp_kses_post( $settings['content'] ?? '' );
				}
				if ( 'kdv_ad' === $id_base ) {
					$settings['code'] = kdv_sanitize_ad_code( (string) ( $settings['code'] ?? '' ) );
				}
				$instances = get_option( 'widget_' . $id_base, [] );
				$instances = is_array( $instances ) ? $instances : [];
				$numbers   = array_filter( array_keys( $instances ), 'is_int' );
				$number    = $numbers ? max( $numbers ) + 1 : 2;
				$instances[ $number ]      = $settings;
				$instances['_multiwidget'] = 1;
				update_option( 'widget_' . $id_base, $instances );
				$sidebars['sidebar-main'][] = $id_base . '-' . $number;
			}
			wp_set_sidebars_widgets( $sidebars );
		}
	}

	// 5. Ajustes del Personalizador, cada uno por su propio saneado.
	$mods = (array) ( $data['theme_mods'] ?? [] );
	if ( $mods ) {
		$manager = kdv_config_customizer();
		$applied = 0;
		$skipped = [];
		foreach ( $mods as $key => $value ) {
			if ( in_array( $key, kdv_config_excluded_mods(), true ) || 0 !== strpos( (string) $key, 'kdv_' ) ) {
				continue;
			}
			$setting = $manager ? $manager->get_setting( $key ) : null;
			if ( ! $setting ) {
				$skipped[] = $key;
				continue;
			}
			$clean = $setting->sanitize( $localize( $value ) );
			if ( null === $clean || is_wp_error( $clean ) ) {
				$skipped[] = $key;
				continue;
			}
			if ( ! $dry_run ) {
				set_theme_mod( $key, $clean );
			}
			$applied++;
		}
		/* translators: %d: número de ajustes. */
		$log[] = sprintf( _n( 'Personalizador → %d ajuste.', 'Personalizador → %d ajustes.', $applied, 'revista-koltor-dev' ), $applied );
		if ( $skipped ) {
			/* translators: %s: ajustes. */
			$log[] = sprintf( __( 'Se ignoran (no existen en esta versión del tema o no son válidos): %s', 'revista-koltor-dev' ), implode( ', ', array_map( 'sanitize_key', $skipped ) ) );
		}
	}

	// 6. Información del sitio (con su propio saneado).
	if ( ! empty( $data['site_info'] ) && function_exists( 'kdv_sanitize_site_info' ) ) {
		$log[] = __( 'Información del sitio (pie de página y créditos) → se actualiza.', 'revista-koltor-dev' );
		if ( ! $dry_run ) {
			update_option( 'kdv_site_info', kdv_sanitize_site_info( $localize( $data['site_info'] ) ) );
		}
	}

	return $log;
}

/**
 * Un gestor del Personalizador con los ajustes del tema registrados, para
 * pasar cada valor importado por el MISMO saneado que al guardarlo a mano.
 *
 * @return WP_Customize_Manager|null
 */
function kdv_config_customizer() {
	require_once ABSPATH . WPINC . '/class-wp-customize-manager.php';
	$manager = new WP_Customize_Manager();
	if ( function_exists( 'kdv_customize_register' ) ) {
		kdv_customize_register( $manager );
	}
	return $manager;
}

/**
 * Sube un icono del paquete a la biblioteca de medios (solo imágenes PNG,
 * JPEG, WebP o GIF; comprobadas por contenido, no solo por la extensión).
 *
 * @param array  $icon  [ filename, mime, data(base64) ].
 * @param string $title Título del adjunto.
 * @return int|WP_Error ID del adjunto.
 */
function kdv_config_sideload_icon( array $icon, $title ) {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$bytes = base64_decode( (string) ( $icon['data'] ?? '' ), true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
	if ( ! $bytes || strlen( $bytes ) > 512 * KB_IN_BYTES ) {
		return new WP_Error( 'kdv_icon', __( 'icono vacío o demasiado grande.', 'revista-koltor-dev' ) );
	}
	$tmp = wp_tempnam( 'kdv-icon' );
	file_put_contents( $tmp, $bytes ); // phpcs:ignore WordPress.WP.AlternativeFunctions

	$info = @getimagesize( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors -- un archivo que no es imagen devuelve false.
	$ok   = [ 'image/png' => 'png', 'image/jpeg' => 'jpg', 'image/webp' => 'webp', 'image/gif' => 'gif' ];
	if ( ! $info || ! isset( $ok[ $info['mime'] ] ) ) {
		wp_delete_file( $tmp );
		return new WP_Error( 'kdv_icon', __( 'el icono no es una imagen válida.', 'revista-koltor-dev' ) );
	}
	$name = sanitize_file_name( pathinfo( (string) ( $icon['filename'] ?? 'icono' ), PATHINFO_FILENAME ) ) . '.' . $ok[ $info['mime'] ];
	$id   = media_handle_sideload( [ 'name' => $name, 'tmp_name' => $tmp ], 0, $title );
	if ( is_wp_error( $id ) ) {
		wp_delete_file( $tmp );
	}
	return $id;
}

/**
 * Paso 1 (subir y previsualizar) y paso 2 (aplicar). El paquete subido se
 * guarda unos minutos en un transitorio del usuario entre los dos pasos.
 */
function kdv_handle_config_import() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'No tienes permiso para importar la configuración.', 'revista-koltor-dev' ), 403 );
	}
	check_admin_referer( 'kdv_config_import' );

	$key  = 'kdv_config_import_' . get_current_user_id();
	$back = admin_url( 'admin.php?page=kdv-config-transfer' );

	if ( isset( $_POST['kdv_apply'] ) ) {
		$data = get_transient( $key );
		if ( ! is_array( $data ) ) {
			wp_safe_redirect( add_query_arg( 'kdv-error', rawurlencode( __( 'La vista previa caducó. Sube el archivo otra vez.', 'revista-koltor-dev' ) ), $back ) );
			exit;
		}
		// Copia de seguridad para el botón "Restaurar".
		update_option( 'kdv_config_backup', [
			'date'       => gmdate( 'c' ),
			'theme_mods' => get_theme_mods(),
			'site_info'  => get_option( 'kdv_site_info', [] ),
		], false );
		$log = kdv_config_apply( $data, false );
		delete_transient( $key );
		set_transient( $key . '_log', $log, HOUR_IN_SECONDS );
		wp_safe_redirect( add_query_arg( 'kdv-done', 1, $back ) );
		exit;
	}

	$file = $_FILES['kdv_config_file'] ?? null; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- se valida abajo.
	if ( ! $file || UPLOAD_ERR_OK !== ( $file['error'] ?? 1 ) || ! is_uploaded_file( $file['tmp_name'] ) || $file['size'] > 5 * MB_IN_BYTES ) {
		wp_safe_redirect( add_query_arg( 'kdv-error', rawurlencode( __( 'No se recibió un archivo válido (máximo 5 MB).', 'revista-koltor-dev' ) ), $back ) );
		exit;
	}
	$data  = json_decode( (string) file_get_contents( $file['tmp_name'] ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	$valid = kdv_config_validate( $data );
	if ( is_wp_error( $valid ) ) {
		wp_safe_redirect( add_query_arg( 'kdv-error', rawurlencode( $valid->get_error_message() ), $back ) );
		exit;
	}
	set_transient( $key, $data, 30 * MINUTE_IN_SECONDS );
	wp_safe_redirect( add_query_arg( 'kdv-preview', 1, $back ) );
	exit;
}
add_action( 'admin_post_kdv_config_import', 'kdv_handle_config_import' );

function kdv_handle_config_restore() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'No tienes permiso.', 'revista-koltor-dev' ), 403 );
	}
	check_admin_referer( 'kdv_config_restore' );
	$backup = get_option( 'kdv_config_backup' );
	if ( is_array( $backup ) ) {
		$stylesheet = get_option( 'stylesheet' );
		update_option( 'theme_mods_' . $stylesheet, (array) $backup['theme_mods'] );
		update_option( 'kdv_site_info', $backup['site_info'] );
		delete_option( 'kdv_config_backup' );
	}
	wp_safe_redirect( add_query_arg( 'kdv-restored', 1, admin_url( 'admin.php?page=kdv-config-transfer' ) ) );
	exit;
}
add_action( 'admin_post_kdv_config_restore', 'kdv_handle_config_restore' );

/* =====================================================================
 * PANTALLA
 * =================================================================== */

function kdv_render_config_transfer_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$key     = 'kdv_config_import_' . get_current_user_id();
	$post_to = esc_url( admin_url( 'admin-post.php' ) );
	// phpcs:disable WordPress.Security.NonceVerification -- solo deciden qué aviso mostrar.
	$error   = isset( $_GET['kdv-error'] ) ? sanitize_text_field( wp_unslash( $_GET['kdv-error'] ) ) : '';
	$preview = isset( $_GET['kdv-preview'] ) ? get_transient( $key ) : null;
	$done    = isset( $_GET['kdv-done'] ) ? get_transient( $key . '_log' ) : null;
	$restored = isset( $_GET['kdv-restored'] );
	// phpcs:enable
	$backup  = get_option( 'kdv_config_backup' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Exportar / Importar configuración', 'revista-koltor-dev' ); ?></h1>
		<p style="max-width:860px;font-size:14px;">
			<?php esc_html_e( 'Lleva la configuración de este sitio a otro (por ejemplo, del sitio local de pruebas a producción): categorías, plataformas con sus iconos y colores, géneros, menú, página Tops, barra lateral, ajustes del Personalizador e Información del sitio. No toca entradas, reseñas, comentarios ni usuarios, ni el modo construcción.', 'revista-koltor-dev' ); ?>
		</p>

		<?php if ( $error ) : ?>
			<div class="notice notice-error"><p><?php echo esc_html( $error ); ?></p></div>
		<?php endif; ?>
		<?php if ( $restored ) : ?>
			<div class="notice notice-success"><p><?php esc_html_e( 'Se restauraron los ajustes del Personalizador y la Información del sitio anteriores a la importación.', 'revista-koltor-dev' ); ?></p></div>
		<?php endif; ?>

		<?php if ( is_array( $done ) ) : ?>
			<div class="notice notice-success"><p><strong><?php esc_html_e( 'Configuración importada.', 'revista-koltor-dev' ); ?></strong> <?php esc_html_e( 'Si usas un plugin de caché, vacíala para ver los cambios.', 'revista-koltor-dev' ); ?></p>
				<ul style="list-style:disc;margin-left:20px;"><?php foreach ( $done as $line ) : ?><li><?php echo esc_html( $line ); ?></li><?php endforeach; ?></ul>
			</div>
		<?php endif; ?>

		<?php if ( is_array( $preview ) ) : ?>
			<div class="card" style="max-width:860px;">
				<h2><?php esc_html_e( 'Vista previa: esto es lo que va a cambiar', 'revista-koltor-dev' ); ?></h2>
				<p>
					<?php
					/* translators: 1: sitio de origen, 2: fecha, 3: versión. */
					printf( esc_html__( 'Archivo exportado desde %1$s el %2$s (tema %3$s).', 'revista-koltor-dev' ), '<code>' . esc_html( $preview['source'] ?? '?' ) . '</code>', esc_html( $preview['generated'] ?? '?' ), esc_html( $preview['theme_version'] ?? '?' ) );
					?>
				</p>
				<ul style="list-style:disc;margin-left:20px;">
					<?php foreach ( kdv_config_apply( $preview, true ) as $line ) : ?>
						<li><?php echo esc_html( $line ); ?></li>
					<?php endforeach; ?>
				</ul>
				<form method="post" action="<?php echo $post_to; // phpcs:ignore WordPress.Security.EscapeOutput -- escapado arriba. ?>">
					<input type="hidden" name="action" value="kdv_config_import" />
					<input type="hidden" name="kdv_apply" value="1" />
					<?php wp_nonce_field( 'kdv_config_import' ); ?>
					<?php submit_button( __( 'Aplicar la configuración', 'revista-koltor-dev' ), 'primary', 'submit', false ); ?>
					<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=kdv-config-transfer' ) ); ?>"><?php esc_html_e( 'Cancelar', 'revista-koltor-dev' ); ?></a>
				</form>
			</div>
		<?php endif; ?>

		<h2 class="title"><?php esc_html_e( '1. Exportar la configuración de este sitio', 'revista-koltor-dev' ); ?></h2>
		<form method="post" action="<?php echo $post_to; // phpcs:ignore WordPress.Security.EscapeOutput ?>">
			<input type="hidden" name="action" value="kdv_config_export" />
			<?php wp_nonce_field( 'kdv_config_export' ); ?>
			<?php submit_button( __( 'Descargar archivo de configuración', 'revista-koltor-dev' ), 'secondary', 'submit', false ); ?>
		</form>

		<h2 class="title"><?php esc_html_e( '2. Importar en este sitio', 'revista-koltor-dev' ); ?></h2>
		<p><?php esc_html_e( 'Sube el archivo exportado desde el otro sitio. Primero verás qué va a cambiar; nada se aplica hasta que lo confirmes. Antes de aplicar se guarda una copia de tus ajustes actuales.', 'revista-koltor-dev' ); ?></p>
		<form method="post" action="<?php echo $post_to; // phpcs:ignore WordPress.Security.EscapeOutput ?>" enctype="multipart/form-data">
			<input type="hidden" name="action" value="kdv_config_import" />
			<?php wp_nonce_field( 'kdv_config_import' ); ?>
			<input type="file" name="kdv_config_file" accept=".json,application/json" required />
			<?php submit_button( __( 'Ver qué va a cambiar', 'revista-koltor-dev' ), 'primary', 'submit', false ); ?>
		</form>

		<?php if ( is_array( $backup ) ) : ?>
			<h2 class="title"><?php esc_html_e( 'Deshacer la última importación', 'revista-koltor-dev' ); ?></h2>
			<form method="post" action="<?php echo $post_to; // phpcs:ignore WordPress.Security.EscapeOutput ?>" onsubmit="return confirm(<?php echo esc_attr( wp_json_encode( __( '¿Restaurar los ajustes del Personalizador y la Información del sitio anteriores a la importación?', 'revista-koltor-dev' ) ) ); ?>);">
				<input type="hidden" name="action" value="kdv_config_restore" />
				<?php wp_nonce_field( 'kdv_config_restore' ); ?>
				<p class="description">
					<?php
					/* translators: %s: fecha. */
					printf( esc_html__( 'Copia guardada el %s. Restaura los ajustes del Personalizador y la Información del sitio; las categorías, plataformas, menú y barra lateral se ajustan a mano si hace falta.', 'revista-koltor-dev' ), esc_html( $backup['date'] ?? '' ) );
					?>
				</p>
				<?php submit_button( __( 'Restaurar ajustes anteriores', 'revista-koltor-dev' ), 'secondary', 'submit', false ); ?>
			</form>
		<?php endif; ?>
	</div>
	<?php
}
