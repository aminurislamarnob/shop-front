import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';

export default function DashboardSidebarImageControl( {
	label,
	help,
	attachmentId,
	onChange,
} ) {
	const [ previewUrl, setPreviewUrl ] = useState( '' );
	const id = parseInt( attachmentId, 10 ) || 0;

	useEffect( () => {
		if ( ! id ) {
			setPreviewUrl( '' );
			return;
		}
		let cancelled = false;
		apiFetch( { path: `/wp/v2/media/${ id }` } )
			.then( ( media ) => {
				if ( ! cancelled && media?.source_url ) {
					setPreviewUrl( media.source_url );
				}
			} )
			.catch( () => {
				if ( ! cancelled ) {
					setPreviewUrl( '' );
				}
			} );
		return () => {
			cancelled = true;
		};
	}, [ id ] );

	const openMediaLibrary = () => {
		if ( ! window.wp?.media ) {
			return;
		}
		const frame = window.wp.media( {
			title: __( 'Select image', 'storesuite' ),
			button: { text: __( 'Use this image', 'storesuite' ) },
			multiple: false,
			library: { type: 'image' },
		} );
		frame.on( 'select', () => {
			const attachment = frame
				.state()
				.get( 'selection' )
				.first()
				.toJSON();
			if ( attachment?.id ) {
				onChange( attachment.id );
			}
		} );
		frame.open();
	};

	return (
		<div className="storesuite-settings-group storesuite-sidebar-image-control">
			<p className="storesuite-sidebar-image-control__label">{ label }</p>
			{ help && (
				<p className="storesuite-sidebar-image-control__help">
					{ help }
				</p>
			) }
			{ previewUrl && (
				<div className="storesuite-sidebar-image-control__preview">
					<img src={ previewUrl } alt="" />
				</div>
			) }
			<div className="storesuite-sidebar-image-control__actions">
				<Button variant="secondary" onClick={ openMediaLibrary }>
					{ id
						? __( 'Replace image', 'storesuite' )
						: __( 'Select image', 'storesuite' ) }
				</Button>
				{ id > 0 && (
					<Button
						isDestructive
						variant="tertiary"
						onClick={ () => onChange( 0 ) }
					>
						{ __( 'Remove', 'storesuite' ) }
					</Button>
				) }
			</div>
		</div>
	);
}
