/**
 * WordPress dependencies
 */
import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	Button,
	Card,
	CardBody,
	Notice,
	TextControl,
	Spinner,
} from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';

/**
 * Internal dependencies
 */
import { CollectionIcon } from './icons';

const PAGINATION_FIELDS = [
	{
		key: 'productPerPage',
		apiKey: 'storesuite_product_per_page',
		label: __( 'Products per page', 'storesuite' ),
		help: __( 'Default: 10', 'storesuite' ),
	},
	{
		key: 'orderPerPage',
		apiKey: 'storesuite_order_per_page',
		label: __( 'Orders per page', 'storesuite' ),
		help: __( 'Default: 10', 'storesuite' ),
	},
	{
		key: 'categoryPerPage',
		apiKey: 'storesuite_category_per_page',
		label: __( 'Categories per page', 'storesuite' ),
		help: __( 'Default: 10', 'storesuite' ),
	},
	{
		key: 'tagPerPage',
		apiKey: 'storesuite_tag_per_page',
		label: __( 'Tags per page', 'storesuite' ),
		help: __( 'Default: 10', 'storesuite' ),
	},
	{
		key: 'brandPerPage',
		apiKey: 'storesuite_brand_per_page',
		label: __( 'Brands per page', 'storesuite' ),
		help: __( 'Default: 10', 'storesuite' ),
	},
	{
		key: 'couponPerPage',
		apiKey: 'storesuite_coupon_per_page',
		label: __( 'Coupons per page', 'storesuite' ),
		help: __( 'Default: 10', 'storesuite' ),
	},
];

const PaginationSettings = () => {
	const [ pagination, setPagination ] = useState( () =>
		Object.fromEntries(
			PAGINATION_FIELDS.map( ( { key } ) => [ key, '' ] )
		)
	);
	const [ isLoading, setIsLoading ] = useState( false );
	const [ message, setMessage ] = useState( '' );
	const [ error, setError ] = useState( '' );

	// Fetch plugin settings.
	useEffect( () => {
		setIsLoading( true );
		const fetchSettings = async () => {
			try {
				const response = await apiFetch( {
					path: '/storesuite/v1/settings',
				} );

				// Pagination fields.
				const paginationData = {};
				PAGINATION_FIELDS.forEach( ( { key, apiKey } ) => {
					paginationData[ key ] =
						response[ apiKey ] !== undefined
							? String( response[ apiKey ] )
							: '';
				} );
				setPagination( paginationData );

				setError( null );
				setIsLoading( false );
			} catch ( err ) {
				setError( err.message );
				setIsLoading( false );
			}
		};

		fetchSettings();
	}, [] );

	// Handle submit to save pagination settings.
	const handleSubmit = async ( event ) => {
		event.preventDefault();
		setIsLoading( true );
		try {
			const data = {};
			PAGINATION_FIELDS.forEach( ( { key, apiKey } ) => {
				data[ apiKey ] = pagination[ key ] ?? '';
			} );

			const response = await apiFetch( {
				path: '/storesuite/v1/settings',
				method: 'POST',
				data,
			} );

			// Pagination fields.
			const paginationData = {};
			PAGINATION_FIELDS.forEach( ( { key, apiKey } ) => {
				paginationData[ key ] =
					response[ apiKey ] !== undefined
						? String( response[ apiKey ] )
						: '';
			} );
			setPagination( paginationData );

			setMessage( __( 'Settings saved successfully!', 'storesuite' ) );
			setError( '' );
			setIsLoading( false );
		} catch ( submitError ) {
			setError( submitError.message );
			setMessage( '' );
			setIsLoading( false );
		}
	};

	return (
		<div>
			<div className="settings-header">
				<div className="settings-header-icon">
					<CollectionIcon />
				</div>
				<h2>{ __( 'Pagination Settings', 'storesuite' ) }</h2>
			</div>
			{ message && (
				<Notice
					className="storesuite-notice"
					status="success"
					isDismissible
					onDismiss={ () => setMessage( '' ) }
				>
					{ message }
				</Notice>
			) }
			{ error && (
				<Notice
					className="storesuite-notice"
					status="error"
					isDismissible
					onDismiss={ () => setError( '' ) }
				>
					{ error }
				</Notice>
			) }
			<form onSubmit={ handleSubmit }>
				<Card>
					<CardBody>
						{ PAGINATION_FIELDS.map( ( { key, label, help } ) => (
							<div
								key={ key }
								className="storesuite-settings-group"
							>
								<TextControl
									label={ label }
									help={ help }
									value={ pagination[ key ] }
									onChange={ ( value ) =>
										setPagination( ( prev ) => ( {
											...prev,
											[ key ]: value,
										} ) )
									}
								/>
							</div>
						) ) }
						<Button
							variant="primary"
							type="submit"
							disabled={ isLoading }
						>
							{ isLoading && <Spinner /> }
							{ __( 'Save Changes', 'storesuite' ) }
						</Button>
					</CardBody>
				</Card>
			</form>
		</div>
	);
};

export default PaginationSettings;
