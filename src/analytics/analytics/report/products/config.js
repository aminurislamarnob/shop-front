import { __, _x } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';
import { getProductLabels, getVariationLabels } from '../../../lib/async-requests';

export const charts = applyFilters( 'storesuite_analytics_products_report_charts', [
	{ key: 'items_sold',  label: __( 'Items sold', 'storesuite' ),  order: 'desc', orderby: 'items_sold',  type: 'number' },
	{ key: 'net_revenue', label: __( 'Net sales', 'storesuite' ),   order: 'desc', orderby: 'net_revenue', type: 'currency' },
	{ key: 'orders_count', label: __( 'Orders', 'storesuite' ),     order: 'desc', orderby: 'orders_count', type: 'number' },
] );

export const advancedFilters = applyFilters(
	'storesuite_analytics_products_report_advanced_filters',
	{
		filters: {},
		title:   _x( 'Products Match <select/> Filters', 'A sentence describing filters for Products.', 'storesuite' ),
	}
);

export const filters = applyFilters( 'storesuite_analytics_products_report_filters', [
	{
		label:        __( 'Show', 'storesuite' ),
		staticParams: [ 'chartType', 'paged', 'per_page' ],
		param:        'filter',
		showFilters:  () => true,
		filters: [
			{ label: __( 'All products', 'storesuite' ),  value: 'all' },
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
				value:     'compare-products',
				chartMode: 'item-comparison',
				settings: {
					type:      'products',
					param:     'products',
					getLabels: getProductLabels,
					labels: {
						helpText:    __( 'Check at least two products below to compare', 'storesuite' ),
						placeholder: __( 'Search for products to compare', 'storesuite' ),
						title:       __( 'Compare Products', 'storesuite' ),
						update:      __( 'Compare', 'storesuite' ),
					},
				},
			},
		],
	},
	{
		// Secondary filter shown only when viewing a single variable product,
		// letting the report drill into that product's variations.
		showFilters:  ( query ) =>
			'single_product' === query.filter && !! query.products && query[ 'is-variable' ],
		staticParams: [ 'filter', 'products', 'chartType', 'paged', 'per_page' ],
		param:        'filter-variations',
		filters: [
			{ label: __( 'All variations', 'storesuite' ), chartMode: 'item-comparison', value: 'all' },
			{
				label:     __( 'Single variation', 'storesuite' ),
				value:     'select_variation',
				subFilters: [
					{
						component: 'Search',
						value:     'single_variation',
						path:      [ 'select_variation' ],
						settings: {
							type:      'variations',
							param:     'variations',
							getLabels: getVariationLabels,
							labels: {
								placeholder: __( 'Type to search for a variation', 'storesuite' ),
								button:      __( 'Single variation', 'storesuite' ),
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
