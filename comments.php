<?php
/**
 * Comments template.
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( post_password_required() ) {
	return;
}
?>
<div class="kdv-comments" id="comments">

	<?php if ( have_comments() ) : ?>
		<h2 class="kdv-comments__title">
			<?php
			$count = get_comments_number();
			/* translators: %s: number of comments. */
			printf( esc_html( _n( '%s comentario', '%s comentarios', $count, 'revista-koltor-dev' ) ), esc_html( number_format_i18n( $count ) ) );
			?>
		</h2>

		<ol class="comment-list">
			<?php
			wp_list_comments( [
				'style'      => 'ol',
				'short_ping' => true,
				'callback'   => 'kdv_comment_template',
			] );
			?>
		</ol>

		<?php the_comments_pagination( [ 'class' => 'kdv-pagination' ] ); ?>

	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>
		<p class="kdv-comments__closed"><?php esc_html_e( 'Los comentarios están cerrados.', 'revista-koltor-dev' ); ?></p>
	<?php endif; ?>

	<?php comment_form(); ?>
</div>
<?php

/**
 * Custom comment markup.
 */
function kdv_comment_template( $comment, $args, $depth ) {
	?>
	<li <?php comment_class( 'kdv-comment' ); ?> id="comment-<?php comment_ID(); ?>">
		<div class="kdv-comment__avatar"><?php echo get_avatar( $comment, 48 ); ?></div>
		<div class="kdv-comment__meta">
			<strong><?php comment_author(); ?></strong> · <?php comment_date(); ?>
		</div>
		<div class="kdv-comment__content">
			<?php comment_text(); ?>
		</div>
		<?php
		comment_reply_link( array_merge( $args, [
			'depth'     => $depth,
			'max_depth' => $args['max_depth'],
		] ) );
		?>
	</li>
	<?php
}
