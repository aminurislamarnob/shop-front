import { __, _n } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { map } from 'lodash';
import { Date, Link } from '@woocommerce/components';
import { formatValue } from '@woocommerce/number';
import { defaultTableDateFormat } from '@woocommerce/date';
import { CurrencyContext } from '@woocommerce/currency';
import ReportTable from '../../components/report-table';
import { getAdminSetting } from '../../../utils/admin-settings';

class CouponsReportTable extends Component {
	constructor() {
		super();
		this.getHeadersContent = this.getHeadersContent.bind( this );
		this.getRowsContent    = this.getRowsContent.bind( this );
		this.getSummary        = this.getSummary.bind( this );
	}

	getHeadersContent() {
		return [
			{ label: __( 'Coupon code', 'storesuite' ),       key: 'code',         required: true, isLeftAligned: true },
			{ label: __( 'Orders', 'storesuite' ),            key: 'orders_count', required: true, defaultSort: true, isSortable: true, isNumeric: true },
			{ label: __( 'Amount discounted', 'storesuite' ), key: 'amount',       isSortable: true, isNumeric: true },
			{ label: __( 'Created', 'storesuite' ),           key: 'created' },
			{ label: __( 'Expires', 'storesuite' ),           key: 'expires' },
			{ label: __( 'Type', 'storesuite' ),              key: 'type' },
		];
	}

	getCouponType( discountType ) {
		const types = {
			percent:       __( 'Percentage', 'storesuite' ),
			fixed_cart:    __( 'Fixed cart', 'storesuite' ),
			fixed_product: __( 'Fixed product', 'storesuite' ),
		};
		return types[ discountType ] || __( 'N/A', 'storesuite' );
	}

	getRowsContent( coupons ) {
		const dateFormat = getAdminSetting( 'dateFormat', defaultTableDateFormat );
		const { render: renderCurrency, getCurrencyConfig } = this.context;

		return map( coupons, ( coupon ) => {
			const { amount, coupon_id: couponId, orders_count: ordersCount } = coupon;
			const extendedInfo = coupon.extended_info || {};
			const {
				code          = '',
				date_created:  dateCreated,
				date_expires:  dateExpires,
				discount_type: discountType,
			} = extendedInfo;

			return [
				{
					display: (
						<Link href={ `post.php?post=${ couponId }&action=edit` } type="external">
							{ code }
						</Link>
					),
					value: code,
				},
				{ display: formatValue( getCurrencyConfig(), 'number', ordersCount ), value: Number( ordersCount ) },
				{ display: renderCurrency( amount ),                                   value: Number( amount ) },
				{
					display: dateCreated ? <Date date={ dateCreated } visibleFormat={ dateFormat } /> : __( 'N/A', 'storesuite' ),
					value:   dateCreated,
				},
				{
					display: dateExpires ? <Date date={ dateExpires } visibleFormat={ dateFormat } /> : __( 'N/A', 'storesuite' ),
					value:   dateExpires,
				},
				{ display: this.getCouponType( discountType ), value: discountType },
			];
		} );
	}

	getSummary( totals ) {
		const {
			coupons_count: couponsCount = 0,
			orders_count:  ordersCount  = 0,
			amount        = 0,
		} = totals;
		const { formatAmount, getCurrencyConfig } = this.context;
		const currency = getCurrencyConfig();
		return [
			{ label: _n( 'coupon', 'coupons', couponsCount, 'storesuite' ),          value: formatValue( currency, 'number', couponsCount ) },
			{ label: _n( 'order', 'orders', ordersCount, 'storesuite' ),             value: formatValue( currency, 'number', ordersCount ) },
			{ label: __( 'amount discounted', 'storesuite' ),                        value: formatAmount( amount ) },
		];
	}

	render() {
		const { advancedFilters, filters, isRequesting, query } = this.props;
		return (
			<ReportTable
				compareBy="coupons"
				endpoint="coupons"
				getHeadersContent={ this.getHeadersContent }
				getRowsContent={ this.getRowsContent }
				getSummary={ this.getSummary }
				summaryFields={ [ 'coupons_count', 'orders_count', 'amount' ] }
				isRequesting={ isRequesting }
				itemIdField="coupon_id"
				query={ query }
				searchBy="coupons"
				title={ __( 'Coupons', 'storesuite' ) }
				columnPrefsKey="coupons_report_columns"
				filters={ filters }
				advancedFilters={ advancedFilters }
				tableQuery={ {
					extended_info: true,
					orderby: [ 'orders_count', 'amount' ].includes( query.orderby ) ? query.orderby : 'orders_count',
				} }
			/>
		);
	}
}

CouponsReportTable.contextType = CurrencyContext;

export default CouponsReportTable;
