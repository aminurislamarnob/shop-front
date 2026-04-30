import { Fragment, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import PropTypes from 'prop-types';
import { EllipsisMenu, MenuItem, MenuTitle } from '@woocommerce/components';
import { indicators, filters, advancedFilters } from './config';
import ReportChart from '../../components/report-chart';
import ReportFilters from '../../components/report-filters';
import OverviewSummary from './overview-summary';

const HIDDEN_STATS_KEY = 'storesuite_analytics_hidden_stats';

function getStoredHiddenStats() {
	try {
		return JSON.parse( localStorage.getItem( HIDDEN_STATS_KEY ) ) || [];
	} catch {
		return [];
	}
}

export default function OverviewReport( { path, query } ) {
	const [ hiddenStats, setHiddenStats ] = useState( getStoredHiddenStats );

	const toggleStat = ( stat ) => {
		const next = hiddenStats.includes( stat )
			? hiddenStats.filter( ( s ) => s !== stat )
			: [ ...hiddenStats, stat ];
		setHiddenStats( next );
		try {
			localStorage.setItem( HIDDEN_STATS_KEY, JSON.stringify( next ) );
		} catch {
			// localStorage unavailable
		}
	};

	const visibleIndicators = indicators.filter( ( i ) => ! hiddenStats.includes( i.stat ) );

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
							<MenuTitle>
								{ __( 'Display stats:', 'storesuite' ) }
							</MenuTitle>
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
			</div>

			{ visibleIndicators.length > 0 && (
				<div className="storesuite-overview-charts-grid">
					{ visibleIndicators.map( ( indicator ) => {
						const chartConfig = {
							key:   indicator.key,
							label: indicator.label,
							type:  indicator.type,
						};
						return (
							<div key={ indicator.stat } className="storesuite-overview-chart-item">
								<ReportChart
									charts={ [ chartConfig ] }
									endpoint={ indicator.endpoint }
									path={ path }
									query={ query }
									selectedChart={ chartConfig }
									filters={ [] }
									advancedFilters={ {} }
									mode="time-comparison"
									showHeaderControls={ false }
								/>
							</div>
						);
					} ) }
				</div>
			) }
		</Fragment>
	);
}

OverviewReport.propTypes = {
	path:  PropTypes.string.isRequired,
	query: PropTypes.object.isRequired,
};
