import { __, _n } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { map } from 'lodash';
import { Date, Link } from '@woocommerce/components';
import { formatValue } from '@woocommerce/number';
import { defaultTableDateFormat } from '@woocommerce/date';
import { CurrencyContext } from '@woocommerce/currency';
import ReportTable from '../../components/report-table';
import { getAdminSetting } from '../../../utils/admin-settings';

class DownloadsReportTable extends Component {
	constructor() {
		super();
		this.getHeadersContent = this.getHeadersContent.bind( this );
		this.getRowsContent    = this.getRowsContent.bind( this );
		this.getSummary        = this.getSummary.bind( this );
	}

	getHeadersContent() {
		return [
			{ label: __( 'Date', 'storesuite' ),       key: 'date',       required: true, defaultSort: true, isLeftAligned: true, isSortable: true },
			{ label: __( 'Product', 'storesuite' ),    key: 'product',    isSortable: true, required: true },
			{ label: __( 'File name', 'storesuite' ),  key: 'file_name' },
			{ label: __( 'Order #', 'storesuite' ),    key: 'order_number' },
			{ label: __( 'Username', 'storesuite' ),   key: 'username' },
			{ label: __( 'IP address', 'storesuite' ), key: 'ip_address' },
		];
	}

	getRowsContent( downloads ) {
		const dateFormat = getAdminSetting( 'dateFormat', defaultTableDateFormat );

		return map( downloads, ( download ) => {
			const { date, file_name: fileName, ip_address: ipAddress, order_id: orderId, order_number: orderNumber, product_id: productId, username } = download;
			const embeddedProduct = ( download._embedded && download._embedded.product && download._embedded.product[ 0 ] ) || {};
			const productName     = embeddedProduct.name || '';

			return [
				{ display: <Date date={ date } visibleFormat={ dateFormat } />, value: date },
				{
					display: (
						<Link href={ `post.php?post=${ productId }&action=edit` } type="external">
							{ productName }
						</Link>
					),
					value: productName,
				},
				{ display: fileName,  value: fileName },
				{
					display: (
						<Link href={ `post.php?post=${ orderId }&action=edit` } type="external">
							{ orderNumber }
						</Link>
					),
					value: orderNumber,
				},
				{ display: username,  value: username },
				{ display: ipAddress, value: ipAddress },
			];
		} );
	}

	getSummary( totals, totalResults = 0 ) {
		const { getCurrencyConfig } = this.context;
		const currency = getCurrencyConfig();
		return [
			{
				label: _n( 'download', 'downloads', totalResults, 'storesuite' ),
				value: formatValue( currency, 'number', totalResults ),
			},
		];
	}

	render() {
		const { advancedFilters, filters, isRequesting, query } = this.props;
		return (
			<ReportTable
				endpoint="downloads"
				getHeadersContent={ this.getHeadersContent }
				getRowsContent={ this.getRowsContent }
				getSummary={ this.getSummary }
				summaryFields={ [ 'download_count' ] }
				isRequesting={ isRequesting }
				itemIdField="download_id"
				query={ query }
				title={ __( 'Downloads', 'storesuite' ) }
				columnPrefsKey="downloads_report_columns"
				filters={ filters }
				advancedFilters={ advancedFilters }
				tableQuery={ {
					_embed:  true,
					orderby: [ 'date', 'product' ].includes( query.orderby ) ? query.orderby : 'date',
				} }
			/>
		);
	}
}

DownloadsReportTable.contextType = CurrencyContext;

export default DownloadsReportTable;
