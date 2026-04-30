import { Component } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { compose } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';
import { find } from 'lodash';
import {
	SummaryList,
	SummaryListPlaceholder,
	SummaryNumber,
} from '@woocommerce/components';
import { calculateDelta, formatValue } from '@woocommerce/number';
import { REPORTS_STORE_NAME, SETTINGS_STORE_NAME } from '@woocommerce/data';
import { getCurrentDates, appendTimestamp, getDateParamsFromQuery } from '@woocommerce/date';
import { CurrencyContext } from '@woocommerce/currency';
import ReportError from '../../components/report-error';

class OverviewSummary extends Component {
	formatVal( value, format ) {
		const { formatAmount, getCurrencyConfig } = this.context;
		return format === 'currency'
			? formatAmount( value )
			: formatValue( getCurrencyConfig(), format, value );
	}

	render() {
		const {
			indicators,
			query,
			primaryData,
			secondaryData,
			primaryRequesting,
			secondaryRequesting,
			primaryError,
			secondaryError,
			defaultDateRange,
		} = this.props;

		if ( primaryRequesting || secondaryRequesting ) {
			return <SummaryListPlaceholder numberOfItems={ indicators.length } />;
		}

		if ( primaryError || secondaryError ) {
			return <ReportError />;
		}

		const { compare } = getDateParamsFromQuery( query, defaultDateRange );
		const prevLabel =
			compare === 'previous_period'
				? __( 'Previous period:', 'storesuite' )
				: __( 'Previous year:', 'storesuite' );

		const renderItems = () =>
			indicators
				.map( ( indicator ) => {
					const primary   = find( primaryData.data,   ( d ) => d.stat === indicator.stat );
					const secondary = find( secondaryData.data, ( d ) => d.stat === indicator.stat );

					if ( ! primary || ! secondary ) {
						return null;
					}

					const format         = primary.format || indicator.type;
					const primaryValue   = this.formatVal( primary.value,   format );
					const secondaryValue = this.formatVal( secondary.value, format );
					const delta          = calculateDelta( primary.value, secondary.value );
					const href           = ( () => {
						if ( ! indicator.linkedReport ) return '';
						const p = new URLSearchParams( window.location.search );
						p.set( 'report', indicator.linkedReport );
						return window.location.pathname + '?' + p.toString();
					} )();

					return (
						<SummaryNumber
							key={ indicator.stat }
							delta={ delta }
							href={ href }
							label={ indicator.label }
							reverseTrend={ indicator.isReverseTrend }
							prevLabel={ prevLabel }
							prevValue={ secondaryValue }
							value={ primaryValue }
						/>
					);
				} )
				.filter( Boolean );

		return <SummaryList>{ renderItems }</SummaryList>;
	}
}

OverviewSummary.contextType = CurrencyContext;

OverviewSummary.defaultProps = {
	primaryData:         { data: [] },
	secondaryData:       { data: [] },
	primaryRequesting:   false,
	secondaryRequesting: false,
	primaryError:        null,
	secondaryError:      null,
};

export default compose(
	withSelect( ( select, props ) => {
		const { indicators, query } = props;

		const { getReportItems, getReportItemsError, isResolving } = select( REPORTS_STORE_NAME );
		const { woocommerce_default_date_range: defaultDateRange } = select( SETTINGS_STORE_NAME )
			.getSetting( 'wc_admin', 'wcAdminSettings' );

		const { primary, secondary } = getCurrentDates( query, defaultDateRange );
		const stats = indicators.map( ( i ) => i.stat ).join( ',' );

		const primaryQuery = {
			after:  appendTimestamp( primary.after,   'start' ),
			before: appendTimestamp( primary.before,  primary.before.isSame( new Date(), 'day' ) ? 'now' : 'end' ),
			stats,
		};

		const secondaryQuery = {
			after:  appendTimestamp( secondary.after,  'start' ),
			before: appendTimestamp( secondary.before, secondary.before.isSame( new Date(), 'day' ) ? 'now' : 'end' ),
			stats,
		};

		return {
			primaryData:         getReportItems( 'performance-indicators', primaryQuery ),
			primaryError:        getReportItemsError( 'performance-indicators', primaryQuery ) || null,
			primaryRequesting:   isResolving( 'getReportItems', [ 'performance-indicators', primaryQuery ] ),
			secondaryData:       getReportItems( 'performance-indicators', secondaryQuery ),
			secondaryError:      getReportItemsError( 'performance-indicators', secondaryQuery ) || null,
			secondaryRequesting: isResolving( 'getReportItems', [ 'performance-indicators', secondaryQuery ] ),
			defaultDateRange,
		};
	} )
)( OverviewSummary );
