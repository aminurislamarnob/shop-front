<?php

namespace PluginizeLab\ShopFront\Order;

/**
 * Plugin order controller class
 */
class OrderController {

	/**
	 * The constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_msfc_add_order_note', array( $this, 'handle_add_order_note' ) );
		add_action( 'wp_ajax_msfc_delete_order_note', array( $this, 'handle_delete_order_note' ) );
		add_action( 'wp_ajax_msfc_add_shipping_to_order', array( $this, 'msfc_add_shipping_to_order' ) );
		add_action( 'wp_ajax_msfc_set_customer_to_order', array( $this, 'msfc_set_customer_to_order' ) );
		add_action( 'wp_ajax_msfc_create_order', array( $this, 'msfc_create_order' ) );
	}

	/**
	 * Handle the AJAX request for adding a new order note.
	 */
	public function handle_add_order_note() {

		// Verify the nonce.
		if ( ! isset( $_POST['msfc_add_order_note_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['msfc_add_order_note_nonce'] ), '_msfc_add_order_note_' ) ) {
			wp_send_json_error( array( 'error' => __( 'Nonce verification failed', 'shop-front' ) ) );
		}

		// Check user permissions.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to perform this action.', 'shop-front' ) ) );
		}

		// Validate inputs.
		$order_id         = absint( $_POST['order_id'] );
		$order_note       = wp_kses_post( trim( wp_unslash( $_POST['order_note'] ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$note_type        = wc_clean( wp_unslash( $_POST['order_note_type'] ) );
		$is_customer_note = ( 'customer' === $note_type ) ? 1 : 0;

		if ( empty( $order_note ) ) {
			wp_send_json_error( array( 'error' => __( 'Order note is required', 'shop-front' ) ) );
		}

		if ( $order_id > 0 ) {
			$order      = wc_get_order( $order_id );
			$comment_id = $order->add_order_note( $order_note, $is_customer_note, true );
			$note       = wc_get_order_note( $comment_id );

			$note_classes   = array( 'note' );
			$note_classes[] = $is_customer_note ? 'customer-note' : '';
			$note_classes   = apply_filters( 'woocommerce_order_note_class', array_filter( $note_classes ), $note );

			// Capture the <li> markup.
			ob_start();
			?>
			<li rel="<?php echo absint( $note->id ); ?>" data-id="<?php echo absint( $note->id ); ?>" class="<?php echo esc_attr( implode( ' ', $note_classes ) ); ?>">
				<div class="note_content">
				<?php echo wp_kses_post( wpautop( wptexturize( make_clickable( $note->content ) ) ) ); ?>
				</div>
				<p class="meta">
					<abbr class="exact-date" title="<?php echo esc_attr( $note->date_created->date( 'Y-m-d H:i:s' ) ); ?>">
					<?php
					/* translators: $1: Date created, $2 Time created */
					printf(
						esc_html__( 'added on %1$s at %2$s', 'woocommerce' ),
						esc_html( $note->date_created->date_i18n( wc_date_format() ) ),
						esc_html( $note->date_created->date_i18n( wc_time_format() ) )
					);
					?>
					</abbr>
						<?php
						if ( 'system' !== $note->added_by ) :
							/* translators: %s: note author */
							printf( ' ' . esc_html__( 'by %s', 'woocommerce' ), esc_html( $note->added_by ) );
						endif;
						?>
					<a href="#" class="delete_note" x-on:click.prevent="handleDeleteNote" role="button"><?php esc_html_e( 'Delete note', 'woocommerce' ); ?></a>
				</p>
			</li>
			<?php
			$note_html = ob_get_clean();

			// Send JSON success with the HTML.
			wp_send_json_success(
				array(
					'message'   => __( 'Order note successfully created', 'shop-front' ),
					'note_id'   => absint( $note->id ),
					'note_html' => $note_html,
				)
			);
		}
	}

	/**
	 * Delete order note via ajax.
	 */
	public static function handle_delete_order_note() {
		// Verify the nonce.
		if ( ! isset( $_POST['msfc_delete_order_note_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['msfc_delete_order_note_nonce'] ), '_msfc_delete_nonce_' ) ) {
			wp_send_json_error( array( 'error' => __( 'Nonce verification failed', 'shop-front' ) ) );
		}

		if ( ! current_user_can( 'manage_woocommerce' ) || ! isset( $_POST['note_id'] ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to perform this action.', 'shop-front' ) ) );
		}

		$note_id = (int) $_POST['note_id'];

		$is_deleted = false;
		if ( $note_id > 0 ) {
			$is_deleted = wc_delete_order_note( $note_id );
		}

		if ( ! $is_deleted ) {
			wp_send_json_error( array( 'error' => __( 'Failed to delete brand', 'shop-front' ) ) );
		} else {
			wp_send_json_success( array( 'message' => __( 'Note successfully deleted', 'shop-front' ) ) );
		}
	}

	/**
	 * Add shipping to order
	 */
	public function msfc_add_shipping_to_order() {
		// Verify nonce
		check_ajax_referer( 'order-item', 'security' );

		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			wp_die( -1 );
		}

		$response = array();

		try {
			$order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
			$order    = wc_get_order( $order_id );

			if ( ! $order ) {
				throw new \Exception( __( 'Invalid order', 'woocommerce' ) );
			}

			$shipping_method_title = isset($_POST['shipping_method_title']) ? sanitize_text_field($_POST['shipping_method_title']) : __('Shipping', 'shop-front');
			$shipping_method_id = isset($_POST['shipping_method']) ? sanitize_text_field($_POST['shipping_method']) : '';
			$shipping_cost = isset($_POST['shipping_cost']) ? floatval($_POST['shipping_cost']) : 0;
			
			// Create shipping item
			$shipping_item = new \WC_Order_Item_Shipping();
			$shipping_item->set_method_title($shipping_method_title);
			$shipping_item->set_method_id($shipping_method_id);
			$shipping_item->set_total($shipping_cost);
			
			// Add to order
			$order->add_item($shipping_item);
			$order->calculate_totals();
			$order->save();

			ob_start();
			include WC()->plugin_path() . '/includes/admin/meta-boxes/views/html-order-items.php';
			$response['html'] = ob_get_clean();
		} catch ( \Exception $e ) {
			wp_send_json_error( array( 'error' => $e->getMessage() ) );
		}

		// wp_send_json_success must be outside the try block not to break phpunit tests.
		wp_send_json_success( $response );
	}

	public function msfc_set_customer_to_order(){
		// Verify nonce
		check_ajax_referer( 'order-item', 'security' );

		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			wp_die( -1 );
		}

		$response = array();

		try {
			$order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
			$order    = wc_get_order( $order_id );
			
			if ( ! $order ) {
				throw new \Exception( __( 'Invalid order', 'woocommerce' ) );
			}

			// Set customer id
			if ( ! is_null( $_POST['customer_id'] ) ) {
				$order->set_customer_id( is_numeric( $_POST['customer_id'] ) ? absint( $_POST['customer_id'] ) : 0 );
			}

			$order->save();

			$response = array(
				'order_id'       => $order_id,
				'customer_id' => $order->get_customer_id(),
			);
		} catch ( \Exception $e ) {
			wp_send_json_error( array( 'error' => $e->getMessage() ) );
		}

		// wp_send_json_success must be outside the try block not to break phpunit tests.
		wp_send_json_success( $response );
	}

	public function msfc_create_order(){
		// Verify nonce
		check_ajax_referer( 'order-item', 'security' );

		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			wp_die( -1 );
		}

		$response = array();

		try {
			$order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
			$order    = wc_get_order( $order_id );
			
			if ( ! $order ) {
				throw new \Exception( __( 'Invalid order', 'woocommerce' ) );
			}

			// Handle button actions.
			if ( ! empty( $_POST['order_action'] ) ) { // @codingStandardsIgnoreLine

				$action = wc_clean( wp_unslash( $_POST['order_action'] ) ); // @codingStandardsIgnoreLine

				if ( 'send_order_details' === $action ) {
					/**
					 * Fires before an order email is resent.
					 *
					 * @since 1.0.0
					 */
					do_action( 'woocommerce_before_resend_order_emails', $order, 'customer_invoice' );

					// Send the customer invoice email.
					WC()->payment_gateways();
					WC()->shipping();
					WC()->mailer()->customer_invoice( $order );

					// Note the event.
					$order->add_order_note( __( 'Order details manually sent to customer.', 'woocommerce' ), false, true );

					/**
					 * Fires after an order email has been resent.
					 *
					 * @since 1.0.0
					 */
					do_action( 'woocommerce_after_resend_order_email', $order, 'customer_invoice' );

				} elseif ( 'send_order_details_admin' === $action ) {

					do_action( 'woocommerce_before_resend_order_emails', $order, 'new_order' );

					WC()->payment_gateways();
					WC()->shipping();
					add_filter( 'woocommerce_new_order_email_allows_resend', '__return_true' );
					WC()->mailer()->emails['WC_Email_New_Order']->trigger( $order->get_id(), $order, true );
					remove_filter( 'woocommerce_new_order_email_allows_resend', '__return_true' );

					do_action( 'woocommerce_after_resend_order_email', $order, 'new_order' );

				} elseif ( 'regenerate_download_permissions' === $action ) {

					$data_store = \WC_Data_Store::load( 'customer-download' );
					$data_store->delete_by_order_id( $order_id );
					wc_downloadable_product_permissions( $order_id, true );

				} else {

					if ( ! did_action( 'woocommerce_order_action_' . sanitize_title( $action ) ) ) {
						do_action( 'woocommerce_order_action_' . sanitize_title( $action ), $order );
					}
				}
			}

			// Update date.
			if ( empty( $_POST['order_date'] ) ) {
				$date = time();
			} else {
				if ( ! isset( $_POST['order_date_hour'] ) || ! isset( $_POST['order_date_minute'] ) || ! isset( $_POST['order_date_second'] ) ) {
					throw new \Exception( __( 'Order date, hour, minute and/or second are missing.', 'woocommerce' ), 400 );
				}
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				$date = gmdate( 'Y-m-d H:i:s', strtotime( $_POST['order_date'] . ' ' . (int) $_POST['order_date_hour'] . ':' . (int) $_POST['order_date_minute'] . ':' . (int) $_POST['order_date_second'] ) );
			}

			$order_status = isset($_POST['order_status']) ? sanitize_text_field($_POST['order_status']) : 'wc-pending';

			// Map and set billing address
			$billing_fields = array(
				'first_name', 'last_name', 'company', 'address_1', 'address_2',
				'city', 'postcode', 'country', 'state', 'email', 'phone'
			);

			foreach ( $billing_fields as $field ) {
				$key = '_billing_' . $field;
				if ( isset( $_POST[ $key ] ) ) {
					$setter = "set_billing_{$field}";
					$value  = is_array( $_POST[ $key ] ) ? '' : wc_clean( wp_unslash( $_POST[ $key ] ) );
					if ( method_exists( $order, $setter ) ) {
						$order->$setter( $value );
					}
				}
			}

			// Map and set shipping address
			$shipping_fields = array(
				'first_name','last_name','company','address_1','address_2',
				'city','postcode','country','state','phone'
			);

			foreach ( $shipping_fields as $field ) {
				$key = '_shipping_' . $field;
				if ( isset( $_POST[ $key ] ) ) {
					$setter = "set_shipping_{$field}";
					$value  = is_array( $_POST[ $key ] ) ? '' : wc_clean( wp_unslash( $_POST[ $key ] ) );
					if ( method_exists( $order, $setter ) ) {
						$order->$setter( $value );
					}
				}
			}

			// Set customer note
			if ( isset( $_POST['customer_note'] ) ) {
				$order->set_customer_note( sanitize_textarea_field( wp_unslash( $_POST['customer_note'] ) ) );
			}

			// Set payment method
			if ( isset( $_POST['_payment_method'] ) ) {
				$order->set_payment_method( sanitize_text_field( wp_unslash( $_POST['_payment_method'] ) ) );
			}

			// Set transaction id
			if ( isset( $_POST['_transaction_id'] ) ) {
				$order->set_transaction_id( sanitize_text_field( wp_unslash( $_POST['_transaction_id'] ) ) );
			}

			// Set to order
            $order->set_date_created( $date );
			$order->set_status( $order_status );
			$order->save();

			ob_start();
			include WC()->plugin_path() . '/includes/admin/meta-boxes/views/html-order-items.php';
			$items_html = ob_get_clean();

			ob_start();
			$notes = wc_get_order_notes( array( 'order_id' => $order_id ) );
			include WC()->plugin_path() . '/includes/admin/meta-boxes/views/html-order-notes.php';
			$notes_html = ob_get_clean();

			$response = array(
				'html'       => $items_html,
				'notes_html' => $notes_html,
			);
		} catch ( \Exception $e ) {
			wp_send_json_error( array( 'error' => $e->getMessage() ) );
		}

		// wp_send_json_success must be outside the try block not to break phpunit tests.
		wp_send_json_success( $response );
	}
}
