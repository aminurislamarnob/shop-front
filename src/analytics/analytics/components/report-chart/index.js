import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { compose } from '@wordpress/compose';
import { format as formatDate } from '@wordpress/date';
import { withSelect } from '@wordpress/data';
import { get, isEqual } from 'lodash';
import PropTypes from 'prop-types';
import { Chart } from '@woocommerce/components';
import {
	getReportChartData,
	getTooltipValueFormat,
	REPORTS_STORE_NAME,
} from '@woocommerce/data';
import {
	getAllowedIntervalsForQuery,
	getCurrentDates,
	getDateFormatsForInterval,
	getIntervalForQuery,
	getChartTypeForQuery,
} from '@woocommerce/date';
import { CurrencyContext } from '@woocommerce/currency';

import ReportError from '../report-error';
import { getDefaultDateRange } from '../../../utils/date';
import { getChartMode, getSelectedFilter, createDateFormatter, buildChartData } from './utils';

export class ReportChart extends Component {
	shouldComponentUpdate( nextProps ) {
		if (
			nextProps.isRequesting !== this.props.isRequesting ||
			nextProps.primaryData.isRequesting !== this.props.primaryData.isRequesting ||
			nextProps.secondaryData.isRequesting !== this.props.secondaryData.isRequesting ||
			! isEqual( nextProps.query, this.props.query )
		) {
			return true;
		}
		return false;
	}

	getItemChartData() {
		const { primaryData, selectedChart } = this.props;
		return primaryData.data.intervals.map( ( interval ) => {
			const intervalData = {};
			( interval.subtotals.segments || [] ).forEach( ( segment ) => {
				if ( segment.segment_label ) {
					const label = intervalData[ segment.segment_label ]
						? segment.segment_label + ' (#' + segment.segment_id + ')'
						: segment.segment_label;
					intervalData[ segment.segment_id ] = {
						label,
						value: segment.subtotals[ selectedChart.key ] || 0,
					};
				}
			} );
			return {
				date: formatDate( "Y-m-d\\TH:i:s", interval.date_start ),
				...intervalData,
			};
		} );
	}

	getTimeChartData() {
		const { query, primaryData, secondaryData, selectedChart, defaultDateRange } = this.props;
		const currentInterval = getIntervalForQuery( query, defaultDateRange );
		const { primary, secondary } = getCurrentDates( query, defaultDateRange );
		return buildChartData(
			primaryData,
			secondaryData,
			primary,
			secondary,
			query.compare,
			selectedChart.key,
			currentInterval
		);
	}

	getTimeChartTotals() {
		const { primaryData, secondaryData, selectedChart } = this.props;
		return {
			primary:   get( primaryData, [ 'data', 'totals', selectedChart.key ], null ),
			secondary: get( secondaryData, [ 'data', 'totals', selectedChart.key ], null ),
		};
	}

	renderChart( mode, isRequesting, chartData, legendTotals ) {
		const {
			emptySearchResults,
			filterParam,
			interactiveLegend,
			itemsLabel,
			legendPosition,
			path,
			query,
			selectedChart,
			showHeaderControls,
			primaryData,
			defaultDateRange,
		} = this.props;
		const currentInterval   = getIntervalForQuery( query, defaultDateRange );
		const allowedIntervals  = getAllowedIntervalsForQuery( query, defaultDateRange );
		const formats           = getDateFormatsForInterval(
			currentInterval,
			primaryData.data.intervals.length,
			{ type: 'php' }
		);
		const emptyMessage       = emptySearchResults
			? __( 'No data for the current search', 'storesuite' )
			: __( 'No data for the selected date range', 'storesuite' );
		const { formatAmount, getCurrencyConfig } = this.context;

		return (
			<Chart
				allowedIntervals={ allowedIntervals }
				data={ chartData }
				dateParser="%Y-%m-%dT%H:%M:%S"
				emptyMessage={ emptyMessage }
				filterParam={ filterParam }
				interactiveLegend={ interactiveLegend }
				interval={ currentInterval }
				isRequesting={ isRequesting }
				itemsLabel={ itemsLabel }
				legendPosition={ legendPosition }
				legendTotals={ legendTotals }
				mode={ mode }
				path={ path }
				query={ query }
				screenReaderFormat={ createDateFormatter( formats.screenReaderFormat ) }
				showHeaderControls={ showHeaderControls }
				title={ selectedChart.label }
				tooltipLabelFormat={ createDateFormatter( formats.tooltipLabelFormat ) }
				tooltipTitle={ ( mode === 'time-comparison' && selectedChart.label ) || null }
				tooltipValueFormat={ getTooltipValueFormat( selectedChart.type, formatAmount ) }
				chartType={ getChartTypeForQuery( query ) }
				valueType={ selectedChart.type }
				xFormat={ createDateFormatter( formats.xFormat ) }
				x2Format={ createDateFormatter( formats.x2Format ) }
				currency={ getCurrencyConfig() }
			/>
		);
	}

	renderItemComparison() {
		const { isRequesting, primaryData } = this.props;
		if ( primaryData.isError ) {
			return <ReportError />;
		}
		const isChartRequesting = isRequesting || primaryData.isRequesting;
		return this.renderChart( 'item-comparison', isChartRequesting, this.getItemChartData() );
	}

	renderTimeComparison() {
		const { isRequesting, primaryData, secondaryData } = this.props;
		if ( ! primaryData || primaryData.isError || secondaryData.isError ) {
			return <ReportError />;
		}
		const isChartRequesting =
			isRequesting || primaryData.isRequesting || secondaryData.isRequesting;
		return this.renderChart(
			'time-comparison',
			isChartRequesting,
			this.getTimeChartData(),
			this.getTimeChartTotals()
		);
	}

	render() {
		const { mode } = this.props;
		if ( mode === 'item-comparison' ) {
			return this.renderItemComparison();
		}
		return this.renderTimeComparison();
	}
}

ReportChart.contextType = CurrencyContext;

ReportChart.defaultProps = {
	isRequesting: false,
	primaryData: {
		data: { intervals: [] },
		isError: false,
		isRequesting: false,
	},
	secondaryData: {
		data: { intervals: [] },
		isError: false,
		isRequesting: false,
	},
};

export default compose(
	withSelect( ( select, props ) => {
		const {
			charts,
			endpoint,
			filters,
			isRequesting,
			limitProperties,
			query,
			advancedFilters,
		} = props;
		const limitBy        = limitProperties || [ endpoint ];
		const selectedFilter = getSelectedFilter( filters, query );
		const filterParam    = get( selectedFilter, [ 'settings', 'param' ] );
		const chartMode      =
			props.mode || getChartMode( selectedFilter, query ) || 'time-comparison';

		const defaultDateRange = getDefaultDateRange( select );

		const reportStoreSelector = select( REPORTS_STORE_NAME );

		const newProps = { mode: chartMode, filterParam, defaultDateRange };

		if ( isRequesting ) {
			return newProps;
		}

		const hasLimitByParam = limitBy.some(
			( item ) => query[ item ] && query[ item ].length
		);

		if ( query.search && ! hasLimitByParam ) {
			return { ...newProps, emptySearchResults: true };
		}

		const fields = charts && charts.map( ( chart ) => chart.key );

		const primaryData = getReportChartData( {
			endpoint,
			dataType: 'primary',
			query,
			selector: reportStoreSelector,
			limitBy,
			filters,
			advancedFilters,
			defaultDateRange,
			fields,
		} );

		if ( chartMode === 'item-comparison' ) {
			return { ...newProps, primaryData };
		}

		const secondaryData = getReportChartData( {
			endpoint,
			dataType: 'secondary',
			query,
			selector: reportStoreSelector,
			limitBy,
			filters,
			advancedFilters,
			defaultDateRange,
			fields,
		} );

		return { ...newProps, primaryData, secondaryData };
	} )
)( ReportChart );
