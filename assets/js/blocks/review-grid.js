/**
 * "Grid de Reseñas" block — editor UI.
 * Uses ServerSideRender so the editor preview matches the PHP output
 * exactly (same render.php that powers the frontend).
 */
( function ( blocks, element, blockEditor, components, i18n, ServerSideRender ) {
	var el = element.createElement;
	var __ = i18n.__;
	var InspectorControls = blockEditor.InspectorControls;
	var useBlockProps = blockEditor.useBlockProps;

	blocks.registerBlockType( 'revista-koltor-dev/review-grid', {
		edit: function ( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var blockProps = useBlockProps ? useBlockProps() : {};

			var inspector = el(
				InspectorControls,
				{},
				el(
					components.PanelBody,
					{ title: __( 'Ajustes de la cuadrícula', 'revista-koltor-dev' ) },
					el( components.TextControl, {
						label: __( 'Título de la sección', 'revista-koltor-dev' ),
						value: attributes.titulo,
						onChange: function ( value ) {
							setAttributes( { titulo: value } );
						},
					} ),
					el( components.RangeControl, {
						label: __( 'Cantidad de reseñas', 'revista-koltor-dev' ),
						value: attributes.cantidad,
						min: 1,
						max: 12,
						onChange: function ( value ) {
							setAttributes( { cantidad: value } );
						},
					} ),
					el( components.RangeControl, {
						label: __( 'Columnas', 'revista-koltor-dev' ),
						value: attributes.columnas,
						min: 2,
						max: 4,
						onChange: function ( value ) {
							setAttributes( { columnas: value } );
						},
					} ),
					el( components.TextControl, {
						label: __( 'Filtrar por género (slug, opcional)', 'revista-koltor-dev' ),
						help: __( 'Ej: acción, estrategia, indie. Déjalo vacío para mostrar todos los géneros.', 'revista-koltor-dev' ),
						value: attributes.generoSlug,
						onChange: function ( value ) {
							setAttributes( { generoSlug: value } );
						},
					} ),
					el( components.ToggleControl, {
						label: __( 'Mostrar puntuación en las tarjetas', 'revista-koltor-dev' ),
						checked: attributes.mostrarPuntuacion,
						onChange: function ( value ) {
							setAttributes( { mostrarPuntuacion: value } );
						},
					} )
				)
			);

			return el(
				element.Fragment,
				{},
				inspector,
				el(
					'div',
					blockProps,
					ServerSideRender
						? el( ServerSideRender, {
								block: 'revista-koltor-dev/review-grid',
								attributes: attributes,
						  } )
						: el( 'p', {}, __( 'Vista previa no disponible en este editor.', 'revista-koltor-dev' ) )
				)
			);
		},
		save: function () {
			return null;
		},
	} );
} )(
	window.wp.blocks,
	window.wp.element,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.i18n,
	window.wp.serverSideRender
);
