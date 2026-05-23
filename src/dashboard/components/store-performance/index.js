import { Component } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { compose } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';
import { find } from 'lodash';
import {
	BanknotesIcon,
	ShoppingCartIcon,
	ReceiptPercentIcon,
	ArrowUturnLeftIcon,
	TagIcon,
	CubeIcon,
} from '@heroicons/react/24/outline';
import {
	SummaryList,
	SummaryListPlaceholder,
	SummaryNumber,
} from '@woocommerce/components';
import { calculateDelta, formatValue } from '@woocommerce/number';
import { REPORTS_STORE_NAME } from '@woocommerce/data';
import { getCurrentDates, appendTimestamp, getDateParamsFromQuery } from '@woocommerce/date';
import { CurrencyContext } from '@woocommerce/currency';
import { storeSuiteDashboard } from '../../config';
import { DASHBOARD_DEFAULT_DATE_RANGE } from '../../constants';

const ICON_MAP = {
	'banknotes':        BanknotesIcon,
	'shopping-cart':    ShoppingCartIcon,
	'receipt-percent':  ReceiptPercentIcon,
	'arrow-uturn-left': ArrowUturnLeftIcon,
	'tag':              TagIcon,
	'cube':             CubeIcon,
};

function StatLabel( { icon, color, label } ) {
	const Icon = ICON_MAP[ icon ];
	return (
		<span className="storesuite-stat-label">
			{ Icon && (
				<span className={ `storesuite-stat-icon storesuite-stat-icon--${ color }` }>
					<Icon />
				</span>
			) }
			{ label }
		</span>
	);
}

class StorePerformance extends Component {
	formatVal( value, format ) {
		const { formatAmount, getCurrencyConfig } = this.context;
		return format === 'currency'
			? formatAmount( value )
			: formatValue( getCurrencyConfig(), format, value );
	}

	hrefForIndicator( indicator, query ) {
		if ( ! indicator.linkedReport || ! storeSuiteDashboard.reportsPath ) {
			return '';
		}

		const params = new URLSearchParams();
		params.set( 'report', indicator.linkedReport );
		if ( indicator.key ) {
			params.set( 'chart', indicator.key );
		}
		[ 'period', 'compare', 'after', 'before' ].forEach( ( k ) => {
			if ( query?.[ k ] ) {
				params.set( k, query[ k ] );
			}
		} );

		return storeSuiteDashboard.reportsPath + '?' + params.toString();
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
		} = this.props;

		if ( primaryRequesting || secondaryRequesting ) {
			return <SummaryListPlaceholder numberOfItems={ indicators.length } />;
		}

		if ( primaryError || secondaryError ) {
			return (
				<div className="storesuite-dashboard-empty-notice">
					{ __( 'There was an error loading store performance.', 'storesuite' ) }
				</div>
			);
		}

		const { compare } = getDateParamsFromQuery( query, DASHBOARD_DEFAULT_DATE_RANGE );
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
					const href           = this.hrefForIndicator( indicator, query );

					return (
						<SummaryNumber
							key={ indicator.stat }
							delta={ delta }
							href={ href }
							label={
								<StatLabel
									icon={ indicator.icon }
									color={ indicator.color }
									label={ indicator.label }
								/>
							}
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

StorePerformance.contextType = CurrencyContext;

StorePerformance.defaultProps = {
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

		const { primary, secondary } = getCurrentDates( query, DASHBOARD_DEFAULT_DATE_RANGE );
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
		};
	} )
)( StorePerformance );
