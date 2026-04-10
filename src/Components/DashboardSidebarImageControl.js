import { __ } from '@wordpress/i18n';
import { useState, useEffect } from 'react';
import { Button } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';

/**
 * @param {number} attachmentId
 * @return {string} Preview URL or empty.
 */
function useAttachmentPreviewUrl( attachmentId ) {
	const [ previewUrl, setPreviewUrl ] = useState( '' );

	useEffect( () => {
		let cancelled = false;
		const id = parseInt( attachmentId, 10 ) || 0;

		if ( ! id ) {
			setPreviewUrl( '' );
			return;
		}

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
	}, [ attachmentId ] );

	return previewUrl;
}

/**
 * Image picker for dashboard sidebar logo / icon (wp.media + REST preview).
 *
 * @param {Object}   props
 * @param {string}   props.label
 * @param {string}   [props.help]
 * @param {number}   props.attachmentId
 * @param {Function} props.onChange     Receives attachment id or 0 when cleared.
 */
export default function DashboardSidebarImageControl( {
	label,
	help,
	attachmentId,
	onChange,
} ) {
	const previewUrl = useAttachmentPreviewUrl( attachmentId );
	const idNum = parseInt( attachmentId, 10 ) || 0;

	const openMediaLibrary = () => {
		if ( typeof window.wp === 'undefined' || ! window.wp.media ) {
			return;
		}
		const frame = window.wp.media( {
			title: __( 'Select image', 'storesuite' ),
			button: {
				text: __( 'Use this image', 'storesuite' ),
			},
			multiple: false,
			library: { type: 'image' },
		} );
		frame.on( 'select', () => {
			const attachment = frame
				.state()
				.get( 'selection' )
				.first()
				.toJSON();
			if ( attachment && attachment.id ) {
				onChange( attachment.id );
			}
		} );
		frame.open();
	};

	return (
		<div className="storesuite-settings-group storesuite-sidebar-image-control">
			<p className="storesuite-sidebar-image-control__label">{ label }</p>
			{ help ? (
				<p className="storesuite-sidebar-image-control__help">
					{ help }
				</p>
			) : null }
			{ previewUrl ? (
				<div className="storesuite-sidebar-image-control__preview">
					<img src={ previewUrl } alt="" />
				</div>
			) : null }
			<div className="storesuite-sidebar-image-control__actions">
				<Button variant="secondary" onClick={ openMediaLibrary }>
					{ idNum
						? __( 'Replace image', 'storesuite' )
						: __( 'Select image', 'storesuite' ) }
				</Button>
				{ idNum > 0 ? (
					<Button
						isDestructive
						variant="tertiary"
						onClick={ () => onChange( 0 ) }
					>
						{ __( 'Remove', 'storesuite' ) }
					</Button>
				) : null }
			</div>
		</div>
	);
}
