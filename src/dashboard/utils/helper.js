import { getHistory } from '@woocommerce/navigation';
import { storeSuiteDashboard } from '../config';

/**
 * Maps an admin.php WooCommerce analytics URL to its frontend analytics route.
 * Used so any links rendered by WooCommerce data stores point at the StoreSuite
 * analytics endpoint instead of /wp-admin/.
 */
export function mapToAnalyticsRoute( url ) {
	if ( ! url.includes( 'admin.php' ) ) {
		return url;
	}

	try {
		const urlObj    = new URL( url );
		const newParams = new URLSearchParams();

		const path = urlObj.searchParams.get( 'path' );
		if ( path ) {
			const reportMatch = path.match( /^\/analytics\/([^/?]+)/ );
			if ( reportMatch ) {
				newParams.set( 'report', reportMatch[ 1 ] );
			}
		}

		for ( const [ key, value ] of urlObj.searchParams.entries() ) {
			if ( key !== 'page' && key !== 'path' ) {
				newParams.set( key, value );
			}
		}

		const base = storeSuiteDashboard.reportsPath || window.location.pathname;
		const qs   = newParams.toString();
		return base + ( qs ? '?' + qs : '' );
	} catch {
		return url;
	}
}

/**
 * Maps an admin.php URL with no /analytics/ path back to the dashboard root,
 * preserving query string params (e.g. period, compare, after, before).
 */
export function mapToDashboardRoute( url ) {
	if ( ! url.includes( 'admin.php' ) ) {
		return url;
	}

	try {
		const urlObj    = new URL( url );
		const newParams = new URLSearchParams();

		for ( const [ key, value ] of urlObj.searchParams.entries() ) {
			if ( key !== 'page' && key !== 'path' ) {
				newParams.set( key, value );
			}
		}

		const base = storeSuiteDashboard.dashboardPath || window.location.pathname;
		const qs   = newParams.toString();
		return base + ( qs ? '?' + qs : '' );
	} catch {
		return url;
	}
}

/**
 * Replace any in-page admin.php URL with the correct frontend route.
 * Routes containing /analytics/ go to the analytics page; everything else
 * stays on the dashboard. Used inside a useEffect on location changes.
 */
export function redirectIfAdminUrl() {
	const href = window.location.href || '';
	if ( ! href.includes( 'admin.php' ) ) {
		return;
	}

	let isAnalyticsRoute = false;
	try {
		const path = new URL( href ).searchParams.get( 'path' );
		isAnalyticsRoute = !! ( path && /^\/analytics\//.test( path ) );
	} catch {}

	const mapped = isAnalyticsRoute
		? mapToAnalyticsRoute( href )
		: mapToDashboardRoute( href );

	if ( mapped !== href ) {
		getHistory().replace( mapped );
	}
}
