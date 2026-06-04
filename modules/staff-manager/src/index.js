/**
 * Staff Manager — module entry.
 *
 * Loaded by `wp_enqueue_script` after core's `storesuite-admin-page` bundle
 * has run (and therefore after `window.StoreSuite.registerScreens` exists).
 * Registers this module's React route(s) with the core router before
 * `DOMContentLoaded` fires and the App mounts.
 *
 * @package StoreSuite
 */

import StaffManagerAdmin from './admin/StaffManagerAdmin';

if (
	window.StoreSuite &&
	typeof window.StoreSuite.registerScreens === 'function'
) {
	window.StoreSuite.registerScreens( 'staff-manager', {
		'/staff-manager': StaffManagerAdmin,
	} );
}
