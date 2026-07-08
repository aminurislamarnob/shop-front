import { __, _n } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { map } from 'lodash';
import { formatValue } from '@woocommerce/number';
import { CurrencyContext } from '@woocommerce/currency';
import ReportTable from '../../components/report-table';

/**
 * Builds a display code for a tax rate, matching wc-admin's getTaxCode():
 * COUNTRY-STATE-NAME-PRIORITY.
 */
function getTaxCode( tax ) {
	return [ tax.country, tax.state, tax.name || __( 'TAX', 'storesuite' ), tax.priority ]
		.filter( Boolean )
		.map( ( item ) => item.toString().toUpperCase().trim() )
		.join( '-' );
}

class TaxesReportTable extends Component {
	constructor() {
		super();
		this.getHeadersContent = this.getHeadersContent.bind( this );
		this.getRowsContent    = this.getRowsContent.bind( this );
		this.getSummary        = this.getSummary.bind( this );
	}

	getHeadersContent() {
		return [
			{ label: __( 'Tax code', 'storesuite' ),     key: 'tax_code',     required: true, isLeftAligned: true, isSortable: true },
			{ label: __( 'Rate', 'storesuite' ),          key: 'rate',         isSortable: true, isNumeric: true },
			{ label: __( 'Total tax', 'storesuite' ),     key: 'total_tax',    required: true, defaultSort: true, isSortable: true, isNumeric: true },
			{ label: __( 'Order tax', 'storesuite' ),     key: 'order_tax',    isSortable: true, isNumeric: true },
			{ label: __( 'Shipping tax', 'storesuite' ),  key: 'shipping_tax', isSortable: true, isNumeric: true },
			{ label: __( 'Orders', 'storesuite' ),        key: 'orders_count', required: true, isSortable: true, isNumeric: true },
		];
	}

	getRowsContent( taxes ) {
		const { render: renderCurrency, getCurrencyConfig } = this.context;
		const currency = getCurrencyConfig();

		return map( taxes, ( tax ) => {
			const {
				order_tax:    orderTax,
				orders_count: ordersCount,
				tax_rate:     taxRate,
				total_tax:    totalTax,
				shipping_tax: shippingTax,
			} = tax;
			const taxCode = getTaxCode( tax );

			return [
				{ display: taxCode,                                                     value: taxCode },
				{ display: `${ Number( taxRate ).toFixed( 2 ) }%`,                     value: Number( taxRate ) },
				{ display: renderCurrency( totalTax ),                                  value: Number( totalTax ) },
				{ display: renderCurrency( orderTax ),                                  value: Number( orderTax ) },
				{ display: renderCurrency( shippingTax ),                               value: Number( shippingTax ) },
				{ display: formatValue( currency, 'number', ordersCount ),              value: Number( ordersCount ) },
			];
		} );
	}

	getSummary( totals, totalResults = 0 ) {
		const {
			total_tax:    totalTax    = 0,
			order_tax:    orderTax    = 0,
			shipping_tax: shippingTax = 0,
			orders_count: ordersCount = 0,
		} = totals;
		const { formatAmount, getCurrencyConfig } = this.context;
		const currency = getCurrencyConfig();
		return [
			{ label: _n( 'tax code', 'tax codes', totalResults, 'storesuite' ), value: formatValue( currency, 'number', totalResults ) },
			{ label: __( 'total tax', 'storesuite' ),                            value: formatAmount( totalTax ) },
			{ label: __( 'order tax', 'storesuite' ),                            value: formatAmount( orderTax ) },
			{ label: __( 'shipping tax', 'storesuite' ),                         value: formatAmount( shippingTax ) },
			{ label: _n( 'order', 'orders', ordersCount, 'storesuite' ),         value: formatValue( currency, 'number', ordersCount ) },
		];
	}

	render() {
		const { advancedFilters, filters, isRequesting, query } = this.props;
		return (
			<ReportTable
				compareBy="taxes"
				endpoint="taxes"
				getHeadersContent={ this.getHeadersContent }
				getRowsContent={ this.getRowsContent }
				getSummary={ this.getSummary }
				summaryFields={ [ 'total_tax', 'order_tax', 'shipping_tax', 'orders_count' ] }
				isRequesting={ isRequesting }
				itemIdField="tax_rate_id"
				query={ query }
				searchBy="taxes"
				title={ __( 'Taxes', 'storesuite' ) }
				columnPrefsKey="taxes_report_columns"
				filters={ filters }
				advancedFilters={ advancedFilters }
				tableQuery={ {
					orderby: [ 'tax_code', 'rate', 'total_tax', 'order_tax', 'shipping_tax', 'orders_count' ].includes( query.orderby ) ? query.orderby : 'total_tax',
				} }
			/>
		);
	}
}

TaxesReportTable.contextType = CurrencyContext;

export default TaxesReportTable;
