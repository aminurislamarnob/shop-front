import { __ } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';

export const indicators = applyFilters( 'storesuite_dashboard_performance_indicators', [
	{ stat: 'revenue/total_sales',       key: 'total_sales',       label: __( 'Total sales', 'storesuite' ),         type: 'currency', isReverseTrend: false, endpoint: 'revenue',   linkedReport: 'revenue' },
	{ stat: 'revenue/gross_sales',       key: 'gross_sales',       label: __( 'Gross sales', 'storesuite' ),         type: 'currency', isReverseTrend: false, endpoint: 'revenue',   linkedReport: 'revenue' },
	{ stat: 'revenue/net_revenue',       key: 'net_revenue',       label: __( 'Net sales', 'storesuite' ),           type: 'currency', isReverseTrend: false, endpoint: 'revenue',   linkedReport: 'revenue' },
	{ stat: 'orders/orders_count',       key: 'orders_count',      label: __( 'Orders', 'storesuite' ),              type: 'number',   isReverseTrend: false, endpoint: 'orders',    linkedReport: 'orders' },
	{ stat: 'orders/avg_order_value',    key: 'avg_order_value',   label: __( 'Average order value', 'storesuite' ), type: 'currency', isReverseTrend: false, endpoint: 'orders',    linkedReport: 'orders' },
	{ stat: 'products/items_sold',       key: 'items_sold',        label: __( 'Products sold', 'storesuite' ),       type: 'number',   isReverseTrend: false, endpoint: 'products',  linkedReport: 'products' },
	{ stat: 'variations/items_sold',     key: 'items_sold',        label: __( 'Variations sold', 'storesuite' ),     type: 'number',   isReverseTrend: false, endpoint: 'variations', linkedReport: 'variations' },
	{ stat: 'revenue/refunds',           key: 'refunds',           label: __( 'Returns', 'storesuite' ),             type: 'currency', isReverseTrend: true,  endpoint: 'revenue',   linkedReport: 'revenue' },
	{ stat: 'coupons/orders_count',      key: 'orders_count',      label: __( 'Discounted orders', 'storesuite' ),   type: 'number',   isReverseTrend: false, endpoint: 'coupons',   linkedReport: null },
	{ stat: 'coupons/amount',            key: 'amount',            label: __( 'Net discount amount', 'storesuite' ), type: 'currency', isReverseTrend: false, endpoint: 'coupons',   linkedReport: null },
	{ stat: 'taxes/total_tax',           key: 'total_tax',         label: __( 'Total tax', 'storesuite' ),           type: 'currency', isReverseTrend: false, endpoint: 'taxes',     linkedReport: null },
	{ stat: 'taxes/order_tax',           key: 'order_tax',         label: __( 'Order tax', 'storesuite' ),           type: 'currency', isReverseTrend: false, endpoint: 'taxes',     linkedReport: null },
	{ stat: 'taxes/shipping_tax',        key: 'shipping_tax',      label: __( 'Shipping tax', 'storesuite' ),        type: 'currency', isReverseTrend: false, endpoint: 'taxes',     linkedReport: null },
	{ stat: 'revenue/shipping',          key: 'shipping',          label: __( 'Shipping', 'storesuite' ),            type: 'currency', isReverseTrend: false, endpoint: 'revenue',   linkedReport: 'revenue' },
	{ stat: 'downloads/download_count',  key: 'download_count',    label: __( 'Downloads', 'storesuite' ),           type: 'number',   isReverseTrend: false, endpoint: 'downloads', linkedReport: null },
] );

export const filters         = applyFilters( 'storesuite_dashboard_performance_filters', [] );
export const advancedFilters = applyFilters( 'storesuite_dashboard_performance_advanced_filters', {} );
