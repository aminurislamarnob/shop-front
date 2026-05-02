import { __ } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';

export const indicators = applyFilters( 'storesuite_dashboard_performance_indicators', [
	{ stat: 'revenue/net_revenue',    key: 'net_revenue',    label: __( 'Net sales', 'storesuite' ),           type: 'currency', isReverseTrend: false, endpoint: 'revenue', linkedReport: 'revenue' },
	{ stat: 'orders/orders_count',    key: 'orders_count',   label: __( 'Orders', 'storesuite' ),              type: 'number',   isReverseTrend: false, endpoint: 'orders',  linkedReport: 'orders' },
	{ stat: 'orders/avg_order_value', key: 'avg_order_value',label: __( 'Average order value', 'storesuite' ), type: 'currency', isReverseTrend: false, endpoint: 'orders',  linkedReport: 'orders' },
	{ stat: 'revenue/refunds',        key: 'refunds',        label: __( 'Returns', 'storesuite' ),             type: 'currency', isReverseTrend: true,  endpoint: 'revenue', linkedReport: 'revenue' },
	{ stat: 'coupons/amount',         key: 'amount',         label: __( 'Net discount amount', 'storesuite' ), type: 'currency', isReverseTrend: false, endpoint: 'coupons', linkedReport: null },
	{ stat: 'products/items_sold',    key: 'items_sold',     label: __( 'Products sold', 'storesuite' ),       type: 'number',   isReverseTrend: false, endpoint: 'products',linkedReport: 'products' },
] );

export const filters         = applyFilters( 'storesuite_dashboard_performance_filters', [] );
export const advancedFilters = applyFilters( 'storesuite_dashboard_performance_advanced_filters', {} );
