<?php
/**
 * Reemplazos nativos (sin plugins) de los widgets clásicos "Entradas
 * recientes" y "Comentarios recientes" de WordPress — con miniatura/avatar,
 * ya que los widgets originales solo muestran una lista de enlaces en texto
 * plano y no tienen forma de añadir eso sin un plugin de terceros.
 *
 * El widget nativo de WordPress no se elimina ni se modifica: estos son
 * widgets adicionales que aparecen junto a los de siempre en Apariencia →
 * Widgets, listos para arrastrar al lugar del original si se prefieren.
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * "Koltor Dev: Entradas recientes" — como el widget nativo, pero con
 * miniatura, y limitado a un máximo de 10 entradas desde el propio formulario
 * del widget (por defecto 5).
 */
class KDV_Widget_Recent_Posts extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'kdv_recent_posts',
			__( 'Koltor Dev: Entradas recientes', 'revista-koltor-dev' ),
			[
				'description' => __( 'Como "Entradas recientes", pero con miniatura de cada entrada.', 'revista-koltor-dev' ),
			]
		);
	}

	public function widget( $args, $instance ) {
		$title  = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Entradas recientes', 'revista-koltor-dev' );
		$title  = apply_filters( 'widget_title', $title, $instance, $this->id_base );
		$number = ! empty( $instance['number'] ) ? absint( $instance['number'] ) : 5;
		$number = max( 1, min( 10, $number ) );

		$kdv_query = new WP_Query( [
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => $number,
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
		] );

		if ( ! $kdv_query->have_posts() ) {
			return;
		}

		echo $args['before_widget']; // phpcs:ignore -- core widget wrapper, no user data.

		if ( $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore -- ídem, title ya escapado.
		}
		?>
		<ul class="kdv-widget-recent-posts">
			<?php while ( $kdv_query->have_posts() ) : $kdv_query->the_post(); ?>
				<li class="kdv-widget-recent-posts__item">
					<a href="<?php the_permalink(); ?>" class="kdv-widget-recent-posts__thumb" tabindex="-1" aria-hidden="true">
						<?php if ( has_post_thumbnail() ) : ?>
							<?php the_post_thumbnail( 'kdv-square' ); ?>
						<?php else : ?>
							<span class="kdv-widget-recent-posts__thumb-fallback"></span>
						<?php endif; ?>
					</a>
					<div class="kdv-widget-recent-posts__body">
						<a href="<?php the_permalink(); ?>" class="kdv-widget-recent-posts__title"><?php the_title(); ?></a>
						<span class="kdv-widget-recent-posts__date"><?php echo esc_html( get_the_date() ); ?></span>
					</div>
				</li>
			<?php endwhile; ?>
		</ul>
		<?php
		wp_reset_postdata();

		echo $args['after_widget']; // phpcs:ignore -- core widget wrapper.
	}

	public function form( $instance ) {
		$title  = isset( $instance['title'] ) ? $instance['title'] : __( 'Entradas recientes', 'revista-koltor-dev' );
		$number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Título:', 'revista-koltor-dev' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"><?php esc_html_e( 'Número de entradas a mostrar (máx. 10):', 'revista-koltor-dev' ); ?></label>
			<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" type="number" step="1" min="1" max="10" value="<?php echo esc_attr( $number ); ?>" size="3">
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance           = [];
		$instance['title']  = sanitize_text_field( $new_instance['title'] );
		$instance['number'] = max( 1, min( 10, absint( $new_instance['number'] ) ) );
		return $instance;
	}
}

/**
 * "Koltor Dev: Comentarios recientes" — como el widget nativo, pero con el
 * avatar de quien comenta y un pequeño fragmento del comentario.
 */
class KDV_Widget_Recent_Comments extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'kdv_recent_comments',
			__( 'Koltor Dev: Comentarios recientes', 'revista-koltor-dev' ),
			[
				'description' => __( 'Como "Comentarios recientes", pero con el avatar de quien comenta.', 'revista-koltor-dev' ),
			]
		);
	}

	public function widget( $args, $instance ) {
		$title  = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Comentarios recientes', 'revista-koltor-dev' );
		$title  = apply_filters( 'widget_title', $title, $instance, $this->id_base );
		$number = ! empty( $instance['number'] ) ? absint( $instance['number'] ) : 5;
		$number = max( 1, min( 10, $number ) );

		$kdv_comments = get_comments( [
			'number'        => $number,
			'status'        => 'approve',
			'post_status'   => 'publish',
			'type'          => 'comment',
			'no_found_rows' => true,
		] );

		if ( ! $kdv_comments ) {
			return;
		}

		echo $args['before_widget']; // phpcs:ignore -- core widget wrapper.

		if ( $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore -- ídem.
		}
		?>
		<ul class="kdv-widget-recent-comments">
			<?php foreach ( $kdv_comments as $kdv_comment ) : ?>
				<li class="kdv-widget-recent-comments__item">
					<?php echo get_avatar( $kdv_comment, 36, '', '', [ 'class' => 'kdv-widget-recent-comments__avatar' ] ); ?>
					<div class="kdv-widget-recent-comments__body">
						<span class="kdv-widget-recent-comments__author"><?php echo esc_html( $kdv_comment->comment_author ); ?></span>
						<a href="<?php echo esc_url( get_comment_link( $kdv_comment ) ); ?>" class="kdv-widget-recent-comments__excerpt">
							<?php echo esc_html( wp_trim_words( wp_strip_all_tags( $kdv_comment->comment_content ), 10 ) ); ?>
						</a>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
		echo $args['after_widget']; // phpcs:ignore -- core widget wrapper.
	}

	public function form( $instance ) {
		$title  = isset( $instance['title'] ) ? $instance['title'] : __( 'Comentarios recientes', 'revista-koltor-dev' );
		$number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Título:', 'revista-koltor-dev' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"><?php esc_html_e( 'Número de comentarios a mostrar (máx. 10):', 'revista-koltor-dev' ); ?></label>
			<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" type="number" step="1" min="1" max="10" value="<?php echo esc_attr( $number ); ?>" size="3">
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance           = [];
		$instance['title']  = sanitize_text_field( $new_instance['title'] );
		$instance['number'] = max( 1, min( 10, absint( $new_instance['number'] ) ) );
		return $instance;
	}
}

/**
 * "Koltor Dev: Categorías" — como el widget nativo de Categorías, pero con
 * el estilo del tema (fila con flecha y contador opcional) en vez de la
 * lista de texto plano del widget original.
 *
 * Hasta la 1.6.0 llevaba además un icono por categoría; se retiró en la
 * 1.7.0 porque en Entre Píxeles los iconos son de las plataformas, no de
 * las categorías, y tener los dos confundía. El widget se conserva para
 * que no desaparezca de ninguna barra lateral donde ya esté puesto.
 */
class KDV_Widget_Categories extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'kdv_categories',
			__( 'Koltor Dev: Categorías', 'revista-koltor-dev' ),
			[
				'description' => __( 'Como "Categorías", pero con el estilo del tema: una fila por categoría con flecha y contador opcional.', 'revista-koltor-dev' ),
			]
		);
	}

	public function widget( $args, $instance ) {
		$title      = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Categorías', 'revista-koltor-dev' );
		$title      = apply_filters( 'widget_title', $title, $instance, $this->id_base );
		$show_count = ! empty( $instance['show_count'] );

		$categories = get_categories( [
			'hide_empty' => true,
			'orderby'    => 'name',
			'order'      => 'ASC',
		] );

		if ( ! $categories ) {
			return;
		}

		echo $args['before_widget']; // phpcs:ignore -- core widget wrapper, no user data.

		if ( $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore -- ídem, title ya escapado.
		}
		?>
		<ul class="kdv-widget-categories">
			<?php foreach ( $categories as $kdv_cat ) : ?>
				<li class="kdv-widget-categories__item">
					<a href="<?php echo esc_url( get_category_link( $kdv_cat ) ); ?>" class="kdv-widget-categories__link">
						<span class="kdv-widget-categories__name"><?php echo esc_html( $kdv_cat->name ); ?></span>
						<?php if ( $show_count ) : ?>
							<span class="kdv-widget-categories__count"><?php echo absint( $kdv_cat->count ); ?></span>
						<?php endif; ?>
						<span class="kdv-widget-categories__chevron" aria-hidden="true">›</span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
		echo $args['after_widget']; // phpcs:ignore -- core widget wrapper.
	}

	public function form( $instance ) {
		$title      = isset( $instance['title'] ) ? $instance['title'] : __( 'Categorías', 'revista-koltor-dev' );
		$show_count = ! empty( $instance['show_count'] );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Título:', 'revista-koltor-dev' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<input class="checkbox" type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_count' ) ); ?>" <?php checked( $show_count ); ?>>
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_count' ) ); ?>"><?php esc_html_e( 'Mostrar número de entradas por categoría', 'revista-koltor-dev' ); ?></label>
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance               = [];
		$instance['title']      = sanitize_text_field( $new_instance['title'] );
		$instance['show_count'] = ! empty( $new_instance['show_count'] );
		return $instance;
	}
}

/**
 * "Koltor Dev: Publicidad" — un anuncio que se coloca en CUALQUIER posición
 * de la barra lateral (o del pie): arriba, entre dos widgets, al final… y
 * tantos como hagan falta. Usa el código de Personalizar → Publicidad →
 * "Barra lateral" o uno propio escrito en el widget.
 *
 * Mismo modelo de confianza que el resto de espacios publicitarios
 * (kdv_sanitize_ad_code()): quien tiene "unfiltered_html" guarda el código
 * tal cual; cualquier otro perfil pasa por wp_kses_post().
 */
class KDV_Widget_Ad extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'kdv_ad',
			__( 'Koltor Dev: Publicidad', 'revista-koltor-dev' ),
			[
				'description' => __( 'Un anuncio (AdSense, afiliados, un banner propio…) en la posición que quieras de la barra lateral.', 'revista-koltor-dev' ),
			]
		);
	}

	public function widget( $args, $instance ) {
		$source = $instance['source'] ?? 'customizer';
		if ( 'custom' === $source ) {
			$code = trim( (string) ( $instance['code'] ?? '' ) );
		} else {
			// El espacio "Barra lateral" de Personalizar → Publicidad, si está activado.
			$code = get_theme_mod( 'kdv_ad_sidebar_enabled', false ) ? trim( (string) get_theme_mod( 'kdv_ad_sidebar_code', '' ) ) : '';
		}
		if ( '' === $code ) {
			return; // Sin código no se pinta nada: ni caja vacía ni etiqueta suelta.
		}

		// Sin título ni caja de widget: un anuncio no debe parecer contenido.
		echo str_replace( 'kdv-widget ', 'kdv-widget kdv-widget--ad ', $args['before_widget'] ); // phpcs:ignore -- envoltorio del propio tema.
		echo '<div class="kdv-ad-slot kdv-ad-slot--sidebar">';
		if ( ! isset( $instance['show_label'] ) || $instance['show_label'] ) {
			echo '<span class="kdv-ad-slot__label">' . esc_html__( 'Publicidad', 'revista-koltor-dev' ) . '</span>';
		}
		echo $code; // phpcs:ignore WordPress.Security.EscapeOutput -- código de anuncio de confianza, saneado al guardar con kdv_sanitize_ad_code().
		echo '</div>';
		echo $args['after_widget']; // phpcs:ignore -- core widget wrapper.
	}

	public function form( $instance ) {
		$source     = $instance['source'] ?? 'customizer';
		$code       = $instance['code'] ?? '';
		$show_label = ! isset( $instance['show_label'] ) || $instance['show_label'];
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'source' ) ); ?>"><?php esc_html_e( 'Código del anuncio:', 'revista-koltor-dev' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'source' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'source' ) ); ?>">
				<option value="customizer" <?php selected( $source, 'customizer' ); ?>><?php esc_html_e( 'El de Personalizar → Publicidad → Barra lateral', 'revista-koltor-dev' ); ?></option>
				<option value="custom" <?php selected( $source, 'custom' ); ?>><?php esc_html_e( 'Uno propio (escríbelo abajo)', 'revista-koltor-dev' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'code' ) ); ?>"><?php esc_html_e( 'Código propio (HTML/JS del anuncio):', 'revista-koltor-dev' ); ?></label>
			<textarea class="widefat code" rows="6" id="<?php echo esc_attr( $this->get_field_id( 'code' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'code' ) ); ?>"><?php echo esc_textarea( $code ); ?></textarea>
		</p>
		<p>
			<input class="checkbox" type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_label' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_label' ) ); ?>" <?php checked( $show_label ); ?>>
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_label' ) ); ?>"><?php esc_html_e( 'Mostrar la etiqueta "Publicidad" encima', 'revista-koltor-dev' ); ?></label>
		</p>
		<p class="description"><?php esc_html_e( 'Si el código está vacío (o el espacio de Personalizar está desactivado), el widget no muestra nada.', 'revista-koltor-dev' ); ?></p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		return [
			'source'     => ( isset( $new_instance['source'] ) && 'custom' === $new_instance['source'] ) ? 'custom' : 'customizer',
			'code'       => kdv_sanitize_ad_code( (string) ( $new_instance['code'] ?? '' ) ),
			'show_label' => ! empty( $new_instance['show_label'] ),
		];
	}
}

add_action(
	'widgets_init',
	function() {
		register_widget( 'KDV_Widget_Ad' );
		register_widget( 'KDV_Widget_Recent_Posts' );
		register_widget( 'KDV_Widget_Recent_Comments' );
		register_widget( 'KDV_Widget_Categories' );
	}
);
