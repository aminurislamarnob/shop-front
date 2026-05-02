import { Fragment, useState, useEffect } from '@wordpress/element';
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
import { EllipsisMenu, MenuItem, MenuTitle } from '@woocommerce/components';

import { getAdminSetting } from './utils/admin-settings';
import { redirectIfAdminUrl } from './utils/helper';
import { DASHBOARD_DEFAULT_DATE_RANGE } from './constants';
import DashboardDateRangePicker from './components/date-range-picker';
import StorePerformance from './components/store-performance';
import { indicators } from './components/store-performance/config';
import DashboardLeaderboards from './components/leaderboards';
import NetSalesChart from './components/net-sales-chart';
import RecentOrders from './components/recent-orders';
import QuickActions from './components/quick-actions';

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

	const defaultQueryParams = Object.fromEntries(
		new URLSearchParams( DASHBOARD_DEFAULT_DATE_RANGE )
	);
	const query = { ...defaultQueryParams, ...getQuery() };

	const [ hiddenStats, setHiddenStats ] = useState( () =>
		lsParsed( HIDDEN_STATS_KEY )
	);

	useEffect( () => {
		redirectIfAdminUrl();
	}, [ location ] );

	const toggleStat = ( stat ) => {
		const next = hiddenStats.includes( stat )
			? hiddenStats.filter( ( s ) => s !== stat )
			: [ ...hiddenStats, stat ];
		setHiddenStats( next );
		lsSet( HIDDEN_STATS_KEY, JSON.stringify( next ) );
	};

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
								<NetSalesChart query={ query } path={ path } />
							</div>
							<div className="col-12 col-lg-5">
								<DashboardLeaderboards query={ query } />
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
