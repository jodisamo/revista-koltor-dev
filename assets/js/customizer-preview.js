/**
 * Live preview bindings for postMessage Customizer settings.
 * Runs inside the Customizer preview iframe.
 */
( function ( wp ) {
	if ( ! wp || ! wp.customize ) {
		return;
	}

	// Brand colours: update CSS custom properties directly, no reload.
	wp.customize( 'kdv_color_primary', function ( value ) {
		value.bind( function ( newval ) {
			document.documentElement.style.setProperty( '--kdv-primary', newval );
		} );
	} );

	wp.customize( 'kdv_color_secondary', function ( value ) {
		value.bind( function ( newval ) {
			document.documentElement.style.setProperty( '--kdv-secondary', newval );
		} );
	} );

	wp.customize( 'kdv_color_accent', function ( value ) {
		value.bind( function ( newval ) {
			document.documentElement.style.setProperty( '--kdv-accent', newval );
		} );
	} );

	// Hero title / subtitle.
	wp.customize( 'kdv_hero_title', function ( value ) {
		value.bind( function ( newval ) {
			var el = document.querySelector( '.kdv-hero__title' );
			if ( el ) {
				el.textContent = newval;
			}
		} );
	} );

	wp.customize( 'kdv_hero_subtitle', function ( value ) {
		value.bind( function ( newval ) {
			var el = document.querySelector( '.kdv-hero__subtitle' );
			if ( el ) {
				el.textContent = newval;
			}
		} );
	} );

	// Footer copyright (keeps %year% behaviour in sync with PHP).
	wp.customize( 'kdv_footer_text', function ( value ) {
		value.bind( function ( newval ) {
			var el = document.querySelector( '.kdv-footer__copyright' );
			if ( el ) {
				el.textContent = newval.replace( '%year%', new Date().getFullYear() );
			}
		} );
	} );
} )( window.wp );
