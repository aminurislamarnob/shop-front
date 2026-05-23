import { __, _n } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { format as formatDate } from '@wordpress/date';
import { withSelect } from '@wordpress/data';
import { compose } from '@wordpress/compose';
import { get, memoize } from 'lodash';
import { Date, Link } from '@woocommerce/components';
import { formatValue } from '@woocommerce/number';
import {
	getReportTableQuery,
	REPORTS_STORE_NAME,
	SETTINGS_STORE_NAME,
	QUERY_DEFAULTS,
	OPTIONS_STORE_NAME,
} from '@woocommerce/data';
import {
	appendTimestamp,
	defaultTableDateFormat,
	getCurrentDates,
} from '@woocommerce/date';
import { stringify } from 'qs';
import { CurrencyContext } from '@woocommerce/currency';
import ReportTable from '../../components/report-table';
import { getAdminSetting } from '../../../utils/admin-settings';

const EMPTY_ARRAY = [];

const summaryFields = [
	'orders_count',
	'gross_sales',
	'total_sales',
	'refunds',
	'coupons',
	'taxes',
	'shipping',
	'net_revenue',
];

class RevenueReportTable extends Component {
	constructor() {
		super();
		this.getHeadersContent = this.getHeadersContent.bind( this );
		this.getRowsContent    = this.getRowsContent.bind( this );
		this.getSummary        = this.getSummary.bind( this );
	}

	getHeadersContent() {
		return [
			{ label: __( 'Date', 'storesuite' ),        key: 'date',          required: true, defaultSort: true, isLeftAligned: true, isSortable: true },
			{ label: __( 'Orders', 'storesuite' ),       key: 'orders_count',  required: false, isSortable: true, isNumeric: true },
			{ label: __( 'Gross sales', 'storesuite' ),  key: 'gross_sales',   required: false, isSortable: true, isNumeric: true },
			{ label: __( 'Returns', 'storesuite' ),      key: 'refunds',       required: false, isSortable: true, isNumeric: true },
			{ label: __( 'Coupons', 'storesuite' ),      key: 'coupons',       required: false, isSortable: true, isNumeric: true },
			{ label: __( 'Net sales', 'storesuite' ),    key: 'net_revenue',   required: false, isSortable: true, isNumeric: true },
			{ label: __( 'Taxes', 'storesuite' ),        key: 'taxes',         required: false, isSortable: true, isNumeric: true },
			{ label: __( 'Shipping', 'storesuite' ),     key: 'shipping',      required: false, isSortable: true, isNumeric: true },
			{ label: __( 'Total sales', 'storesuite' ),  key: 'total_sales',   required: false, isSortable: true, isNumeric: true },
		];
	}

	getRowsContent( data = [] ) {
		const dateFormat = getAdminSetting( 'dateFormat', defaultTableDateFormat );
		const { formatAmount, render: renderCurrency, formatDecimal: getCurrencyFormatDecimal, getCurrencyConfig } = this.context;

		return data.map( ( row ) => {
			const {
				coupons,
				gross_sales:   grossSales,
				total_sales:   totalSales,
				net_revenue:   netRevenue,
				orders_count:  ordersCount,
				refunds,
				shipping,
				taxes,
			} = row.subtotals;

			return [
				{ display: <Date date={ row.date_start } visibleFormat={ dateFormat } />, value: row.date_start },
				{ display: formatValue( getCurrencyConfig(), 'number', ordersCount ), value: Number( ordersCount ) },
				{ display: renderCurrency( grossSales ),   value: getCurrencyFormatDecimal( grossSales ) },
				{ display: formatAmount( refunds ),        value: getCurrencyFormatDecimal( refunds ) },
				{ display: formatAmount( coupons ),        value: getCurrencyFormatDecimal( coupons ) },
				{ display: renderCurrency( netRevenue ),   value: getCurrencyFormatDecimal( netRevenue ) },
				{ display: renderCurrency( taxes ),        value: getCurrencyFormatDecimal( taxes ) },
				{ display: renderCurrency( shipping ),     value: getCurrencyFormatDecimal( shipping ) },
				{ display: renderCurrency( totalSales ),   value: getCurrencyFormatDecimal( totalSales ) },
			];
		} );
	}

	getSummary( totals, totalResults = 0 ) {
		const {
			orders_count:  ordersCount = 0,
			gross_sales:   grossSales  = 0,
			total_sales:   totalSales  = 0,
			refunds = 0,
			coupons = 0,
			taxes   = 0,
			shipping = 0,
			net_revenue: netRevenue = 0,
		} = totals;
		const { formatAmount, getCurrencyConfig } = this.context;
		const currency = getCurrencyConfig();
		return [
			{ label: _n( 'day', 'days', totalResults, 'storesuite' ),         value: formatValue( currency, 'number', totalResults ) },
			{ label: _n( 'order', 'orders', ordersCount, 'storesuite' ),      value: formatValue( currency, 'number', ordersCount ) },
			{ label: __( 'Gross sales', 'storesuite' ),                        value: formatAmount( grossSales ) },
			{ label: __( 'Returns', 'storesuite' ),                            value: formatAmount( refunds ) },
			{ label: __( 'Coupons', 'storesuite' ),                            value: formatAmount( coupons ) },
			{ label: __( 'Net sales', 'storesuite' ),                          value: formatAmount( netRevenue ) },
			{ label: __( 'Taxes', 'storesuite' ),                              value: formatAmount( taxes ) },
			{ label: __( 'Shipping', 'storesuite' ),                           value: formatAmount( shipping ) },
			{ label: __( 'Total sales', 'storesuite' ),                        value: formatAmount( totalSales ) },
		];
	}

	render() {
		const { advancedFilters, filters, tableData, query } = this.props;
		return (
			<ReportTable
				endpoint="revenue"
				getHeadersContent={ this.getHeadersContent }
				getRowsContent={ this.getRowsContent }
				getSummary={ this.getSummary }
				summaryFields={ summaryFields }
				query={ query }
				tableData={ tableData }
				title={ __( 'Revenue', 'storesuite' ) }
				columnPrefsKey="revenue_report_columns"
				filters={ filters }
				advancedFilters={ advancedFilters }
			/>
		);
	}
}

RevenueReportTable.contextType = CurrencyContext;

const formatTableQuery = memoize(
	( order, orderBy, page, pageSize, datesFromQuery ) => ( {
		interval: 'day',
		orderby:  orderBy,
		order,
		page,
		per_page: pageSize,
		after:    appendTimestamp( datesFromQuery.primary.after, 'start' ),
		before:   appendTimestamp( datesFromQuery.primary.before, 'end' ),
	} ),
	( order, orderBy, page, pageSize, datesFromQuery ) =>
		[ order, orderBy, page, pageSize, datesFromQuery.primary.after, datesFromQuery.primary.before ].join( ':' )
);

export default compose(
	withSelect( ( select, props ) => {
		const { query, filters, advancedFilters } = props;
		const { woocommerce_default_date_range: defaultDateRange } = select(
			SETTINGS_STORE_NAME
		).getSetting( 'wc_admin', 'wcAdminSettings' );
		const datesFromQuery = getCurrentDates( query, defaultDateRange );
		const { getReportStats, getReportStatsError, isResolving } = select( REPORTS_STORE_NAME );

		const tableQuery = formatTableQuery(
			query.order   || 'desc',
			query.orderby || 'date',
			query.paged   || 1,
			query.per_page || QUERY_DEFAULTS.pageSize,
			datesFromQuery
		);
		const filteredTableQuery = getReportTableQuery( {
			endpoint: 'revenue',
			query,
			select,
			tableQuery,
			filters,
			advancedFilters,
		} );
		const revenueData    = getReportStats( 'revenue', filteredTableQuery );
		const isError        = Boolean( getReportStatsError( 'revenue', filteredTableQuery ) );
		const isRequesting   = isResolving( 'getReportStats', [ 'revenue', filteredTableQuery ] );

		return {
			tableData: {
				items: {
					data:         get( revenueData, [ 'data', 'intervals' ], EMPTY_ARRAY ),
					totalResults: get( revenueData, [ 'totalResults' ], 0 ),
				},
				isError,
				isRequesting,
				query: tableQuery,
			},
		};
	} )
)( RevenueReportTable );
