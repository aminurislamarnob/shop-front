import { Component, Fragment } from '@wordpress/element';
import PropTypes from 'prop-types';
import { advancedFilters, charts, filters } from './config';
import getSelectedChart from '../../../lib/get-selected-chart';
import ReportChart from '../../components/report-chart';
import ReportSummary from '../../components/report-summary';
import CouponsReportTable from './table';
import ReportFilters from '../../components/report-filters';

export default class CouponsReport extends Component {
	render() {
		const { path, query } = this.props;
		return (
			<Fragment>
				<ReportFilters
					query={ query }
					path={ path }
					report="coupons"
					filters={ filters }
					advancedFilters={ advancedFilters }
				/>
				<ReportSummary
					charts={ charts }
					endpoint="coupons"
					query={ query }
					selectedChart={ getSelectedChart( query.chart, charts ) }
					filters={ filters }
					advancedFilters={ advancedFilters }
				/>
				<ReportChart
					charts={ charts }
					endpoint="coupons"
					path={ path }
					query={ query }
					selectedChart={ getSelectedChart( query.chart, charts ) }
					filters={ filters }
					advancedFilters={ advancedFilters }
				/>
				<CouponsReportTable
					query={ query }
					filters={ filters }
					advancedFilters={ advancedFilters }
				/>
			</Fragment>
		);
	}
}

CouponsReport.propTypes = {
	path:  PropTypes.string.isRequired,
	query: PropTypes.object.isRequired,
};
