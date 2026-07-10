import { Component, Fragment } from '@wordpress/element';
import PropTypes from 'prop-types';
import { advancedFilters, filters } from './config';
import CustomersReportTable from './table';
import ReportFilters from '../../components/report-filters';

export default class CustomersReport extends Component {
	render() {
		const { path, query } = this.props;
		return (
			<Fragment>
				<ReportFilters
					query={ query }
					path={ path }
					report="customers"
					showDatePicker={ false }
					filters={ filters }
					advancedFilters={ advancedFilters }
				/>
				<CustomersReportTable
					query={ query }
					filters={ filters }
					advancedFilters={ advancedFilters }
				/>
			</Fragment>
		);
	}
}

CustomersReport.propTypes = {
	path:  PropTypes.string.isRequired,
	query: PropTypes.object.isRequired,
};
