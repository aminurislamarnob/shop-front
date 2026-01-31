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

const PaginationSettings = () => {
	const [ productPerPage, setProductPerPage ] = useState( '' );
	const [ orderPerPage, setOrderPerPage ] = useState( '' );
	const [ categoryPerPage, setCategoryPerPage ] = useState( '' );
	const [ tagPerPage, setTagPerPage ] = useState( '' );
	const [ brandPerPage, setBrandPerPage ] = useState( '' );
	const [ couponPerPage, setCouponPerPage ] = useState( '' );
	const [ isLoading, setIsLoading ] = useState( false );
	const [ message, setMessage ] = useState( '' );
	const [ error, setError ] = useState( '' );

	useEffect( () => {
		setIsLoading( true );
		apiFetch( { path: '/storesuite/v1/settings' } )
			.then( ( response ) => {
				if ( response.storesuite_product_per_page != null )
					setProductPerPage(
						String( response.storesuite_product_per_page )
					);
				if ( response.storesuite_order_per_page != null )
					setOrderPerPage(
						String( response.storesuite_order_per_page )
					);
				if ( response.storesuite_category_per_page != null )
					setCategoryPerPage(
						String( response.storesuite_category_per_page )
					);
				if ( response.storesuite_tag_per_page != null )
					setTagPerPage( String( response.storesuite_tag_per_page ) );
				if ( response.storesuite_brand_per_page != null )
					setBrandPerPage(
						String( response.storesuite_brand_per_page )
					);
				if ( response.storesuite_coupon_per_page != null )
					setCouponPerPage(
						String( response.storesuite_coupon_per_page )
					);
			} )
			.catch( ( err ) => setError( err.message ) )
			.finally( () => setIsLoading( false ) );
	}, [] );

	const handleSubmit = async ( e ) => {
		e.preventDefault();
		setIsLoading( true );
		try {
			const response = await apiFetch( {
				path: '/storesuite/v1/settings',
				method: 'POST',
				data: {
					storesuite_product_per_page: productPerPage,
					storesuite_order_per_page: orderPerPage,
					storesuite_category_per_page: categoryPerPage,
					storesuite_tag_per_page: tagPerPage,
					storesuite_brand_per_page: brandPerPage,
					storesuite_coupon_per_page: couponPerPage,
				},
			} );
			setProductPerPage( response.storesuite_product_per_page ?? '' );
			setOrderPerPage( response.storesuite_order_per_page ?? '' );
			setCategoryPerPage( response.storesuite_category_per_page ?? '' );
			setTagPerPage( response.storesuite_tag_per_page ?? '' );
			setBrandPerPage( response.storesuite_brand_per_page ?? '' );
			setCouponPerPage( response.storesuite_coupon_per_page ?? '' );
			setMessage( __( 'Settings saved successfully!', 'storesuite' ) );
			setError( '' );
		} catch ( err ) {
			setError( err.message );
			setMessage( '' );
		} finally {
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
						className="bi bi-list-ul"
						viewBox="0 0 16 16"
					>
						<path
							fillRule="evenodd"
							d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm-2-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm-2-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm-1.5-3A1.5 1.5 0 0 0 0 2.5v9A1.5 1.5 0 0 0 1.5 13h9a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 10.5 0h-9z"
						/>
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
						<div className="storesuite-settings-group">
							<TextControl
								label={ __(
									'Products per page',
									'storesuite'
								) }
								help={ __( 'Default: 10', 'storesuite' ) }
								value={ productPerPage }
								onChange={ setProductPerPage }
							/>
						</div>
						<div className="storesuite-settings-group">
							<TextControl
								label={ __( 'Orders per page', 'storesuite' ) }
								help={ __( 'Default: 10', 'storesuite' ) }
								value={ orderPerPage }
								onChange={ setOrderPerPage }
							/>
						</div>
						<div className="storesuite-settings-group">
							<TextControl
								label={ __(
									'Categories per page',
									'storesuite'
								) }
								help={ __( 'Default: 10', 'storesuite' ) }
								value={ categoryPerPage }
								onChange={ setCategoryPerPage }
							/>
						</div>
						<div className="storesuite-settings-group">
							<TextControl
								label={ __( 'Tags per page', 'storesuite' ) }
								help={ __( 'Default: 10', 'storesuite' ) }
								value={ tagPerPage }
								onChange={ setTagPerPage }
							/>
						</div>
						<div className="storesuite-settings-group">
							<TextControl
								label={ __( 'Brands per page', 'storesuite' ) }
								help={ __( 'Default: 10', 'storesuite' ) }
								value={ brandPerPage }
								onChange={ setBrandPerPage }
							/>
						</div>
						<div className="storesuite-settings-group">
							<TextControl
								label={ __( 'Coupons per page', 'storesuite' ) }
								help={ __( 'Default: 10', 'storesuite' ) }
								value={ couponPerPage }
								onChange={ setCouponPerPage }
							/>
						</div>
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
