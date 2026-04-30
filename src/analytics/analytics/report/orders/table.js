import { __, _n, sprintf } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { map } from 'lodash';
import { Date, Link, OrderStatus } from '@woocommerce/components';
import { formatValue } from '@woocommerce/number';
import { getNewPath, getPersistedQuery } from '@woocommerce/navigation';
import { defaultTableDateFormat } from '@woocommerce/date';
import { CurrencyContext } from '@woocommerce/currency';
import ReportTable from '../../components/report-table';
import { getAdminSetting } from '../../../utils/admin-settings';

class OrdersReportTable extends Component {
	constructor() {
		super();
		this.getHeadersContent = this.getHeadersContent.bind( this );
		this.getRowsContent    = this.getRowsContent.bind( this );
		this.getSummary        = this.getSummary.bind( this );
	}

	getHeadersContent() {
		return [
			{ label: __( 'Date', 'storesuite' ),          key: 'date',           required: true, defaultSort: true, isLeftAligned: true, isSortable: true },
			{ label: __( 'Order #', 'storesuite' ),        key: 'order_number',   required: true },
			{ label: __( 'Status', 'storesuite' ),         key: 'status',         required: false },
			{ label: __( 'Customer', 'storesuite' ),       key: 'customer_id',    required: false },
			{ label: __( 'Product(s)', 'storesuite' ),     key: 'products',       required: false },
			{ label: __( 'Items sold', 'storesuite' ),     key: 'num_items_sold', required: false, isSortable: true, isNumeric: true },
			{ label: __( 'Net sales', 'storesuite' ),      key: 'net_total',      required: true, isSortable: true, isNumeric: true },
		];
	}

	getRowsContent( tableData ) {
		const { query } = this.props;
		const persistedQuery = getPersistedQuery( query );
		const dateFormat     = getAdminSetting( 'dateFormat', defaultTableDateFormat );
		const { render: renderCurrency, getCurrencyConfig } = this.context;

		return map( tableData, ( row ) => {
			const {
				date,
				net_total:     netTotal,
				num_items_sold: numItemsSold,
				order_id:      orderId,
				order_number:  orderNumber,
				status,
			} = row;

			const extendedInfo = row.extended_info || {};
			const { customer, products = [] } = extendedInfo;
			const customerName = customer
				? [ customer.first_name, customer.last_name ].filter( Boolean ).join( ' ' )
				: '';
			const productNames = products.slice( 0, 2 ).map( ( p ) => p.name ).join( ', ' );

			return [
				{ display: <Date date={ date } visibleFormat={ dateFormat } />, value: date },
				{ display: <Link href={ `post.php?post=${ orderId }&action=edit` } type="external">{ orderNumber }</Link>, value: orderNumber },
				{
					display: (
						<OrderStatus
							className="woocommerce-orders-table__status"
							order={ { status } }
							labelPositionToLeft={ true }
							orderStatusMap={ getAdminSetting( 'orderStatuses', {} ) }
						/>
					),
					value: status,
				},
				{ display: customerName, value: customerName },
				{ display: productNames, value: productNames },
				{ display: formatValue( getCurrencyConfig(), 'number', numItemsSold ), value: Number( numItemsSold ) },
				{ display: renderCurrency( netTotal ), value: Number( netTotal ) },
			];
		} );
	}

	getSummary( totals, totalResults = 0 ) {
		const {
			orders_count:       ordersCount      = 0,
			net_revenue:        netRevenue        = 0,
			avg_order_value:    avgOrderValue     = 0,
			avg_items_per_order: avgItemsPerOrder = 0,
		} = totals;
		const { formatAmount, getCurrencyConfig } = this.context;
		const currency = getCurrencyConfig();
		return [
			{ label: _n( 'order', 'orders', ordersCount, 'storesuite' ), value: formatValue( currency, 'number', ordersCount ) },
			{ label: __( 'Net sales', 'storesuite' ),                     value: formatAmount( netRevenue ) },
			{ label: __( 'Avg. order value', 'storesuite' ),              value: formatAmount( avgOrderValue ) },
			{ label: __( 'Avg. items / order', 'storesuite' ),            value: formatValue( currency, 'average', avgItemsPerOrder ) },
		];
	}

	render() {
		const { advancedFilters, filters, query } = this.props;
		return (
			<ReportTable
				endpoint="orders"
				getHeadersContent={ this.getHeadersContent }
				getRowsContent={ this.getRowsContent }
				getSummary={ this.getSummary }
				query={ query }
				title={ __( 'Orders', 'storesuite' ) }
				columnPrefsKey="orders_report_columns"
				filters={ filters }
				advancedFilters={ advancedFilters }
				tableQuery={ {
					extended_info: true,
					orderby: [ 'date', 'num_items_sold', 'net_total' ].includes( query.orderby ) ? query.orderby : 'date',
				} }
			/>
		);
	}
}

OrdersReportTable.contextType = CurrencyContext;

export default OrdersReportTable;
