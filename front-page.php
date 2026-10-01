<?php
/**
 * The front page template — magazine-style homepage.
 *
 * If, in Ajustes → Lectura, se configura "Una página estática" como página
 * de inicio, esa página manda por completo (incluido si está armada con
 * Elementor / Elementor Pro) en vez de este layout de revista — así puedes
 * diseñar la portada completa con Elementor cuando quieras, sin pelear con
 * el tema. El layout de revista de abajo solo se usa mientras la portada
 * siga en "Tus últimas entradas" (el comportamiento por defecto del tema).
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( 'page' === get_option( 'show_on_front' ) && get_option( 'page_on_front' ) ) {
	require __DIR__ . '/page.php';
	return;
}

get_header();

$hero_slides     = kdv_get_hero_slides();
$kdv_hero_height = get_theme_mod( 'kdv_hero_height', 'normal' );
$kdv_hero_align  = get_theme_mod( 'kdv_hero_text_align', 'center' );
$kdv_hero_mods   = ( 'normal' !== $kdv_hero_height ? ' kdv-hero--' . $kdv_hero_height . ' kdv-hero-slider--' . $kdv_hero_height : '' )
	. ( 'left' === $kdv_hero_align ? ' kdv-hero--align-left kdv-hero-slider--align-left' : '' );
?>

<?php if ( $hero_slides ) : ?>

	<section class="kdv-hero kdv-hero-slider<?php echo esc_attr( $kdv_hero_mods ); ?>">
		<div class="swiper">
			<div class="swiper-wrapper">
				<?php foreach ( $hero_slides as $slide_index => $slide ) :
					// kdv-hero (1200x720) y no 'full': una foto de 4000px se
					// descargaba entera para un fondo de 1200px como mucho.
					$bg          = get_the_post_thumbnail_url( $slide, 'kdv-hero' );
					$mobile_id   = (int) get_post_meta( $slide->ID, '_kdv_slide_image_mobile', true );
					$mobile_bg   = $mobile_id ? wp_get_attachment_image_url( $mobile_id, 'large' ) : '';
					$subtitle    = get_post_meta( $slide->ID, '_kdv_slide_subtitle', true );
					$button_text = get_post_meta( $slide->ID, '_kdv_slide_button_text', true );
					$button_url  = get_post_meta( $slide->ID, '_kdv_slide_button_url', true );

					$slide_style = '';
					if ( $bg ) {
						$slide_style .= '--kdv-slide-bg:url(' . esc_url( $bg ) . ');';
					}
					if ( $mobile_bg ) {
						$slide_style .= '--kdv-slide-bg-mobile:url(' . esc_url( $mobile_bg ) . ');';
					}
					?>
					<div class="swiper-slide" <?php echo $slide_style ? 'style="' . esc_attr( $slide_style ) . '"' : ''; ?>>
						<div class="kdv-hero-slider__overlay"></div>
						<div class="kdv-container kdv-hero-slider__content">
							<?php
							// Un solo <h1> por página: el resto de diapositivas usan <h2>
							// con la misma clase, así que se ven exactamente igual.
							$kdv_slide_tag = 0 === $slide_index ? 'h1' : 'h2';
							?>
							<<?php echo $kdv_slide_tag; // phpcs:ignore WordPress.Security.EscapeOutput -- 'h1' o 'h2' literal. ?> class="kdv-hero__title"><?php echo esc_html( get_the_title( $slide ) ); ?></<?php echo $kdv_slide_tag; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
							<?php if ( $subtitle ) : ?>
								<p class="kdv-hero__subtitle"><?php echo esc_html( $subtitle ); ?></p>
							<?php endif; ?>
							<?php if ( $button_text && $button_url ) : ?>
								<a class="kdv-btn kdv-hero-slider__cta" href="<?php echo esc_url( $button_url ); ?>"><?php echo esc_html( $button_text ); ?></a>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="swiper-pagination"></div>
			<div class="swiper-button-prev"></div>
			<div class="swiper-button-next"></div>
		</div>
	</section>

<?php else :
	$hero_bg = get_theme_mod( 'kdv_hero_background', '' );
	?>

	<section class="kdv-hero<?php echo esc_attr( $kdv_hero_mods ); ?>" <?php echo $hero_bg ? 'style="background-image:url(' . esc_url( $hero_bg ) . ');background-size:cover;background-position:center;"' : ''; ?>>
		<?php if ( $hero_bg ) : ?>
			<?php
			/*
			 * With a background image, the title/subtítulo need to stay
			 * legible over an arbitrary photo instead of the plain gradient
			 * hero's normal (dark-on-light) text — reusing the slider hero's
			 * dark overlay + forced-white-text classes (assets/css/main.css)
			 * gives it the exact same treatment as the Diapositivas slider,
			 * instead of inheriting the site's light/dark-mode text colour
			 * (which is what made the title unreadable in modo claro).
			 */
			?>
			<div class="kdv-hero-slider__overlay"></div>
		<?php endif; ?>
		<div class="kdv-container<?php echo $hero_bg ? ' kdv-hero-slider__content' : ''; ?>">
			<h1 class="kdv-hero__title"><?php echo esc_html( get_theme_mod( 'kdv_hero_title', __( 'Toda la actualidad, en un solo lugar', 'revista-koltor-dev' ) ) ); ?></h1>
			<p class="kdv-hero__subtitle"><?php echo esc_html( get_theme_mod( 'kdv_hero_subtitle', __( 'Reseñas, novedades y análisis cada semana.', 'revista-koltor-dev' ) ) ); ?></p>
		</div>
	</section>

<?php endif; ?>

<div class="kdv-container">

	<?php
	// --- 0. Populares del mes (Personalizar → Secciones de la portada) ---
	if ( get_theme_mod( 'kdv_home_show_popular', true ) ) :
		$popular_count = max( 4, absint( get_theme_mod( 'kdv_home_popular_count', 8 ) ) );
		$popular_query = kdv_get_popular_posts_this_month( $popular_count );
		if ( $popular_query->have_posts() ) :
			?>
			<section class="kdv-section kdv-popular-section">
				<div class="kdv-section__head">
					<h2 class="kdv-section__title"><?php esc_html_e( 'Populares del mes', 'revista-koltor-dev' ); ?></h2>
				</div>
				<div class="kdv-popular-slider">
					<button type="button" class="kdv-popular-slider__nav kdv-popular-slider__nav--prev" aria-label="<?php esc_attr_e( 'Anterior', 'revista-koltor-dev' ); ?>">‹</button>
					<ul class="kdv-popular-slider__track">
						<?php
						$kdv_pop_rank = 0;
						while ( $popular_query->have_posts() ) :
							$popular_query->the_post();
							$kdv_pop_rank++;
							?>
							<li class="kdv-popular-slider__item">
								<a href="<?php the_permalink(); ?>" class="kdv-popular-slider__link">
									<span class="kdv-popular-slider__rank"><?php echo absint( $kdv_pop_rank ); ?></span>
									<span class="kdv-popular-slider__thumb">
										<?php if ( has_post_thumbnail() ) : ?>
											<?php the_post_thumbnail( 'kdv-square' ); ?>
										<?php else : ?>
											<span class="kdv-popular-slider__thumb-fallback"></span>
										<?php endif; ?>
									</span>
									<span class="kdv-popular-slider__title"><?php the_title(); ?></span>
								</a>
							</li>
						<?php endwhile; ?>
					</ul>
					<button type="button" class="kdv-popular-slider__nav kdv-popular-slider__nav--next" aria-label="<?php esc_attr_e( 'Siguiente', 'revista-koltor-dev' ); ?>">›</button>
				</div>
			</section>
			<?php
		endif;
		wp_reset_postdata();
	endif;
	?>

	<?php
	// --- 1. Últimas reseñas (Personalizar → Secciones de la portada) -----
	if ( get_theme_mod( 'kdv_home_show_resenas', true ) ) :
		$resenas_count = max( 2, absint( get_theme_mod( 'kdv_home_resenas_count', 6 ) ) );
		$resenas       = new WP_Query( [
			'post_type'      => 'kdv_resena',
			'posts_per_page' => $resenas_count,
			'no_found_rows'  => true,
		] );
		if ( $resenas->have_posts() ) :
			?>
			<section class="kdv-section">
				<div class="kdv-section__head">
					<h2 class="kdv-section__title"><?php esc_html_e( 'Últimas reseñas', 'revista-koltor-dev' ); ?></h2>
					<a class="kdv-section__link" href="<?php echo esc_url( get_post_type_archive_link( 'kdv_resena' ) ); ?>"><?php esc_html_e( 'Ver todas', 'revista-koltor-dev' ); ?><span class="kdv-section__link-arrow" aria-hidden="true">→</span></a>
				</div>
				<div class="kdv-cards-grid">
					<?php
					while ( $resenas->have_posts() ) :
						$resenas->the_post();
						get_template_part( 'template-parts/content/review-card' );
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</section>
		<?php endif; ?>
	<?php endif; ?>

	<?php
	// --- 2. Últimas noticias (todas las entradas normales, cualquier categoría) --
	// Cuando está activa, siempre aparece mientras exista al menos una entrada
	// publicada, sin importar a qué categoría esté asignada — así la portada
	// nunca se ve vacía apenas empiezas a publicar.
	if ( get_theme_mod( 'kdv_home_show_latest', true ) ) :
		$latest_count = max( 2, absint( get_theme_mod( 'kdv_home_latest_count', 6 ) ) );
		$ultimas      = new WP_Query( [
			'post_type'      => 'post',
			'posts_per_page' => $latest_count,
			'no_found_rows'  => true,
		] );
		if ( $ultimas->have_posts() ) :
			?>
			<section class="kdv-section">
				<div class="kdv-section__head">
					<h2 class="kdv-section__title"><?php esc_html_e( 'Últimas noticias', 'revista-koltor-dev' ); ?></h2>
				</div>
				<div class="kdv-cards-grid">
					<?php
					while ( $ultimas->have_posts() ) :
						$ultimas->the_post();
						get_template_part( 'template-parts/content/post-card' );
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</section>
		<?php endif; ?>
	<?php endif; ?>

	<?php
	// --- 3-5. Secciones por categoría, elegidas en Personalizar → Secciones
	// de la portada (por defecto: Noticias, Reportajes, Avances, si
	// esas categorías existen). Cada slot se puede apagar u cambiar de
	// categoría sin tocar código.
	for ( $slot = 3; $slot <= 5; $slot++ ) :
		$cat_id = kdv_get_home_section_category_id( $slot );
		if ( ! $cat_id ) {
			continue;
		}
		$term = get_category( $cat_id );
		if ( ! $term || is_wp_error( $term ) ) {
			continue;
		}
		$section_count = max( 2, absint( get_theme_mod( "kdv_home_section_{$slot}_count", 3 ) ) );
		$section_posts = new WP_Query( [
			'post_type'      => 'post',
			'posts_per_page' => $section_count,
			'cat'            => $term->term_id,
			'no_found_rows'  => true,
		] );
		if ( ! $section_posts->have_posts() ) {
			wp_reset_postdata();
			continue;
		}
		?>
		<section class="kdv-section">
			<div class="kdv-section__head">
				<h2 class="kdv-section__title"><?php echo esc_html( $term->name ); ?></h2>
				<a class="kdv-section__link" href="<?php echo esc_url( get_category_link( $term ) ); ?>"><?php esc_html_e( 'Ver más', 'revista-koltor-dev' ); ?><span class="kdv-section__link-arrow" aria-hidden="true">→</span></a>
			</div>
			<div class="kdv-cards-grid kdv-cards-grid--cols-3">
				<?php
				while ( $section_posts->have_posts() ) :
					$section_posts->the_post();
					get_template_part( 'template-parts/content/post-card' );
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		</section>
		<?php
	endfor;
	?>

</div>

<?php get_footer(); ?>
