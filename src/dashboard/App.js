import {
	useState,
	useEffect,
	useMemo,
	lazy,
	Suspense,
} from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { compose } from '@wordpress/compose';
import { identity } from 'lodash';
import { SlotFillProvider } from '@wordpress/components';
import {
	unstable_HistoryRouter as HistoryRouter,
	useLocation,
} from 'react-router-dom';
import { getHistory, getQuery } from '@woocommerce/navigation';
import { withOptionsHydration } from '@woocommerce/data';
import { Spinner } from '@woocommerce/components';

import { getAdminSetting } from './utils/admin-settings';
import { redirectIfAdminUrl } from './utils/helper';
import { DASHBOARD_DEFAULT_DATE_RANGE } from './constants';
import { indicators } from './components/store-performance/config';

import getReports from './get-reports';

const reports = getReports();
const getReportComponent = ( name ) => reports.find( ( r ) => r.report === name )?.component;

const DashboardDateRangePicker = getReportComponent( 'DashboardDateRangePicker' );
const StorePerformance = getReportComponent( 'StorePerformance' );
const NetSalesChart = getReportComponent( 'NetSalesChart' );
const DashboardLeaderboards = getReportComponent( 'DashboardLeaderboards' );
const RecentOrders = getReportComponent( 'RecentOrders' );
const QuickActions = getReportComponent( 'QuickActions' );

const HIDDEN_STATS_KEY = 'storesuite_dashboard_hidden_stats';

function lsParsed( key ) {
	try {
		return JSON.parse( localStorage.getItem( key ) ) || [];
	} catch {
		return [];
	}
}

function lsSet( key, value ) {
	try {
		localStorage.setItem( key, value );
	} catch {}
}

function DashboardPage() {
	const location = useLocation();
	const path = location.pathname;

	// Memoize the merged query so identity is stable across renders. Otherwise
	// every render produces a new object, propagating new prop identities to
	// child components and busting `withSelect` resolver caches.
	const query = useMemo( () => {
		const defaults = Object.fromEntries(
			new URLSearchParams( DASHBOARD_DEFAULT_DATE_RANGE )
		);
		return { ...defaults, ...getQuery() };
	}, [ location.search ] );

	const [ hiddenStats, setHiddenStats ] = useState( () =>
		lsParsed( HIDDEN_STATS_KEY )
	);

	useEffect( () => {
		redirectIfAdminUrl();
	}, [ location ] );

	const visibleIndicators = indicators.filter(
		( i ) => ! hiddenStats.includes( i.stat )
	);

	return (
		<SlotFillProvider>
			<div className="woocommerce-layout storesuite-dashboard-layout">
				<div className="woocommerce-layout__main">
					{ DashboardDateRangePicker &&
						StorePerformance &&
						NetSalesChart &&
						DashboardLeaderboards &&
						RecentOrders &&
						QuickActions && (
							<Suspense
								fallback={
									<div className="storesuite-dashboard-loading">
										<Spinner />
									</div>
								}
							>
								<div className="storesuite-dashboard-title-wrapper">
									<div className="storesuite-dashboard-title">
										<h3 className="storesuite-page-main-title">
											{ __( 'Dashboard', 'storesuite' ) }
										</h3>
										<p>
											{ __(
												"Here's what's happening with your store today.",
												'storesuite'
											) }
										</p>
									</div>
									<div className="dashboard-date-range-picker">
										<DashboardDateRangePicker
											query={ query }
											path={ path }
										/>
									</div>
								</div>

								{ visibleIndicators.length > 0 ? (
									<StorePerformance
										indicators={ visibleIndicators }
										query={ query }
									/>
								) : (
									<p className="storesuite-dashboard-empty-notice">
										{ __(
											'No stats selected. Use the menu above to choose which stats to display.',
											'storesuite'
										) }
									</p>
								) }
								<div className="dashboard-graph-section">
									<div className="row">
										<div className="col-12 col-lg-7">
											<NetSalesChart
												query={ query }
												path={ path }
											/>
										</div>
										<div className="col-12 col-lg-5">
											<DashboardLeaderboards
												query={ query }
											/>
										</div>
									</div>
								</div>
								<div className="dashboard-graph-section">
									<div className="row">
										<div className="col-12 col-lg-7">
											<RecentOrders />
										</div>
										<div className="col-12 col-lg-5">
											<QuickActions />
										</div>
									</div>
								</div>
							</Suspense>
						) }
				</div>
			</div>
		</SlotFillProvider>
	);
}

const _App = () => (
	<HistoryRouter history={ getHistory() }>
		<DashboardPage />
	</HistoryRouter>
);

export const App = compose(
	window.wcSettings?.admin
		? withOptionsHydration( {
				...getAdminSetting( 'preloadOptions', {} ),
		  } )
		: identity
)( _App );

export default App;
