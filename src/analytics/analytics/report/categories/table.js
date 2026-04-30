import { __, _n } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { formatValue } from '@woocommerce/number';
import { CurrencyContext } from '@woocommerce/currency';
import ReportTable from '../../components/report-table';

class CategoriesReportTable extends Component {
	constructor() {
		super();
		this.getHeadersContent = this.getHeadersContent.bind( this );
		this.getRowsContent    = this.getRowsContent.bind( this );
		this.getSummary        = this.getSummary.bind( this );
	}

	getHeadersContent() {
		return [
			{ label: __( 'Category', 'storesuite' ),     key: 'category',      required: true, isLeftAligned: true },
			{ label: __( 'Items sold', 'storesuite' ),   key: 'items_sold',    required: true, defaultSort: true, isSortable: true, isNumeric: true },
			{ label: __( 'Net sales', 'storesuite' ),    key: 'net_revenue',   required: false, isSortable: true, isNumeric: true },
			{ label: __( 'Orders', 'storesuite' ),       key: 'orders_count',  required: false, isSortable: true, isNumeric: true },
			{ label: __( 'Products', 'storesuite' ),     key: 'products_count', required: false, isNumeric: true },
		];
	}

	getRowsContent( data = [] ) {
		const { render: renderCurrency, getCurrencyConfig } = this.context;
		const currency = getCurrencyConfig();

		return data.map( ( row ) => {
			const { extended_info = {}, items_sold, net_revenue, orders_count, products_count } = row;
			const { name = '' } = extended_info;

			return [
				{ display: name,                                                               value: name },
				{ display: formatValue( currency, 'number', items_sold ),                     value: Number( items_sold ) },
				{ display: renderCurrency( net_revenue ),                                      value: Number( net_revenue ) },
				{ display: formatValue( currency, 'number', orders_count ),                   value: Number( orders_count ) },
				{ display: formatValue( currency, 'number', products_count ),                 value: Number( products_count ) },
			];
		} );
	}

	getSummary( totals, totalResults = 0 ) {
		const { items_sold = 0, net_revenue = 0, orders_count = 0 } = totals;
		const { formatAmount, getCurrencyConfig } = this.context;
		const currency = getCurrencyConfig();
		return [
			{ label: _n( 'category', 'categories', totalResults, 'storesuite' ), value: formatValue( currency, 'number', totalResults ) },
			{ label: __( 'Items sold', 'storesuite' ),                            value: formatValue( currency, 'number', items_sold ) },
			{ label: __( 'Net sales', 'storesuite' ),                             value: formatAmount( net_revenue ) },
			{ label: __( 'Orders', 'storesuite' ),                                value: formatValue( currency, 'number', orders_count ) },
		];
	}

	render() {
		const { query, filters, advancedFilters, isRequesting } = this.props;
		return (
			<ReportTable
				endpoint="categories"
				getHeadersContent={ this.getHeadersContent }
				getRowsContent={ this.getRowsContent }
				getSummary={ this.getSummary }
				summaryFields={ [ 'items_sold', 'net_revenue', 'orders_count' ] }
				isRequesting={ isRequesting }
				itemIdField="category_id"
				query={ query }
				searchBy="categories"
				compareBy="categories"
				compareParam="filter"
				title={ __( 'Categories', 'storesuite' ) }
				columnPrefsKey="categories_report_columns"
				filters={ filters }
				advancedFilters={ advancedFilters }
				tableQuery={ { orderby: query.orderby || 'items_sold', order: query.order || 'desc', extended_info: true } }
			/>
		);
	}
}

CategoriesReportTable.contextType = CurrencyContext;

export default CategoriesReportTable;
