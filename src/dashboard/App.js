import {
	useState,
	useEffect,
	useMemo,
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

	// Defer below-fold components until after the first paint so the browser
	// can render above-fold LCP content (store performance stats) without
	// competing with chart/leaderboard/orders data fetches.
	const [ belowFoldReady, setBelowFoldReady ] = useState( false );

	useEffect( () => {
		redirectIfAdminUrl();
	}, [ location ] );

	useEffect( () => {
		const schedule = window.requestIdleCallback || ( ( fn ) => setTimeout( fn, 150 ) );
		const cancel = window.cancelIdleCallback || clearTimeout;
		const id = schedule( () => setBelowFoldReady( true ), { timeout: 300 } );
		return () => cancel( id );
	}, [] );

	const visibleIndicators = indicators.filter(
		( i ) => ! hiddenStats.includes( i.stat )
	);

	return (
		<SlotFillProvider>
			<div className="woocommerce-layout storesuite-dashboard-layout">
				<div className="woocommerce-layout__main">
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
						{ DashboardDateRangePicker && (
							<div className="dashboard-date-range-picker">
								<Suspense fallback={ null }>
									<DashboardDateRangePicker
										query={ query }
										path={ path }
									/>
								</Suspense>
							</div>
						) }
					</div>

					{ StorePerformance && (
						<Suspense fallback={ <div className="storesuite-stats-skeleton" /> }>
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
						</Suspense>
					) }

					{ belowFoldReady && NetSalesChart && DashboardLeaderboards && (
						<div className="dashboard-graph-section">
							<div className="row">
								<div className="col-12 col-lg-7">
									<Suspense fallback={ <div className="storesuite-chart-skeleton" /> }>
										<NetSalesChart
											query={ query }
											path={ path }
										/>
									</Suspense>
								</div>
								<div className="col-12 col-lg-5">
									<Suspense fallback={ <div className="storesuite-leaderboard-skeleton" /> }>
										<DashboardLeaderboards
											query={ query }
										/>
									</Suspense>
								</div>
							</div>
						</div>
					) }

					{ belowFoldReady && RecentOrders && QuickActions && (
						<div className="dashboard-graph-section">
							<div className="row">
								<div className="col-12 col-lg-7">
									<Suspense fallback={ <div className="storesuite-table-skeleton" /> }>
										<RecentOrders />
									</Suspense>
								</div>
								<div className="col-12 col-lg-5">
									<Suspense fallback={ null }>
										<QuickActions />
									</Suspense>
								</div>
							</div>
						</div>
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
