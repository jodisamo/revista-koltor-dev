/**
 * Dark mode toggle. Zero dependencies, vanilla JS.
 * Works together with the inline "no-flash" snippet in dark-mode.php,
 * which already sets [data-kdv-theme] on <html> before this file loads.
 */
( function () {
	'use strict';

	function currentTheme() {
		return document.documentElement.getAttribute( 'data-kdv-theme' ) || 'light';
	}

	function setTheme( theme ) {
		document.documentElement.setAttribute( 'data-kdv-theme', theme );
		try {
			localStorage.setItem( 'kdv-theme', theme );
		} catch ( e ) {
			// Ignore storage errors (private browsing, disabled storage, etc.).
		}
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		var toggle = document.getElementById( 'kdv-dark-toggle' );
		if ( ! toggle ) {
			return;
		}
		toggle.addEventListener( 'click', function () {
			setTheme( 'dark' === currentTheme() ? 'light' : 'dark' );
		} );
	} );
} )();
