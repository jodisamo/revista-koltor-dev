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
 * un icono (emoji, configurable por categoría desde Entradas → Categorías)
 * y una flechita, en vez de la lista de texto plano sin estilo del widget
 * original.
 */
class KDV_Widget_Categories extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'kdv_categories',
			__( 'Koltor Dev: Categorías', 'revista-koltor-dev' ),
			[
				'description' => __( 'Como "Categorías", pero con un icono por categoría y una flecha — asigna el icono de cada una desde Entradas → Categorías.', 'revista-koltor-dev' ),
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
			<?php foreach ( $categories as $kdv_cat ) :
				$icon_slug = get_term_meta( $kdv_cat->term_id, 'kdv_category_icon', true );
				$color     = ( $kdv_cat->term_id % 8 ) + 1;
				?>
				<li class="kdv-widget-categories__item">
					<a href="<?php echo esc_url( get_category_link( $kdv_cat ) ); ?>" class="kdv-widget-categories__link">
						<span class="kdv-widget-categories__icon kdv-tag--<?php echo absint( $color ); ?>"><?php kdv_render_icon_svg( $icon_slug ?: 'tag', 17 ); ?></span>
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
		<p class="description"><?php esc_html_e( 'El icono de cada categoría se asigna desde Entradas → Categorías (campo "Icono").', 'revista-koltor-dev' ); ?></p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance               = [];
		$instance['title']      = sanitize_text_field( $new_instance['title'] );
		$instance['show_count'] = ! empty( $new_instance['show_count'] );
		return $instance;
	}
}

add_action(
	'widgets_init',
	function() {
		register_widget( 'KDV_Widget_Recent_Posts' );
		register_widget( 'KDV_Widget_Recent_Comments' );
		register_widget( 'KDV_Widget_Categories' );
	}
);
