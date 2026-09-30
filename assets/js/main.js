/**
 * Core front-end interactions: mobile menu toggle, search toggle.
 * Vanilla JS, no jQuery dependency.
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {

		// Mobile menu (offcanvas) toggle.
		var menuToggle = document.getElementById( 'kdv-menu-toggle' );
		var siteHeader  = document.querySelector( '.kdv-header' );

		var navPanel = siteHeader ? siteHeader.querySelector( '.kdv-header__nav' ) : null;

		/*
		 * ¿Está activo el panel deslizante? Por encima del punto de corte el
		 * menú es una barra horizontal normal y NO hay que retener el foco
		 * dentro de él ni moverlo a ninguna parte.
		 */
		var kdvOffcanvasActive = function () {
			return ! window.matchMedia || window.matchMedia( '(max-width: 1100px)' ).matches;
		};

		var KDV_FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

		// Solo lo que de verdad se puede enfocar: un enlace de un submenú
		// oculto tiene tamaño cero y no debe entrar en el recorrido.
		var kdvPanelFocusables = function () {
			if ( ! navPanel ) {
				return [];
			}
			return Array.prototype.filter.call( navPanel.querySelectorAll( KDV_FOCUSABLE ), function ( el ) {
				return el.offsetWidth > 0 || el.offsetHeight > 0;
			} );
		};

		if ( menuToggle && siteHeader ) {
			// Se guarda la etiqueta original para poder alternarla: mientras el
			// panel está abierto, el mismo botón anuncia "Cerrar menú".
			var menuLabelOpen  = menuToggle.getAttribute( 'aria-label' ) || '';
			var menuLabelClose = menuToggle.getAttribute( 'data-label-close' ) || menuLabelOpen;

			var closeNav = function () {
				var focusWasInside = !! ( navPanel && document.activeElement &&
					( navPanel.contains( document.activeElement ) || document.activeElement === document.body ) );

				siteHeader.classList.remove( 'kdv-nav-open' );
				document.documentElement.classList.remove( 'kdv-nav-locked' );
				menuToggle.setAttribute( 'aria-expanded', 'false' );
				menuToggle.setAttribute( 'aria-label', menuLabelOpen );

				/*
				 * El foco vuelve a la hamburguesa. Sin esto, al cerrar con el
				 * botón "×" o con el fondo oscuro el foco se quedaba en un
				 * elemento que acaba de desaparecer y el siguiente tabulador
				 * saltaba al principio de la página.
				 */
				if ( focusWasInside && kdvOffcanvasActive() ) {
					menuToggle.focus();
				}
			};

			var openNav = function () {
				siteHeader.classList.add( 'kdv-nav-open' );
				// Con el panel abierto se bloquea el desplazamiento del fondo:
				// si no, el dedo arrastra la página de debajo y el visitante
				// pierde de vista tanto el panel como el botón de cerrar.
				document.documentElement.classList.add( 'kdv-nav-locked' );
				menuToggle.setAttribute( 'aria-expanded', 'true' );
				menuToggle.setAttribute( 'aria-label', menuLabelClose );

				/*
				 * Se lleva el foco DENTRO del panel. Es imprescindible: en el
				 * HTML la hamburguesa va después del <nav>, así que al abrir el
				 * menú y pulsar el tabulador el foco no entraba en el panel —
				 * se iba al contenido de la página que está debajo del fondo
				 * oscuro, invisible pero enfocable.
				 */
				if ( kdvOffcanvasActive() ) {
					var first = kdvPanelFocusables()[0];
					if ( first ) {
						first.focus();
					}
				}
			};

			menuToggle.addEventListener( 'click', function () {
				if ( siteHeader.classList.contains( 'kdv-nav-open' ) ) {
					closeNav();
				} else {
					openNav();
				}
			} );

			/*
			 * El foco se queda dentro del panel mientras está abierto. Un panel
			 * que tapa la página con un fondo oscuro pero deja tabular por
			 * detrás es una trampa para quien navega con teclado o con acceso
			 * por conmutador: se recorren enlaces que no se ven.
			 */
			document.addEventListener( 'keydown', function ( event ) {
				if ( 'Tab' !== event.key || ! siteHeader.classList.contains( 'kdv-nav-open' ) || ! kdvOffcanvasActive() ) {
					return;
				}
				var focusables = kdvPanelFocusables();
				if ( ! focusables.length ) {
					return;
				}
				var first = focusables[0];
				var last  = focusables[ focusables.length - 1 ];

				// Si el foco se ha escapado del panel, se devuelve al principio.
				if ( ! navPanel.contains( document.activeElement ) ) {
					event.preventDefault();
					first.focus();
					return;
				}
				if ( event.shiftKey && document.activeElement === first ) {
					event.preventDefault();
					last.focus();
				} else if ( ! event.shiftKey && document.activeElement === last ) {
					event.preventDefault();
					first.focus();
				}
			} );

			// Al girar el móvil o ensanchar la ventana por encima del punto de
			// corte, el panel deslizante vuelve a ser una barra normal: si la
			// clase se quedara puesta, el menú quedaría "abierto" sin panel.
			if ( window.matchMedia ) {
				var navBreakpoint = window.matchMedia( '(min-width: 1101px)' );
				var onNavBreakpoint = function ( event ) {
					if ( event.matches ) {
						closeNav();
					}
				};
				if ( navBreakpoint.addEventListener ) {
					navBreakpoint.addEventListener( 'change', onNavBreakpoint );
				} else if ( navBreakpoint.addListener ) {
					navBreakpoint.addListener( onNavBreakpoint );
				}
			}

			// Cuatro formas de cerrarlo, porque en un móvil la hamburguesa
			// puede quedar bajo el dedo equivocado: el botón de cerrar, tocar
			// el fondo oscuro, la tecla Escape, y elegir cualquier enlace.
			var navClose = document.getElementById( 'kdv-nav-close' );
			if ( navClose ) {
				navClose.addEventListener( 'click', closeNav );
			}

			var navBackdrop = document.getElementById( 'kdv-nav-backdrop' );
			if ( navBackdrop ) {
				navBackdrop.addEventListener( 'click', closeNav );
			}

			document.addEventListener( 'keydown', function ( event ) {
				if ( 'Escape' === event.key && siteHeader.classList.contains( 'kdv-nav-open' ) ) {
					closeNav();
					// closeNav ya devuelve el foco si estaba dentro; con Escape
					// puede pulsarse desde fuera, así que se asegura aquí.
					menuToggle.focus();
				}
			} );

			if ( navPanel ) {
				navPanel.addEventListener( 'click', function ( event ) {
					// Solo los enlaces que llevan a otra parte; los que abren
					// un submenú (href="#") no deberían cerrar el panel.
					var link = event.target.closest( 'a[href]' );
					if ( link && '#' !== link.getAttribute( 'href' ) ) {
						closeNav();
					}
				} );
			}
		}

		/*
		 * Desplegables del menú de escritorio que no caben en la pantalla.
		 *
		 * Los submenús se anclan al borde izquierdo de su categoría y miden
		 * 230px, así que en las últimas categorías del menú se salían por la
		 * derecha; y el tercer nivel, que se abre a la derecha del submenú
		 * padre, se salía a cualquier ancho de ventana — una subcategoría de
		 * una subcategoría quedaba fuera de la pantalla y era inalcanzable.
		 *
		 * Aquí se mide cada submenú en el momento de abrirse (es cuando el
		 * navegador ya le ha dado tamaño) y, si no cabe, se le pone la clase
		 * que en main.css lo voltea hacia el lado contrario. Medir es la única
		 * forma fiable: depende del ancho de la ventana, de cuántas categorías
		 * haya en el menú y de lo largos que sean sus nombres.
		 */
		var primaryMenu = document.querySelector( '.kdv-primary-menu' );
		if ( primaryMenu ) {
			var directSubMenu = function ( item ) {
				for ( var i = 0; i < item.children.length; i++ ) {
					if ( item.children[ i ].classList.contains( 'sub-menu' ) ) {
						return item.children[ i ];
					}
				}
				return null;
			};

			var placeSubMenu = function ( item ) {
				// En el panel deslizante los submenús van en línea, no flotando:
				// ahí no hay nada que voltear.
				if ( kdvOffcanvasActive() ) {
					item.classList.remove( 'kdv-submenu-flip' );
					return;
				}
				var sub = directSubMenu( item );
				if ( ! sub ) {
					return;
				}
				var width = sub.offsetWidth;
				if ( ! width ) {
					return; // Todavía oculto: nada que medir.
				}
				/*
				 * Se calcula dónde CAERÍA sin voltear, no dónde está ahora —
				 * si no, una vez volteado se mediría a sí mismo y la decisión
				 * se quedaría pegada.
				 */
				var parentList = item.parentNode;
				var parentSub  = ( parentList && parentList.classList && parentList.classList.contains( 'sub-menu' ) ) ? parentList : null;
				var naturalLeft = parentSub
					? parentSub.getBoundingClientRect().right
					: item.getBoundingClientRect().left;

				var margin = 8;
				item.classList.toggle( 'kdv-submenu-flip', ( naturalLeft + width ) > ( window.innerWidth - margin ) );
			};

			var parentItems = primaryMenu.querySelectorAll( '.menu-item-has-children' );
			Array.prototype.forEach.call( parentItems, function ( item ) {
				// "mouseenter" no propaga, así que va en cada item.
				item.addEventListener( 'mouseenter', function () { placeSubMenu( item ); } );
			} );

			// El teclado abre los submenús con :focus-within, que no dispara
			// mouseenter — de ahí este segundo camino.
			primaryMenu.addEventListener( 'focusin', function ( event ) {
				var item = event.target.closest ? event.target.closest( '.menu-item-has-children' ) : null;
				while ( item ) {
					placeSubMenu( item );
					item = item.parentNode && item.parentNode.closest ? item.parentNode.closest( '.menu-item-has-children' ) : null;
				}
			} );

			// Al cambiar el tamaño de la ventana las decisiones anteriores ya no
			// valen: se olvidan y se vuelven a tomar en la siguiente apertura.
			var resetFlips = function () {
				Array.prototype.forEach.call( parentItems, function ( item ) {
					item.classList.remove( 'kdv-submenu-flip' );
				} );
			};
			var resizeTimer = null;
			window.addEventListener( 'resize', function () {
				window.clearTimeout( resizeTimer );
				resizeTimer = window.setTimeout( resetFlips, 150 );
			}, { passive: true } );
		}

		/*
		 * Tablas anchas dentro del contenido. Una tabla de varias columnas es
		 * más ancha que la pantalla de un móvil y, al no poder encogerse por
		 * debajo de su contenido, arrastra consigo el ancho de toda la página
		 * (ver la nota de "Red de seguridad de anchura" en main.css). El
		 * bloque "Tabla" de Gutenberg ya viene envuelto en
		 * <figure class="wp-block-table">, que la hoja de estilos hace
		 * desplazable; las tablas pegadas a mano no traen ese envoltorio, así
		 * que se les pone aquí.
		 */
		Array.prototype.forEach.call( document.querySelectorAll( 'table' ), function ( table ) {
			// Las que ya están dentro de algo desplazable, y las de la barra
			// de administración de WordPress, se dejan como están.
			if ( ! table.closest || table.closest( '.wp-block-table, .kdv-table-scroll, #wpadminbar' ) ) {
				return;
			}
			if ( ! table.parentNode ) {
				return;
			}
			var wrap = document.createElement( 'div' );
			wrap.className = 'kdv-table-scroll';
			table.parentNode.insertBefore( wrap, table );
			wrap.appendChild( table );
		} );

		// Back to top button.
		var backToTop = document.getElementById( 'kdv-back-to-top' );
		if ( backToTop ) {
			// window.scrollY is enough on its own in a normal page, but some
			// plugins/page builders wrap the content in a scrolling container
			// instead of scrolling the window — falling back to
			// documentElement/body scrollTop keeps this working either way.
			var getScrollPos = function () {
				return window.scrollY || document.documentElement.scrollTop || document.body.scrollTop || 0;
			};
			var toggleBackToTop = function () {
				if ( getScrollPos() > 500 ) {
					backToTop.classList.add( 'is-visible' );
				} else {
					backToTop.classList.remove( 'is-visible' );
				}
			};
			window.addEventListener( 'scroll', toggleBackToTop, { passive: true } );
			document.addEventListener( 'scroll', toggleBackToTop, { passive: true, capture: true } );
			toggleBackToTop();

			backToTop.addEventListener( 'click', function () {
				window.scrollTo( { top: 0, behavior: 'smooth' } );
			} );
		}

		// "Populares del mes" slider — arrow buttons scroll the track; the
		// swipe/drag-to-scroll on mobile and trackpads is native (just a
		// horizontally scrolling list with CSS scroll-snap), no JS needed
		// for that part.
		var popularTrack = document.querySelector( '.kdv-popular-slider__track' );
		if ( popularTrack ) {
			var scrollPopularBy = function ( direction ) {
				var firstItem = popularTrack.querySelector( '.kdv-popular-slider__item' );
				var step      = firstItem ? firstItem.getBoundingClientRect().width + 18 : 200;
				popularTrack.scrollBy( { left: direction * step * 2, behavior: 'smooth' } );
			};
			var prevBtn = document.querySelector( '.kdv-popular-slider__nav--prev' );
			var nextBtn = document.querySelector( '.kdv-popular-slider__nav--next' );
			if ( prevBtn ) {
				prevBtn.addEventListener( 'click', function () { scrollPopularBy( -1 ); } );
			}
			if ( nextBtn ) {
				nextBtn.addEventListener( 'click', function () { scrollPopularBy( 1 ); } );
			}
		}

		// Header search toggle.
		var searchToggle = document.getElementById( 'kdv-search-toggle' );
		var searchForm   = document.getElementById( 'kdv-header-search' );

		if ( searchToggle && searchForm ) {
			searchToggle.addEventListener( 'click', function () {
				var isOpen = searchForm.classList.toggle( 'is-open' );
				searchToggle.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
				if ( isOpen ) {
					var input = searchForm.querySelector( 'input[type="search"]' );
					if ( input ) {
						input.focus();
					}
				}
			} );
		}

		// Share buttons: "copy link" — copies the button's data-copy-url to
		// the clipboard and shows a brief "¡Copiado!" confirmation. Falls
		// back to a hidden-textarea + execCommand for browsers/contexts
		// (e.g. non-HTTPS) without the async Clipboard API.
		var copyButtons = document.querySelectorAll( '.kdv-share-btn--copy' );
		if ( copyButtons.length ) {
			copyButtons.forEach( function ( button ) {
				button.addEventListener( 'click', function () {
					var url = button.getAttribute( 'data-copy-url' );
					if ( ! url ) {
						return;
					}

					var showCopied = function () {
						button.classList.add( 'is-copied' );
						window.setTimeout( function () {
							button.classList.remove( 'is-copied' );
						}, 1500 );
					};

					if ( navigator.clipboard && navigator.clipboard.writeText ) {
						navigator.clipboard.writeText( url ).then( showCopied, function () {
							kdvFallbackCopy( url, showCopied );
						} );
					} else {
						kdvFallbackCopy( url, showCopied );
					}
				} );
			} );
		}

		function kdvFallbackCopy( text, onSuccess ) {
			var textarea = document.createElement( 'textarea' );
			textarea.value = text;
			textarea.setAttribute( 'readonly', '' );
			textarea.style.position = 'absolute';
			textarea.style.left = '-9999px';
			document.body.appendChild( textarea );
			textarea.select();
			try {
				document.execCommand( 'copy' );
				onSuccess();
			} catch ( err ) {
				// Nothing we can do — the user can still copy the URL from the address bar.
			}
			document.body.removeChild( textarea );
		}

		// Header glass effect on scroll — only meaningful (and only styled in
		// main.css) while the header is sticky, but toggling the class here
		// unconditionally is harmless either way.
		if ( siteHeader ) {
			var toggleHeaderScrolled = function () {
				if ( window.scrollY > 12 ) {
					siteHeader.classList.add( 'kdv-header--scrolled' );
				} else {
					siteHeader.classList.remove( 'kdv-header--scrolled' );
				}
			};
			window.addEventListener( 'scroll', toggleHeaderScrolled, { passive: true } );
			toggleHeaderScrolled();
		}

		// Reading progress bar (single artículo/reseña only — the element
		// simply doesn't exist on other templates, so this is a no-op there).
		var readingProgressBar = document.querySelector( '.kdv-reading-progress__bar' );
		if ( readingProgressBar ) {
			var updateReadingProgress = function () {
				var scrollable = document.documentElement.scrollHeight - window.innerHeight;
				var progress   = scrollable > 0 ? ( window.scrollY / scrollable ) * 100 : 0;
				readingProgressBar.style.width = Math.min( 100, Math.max( 0, progress ) ) + '%';
			};
			window.addEventListener( 'scroll', updateReadingProgress, { passive: true } );
			window.addEventListener( 'resize', updateReadingProgress );
			updateReadingProgress();
		}

		// Fade-in-on-scroll for card grids and the ranking list. Progressive
		// enhancement: elements are only ever hidden once this script adds
		// "kdv-fade-ready" to <html> (see main.css) — with no JS, JS disabled,
		// no IntersectionObserver support, or "reduce motion" requested,
		// nothing is hidden and everything is simply visible right away.
		var prefersReducedMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
		if ( 'IntersectionObserver' in window && ! prefersReducedMotion ) {
			var fadeTargets = document.querySelectorAll( '.kdv-cards-grid > *, .kdv-ranking-item' );
			if ( fadeTargets.length ) {
				document.documentElement.classList.add( 'kdv-fade-ready' );
				var fadeObserver = new IntersectionObserver( function ( entries, observer ) {
					entries.forEach( function ( entry ) {
						if ( entry.isIntersecting ) {
							entry.target.classList.add( 'kdv-fade-in' );
							observer.unobserve( entry.target );
						}
					} );
				}, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' } );
				fadeTargets.forEach( function ( el ) {
					fadeObserver.observe( el );
				} );
			}
		}
	} );
} )();
