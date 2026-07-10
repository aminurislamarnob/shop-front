import { Component, Fragment } from '@wordpress/element';
import PropTypes from 'prop-types';
import { advancedFilters, charts, filters } from './config';
import getSelectedChart from '../../../lib/get-selected-chart';
import ReportChart from '../../components/report-chart';
import ReportSummary from '../../components/report-summary';
import DownloadsReportTable from './table';
import ReportFilters from '../../components/report-filters';

export default class DownloadsReport extends Component {
	render() {
		const { path, query } = this.props;
		return (
			<Fragment>
				<ReportFilters
					query={ query }
					path={ path }
					report="downloads"
					filters={ filters }
					advancedFilters={ advancedFilters }
				/>
				<ReportSummary
					charts={ charts }
					endpoint="downloads"
					query={ query }
					selectedChart={ getSelectedChart( query.chart, charts ) }
					filters={ filters }
					advancedFilters={ advancedFilters }
				/>
				<ReportChart
					charts={ charts }
					endpoint="downloads"
					path={ path }
					query={ query }
					selectedChart={ getSelectedChart( query.chart, charts ) }
					filters={ filters }
					advancedFilters={ advancedFilters }
				/>
				<DownloadsReportTable
					query={ query }
					filters={ filters }
					advancedFilters={ advancedFilters }
				/>
			</Fragment>
		);
	}
}

DownloadsReport.propTypes = {
	path:  PropTypes.string.isRequired,
	query: PropTypes.object.isRequired,
};
