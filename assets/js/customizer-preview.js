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

	// Menús y efectos: resalte al pasar el ratón.
	var rootStyle = document.documentElement.style;

	wp.customize( 'kdv_hover_color', function ( value ) {
		value.bind( function ( newval ) {
			// Vacío = volver al color primario (la variable deja de existir
			// y el CSS usa su valor de respaldo, var(--kdv-primary)).
			if ( newval ) {
				rootStyle.setProperty( '--kdv-hover-color', newval );
			} else {
				rootStyle.removeProperty( '--kdv-hover-color' );
			}
		} );
	} );

	wp.customize( 'kdv_hover_speed', function ( value ) {
		value.bind( function ( newval ) {
			rootStyle.setProperty( '--kdv-hover-duration', Math.min( 600, Math.max( 0, parseInt( newval, 10 ) || 0 ) ) + 'ms' );
		} );
	} );

	wp.customize( 'kdv_platform_hover_bg', function ( value ) {
		value.bind( function ( newval ) {
			var mixes = ( window.KdvPreview && window.KdvPreview.platformHoverMixes ) || {};
			if ( Object.prototype.hasOwnProperty.call( mixes, newval ) ) {
				rootStyle.setProperty( '--kdv-platform-hover-mix', mixes[ newval ] + '%' );
			}
		} );
	} );

	wp.customize( 'kdv_platform_icon_effect', function ( value ) {
		value.bind( function ( newval ) {
			var body = document.body;
			[ 'lift', 'zoom', 'none' ].forEach( function ( fx ) {
				body.classList.remove( 'kdv-platform-icon-fx-' + fx );
			} );
			body.classList.add( 'kdv-platform-icon-fx-' + newval );
		} );
	} );

	// Barra lateral y widgets.
	var swapBodyClass = function ( prefix, options, value ) {
		options.forEach( function ( option ) {
			document.body.classList.remove( prefix + option );
		} );
		document.body.classList.add( prefix + value );
	};
	var previewData = window.KdvPreview || {};

	wp.customize( 'kdv_widget_box', function ( value ) {
		value.bind( function ( newval ) {
			swapBodyClass( 'kdv-widgets-', [ 'card', 'border', 'flat' ], newval );
		} );
	} );

	wp.customize( 'kdv_widget_title_style', function ( value ) {
		value.bind( function ( newval ) {
			swapBodyClass( 'kdv-widget-title-', [ 'bar', 'underline', 'plain' ], newval );
		} );
	} );

	wp.customize( 'kdv_widget_title_size', function ( value ) {
		value.bind( function ( newval ) {
			var sizes = previewData.widgetTitleSizes || {};
			if ( sizes[ newval ] ) {
				rootStyle.setProperty( '--kdv-widget-title-size', sizes[ newval ] + 'rem' );
			}
		} );
	} );

	wp.customize( 'kdv_widget_radius', function ( value ) {
		value.bind( function ( newval ) {
			var radii = previewData.widgetRadii || {};
			if ( radii[ newval ] ) {
				rootStyle.setProperty( '--kdv-widget-radius', radii[ newval ] + 'px' );
			}
		} );
	} );

	wp.customize( 'kdv_widget_accent', function ( value ) {
		value.bind( function ( newval ) {
			if ( newval ) {
				rootStyle.setProperty( '--kdv-widget-accent', newval );
			} else {
				rootStyle.removeProperty( '--kdv-widget-accent' );
			}
		} );
	} );

	wp.customize( 'kdv_widget_separators', function ( value ) {
		value.bind( function ( newval ) {
			document.body.classList.toggle( 'kdv-widget-no-separators', ! newval );
		} );
	} );

	wp.customize( 'kdv_sidebar_sticky', function ( value ) {
		value.bind( function ( newval ) {
			document.body.classList.toggle( 'kdv-sidebar-sticky', !! newval );
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
