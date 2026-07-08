import { __ } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';
import { lazy } from '@wordpress/element';
import { getAdminSetting } from '../../utils/admin-settings';

const OverviewReport   = lazy( () => import( /* webpackChunkName: "ss-analytics-overview" */   './overview' ) );
const RevenueReport    = lazy( () => import( /* webpackChunkName: "ss-analytics-revenue" */    './revenue' ) );
const OrdersReport     = lazy( () => import( /* webpackChunkName: "ss-analytics-orders" */     './orders' ) );
const ProductsReport   = lazy( () => import( /* webpackChunkName: "ss-analytics-products" */   './products' ) );
const VariationsReport = lazy( () => import( /* webpackChunkName: "ss-analytics-variations" */ './variations' ) );
const CategoriesReport = lazy( () => import( /* webpackChunkName: "ss-analytics-categories" */ './categories' ) );
const CouponsReport    = lazy( () => import( /* webpackChunkName: "ss-analytics-coupons" */    './coupons' ) );
const TaxesReport      = lazy( () => import( /* webpackChunkName: "ss-analytics-taxes" */      './taxes' ) );
const DownloadsReport  = lazy( () => import( /* webpackChunkName: "ss-analytics-downloads" */  './downloads' ) );
const StockReport      = lazy( () => import( /* webpackChunkName: "ss-analytics-stock" */      './stock' ) );
const CustomersReport  = lazy( () => import( /* webpackChunkName: "ss-analytics-customers" */  './customers' ) );
const SettingsReport   = lazy( () => import( /* webpackChunkName: "ss-analytics-settings" */   './settings' ) );

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
			report:  'coupons',
			title:   __( 'Coupons', 'storesuite' ),
			component: CouponsReport,
		},
		{
			report:  'taxes',
			title:   __( 'Taxes', 'storesuite' ),
			component: TaxesReport,
		},
		{
			report:  'downloads',
			title:   __( 'Downloads', 'storesuite' ),
			component: DownloadsReport,
		},
		{
			report:  'stock',
			title:   __( 'Stock', 'storesuite' ),
			component: StockReport,
		},
		{
			report:  'customers',
			title:   __( 'Customers', 'storesuite' ),
			component: CustomersReport,
		},
		{
			report:  'settings',
			title:   __( 'Settings', 'storesuite' ),
			component: SettingsReport,
		},
	];

	// Mirror WooCommerce core: the Stock report only exists when stock
	// management is enabled.
	const manageStock = getAdminSetting( 'manageStock', 'no' );
	const available   = 'yes' === manageStock
		? reports
		: reports.filter( ( { report } ) => 'stock' !== report );

	return applyFilters( REPORTS_FILTER, available );
};
