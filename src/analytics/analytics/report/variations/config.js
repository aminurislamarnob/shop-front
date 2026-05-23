import { __, _x } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';
import { getProductLabels, getVariationLabels } from '../../../lib/async-requests';

export const charts = applyFilters( 'storesuite_analytics_variations_report_charts', [
	{ key: 'items_sold',  label: __( 'Items sold', 'storesuite' ),  order: 'desc', orderby: 'items_sold',  type: 'number' },
	{ key: 'net_revenue', label: __( 'Net sales', 'storesuite' ),   order: 'desc', orderby: 'net_revenue', type: 'currency' },
	{ key: 'orders_count', label: __( 'Orders', 'storesuite' ),     order: 'desc', orderby: 'orders_count', type: 'number' },
] );

export const advancedFilters = applyFilters(
	'storesuite_analytics_variations_report_advanced_filters',
	{
		filters: {},
		title:   _x( 'Variations Match <select/> Filters', 'A sentence describing filters for Variations.', 'storesuite' ),
	}
);

export const filters = applyFilters( 'storesuite_analytics_variations_report_filters', [
	{
		label:        __( 'Show', 'storesuite' ),
		staticParams: [ 'chartType', 'paged', 'per_page' ],
		param:        'filter',
		showFilters:  () => true,
		filters: [
			{ label: __( 'All variations', 'storesuite' ), value: 'all' },
			{
				label:     __( 'Single variation', 'storesuite' ),
				value:     'select_variation',
				chartMode: 'item-comparison',
				subFilters: [
					{
						component: 'Search',
						value:     'single_variation',
						chartMode: 'item-comparison',
						path:      [ 'select_variation' ],
						settings: {
							type:      'variations',
							param:     'variations',
							getLabels: getVariationLabels,
							labels: {
								placeholder: __( 'Type to search for a variation', 'storesuite' ),
								button:      __( 'Single Variation', 'storesuite' ),
							},
						},
					},
				],
			},
			{
				label:     __( 'Single product', 'storesuite' ),
				value:     'select_product',
				chartMode: 'item-comparison',
				subFilters: [
					{
						component: 'Search',
						value:     'single_product',
						chartMode: 'item-comparison',
						path:      [ 'select_product' ],
						settings: {
							type:      'products',
							param:     'products',
							getLabels: getProductLabels,
							labels: {
								placeholder: __( 'Type to search for a product', 'storesuite' ),
								button:      __( 'Single Product', 'storesuite' ),
							},
						},
					},
				],
			},
			{
				label:     __( 'Comparison', 'storesuite' ),
				value:     'compare-variations',
				chartMode: 'item-comparison',
				settings: {
					type:      'variations',
					param:     'variations',
					getLabels: getVariationLabels,
					labels: {
						helpText:    __( 'Check at least two variations below to compare', 'storesuite' ),
						placeholder: __( 'Search for variations to compare', 'storesuite' ),
						title:       __( 'Compare Variations', 'storesuite' ),
						update:      __( 'Compare', 'storesuite' ),
					},
				},
			},
		],
	},
] );
