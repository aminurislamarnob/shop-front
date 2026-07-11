<?php
/**
 * Employee Manager — activity log capture & querying.
 *
 * @package StoreSuite
 */

namespace PluginizeLab\StoreSuite\Modules\EmployeeManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Records dashboard actions to the activity_log table and exposes a query API
 * for the Activity screen.
 *
 * Capture is opt-out (Settings: enable_activity_log). Other modules can log
 * without depending on this one by firing:
 *   do_action( 'storesuite_log_activity', $action, $object_type, $object_id, $summary, $meta )
 */
class ActivityLogger {

	const CRON_HOOK = 'storesuite_employee_manager_purge_log';

	/**
	 * Wire up capture hooks, the public logging action, and the purge cron.
	 * Called from Module::boot().
	 *
	 * @return void
	 */
	public function register() {
		// Public logging entry point for this and other modules.
		add_action( 'storesuite_log_activity', array( $this, 'log' ), 10, 5 );

		if ( ! (bool) Settings::value( 'enable_activity_log' ) ) {
			return;
		}

		// StoreSuite domain events.
		add_action( 'storesuite_new_product_added', array( $this, 'on_product_added' ), 10, 1 );
		add_action( 'storesuite_product_updated', array( $this, 'on_product_updated' ), 10, 1 );
		add_action( 'storesuite_product_quick_edit_updated', array( $this, 'on_product_updated' ), 10, 1 );

		// WooCommerce order status changes.
		add_action( 'woocommerce_order_status_changed', array( $this, 'on_order_status_changed' ), 10, 3 );

		// Employee lifecycle.
		add_action( 'storesuite_employee_created', array( $this, 'on_employee_created' ), 10, 2 );
		add_action( 'storesuite_employee_status_changed', array( $this, 'on_employee_status_changed' ), 10, 2 );

		// Logins (only employees are recorded, inside log_login()).
		add_action( 'wp_login', array( $this, 'on_login' ), 10, 2 );

		// Daily purge of aged entries.
		add_action( self::CRON_HOOK, array( $this, 'purge' ) );
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::CRON_HOOK );
		}
	}

	/**
	 * Insert a log row for the current user.
	 *
	 * @param string $action      Machine action key (e.g. product.update).
	 * @param string $object_type Object type (product, order, employee, ...).
	 * @param int    $object_id   Related object ID.
	 * @param string $summary     Human summary.
	 * @param array  $meta        Optional structured extras.
	 * @return void
	 */
	public function log( $action, $object_type = '', $object_id = 0, $summary = '', $meta = array() ) {
		if ( ! (bool) Settings::value( 'enable_activity_log' ) ) {
			return;
		}

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->insert(
			Installer::activity_log_table(),
			array(
				'user_id'     => get_current_user_id(),
				'action'      => substr( (string) $action, 0, 64 ),
				'object_type' => substr( (string) $object_type, 0, 32 ),
				'object_id'   => (int) $object_id,
				'summary'     => substr( (string) $summary, 0, 255 ),
				'meta'        => empty( $meta ) ? null : wp_json_encode( $meta ),
				'ip'          => self::client_ip(),
				'created_at'  => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%s', '%d', '%s', '%s', '%s', '%s' )
		);
	}

	/**
	 * Query log rows with optional filters.
	 *
	 * @param array $args {
	 *     @type int    $user_id  Filter by user.
	 *     @type string $action   Filter by action key.
	 *     @type int    $paged    Page (1-based).
	 *     @type int    $per_page Rows per page.
	 * }
	 * @return array{items:array,total:int,total_pages:int}
	 */
	public static function query( array $args = array() ) {
		global $wpdb;

		$table    = Installer::activity_log_table();
		$per_page = isset( $args['per_page'] ) ? max( 1, (int) $args['per_page'] ) : 30;
		$paged    = isset( $args['paged'] ) ? max( 1, (int) $args['paged'] ) : 1;
		$offset   = ( $paged - 1 ) * $per_page;

		$where  = array( '1=1' );
		$params = array();

		if ( ! empty( $args['user_id'] ) ) {
			$where[]  = 'user_id = %d';
			$params[] = (int) $args['user_id'];
		}
		if ( ! empty( $args['action'] ) ) {
			$where[]  = 'action = %s';
			$params[] = (string) $args['action'];
		}

		$where_sql = implode( ' AND ', $where );

		// $where_sql is built only from the fixed fragments above; user values
		// are parameterised through $params.
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$count_sql = "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}";
		$total     = (int) $wpdb->get_var( empty( $params ) ? $count_sql : $wpdb->prepare( $count_sql, $params ) );

		$list_params = array_merge( $params, array( $per_page, $offset ) );
		$list_sql    = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY created_at DESC LIMIT %d OFFSET %d";
		$rows        = $wpdb->get_results( $wpdb->prepare( $list_sql, $list_params ), ARRAY_A );
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		return array(
			'items'       => is_array( $rows ) ? $rows : array(),
			'total'       => $total,
			'total_pages' => (int) ceil( $total / $per_page ),
		);
	}

	/**
	 * Delete entries older than the configured retention window.
	 *
	 * @return void
	 */
	public function purge() {
		$days = (int) Settings::value( 'log_retention_days' );
		if ( $days <= 0 ) {
			return;
		}

		global $wpdb;
		$table  = Installer::activity_log_table();
		$cutoff = gmdate( 'Y-m-d H:i:s', time() - ( $days * DAY_IN_SECONDS ) );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE created_at < %s", $cutoff ) );
	}

	/* -----------------------------------------------------------------
	 * Event handlers
	 * --------------------------------------------------------------- */

	/**
	 * @param int $product_id Product ID.
	 * @return void
	 */
	public function on_product_added( $product_id ) {
		$this->log( 'product.create', 'product', (int) $product_id, self::product_summary( $product_id, __( 'created product', 'storesuite' ) ) );
	}

	/**
	 * @param int $product_id Product ID.
	 * @return void
	 */
	public function on_product_updated( $product_id ) {
		$this->log( 'product.update', 'product', (int) $product_id, self::product_summary( $product_id, __( 'updated product', 'storesuite' ) ) );
	}

	/**
	 * @param int    $order_id   Order ID.
	 * @param string $old_status Previous status.
	 * @param string $new_status New status.
	 * @return void
	 */
	public function on_order_status_changed( $order_id, $old_status, $new_status ) {
		$this->log(
			'order.status_change',
			'order',
			(int) $order_id,
			sprintf(
				/* translators: 1: order id, 2: old status, 3: new status */
				__( 'changed order #%1$d from %2$s to %3$s', 'storesuite' ),
				$order_id,
				$old_status,
				$new_status
			),
			array(
				'from' => $old_status,
				'to'   => $new_status,
			)
		);
	}

	/**
	 * @param int    $user_id New employee user ID.
	 * @param string $role    Assigned role.
	 * @return void
	 */
	public function on_employee_created( $user_id, $role ) {
		$this->log( 'employee.create', 'employee', (int) $user_id, __( 'added a team member', 'storesuite' ), array( 'role' => $role ) );
	}

	/**
	 * @param int    $user_id Employee user ID.
	 * @param string $status  New status.
	 * @return void
	 */
	public function on_employee_status_changed( $user_id, $status ) {
		$this->log( 'employee.status', 'employee', (int) $user_id, sprintf( /* translators: %s: status */ __( 'set a team member to %s', 'storesuite' ), $status ) );
	}

	/**
	 * @param string   $user_login Login name.
	 * @param \WP_User $user       User object.
	 * @return void
	 */
	public function on_login( $user_login, $user = null ) {
		if ( ! $user instanceof \WP_User || ! EmployeeManager::is_employee( $user->ID ) ) {
			return;
		}
		EmployeeManager::touch_last_login( $user->ID );

		global $wpdb;
		// Log directly against the logging-in user (get_current_user_id may not
		// be populated yet at wp_login time).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->insert(
			Installer::activity_log_table(),
			array(
				'user_id'     => $user->ID,
				'action'      => 'login',
				'object_type' => 'employee',
				'object_id'   => $user->ID,
				'summary'     => __( 'signed in', 'storesuite' ),
				'meta'        => null,
				'ip'          => self::client_ip(),
				'created_at'  => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%s', '%d', '%s', '%s', '%s', '%s' )
		);
	}

	/* -----------------------------------------------------------------
	 * Helpers
	 * --------------------------------------------------------------- */

	/**
	 * Build a "verb product-name (#id)" summary.
	 *
	 * @param int    $product_id Product ID.
	 * @param string $verb       Localised verb phrase.
	 * @return string
	 */
	private static function product_summary( $product_id, $verb ) {
		$name = get_the_title( $product_id );
		return $name ? sprintf( '%s "%s" (#%d)', $verb, $name, $product_id ) : sprintf( '%s #%d', $verb, $product_id );
	}

	/**
	 * Best-effort client IP for the log.
	 *
	 * @return string
	 */
	private static function client_ip() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		return substr( $ip, 0, 45 );
	}

	/**
	 * Clear the scheduled purge event. Called on module deactivation.
	 *
	 * @return void
	 */
	public static function unschedule() {
		$timestamp = wp_next_scheduled( self::CRON_HOOK );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, self::CRON_HOOK );
		}
	}
}
