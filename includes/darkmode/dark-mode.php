<?php
/**
 * Native dark mode: no plugin, no dependency — a tiny inline script placed
 * in <head> (before CSS/JS parsing) picks the right class before first
 * paint so there's no flash of the wrong theme, plus a persistent toggle
 * button wired to assets/js/dark-mode.js.
 *
 * @package Revista_Koltor_Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Inline "no-flash" script. Must run as early as possible, so it's hooked
 * to wp_head with a very low priority number and marked so WP doesn't try
 * to move/defer it.
 */
function kdv_dark_mode_inline_script() {
	$default = get_theme_mod( 'kdv_dark_mode_default', 'auto' );
	?>
	<script>
	( function () {
		try {
			var stored  = localStorage.getItem( 'kdv-theme' );
			var initial = stored || '<?php echo esc_js( $default ); ?>';

			if ( 'auto' === initial ) {
				initial = window.matchMedia( '(prefers-color-scheme: dark)' ).matches ? 'dark' : 'light';
			}

			document.documentElement.setAttribute( 'data-kdv-theme', initial );
		} catch ( e ) {
			// localStorage might be unavailable (private mode, etc.) — fall back silently to light.
			document.documentElement.setAttribute( 'data-kdv-theme', 'light' );
		}
	} )();
	</script>
	<?php
}
add_action( 'wp_head', 'kdv_dark_mode_inline_script', 0 );

/**
 * Renders the toggle button. Used from template-parts/header.
 */
function kdv_dark_mode_toggle_button() {
	if ( ! get_theme_mod( 'kdv_header_show_dark_toggle', true ) ) {
		return;
	}
	?>
	<button
		type="button"
		class="kdv-icon-btn kdv-dark-toggle"
		id="kdv-dark-toggle"
		aria-label="<?php esc_attr_e( 'Cambiar entre modo claro y oscuro', 'revista-koltor-dev' ); ?>"
	>
		<span class="kdv-dark-toggle__icon kdv-dark-toggle__icon--sun" aria-hidden="true">
			<svg viewBox="0 0 24 24" width="19" height="19" focusable="false"><circle cx="12" cy="12" r="4.5" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 2.5v3M12 18.5v3M21.5 12h-3M5.5 12h-3M18.4 5.6l-2.1 2.1M7.7 16.3l-2.1 2.1M18.4 18.4l-2.1-2.1M7.7 7.7 5.6 5.6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
		</span>
		<span class="kdv-dark-toggle__icon kdv-dark-toggle__icon--moon" aria-hidden="true">
			<svg viewBox="0 0 24 24" width="19" height="19" focusable="false"><path d="M20 14.3A8.3 8.3 0 1 1 9.7 4a6.6 6.6 0 0 0 10.3 10.3Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>
		</span>
	</button>
	<?php
}
