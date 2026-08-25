import { execSync } from 'child_process';

export const WP = 'wp --path=/tmp/wp-e2e --allow-root';
export const SCRATCH =
	'/tmp/claude-0/-home-user-storesuite/071ccf52-ffff-5a94-b502-43aa5b146b00/scratchpad/qa184';

/** Run a wp-cli command, returning trimmed stdout. */
export function wpCli( args: string ): string {
	return execSync( `${ WP } ${ args } 2>/dev/null`, { encoding: 'utf8' } ).trim();
}

/** Run PHP against the site, returning trimmed stdout. */
export function wpEval( code: string ): string {
	const quoted = `'${ code.replace( /'/g, `'\\''` ) }'`;
	return execSync( `${ WP } eval ${ quoted } 2>/dev/null`, {
		encoding: 'utf8',
	} ).trim();
}

/**
 * Fire the new-order notification pipeline. Notifications dedupe on
 * (type, object_id), so each event needs a genuinely new order; these are
 * tagged QA184 for cleanup.
 */
export function fireNewOrderEvent( times = 1 ): void {
	wpEval(
		`for ( $i = 0; $i < ${ times }; $i++ ) {
			$order = wc_create_order( array( 'status' => 'processing', 'customer_id' => 0 ) );
			$order->set_billing_last_name( 'QA184' );
			$order->save();
			do_action( 'woocommerce_checkout_order_processed', $order->get_id() );
		}
		echo 'fired';`
	);
}

/** Remove QA184 orders and reset the notifications table. */
export function cleanupNotificationsFixtures(): void {
	wpEval(
		`$ids = wc_get_orders( array( 'billing_last_name' => 'QA184', 'limit' => -1, 'return' => 'ids' ) );
		foreach ( $ids as $id ) { wp_delete_post( $id, true ); }
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->prefix}storesuite_notifications" );
		echo 'cleaned ' . count( $ids );`
	);
}
