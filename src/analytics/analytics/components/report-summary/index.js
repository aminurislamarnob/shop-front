import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { compose } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';
import PropTypes from 'prop-types';
import { getNewPath } from '@woocommerce/navigation';
import { SummaryListPlaceholder, SummaryNumber } from '@woocommerce/components';
import { calculateDelta, formatValue } from '@woocommerce/number';
import { getSummaryNumbers } from '@woocommerce/data';
import { getDateParamsFromQuery } from '@woocommerce/date';
import { CurrencyContext } from '@woocommerce/currency';
import ReportError from '../report-error';
import { getDefaultDateRange } from '../../../utils/date';

export class ReportSummary extends Component {
	formatVal( val, type ) {
		const { formatAmount, getCurrencyConfig } = this.context;
		return type === 'currency'
			? formatAmount( val )
			: formatValue( getCurrencyConfig(), type, val );
	}

	getValues( key, type ) {
		const { emptySearchResults, summaryData } = this.props;
		const { totals } = summaryData;
		const primaryTotal   = totals.primary   ? totals.primary[ key ]   : 0;
		const secondaryTotal = totals.secondary ? totals.secondary[ key ] : 0;
		const primaryValue   = emptySearchResults ? 0 : primaryTotal;
		const secondaryValue = emptySearchResults ? 0 : secondaryTotal;
		return {
			delta:      calculateDelta( primaryValue, secondaryValue ),
			prevValue:  this.formatVal( secondaryValue, type ),
			value:      this.formatVal( primaryValue, type ),
		};
	}

	render() {
		const { charts, query, selectedChart, summaryData, endpoint, report, defaultDateRange } = this.props;
		const { isError, isRequesting } = summaryData;

		if ( isError ) {
			return <ReportError />;
		}
		if ( isRequesting ) {
			return <SummaryListPlaceholder numberOfItems={ charts.length } />;
		}

		const { compare } = getDateParamsFromQuery( query, defaultDateRange );

		const renderSummaryNumbers = () =>
			charts.map( ( chart ) => {
				const { key, order, orderby, label, type, isReverseTrend, labelTooltipText } = chart;
				const newPath = { chart: key };
				if ( orderby ) {
					newPath.orderby = orderby;
				}
				if ( order ) {
					newPath.order = order;
				}
				const href       = getNewPath( newPath );
				const isSelected = selectedChart.key === key;
				const { delta, prevValue, value } = this.getValues( key, type );

				return (
					<SummaryNumber
						key={ key }
						delta={ delta }
						href={ href }
						label={ label }
						reverseTrend={ isReverseTrend }
						prevLabel={
							compare === 'previous_period'
								? __( 'Previous period:', 'storesuite' )
								: __( 'Previous year:', 'storesuite' )
						}
						prevValue={ prevValue }
						selected={ isSelected }
						value={ value }
						labelTooltipText={ labelTooltipText }
					/>
				);
			} );

		// Render the same markup Woo's <SummaryList> produces in its list mode,
		// rather than <SummaryList> itself. SummaryList collapses into a
		// single-item dropdown on mobile when there are 10 or fewer numbers
		// (which every per-report summary is), whereas the Overview (15
		// indicators) stays a list. Rendering the list directly keeps every
		// report's summary stacked on mobile. The `has-N-items` class is what
		// drives the desktop grid columns, so it must be included to keep the
		// desktop row layout (without it the grid falls back to one full-width
		// column). Woo caps the count at 10.
		const items = renderSummaryNumbers();
		const itemCount = Math.min( items.length, 10 );

		return (
			<ul className={ `woocommerce-summary has-${ itemCount }-items` }>
				{ items }
			</ul>
		);
	}
}

ReportSummary.defaultProps = {
	summaryData: {
		totals:      { primary: {}, secondary: {} },
		isError:     false,
		isRequesting: false,
	},
};

ReportSummary.contextType = CurrencyContext;

export default compose(
	withSelect( ( select, props ) => {
		const {
			charts,
			endpoint,
			limitProperties,
			query,
			filters,
			advancedFilters,
		} = props;
		const limitBy = limitProperties || [ endpoint ];

		const hasLimitByParam = limitBy.some(
			( item ) => query[ item ] && query[ item ].length
		);
		if ( query.search && ! hasLimitByParam ) {
			return { emptySearchResults: true };
		}

		const fields = charts && charts.map( ( chart ) => chart.key );

		const defaultDateRange = getDefaultDateRange( select );

		const summaryData = getSummaryNumbers( {
			endpoint,
			query,
			select,
			limitBy,
			filters,
			advancedFilters,
			defaultDateRange,
			fields,
		} );

		return { summaryData, defaultDateRange };
	} )
)( ReportSummary );
