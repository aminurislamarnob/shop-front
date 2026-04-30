import { __ } from '@wordpress/i18n';
import { Component, Fragment } from '@wordpress/element';
import PropTypes from 'prop-types';
import { advancedFilters, charts, filters } from './config';
import getSelectedChart from '../../../lib/get-selected-chart';
import VariationsReportTable from './table';
import ReportChart from '../../components/report-chart';
import ReportError from '../../components/report-error';
import ReportSummary from '../../components/report-summary';
import ReportFilters from '../../components/report-filters';

export default class VariationsReport extends Component {
	getChartMeta() {
		const { query } = this.props;
		const isCompareView =
			query.filter === 'compare-variations' &&
			query.variations &&
			query.variations.split( ',' ).length > 1;

		const mode =
			isCompareView || query.filter === 'single_variation' || query.filter === 'single_product'
				? 'item-comparison'
				: 'time-comparison';

		return {
			compareObject: 'variations',
			itemsLabel:    __( '%d variations', 'storesuite' ),
			mode,
		};
	}

	render() {
		const { compareObject, itemsLabel, mode } = this.getChartMeta();
		const { path, query, isError, isRequesting } = this.props;

		if ( isError ) {
			return <ReportError />;
		}

		const chartQuery = { ...query };
		if ( mode === 'item-comparison' ) {
			chartQuery.segmentby = 'variation';
		}

		return (
			<Fragment>
				<ReportFilters
					query={ query }
					path={ path }
					filters={ filters }
					advancedFilters={ advancedFilters }
					report="variations"
				/>
				<ReportSummary
					mode={ mode }
					charts={ charts }
					endpoint="variations"
					isRequesting={ isRequesting }
					query={ chartQuery }
					selectedChart={ getSelectedChart( query.chart, charts ) }
					filters={ filters }
					advancedFilters={ advancedFilters }
				/>
				<ReportChart
					charts={ charts }
					mode={ mode }
					filters={ filters }
					advancedFilters={ advancedFilters }
					endpoint="variations"
					isRequesting={ isRequesting }
					itemsLabel={ itemsLabel }
					path={ path }
					query={ chartQuery }
					selectedChart={ getSelectedChart( chartQuery.chart, charts ) }
				/>
				<VariationsReportTable
					isRequesting={ isRequesting }
					query={ query }
					filters={ filters }
					advancedFilters={ advancedFilters }
				/>
			</Fragment>
		);
	}
}

VariationsReport.propTypes = {
	path:  PropTypes.string.isRequired,
	query: PropTypes.object.isRequired,
};
