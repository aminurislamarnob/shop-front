import { getHistory } from '@woocommerce/navigation';
import { storeSuiteConfig } from '../config';

export const DASHBOARD_BASE = window.location.pathname;

/**
 * Maps an admin.php WooCommerce analytics URL to its frontend equivalent.
 * Uses reportsPath (the analytics endpoint) as the base so the generated URL
 * always targets the analytics template, avoiding conflicts with WordPress
 * rewrite query vars (e.g. ?products=67 activating the products endpoint).
 */
export function mapToDashboardRoute( url ) {
	if ( ! url.includes( 'admin.php' ) ) {
		return url;
	}

	try {
		const urlObj    = new URL( url );
		const newParams = new URLSearchParams();

		// Convert WP admin path (e.g. /analytics/products) to report= param.
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

		const base = storeSuiteConfig.reportsPath || DASHBOARD_BASE;
		const qs   = newParams.toString();
		return base + ( qs ? '?' + qs : '' );
	} catch {
		return url;
	}
}

/**
 * Redirects to the frontend-equivalent URL if the current URL contains admin.php.
 * Call this inside a useEffect watching location so any admin.php navigation
 * (e.g. from SummaryNumber / getNewPath hrefs) gets transparently remapped.
 */
export function redirectIfAdminUrl() {
	const href   = window.location.href || '';
	const mapped = mapToDashboardRoute( href );
	if ( mapped !== href ) {
		getHistory().push( mapped );
	}
}
