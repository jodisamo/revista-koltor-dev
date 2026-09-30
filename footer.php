<?php
/**
 * The footer for our theme.
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$kdv_footer_widgets_on = get_theme_mod( 'kdv_footer_widgets_enabled', true );
$kdv_footer_columns    = absint( get_theme_mod( 'kdv_footer_columns', 3 ) );
$kdv_newsletter_on     = get_theme_mod( 'kdv_footer_newsletter_enabled', false );
$kdv_newsletter_code   = trim( get_theme_mod( 'kdv_footer_newsletter_code', '' ) );
$kdv_has_newsletter    = $kdv_newsletter_on && '' !== $kdv_newsletter_code;
?>
</div><!-- #kdv-content -->

<footer class="kdv-footer">
	<div class="kdv-container kdv-footer__top">
		<?php
		// Franja de información del sitio: presentación + redes, secciones,
		// legal y contacto. Se edita en "Revista Koltor Dev → Información del sitio".
		kdv_render_footer_info();
		?>

		<?php if ( $kdv_footer_widgets_on ) : ?>
			<?php for ( $i = 1; $i <= $kdv_footer_columns; $i++ ) : ?>
				<?php if ( is_active_sidebar( 'footer-' . $i ) ) : ?>
					<div class="kdv-footer__col">
						<?php dynamic_sidebar( 'footer-' . $i ); ?>
					</div>
				<?php endif; ?>
			<?php endfor; ?>
		<?php endif; ?>

		<?php if ( $kdv_has_newsletter ) : ?>
			<?php /* info-col y no footer__col: este bloque debe quedarse junto en una sola celda (footer__col es display:contents y repartiría el título y el formulario en columnas distintas). */ ?>
			<div class="kdv-footer__info-col">
				<h4 class="kdv-widget-title kdv-footer__newsletter-title"><?php echo esc_html( get_theme_mod( 'kdv_footer_newsletter_title', __( 'Únete al boletín', 'revista-koltor-dev' ) ) ); ?></h4>
				<?php echo $kdv_newsletter_code; // phpcs:ignore WordPress.Security.EscapeOutput -- trusted, admin-only raw embed code from the site's newsletter provider. ?>
			</div>
		<?php endif; ?>

	</div>

	<?php if ( get_theme_mod( 'kdv_ad_footer_enabled', false ) && trim( get_theme_mod( 'kdv_ad_footer_code', '' ) ) ) : ?>
		<div class="kdv-container">
			<?php kdv_render_ad_slot( 'footer' ); ?>
		</div>
	<?php endif; ?>

	<div class="kdv-container kdv-footer__bottom">
		<span class="kdv-footer__copyright">
			<?php kdv_render_footer_copyright(); ?>
		</span>
		<?php kdv_render_footer_credits(); ?>
	</div>
</footer>

<?php kdv_render_social_floating(); ?>

<button type="button" id="kdv-back-to-top" class="kdv-back-to-top" aria-label="<?php esc_attr_e( 'Volver arriba', 'revista-koltor-dev' ); ?>">
	<span aria-hidden="true">↑</span>
</button>

<?php wp_footer(); ?>
</body>
</html>
