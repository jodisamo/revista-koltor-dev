<?php
/**
 * Single post template (any standard post).
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="kdv-container">
	<div class="kdv-content kdv-content__grid">
		<main>
			<?php while ( have_posts() ) : the_post(); ?>
				<?php
				$kdv_post_id    = get_the_ID();
				$kdv_categories = get_the_category( $kdv_post_id );
				?>
				<article <?php post_class( 'kdv-post' ); ?>>
					<?php
					if ( ! empty( $kdv_categories ) ) :
						?>
						<a href="<?php echo esc_url( get_category_link( $kdv_categories[0] ) ); ?>" class="kdv-card__category"><?php echo esc_html( $kdv_categories[0]->name ); ?></a>
					<?php endif; ?>

					<h1 class="kdv-post__title"><?php the_title(); ?></h1>

					<div class="kdv-post__meta">
						<span><?php echo esc_html( get_the_date() ); ?></span>
						<span><?php esc_html_e( 'Por', 'revista-koltor-dev' ); ?> <?php the_author(); ?></span>
						<?php
						$terms = get_the_terms( get_the_ID(), 'kdv_genero' );
						if ( $terms && ! is_wp_error( $terms ) ) :
							?>
							<span><?php echo esc_html( implode( ', ', wp_list_pluck( $terms, 'name' ) ) ); ?></span>
						<?php endif; ?>
					</div>

					<?php if ( has_post_thumbnail() ) : ?>
						<div class="kdv-post__thumbnail"><?php the_post_thumbnail( 'kdv-hero' ); ?></div>
					<?php endif; ?>

					<div class="kdv-post__body">
						<?php the_content(); ?>
					</div>

					<?php
					wp_link_pages( [
						'before' => '<div class="kdv-pagination">',
						'after'  => '</div>',
					] );
					?>

					<?php kdv_render_post_tags( $kdv_post_id ); ?>
				</article>

				<?php kdv_render_share_buttons( $kdv_post_id ); ?>

				<?php kdv_render_author_box( $kdv_post_id ); ?>

				<?php
				// Related articles: same primary category, excluding this one.
				if ( ! empty( $kdv_categories ) ) :
					$kdv_related = new WP_Query( [
						'post_type'      => 'post',
						'posts_per_page' => 3,
						'post__not_in'   => [ $kdv_post_id ],
						'no_found_rows'  => true,
						'category__in'   => [ $kdv_categories[0]->term_id ],
					] );
					if ( $kdv_related->have_posts() ) :
						?>
						<section class="kdv-section">
							<div class="kdv-section__head">
								<h2 class="kdv-section__title"><?php esc_html_e( 'Artículos relacionados', 'revista-koltor-dev' ); ?></h2>
							</div>
							<div class="kdv-cards-grid">
								<?php
								while ( $kdv_related->have_posts() ) :
									$kdv_related->the_post();
									get_template_part( 'template-parts/content/post-card' );
								endwhile;
								wp_reset_postdata();
								?>
							</div>
						</section>
						<?php
					endif;
				endif;
				?>

				<?php if ( comments_open() || get_comments_number() ) : ?>
					<?php comments_template(); ?>
				<?php endif; ?>

			<?php endwhile; ?>
		</main>

		<?php get_sidebar(); ?>
	</div>
</div>
<?php get_footer(); ?>
