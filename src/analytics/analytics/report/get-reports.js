import { __ } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';
import { lazy } from '@wordpress/element';

const OverviewReport   = lazy( () => import( /* webpackChunkName: "ss-analytics-overview" */   './overview' ) );
const RevenueReport    = lazy( () => import( /* webpackChunkName: "ss-analytics-revenue" */    './revenue' ) );
const OrdersReport     = lazy( () => import( /* webpackChunkName: "ss-analytics-orders" */     './orders' ) );
const ProductsReport   = lazy( () => import( /* webpackChunkName: "ss-analytics-products" */   './products' ) );
const VariationsReport = lazy( () => import( /* webpackChunkName: "ss-analytics-variations" */ './variations' ) );
const CategoriesReport = lazy( () => import( /* webpackChunkName: "ss-analytics-categories" */ './categories' ) );
const StockReport      = lazy( () => import( /* webpackChunkName: "ss-analytics-stock" */      './stock' ) );

const REPORTS_FILTER = 'storesuite_analytics_reports_list';

export default () => {
	const reports = [
		{
			report:    'overview',
			title:     __( 'Overview', 'storesuite' ),
			component: OverviewReport,
		},
		{
			report:  'revenue',
			title:   __( 'Revenue', 'storesuite' ),
			component: RevenueReport,
		},
		{
			report:  'orders',
			title:   __( 'Orders', 'storesuite' ),
			component: OrdersReport,
		},
		{
			report:  'products',
			title:   __( 'Products', 'storesuite' ),
			component: ProductsReport,
		},
		{
			report:  'variations',
			title:   __( 'Variations', 'storesuite' ),
			component: VariationsReport,
		},
		{
			report:  'categories',
			title:   __( 'Categories', 'storesuite' ),
			component: CategoriesReport,
		},
		{
			report:  'stock',
			title:   __( 'Stock', 'storesuite' ),
			component: StockReport,
		},
	];

	return applyFilters( REPORTS_FILTER, reports );
};
