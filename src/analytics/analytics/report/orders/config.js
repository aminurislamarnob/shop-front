import { __, _x } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';
import { getProductLabels, getCouponLabels, getVariationLabels, getTaxRateLabels } from '../../../lib/async-requests';
import { ORDER_STATUSES } from '../../../utils/admin-settings';

export const charts = applyFilters( 'storesuite_analytics_orders_report_charts', [
	{ key: 'orders_count',        label: __( 'Orders', 'storesuite' ),                   type: 'number' },
	{ key: 'net_revenue',         label: __( 'Net sales', 'storesuite' ),                order: 'desc', orderby: 'net_total', type: 'currency' },
	{ key: 'avg_order_value',     label: __( 'Average order value', 'storesuite' ),      type: 'currency' },
	{ key: 'avg_items_per_order', label: __( 'Average items per order', 'storesuite' ),  order: 'desc', orderby: 'num_items_sold', type: 'average' },
] );

export const advancedFilters = applyFilters( 'storesuite_analytics_orders_report_advanced_filters', {
	title:   _x( 'Orders match <select/> filters', 'A sentence describing filters for Orders.', 'storesuite' ),
	filters: {
		status: {
			labels: {
				add:    __( 'Order status', 'storesuite' ),
				remove: __( 'Remove order status filter', 'storesuite' ),
				rule:   __( 'Select an order status filter match', 'storesuite' ),
				title:  __( '<title>Order status</title> <rule/> <filter/>', 'storesuite' ),
				filter: __( 'Select an order status', 'storesuite' ),
			},
			rules: [
				{ value: 'is',     label: _x( 'Is', 'order status', 'storesuite' ) },
				{ value: 'is_not', label: _x( 'Is Not', 'order status', 'storesuite' ) },
			],
			input: {
				component: 'SelectControl',
				options:   Object.keys( ORDER_STATUSES || {} ).map( ( key ) => ( {
					value: key,
					label: ORDER_STATUSES[ key ],
				} ) ),
			},
		},
		product: {
			labels: {
				add:         __( 'Product', 'storesuite' ),
				placeholder: __( 'Search products', 'storesuite' ),
				remove:      __( 'Remove product filter', 'storesuite' ),
				rule:        __( 'Select a product filter match', 'storesuite' ),
				title:       __( '<title>Product</title> <rule/> <filter/>', 'storesuite' ),
				filter:      __( 'Select products', 'storesuite' ),
			},
			rules: [
				{ value: 'includes', label: _x( 'Includes', 'products', 'storesuite' ) },
				{ value: 'excludes', label: _x( 'Excludes', 'products', 'storesuite' ) },
			],
			input: {
				component: 'Search',
				type:      'products',
				getLabels: getProductLabels,
			},
		},
	},
} );

export const filters = applyFilters( 'storesuite_analytics_orders_report_filters', [
	{
		label:        __( 'Show', 'storesuite' ),
		staticParams: [ 'chartType', 'paged', 'per_page' ],
		param:        'filter',
		showFilters:  () => true,
		filters: [
			{ label: __( 'All orders', 'storesuite' ),      value: 'all' },
			{ label: __( 'Advanced filters', 'storesuite' ), value: 'advanced' },
		],
	},
] );
