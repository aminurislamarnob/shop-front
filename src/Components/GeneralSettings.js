import { __ } from '@wordpress/i18n';
import { useState, useEffect } from 'react';
import {
	Button,
	Card,
	CardBody,
	Notice,
	Spinner,
	SelectControl,
	ToggleControl,
} from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';

const PERFORMANCE_BOX_KEYS = [
	{ key: 'revenue_total_sales', label: __( 'Total sales', 'storesuite' ) },
	{ key: 'revenue_gross_sales', label: __( 'Gross sales', 'storesuite' ) },
	{ key: 'revenue_net_revenue', label: __( 'Net sales', 'storesuite' ) },
	{ key: 'orders_orders_count', label: __( 'Orders', 'storesuite' ) },
	{
		key: 'orders_avg_order_value',
		label: __( 'Average order value', 'storesuite' ),
	},
	{ key: 'products_items_sold', label: __( 'Products sold', 'storesuite' ) },
	{
		key: 'variations_items_sold',
		label: __( 'Variations sold', 'storesuite' ),
	},
	{ key: 'revenue_refunds', label: __( 'Returns', 'storesuite' ) },
	{
		key: 'coupons_orders_count',
		label: __( 'Discounted orders', 'storesuite' ),
	},
	{ key: 'coupons_amount', label: __( 'Net discount amount', 'storesuite' ) },
	{ key: 'taxes_total_tax', label: __( 'Total tax', 'storesuite' ) },
	{ key: 'taxes_order_tax', label: __( 'Order tax', 'storesuite' ) },
	{ key: 'taxes_shipping_tax', label: __( 'Shipping tax', 'storesuite' ) },
	{ key: 'revenue_shipping', label: __( 'Shipping', 'storesuite' ) },
	{ key: 'downloads_download_count', label: __( 'Downloads', 'storesuite' ) },
];

const DASHBOARD_WIDGET_KEYS = [
	{
		key: 'top_products_items_sold',
		label: __( 'Top products - Items sold', 'storesuite' ),
	},
	{
		key: 'top_categories_items_sold',
		label: __( 'Top categories - Items sold', 'storesuite' ),
	},
	{
		key: 'top_customers_total_spend',
		label: __( 'Top customers - Total spend', 'storesuite' ),
	},
	{
		key: 'top_coupons_orders_count',
		label: __( 'Top coupons - Number of orders', 'storesuite' ),
	},
];

const yes = ( v ) => v !== 'no' && v !== false;

const GeneralSettings = () => {
	const [ pages, setPages ] = useState( [] );
	const [ dashboardPage, setDashboardPage ] = useState( '' );
	const [ preventAdminAccess, setPreventAdminAccess ] = useState( false );
	const [ performanceBoxes, setPerformanceBoxes ] = useState( {} );
	const [ dashboardWidgets, setDashboardWidgets ] = useState( {} );
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

				if ( response.storesuite_dashboard_page_id ) {
					setDashboardPage( response.storesuite_dashboard_page_id );
				}

				if ( response.storesuite_prevent_admin_access !== undefined ) {
					setPreventAdminAccess(
						response.storesuite_prevent_admin_access === 'yes' ||
							response.storesuite_prevent_admin_access === true
					);
				}

				const perf = {};
				PERFORMANCE_BOX_KEYS.forEach( ( { key } ) => {
					const opt = `storesuite_show_perf_${ key }`;
					perf[ key ] =
						response[ opt ] !== undefined
							? yes( response[ opt ] )
							: true;
				} );
				setPerformanceBoxes( perf );

				const widgets = {};
				DASHBOARD_WIDGET_KEYS.forEach( ( { key } ) => {
					const opt = `storesuite_show_widget_${ key }`;
					widgets[ key ] =
						response[ opt ] !== undefined
							? yes( response[ opt ] )
							: true;
				} );
				setDashboardWidgets( widgets );

				setError( null ); // Clear any previous errors
				setIsLoading( false );
			} catch ( err ) {
				setError( err.message );
				setIsLoading( false );
			}
		};

		fetchSettings();
	}, [] );

	// Fetch 100 pages.
	useEffect( () => {
		setIsLoading( true );
		const fetchPages = async () => {
			try {
				const pages = await apiFetch( {
					path: '/wp/v2/pages?per_page=100&page=1',
				} );

				// Map the pages to the options format required by SelectControl
				const options = pages.map( ( page ) => ( {
					label: page.title.rendered,
					value: page.id,
				} ) );

				options.unshift( {
					value: '',
					label: __( 'Select dashboard page', 'storesuite' ),
					disabled: true,
				} );

				setPages( options );
				setError( null ); // Clear any previous errors
				setIsLoading( false );
			} catch ( err ) {
				setError( err.message );
				setIsLoading( false );
			}
		};

		fetchPages();
	}, [] );

	// Handle submit to save dashboard page
	const handleSubmit = async ( event ) => {
		event.preventDefault();
		setIsLoading( true );
		try {
			const toYesNo = ( b ) => ( b ? 'yes' : 'no' );
			const data = {
				storesuite_dashboard_page_id: dashboardPage,
				storesuite_prevent_admin_access: preventAdminAccess
					? 'yes'
					: 'no',
			};
			PERFORMANCE_BOX_KEYS.forEach( ( { key } ) => {
				data[ `storesuite_show_perf_${ key }` ] = toYesNo(
					performanceBoxes[ key ] !== false
				);
			} );

			DASHBOARD_WIDGET_KEYS.forEach( ( { key } ) => {
				data[ `storesuite_show_widget_${ key }` ] = toYesNo(
					dashboardWidgets[ key ] !== false
				);
			} );

			console.log( data );

			const response = await apiFetch( {
				path: '/storesuite/v1/settings',
				method: 'POST',
				data,
			} );

			setDashboardPage( response.storesuite_dashboard_page_id );

			if ( response.storesuite_prevent_admin_access !== undefined ) {
				setPreventAdminAccess(
					response.storesuite_prevent_admin_access === 'yes' ||
						response.storesuite_prevent_admin_access === true
				);
			}

			const perf = {};
			PERFORMANCE_BOX_KEYS.forEach( ( { key } ) => {
				const opt = `storesuite_show_perf_${ key }`;
				perf[ key ] =
					response[ opt ] !== undefined
						? yes( response[ opt ] )
						: true;
			} );
			setPerformanceBoxes( perf );

			const widgets = {};
			DASHBOARD_WIDGET_KEYS.forEach( ( { key } ) => {
				const opt = `storesuite_show_widget_${ key }`;
				widgets[ key ] =
					response[ opt ] !== undefined
						? yes( response[ opt ] )
						: true;
			} );
			setDashboardWidgets( widgets );

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
						className="bi bi-gear"
						viewBox="0 0 16 16"
					>
						<path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492M5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0" />
						<path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115z" />
					</svg>
				</div>
				<h2>{ __( 'General Settings', 'storesuite' ) }</h2>
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
							<SelectControl
								label="Select Dashboard Page"
								value={ dashboardPage }
								options={ pages }
								onChange={ ( page ) =>
									setDashboardPage( page )
								}
							/>
						</div>
						<div className="storesuite-settings-group admin-area-access">
							<ToggleControl
								label={ __(
									'Admin Area Access',
									'storesuite'
								) }
								help={ __(
									'Prevent shop manager from accessing the wp-admin dashboard area. If HPOS feature is enabled, admin access will be blocked regardless of this setting.',
									'storesuite'
								) }
								checked={ preventAdminAccess }
								onChange={ setPreventAdminAccess }
							/>
						</div>
						<div className="storesuite-settings-group">
							<div className="storesuite-settings-sec-header">
								<h3 className="storesuite-section-title">
									{ __( 'Performance boxes', 'storesuite' ) }
								</h3>
								<p className="storesuite-section-description">
									{ __(
										'Show or hide each performance box on the dashboard.',
										'storesuite'
									) }
								</p>
							</div>
							{ PERFORMANCE_BOX_KEYS.map( ( { key, label } ) => (
								<ToggleControl
									key={ key }
									label={ label }
									checked={
										performanceBoxes[ key ] !== false
									}
									onChange={ ( checked ) =>
										setPerformanceBoxes( ( prev ) => ( {
											...prev,
											[ key ]: checked,
										} ) )
									}
								/>
							) ) }
						</div>
						<div className="storesuite-settings-group">
							<div className="storesuite-settings-sec-header">
								<h3 className="storesuite-section-title">
									{ __( 'Dashboard widgets', 'storesuite' ) }
								</h3>
								<p className="storesuite-section-description">
									{ __(
										'Show or hide each dashboard widget.',
										'storesuite'
									) }
								</p>
							</div>
							{ DASHBOARD_WIDGET_KEYS.map( ( { key, label } ) => (
								<ToggleControl
									key={ key }
									label={ label }
									checked={
										dashboardWidgets[ key ] !== false
									}
									onChange={ ( checked ) =>
										setDashboardWidgets( ( prev ) => ( {
											...prev,
											[ key ]: checked,
										} ) )
									}
								/>
							) ) }
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

export default GeneralSettings;
