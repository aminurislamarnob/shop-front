import { getQuery, getPersistedQuery } from '@woocommerce/navigation';

/**
 * Synchronizes the PHP-rendered dashboard sidebar with the analytics SPA.
 *
 * The sidebar is server-rendered once per page load, but report switches
 * happen via pushState. On every route change this:
 * 1. Moves the `.active` class to the submenu anchor matching `?report=`.
 * 2. Rewrites each report link's href with the current persisted query
 *    (period/compare/after/before/…) so switching reports via the sidebar
 *    keeps the selected date range — mirroring wc-admin's updateLinkHref.
 *
 * Only anchors carrying a `data-report` attribute (rendered by
 * DashboardMenu.php for report-style submenus) are touched; other menus
 * keep their server-rendered state.
 */
export function syncSidebar() {
	const anchors = document.querySelectorAll(
		'.storesuite-dashboard-menu a[data-report]'
	);
	if ( ! anchors.length ) {
		return;
	}

	const query        = getQuery();
	const activeReport = query.report || 'overview';
	const persisted    = getPersistedQuery( query );

	let analyticsLi = null;

	anchors.forEach( ( anchor ) => {
		const report = anchor.dataset.report;

		if ( ! anchor.dataset.ssBaseHref ) {
			anchor.dataset.ssBaseHref = anchor.getAttribute( 'href' ) || '';
		}
		const base   = anchor.dataset.ssBaseHref.split( '?' )[ 0 ];
		const params = new URLSearchParams( { report, ...persisted } );
		anchor.setAttribute( 'href', base + '?' + params.toString() );

		anchor.classList.toggle( 'active', report === activeReport );

		if ( ! analyticsLi ) {
			analyticsLi = anchor.closest( 'li.has-submenu' );
		}
	} );

	if ( analyticsLi ) {
		analyticsLi.classList.add( 'is-open' );
		const parentAnchor = analyticsLi.querySelector( ':scope > a' );
		if ( parentAnchor ) {
			parentAnchor.classList.add( 'active' );
		}
	}
}
