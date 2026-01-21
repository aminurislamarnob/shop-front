<?php
/**
 * Order List Filters - Off-Canvas
 *
 * @package ShopFront
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get current filter values
$current_status        = isset( $_GET['order_status'] ) ? sanitize_text_field( wp_unslash( $_GET['order_status'] ) ) : '';
$current_customer_user = isset( $_GET['_customer_user'] ) ? absint( wp_unslash( $_GET['_customer_user'] ) ) : 0;
$current_channel       = isset( $_GET['order_channel'] ) ? sanitize_text_field( wp_unslash( $_GET['order_channel'] ) ) : '';
$current_month         = isset( $_GET['m'] ) ? sanitize_text_field( wp_unslash( $_GET['m'] ) ) : '0';

// Build WooCommerce-style month options
$order_manager  = isset( $orders_obj ) ? $orders_obj : new \PluginizeLab\ShopFront\Order\OrderManager();
$months_options = method_exists( $order_manager, 'get_months_filter_options' ) ? $order_manager->get_months_filter_options() : array();
global $wp_locale;
?>

<div class="msf-filter-offcanvas-overlay" id="msf-order-filter-overlay"></div>
<div class="msf-filter-offcanvas" id="msf-order-filter-offcanvas">
	<div class="msf-filter-offcanvas-header">
		<h3><?php esc_html_e( 'Filter Orders', 'shop-front' ); ?></h3>
		<button type="button" class="msf-filter-close" id="msf-order-filter-close">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
				<path d="M18,6h0a1,1,0,0,0-1.414,0L12,10.586,7.414,6A1,1,0,0,0,6,6H6a1,1,0,0,0,0,1.414L10.586,12,6,16.586A1,1,0,0,0,6,18h0a1,1,0,0,0,1.414,0L12,13.414,16.586,18A1,1,0,0,0,18,18h0a1,1,0,0,0,0-1.414L13.414,12,18,7.414A1,1,0,0,0,18,6Z"/>
			</svg>
		</button>
	</div>

	<div class="msf-filter-offcanvas-body">
		<form method="get" class="msf-filters-form-offcanvas">
			<!-- Filter by Order Status -->
			<div class="msf-form-group">
				<label><?php esc_html_e( 'Order Status', 'shop-front' ); ?></label>
				<select name="order_status" class="msf-form-control">
					<option value=""><?php esc_html_e( 'All Statuses', 'shop-front' ); ?></option>
					<?php
					$order_statuses = wc_get_order_statuses();
					foreach ( $order_statuses as $status_key => $status_label ) {
						// Remove 'wc-' prefix for value
						$status_value = str_replace( 'wc-', '', $status_key );
						printf(
							'<option value="%s" %s>%s</option>',
							esc_attr( $status_value ),
							selected( $current_status, $status_value, false ),
							esc_html( $status_label )
						);
					}
					?>
				</select>
			</div>

			<!-- Filter by Registered Customer -->
			<div class="msf-form-group">
				<label><?php esc_html_e( 'Registered Customer', 'shop-front' ); ?></label>
				<?php
				$user_string = '';
				$user_id     = '';

		        // phpcs:disable WordPress.Security.NonceVerification.Recommended
				if ( ! empty( $_GET['_customer_user'] ) ) {
					$user_id = absint( $_GET['_customer_user'] );
					$user    = get_user_by( 'id', $user_id );

					$user_string = sprintf(
						/* translators: 1: user display name 2: user ID 3: user email */
						esc_html__( '%1$s (#%2$s &ndash; %3$s)', 'woocommerce' ),
						$user->display_name,
						absint( $user->ID ),
						$user->user_email
					);
				}

				// Note: use of htmlspecialchars (below) is to prevent XSS when rendered by selectWoo.
				?>
				<select class="wc-customer-search msf-form-control" name="_customer_user" data-placeholder="<?php esc_attr_e( 'Filter by registered customer', 'woocommerce' ); ?>" data-allow_clear="true">
					<option value="<?php echo esc_attr( $user_id ); ?>" selected="selected"><?php echo htmlspecialchars( wp_kses_post( $user_string ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></option>
				</select>
			</div>

			<!-- Filter by Sales Channel -->
			<div class="msf-form-group">
				<label><?php esc_html_e( 'Sales Channel', 'shop-front' ); ?></label>
				<select name="order_channel" class="msf-form-control">
					<?php
					$created_via_options = array(
						''                   => __( 'All sales channels', 'woocommerce' ),
						'admin'              => __( 'Admin', 'woocommerce' ),
						'checkout,store-api' => __( 'Checkout', 'woocommerce' ),
						'pos-rest-api'       => __( 'Point of Sale', 'woocommerce' ),
					);
					foreach ( $created_via_options as $value => $label ) {
						printf(
							'<option value="%s" %s>%s</option>',
							esc_attr( $value ),
							selected( $current_channel, $value, false ),
							esc_html( $label )
						);
					}
					?>
				</select>
			</div>

			<!-- Filter by Date (WooCommerce month dropdown: m=YYYYMM) -->
			<div class="msf-form-group">
				<label><?php esc_html_e( 'Date', 'shop-front' ); ?></label>
				<select name="m" id="filter-by-date" class="msf-form-control">
					<option value="0" <?php selected( (int) $current_month, 0, true ); ?>><?php esc_html_e( 'All dates', 'shop-front' ); ?></option>
					<?php
					foreach ( $months_options as $option ) {
						$month           = zeroise( $option->month, 2 );
						$month_year_text = sprintf(
							/* translators: 1: Month name, 2: 4-digit year. */
							esc_html_x( '%1$s %2$d', 'order dates dropdown', 'shop-front' ),
							$wp_locale->get_month( $month ),
							$option->year
						);
						$value = $option->year . $month;
						printf(
							'<option %1$s value="%2$s">%3$s</option>' . "\n",
							selected( $current_month, $value, false ),
							esc_attr( $value ),
							esc_html( $month_year_text )
						);
					}
					?>
				</select>
			</div>

			<div class="msf-filter-offcanvas-footer">
				<button type="submit" class="my-shop-front-button"><?php esc_html_e( 'Apply Filters', 'shop-front' ); ?></button>
				<a href="?" class="my-shop-front-button msf-filter-reset"><?php esc_html_e( 'Reset', 'shop-front' ); ?></a>
			</div>
		</form>
	</div>
</div>
