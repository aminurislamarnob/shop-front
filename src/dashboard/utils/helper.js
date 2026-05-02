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
 * Replace any in-page admin.php URL with the analytics route equivalent.
 * Used inside a useEffect on location changes.
 */
export function redirectIfAdminUrl() {
	const href   = window.location.href || '';
	const mapped = mapToAnalyticsRoute( href );
	if ( mapped !== href ) {
		getHistory().replace( mapped );
	}
}
