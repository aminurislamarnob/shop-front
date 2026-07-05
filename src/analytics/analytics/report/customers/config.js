import { __, _x } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';
import { getCustomerLabels, getLabelsFromQuery } from '../../../lib/async-requests';

/**
 * Resolves country codes to display names using the localized settings
 * provided by WooCommerce (falls back to the raw code).
 */
const getCountryLabels = ( queryString = '' ) => {
	const countries = ( window.wcSettings && window.wcSettings.countries ) || {};
	return Promise.resolve(
		String( queryString )
			.split( ',' )
			.filter( Boolean )
			.map( ( code ) => ( { key: code, label: countries[ code ] || code } ) )
	);
};

export const charts = applyFilters( 'storesuite_analytics_customers_report_charts', [] );

export const advancedFilters = applyFilters( 'storesuite_analytics_customers_report_advanced_filters', {
	title:   _x( 'Customers match <select/> filters', 'A sentence describing filters for Customers.', 'storesuite' ),
	filters: {
		name: {
			labels: {
				add:         __( 'Name', 'storesuite' ),
				placeholder: __( 'Search customer name', 'storesuite' ),
				remove:      __( 'Remove customer name filter', 'storesuite' ),
				rule:        __( 'Select a customer name filter match', 'storesuite' ),
				title:       __( '<title>Name</title> <rule/> <filter/>', 'storesuite' ),
				filter:      __( 'Select customer name', 'storesuite' ),
			},
			rules: [
				{ value: 'includes', label: _x( 'Includes', 'customer names', 'storesuite' ) },
				{ value: 'excludes', label: _x( 'Excludes', 'customer names', 'storesuite' ) },
			],
			input: {
				component: 'Search',
				type:      'customers',
				getLabels: getCustomerLabels,
			},
		},
		country: {
			labels: {
				add:         __( 'Country / Region', 'storesuite' ),
				placeholder: __( 'Search country / region', 'storesuite' ),
				remove:      __( 'Remove country / region filter', 'storesuite' ),
				rule:        __( 'Select a country / region filter match', 'storesuite' ),
				title:       __( '<title>Country / Region</title> <rule/> <filter/>', 'storesuite' ),
				filter:      __( 'Select country / region', 'storesuite' ),
			},
			rules: [
				{ value: 'includes', label: _x( 'Includes', 'countries', 'storesuite' ) },
				{ value: 'excludes', label: _x( 'Excludes', 'countries', 'storesuite' ) },
			],
			input: {
				component: 'Search',
				type:      'countries',
				getLabels: getCountryLabels,
			},
		},
		city: {
			labels: {
				add:         __( 'City', 'storesuite' ),
				placeholder: __( 'Search city', 'storesuite' ),
				remove:      __( 'Remove city filter', 'storesuite' ),
				rule:        __( 'Select a city filter match', 'storesuite' ),
				title:       __( '<title>City</title> <rule/> <filter/>', 'storesuite' ),
				filter:      __( 'Select city', 'storesuite' ),
			},
			rules: [
				{ value: 'includes', label: _x( 'Includes', 'cities', 'storesuite' ) },
				{ value: 'excludes', label: _x( 'Excludes', 'cities', 'storesuite' ) },
			],
			input: {
				component: 'Search',
				type:      'cities',
				getLabels: getLabelsFromQuery,
			},
		},
		email: {
			labels: {
				add:         __( 'Email', 'storesuite' ),
				placeholder: __( 'Search customer email', 'storesuite' ),
				remove:      __( 'Remove customer email filter', 'storesuite' ),
				rule:        __( 'Select a customer email filter match', 'storesuite' ),
				title:       __( '<title>Email</title> <rule/> <filter/>', 'storesuite' ),
				filter:      __( 'Select customer email', 'storesuite' ),
			},
			rules: [
				{ value: 'includes', label: _x( 'Includes', 'customer emails', 'storesuite' ) },
				{ value: 'excludes', label: _x( 'Excludes', 'customer emails', 'storesuite' ) },
			],
			input: {
				component: 'Search',
				type:      'emails',
				getLabels: getLabelsFromQuery,
			},
		},
		orders_count: {
			labels: {
				add:    __( 'No. of Orders', 'storesuite' ),
				remove: __( 'Remove order filter', 'storesuite' ),
				rule:   __( 'Select an order count filter match', 'storesuite' ),
				title:  __( '<title>No. of Orders</title> <rule/> <filter/>', 'storesuite' ),
			},
			rules: [
				{ value: 'max',     label: __( 'Less Than', 'storesuite' ) },
				{ value: 'min',     label: __( 'More Than', 'storesuite' ) },
				{ value: 'between', label: __( 'Between', 'storesuite' ) },
			],
			input: { component: 'Number' },
		},
		total_spend: {
			labels: {
				add:    __( 'Total Spend', 'storesuite' ),
				remove: __( 'Remove total spend filter', 'storesuite' ),
				rule:   __( 'Select a total spend filter match', 'storesuite' ),
				title:  __( '<title>Total Spend</title> <rule/> <filter/>', 'storesuite' ),
			},
			rules: [
				{ value: 'max',     label: __( 'Less Than', 'storesuite' ) },
				{ value: 'min',     label: __( 'More Than', 'storesuite' ) },
				{ value: 'between', label: __( 'Between', 'storesuite' ) },
			],
			input: { component: 'Currency' },
		},
		avg_order_value: {
			labels: {
				add:    __( 'AOV', 'storesuite' ),
				remove: __( 'Remove average order value filter', 'storesuite' ),
				rule:   __( 'Select an average order value filter match', 'storesuite' ),
				title:  __( '<title>AOV</title> <rule/> <filter/>', 'storesuite' ),
			},
			rules: [
				{ value: 'max',     label: __( 'Less Than', 'storesuite' ) },
				{ value: 'min',     label: __( 'More Than', 'storesuite' ) },
				{ value: 'between', label: __( 'Between', 'storesuite' ) },
			],
			input: { component: 'Currency' },
		},
		registered: {
			labels: {
				add:    __( 'Registered', 'storesuite' ),
				remove: __( 'Remove registered filter', 'storesuite' ),
				rule:   __( 'Select a registered filter match', 'storesuite' ),
				title:  __( '<title>Registered</title> <rule/> <filter/>', 'storesuite' ),
				filter: __( 'Select registered date', 'storesuite' ),
			},
			rules: [
				{ value: 'before',  label: __( 'Before', 'storesuite' ) },
				{ value: 'after',   label: __( 'After', 'storesuite' ) },
				{ value: 'between', label: __( 'Between', 'storesuite' ) },
			],
			input: { component: 'Date' },
		},
		last_active: {
			labels: {
				add:    __( 'Last active', 'storesuite' ),
				remove: __( 'Remove last active filter', 'storesuite' ),
				rule:   __( 'Select a last active filter match', 'storesuite' ),
				title:  __( '<title>Last active</title> <rule/> <filter/>', 'storesuite' ),
				filter: __( 'Select last active date', 'storesuite' ),
			},
			rules: [
				{ value: 'before',  label: __( 'Before', 'storesuite' ) },
				{ value: 'after',   label: __( 'After', 'storesuite' ) },
				{ value: 'between', label: __( 'Between', 'storesuite' ) },
			],
			input: { component: 'Date' },
		},
	},
} );

export const filters = applyFilters( 'storesuite_analytics_customers_report_filters', [
	{
		label:        __( 'Show', 'storesuite' ),
		staticParams: [ 'paged', 'per_page' ],
		param:        'filter',
		showFilters:  () => true,
		filters: [
			{ label: __( 'All customers', 'storesuite' ), value: 'all' },
			{
				label:     __( 'Single customer', 'storesuite' ),
				value:     'select_customer',
				subFilters: [
					{
						component: 'Search',
						value:     'single_customer',
						path:      [ 'select_customer' ],
						settings: {
							type:      'customers',
							param:     'customers',
							getLabels: getCustomerLabels,
							labels: {
								placeholder: __( 'Type to search for a customer', 'storesuite' ),
								button:      __( 'Single Customer', 'storesuite' ),
							},
						},
					},
				],
			},
			{ label: __( 'Advanced filters', 'storesuite' ), value: 'advanced' },
		],
	},
] );
