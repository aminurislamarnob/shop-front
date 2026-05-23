import { Component } from '@wordpress/element';
import { ReportFilters as Filters } from '@woocommerce/components';
import {
	getCurrentDates,
	getDateParamsFromQuery,
	isoDateFormat,
} from '@woocommerce/date';
import { CurrencyContext } from '@woocommerce/currency';
import PropTypes from 'prop-types';

import { LOCALE } from '../../utils/admin-settings';
import { DASHBOARD_DEFAULT_DATE_RANGE } from '../../constants';

class DashboardDateRangePicker extends Component {
	render() {
		const { path, query } = this.props;
		const { period, compare, before, after } = getDateParamsFromQuery( query, DASHBOARD_DEFAULT_DATE_RANGE );
		const { primary: primaryDate, secondary: secondaryDate } = getCurrentDates( query, DASHBOARD_DEFAULT_DATE_RANGE );
		const dateQuery = { period, compare, before, after, primaryDate, secondaryDate };
		const Currency  = this.context;

		return (
			<Filters
				query={ query }
				siteLocale={ LOCALE.siteLocale }
				currency={ Currency.getCurrencyConfig() }
				path={ path }
				filters={ [] }
				advancedFilters={ {} }
				showDatePicker={ true }
				onDateSelect={ () => {} }
				onFilterSelect={ () => {} }
				onAdvancedFilterAction={ () => {} }
				dateQuery={ dateQuery }
				isoDateFormat={ isoDateFormat }
			/>
		);
	}
}

DashboardDateRangePicker.contextType = CurrencyContext;

DashboardDateRangePicker.propTypes = {
	path:  PropTypes.string.isRequired,
	query: PropTypes.object,
};

export default DashboardDateRangePicker;
