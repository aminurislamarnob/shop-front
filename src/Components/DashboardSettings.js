import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	Button,
	Card,
	CardBody,
	Spinner,
	ToggleControl,
} from '@wordpress/components';

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

const initToggles = ( keys, settings, prefix ) =>
	Object.fromEntries(
		keys.map( ( { key } ) => {
			const storedValue = settings[ `${ prefix }${ key }` ];
			return [
				key,
				storedValue === undefined
					? true
					: storedValue !== 'no' && storedValue !== false,
			];
		} )
	);

const ToggleGroup = ( { title, description, items, state, setState } ) => (
	<div className="storesuite-settings-group">
		<div className="storesuite-settings-sec-header">
			<h3 className="storesuite-section-title">{ title }</h3>
			<p className="storesuite-section-description">{ description }</p>
		</div>
		{ items.map( ( { key, label } ) => (
			<ToggleControl
				key={ key }
				label={ label }
				checked={ state[ key ] }
				onChange={ ( checked ) =>
					setState( ( prev ) => ( { ...prev, [ key ]: checked } ) )
				}
			/>
		) ) }
	</div>
);

const DashboardSettings = () => {
	const { settings, isSaving, saveSettings } = useSettings();

	const [ performanceBoxes, setPerformanceBoxes ] = useState( () =>
		initToggles( PERFORMANCE_BOX_KEYS, settings, 'storesuite_show_perf_' )
	);
	const [ dashboardWidgets, setDashboardWidgets ] = useState( () =>
		initToggles(
			DASHBOARD_WIDGET_KEYS,
			settings,
			'storesuite_show_widget_'
		)
	);

	const handleSubmit = ( event ) => {
		event.preventDefault();
		const data = {};
		PERFORMANCE_BOX_KEYS.forEach( ( { key } ) => {
			data[ `storesuite_show_perf_${ key }` ] = performanceBoxes[ key ]
				? 'yes'
				: 'no';
		} );
		DASHBOARD_WIDGET_KEYS.forEach( ( { key } ) => {
			data[ `storesuite_show_widget_${ key }` ] = dashboardWidgets[ key ]
				? 'yes'
				: 'no';
		} );
		saveSettings( data );
	};

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
						<ToggleGroup
							title={ __( 'Performance boxes', 'storesuite' ) }
							description={ __(
								'Show or hide each performance box on the dashboard.',
								'storesuite'
							) }
							items={ PERFORMANCE_BOX_KEYS }
							state={ performanceBoxes }
							setState={ setPerformanceBoxes }
						/>
						<ToggleGroup
							title={ __( 'Dashboard widgets', 'storesuite' ) }
							description={ __(
								'Show or hide each dashboard widget.',
								'storesuite'
							) }
							items={ DASHBOARD_WIDGET_KEYS }
							state={ dashboardWidgets }
							setState={ setDashboardWidgets }
						/>
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
