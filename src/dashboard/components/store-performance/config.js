import { __ } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';

export const indicators = applyFilters(
	'storesuite_dashboard_performance_indicators',
	[
		{
			stat: 'revenue/net_revenue',
			key: 'net_revenue',
			label: __( 'Net sales', 'storesuite' ),
			type: 'currency',
			isReverseTrend: false,
			endpoint: 'revenue',
			linkedReport: null,
			icon: 'banknotes',
			color: 'green',
		},
		{
			stat: 'orders/orders_count',
			key: 'orders_count',
			label: __( 'Orders', 'storesuite' ),
			type: 'number',
			isReverseTrend: false,
			endpoint: 'orders',
			linkedReport: null,
			icon: 'shopping-cart',
			color: 'blue',
		},
		{
			stat: 'orders/avg_order_value',
			key: 'avg_order_value',
			label: __( 'Average order value', 'storesuite' ),
			type: 'currency',
			isReverseTrend: false,
			endpoint: 'orders',
			linkedReport: null,
			icon: 'receipt-percent',
			color: 'purple',
		},
		{
			stat: 'revenue/refunds',
			key: 'refunds',
			label: __( 'Returns', 'storesuite' ),
			type: 'currency',
			isReverseTrend: true,
			endpoint: 'revenue',
			linkedReport: null,
			icon: 'arrow-uturn-left',
			color: 'pink',
		},
		{
			stat: 'coupons/amount',
			key: 'amount',
			label: __( 'Net discount amount', 'storesuite' ),
			type: 'currency',
			isReverseTrend: false,
			endpoint: 'coupons',
			linkedReport: null,
			icon: 'tag',
			color: 'orange',
		},
		{
			stat: 'products/items_sold',
			key: 'items_sold',
			label: __( 'Products sold', 'storesuite' ),
			type: 'number',
			isReverseTrend: false,
			endpoint: 'products',
			linkedReport: null,
			icon: 'cube',
			color: 'teal',
		},
	]
);

export const filters = applyFilters(
	'storesuite_dashboard_performance_filters',
	[]
);
export const advancedFilters = applyFilters(
	'storesuite_dashboard_performance_advanced_filters',
	{}
);
