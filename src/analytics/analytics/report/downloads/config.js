import { __, _x } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';
import { getProductLabels, getOrderLabels, getLabelsFromQuery } from '../../../lib/async-requests';

export const charts = applyFilters( 'storesuite_analytics_downloads_report_charts', [
	{ key: 'download_count', label: __( 'Downloads', 'storesuite' ), order: 'desc', orderby: 'download_count', type: 'number' },
] );

export const advancedFilters = applyFilters( 'storesuite_analytics_downloads_report_advanced_filters', {
	title:   _x( 'Downloads match <select/> filters', 'A sentence describing filters for Downloads.', 'storesuite' ),
	filters: {
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
		customer: {
			labels: {
				add:         __( 'Username', 'storesuite' ),
				placeholder: __( 'Search customer username', 'storesuite' ),
				remove:      __( 'Remove customer username filter', 'storesuite' ),
				rule:        __( 'Select a customer username filter match', 'storesuite' ),
				title:       __( '<title>Username</title> <rule/> <filter/>', 'storesuite' ),
				filter:      __( 'Select customer username', 'storesuite' ),
			},
			rules: [
				{ value: 'includes', label: _x( 'Includes', 'customer usernames', 'storesuite' ) },
				{ value: 'excludes', label: _x( 'Excludes', 'customer usernames', 'storesuite' ) },
			],
			input: {
				component: 'Search',
				type:      'usernames',
				getLabels: getLabelsFromQuery,
			},
		},
		order: {
			labels: {
				add:         __( 'Order #', 'storesuite' ),
				placeholder: __( 'Search order number', 'storesuite' ),
				remove:      __( 'Remove order number filter', 'storesuite' ),
				rule:        __( 'Select an order number filter match', 'storesuite' ),
				title:       __( '<title>Order #</title> <rule/> <filter/>', 'storesuite' ),
				filter:      __( 'Select order number', 'storesuite' ),
			},
			rules: [
				{ value: 'includes', label: _x( 'Includes', 'order numbers', 'storesuite' ) },
				{ value: 'excludes', label: _x( 'Excludes', 'order numbers', 'storesuite' ) },
			],
			input: {
				component: 'Search',
				type:      'orders',
				getLabels: getOrderLabels,
			},
		},
		ip_address: {
			labels: {
				add:         __( 'IP Address', 'storesuite' ),
				placeholder: __( 'Search IP address', 'storesuite' ),
				remove:      __( 'Remove IP address filter', 'storesuite' ),
				rule:        __( 'Select an IP address filter match', 'storesuite' ),
				title:       __( '<title>IP Address</title> <rule/> <filter/>', 'storesuite' ),
				filter:      __( 'Select IP address', 'storesuite' ),
			},
			rules: [
				{ value: 'includes', label: _x( 'Includes', 'IP addresses', 'storesuite' ) },
				{ value: 'excludes', label: _x( 'Excludes', 'IP addresses', 'storesuite' ) },
			],
			input: {
				component: 'Search',
				type:      'downloadIps',
				getLabels: getLabelsFromQuery,
			},
		},
	},
} );

export const filters = applyFilters( 'storesuite_analytics_downloads_report_filters', [
	{
		label:        __( 'Show', 'storesuite' ),
		staticParams: [ 'chartType', 'paged', 'per_page' ],
		param:        'filter',
		showFilters:  () => true,
		filters: [
			{ label: __( 'All downloads', 'storesuite' ),   value: 'all' },
			{ label: __( 'Advanced filters', 'storesuite' ), value: 'advanced' },
		],
	},
] );
