import { getHistory } from '@woocommerce/navigation';

/**
 * Maps an admin.php WooCommerce analytics URL to its frontend equivalent,
 * preserving all query params except `page` and `path` (WP admin internals).
 *
 * Mirrors Dokan's mapToDashboardRoute approach so getNewPath() can be used
 * throughout the codebase without generating broken admin.php hrefs.
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

		const base = window.location.pathname;
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
