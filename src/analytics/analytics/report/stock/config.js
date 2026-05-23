import { __, _x } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';

export const advancedFilters = applyFilters(
	'storesuite_analytics_stock_report_advanced_filters',
	{
		filters: {},
		title:   _x( 'Stock Match <select/> Filters', 'A sentence describing filters for Stock.', 'storesuite' ),
	}
);

export const filters = applyFilters( 'storesuite_analytics_stock_report_filters', [
	{
		label:        __( 'Show', 'storesuite' ),
		staticParams: [ 'paged', 'per_page' ],
		param:        'type',
		showFilters:  () => true,
		filters: [
			{ label: __( 'All products', 'storesuite' ),    value: 'all' },
			{ label: __( 'In stock', 'storesuite' ),        value: 'instock' },
			{ label: __( 'Out of stock', 'storesuite' ),    value: 'outofstock' },
			{ label: __( 'On back order', 'storesuite' ),   value: 'onbackorder' },
			{ label: __( 'Low stock', 'storesuite' ),       value: 'lowstock' },
		],
	},
] );
