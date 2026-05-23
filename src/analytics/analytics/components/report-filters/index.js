import { Component } from '@wordpress/element';
import { compose } from '@wordpress/compose';
import PropTypes from 'prop-types';
import { omitBy, isUndefined, snakeCase } from 'lodash';
import { withSelect } from '@wordpress/data';
import { ReportFilters as Filters } from '@woocommerce/components';
import { SETTINGS_STORE_NAME } from '@woocommerce/data';
import {
	getCurrentDates,
	getDateParamsFromQuery,
	isoDateFormat,
} from '@woocommerce/date';
import { CurrencyContext } from '@woocommerce/currency';
import { LOCALE } from '../../../utils/admin-settings';

// CES store key — safely handle if package not available.
let CES_STORE_KEY = null;
let addCesSurveyForAnalytics = () => {};
try {
	const ces          = window?.wc?.customerEffortScore;
	CES_STORE_KEY      = ces?.STORE_KEY;
	if ( CES_STORE_KEY ) {
		// Will be retrieved via withDispatch if store is available.
	}
} catch ( e ) {
	// Customer effort score not available — skip.
}

class ReportFilters extends Component {
	constructor() {
		super();
		this.onDateSelect           = this.onDateSelect.bind( this );
		this.onFilterSelect         = this.onFilterSelect.bind( this );
		this.onAdvancedFilterAction = this.onAdvancedFilterAction.bind( this );
	}

	onDateSelect() {}
	onFilterSelect() {}
	onAdvancedFilterAction() {}

	render() {
		const { advancedFilters, filters, path, query, showDatePicker, defaultDateRange } = this.props;
		const { period, compare, before, after } = getDateParamsFromQuery( query, defaultDateRange );
		const { primary: primaryDate, secondary: secondaryDate } = getCurrentDates(
			query,
			defaultDateRange
		);
		const dateQuery = { period, compare, before, after, primaryDate, secondaryDate };
		const Currency  = this.context;

		return (
			<Filters
				query={ query }
				siteLocale={ LOCALE.siteLocale }
				currency={ Currency.getCurrencyConfig() }
				path={ path }
				filters={ filters }
				advancedFilters={ advancedFilters }
				showDatePicker={ showDatePicker }
				onDateSelect={ this.onDateSelect }
				onFilterSelect={ this.onFilterSelect }
				onAdvancedFilterAction={ this.onAdvancedFilterAction }
				dateQuery={ dateQuery }
				isoDateFormat={ isoDateFormat }
			/>
		);
	}
}

ReportFilters.contextType = CurrencyContext;

ReportFilters.propTypes = {
	advancedFilters: PropTypes.object,
	filters:         PropTypes.array,
	path:            PropTypes.string.isRequired,
	query:           PropTypes.object,
	showDatePicker:  PropTypes.bool,
	report:          PropTypes.string.isRequired,
};

export default compose(
	withSelect( ( select ) => {
		const { woocommerce_default_date_range: defaultDateRange } = select(
			SETTINGS_STORE_NAME
		).getSetting( 'wc_admin', 'wcAdminSettings' );
		return { defaultDateRange };
	} )
)( ReportFilters );
