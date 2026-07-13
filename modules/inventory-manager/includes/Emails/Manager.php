<?php
/**
 * Inventory Manager — WooCommerce email registration.
 *
 * @package StoreSuite
 */

namespace PluginizeLab\StoreSuite\Modules\InventoryManager\Emails;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the module's emails with WooCommerce so they appear (and are
 * configured) under WooCommerce → Settings → Emails, and keeps the recurring
 * daily-digest action in step with the digest email's enabled state.
 *
 * The WC_Email subclasses are required lazily inside the
 * `woocommerce_email_classes` callback because WC_Email itself is only loaded
 * once the WooCommerce mailer boots.
 */
class Manager {

	const DIGEST_HOOK  = 'storesuite_inventory_digest';
	const QUEUE_OPTION = 'storesuite_inventory_digest_queue';
	const AS_GROUP     = 'storesuite';

	/**
	 * Option WooCommerce stores the digest email's settings under
	 * (`woocommerce_{email_id}_settings`).
	 */
	const DIGEST_SETTINGS_OPTION = 'woocommerce_storesuite_daily_stock_digest_settings';

	/**
	 * Register hooks. Called from Module::boot().
	 *
	 * @return void
	 */
	public function register() {
		add_filter( 'woocommerce_email_classes', array( $this, 'register_emails' ), 35 );
		add_filter( 'woocommerce_email_actions', array( $this, 'register_email_actions' ) );
		add_filter( 'woocommerce_template_directory', array( $this, 'set_template_directory' ), 15, 2 );

		// Fires after the digest email's settings form saves in wp-admin.
		add_action( 'woocommerce_update_options_email_storesuite_daily_stock_digest', array( __CLASS__, 'sync_digest_schedule' ) );

		// Self-heal a missing recurring action while the digest is enabled
		// (Action Scheduler can't accept new actions before `init`).
		if ( self::is_digest_enabled() ) {
			add_action( 'init', array( __CLASS__, 'sync_digest_schedule' ) );
		}
	}

	/**
	 * Add the module's emails to the WooCommerce mailer.
	 *
	 * @param array $emails Email instances keyed by class-name-style ids.
	 * @return array
	 */
	public function register_emails( $emails ) {
		require_once __DIR__ . '/LowStockAlert.php';
		require_once __DIR__ . '/DailyStockDigest.php';

		$emails['StoreSuite_Email_Low_Stock_Alert']    = new LowStockAlert();
		$emails['StoreSuite_Email_Daily_Stock_Digest'] = new DailyStockDigest();

		return $emails;
	}

	/**
	 * Register the digest action so WooCommerce loads the mailer and fires
	 * `{action}_notification` when Action Scheduler runs it. The low/no-stock
	 * actions are already WooCommerce core email actions.
	 *
	 * @param array $actions Email-triggering action hooks.
	 * @return array
	 */
	public function register_email_actions( $actions ) {
		if ( is_array( $actions ) ) {
			$actions[] = self::DIGEST_HOOK;
		}
		return $actions;
	}

	/**
	 * Route theme overrides of our email templates to a `storesuite/`
	 * subfolder instead of `woocommerce/`.
	 *
	 * @param string $template_directory Default directory name.
	 * @param string $template           Template being located.
	 * @return string
	 */
	public function set_template_directory( $template_directory, $template ) {
		$own = array( 'low-stock-alert.php', 'daily-stock-digest.php' );

		if ( in_array( basename( (string) $template ), $own, true ) ) {
			return 'storesuite';
		}

		return $template_directory;
	}

	/**
	 * Whether the digest email is enabled in WooCommerce → Settings → Emails.
	 * Reads the raw option so it works before/without the mailer being loaded.
	 *
	 * @return bool
	 */
	public static function is_digest_enabled() {
		$settings = get_option( self::DIGEST_SETTINGS_OPTION, array() );
		return is_array( $settings ) && isset( $settings['enabled'] ) && 'yes' === $settings['enabled'];
	}

	/**
	 * Reconcile the recurring digest action with the email's enabled state, so
	 * toggling it never strands queued alerts or leaves an orphan action.
	 *
	 * @return void
	 */
	public static function sync_digest_schedule() {
		if ( ! function_exists( 'as_schedule_recurring_action' ) ) {
			return;
		}

		// Clean up the legacy WP-Cron event from pre-Action Scheduler versions.
		$legacy_ts = wp_next_scheduled( self::DIGEST_HOOK );
		if ( $legacy_ts ) {
			wp_unschedule_event( $legacy_ts, self::DIGEST_HOOK );
			\storesuite_log( '[inventory-manager] Migrated digest schedule from WP-Cron to Action Scheduler.', 'info' );
		}

		if ( self::is_digest_enabled() ) {
			if ( false === as_next_scheduled_action( self::DIGEST_HOOK, array(), self::AS_GROUP ) ) {
				as_schedule_recurring_action( time() + HOUR_IN_SECONDS, DAY_IN_SECONDS, self::DIGEST_HOOK, array(), self::AS_GROUP, true );
				\storesuite_log( '[inventory-manager] Scheduled the recurring daily digest action.', 'info' );
			}
			return;
		}

		// Digest disabled — the user opted out of it; discard anything queued.
		$queue = (array) get_option( self::QUEUE_OPTION, array() );
		if ( ! empty( $queue ) ) {
			\storesuite_log(
				sprintf( '[inventory-manager] Digest email disabled with %d queued item(s) — discarding the digest queue.', count( $queue ) ),
				'info'
			);
			delete_option( self::QUEUE_OPTION );
		}

		if ( false !== as_next_scheduled_action( self::DIGEST_HOOK, array(), self::AS_GROUP ) ) {
			as_unschedule_all_actions( self::DIGEST_HOOK, array(), self::AS_GROUP );
			\storesuite_log( '[inventory-manager] Unscheduled the daily digest action (digest email is off).', 'info' );
		}
	}

	/**
	 * Clear the digest schedule. Called on module deactivate.
	 *
	 * @return void
	 */
	public static function unschedule() {
		if ( function_exists( 'as_unschedule_all_actions' ) ) {
			as_unschedule_all_actions( self::DIGEST_HOOK, array(), self::AS_GROUP );
		}

		// Also clear the legacy WP-Cron event from pre-Action Scheduler versions.
		$ts = wp_next_scheduled( self::DIGEST_HOOK );
		if ( $ts ) {
			wp_unschedule_event( $ts, self::DIGEST_HOOK );
		}
	}
}
