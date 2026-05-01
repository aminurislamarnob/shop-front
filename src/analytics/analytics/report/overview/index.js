import { Fragment, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { Button, NavigableMenu, SelectControl } from '@wordpress/components';
import clsx from 'clsx';
import PropTypes from 'prop-types';
import LineGraphIcon from 'gridicons/dist/line-graph';
import StatsAltIcon from 'gridicons/dist/stats-alt';
import { EllipsisMenu, MenuItem, MenuTitle } from '@woocommerce/components';
import { SETTINGS_STORE_NAME } from '@woocommerce/data';
import { getAllowedIntervalsForQuery } from '@woocommerce/date';
import { indicators, filters, advancedFilters } from './config';
import ReportChart from '../../components/report-chart';
import ReportFilters from '../../components/report-filters';
import OverviewSummary from './overview-summary';
import OverviewLeaderboards from './overview-leaderboards';

const HIDDEN_STATS_KEY   = 'storesuite_analytics_hidden_stats';
const HIDDEN_CHARTS_KEY  = 'storesuite_analytics_hidden_charts';
const CHART_INTERVAL_KEY = 'storesuite_analytics_chart_interval';
const CHART_TYPE_KEY     = 'storesuite_analytics_chart_type';

function lsGet( key, fallback ) {
	try { return localStorage.getItem( key ) || fallback; } catch { return fallback; }
}

function lsSet( key, value ) {
	try { localStorage.setItem( key, value ); } catch {}
}

function lsParsed( key ) {
	try { return JSON.parse( localStorage.getItem( key ) ) || []; } catch { return []; }
}

const INTERVAL_LABELS = {
	hour:    __( 'By hour',    'storesuite' ),
	day:     __( 'By day',     'storesuite' ),
	week:    __( 'By week',    'storesuite' ),
	month:   __( 'By month',   'storesuite' ),
	quarter: __( 'By quarter', 'storesuite' ),
	year:    __( 'By year',    'storesuite' ),
};

export default function OverviewReport( { path, query } ) {
	const [ hiddenStats,   setHiddenStats   ] = useState( () => lsParsed( HIDDEN_STATS_KEY ) );
	const [ hiddenCharts,  setHiddenCharts  ] = useState( () => lsParsed( HIDDEN_CHARTS_KEY ) );
	const [ chartInterval, setChartInterval ] = useState( () => lsGet( CHART_INTERVAL_KEY, 'day' ) );
	const [ chartType,     setChartType     ] = useState( () => lsGet( CHART_TYPE_KEY, 'line' ) );

	const defaultDateRange = useSelect( ( select ) =>
		select( SETTINGS_STORE_NAME )
			.getSetting( 'wc_admin', 'wcAdminSettings' )
			?.woocommerce_default_date_range
	);

	const toggleStat = ( stat ) => {
		const next = hiddenStats.includes( stat )
			? hiddenStats.filter( ( s ) => s !== stat )
			: [ ...hiddenStats, stat ];
		setHiddenStats( next );
		lsSet( HIDDEN_STATS_KEY, JSON.stringify( next ) );
	};

	const toggleChart = ( stat ) => {
		const next = hiddenCharts.includes( stat )
			? hiddenCharts.filter( ( s ) => s !== stat )
			: [ ...hiddenCharts, stat ];
		setHiddenCharts( next );
		lsSet( HIDDEN_CHARTS_KEY, JSON.stringify( next ) );
	};

	const handleInterval = ( value ) => {
		setChartInterval( value );
		lsSet( CHART_INTERVAL_KEY, value );
	};

	const handleChartType = ( type ) => () => {
		setChartType( type );
		lsSet( CHART_TYPE_KEY, type );
	};

	const visibleIndicators = indicators.filter( ( i ) => ! hiddenStats.includes( i.stat ) );
	const visibleCharts     = indicators.filter( ( i ) => ! hiddenCharts.includes( i.stat ) );
	const allowedIntervals  = getAllowedIntervalsForQuery( query, defaultDateRange ) || [ 'day', 'week', 'month' ];
	const chartQuery        = { ...query, chartType, interval: chartInterval };

	return (
		<Fragment>
			<ReportFilters
				query={ query }
				path={ path }
				report="overview"
				filters={ filters }
				advancedFilters={ advancedFilters }
			/>

			<div className="storesuite-overview-section-header">
				<h3>{ __( 'Performance', 'storesuite' ) }</h3>
				<EllipsisMenu
					label={ __( 'Choose which analytics to display', 'storesuite' ) }
					renderContent={ () => (
						<Fragment>
							<MenuTitle>{ __( 'Display stats:', 'storesuite' ) }</MenuTitle>
							{ indicators.map( ( indicator ) => (
								<MenuItem
									key={ indicator.stat }
									checked={ ! hiddenStats.includes( indicator.stat ) }
									isCheckbox
									isClickable
									onInvoke={ () => toggleStat( indicator.stat ) }
								>
									{ indicator.label }
								</MenuItem>
							) ) }
						</Fragment>
					) }
				/>
			</div>

			{ visibleIndicators.length > 0 ? (
				<OverviewSummary
					indicators={ visibleIndicators }
					query={ query }
				/>
			) : (
				<p className="storesuite-overview-empty-notice">
					{ __( 'No stats selected. Use the menu above to choose which stats to display.', 'storesuite' ) }
				</p>
			) }

			<div className="storesuite-overview-section-header">
				<h3>{ __( 'Charts', 'storesuite' ) }</h3>

				<div className="storesuite-overview-chart-controls">
					<SelectControl
						className="woocommerce-chart__interval-select"
						value={ chartInterval }
						options={ allowedIntervals.map( ( interval ) => ( {
							value: interval,
							label: INTERVAL_LABELS[ interval ] || interval,
						} ) ) }
						onChange={ handleInterval }
						__next40pxDefaultSize
						__nextHasNoMarginBottom
					/>

					<NavigableMenu
						className="woocommerce-chart__types"
						orientation="horizontal"
						role="menubar"
					>
						<Button
							className={ clsx( 'woocommerce-chart__type-button', {
								'woocommerce-chart__type-button-selected': ! chartType || chartType === 'line',
							} ) }
							title={ __( 'Line chart', 'storesuite' ) }
							aria-checked={ chartType === 'line' }
							role="menuitemradio"
							tabIndex={ chartType === 'line' ? 0 : -1 }
							onClick={ handleChartType( 'line' ) }
						>
							<LineGraphIcon />
						</Button>
						<Button
							className={ clsx( 'woocommerce-chart__type-button', {
								'woocommerce-chart__type-button-selected': chartType === 'bar',
							} ) }
							title={ __( 'Bar chart', 'storesuite' ) }
							aria-checked={ chartType === 'bar' }
							role="menuitemradio"
							tabIndex={ chartType === 'bar' ? 0 : -1 }
							onClick={ handleChartType( 'bar' ) }
						>
							<StatsAltIcon />
						</Button>
					</NavigableMenu>

					<EllipsisMenu
						label={ __( 'Choose which charts to display', 'storesuite' ) }
						renderContent={ () => (
							<Fragment>
								<MenuTitle>{ __( 'Charts', 'storesuite' ) }</MenuTitle>
								{ indicators.map( ( indicator ) => (
									<MenuItem
										key={ indicator.stat }
										checked={ ! hiddenCharts.includes( indicator.stat ) }
										isCheckbox
										isClickable
										onInvoke={ () => toggleChart( indicator.stat ) }
									>
										{ indicator.label }
									</MenuItem>
								) ) }
							</Fragment>
						) }
					/>
				</div>
			</div>

			{ visibleCharts.length > 0 && (
				<div className="storesuite-overview-charts-grid">
					{ visibleCharts.map( ( indicator ) => {
						const chartConfig = {
							key:   indicator.key,
							label: indicator.label,
							type:  indicator.type,
						};
						return (
							<div key={ indicator.stat } className="storesuite-overview-chart-item">
								<h4 className="storesuite-overview-chart-title">
									{ indicator.label }
								</h4>
								<ReportChart
									charts={ [ chartConfig ] }
									endpoint={ indicator.endpoint }
									path={ path }
									query={ chartQuery }
									selectedChart={ chartConfig }
									filters={ [] }
									advancedFilters={ {} }
									mode="time-comparison"
									showHeaderControls={ false }
									legendPosition="bottom"
									interactiveLegend={ false }
								/>
							</div>
						);
					} ) }
				</div>
			) }

			<OverviewLeaderboards query={ query } />
		</Fragment>
	);
}

OverviewReport.propTypes = {
	path:  PropTypes.string.isRequired,
	query: PropTypes.object.isRequired,
};
