import { find, get } from 'lodash';
import { flattenFilters } from '@woocommerce/navigation';
import { format as formatDate } from '@wordpress/date';
import { getPreviousDate, containsLeapYear, isLeapYear } from '@woocommerce/date';

export const DEFAULT_FILTER = 'all';

export function getSelectedFilter( filters, query, selectedFilterArgs = {} ) {
	if ( ! filters || filters.length === 0 ) {
		return null;
	}

	const clonedFilters = filters.slice( 0 );
	const filterConfig  = clonedFilters.pop();

	if ( filterConfig.showFilters( query, selectedFilterArgs ) ) {
		const allFilters = flattenFilters( filterConfig.filters );
		const value      =
			query[ filterConfig.param ] || filterConfig.defaultValue || DEFAULT_FILTER;
		return find( allFilters, { value } );
	}

	return getSelectedFilter( clonedFilters, query, selectedFilterArgs );
}

export function getChartMode( selectedFilter, query ) {
	if ( selectedFilter && query ) {
		const selectedFilterParam = get( selectedFilter, [ 'settings', 'param' ] );
		if ( ! selectedFilterParam || Object.keys( query ).includes( selectedFilterParam ) ) {
			return get( selectedFilter, [ 'chartMode' ] );
		}
	}
	return null;
}

export function createDateFormatter( format ) {
	return ( date ) => formatDate( format, date );
}

export function buildChartData(
	primaryData,
	secondaryData,
	primary,
	secondary,
	compare,
	selectedKey,
	currentInterval
) {
	if (
		! primaryData ||
		! primaryData.data ||
		! primaryData.data.intervals
	) {
		return [];
	}

	return primaryData.data.intervals.map( ( interval, i ) => {
		const secondaryInterval =
			secondaryData &&
			secondaryData.data &&
			secondaryData.data.intervals &&
			secondaryData.data.intervals[ i ];

		const primaryLabel   = primary.range ? `${ primary.label } (${ primary.range })` : primary.label;
		const secondaryLabel = secondary && secondary.range ? `${ secondary.label } (${ secondary.range })` : ( secondary ? secondary.label : '' );

		const entry = {
			date: formatDate( "Y-m-d\\TH:i:s", interval.date_start ),
			primary: {
				label:     primaryLabel,
				labelDate: interval.date_start,
				value:     get( interval, [ 'subtotals', selectedKey ], 0 ),
			},
		};

		if ( secondaryInterval ) {
			entry.secondary = {
				label:     secondaryLabel,
				labelDate: secondaryInterval.date_start,
				value:     get( secondaryInterval, [ 'subtotals', selectedKey ], 0 ),
			};
		}

		return entry;
	} );
}
