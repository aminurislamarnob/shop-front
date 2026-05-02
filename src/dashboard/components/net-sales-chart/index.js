import { Component } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { compose } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';
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
import PropTypes from 'prop-types';

import {
	buildChartData,
	createDateFormatter,
} from 'analytics/analytics/components/report-chart/utils';
import { DASHBOARD_DEFAULT_DATE_RANGE } from '../../constants';

class NetSalesChart extends Component {
	render() {
		const { query, path, primaryData, secondaryData } = this.props;
		const { formatAmount, getCurrencyConfig } = this.context;

		const currentInterval = getIntervalForQuery(
			query,
			DASHBOARD_DEFAULT_DATE_RANGE
		);
		const allowedIntervals = getAllowedIntervalsForQuery(
			query,
			DASHBOARD_DEFAULT_DATE_RANGE
		);
		const { primary, secondary } = getCurrentDates(
			query,
			DASHBOARD_DEFAULT_DATE_RANGE
		);

		const chartData = buildChartData(
			primaryData,
			secondaryData,
			primary,
			secondary,
			query.compare,
			'net_revenue',
			currentInterval
		);

		const intervalCount = primaryData?.data?.intervals?.length || 0;
		const formats = getDateFormatsForInterval(
			currentInterval,
			intervalCount,
			{ type: 'php' }
		);

		const isRequesting =
			primaryData?.isRequesting || secondaryData?.isRequesting;

		return (
			<div className="storesuite-dashboard-net-sales-chart">
				<Chart
					allowedIntervals={ allowedIntervals }
					data={ chartData }
					dateParser="%Y-%m-%dT%H:%M:%S"
					emptyMessage={ __(
						'No data for the selected date range',
						'storesuite'
					) }
					interval={ currentInterval }
					isRequesting={ isRequesting }
					legendPosition="bottom"
					mode="time-comparison"
					path={ path }
					query={ query }
					showHeaderControls={ true }
					title={ __( 'Net sales', 'storesuite' ) }
					tooltipTitle={ __( 'Net sales', 'storesuite' ) }
					chartType={ getChartTypeForQuery( query ) }
					valueType="currency"
					tooltipValueFormat={ getTooltipValueFormat(
						'currency',
						formatAmount
					) }
					screenReaderFormat={ createDateFormatter(
						formats.screenReaderFormat
					) }
					tooltipLabelFormat={ createDateFormatter(
						formats.tooltipLabelFormat
					) }
					xFormat={ createDateFormatter( formats.xFormat ) }
					x2Format={ createDateFormatter( formats.x2Format ) }
					currency={ getCurrencyConfig() }
				/>
			</div>
		);
	}
}

NetSalesChart.contextType = CurrencyContext;

NetSalesChart.defaultProps = {
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
		const { query } = props;
		const selector = select( REPORTS_STORE_NAME );

		const primaryData = getReportChartData( {
			endpoint: 'revenue',
			dataType: 'primary',
			query,
			selector,
			limitBy: [ 'revenue' ],
			filters: [],
			advancedFilters: {},
			defaultDateRange: DASHBOARD_DEFAULT_DATE_RANGE,
			fields: [ 'net_revenue' ],
		} );

		const secondaryData = getReportChartData( {
			endpoint: 'revenue',
			dataType: 'secondary',
			query,
			selector,
			limitBy: [ 'revenue' ],
			filters: [],
			advancedFilters: {},
			defaultDateRange: DASHBOARD_DEFAULT_DATE_RANGE,
			fields: [ 'net_revenue' ],
		} );

		return { primaryData, secondaryData };
	} )
)( NetSalesChart );
