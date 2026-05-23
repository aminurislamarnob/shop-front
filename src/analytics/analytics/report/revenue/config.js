import { __, _x } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';

export const charts = applyFilters( 'storesuite_analytics_revenue_report_charts', [
	{
		key:   'gross_sales',
		label: __( 'Gross sales', 'storesuite' ),
		order: 'desc',
		orderby: 'gross_sales',
		type:  'currency',
		isReverseTrend: false,
	},
	{
		key:   'refunds',
		label: __( 'Returns', 'storesuite' ),
		order: 'desc',
		orderby: 'refunds',
		type:  'currency',
		isReverseTrend: true,
	},
	{
		key:   'coupons',
		label: __( 'Coupons', 'storesuite' ),
		order: 'desc',
		orderby: 'coupons',
		type:  'currency',
		isReverseTrend: false,
	},
	{
		key:     'net_revenue',
		label:   __( 'Net sales', 'storesuite' ),
		orderby: 'net_revenue',
		type:    'currency',
		isReverseTrend: false,
	},
	{
		key:   'taxes',
		label: __( 'Taxes', 'storesuite' ),
		order: 'desc',
		orderby: 'taxes',
		type:  'currency',
		isReverseTrend: false,
	},
	{
		key:     'shipping',
		label:   __( 'Shipping', 'storesuite' ),
		orderby: 'shipping',
		type:    'currency',
		isReverseTrend: false,
	},
	{
		key:   'total_sales',
		label: __( 'Total sales', 'storesuite' ),
		order: 'desc',
		orderby: 'total_sales',
		type:  'currency',
		isReverseTrend: false,
	},
] );

export const advancedFilters = applyFilters(
	'storesuite_analytics_revenue_report_advanced_filters',
	{
		filters: {},
		title:   _x(
			'Revenue Matches <select/> Filters',
			'A sentence describing filters for Revenue.',
			'storesuite'
		),
	}
);

const filterValues = [];
if ( Object.keys( advancedFilters.filters ).length ) {
	filterValues.push( { label: __( 'All Revenue', 'storesuite' ), value: 'all' } );
	filterValues.push( { label: __( 'Advanced Filters', 'storesuite' ), value: 'advanced' } );
}

export const filters = applyFilters( 'storesuite_analytics_revenue_report_filters', [
	{
		label:        __( 'Show', 'storesuite' ),
		staticParams: [ 'chartType', 'paged', 'per_page' ],
		param:        'filter',
		showFilters:  () => filterValues.length > 0,
		filters:      filterValues,
	},
] );
