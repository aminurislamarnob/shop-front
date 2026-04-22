/**
 * WordPress dependencies
 */
import { useState, useEffect, useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	Button,
	Card,
	CardBody,
	Spinner,
	SelectControl,
	ToggleControl,
} from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';

/**
 * Internal dependencies
 */
import { useSettings } from '../context/SettingsContext';
import DashboardSidebarImageControl from './DashboardSidebarImageControl';

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
	const { settings, isSaving, saveSettings } = useSettings();

	const [ pages, setPages ] = useState( [] );
	const [ dashboardPage, setDashboardPage ] = useState(
		() => settings.storesuite_dashboard_page_id ?? ''
	);
	const [ preventAdminAccess, setPreventAdminAccess ] = useState(
		() =>
			settings.storesuite_prevent_admin_access === 'yes' ||
			settings.storesuite_prevent_admin_access === true
	);
	const [ sidebarLogoId, setSidebarLogoId ] = useState(
		() =>
			parseInt( settings.storesuite_dashboard_sidebar_logo_id, 10 ) || 0
	);
	const [ sidebarIconId, setSidebarIconId ] = useState(
		() =>
			parseInt( settings.storesuite_dashboard_sidebar_icon_id, 10 ) || 0
	);
	const [ performanceBoxes, setPerformanceBoxes ] = useState( () => {
		const perf = {};
		PERFORMANCE_BOX_KEYS.forEach( ( { key } ) => {
			const opt = `storesuite_show_perf_${ key }`;
			perf[ key ] =
				settings[ opt ] !== undefined ? yes( settings[ opt ] ) : true;
		} );
		return perf;
	} );
	const [ dashboardWidgets, setDashboardWidgets ] = useState( () => {
		const widgets = {};
		DASHBOARD_WIDGET_KEYS.forEach( ( { key } ) => {
			const opt = `storesuite_show_widget_${ key }`;
			widgets[ key ] =
				settings[ opt ] !== undefined ? yes( settings[ opt ] ) : true;
		} );
		return widgets;
	} );

	// Fetch available pages for the dashboard page selector (tab-local).
	useEffect( () => {
		const fetchPages = async () => {
			try {
				const wpPages = await apiFetch( {
					path: '/wp/v2/pages?per_page=100&page=1',
				} );

				const options = wpPages.map( ( page ) => ( {
					label: page.title.rendered,
					value: page.id,
				} ) );

				options.unshift( {
					value: '',
					label: __( 'Select dashboard page', 'storesuite' ),
					disabled: true,
				} );

				setPages( options );
			} catch ( err ) {
				// Page dropdown stays empty on error.
			}
		};

		fetchPages();
	}, [] );

	const handleSubmit = useCallback(
		async ( event ) => {
			event.preventDefault();
			const toYesNo = ( b ) => ( b ? 'yes' : 'no' );
			const data = {
				storesuite_dashboard_page_id: dashboardPage,
				storesuite_prevent_admin_access: preventAdminAccess
					? 'yes'
					: 'no',
				storesuite_dashboard_sidebar_logo_id: sidebarLogoId,
				storesuite_dashboard_sidebar_icon_id: sidebarIconId,
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

			await saveSettings( data );
		},
		[
			dashboardPage,
			preventAdminAccess,
			sidebarLogoId,
			sidebarIconId,
			performanceBoxes,
			dashboardWidgets,
			saveSettings,
		]
	);

	return (
		<div
			className="storesuite-section storesuite-section--narrow"
			id="storesuite-general-settings"
		>
			<form onSubmit={ handleSubmit }>
				<Card className="storesuite-form-header-card">
					<CardBody className="storesuite-form-section-header">
						<h3 className="storesuite-section-title">
							{ __( 'General Settings', 'storesuite' ) }
						</h3>
						<p className="storesuite-section-description">
							{ __(
								'Configure your dashboard page, sidebar branding, and admin area access.',
								'storesuite'
							) }
						</p>
					</CardBody>
				</Card>
				<Card>
					<CardBody className="storesuite-form-section-body">
						<div className="storesuite-settings-group">
							<SelectControl
								label={ __(
									'Select Dashboard Page',
									'storesuite'
								) }
								value={ dashboardPage }
								options={ pages }
								onChange={ ( page ) =>
									setDashboardPage( page )
								}
							/>
						</div>
						<DashboardSidebarImageControl
							label={ __(
								'Dashboard sidebar logo',
								'storesuite'
							) }
							help={ __(
								'Shown in the expanded sidebar. If set, the site title is visually hidden but kept for screen readers.',
								'storesuite'
							) }
							attachmentId={ sidebarLogoId }
							onChange={ setSidebarLogoId }
						/>
						<DashboardSidebarImageControl
							label={ __(
								'Dashboard sidebar icon',
								'storesuite'
							) }
							help={ __(
								'Shown in the collapsed (icon-only) sidebar. If empty, the logo is used when collapsed when a logo is set.',
								'storesuite'
							) }
							attachmentId={ sidebarIconId }
							onChange={ setSidebarIconId }
						/>
						<div className="storesuite-settings-group admin-area-access">
							<ToggleControl
								label={ __(
									'Restrict Admin Area Access',
									'storesuite'
								) }
								help={ __(
									'Prevent shop manager from accessing the wp-admin dashboard area.',
									'storesuite'
								) }
								checked={ preventAdminAccess }
								onChange={ setPreventAdminAccess }
							/>
						</div>
						<div className="storesuite-settings-group">
							<div className="storesuite-settings-sec-header">
								<h3 className="storesuite-section-title">
									{ __(
										'Performance boxes',
										'storesuite'
									) }
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
									{ __(
										'Dashboard widgets',
										'storesuite'
									) }
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
							isBusy={ isSaving }
							disabled={ isSaving }
						>
							{ isSaving && <Spinner /> }
							{ __( 'Save Changes', 'storesuite' ) }
						</Button>
					</CardBody>
				</Card>
			</form>
		</div>
	);
};

export default GeneralSettings;
