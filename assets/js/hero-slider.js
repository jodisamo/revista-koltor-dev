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

		var hasAutoplay = settings.autoplay && config.loop;

		if ( hasAutoplay ) {
			config.autoplay = {
				delay: settings.autoplaySpeed || 5000,
				disableOnInteraction: false,
				pauseOnMouseEnter: true,
			};
		}

		var swiper = new Swiper( el, config );

		if ( ! hasAutoplay ) {
			return;
		}

		/*
		 * Botón de pausa. Todo lo que se mueve solo durante más de 5
		 * segundos tiene que poder detenerse (WCAG 2.2.2): pausar al pasar el
		 * ratón no sirve en móvil ni con teclado. Con "reducir movimiento"
		 * activado en el sistema, el pase arranca ya detenido.
		 */
		var userPaused = !! ( window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches );

		var button = document.createElement( 'button' );
		button.type = 'button';
		button.className = 'kdv-hero-slider__pause';
		button.innerHTML = '<span aria-hidden="true"></span>';

		var sync = function () {
			button.classList.toggle( 'is-paused', userPaused );
			button.setAttribute( 'aria-label', userPaused ? ( settings.labelPlay || 'Play' ) : ( settings.labelPause || 'Pause' ) );
		};

		button.addEventListener( 'click', function () {
			userPaused = ! userPaused;
			if ( userPaused ) {
				swiper.autoplay.stop();
			} else {
				swiper.autoplay.start();
			}
			sync();
		} );

		// Al salir el ratón Swiper reanuda por su cuenta: si quien visita lo
		// había pausado con el botón, se vuelve a detener.
		swiper.on( 'autoplayResume autoplayStart', function () {
			if ( userPaused ) {
				swiper.autoplay.stop();
			}
		} );

		if ( userPaused ) {
			swiper.autoplay.stop();
		}

		el.appendChild( button );
		sync();
	} );
}() );
