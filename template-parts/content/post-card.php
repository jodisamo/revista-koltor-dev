<?php
/**
 * Card for a standard post (any standard post).
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<article <?php post_class( 'kdv-card' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a href="<?php the_permalink(); ?>" class="kdv-card__thumb">
			<?php the_post_thumbnail( 'kdv-card' ); ?>
		</a>
	<?php endif; ?>

	<div class="kdv-card__body">
		<?php
		$categories = get_the_category();
		if ( ! empty( $categories ) ) :
			?>
			<a href="<?php echo esc_url( get_category_link( $categories[0] ) ); ?>" class="kdv-card__category"><?php echo esc_html( $categories[0]->name ); ?></a>
		<?php endif; ?>

		<h3 class="kdv-card__title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h3>

		<p class="kdv-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), absint( get_theme_mod( 'kdv_card_excerpt_length', 16 ) ) ) ); ?></p>

		<div class="kdv-card__meta">
			<?php if ( get_theme_mod( 'kdv_card_show_author_avatar', false ) ) : ?>
				<?php echo get_avatar( get_the_author_meta( 'ID' ), 18, '', '', [ 'class' => 'kdv-card__avatar' ] ); ?>
			<?php endif; ?>
			<span><?php echo esc_html( get_the_date() ); ?> · <?php the_author(); ?></span>
			<?php if ( get_theme_mod( 'kdv_card_show_reading_time', false ) ) : ?>
				<?php /* translators: %d: estimated reading time in minutes. */ ?>
				<span>· <?php echo esc_html( sprintf( __( '%d min de lectura', 'revista-koltor-dev' ), kdv_get_reading_time() ) ); ?></span>
			<?php endif; ?>
		</div>
	</div>
</article>
