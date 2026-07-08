import { __, _x } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';
import { getTaxRateLabels } from '../../../lib/async-requests';

export const charts = applyFilters( 'storesuite_analytics_taxes_report_charts', [
	{ key: 'total_tax',    label: __( 'Total tax', 'storesuite' ),    order: 'desc', orderby: 'total_tax',    type: 'currency' },
	{ key: 'order_tax',    label: __( 'Order tax', 'storesuite' ),    order: 'desc', orderby: 'order_tax',    type: 'currency' },
	{ key: 'shipping_tax', label: __( 'Shipping tax', 'storesuite' ), order: 'desc', orderby: 'shipping_tax', type: 'currency' },
	{ key: 'orders_count', label: __( 'Orders', 'storesuite' ),       order: 'desc', orderby: 'orders_count', type: 'number' },
] );

export const advancedFilters = applyFilters(
	'storesuite_analytics_taxes_report_advanced_filters',
	{
		filters: {},
		title:   _x( 'Taxes Match <select/> Filters', 'A sentence describing filters for Taxes.', 'storesuite' ),
	}
);

export const filters = applyFilters( 'storesuite_analytics_taxes_report_filters', [
	{
		label:        __( 'Show', 'storesuite' ),
		staticParams: [ 'chartType', 'paged', 'per_page' ],
		param:        'filter',
		showFilters:  () => true,
		filters: [
			{ label: __( 'All taxes', 'storesuite' ), value: 'all' },
			{
				label:     __( 'Comparison', 'storesuite' ),
				value:     'compare-taxes',
				chartMode: 'item-comparison',
				settings: {
					type:      'taxes',
					param:     'taxes',
					getLabels: getTaxRateLabels,
					labels: {
						helpText:    __( 'Check at least two tax codes below to compare', 'storesuite' ),
						placeholder: __( 'Search for tax codes to compare', 'storesuite' ),
						title:       __( 'Compare Tax Codes', 'storesuite' ),
						update:      __( 'Compare', 'storesuite' ),
					},
				},
			},
		],
	},
] );
