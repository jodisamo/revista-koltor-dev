/**
 * Hero slider init (Swiper.js). Only runs on the magazine homepage when at
 * least one "Diapositiva" has been published — see includes/core/enqueue.php
 * for the conditional enqueue and includes/core/template-tags.php for the
 * KdvHeroSlider settings localized from the Customizer.
 *
 * @package Revista_Koltor_Dev
 */
( function() {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function() {
		var el = document.querySelector( '.kdv-hero-slider .swiper' );

		if ( ! el || typeof Swiper === 'undefined' ) {
			return;
		}

		var settings = window.KdvHeroSlider || {};
		var effect   = 'slide' === settings.effect ? 'slide' : 'fade';

		var config = {
			effect: effect,
			fadeEffect: { crossFade: true },
			loop: el.querySelectorAll( '.swiper-slide' ).length > 1,
			speed: 600,
			pagination: settings.dots ? {
				el: '.kdv-hero-slider .swiper-pagination',
				clickable: true,
			} : false,
			navigation: settings.arrows ? {
				nextEl: '.kdv-hero-slider .swiper-button-next',
				prevEl: '.kdv-hero-slider .swiper-button-prev',
			} : false,
			a11y: { enabled: true },
			keyboard: { enabled: true },
		};

		if ( settings.autoplay ) {
			config.autoplay = {
				delay: settings.autoplaySpeed || 5000,
				disableOnInteraction: false,
				pauseOnMouseEnter: true,
			};
		}

		// eslint-disable-next-line no-new
		new Swiper( el, config );
	} );
}() );
