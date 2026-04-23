/**
 * WordPress dependencies
 */
import { useState, useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button, Card, CardBody, Spinner, ToggleControl } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { useSettings } from '../context/SettingsContext';

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

const DashboardSettings = () => {
	const { settings, isSaving, saveSettings } = useSettings();

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

	const handleSubmit = useCallback(
		async ( event ) => {
			event.preventDefault();
			const toYesNo = ( b ) => ( b ? 'yes' : 'no' );
			const data = {};

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
		[ performanceBoxes, dashboardWidgets, saveSettings ]
	);

	return (
		<div
			className="storesuite-section storesuite-section--narrow"
			id="storesuite-dashboard-settings"
		>
			<form onSubmit={ handleSubmit }>
				<Card className="storesuite-form-header-card">
					<CardBody className="storesuite-form-section-header">
						<h3 className="storesuite-section-title">
							{ __( 'Dashboard Settings', 'storesuite' ) }
						</h3>
						<p className="storesuite-section-description">
							{ __(
								'Show or hide performance boxes and widgets on your dashboard.',
								'storesuite'
							) }
						</p>
					</CardBody>
				</Card>
				<Card>
					<CardBody className="storesuite-form-section-body">
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

export default DashboardSettings;
