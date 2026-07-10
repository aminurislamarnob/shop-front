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
			if ( ! wp_next_scheduled( self::DIGEST_HOOK ) ) {
				wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::DIGEST_HOOK );
			}
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
			return;
		}

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
			return;
		}

		$lines = array( '<ul>' );
		foreach ( $queue as $item ) {
			$lines[] = '<li>' . esc_html( $item['name'] ) . ' (' . esc_html( $item['sku'] ) . ') — ' . esc_html( (string) $item['qty'] ) . '</li>';
		}
		$lines[] = '</ul>';

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
		foreach ( explode( ',', $recipients ) as $email ) {
			$email = sanitize_email( trim( $email ) );
			if ( is_email( $email ) ) {
				$to[] = $email;
			}
		}
		if ( empty( $to ) ) {
			$to[] = get_option( 'admin_email' );
		}

		$content = ( function_exists( 'WC' ) && WC()->mailer() )
			? WC()->mailer()->wrap_message( $subject, $body )
			: $body;

		wp_mail( $to, $subject, $content, array( 'Content-Type: text/html; charset=UTF-8' ) );
	}

	/**
	 * Clear the digest cron. Called on deactivate.
	 *
	 * @return void
	 */
	public static function unschedule() {
		$ts = wp_next_scheduled( self::DIGEST_HOOK );
		if ( $ts ) {
			wp_unschedule_event( $ts, self::DIGEST_HOOK );
		}
	}
}
