import { lazy } from '@wordpress/element';

const RELOAD_FLAG = 'storesuite_dashboard_chunk_reloaded';

/*
 * Drop-in replacement for React.lazy that survives stale/missing chunks.
 *
 * After a new build the previous bundle's chunk references can disappear
 * (filenames are unhashed and carry no cache-busting query), so a lazy
 * import can throw ChunkLoadError. We retry once, and if it still fails we
 * force a single full reload to pull the fresh manifest — a sessionStorage
 * flag prevents a reload loop when the chunk is genuinely gone.
 */
export default function lazyWithRetry( importer ) {
	const load = () =>
		importer()
			.then( ( module ) => {
				// Loaded cleanly — allow a future stale build to reload again.
				window.sessionStorage.removeItem( RELOAD_FLAG );
				return module;
			} )
			.catch( () =>
				// Single retry for a transient network/build race.
				importer().catch( ( error ) => {
					const alreadyReloaded =
						window.sessionStorage.getItem( RELOAD_FLAG ) === '1';

					if ( ! alreadyReloaded ) {
						window.sessionStorage.setItem( RELOAD_FLAG, '1' );
						window.location.reload();
						// Keep the promise pending while the page reloads.
						return new Promise( () => {} );
					}

					throw error;
				} )
			);

	return lazy( load );
}
