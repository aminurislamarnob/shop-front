import { CheckboxControl, Button } from '@wordpress/components';
import { applyFilters } from '@wordpress/hooks';
import { Fragment, useRef, useState } from '@wordpress/element';
import { compose } from '@wordpress/compose';
import { focus } from '@wordpress/dom';
import { withDispatch, withSelect } from '@wordpress/data';
import { get, noop, partial, uniq } from 'lodash';
import { __, sprintf } from '@wordpress/i18n';
import PropTypes from 'prop-types';
import { CompareButton, Search, TableCard } from '@woocommerce/components';
import {
	getIdsFromQuery,
	getSearchWords,
	onQueryChange,
	updateQueryString,
} from '@woocommerce/navigation';
import {
	getReportChartData,
	getReportTableData,
	SETTINGS_STORE_NAME,
	REPORTS_STORE_NAME,
	useUserPreferences,
	QUERY_DEFAULTS,
} from '@woocommerce/data';

import ReportError from '../report-error';
import DownloadIcon from './download-icon';

const TABLE_FILTER = 'storesuite_analytics_report_table';

function toCSV( headers, rows ) {
	const visible = headers.filter( ( h ) => h.visible !== false );
	const escape  = ( v ) => `"${ String( v ?? '' ).replace( /"/g, '""' ) }"`;
	const head    = visible.map( ( h ) => escape( h.label ) ).join( ',' );
	const body    = rows.map( ( row ) =>
		visible.map( ( _h, i ) => {
			const cell = row[ i ];
			const val  = cell ? ( cell.value !== undefined ? cell.value : '' ) : '';
			return escape( val );
		} ).join( ',' )
	);
	return [ head, ...body ].join( '\n' );
}

function triggerDownload( filename, csv ) {
	const blob = new Blob( [ csv ], { type: 'text/csv;charset=utf-8;' } );
	const url  = URL.createObjectURL( blob );
	const a    = document.createElement( 'a' );
	a.href     = url;
	a.download = filename;
	document.body.appendChild( a );
	a.click();
	document.body.removeChild( a );
	URL.revokeObjectURL( url );
}

const ReportTable = ( {
	getHeadersContent,
	getRowsContent,
	getSummary,
	isRequesting,
	primaryData = {},
	tableData = { items: { data: [], totalResults: 0 }, query: {} },
	endpoint,
	ids = [],
	itemIdField,
	tableQuery = {},
	compareBy,
	compareParam = 'filter',
	searchBy,
	labels = {},
	query,
	columnPrefsKey,
	onSearch = noop,
	...tableProps
} ) => {
	const { items, query: reportQuery } = tableData;
	const initialSelectedRows = query[ compareParam ]
		? getIdsFromQuery( query[ compareBy ] )
		: [];
	const [ selectedRows, setSelectedRows ] = useState( initialSelectedRows );
	const scrollPointRef = useRef( null );
	const { updateUserPreferences, ...userData } = useUserPreferences();

	const isError = tableData.isError || ( primaryData && primaryData.isError );
	if ( isError ) {
		return <ReportError />;
	}

	let userPrefColumns = [];
	if ( columnPrefsKey ) {
		userPrefColumns =
			userData && userData[ columnPrefsKey ] ? userData[ columnPrefsKey ] : userPrefColumns;
	}

	const onPageChange = ( newPage, source ) => {
		if ( scrollPointRef.current ) {
			scrollPointRef.current.scrollIntoView();
		}
	};

	const onSort = ( key, direction ) => {
		onQueryChange( 'sort' )( key, direction );
	};

	const selectAllRows = ( checked ) => {
		setSelectedRows( checked ? ids : [] );
	};

	const selectRow = ( rowIndex, checked ) => {
		if ( checked ) {
			setSelectedRows( uniq( [ ids[ rowIndex ], ...selectedRows ] ) );
		} else {
			const index = selectedRows.indexOf( ids[ rowIndex ] );
			setSelectedRows( [
				...selectedRows.slice( 0, index ),
				...selectedRows.slice( index + 1 ),
			] );
		}
	};

	const getCheckbox = ( rowIndex ) => {
		const isSelected = -1 !== selectedRows.indexOf( ids[ rowIndex ] );
		return {
			display: (
				<CheckboxControl
					__nextHasNoMarginBottom
					onChange={ partial( selectRow, rowIndex ) }
					checked={ isSelected }
				/>
			),
			value: false,
		};
	};

	const getSelectAllHeader = () => {
		const hasRows      = ids.length > 0;
		const allSelected  = hasRows && ids.length === selectedRows.length;
		return {
			cellClassName: 'is-checkbox-column',
			key:           'compare',
			label: (
				<CheckboxControl
					__nextHasNoMarginBottom
					onChange={ selectAllRows }
					aria-label={ __( 'Select All', 'storesuite' ) }
					checked={ allSelected }
					disabled={ ! hasRows }
				/>
			),
			required: true,
		};
	};

	const filterShownHeaders = ( headers, hiddenKeys ) => {
		if ( ! hiddenKeys ) {
			return headers.map( ( header ) => ( {
				...header,
				visible: header.required || ! header.hiddenByDefault,
			} ) );
		}
		return headers.map( ( header ) => ( {
			...header,
			visible: header.required || ! hiddenKeys.includes( header.key ),
		} ) );
	};

	const applyTableFilters = ( data, totals, totalResults ) => {
		const summary = getSummary ? getSummary( totals, totalResults ) : null;
		return applyFilters( TABLE_FILTER, {
			endpoint,
			headers: getHeadersContent(),
			rows:    getRowsContent( data ),
			totals,
			summary,
			items,
		} );
	};

	const onColumnsChange = ( shownColumns, toggledColumn ) => {
		const columns       = getHeadersContent().map( ( h ) => h.key );
		const hiddenColumns = columns.filter( ( c ) => ! shownColumns.includes( c ) );
		if ( columnPrefsKey ) {
			updateUserPreferences( { [ columnPrefsKey ]: hiddenColumns } );
		}
	};

	const isLoading      = isRequesting || tableData.isRequesting || ( primaryData && primaryData.isRequesting );
	const totals         = get( primaryData, [ 'data', 'totals' ], {} );
	const totalResults   = items.totalResults || 0;
	const downloadable   = totalResults > 0;
	const searchWords    = getSearchWords( query );
	const searchedLabels = searchWords.map( ( v ) => ( { key: v, label: v } ) );
	const { data }       = items;

	const applyTableFiltersResult = applyTableFilters( data, totals, totalResults );
	let { headers, rows }         = applyTableFiltersResult;
	const { summary }             = applyTableFiltersResult;

	// When the report supports comparison, prepend a selection checkbox
	// column (header + per-row) so rows can be picked for the compare view.
	if ( compareBy ) {
		rows    = rows.map( ( row, index ) => [ getCheckbox( index ), ...row ] );
		headers = [ getSelectAllHeader(), ...headers ];
	}

	const filteredHeaders = filterShownHeaders( headers, userPrefColumns );

	const title = tableProps.title || '';

	const handleDownload = () => {
		const date     = new Date().toISOString().slice( 0, 10 );
		const filename = `${ title || 'report' }_${ date }.csv`;
		triggerDownload( filename, toCSV( filteredHeaders, rows ) );
	};

	return (
		<Fragment>
			<div
				className="woocommerce-report-table__scroll-point"
				ref={ scrollPointRef }
				aria-hidden
			/>
			<TableCard
				className="woocommerce-report-table"
				hasSearch={ !! searchBy }
				actions={ [
					compareBy && (
						<CompareButton
							key="compare"
							className="woocommerce-table__compare"
							count={ selectedRows.length }
							helpText={
								labels.helpText ||
								__( 'Check at least two items below to compare', 'storesuite' )
							}
							disabled={ ! downloadable }
							onClick={ () => {
								onQueryChange( 'compare' )(
									compareBy,
									compareParam,
									selectedRows.join( ',' )
								);
							} }
						>
							{ labels.compareButton || __( 'Compare', 'storesuite' ) }
						</CompareButton>
					),
					searchBy && (
						<Search
							allowFreeTextSearch={ true }
							inlineTags
							key="search"
							onChange={ ( values ) => {
								const searchTerms = values.map( ( v ) => v.label.replace( ',', '%2C' ) );
								if ( searchTerms.length ) {
									updateQueryString( {
										filter:             undefined,
										[ compareParam ]:   undefined,
										[ searchBy ]:       undefined,
										search:             uniq( searchTerms ).join( ',' ),
									} );
								} else {
									updateQueryString( { search: undefined } );
								}
							} }
							placeholder={ labels.placeholder || __( 'Search by item name', 'storesuite' ) }
							selected={ searchedLabels }
							showClearButton={ true }
							type={ searchBy }
							disabled={ ! downloadable }
						/>
					),
					downloadable && (
						<Button
							key="download"
							className="woocommerce-table__download-button"
							disabled={ isLoading }
							onClick={ handleDownload }
						>
							<DownloadIcon />
							<span className="woocommerce-table__download-button__label">
								{ __( 'Download', 'storesuite' ) }
							</span>
						</Button>
					),
				].filter( Boolean ) }
				headers={ filteredHeaders }
				query={ query }
				isLoading={ isLoading }
				onQueryChange={ onQueryChange }
				onColumnsChange={ onColumnsChange }
				onSort={ onSort }
				onPageChange={ onPageChange }
				rows={ rows }
				rowsPerPage={ parseInt( reportQuery.per_page, 10 ) || QUERY_DEFAULTS.pageSize }
				summary={ summary }
				totalRows={ totalResults }
				{ ...tableProps }
			/>
		</Fragment>
	);
};

const EMPTY_ARRAY  = [];
const EMPTY_OBJECT = {};

export default compose(
	withSelect( ( select, props ) => {
		const {
			endpoint,
			getSummary,
			isRequesting,
			itemIdField,
			query,
			tableData,
			tableQuery,
			filters,
			advancedFilters,
			summaryFields,
		} = props;

		const reportStoreSelector = select( REPORTS_STORE_NAME );

		const { woocommerce_default_date_range: defaultDateRange } = select(
			SETTINGS_STORE_NAME
		).getSetting( 'wc_admin', 'wcAdminSettings' );

		const noSearchResultsFound =
			query.search && ! ( query[ endpoint ] && query[ endpoint ].length );
		if ( isRequesting || noSearchResultsFound ) {
			return EMPTY_OBJECT;
		}

		const chartEndpoint = endpoint === 'categories' ? 'products' : endpoint;
		const primaryData   = getSummary
			? getReportChartData( {
				endpoint:      chartEndpoint,
				selector:      reportStoreSelector,
				dataType:      'primary',
				query,
				filters,
				advancedFilters,
				defaultDateRange,
				fields:        summaryFields,
			  } )
			: EMPTY_OBJECT;

		const queriedTableData = tableData || getReportTableData( {
			endpoint,
			query,
			selector:      reportStoreSelector,
			tableQuery,
			filters,
			advancedFilters,
			defaultDateRange,
		} );

		return {
			primaryData,
			ids: itemIdField && queriedTableData.items.data
				? queriedTableData.items.data.map( ( item ) => item[ itemIdField ] )
				: EMPTY_ARRAY,
			tableData: queriedTableData,
			query,
		};
	} )
)( ReportTable );
