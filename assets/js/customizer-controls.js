/**
 * Small niceties for the Customizer controls panel itself
 * (left side, not the live preview iframe).
 */
( function ( wp ) {
	if ( ! wp || ! wp.customize ) {
		return;
	}
	// Reserved for future controls-panel UX tweaks (kept intentionally minimal —
	// this theme relies on core WP_Customize_Color_Control / Image_Control,
	// which already provide their own polished UI, no need to reinvent it).
} )( window.wp );
