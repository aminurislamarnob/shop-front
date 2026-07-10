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
		// The Customers report lives at /customers in wc-admin (not under
		// /analytics), so it needs its own mapping — Top Customers leaderboard
		// links point there.
		const path = urlObj.searchParams.get( 'path' );
		if ( path ) {
			const reportMatch = path.match( /^\/analytics\/([^/?]+)/ );
			if ( reportMatch ) {
				newParams.set( 'report', reportMatch[ 1 ] );
			} else if ( /^\/customers\/?$/.test( path ) ) {
				newParams.set( 'report', 'customers' );
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
		getHistory().replace( mapped );
	}
}

/**
 * Capture-phase click handler for internal WooCommerce admin.php links.
 *
 * WooCommerce data-store components (report tables, leaderboards) render hrefs
 * like `admin.php?page=wc-admin&path=/analytics/...`. On the frontend dashboard
 * these must resolve to `?report=...` routes. Handling the click directly is
 * more reliable than remapping after navigation — in particular it covers
 * same-report drill-downs (e.g. a product name → single-product view) where the
 * report query var is unchanged and the post-navigation redirect does not fire.
 */
export function handleAdminLinkClick( event ) {
	// Let the browser handle modified / non-primary clicks (new tab, etc.).
	if (
		event.defaultPrevented ||
		event.button !== 0 ||
		event.metaKey ||
		event.ctrlKey ||
		event.shiftKey ||
		event.altKey
	) {
		return;
	}

	const anchor = event.target.closest && event.target.closest( 'a[href]' );
	if ( ! anchor ) {
		return;
	}

	// Only intercept the internal wc-admin links; leave post.php / external
	// links (e.g. edit order/product in wp-admin) to navigate normally.
	if ( ! ( anchor.getAttribute( 'href' ) || '' ).includes( 'admin.php' ) ) {
		return;
	}

	const mapped = mapToDashboardRoute( anchor.href );
	if ( mapped === anchor.href ) {
		return;
	}

	event.preventDefault();
	event.stopImmediatePropagation();
	getHistory().push( mapped );
}
