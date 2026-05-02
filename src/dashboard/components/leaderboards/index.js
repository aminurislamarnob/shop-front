import { Fragment, useState, Component } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { compose } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';
import { Card, CardBody, CardHeader, SelectControl } from '@wordpress/components';
import { EllipsisMenu, EmptyTable, MenuItem, MenuTitle, TableCard } from '@woocommerce/components';
import { getLeaderboard } from '@woocommerce/data';
import { getHistory, getPersistedQuery } from '@woocommerce/navigation';
import PropTypes from 'prop-types';

import { getAdminSetting } from '../../utils/admin-settings';
import { mapToAnalyticsRoute } from '../../utils/helper';
import { DASHBOARD_DEFAULT_DATE_RANGE } from '../../constants';

const HIDDEN_LEADERBOARDS_KEY = 'storesuite_dashboard_hidden_leaderboards';
const LEADERBOARD_ROWS_KEY    = 'storesuite_dashboard_leaderboard_rows';

function lsGet( key, fallback ) {
	try { return localStorage.getItem( key ) || fallback; } catch { return fallback; }
}

function lsSet( key, value ) {
	try { localStorage.setItem( key, value ); } catch {}
}

function lsParsed( key ) {
	try { return JSON.parse( localStorage.getItem( key ) ) || []; } catch { return []; }
}

function rewriteLeaderboardLinks( html ) {
	if ( ! html || ! html.includes( 'admin.php' ) ) {
		return html;
	}
	const div = document.createElement( 'div' );
	div.innerHTML = html;
	div.querySelectorAll( 'a[href]' ).forEach( ( a ) => {
		try {
			const absolute = new URL( a.getAttribute( 'href' ), window.location.href ).href;
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
			isLeftAligned:  i === 0,
			hiddenByDefault: false,
			isSortable:     false,
			key:            header.label,
			label:          header.label,
		} ) );
	}

	getFormattedRows() {
		return ( this.props.rows || [] ).map( ( row ) =>
			row.map( ( column ) => ( {
				display: (
					<span
						dangerouslySetInnerHTML={ { __html: rewriteLeaderboardLinks( column.display ) } }
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
						<h3 className="storesuite-leaderboard__title">{ title }</h3>
					</CardHeader>
					<CardBody>
						<EmptyTable>
							{ __( 'There was an error loading this leaderboard.', 'storesuite' ) }
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
						<h3 className="storesuite-leaderboard__title">{ title }</h3>
					</CardHeader>
					<CardBody>
						<EmptyTable>
							{ __( 'No data recorded for the selected time period.', 'storesuite' ) }
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
	headers:      [],
	rows:         [],
	isError:      false,
	isRequesting: false,
};

const ConnectedLeaderboardTable = compose(
	withSelect( ( select, props ) => {
		const { id, query, totalRows } = props;

		const leaderboardQuery = {
			id,
			per_page:        totalRows,
			persisted_query: getPersistedQuery( query ),
			query,
			select,
			defaultDateRange: DASHBOARD_DEFAULT_DATE_RANGE,
		};

		return getLeaderboard( leaderboardQuery );
	} )
)( LeaderboardTable );

export default function DashboardLeaderboards( { query } ) {
	const allLeaderboards = getAdminSetting( 'dataEndpoints', { leaderboards: [] } ).leaderboards || [];

	const [ hiddenLeaderboards, setHiddenLeaderboards ] = useState( () => lsParsed( HIDDEN_LEADERBOARDS_KEY ) );
	const [ rowsPerTable,       setRowsPerTableState   ] = useState( () => parseInt( lsGet( LEADERBOARD_ROWS_KEY, '5' ), 10 ) );

	if ( ! allLeaderboards.length ) {
		return null;
	}

	const toggleLeaderboard = ( id ) => {
		const next = hiddenLeaderboards.includes( id )
			? hiddenLeaderboards.filter( ( l ) => l !== id )
			: [ ...hiddenLeaderboards, id ];
		setHiddenLeaderboards( next );
		lsSet( HIDDEN_LEADERBOARDS_KEY, JSON.stringify( next ) );
	};

	const handleRowsChange = ( value ) => {
		const rows = parseInt( value, 10 );
		setRowsPerTableState( rows );
		lsSet( LEADERBOARD_ROWS_KEY, String( rows ) );
	};

	const visibleLeaderboards = allLeaderboards.filter( ( l ) => ! hiddenLeaderboards.includes( l.id ) );

	return (
		<Fragment>
			<div className="storesuite-dashboard-section-header">
				<h3>{ __( 'Leaderboards', 'storesuite' ) }</h3>
				<EllipsisMenu
					label={ __( 'Choose which leaderboards to display', 'storesuite' ) }
					renderContent={ () => (
						<Fragment>
							<MenuTitle>{ __( 'Leaderboards', 'storesuite' ) }</MenuTitle>
							{ allLeaderboards.map( ( leaderboard ) => (
								<MenuItem
									key={ leaderboard.id }
									checked={ ! hiddenLeaderboards.includes( leaderboard.id ) }
									isCheckbox
									isClickable
									onInvoke={ () => toggleLeaderboard( leaderboard.id ) }
								>
									{ leaderboard.label }
								</MenuItem>
							) ) }
							<MenuItem>
								<SelectControl
									label={ __( 'Rows per table', 'storesuite' ) }
									value={ String( rowsPerTable ) }
									options={ [ 5, 10, 15, 20, 25 ].map( ( n ) => ( { value: String( n ), label: String( n ) } ) ) }
									onChange={ handleRowsChange }
									__next40pxDefaultSize
									__nextHasNoMarginBottom
								/>
							</MenuItem>
						</Fragment>
					) }
				/>
			</div>

			{ visibleLeaderboards.length > 0 ? (
				<div className="storesuite-dashboard-leaderboards-grid">
					{ visibleLeaderboards.map( ( leaderboard ) => (
						<ConnectedLeaderboardTable
							key={ leaderboard.id }
							id={ leaderboard.id }
							title={ leaderboard.label }
							headers={ leaderboard.headers }
							query={ query }
							totalRows={ rowsPerTable }
						/>
					) ) }
				</div>
			) : (
				<p className="storesuite-dashboard-empty-notice">
					{ __( 'No leaderboards selected. Use the menu above to choose which leaderboards to display.', 'storesuite' ) }
				</p>
			) }
		</Fragment>
	);
}

DashboardLeaderboards.propTypes = {
	query: PropTypes.object.isRequired,
};
