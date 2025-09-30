<?php
/**
 * MSFC order creation page.
 * ***/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'msf_dashboard_wrapper_start' );

global $theorder;
$order = $theorder;
?>
<div class="my-shop-front-container">
	<aside class="my-shop-front-sidebar">
		<?php do_action( 'msf_dashboard_navigation' ); ?>
	</aside>
	<div class="my-shop-front-wrapper">
		<?php do_action( 'msf_dashboard_content_before' ); ?>
		<main class="my-shop-front-page-content">
			<?php do_action( 'msf_dashboard_before_main_content' ); ?>
			<div class="order-creation-form">
				<form action="" method="post">
					<div class="row">
						<!-- Left Column -->
						<div class="col-md-8">
							<!-- Customer Section -->
							<div class="msf-card">
								<div class="card-title-with-link">
									<h3 class="msf-card-title"><?php esc_html_e( 'Customer', 'shop-front' ); ?></h3>
									<div class="add-customer-link">
										<span class="or-text"><?php esc_html_e( 'Or', 'shop-front' ); ?></span>
										<a href="#" id="add-new-customer-link"><?php esc_html_e( 'add a new customer', 'shop-front' ); ?></a>
									</div>
								</div>
								<div class="msf-card-content">
									<div class="msf-form-group search-group">
										<?php
										$user_string = '';
										$user_id     = '';
										?>
										<select class="wc-customer-search" id="customer_user" name="customer_user" data-placeholder="<?php esc_attr_e( 'Guest', 'woocommerce' ); ?>" data-allow_clear="true">
											<?php
											// phpcs:disable WooCommerce.Commenting.CommentHooks.MissingHookComment
											/**
											 * Filter to customize the display of the currently selected customer for an order in the order edit page.
											 * This is the same filter used in the ajax call for customer search in the same metabox.
											 *
											 * @param array @user_info An array containing one item with the name and email of the user currently selected as the customer for the order.
											 */
											?>
											<option value="<?php echo esc_attr( $user_id ); ?>" selected="selected"><?php echo esc_html( htmlspecialchars( wp_kses_post( current( apply_filters( 'woocommerce_json_search_found_customers', array( $user_string ) ) ) ) ) ); ?></option>
											<?php // phpcs:enable WooCommerce.Commenting.CommentHooks.MissingHookComment ?>
										</select>
									</div>
								<div class="new-customer-fields" id="new-customer-fields" style="display: none;">
									<div class="customer-type-group">
										<label class="radio-label">
											<input type="radio" name="customer_type" value="individual" checked>
											<span><?php esc_html_e( 'Individual', 'shop-front' ); ?></span>
										</label>
										<label class="radio-label">
											<input type="radio" name="customer_type" value="company">
											<span><?php esc_html_e( 'Company', 'shop-front' ); ?></span>
										</label>
									</div>
									<div class="row">
										<div class="col-md-6">
											<div class="msf-form-group">
												<label for="first_name"><?php esc_html_e( 'First Name', 'shop-front' ); ?></label>
												<input type="text" class="msf-form-control" id="first_name" name="first_name" value="John">
											</div>
										</div>
										<div class="col-md-6">
											<div class="msf-form-group">
												<label for="last_name"><?php esc_html_e( 'Last Name', 'shop-front' ); ?></label>
												<input type="text" class="msf-form-control" id="last_name" name="last_name" value="Doe">
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-6">
											<div class="msf-form-group">
												<label for="email"><?php esc_html_e( 'Email Address', 'shop-front' ); ?></label>
												<input type="email" class="msf-form-control" id="email" name="email" value="john.doe@example.com">
											</div>
										</div>
										<div class="col-md-6">
											<div class="msf-form-group">
												<label for="phone"><?php esc_html_e( 'Phone Number', 'shop-front' ); ?></label>
												<input type="tel" class="msf-form-control" id="phone" name="phone" value="+1 (555) 123-4567">
											</div>
										</div>
									</div>
								</div>
								</div>
							</div>
	
							<!-- Products Section -->
							<div class="msf-card product-serach-for-order-box">
								<h3 class="msf-card-title"><?php esc_html_e( 'Products', 'shop-front' ); ?></h3>
								<div class="msf-card-content">
									<div class="msf-form-group search-group">
										<select class="wc-product-search" id="msf_product_search" name="item_id" data-allow_clear="true" data-display_stock="true" data-exclude_type="variable" data-placeholder="<?php echo esc_attr__( 'Search for a product&hellip;', 'shop-front' ); ?>"></select>
									</div>
									<div id="search-order-items" class="products-table">
										<table class="msf-table msf-mb-20">
											<thead>
												<tr>
													<th><?php esc_html_e( 'Product', 'shop-front' ); ?></th>
													<th><?php esc_html_e( 'Quantity', 'shop-front' ); ?></th>
													<th><?php esc_html_e( 'Action', 'shop-front' ); ?></th>
												</tr>
											</thead>
											<tbody></tbody>
										</table>
										<button id="add-to-order-items" type="button" class="my-shop-front-button"><?php esc_html_e( 'Add To Order', 'shop-front' ); ?></button>
									</div>
								</div>
							</div>

							<div id="woocommerce-order-items" class="msf-card msfc-order-items-box">
								<div class="inside"></div>
							</div>
	
							<!-- Discounts & Fees and Order Summary Section -->
							<div class="row">
								<div class="col-md-6">
									<div class="msf-card">
										<h3 class="msf-card-title"><?php esc_html_e( 'Discounts & Fees', 'shop-front' ); ?></h3>
										<div class="msf-card-content">
											<div class="msf-form-group">
												<div class="coupon-group">
													<input type="text" class="msf-form-control" id="coupon_code" placeholder="<?php echo esc_attr__( 'e.g. SUMMER20', 'shop-front' ); ?>">
													<button type="button" class="apply-btn msfc-apply-coupon"><?php esc_html_e( 'Apply Coupon', 'shop-front' ); ?></button>
												</div>
											</div>
											<div class="msf-form-group">
												<div class="coupon-group">
													<input type="text" class="msf-form-control" id="add_fee" placeholder="<?php echo esc_attr__( 'Enter a fixed amount or percentage', 'shop-front' ); ?>">
													<button type="button" class="apply-btn msfc-add-fee"><?php esc_html_e( 'Add Fee', 'shop-front' ); ?></button>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="msf-card">
										<h3 class="msf-card-title"><?php esc_html_e( 'Shipping', 'shop-front' ); ?></h3>
										<div class="msf-card-content">
											<div class="msf-form-group">
												<div class="coupon-group">
													<input type="text" class="shipping_method_title msf-form-control" placeholder="<?php esc_attr_e( 'Shipping name', 'woocommerce' ); ?>" name="msf_shipping_method_title" value="<?php echo esc_attr__( 'Shipping', 'woocommerce' ); ?>" />
													<input type="text" name="msf_shipping_cost" placeholder="0" class="msf-form-control" />
													<select class="shipping_method msf-form-control" name="msf_shipping_method">
														<optgroup label="<?php esc_attr_e( 'Shipping method', 'woocommerce' ); ?>">
															<option value=""><?php esc_html_e( 'N/A', 'woocommerce' ); ?></option>
															<?php
															$found_method = false;
															$shipping_methods = WC()->shipping() ? WC()->shipping()->load_shipping_methods() : array();

															foreach ( $shipping_methods as $method ) {
																echo '<option value="' . esc_attr( $method->id ) . '" ' . selected( true, $is_active, false ) . '>' . esc_html( $method->get_method_title() ) . '</option>';
															}

															echo '<option value="other">' . esc_html__( 'Other', 'woocommerce' ) . '</option>';
															?>
														</optgroup>
													</select>
													<button type="button" class="apply-btn msfc-add-shipping"><?php esc_html_e( 'Add Shipping', 'shop-front' ); ?></button>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
	
							<!-- Billing & Shipping Address Section -->
							<div class="row">
								<div class="col-md-6">
									<div class="msf-card">
										<h3 class="msf-card-title"><?php esc_html_e( 'Billing Address', 'shop-front' ); ?></h3>
										<div class="msf-card-content">
											<div class="row">
												<div class="col-md-6">
													<div class="msf-form-group">
														<label for="_billing_first_name"><?php esc_html_e( 'First Name', 'shop-front' ); ?></label>
														<input type="text" class="msf-form-control" id="_billing_first_name" name="_billing_first_name" value="">
													</div>
												</div>
												<div class="col-md-6">
													<div class="msf-form-group">
														<label for="_billing_last_name"><?php esc_html_e( 'Last Name', 'shop-front' ); ?></label>
														<input type="text" class="msf-form-control" id="_billing_last_name" name="_billing_last_name" value="">
													</div>
												</div>
											</div>
											<div class="msf-form-group">
												<label for="_billing_company"><?php esc_html_e( 'Company', 'shop-front' ); ?></label>
												<input type="text" class="msf-form-control" id="_billing_company" name="_billing_company" value="">
											</div>
											<div class="row">
												<div class="col-md-6">
													<div class="msf-form-group">
														<label for="_billing_address_1"><?php esc_html_e( 'Address Line 1', 'shop-front' ); ?></label>
														<input type="text" class="msf-form-control" id="_billing_address_1" name="_billing_address_1" value="">
													</div>
												</div>
												<div class="col-md-6">
													<div class="msf-form-group">
														<label for="_billing_address_2"><?php esc_html_e( 'Address Line 2', 'shop-front' ); ?></label>
														<input type="text" class="msf-form-control" id="_billing_address_2" name="_billing_address_2" value="CA">
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-md-6">
													<div class="msf-form-group">
														<label for="_billing_city"><?php esc_html_e( 'City', 'shop-front' ); ?></label>
														<input type="text" class="msf-form-control" id="_billing_city" name="_billing_city" value="">
													</div>
												</div>
												<div class="col-md-6">
													<div class="msf-form-group">
														<label for="_billing_postcode"><?php esc_html_e( 'Postcode / ZIP', 'shop-front' ); ?></label>
														<input type="text" class="msf-form-control" id="_billing_postcode" name="_billing_postcode" value="">
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-md-6">
													<div class="msf-form-group">
														<label for="_billing_country"><?php esc_html_e( 'Country / Region', 'shop-front' ); ?></label>
														<select class="msf-form-control" id="_billing_country" name="_billing_country"></select>
													</div>
												</div>
												<div class="col-md-6">
													<div class="msf-form-group">
														<label for="_billing_state"><?php esc_html_e( 'State / County', 'shop-front' ); ?></label>
														<input type="text" class="msf-form-control" id="_billing_state" name="_billing_state" value="">
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-md-6">
													<div class="msf-form-group">
														<label for="_billing_email"><?php esc_html_e( 'Email Address', 'shop-front' ); ?></label>
														<input type="text" class="msf-form-control" id="_billing_email" name="_billing_email" value="">
													</div>
												</div>
												<div class="col-md-6">
													<div class="msf-form-group">
														<label for="_billing_phone"><?php esc_html_e( 'Phone', 'shop-front' ); ?></label>
														<input type="text" class="msf-form-control" id="_billing_phone" name="_billing_phone" value="">
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-md-6">
													<div class="msf-form-group">
														<label for="_payment_method"><?php esc_html_e( 'Payment Method', 'shop-front' ); ?></label>
														<input type="text" class="msf-form-control" id="_payment_method" name="_payment_method" value="">
													</div>
												</div>
												<div class="col-md-6">
													<div class="msf-form-group">
														<label for="_transaction_id"><?php esc_html_e( 'Transaction ID', 'shop-front' ); ?></label>
														<input type="text" class="msf-form-control" id="_transaction_id" name="_transaction_id" value="">
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="msf-card">
										<h3 class="msf-card-title"><?php esc_html_e( 'Shipping Address', 'shop-front' ); ?></h3>
										<div class="msf-card-content">
											<div class="row">
												<div class="col-md-6">
													<div class="msf-form-group">
														<label for="_shipping_first_name"><?php esc_html_e( 'First Name', 'shop-front' ); ?></label>
														<input type="text" class="msf-form-control" id="_shipping_first_name" name="_shipping_first_name" value="">
													</div>
												</div>
												<div class="col-md-6">
													<div class="msf-form-group">
														<label for="_shipping_last_name"><?php esc_html_e( 'Last Name', 'shop-front' ); ?></label>
														<input type="text" class="msf-form-control" id="_shipping_last_name" name="_shipping_last_name" value="">
													</div>
												</div>
											</div>
											<div class="msf-form-group">
												<label for="_shipping_company"><?php esc_html_e( 'Company', 'shop-front' ); ?></label>
												<input type="text" class="msf-form-control" id="_shipping_company" name="_shipping_company" value="">
											</div>
											<div class="row">
												<div class="col-md-6">
													<div class="msf-form-group">
														<label for="_shipping_address_1"><?php esc_html_e( 'Address Line 1', 'shop-front' ); ?></label>
														<input type="text" class="msf-form-control" id="_shipping_address_1" name="_shipping_address_1" value="">
													</div>
												</div>
												<div class="col-md-6">
													<div class="msf-form-group">
														<label for="_shipping_address_2"><?php esc_html_e( 'Address Line 2', 'shop-front' ); ?></label>
														<input type="text" class="msf-form-control" id="_shipping_address_2" name="_shipping_address_2" value="CA">
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-md-6">
													<div class="msf-form-group">
														<label for="_shipping_city"><?php esc_html_e( 'City', 'shop-front' ); ?></label>
														<input type="text" class="msf-form-control" id="_shipping_city" name="_shipping_city" value="">
													</div>
												</div>
												<div class="col-md-6">
													<div class="msf-form-group">
														<label for="_shipping_postcode"><?php esc_html_e( 'Postcode / ZIP', 'shop-front' ); ?></label>
														<input type="text" class="msf-form-control" id="_shipping_postcode" name="_shipping_postcode" value="">
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-md-6">
													<div class="msf-form-group">
														<label for="_shipping_country"><?php esc_html_e( 'Country / Region', 'shop-front' ); ?></label>
														<select class="msf-form-control" id="_shipping_country" name="_shipping_country"></select>
													</div>
												</div>
												<div class="col-md-6">
													<div class="msf-form-group">
														<label for="_shipping_state"><?php esc_html_e( 'State / County', 'shop-front' ); ?></label>
														<input type="text" class="msf-form-control" id="_shipping_state" name="_shipping_state" value="">
													</div>
												</div>
											</div>
											<div class="msf-form-group">
												<label for="_shipping_phone"><?php esc_html_e( 'Phone', 'shop-front' ); ?></label>
												<input type="text" class="msf-form-control" id="_shipping_phone" name="_shipping_phone" value="">
											</div>
											<div class="msf-form-group">
												<label for="customer_note"><?php esc_html_e( 'Customer Provided Note', 'shop-front' ); ?></label>
												<textarea class="msf-form-control" id="customer_note" name="customer_note" placeholder="Enter your note here..." rows="4"></textarea>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
	
						<!-- Right Column -->
						<div class="col-md-4">
							<!-- Action Buttons -->
							<div class="action-buttons">
								<button type="submit" class="create-order-btn"><?php esc_html_e( 'Create Order', 'shop-front' ); ?></button>
								<button type="button" class="save-draft-btn"><?php esc_html_e( 'Save as Draft', 'shop-front' ); ?></button>
							</div>
	
							<!-- General Section -->
							<div class="msf-card">
								<h3 class="msf-card-title"><?php esc_html_e( 'General', 'shop-front' ); ?></h3>
								<div class="msf-card-content">
									<div class="msf-form-group">
										<label for="date_created"><?php esc_html_e( 'Date Created', 'shop-front' ); ?></label>
										<div class="date-time-group">
											<input type="date" class="msf-form-control date-input" id="date_created" name="date_created" value="2025-09-19">
											<span class="date-separator">@</span>
											<input type="number" class="msf-form-control time-input" id="time_hours" name="time_hours" value="8" min="0" max="23" placeholder="HH">
											<span class="time-separator">:</span>
											<input type="number" class="msf-form-control time-input" id="time_minutes" name="time_minutes" value="19" min="0" max="59" placeholder="MM">
										</div>
									</div>
									
									<div class="msf-form-group">
										<label for="order_status"><?php esc_html_e( 'Status', 'shop-front' ); ?></label>
										<select class="msf-form-control" id="order_status" name="order_status">
											<?php
												$statuses = wc_get_order_statuses();
												foreach ( $statuses as $status => $status_name ) {
													echo '<option value="' . esc_attr( $status ) . '">' . esc_html( $status_name ) . '</option>';
												}
											?>
										</select>
									</div>
									<div class="msf-form-group">
										<?php
										$order_id      = $order->get_id();
										$order_actions = PluginizeLab\ShopFront\Order\OrderManager::get_available_order_actions_for_order( $order );
										?>
										<label for="payment_method"><?php esc_html_e( 'Order Actions', 'shop-front' ); ?></label>
										<select class="msf-form-control" id="payment_method" name="payment_method">
											<option value=""><?php esc_html_e( 'Choose an action...', 'shop-front' ); ?></option>
											<?php foreach ( $order_actions as $action => $title ) { ?>
												<option value="<?php echo esc_attr( $action ); ?>"><?php echo esc_html( $title ); ?></option>
											<?php } ?>
										</select>
									</div>
								</div>
							</div>
							<?php
								/**
								 * Action hook fired after the order details action.
								 *
								 * @param WC_Order $order Order data.
								 */
								do_action( 'msfc_after_new_order_actions', $order );
							?>
	
							<!-- Order Notes Section -->
							<div id="new_order_notes" class="msf-card">
								<h3 class="msf-card-title"><?php esc_html_e( 'Order notes', 'shop-front' ); ?></h3>
								<div class="msf-card-content">
									<!-- Existing Notes -->
									<ul class="existing-notes order_notes">
										<li class="note no-items">
											<div class="note_content">
												<p><?php esc_html_e( 'There are no notes yet.', 'shop-front' ); ?></p>
											</div>
										</li>
									</ul>
									
									<!-- Add Note Section -->
									<div class="add-note-section">
										<div class="add-note-header">
											<h4 class="add-note-title"><?php esc_html_e( 'Add Note', 'shop-front' ); ?></h4>
										</div>
										<div class="msf-form-group">
											<textarea id="add_order_note" class="msf-form-control note-textarea" placeholder="<?php echo esc_attr__( 'Enter your note here...', 'shop-front' ); ?>" rows="4"></textarea>
											<small><?php esc_html_e( 'Add a note for your reference, or add a customer note (the user will be notified).', 'shop-front' ); ?></small>
										</div>
										<div class="note-options">
											<div class="msf-form-group">
												<select class="msf-form-control" id="order_note_type">
													<option><?php esc_html_e( 'Internal note', 'shop-front' ); ?></option>
													<option value="customer"><?php esc_html_e( 'Note to customer', 'shop-front' ); ?></option>
												</select>
											</div>
											<button type="button" class="add-note add-note-btn"><?php esc_html_e( 'Add', 'shop-front' ); ?></button>
										</div>
									</div>
								</div>
							</div>
	
						</div>
					</div>
					<input type="hidden" id="post_ID" name="post_ID" value="<?php echo esc_attr( $order->get_id() ); ?>">
				</form>
			</div>
		</main>
	</div>
</div>
<?php do_action( 'msf_dashboard_wrapper_end' ); ?>