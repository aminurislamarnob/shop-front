import { __ } from '@wordpress/i18n';
import { Component, Fragment } from '@wordpress/element';
import PropTypes from 'prop-types';
import { advancedFilters, charts, filters } from './config';
import getSelectedChart from '../../../lib/get-selected-chart';
import CategoriesReportTable from './table';
import ProductsReportTable from '../products/table';
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
		const isSingleCategoryView = query.filter === 'single_category' && !! query.categories;
		const mode = isCompareView || isSingleCategoryView ? 'item-comparison' : 'time-comparison';

		return {
			isSingleCategoryView,
			itemsLabel: isSingleCategoryView
				? __( '%d products', 'storesuite' )
				: __( '%d categories', 'storesuite' ),
			mode,
		};
	}

	render() {
		const { path, query, isError, isRequesting } = this.props;
		const { mode, itemsLabel, isSingleCategoryView } = this.getChartMeta();

		if ( isError ) {
			return <ReportError />;
		}

		const chartQuery = { ...query };
		if ( mode === 'item-comparison' ) {
			chartQuery.segmentby = isSingleCategoryView ? 'product' : 'category';
		}

		const limitProperties = isSingleCategoryView
			? [ 'products', 'categories' ]
			: [ 'categories' ];

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
					limitProperties={ limitProperties }
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
					limitProperties={ limitProperties }
					path={ path }
					query={ chartQuery }
					isRequesting={ isRequesting }
					itemsLabel={ itemsLabel }
					selectedChart={ getSelectedChart( chartQuery.chart, charts ) }
				/>
				{ isSingleCategoryView ? (
					<ProductsReportTable
						isRequesting={ isRequesting }
						query={ chartQuery }
						baseSearchQuery={ { filter: 'single_category' } }
						hideCompare={ isSingleCategoryView }
						filters={ filters }
						advancedFilters={ advancedFilters }
					/>
				) : (
					<CategoriesReportTable
						isRequesting={ isRequesting }
						query={ query }
						filters={ filters }
						advancedFilters={ advancedFilters }
					/>
				) }
			</Fragment>
		);
	}
}

CategoriesReport.propTypes = {
	path:  PropTypes.string.isRequired,
	query: PropTypes.object.isRequired,
};
