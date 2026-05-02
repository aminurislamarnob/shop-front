import { useState, useEffect, useContext, useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import {
	Date as WCDate,
	OrderStatus,
	TableCard,
} from '@woocommerce/components';
import { CurrencyContext } from '@woocommerce/currency';
import { defaultTableDateFormat } from '@woocommerce/date';

import { getAdminSetting } from '../../utils/admin-settings';
import { storeSuiteDashboard } from '../../config';

const ORDERS_PER_PAGE = 5;

function getHeaders() {
	return [
		{
			label: __( 'Order', 'storesuite' ),
			key: 'order',
			isLeftAligned: true,
			isSortable: false,
			required: true,
		},
		{
			label: __( 'Customer', 'storesuite' ),
			key: 'customer',
			isLeftAligned: false,
			isSortable: false,
		},
		{
			label: __( 'Date', 'storesuite' ),
			key: 'date',
			isLeftAligned: false,
			isSortable: false,
		},
		{
			label: __( 'Total', 'storesuite' ),
			key: 'total',
			isLeftAligned: false,
			isSortable: false,
			isNumeric: true,
		},
		{
			label: __( 'Status', 'storesuite' ),
			key: 'status',
			isLeftAligned: false,
			isSortable: false,
		},
	];
}

function getRows( orders, renderCurrency ) {
	const dateFormat = getAdminSetting( 'dateFormat', defaultTableDateFormat );
	const orderStatusMap = getAdminSetting( 'orderStatuses', {} );

	return orders.map( ( order ) => {
		const {
			number: orderNumber,
			date_created: date,
			status,
			total,
			billing,
		} = order;

		const customerName = billing
			? [ billing.first_name, billing.last_name ]
					.filter( Boolean )
					.join( ' ' )
			: __( 'Guest', 'storesuite' );

		return [
			{
				display: (
					<span className="storesuite-recent-orders__number">
						#{ orderNumber }
					</span>
				),
				value: orderNumber,
			},
			{
				display: customerName,
				value: customerName,
			},
			{
				display: <WCDate date={ date } visibleFormat={ dateFormat } />,
				value: date,
			},
			{
				display: renderCurrency( total ),
				value: Number( total ),
			},
			{
				display: (
					<OrderStatus
						className="storesuite-recent-orders__status"
						order={ { status } }
						labelPositionToLeft={ false }
						orderStatusMap={ orderStatusMap }
					/>
				),
				value: status,
			},
		];
	} );
}

// Stable reference — headers don't depend on props/state.
const HEADERS = getHeaders();

export default function RecentOrders() {
	const canViewOrders = storeSuiteDashboard.canViewOrders !== false;
	const [ orders, setOrders ] = useState( [] );
	const [ isLoading, setLoading ] = useState( canViewOrders );

	const { render: renderCurrency } = useContext( CurrencyContext );

	useEffect( () => {
		if ( ! canViewOrders ) {
			return;
		}
		apiFetch( {
			path: `/wc/v3/orders?per_page=${ ORDERS_PER_PAGE }&orderby=date&order=desc`,
		} )
			.then( ( data ) => {
				setOrders( Array.isArray( data ) ? data : [] );
				setLoading( false );
			} )
			.catch( () => {
				setLoading( false );
			} );
	}, [ canViewOrders ] );

	const rows = useMemo(
		() => ( isLoading ? [] : getRows( orders, renderCurrency ) ),
		[ orders, isLoading, renderCurrency ]
	);

	if ( ! canViewOrders ) {
		return null;
	}

	return (
		<div className="storesuite-dashboard-recent-orders">
			<TableCard
				className="storesuite-recent-orders-table"
				title={ __( 'Recent Orders', 'storesuite' ) }
				headers={ HEADERS }
				rows={ rows }
				rowsPerPage={ ORDERS_PER_PAGE }
				totalRows={ rows.length }
				isLoading={ isLoading }
				showMenu={ false }
				onPageChange={ () => {} }
				onQueryChange={ () => () => {} }
				query={ {} }
			/>
		</div>
	);
}
