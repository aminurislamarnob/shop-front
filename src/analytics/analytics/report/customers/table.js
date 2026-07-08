import { __, _n } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { map } from 'lodash';
import { Date } from '@woocommerce/components';
import { formatValue } from '@woocommerce/number';
import { defaultTableDateFormat } from '@woocommerce/date';
import { CurrencyContext } from '@woocommerce/currency';
import ReportTable from '../../components/report-table';
import { getAdminSetting } from '../../../utils/admin-settings';

class CustomersReportTable extends Component {
	constructor() {
		super();
		this.getHeadersContent = this.getHeadersContent.bind( this );
		this.getRowsContent    = this.getRowsContent.bind( this );
		this.getSummary        = this.getSummary.bind( this );
	}

	getHeadersContent() {
		return [
			{ label: __( 'Name', 'storesuite' ),          key: 'name',             required: true, isLeftAligned: true, isSortable: true },
			{ label: __( 'Username', 'storesuite' ),      key: 'username',         hiddenByDefault: true },
			{ label: __( 'Last active', 'storesuite' ),   key: 'date_last_active', defaultSort: true, isSortable: true },
			{ label: __( 'Sign up', 'storesuite' ),       key: 'date_registered',  isSortable: true },
			{ label: __( 'Email', 'storesuite' ),         key: 'email' },
			{ label: __( 'Orders', 'storesuite' ),        key: 'orders_count',     isSortable: true, isNumeric: true },
			{ label: __( 'Total spend', 'storesuite' ),   key: 'total_spend',      isSortable: true, isNumeric: true },
			{ label: __( 'AOV', 'storesuite' ),           key: 'avg_order_value',  isNumeric: true },
			{ label: __( 'Country / Region', 'storesuite' ), key: 'country' },
			{ label: __( 'City', 'storesuite' ),          key: 'city',             hiddenByDefault: true },
			{ label: __( 'Region', 'storesuite' ),        key: 'state',            hiddenByDefault: true },
			{ label: __( 'Postal code', 'storesuite' ),   key: 'postcode',         hiddenByDefault: true },
		];
	}

	getCountryName( code ) {
		const countries = ( window.wcSettings && window.wcSettings.countries ) || {};
		return countries[ code ] || code || '';
	}

	getRowsContent( customers ) {
		const dateFormat = getAdminSetting( 'dateFormat', defaultTableDateFormat );
		const { formatAmount, getCurrencyConfig } = this.context;
		const currency = getCurrencyConfig();

		return map( customers, ( customer ) => {
			const {
				avg_order_value:  avgOrderValue,
				date_last_active: dateLastActive,
				date_registered:  dateRegistered,
				email,
				name,
				username,
				orders_count:     ordersCount,
				total_spend:      totalSpend,
				country,
				city,
				state,
				postcode,
			} = customer;

			const countryName = this.getCountryName( country );

			return [
				{ display: name,     value: name },
				{ display: username, value: username },
				{
					display: dateLastActive ? <Date date={ dateLastActive } visibleFormat={ dateFormat } /> : '—',
					value:   dateLastActive,
				},
				{
					display: dateRegistered ? <Date date={ dateRegistered } visibleFormat={ dateFormat } /> : '—',
					value:   dateRegistered,
				},
				{ display: <a href={ `mailto:${ email }` }>{ email }</a>,               value: email },
				{ display: formatValue( currency, 'number', ordersCount ),              value: Number( ordersCount ) },
				{ display: formatAmount( totalSpend ),                                  value: Number( totalSpend ) },
				{ display: formatAmount( avgOrderValue ),                               value: Number( avgOrderValue ) },
				{ display: countryName,                                                 value: country },
				{ display: city,                                                        value: city },
				{ display: state,                                                       value: state },
				{ display: postcode,                                                    value: postcode },
			];
		} );
	}

	getSummary( totals ) {
		const {
			customers_count:     customersCount   = 0,
			avg_orders_count:    avgOrdersCount   = 0,
			avg_total_spend:     avgTotalSpend    = 0,
			avg_avg_order_value: avgAvgOrderValue = 0,
		} = totals;
		const { formatAmount, getCurrencyConfig } = this.context;
		const currency = getCurrencyConfig();
		return [
			{ label: _n( 'customer', 'customers', customersCount, 'storesuite' ), value: formatValue( currency, 'number', customersCount ) },
			{ label: __( 'Avg. orders', 'storesuite' ),                            value: formatValue( currency, 'number', avgOrdersCount ) },
			{ label: __( 'Avg. total spend', 'storesuite' ),                       value: formatAmount( avgTotalSpend ) },
			{ label: __( 'Avg. order value', 'storesuite' ),                       value: formatAmount( avgAvgOrderValue ) },
		];
	}

	render() {
		const { advancedFilters, filters, isRequesting, query } = this.props;
		return (
			<ReportTable
				endpoint="customers"
				getHeadersContent={ this.getHeadersContent }
				getRowsContent={ this.getRowsContent }
				getSummary={ this.getSummary }
				summaryFields={ [ 'customers_count', 'avg_orders_count', 'avg_total_spend', 'avg_avg_order_value' ] }
				isRequesting={ isRequesting }
				itemIdField="id"
				query={ query }
				searchBy="customers"
				title={ __( 'Customers', 'storesuite' ) }
				columnPrefsKey="customers_report_columns"
				filters={ filters }
				advancedFilters={ advancedFilters }
				tableQuery={ {
					orderby: [ 'name', 'username', 'date_last_active', 'date_registered', 'orders_count', 'total_spend' ].includes( query.orderby ) ? query.orderby : 'date_last_active',
					order:   query.order || 'desc',
				} }
			/>
		);
	}
}

CustomersReportTable.contextType = CurrencyContext;

export default CustomersReportTable;
