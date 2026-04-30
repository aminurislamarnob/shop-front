import { Component, Fragment } from '@wordpress/element';
import PropTypes from 'prop-types';
import { advancedFilters, filters } from './config';
import StockReportTable from './table';
import ReportError from '../../components/report-error';
import ReportFilters from '../../components/report-filters';

export default class StockReport extends Component {
	render() {
		const { path, query, isError, isRequesting } = this.props;

		if ( isError ) {
			return <ReportError />;
		}

		return (
			<Fragment>
				<ReportFilters
					query={ query }
					path={ path }
					filters={ filters }
					advancedFilters={ advancedFilters }
					report="stock"
				/>
				<StockReportTable
					isRequesting={ isRequesting }
					query={ query }
					filters={ filters }
					advancedFilters={ advancedFilters }
				/>
			</Fragment>
		);
	}
}

StockReport.propTypes = {
	path:  PropTypes.string.isRequired,
	query: PropTypes.object.isRequired,
};
