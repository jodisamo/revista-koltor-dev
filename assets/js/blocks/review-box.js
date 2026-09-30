/**
 * "Caja de Reseña" block — editor UI.
 * Plain wp.element.createElement calls, no JSX / build step needed.
 */
( function ( blocks, element, blockEditor, components, i18n, mediaUtils ) {
	var el = element.createElement;
	var __ = i18n.__;
	var InspectorControls = blockEditor.InspectorControls;
	var MediaUpload = blockEditor.MediaUpload || ( mediaUtils && mediaUtils.MediaUpload );
	var useBlockProps = blockEditor.useBlockProps;

	blocks.registerBlockType( 'revista-koltor-dev/review-box', {
		edit: function ( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var blockProps = useBlockProps ? useBlockProps( { className: 'kdv-review-box kdv-review-box--editor' } ) : {};

			function onSelectImage( media ) {
				setAttributes( {
					imagenUrl: media.url,
					imagenId: media.id,
				} );
			}

			var inspector = el(
				InspectorControls,
				{},
				el(
					components.PanelBody,
					{ title: __( 'Puntuación', 'revista-koltor-dev' ) },
					el( components.RangeControl, {
						label: __( 'Puntuación (0-10)', 'revista-koltor-dev' ),
						value: attributes.puntuacion,
						min: 0,
						max: 10,
						step: 0.1,
						onChange: function ( value ) {
							setAttributes( { puntuacion: value } );
						},
					} )
				),
				el(
					components.PanelBody,
					{ title: __( 'Imagen', 'revista-koltor-dev' ), initialOpen: false },
					MediaUpload
						? el( MediaUpload, {
								onSelect: onSelectImage,
								allowedTypes: [ 'image' ],
								render: function ( obj ) {
									return el(
										components.Button,
										{ onClick: obj.open, variant: 'secondary' },
										attributes.imagenUrl ? __( 'Cambiar imagen', 'revista-koltor-dev' ) : __( 'Elegir imagen', 'revista-koltor-dev' )
									);
								},
						  } )
						: null
				)
			);

			return el(
				element.Fragment,
				{},
				inspector,
				el(
					'div',
					blockProps,
					attributes.imagenUrl
						? el( 'img', {
								src: attributes.imagenUrl,
								className: 'kdv-review-box__image',
								style: { maxWidth: '160px', display: 'block', marginBottom: '12px' },
						  } )
						: null,
					el( components.TextControl, {
						label: __( 'Título', 'revista-koltor-dev' ),
						value: attributes.titulo,
						onChange: function ( value ) {
							setAttributes( { titulo: value } );
						},
					} ),
					el( components.TextareaControl, {
						label: __( 'Resumen', 'revista-koltor-dev' ),
						value: attributes.resumen,
						onChange: function ( value ) {
							setAttributes( { resumen: value } );
						},
					} ),
					el( components.TextareaControl, {
						label: __( 'Pros (una línea por punto)', 'revista-koltor-dev' ),
						value: attributes.pros,
						onChange: function ( value ) {
							setAttributes( { pros: value } );
						},
					} ),
					el( components.TextareaControl, {
						label: __( 'Contras (una línea por punto)', 'revista-koltor-dev' ),
						value: attributes.contras,
						onChange: function ( value ) {
							setAttributes( { contras: value } );
						},
					} )
				)
			);
		},
		save: function () {
			// Dynamic block: markup comes entirely from render.php on the server.
			return null;
		},
	} );
} )(
	window.wp.blocks,
	window.wp.element,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.i18n,
	window.wp.mediaUtils
);
