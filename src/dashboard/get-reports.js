import { __ } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';

import lazyWithRetry from './utils/lazy-with-retry';

const DashboardDateRangePicker = lazyWithRetry( () =>
	import(
		/* webpackChunkName: "ss-dashboard-date-range-picker" */ './components/date-range-picker'
	)
);
const StorePerformance = lazyWithRetry( () =>
	import(
		/* webpackChunkName: "ss-dashboard-store-performance" */ './components/store-performance'
	)
);
const NetSalesChart = lazyWithRetry( () =>
	import(
		/* webpackChunkName: "ss-dashboard-net-sales" */ './components/net-sales-chart'
	)
);
const DashboardLeaderboards = lazyWithRetry( () =>
	import(
		/* webpackChunkName: "ss-dashboard-leaderboards" */ './components/leaderboards'
	)
);
const RecentOrders = lazyWithRetry( () =>
	import(
		/* webpackChunkName: "ss-dashboard-recent-orders" */ './components/recent-orders'
	)
);
const QuickActions = lazyWithRetry( () =>
	import(
		/* webpackChunkName: "ss-dashboard-quick-actions" */ './components/quick-actions'
	)
);

const REPORTS_FILTER = 'storesuite_dashboard_analytics_reports_list';

export default () => {
	const reports = [
		{
			report: 'DashboardDateRangePicker',
			title: __( 'DashboardDateRangePicker', 'storesuite' ),
			component: DashboardDateRangePicker,
		},
		{
			report: 'StorePerformance',
			title: __( 'StorePerformance', 'storesuite' ),
			component: StorePerformance,
		},
		{
			report: 'NetSalesChart',
			title: __( 'NetSalesChart', 'storesuite' ),
			component: NetSalesChart,
		},
		{
			report: 'DashboardLeaderboards',
			title: __( 'DashboardLeaderboards', 'storesuite' ),
			component: DashboardLeaderboards,
		},
		{
			report: 'RecentOrders',
			title: __( 'RecentOrders', 'storesuite' ),
			component: RecentOrders,
		},
		{
			report: 'QuickActions',
			title: __( 'QuickActions', 'storesuite' ),
			component: QuickActions,
		},
	];

	return applyFilters( REPORTS_FILTER, reports );
};
