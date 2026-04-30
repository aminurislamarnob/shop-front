import { __, _n } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { Link } from '@woocommerce/components';
import { formatValue } from '@woocommerce/number';
import { CurrencyContext } from '@woocommerce/currency';
import ReportTable from '../../components/report-table';

class ProductsReportTable extends Component {
	constructor() {
		super();
		this.getHeadersContent = this.getHeadersContent.bind( this );
		this.getRowsContent    = this.getRowsContent.bind( this );
		this.getSummary        = this.getSummary.bind( this );
	}

	getHeadersContent() {
		return [
			{ label: __( 'Product', 'storesuite' ),      key: 'product_name',  required: true, isLeftAligned: true },
			{ label: __( 'SKU', 'storesuite' ),           key: 'sku',           required: false },
			{ label: __( 'Items sold', 'storesuite' ),    key: 'items_sold',    required: true, defaultSort: true, isSortable: true, isNumeric: true },
			{ label: __( 'Net sales', 'storesuite' ),     key: 'net_revenue',   required: false, isSortable: true, isNumeric: true },
			{ label: __( 'Orders', 'storesuite' ),        key: 'orders_count',  required: false, isSortable: true, isNumeric: true },
			{ label: __( 'Category', 'storesuite' ),      key: 'product_cat',   required: false },
		];
	}

	getRowsContent( data = [] ) {
		const { render: renderCurrency, getCurrencyConfig } = this.context;
		const currency = getCurrencyConfig();

		return data.map( ( row ) => {
			const { product_id, extended_info = {}, items_sold, net_revenue, orders_count } = row;
			const { name = '', sku = '', category_ids = [] } = extended_info;

			return [
				{
					display: (
						<Link
							href={ ( () => {
								const p = new URLSearchParams( window.location.search );
								p.set( 'report', 'products' );
								p.set( 'filter', 'single_product' );
								p.set( 'products', product_id );
								return window.location.pathname + '?' + p.toString();
							} )() }
							type="wc-admin"
						>
							{ name }
						</Link>
					),
					value: name,
				},
				{ display: sku,                                                                 value: sku },
				{ display: formatValue( currency, 'number', items_sold ),                      value: Number( items_sold ) },
				{ display: renderCurrency( net_revenue ),                                       value: Number( net_revenue ) },
				{ display: formatValue( currency, 'number', orders_count ),                    value: Number( orders_count ) },
				{ display: category_ids.length ? `#${ category_ids[ 0 ] }` : '',              value: '' },
			];
		} );
	}

	getSummary( totals, totalResults = 0 ) {
		const { items_sold = 0, net_revenue = 0, orders_count = 0 } = totals;
		const { formatAmount, getCurrencyConfig } = this.context;
		const currency = getCurrencyConfig();
		return [
			{ label: _n( 'product', 'products', totalResults, 'storesuite' ), value: formatValue( currency, 'number', totalResults ) },
			{ label: __( 'Items sold', 'storesuite' ),                         value: formatValue( currency, 'number', items_sold ) },
			{ label: __( 'Net sales', 'storesuite' ),                          value: formatAmount( net_revenue ) },
			{ label: __( 'Orders', 'storesuite' ),                             value: formatValue( currency, 'number', orders_count ) },
		];
	}

	render() {
		const { query, filters, advancedFilters, isRequesting, baseSearchQuery, hideCompare } = this.props;
		return (
			<ReportTable
				endpoint="products"
				getHeadersContent={ this.getHeadersContent }
				getRowsContent={ this.getRowsContent }
				getSummary={ this.getSummary }
				summaryFields={ [ 'items_sold', 'net_revenue', 'orders_count' ] }
				isRequesting={ isRequesting }
				itemIdField="product_id"
				query={ query }
				searchBy="products"
				compareBy={ hideCompare ? undefined : 'products' }
				compareParam="filter"
				title={ __( 'Products', 'storesuite' ) }
				columnPrefsKey="products_report_columns"
				filters={ filters }
				advancedFilters={ advancedFilters }
				baseSearchQuery={ baseSearchQuery }
				tableQuery={ { extended_info: true } }
			/>
		);
	}
}

ProductsReportTable.contextType = CurrencyContext;

export default ProductsReportTable;
