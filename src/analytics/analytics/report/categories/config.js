import { __, _x } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';
import { getCategoryLabels } from '../../../lib/async-requests';

export const charts = applyFilters( 'storesuite_analytics_categories_report_charts', [
	{ key: 'items_sold',  label: __( 'Items sold', 'storesuite' ),  order: 'desc', orderby: 'items_sold',  type: 'number' },
	{ key: 'net_revenue', label: __( 'Net sales', 'storesuite' ),   order: 'desc', orderby: 'net_revenue', type: 'currency' },
	{ key: 'orders_count', label: __( 'Orders', 'storesuite' ),     order: 'desc', orderby: 'orders_count', type: 'number' },
] );

export const advancedFilters = applyFilters(
	'storesuite_analytics_categories_report_advanced_filters',
	{
		filters: {},
		title:   _x( 'Categories Match <select/> Filters', 'A sentence describing filters for Categories.', 'storesuite' ),
	}
);

export const filters = applyFilters( 'storesuite_analytics_categories_report_filters', [
	{
		label:        __( 'Show', 'storesuite' ),
		staticParams: [ 'chartType', 'paged', 'per_page' ],
		param:        'filter',
		showFilters:  () => true,
		filters: [
			{ label: __( 'All categories', 'storesuite' ), value: 'all' },
			{
				label:     __( 'Single category', 'storesuite' ),
				value:     'select_category',
				chartMode: 'item-comparison',
				subFilters: [
					{
						component: 'Search',
						value:     'single_category',
						chartMode: 'item-comparison',
						path:      [ 'select_category' ],
						settings: {
							type:      'categories',
							param:     'categories',
							getLabels: getCategoryLabels,
							labels: {
								placeholder: __( 'Type to search for a category', 'storesuite' ),
								button:      __( 'Single Category', 'storesuite' ),
							},
						},
					},
				],
			},
			{
				label:     __( 'Comparison', 'storesuite' ),
				value:     'compare-categories',
				chartMode: 'item-comparison',
				settings: {
					type:      'categories',
					param:     'categories',
					getLabels: getCategoryLabels,
					labels: {
						helpText:    __( 'Check at least two categories below to compare', 'storesuite' ),
						placeholder: __( 'Search for categories to compare', 'storesuite' ),
						title:       __( 'Compare Categories', 'storesuite' ),
						update:      __( 'Compare', 'storesuite' ),
					},
				},
			},
		],
	},
] );
