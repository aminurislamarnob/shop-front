import { __, _x } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';
import { getCouponLabels } from '../../../lib/async-requests';

export const charts = applyFilters( 'storesuite_analytics_coupons_report_charts', [
	{ key: 'orders_count', label: __( 'Discounted orders', 'storesuite' ), order: 'desc', orderby: 'orders_count', type: 'number' },
	{ key: 'amount',       label: __( 'Amount', 'storesuite' ),            order: 'desc', orderby: 'amount',       type: 'currency' },
] );

export const advancedFilters = applyFilters(
	'storesuite_analytics_coupons_report_advanced_filters',
	{
		filters: {},
		title:   _x( 'Coupons Match <select/> Filters', 'A sentence describing filters for Coupons.', 'storesuite' ),
	}
);

export const filters = applyFilters( 'storesuite_analytics_coupons_report_filters', [
	{
		label:        __( 'Show', 'storesuite' ),
		staticParams: [ 'chartType', 'paged', 'per_page' ],
		param:        'filter',
		showFilters:  () => true,
		filters: [
			{ label: __( 'All coupons', 'storesuite' ), value: 'all' },
			{
				label:     __( 'Single coupon', 'storesuite' ),
				value:     'select_coupon',
				chartMode: 'item-comparison',
				subFilters: [
					{
						component: 'Search',
						value:     'single_coupon',
						chartMode: 'item-comparison',
						path:      [ 'select_coupon' ],
						settings: {
							type:      'coupons',
							param:     'coupons',
							getLabels: getCouponLabels,
							labels: {
								placeholder: __( 'Type to search for a coupon', 'storesuite' ),
								button:      __( 'Single Coupon', 'storesuite' ),
							},
						},
					},
				],
			},
			{
				label:     __( 'Comparison', 'storesuite' ),
				value:     'compare-coupons',
				chartMode: 'item-comparison',
				settings: {
					type:      'coupons',
					param:     'coupons',
					getLabels: getCouponLabels,
					labels: {
						helpText:    __( 'Check at least two coupons below to compare', 'storesuite' ),
						placeholder: __( 'Search for coupons to compare', 'storesuite' ),
						title:       __( 'Compare Coupons', 'storesuite' ),
						update:      __( 'Compare', 'storesuite' ),
					},
				},
			},
		],
	},
] );
