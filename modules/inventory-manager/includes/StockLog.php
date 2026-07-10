<?php
/**
 * Inventory Manager — stock movement log.
 *
 * @package StoreSuite
 */

namespace PluginizeLab\StoreSuite\Modules\InventoryManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Writes and reads the stock movement log. Captures every stock change via the
 * WooCommerce stock hooks (order reductions included) as well as explicit
 * dashboard/bulk edits.
 */
class StockLog {

	const CRON_HOOK = 'storesuite_inventory_manager_purge_log';

	/**
	 * Guards against double-logging when our own setter also triggers the WC
	 * stock hook.
	 *
	 * @var array<int,bool>
	 */
	private static $suppress = array();

	/**
	 * Register capture hooks + purge cron. Called from Module::boot().
	 *
	 * @return void
	 */
	public function register() {
		if ( (bool) Settings::value( 'enable_stock_log' ) ) {
			add_action( 'woocommerce_product_set_stock', array( $this, 'on_stock_set' ), 10, 1 );
			add_action( 'woocommerce_variation_set_stock', array( $this, 'on_stock_set' ), 10, 1 );
			add_action( 'woocommerce_reduce_order_stock', array( $this, 'on_order_reduce' ), 10, 1 );

			add_action( self::CRON_HOOK, array( $this, 'purge' ) );
			if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
				wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::CRON_HOOK );
			}
		}
	}

	/**
	 * Insert a movement row.
	 *
	 * @param int      $product_id Product/variation ID.
	 * @param int|null $before     Quantity before.
	 * @param int|null $after      Quantity after.
	 * @param string   $type       Change type (manual/bulk/order_reduce/...).
	 * @param string   $reference  Optional reference (e.g. order #).
	 * @param string   $note       Optional note.
	 * @return void
	 */
	public static function record( $product_id, $before, $after, $type = 'manual', $reference = '', $note = '' ) {
		if ( ! (bool) Settings::value( 'enable_stock_log' ) ) {
			return;
		}

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->insert(
			Installer::stock_log_table(),
			array(
				'product_id'  => (int) $product_id,
				'user_id'     => get_current_user_id(),
				'qty_before'  => null === $before ? null : (int) $before,
				'qty_after'   => null === $after ? null : (int) $after,
				'change_type' => substr( (string) $type, 0, 32 ),
				'reference'   => $reference ? substr( (string) $reference, 0, 128 ) : null,
				'note'        => $note ? substr( (string) $note, 0, 255 ) : null,
				'created_at'  => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s' )
		);
	}

	/**
	 * Suppress the next WC-hook capture for a product (used when we log the
	 * change explicitly ourselves).
	 *
	 * @param int $product_id Product ID.
	 * @return void
	 */
	public static function suppress( $product_id ) {
		self::$suppress[ (int) $product_id ] = true;
	}

	/**
	 * Capture a generic stock set from WooCommerce.
	 *
	 * @param \WC_Product $product Product whose stock changed.
	 * @return void
	 */
	public function on_stock_set( $product ) {
		if ( ! $product instanceof \WC_Product ) {
			return;
		}
		$id = $product->get_id();
		if ( ! empty( self::$suppress[ $id ] ) ) {
			unset( self::$suppress[ $id ] );
			return;
		}
		// We don't know the previous value here; record the resulting quantity.
		self::record( $id, null, $product->get_stock_quantity(), 'adjustment' );
	}

	/**
	 * Tag order-driven reductions with the order reference.
	 *
	 * @param \WC_Order $order Order being reduced.
	 * @return void
	 */
	public function on_order_reduce( $order ) {
		if ( ! $order instanceof \WC_Order ) {
			return;
		}
		foreach ( $order->get_items() as $item ) {
			$product = $item->get_product();
			if ( $product ) {
				self::record( $product->get_id(), null, $product->get_stock_quantity(), 'order_reduce', '#' . $order->get_order_number() );
				self::suppress( $product->get_id() );
			}
		}
	}

	/**
	 * Query the log with optional product filter.
	 *
	 * @param array $args {
	 *     @type int $product_id Filter by product.
	 *     @type int $paged      Page (1-based).
	 *     @type int $per_page   Rows per page.
	 * }
	 * @return array{items:array,total:int,total_pages:int}
	 */
	public static function query( array $args = array() ) {
		global $wpdb;

		$table    = Installer::stock_log_table();
		$per_page = isset( $args['per_page'] ) ? max( 1, (int) $args['per_page'] ) : 30;
		$paged    = isset( $args['paged'] ) ? max( 1, (int) $args['paged'] ) : 1;
		$offset   = ( $paged - 1 ) * $per_page;

		$where  = array( '1=1' );
		$params = array();
		if ( ! empty( $args['product_id'] ) ) {
			$where[]  = 'product_id = %d';
			$params[] = (int) $args['product_id'];
		}
		$where_sql = implode( ' AND ', $where );

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$count_sql = "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}";
		$total     = (int) $wpdb->get_var( $params ? $wpdb->prepare( $count_sql, $params ) : $count_sql );

		$list_sql    = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY created_at DESC LIMIT %d OFFSET %d";
		$list_params = array_merge( $params, array( $per_page, $offset ) );
		$rows        = $wpdb->get_results( $wpdb->prepare( $list_sql, $list_params ), ARRAY_A );
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		return array(
			'items'       => is_array( $rows ) ? $rows : array(),
			'total'       => $total,
			'total_pages' => (int) ceil( $total / $per_page ),
		);
	}

	/**
	 * Purge entries past the retention window.
	 *
	 * @return void
	 */
	public function purge() {
		$days = (int) Settings::value( 'log_retention_days' );
		if ( $days <= 0 ) {
			return;
		}
		global $wpdb;
		$table  = Installer::stock_log_table();
		$cutoff = gmdate( 'Y-m-d H:i:s', time() - ( $days * DAY_IN_SECONDS ) );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE created_at < %s", $cutoff ) );
	}

	/**
	 * Clear the purge cron. Called on deactivate.
	 *
	 * @return void
	 */
	public static function unschedule() {
		$ts = wp_next_scheduled( self::CRON_HOOK );
		if ( $ts ) {
			wp_unschedule_event( $ts, self::CRON_HOOK );
		}
	}
}
