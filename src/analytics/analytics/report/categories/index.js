import { __ } from '@wordpress/i18n';
import { Component, Fragment } from '@wordpress/element';
import PropTypes from 'prop-types';
import { advancedFilters, charts, filters } from './config';
import getSelectedChart from '../../../lib/get-selected-chart';
import CategoriesReportTable from './table';
import ReportChart from '../../components/report-chart';
import ReportError from '../../components/report-error';
import ReportSummary from '../../components/report-summary';
import ReportFilters from '../../components/report-filters';

export default class CategoriesReport extends Component {
	getChartMeta() {
		const { query } = this.props;
		const isCompareView =
			query.filter === 'compare-categories' &&
			query.categories &&
			query.categories.split( ',' ).length > 1;

		const mode = isCompareView ? 'item-comparison' : 'time-comparison';

		return {
			itemsLabel: __( '%d categories', 'storesuite' ),
			mode,
		};
	}

	render() {
		const { path, query, isError, isRequesting } = this.props;
		const { mode, itemsLabel } = this.getChartMeta();

		if ( isError ) {
			return <ReportError />;
		}

		const chartQuery = { ...query };
		if ( mode === 'item-comparison' ) {
			chartQuery.segmentby = 'category';
		}

		return (
			<Fragment>
				<ReportFilters
					query={ query }
					path={ path }
					filters={ filters }
					advancedFilters={ advancedFilters }
					report="categories"
				/>
				<ReportSummary
					charts={ charts }
					endpoint="products"
					limitProperties={ [ 'categories' ] }
					isRequesting={ isRequesting }
					query={ chartQuery }
					selectedChart={ getSelectedChart( query.chart, charts ) }
					filters={ filters }
					advancedFilters={ advancedFilters }
					report="categories"
				/>
				<ReportChart
					charts={ charts }
					filters={ filters }
					advancedFilters={ advancedFilters }
					mode={ mode }
					endpoint="products"
					limitProperties={ [ 'categories' ] }
					path={ path }
					query={ chartQuery }
					isRequesting={ isRequesting }
					itemsLabel={ itemsLabel }
					selectedChart={ getSelectedChart( chartQuery.chart, charts ) }
				/>
				<CategoriesReportTable
					isRequesting={ isRequesting }
					query={ query }
					filters={ filters }
					advancedFilters={ advancedFilters }
				/>
			</Fragment>
		);
	}
}

CategoriesReport.propTypes = {
	path:  PropTypes.string.isRequired,
	query: PropTypes.object.isRequired,
};
