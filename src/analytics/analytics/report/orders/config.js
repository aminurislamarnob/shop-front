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
		variation: {
			labels: {
				add:         __( 'Product variation', 'storesuite' ),
				placeholder: __( 'Search product variations', 'storesuite' ),
				remove:      __( 'Remove product variation filter', 'storesuite' ),
				rule:        __( 'Select a product variation filter match', 'storesuite' ),
				title:       __( '<title>Product variation</title> <rule/> <filter/>', 'storesuite' ),
				filter:      __( 'Select variation', 'storesuite' ),
			},
			rules: [
				{ value: 'includes', label: _x( 'Includes', 'variations', 'storesuite' ) },
				{ value: 'excludes', label: _x( 'Excludes', 'variations', 'storesuite' ) },
			],
			input: {
				component: 'Search',
				type:      'variations',
				getLabels: getVariationLabels,
			},
		},
		coupon: {
			labels: {
				add:         __( 'Coupon code', 'storesuite' ),
				placeholder: __( 'Search coupons', 'storesuite' ),
				remove:      __( 'Remove coupon filter', 'storesuite' ),
				rule:        __( 'Select a coupon filter match', 'storesuite' ),
				title:       __( '<title>Coupon code</title> <rule/> <filter/>', 'storesuite' ),
				filter:      __( 'Select coupon codes', 'storesuite' ),
			},
			rules: [
				{ value: 'includes', label: _x( 'Includes', 'coupon code', 'storesuite' ) },
				{ value: 'excludes', label: _x( 'Excludes', 'coupon code', 'storesuite' ) },
			],
			input: {
				component: 'Search',
				type:      'coupons',
				getLabels: getCouponLabels,
			},
		},
		customer_type: {
			labels: {
				add:    __( 'Customer type', 'storesuite' ),
				remove: __( 'Remove customer filter', 'storesuite' ),
				rule:   __( 'Select a customer filter match', 'storesuite' ),
				title:  __( '<title>Customer is</title> <filter/>', 'storesuite' ),
				filter: __( 'Select a customer type', 'storesuite' ),
			},
			input: {
				component:     'SelectControl',
				options:       [
					{ value: 'new',       label: __( 'New', 'storesuite' ) },
					{ value: 'returning', label: __( 'Returning', 'storesuite' ) },
				],
				defaultOption: 'new',
			},
		},
		refunds: {
			labels: {
				add:    __( 'Refund', 'storesuite' ),
				remove: __( 'Remove refund filter', 'storesuite' ),
				rule:   __( 'Select a refund filter match', 'storesuite' ),
				title:  __( '<title>Refund</title> <filter/>', 'storesuite' ),
				filter: __( 'Select a refund type', 'storesuite' ),
			},
			input: {
				component:     'SelectControl',
				options:       [
					{ value: 'all',     label: __( 'All', 'storesuite' ) },
					{ value: 'partial', label: __( 'Partially refunded', 'storesuite' ) },
					{ value: 'full',    label: __( 'Fully refunded', 'storesuite' ) },
					{ value: 'none',    label: __( 'None', 'storesuite' ) },
				],
				defaultOption: 'all',
			},
		},
		tax_rate: {
			labels: {
				add:         __( 'Tax rate', 'storesuite' ),
				placeholder: __( 'Search tax rates', 'storesuite' ),
				remove:      __( 'Remove tax rate filter', 'storesuite' ),
				rule:        __( 'Select a tax rate filter match', 'storesuite' ),
				title:       __( '<title>Tax Rate</title> <rule/> <filter/>', 'storesuite' ),
				filter:      __( 'Select tax rates', 'storesuite' ),
			},
			rules: [
				{ value: 'includes', label: _x( 'Includes', 'tax rate', 'storesuite' ) },
				{ value: 'excludes', label: _x( 'Excludes', 'tax rate', 'storesuite' ) },
			],
			input: {
				component: 'Search',
				type:      'taxes',
				getLabels: getTaxRateLabels,
			},
		},
		attribute: {
			allowMultiple: true,
			labels: {
				add:         __( 'Product attribute', 'storesuite' ),
				placeholder: __( 'Search product attributes', 'storesuite' ),
				remove:      __( 'Remove product attribute filter', 'storesuite' ),
				rule:        __( 'Select a product attribute filter match', 'storesuite' ),
				title:       __( '<title>Product attribute</title> <rule/> <filter/>', 'storesuite' ),
				filter:      __( 'Select attributes', 'storesuite' ),
			},
			rules: [
				{ value: 'is',     label: _x( 'Is', 'product attribute', 'storesuite' ) },
				{ value: 'is_not', label: _x( 'Is Not', 'product attribute', 'storesuite' ) },
			],
			input: {
				component: 'ProductAttribute',
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
