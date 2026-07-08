import { __, _n } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { Link } from '@woocommerce/components';
import { formatValue } from '@woocommerce/number';
import { CurrencyContext } from '@woocommerce/currency';
import ReportTable from '../../components/report-table';
import { getAdminSetting } from '../../../utils/admin-settings';

class StockReportTable extends Component {
	constructor() {
		super();
		this.getHeadersContent = this.getHeadersContent.bind( this );
		this.getRowsContent    = this.getRowsContent.bind( this );
		this.getSummary        = this.getSummary.bind( this );
	}

	getHeadersContent() {
		return [
			{ label: __( 'Product / Variation', 'storesuite' ), key: 'product_name', required: true, isLeftAligned: true },
			{ label: __( 'SKU', 'storesuite' ),                  key: 'sku',          required: false },
			{ label: __( 'Status', 'storesuite' ),               key: 'stock_status', required: true },
			{ label: __( 'Stock', 'storesuite' ),                key: 'stock_quantity', required: false, isSortable: true, isNumeric: true, defaultSort: true },
		];
	}

	getRowsContent( data = [] ) {
		const { getCurrencyConfig } = this.context;
		const currency = getCurrencyConfig();
		const stockStatuses = getAdminSetting( 'stockStatuses', {} );

		return data.map( ( row ) => {
			const { product_id, name = '', sku = '', stock_status = '', stock_quantity = 0, manage_stock = false } = row;
			const statusLabel = stockStatuses[ stock_status ] || stock_status;

			return [
				{
					display: (
						<Link href={ `post.php?post=${ product_id }&action=edit` } type="external">
							{ name }
						</Link>
					),
					value: name,
				},
				{ display: sku,                                                         value: sku },
				{ display: statusLabel,                                                  value: stock_status },
				{
					display: manage_stock
						? formatValue( currency, 'number', stock_quantity )
						: __( 'N/A', 'storesuite' ),
					value: manage_stock ? Number( stock_quantity ) : null,
				},
			];
		} );
	}

	getSummary( totals ) {
		const {
			products     = 0,
			outofstock   = 0,
			lowstock     = 0,
			onbackorder  = 0,
			instock      = 0,
		} = totals;
		const currency = this.context.getCurrencyConfig();
		return [
			{ label: _n( 'Product', 'Products', products, 'storesuite' ),  value: formatValue( currency, 'number', products ) },
			{ label: __( 'Out of stock', 'storesuite' ),                    value: formatValue( currency, 'number', outofstock ) },
			{ label: __( 'Low stock', 'storesuite' ),                       value: formatValue( currency, 'number', lowstock ) },
			{ label: __( 'On backorder', 'storesuite' ),                    value: formatValue( currency, 'number', onbackorder ) },
			{ label: __( 'In stock', 'storesuite' ),                        value: formatValue( currency, 'number', instock ) },
		];
	}

	render() {
		const { query, filters, advancedFilters, isRequesting } = this.props;
		return (
			<ReportTable
				endpoint="stock"
				getHeadersContent={ this.getHeadersContent }
				getRowsContent={ this.getRowsContent }
				getSummary={ this.getSummary }
				summaryFields={ [ 'products', 'outofstock', 'lowstock', 'onbackorder', 'instock' ] }
				isRequesting={ isRequesting }
				itemIdField="product_id"
				query={ query }
				title={ __( 'Stock', 'storesuite' ) }
				columnPrefsKey="stock_report_columns"
				filters={ filters }
				advancedFilters={ advancedFilters }
				tableQuery={ {
					orderby: [ 'stock_status', 'stock_quantity', 'date', 'id', 'title', 'sku' ].includes( query.orderby ) ? query.orderby : 'stock_quantity',
					order:   query.order || 'asc',
					type:    query.type || 'all',
				} }
			/>
		);
	}
}

StockReportTable.contextType = CurrencyContext;

export default StockReportTable;
