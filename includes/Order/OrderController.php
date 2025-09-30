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
}
