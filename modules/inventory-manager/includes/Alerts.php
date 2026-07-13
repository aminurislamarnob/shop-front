<?php
/**
 * Inventory Manager — low-stock email alerts.
 *
 * @package StoreSuite
 */

namespace PluginizeLab\StoreSuite\Modules\InventoryManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Emails store staff when a product hits its low/no-stock threshold, hooking
 * WooCommerce's native woocommerce_low_stock / woocommerce_no_stock actions
 * (fired on order-driven stock reduction). Supports immediate per-product
 * emails or a batched daily digest, with a per-product 24h de-dupe so an
 * oscillating SKU can't spam.
 */
class Alerts {

	const DIGEST_HOOK  = 'storesuite_inventory_digest';
	const QUEUE_OPTION = 'storesuite_inventory_digest_queue';
	const AS_GROUP     = 'storesuite';

	/**
	 * Register hooks. Called from Module::boot().
	 *
	 * @return void
	 */
	public function register() {
		if ( ! (bool) Settings::value( 'enable_alerts' ) ) {
			return;
		}

		add_action( 'woocommerce_low_stock', array( $this, 'on_low_stock' ) );
		add_action( 'woocommerce_no_stock', array( $this, 'on_no_stock' ) );

		if ( 'daily' === Settings::value( 'alert_mode' ) ) {
			add_action( self::DIGEST_HOOK, array( $this, 'send_digest' ) );
			// Action Scheduler can't accept new actions before `init`.
			add_action( 'init', array( __CLASS__, 'sync_schedule' ) );
		}
	}

	/**
	 * Reconcile the recurring digest action with the current settings. Runs on
	 * `init` while in daily mode and directly after every settings save, so
	 * switching modes never strands queued alerts or leaves an orphan action.
	 *
	 * @return void
	 */
	public static function sync_schedule() {
		if ( ! function_exists( 'as_schedule_recurring_action' ) ) {
			return;
		}

		// Clean up the legacy WP-Cron event from pre-Action Scheduler versions.
		$legacy_ts = wp_next_scheduled( self::DIGEST_HOOK );
		if ( $legacy_ts ) {
			wp_unschedule_event( $legacy_ts, self::DIGEST_HOOK );
			\storesuite_log( '[inventory-manager] Migrated digest schedule from WP-Cron to Action Scheduler.', 'info' );
		}

		$alerts_on = (bool) Settings::value( 'enable_alerts' );
		$daily_on  = $alerts_on && 'daily' === Settings::value( 'alert_mode' );

		if ( $daily_on ) {
			if ( false === as_next_scheduled_action( self::DIGEST_HOOK, array(), self::AS_GROUP ) ) {
				as_schedule_recurring_action( time() + HOUR_IN_SECONDS, DAY_IN_SECONDS, self::DIGEST_HOOK, array(), self::AS_GROUP, true );
				\storesuite_log( '[inventory-manager] Scheduled the recurring daily digest action.', 'info' );
			}
			return;
		}

		// Leaving daily mode: don't strand anything already queued.
		$queue = (array) get_option( self::QUEUE_OPTION, array() );
		if ( ! empty( $queue ) ) {
			if ( $alerts_on ) {
				// Switched to immediate — flush the pending digest one last time.
				\storesuite_log(
					sprintf( '[inventory-manager] Alert mode left daily with %d queued item(s) — flushing a final digest.', count( $queue ) ),
					'info'
				);
				( new self() )->send_digest();
			} else {
				// Alerts disabled — the user opted out of emails; discard quietly.
				\storesuite_log(
					sprintf( '[inventory-manager] Alerts disabled with %d queued item(s) — discarding the digest queue.', count( $queue ) ),
					'info'
				);
				delete_option( self::QUEUE_OPTION );
			}
		}

		if ( false !== as_next_scheduled_action( self::DIGEST_HOOK, array(), self::AS_GROUP ) ) {
			as_unschedule_all_actions( self::DIGEST_HOOK, array(), self::AS_GROUP );
			\storesuite_log( '[inventory-manager] Unscheduled the daily digest action (daily mode is off).', 'info' );
		}
	}

	/**
	 * @param \WC_Product $product Product at low stock.
	 * @return void
	 */
	public function on_low_stock( $product ) {
		$this->handle( $product, 'low' );
	}

	/**
	 * @param \WC_Product $product Product out of stock.
	 * @return void
	 */
	public function on_no_stock( $product ) {
		$this->handle( $product, 'out' );
	}

	/**
	 * Dispatch immediately or queue for the digest, respecting de-dupe.
	 *
	 * @param \WC_Product $product Product.
	 * @param string      $level   low|out.
	 * @return void
	 */
	private function handle( $product, $level ) {
		if ( ! $product instanceof \WC_Product ) {
			return;
		}

		$id       = $product->get_id();
		$dedupe   = 'storesuite_low_stock_sent_' . $id;
		if ( get_transient( $dedupe ) ) {
			\storesuite_log(
				sprintf( '[inventory-manager] Skipping %1$s alert for product #%2$d — already alerted within the last 24h.', $level, $id ),
				'debug'
			);
			return;
		}
		set_transient( $dedupe, 1, DAY_IN_SECONDS );

		if ( 'daily' === Settings::value( 'alert_mode' ) ) {
			$queue        = (array) get_option( self::QUEUE_OPTION, array() );
			$queue[ $id ] = array(
				'name'  => $product->get_name(),
				'sku'   => $product->get_sku(),
				'qty'   => $product->get_stock_quantity(),
				'level' => $level,
			);
			update_option( self::QUEUE_OPTION, $queue );
			\storesuite_log(
				sprintf( '[inventory-manager] Queued product #%1$d (%2$s) for daily digest; queue now holds %3$d item(s).', $id, $level, count( $queue ) ),
				'info'
			);
			return;
		}

		\storesuite_log(
			sprintf( '[inventory-manager] Dispatching immediate %1$s-stock alert for product #%2$d.', $level, $id ),
			'info'
		);

		$this->send(
			$level === 'out'
				? __( 'Product out of stock', 'storesuite' )
				: __( 'Product low on stock', 'storesuite' ),
			$this->single_body( $product, $level )
		);
	}

	/**
	 * Send the batched daily digest.
	 *
	 * @return void
	 */
	public function send_digest() {
		$queue = (array) get_option( self::QUEUE_OPTION, array() );
		if ( empty( $queue ) ) {
			\storesuite_log( '[inventory-manager] Daily digest cron ran but the queue is empty — nothing to send.', 'debug' );
			return;
		}

		$lines = array( '<ul>' );
		foreach ( $queue as $item ) {
			$status  = ( 'out' === ( $item['level'] ?? 'low' ) )
				? __( 'out of stock', 'storesuite' )
				: __( 'low on stock', 'storesuite' );
			$lines[] = '<li>' . esc_html( $item['name'] ) . ' (' . esc_html( $item['sku'] ) . ') — ' . esc_html( $status ) . ', ' . esc_html( (string) $item['qty'] ) . ' ' . esc_html__( 'left', 'storesuite' ) . '</li>';
		}
		$lines[] = '</ul>';

		\storesuite_log(
			sprintf( '[inventory-manager] Sending daily digest for %d product(s).', count( $queue ) ),
			'info'
		);
		$this->send( __( 'Daily low-stock digest', 'storesuite' ), implode( "\n", $lines ) );
		delete_option( self::QUEUE_OPTION );
	}

	/**
	 * Single-product email body.
	 *
	 * @param \WC_Product $product Product.
	 * @param string      $level   low|out.
	 * @return string
	 */
	private function single_body( $product, $level ) {
		$status = 'out' === $level ? __( 'is out of stock', 'storesuite' ) : __( 'is low on stock', 'storesuite' );
		return sprintf(
			'<p>%s</p>',
			sprintf(
				/* translators: 1: product name, 2: status phrase, 3: quantity */
				esc_html__( '%1$s %2$s (current quantity: %3$s).', 'storesuite' ),
				esc_html( $product->get_name() ),
				esc_html( $status ),
				esc_html( (string) $product->get_stock_quantity() )
			)
		);
	}

	/**
	 * Send an alert email to the configured recipients.
	 *
	 * @param string $subject Subject.
	 * @param string $body    HTML body.
	 * @return void
	 */
	private function send( $subject, $body ) {
		$recipients = (string) Settings::value( 'alert_recipients' );
		$to         = array();
		$invalid    = array();
		foreach ( explode( ',', $recipients ) as $email ) {
			$email = sanitize_email( trim( $email ) );
			if ( is_email( $email ) ) {
				$to[] = $email;
			} elseif ( '' !== trim( $email ) ) {
				$invalid[] = trim( $email );
			}
		}

		if ( ! empty( $invalid ) ) {
			\storesuite_log(
				sprintf( '[inventory-manager] Ignored %1$d invalid recipient address(es): %2$s', count( $invalid ), implode( ', ', $invalid ) ),
				'warning'
			);
		}

		if ( empty( $to ) ) {
			$to[] = get_option( 'admin_email' );
			\storesuite_log(
				sprintf( '[inventory-manager] No valid custom recipients configured — falling back to site admin email (%s).', $to[0] ),
				'info'
			);
		}

		$content = ( function_exists( 'WC' ) && WC()->mailer() )
			? WC()->mailer()->wrap_message( $subject, $body )
			: $body;

		// Capture any wp_mail failure (e.g. PHPMailer exception) for this send.
		$mail_error = '';
		$capture    = function ( $wp_error ) use ( &$mail_error ) {
			$mail_error = $wp_error instanceof \WP_Error ? $wp_error->get_error_message() : '';
		};
		add_action( 'wp_mail_failed', $capture );

		$sent = wp_mail( $to, $subject, $content, array( 'Content-Type: text/html; charset=UTF-8' ) );

		remove_action( 'wp_mail_failed', $capture );

		if ( $sent ) {
			\storesuite_log(
				sprintf( '[inventory-manager] Alert email "%1$s" handed to wp_mail for: %2$s', $subject, implode( ', ', $to ) ),
				'info'
			);
		} else {
			\storesuite_log(
				sprintf(
					'[inventory-manager] wp_mail FAILED to send "%1$s" to: %2$s%3$s',
					$subject,
					implode( ', ', $to ),
					'' !== $mail_error ? ' — ' . $mail_error : ''
				),
				'error'
			);
		}
	}

	/**
	 * Clear the digest cron. Called on deactivate.
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
