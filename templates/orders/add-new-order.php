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
			<div class="msfc-dashboard-order-details">
				<form action="" method="post">
					<div class="row">
						<!-- Left Column -->
						<div class="col-md-8">
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
							<div class="order-fee-and-shipping-box<?php echo $order->get_item_count() > 0 ? ' active' : ''; ?>">
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
							</div>
	
							<!-- Billing & Shipping Address Section -->
							<div class="row">
								<div class="col-md-6">
									<div class="msf-card customer-address-box">
										<h3 class="msf-card-title">
											<?php esc_html_e( 'Billing Address', 'shop-front' ); ?>
											<button class="edit-msf-order-address edit-msf-order-billing-address"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="m19,0H5C2.243,0,0,2.243,0,5v14c0,2.757,2.243,5,5,5h14c2.757,0,5-2.243,5-5V5c0-2.757-2.243-5-5-5Zm3,19c0,1.654-1.346,3-3,3H5c-1.654,0-3-1.346-3-3V5c0-1.654,1.346-3,3-3h14c1.654,0,3,1.346,3,3v14ZM13.879,6.379l-6.707,6.707c-.755.755-1.172,1.76-1.172,2.828v1.586c0,.553.448,1,1,1h1.586c1.068,0,2.073-.416,2.828-1.172l6.707-6.707c1.17-1.17,1.17-3.072,0-4.242-1.134-1.133-3.11-1.133-4.243,0Zm-3.879,9.535c-.373.372-.888.586-1.414.586h-.586v-.586c0-.534.208-1.036.586-1.414l4.25-4.25,1.414,1.414-4.25,4.25Zm6.707-6.707l-1.043,1.043-1.414-1.414,1.043-1.043c.377-.379,1.036-.379,1.414,0,.39.39.39,1.024,0,1.414Z"/></svg></button>
										</h3>
										<div class="customer-billing-address">
											<ul>
												<li class="_billing_first_name">
													<strong><?php esc_html_e( 'Full Name', 'shop-front' ); ?>:</strong>
													<span></span>
												</li>
												<li class="_billing_company">
													<strong><?php esc_html_e( 'Company', 'shop-front' ); ?>:</strong>
													<span></span>
												</li>
												<li class="_billing_address_1">
													<strong><?php esc_html_e( 'Address Line 1', 'shop-front' ); ?>:</strong>
													<span></span>
												</li>
												<li class="_billing_address_2">
													<strong><?php esc_html_e( 'Address Line 2', 'shop-front' ); ?>:</strong>
													<span></span>
												</li>
												<li class="_billing_city">
													<strong><?php esc_html_e( 'City', 'shop-front' ); ?>:</strong>
													<span></span>
												</li>
												<li class="_billing_postcode">
													<strong><?php esc_html_e( 'Postcode / ZIP', 'shop-front' ); ?>:</strong>
													<span></span>
												</li>
												<li class="_billing_country">
													<strong><?php esc_html_e( 'Country / Region', 'shop-front' ); ?>:</strong>
													<span></span>
												</li>
												<li class="_billing_state">
													<strong><?php esc_html_e( 'State / County', 'shop-front' ); ?>:</strong>
													<span></span>
												</li>
												<li class="_billing_email">
													<strong><?php esc_html_e( 'Email Address', 'shop-front' ); ?>:</strong>
													<span></span>
												</li>
												<li class="_billing_phone">
													<strong><?php esc_html_e( 'Phone', 'shop-front' ); ?>:</strong>
													<span></span>
												</li>
											</ul>
										</div>
										<div class="msf-card-content msf-billing-address-fields">
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
														<select class="msf-form-control js_field-country" id="_billing_country" name="_billing_country">
															<option value=""><?php esc_html_e( 'Select a country...', 'shop-front' ); ?></option>
															<?php
																$countries = WC()->countries->get_countries();
																$selected_billing_country = $order ? $order->get_billing_country() : '';
																
																foreach ( $countries as $code => $name ) {
																	printf(
																		'<option value="%s" %s>%s</option>',
																		esc_attr( $code ),
																		selected( $selected_billing_country, $code, false ),
																		esc_html( $name )
																	);
																}
															?>
														</select>
													</div>
												</div>
												<div class="col-md-6">
													<div class="msf-form-group">
														<label for="_billing_state"><?php esc_html_e( 'State / County', 'shop-front' ); ?></label>
														<?php
														$billing_state   = $order ? $order->get_billing_state() : '';
														$states          = WC()->countries->get_states( $selected_billing_country );
														
														if ( ! empty( $states ) ) {
															?>
															<select class="msf-form-control js_field-state" id="_billing_state" name="_billing_state">
																<option value=""><?php esc_html_e( 'Select a state...', 'shop-front' ); ?></option>
																<?php
																foreach ( $states as $code => $name ) {
																	printf(
																		'<option value="%s" %s>%s</option>',
																		esc_attr( $code ),
																		selected( $billing_state, $code, false ),
																		esc_html( $name )
																	);
																}
																?>
															</select>
															<?php
														} else {
															?>
															<input 
																type="text" 
																class="msf-form-control js_field-state" 
																id="_billing_state" 
																name="_billing_state" 
																value="<?php echo esc_attr( $billing_state ); ?>"
																placeholder="<?php esc_attr_e( 'State / County', 'shop-front' ); ?>"
															/>
															<?php
														}
														?>
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
														<select name="_payment_method" id="_payment_method" class=" msf-form-control">
															<option value=""><?php esc_html_e( 'N/A', 'woocommerce' ); ?></option>
															<?php
															if ( WC()->payment_gateways() ) {
																$payment_gateways = WC()->payment_gateways->payment_gateways();
															} else {
																$payment_gateways = array();
															}
															$payment_method = $order->get_payment_method();
															$found_method = false;

															foreach ( $payment_gateways as $gateway ) {
																if ( 'yes' === $gateway->enabled ) {
																	echo '<option value="' . esc_attr( $gateway->id ) . '" ' . selected( $payment_method, $gateway->id, false ) . '>' . esc_html( $gateway->get_title() ) . '</option>';
																	if ( $payment_method === $gateway->id ) {
																		$found_method = true;
																	}
																}
															}

															if ( ! $found_method && ! empty( $payment_method ) ) {
																echo '<option value="' . esc_attr( $payment_method ) . '" selected="selected">' . esc_html__( 'Other', 'woocommerce' ) . '</option>';
															} else {
																echo '<option value="other">' . esc_html__( 'Other', 'woocommerce' ) . '</option>';
															}
															?>
														</select>
													</div>
												</div>
												<div class="col-md-6">
													<div class="msf-form-group">
														<label for="_transaction_id"><?php esc_html_e( 'Transaction ID', 'shop-front' ); ?></label>
														<input type="text" class="msf-form-control" id="_transaction_id" name="_transaction_id" value="<?php echo esc_attr( $order->get_transaction_id() ); ?>">
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="msf-card customer-address-box">
										<h3 class="msf-card-title">
											<?php esc_html_e( 'Shipping Address', 'shop-front' ); ?>
											<button class="edit-msf-order-address edit-msf-order-shipping-address"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="m19,0H5C2.243,0,0,2.243,0,5v14c0,2.757,2.243,5,5,5h14c2.757,0,5-2.243,5-5V5c0-2.757-2.243-5-5-5Zm3,19c0,1.654-1.346,3-3,3H5c-1.654,0-3-1.346-3-3V5c0-1.654,1.346-3,3-3h14c1.654,0,3,1.346,3,3v14ZM13.879,6.379l-6.707,6.707c-.755.755-1.172,1.76-1.172,2.828v1.586c0,.553.448,1,1,1h1.586c1.068,0,2.073-.416,2.828-1.172l6.707-6.707c1.17-1.17,1.17-3.072,0-4.242-1.134-1.133-3.11-1.133-4.243,0Zm-3.879,9.535c-.373.372-.888.586-1.414.586h-.586v-.586c0-.534.208-1.036.586-1.414l4.25-4.25,1.414,1.414-4.25,4.25Zm6.707-6.707l-1.043,1.043-1.414-1.414,1.043-1.043c.377-.379,1.036-.379,1.414,0,.39.39.39,1.024,0,1.414Z"/></svg></button>
										</h3>
										<div class="customer-shipping-address">
											<ul>
												<li class="_shipping_first_name">
													<strong><?php esc_html_e( 'Full Name', 'shop-front' ); ?>:</strong>
													<span></span>
												</li>
												<li class="_shipping_company">
													<strong><?php esc_html_e( 'Company', 'shop-front' ); ?>:</strong>
													<span></span>
												</li>
												<li class="_shipping_address_1">
													<strong><?php esc_html_e( 'Address Line 1', 'shop-front' ); ?>:</strong>
													<span></span>
												</li>
												<li class="_shipping_address_2">
													<strong><?php esc_html_e( 'Address Line 2', 'shop-front' ); ?>:</strong>
													<span></span>
												</li>
												<li class="_shipping_city">
													<strong><?php esc_html_e( 'City', 'shop-front' ); ?>:</strong>
													<span></span>
												</li>
												<li class="_shipping_postcode">
													<strong><?php esc_html_e( 'Postcode / ZIP', 'shop-front' ); ?>:</strong>
													<span></span>
												</li>
												<li class="_shipping_country">
													<strong><?php esc_html_e( 'Country / Region', 'shop-front' ); ?>:</strong>
													<span></span>
												</li>
												<li class="_shipping_state">
													<strong><?php esc_html_e( 'State / County', 'shop-front' ); ?>:</strong>
													<span></span>
												</li>
												<li class="_shipping_phone">
													<strong><?php esc_html_e( 'Phone', 'shop-front' ); ?>:</strong>
													<span></span>
												</li>
												<li class="customer_note">
													<strong><?php esc_html_e( 'Note', 'shop-front' ); ?>:</strong>
													<span></span>
												</li>
											</ul>
										</div>
										<div class="msf-card-content msf-shipping-address-fields">
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
														<select class="msf-form-control js_field-country" id="_shipping_country" name="_shipping_country">
														<option value=""><?php esc_html_e( 'Select a country...', 'shop-front' ); ?></option>
															<?php
																$selected_shipping_country = $order ? $order->get_shipping_country() : '';
																
																foreach ( $countries as $code => $name ) {
																	printf(
																		'<option value="%s" %s>%s</option>',
																		esc_attr( $code ),
																		selected( $selected_shipping_country, $code, false ),
																		esc_html( $name )
																	);
																}
															?>
														</select>
													</div>
												</div>
												<div class="col-md-6">
													<div class="msf-form-group">
														<label for="_shipping_state"><?php esc_html_e( 'State / County', 'shop-front' ); ?></label>
														<?php
														$shipping_state   = $order ? $order->get_shipping_state() : '';
														$shipping_states          = WC()->countries->get_states( $selected_shipping_country );
														
														if ( ! empty( $shipping_states ) ) {
															?>
															<select class="msf-form-control js_field-state" id="_shipping_state" name="_shipping_state">
																<option value=""><?php esc_html_e( 'Select a state...', 'shop-front' ); ?></option>
																<?php
																foreach ( $shipping_states as $code => $name ) {
																	printf(
																		'<option value="%s" %s>%s</option>',
																		esc_attr( $code ),
																		selected( $shipping_state, $code, false ),
																		esc_html( $name )
																	);
																}
																?>
															</select>
															<?php
														} else {
															?>
															<input 
																type="text" 
																class="msf-form-control js_field-state" 
																id="_shipping_state" 
																name="_shipping_state" 
																value="<?php echo esc_attr( $shipping_state ); ?>"
																placeholder="<?php esc_attr_e( 'State / County', 'shop-front' ); ?>"
															/>
															<?php
														}
														?>
													</div>
												</div>
											</div>
											<div class="msf-form-group">
												<label for="_shipping_phone"><?php esc_html_e( 'Phone', 'shop-front' ); ?></label>
												<input type="text" class="msf-form-control" id="_shipping_phone" name="_shipping_phone" value="">
											</div>
											<?php
											if ( apply_filters( 'woocommerce_enable_order_notes_field', 'yes' === get_option( 'woocommerce_enable_order_comments', 'yes' ) ) ){
											?>
											<div class="msf-form-group">
												<label for="customer_note"><?php esc_html_e( 'Customer Provided Note', 'shop-front' ); ?></label>
												<textarea rows="3" cols="40" class="msf-form-control" name="customer_note" tabindex="6" id="customer_note" placeholder="<?php esc_attr_e( 'Customer notes about the order', 'woocommerce' ); ?>"><?php echo wp_kses( $order->get_customer_note(), array( 'br' => array() ) ); ?></textarea>
											</div>
											<?php } ?>
										</div>
									</div>
								</div>
							</div>
						</div>
	
						<!-- Right Column -->
						<div class="col-md-4">
							<!-- General Section -->
							<div class="msf-card">
								<h3 class="msf-card-title"><?php esc_html_e( 'General', 'shop-front' ); ?></h3>
								<div class="msf-card-content">
									<div class="msf-form-group search-group">
										<?php
										$user_string = '';
										$user_id     = '';
										?>
										<label for="date_created"><?php esc_html_e( 'Customer', 'shop-front' ); ?></label>
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
									<div class="msf-form-group">
										<label for="date_created"><?php esc_html_e( 'Date Created', 'shop-front' ); ?></label>
										<div class="date-time-group">
											<?php
											$order_date_created_localised = ! is_null( $order->get_date_created() ) ? $order->get_date_created()->getOffsetTimestamp() : '';
											?>
											<input type="date" class="date-picker msf-form-control date-input" name="order_date" maxlength="10" value="<?php echo esc_attr( date_i18n( 'Y-m-d', $order_date_created_localised ) ); ?>" pattern="<?php echo esc_attr( apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ) ); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment ?>" />@
											&lrm;
											<input type="number" class="hour msf-form-control time-input" placeholder="<?php esc_attr_e( 'h', 'woocommerce' ); ?>" name="order_date_hour" min="0" max="23" step="1" value="<?php echo esc_attr( date_i18n( 'H', $order_date_created_localised ) ); ?>" pattern="([01]?[0-9]{1}|2[0-3]{1})" />:
											<input type="number" class="minute msf-form-control time-input" placeholder="<?php esc_attr_e( 'm', 'woocommerce' ); ?>" name="order_date_minute" min="0" max="59" step="1" value="<?php echo esc_attr( date_i18n( 'i', $order_date_created_localised ) ); ?>" pattern="[0-5]{1}[0-9]{1}" />
											<input type="hidden" name="order_date_second" value="<?php echo esc_attr( date_i18n( 's', $order_date_created_localised ) ); ?>" />
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
										<label for="order_action"><?php esc_html_e( 'Order Actions', 'shop-front' ); ?></label>
										<select class="msf-form-control" id="order_action" name="order_action">
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

							<!-- Action Buttons -->
							<div class="action-buttons">
								<button type="submit" class="create-order-btn"><?php esc_html_e( 'Create Order', 'shop-front' ); ?></button>
								<button type="button" class="save-draft-btn"><?php esc_html_e( 'Save as Draft', 'shop-front' ); ?></button>
							</div>
	
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