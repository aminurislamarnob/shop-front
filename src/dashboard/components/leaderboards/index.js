import { Fragment, useState, Component } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { compose } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';
import { Card, CardBody, CardHeader } from '@wordpress/components';
import { EmptyTable, TableCard } from '@woocommerce/components';
import { getLeaderboard } from '@woocommerce/data';
import { getHistory } from '@woocommerce/navigation';
import PropTypes from 'prop-types';

import { getAdminSetting } from '../../utils/admin-settings';
import { mapToAnalyticsRoute } from '../../utils/helper';
import { DASHBOARD_DEFAULT_DATE_RANGE } from '../../constants';

const LEADERBOARD_ROWS_KEY = 'storesuite_dashboard_leaderboard_rows';

function lsGet( key, fallback ) {
	try {
		return localStorage.getItem( key ) || fallback;
	} catch {
		return fallback;
	}
}

function lsSet( key, value ) {
	try {
		localStorage.setItem( key, value );
	} catch {}
}

function rewriteLeaderboardLinks( html ) {
	if ( ! html || ! html.includes( 'admin.php' ) ) {
		return html;
	}
	const div = document.createElement( 'div' );
	div.innerHTML = html;
	div.querySelectorAll( 'a[href]' ).forEach( ( a ) => {
		try {
			const absolute = new URL(
				a.getAttribute( 'href' ),
				window.location.href
			).href;
			a.setAttribute( 'href', mapToAnalyticsRoute( absolute ) );
		} catch {}
	} );
	return div.innerHTML;
}

function handleLeaderboardLinkClick( e ) {
	const anchor = e.target.closest( 'a' );
	if ( anchor ) {
		e.preventDefault();
		getHistory().push( anchor.getAttribute( 'href' ) );
	}
}

class LeaderboardTable extends Component {
	getFormattedHeaders() {
		return ( this.props.headers || [] ).map( ( header, i ) => ( {
			isLeftAligned: i === 0,
			hiddenByDefault: false,
			isSortable: false,
			key: header.label,
			label: header.label,
		} ) );
	}

	getFormattedRows() {
		return ( this.props.rows || [] ).map( ( row ) =>
			row.map( ( column ) => ( {
				display: (
					<span
						dangerouslySetInnerHTML={ {
							__html: rewriteLeaderboardLinks( column.display ),
						} }
						onClick={ handleLeaderboardLinkClick }
					/>
				),
				value: column.value,
			} ) )
		);
	}

	render() {
		const { isRequesting, isError, totalRows, title } = this.props;
		const classes = 'storesuite-leaderboard';

		if ( isError ) {
			return (
				<Card className={ classes }>
					<CardHeader>
						<h3 className="storesuite-leaderboard__title">
							{ title }
						</h3>
					</CardHeader>
					<CardBody>
						<EmptyTable>
							{ __(
								'There was an error loading this leaderboard.',
								'storesuite'
							) }
						</EmptyTable>
					</CardBody>
				</Card>
			);
		}

		const rows = this.getFormattedRows();

		if ( ! isRequesting && rows.length === 0 ) {
			return (
				<Card className={ classes }>
					<CardHeader>
						<h3 className="storesuite-leaderboard__title">
							{ title }
						</h3>
					</CardHeader>
					<CardBody>
						<EmptyTable>
							{ __(
								'No data recorded for the selected time period.',
								'storesuite'
							) }
						</EmptyTable>
					</CardBody>
				</Card>
			);
		}

		return (
			<TableCard
				className={ classes }
				headers={ this.getFormattedHeaders() }
				isLoading={ isRequesting }
				rows={ rows }
				rowsPerPage={ totalRows }
				showMenu={ false }
				title={ title }
				totalRows={ totalRows }
			/>
		);
	}
}

LeaderboardTable.defaultProps = {
	headers: [],
	rows: [],
	isError: false,
	isRequesting: false,
};

const ConnectedLeaderboardTable = compose(
	withSelect( ( select, props ) => {
		const { id, query, totalRows } = props;

		const leaderboardQuery = {
			id,
			per_page: totalRows,
			persisted_query: {},
			query,
			select,
			defaultDateRange: DASHBOARD_DEFAULT_DATE_RANGE,
		};

		return getLeaderboard( leaderboardQuery );
	} )
)( LeaderboardTable );

export default function DashboardLeaderboards( { query } ) {
	const allLeaderboards =
		getAdminSetting( 'dataEndpoints', { leaderboards: [] } ).leaderboards ||
		[];
	const [ rowsPerTable, setRowsPerTableState ] = useState( () =>
		parseInt( lsGet( LEADERBOARD_ROWS_KEY, '5' ), 7 )
	);

	const productsLeaderboard = allLeaderboards.find(
		( l ) => l.id === 'products'
	);

	if ( ! productsLeaderboard ) {
		return null;
	}

	return (
		<Fragment>
			<ConnectedLeaderboardTable
				id={ productsLeaderboard.id }
				title={ productsLeaderboard.label }
				headers={ productsLeaderboard.headers }
				query={ query }
				totalRows={ rowsPerTable }
			/>
		</Fragment>
	);
}

DashboardLeaderboards.propTypes = {
	query: PropTypes.object.isRequired,
};
