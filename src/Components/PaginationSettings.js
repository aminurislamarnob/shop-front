import { __ } from '@wordpress/i18n';
import { useState, useEffect } from 'react';
import {
	Button,
	Card,
	CardBody,
	Notice,
	TextControl,
	Spinner,
} from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';

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
	const [ pagination, setPagination ] = useState( {} );
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
		} catch ( error ) {
			setError( error.message );
			setMessage( '' );
			setIsLoading( false );
		}
	};

	return (
		<div>
			<div className="settings-header">
				<div className="settings-header-icon">
					<svg
						xmlns="http://www.w3.org/2000/svg"
						width="16"
						height="16"
						fill="currentColor"
						class="bi bi-collection"
						viewBox="0 0 16 16"
					>
						<path d="M2.5 3.5a.5.5 0 0 1 0-1h11a.5.5 0 0 1 0 1zm2-2a.5.5 0 0 1 0-1h7a.5.5 0 0 1 0 1zM0 13a1.5 1.5 0 0 0 1.5 1.5h13A1.5 1.5 0 0 0 16 13V6a1.5 1.5 0 0 0-1.5-1.5h-13A1.5 1.5 0 0 0 0 6zm1.5.5A.5.5 0 0 1 1 13V6a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-.5.5z" />
					</svg>
				</div>
				<h2>{ __( 'Pagination Settings', 'storesuite' ) }</h2>
			</div>
			{ message && (
				<Notice
					className="w-full mb-4"
					status="success"
					isDismissible
					onDismiss={ () => setMessage( '' ) }
				>
					{ message }
				</Notice>
			) }
			{ error && (
				<Notice
					className="w-full mb-4"
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
