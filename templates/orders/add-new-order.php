<?php
/**
 * MSFC order creation page.
 * ***/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'msf_dashboard_wrapper_start' );
?>
<div class="my-shop-front-container">
	<aside class="my-shop-front-sidebar">
		<?php do_action( 'msf_dashboard_navigation' ); ?>
	</aside>
	<div class="my-shop-front-wrapper">
		<?php do_action( 'msf_dashboard_content_before' ); ?>
		<main class="my-shop-front-page-content">
			<?php do_action( 'msf_dashboard_before_main_content' ); ?>
			
			<style>			
			.msf-card {
				background: #fff;
				margin-bottom: 24px;	
				box-shadow: 0 1px 3px 0 #0000001a, 0 1px 2px -1px #0000001a;
				border-radius: 8px;
				overflow: hidden;
				padding: 0;
			}
			
			.msf-card-content {
				padding: 24px;
			}
			
			.msf-card-title {
				font-size: 16px;
				font-weight: 500;
				margin: 0;
				color: #374151;
				padding: 16px 24px;
				border-bottom: 1px solid #e5e7eb;
			}
			
			.msf-form-group {
				margin-bottom: 16px;
				position: relative;
			}
			
			.msf-form-group label {
				display: block;
				margin-bottom: 6px;
				font-weight: 500;
				color: #495057;
				font-size: 14px;
			}
			
			.msf-form-control {
				width: 100%;
				padding: 12px 16px;
				border: 2px solid #e9ecef;
				border-radius: 8px;
				font-size: 14px;
				transition: all 0.3s ease;
				background-color: white;
			}
			
			.msf-form-control:focus {
				outline: none;
				border-color: #8b5cf6;
				box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
			}
			
			.search-group {
				position: relative;
			}
			
			.search-icon {
				position: absolute;
				right: 12px;
				top: 50%;
				transform: translateY(-50%);
				color: #6c757d;
				font-size: 16px;
				pointer-events: none;
			}
			
			.separator {
				text-align: center;
				margin: 20px 0;
				position: relative;
			}
			
			.separator::before {
				content: '';
				position: absolute;
				top: 50%;
				left: 0;
				right: 0;
				height: 1px;
				background-color: #e9ecef;
			}
			
			.separator span {
				background: white;
				padding: 0 16px;
				color: #6c757d;
				font-size: 14px;
			}
			
			.customer-type-group {
				display: flex;
				gap: 20px;
				margin-bottom: 20px;
			}
			
			.radio-label {
				display: flex;
				align-items: center;
				gap: 8px;
				cursor: pointer;
				font-weight: 500;
			}
			
			.radio-label input[type="radio"] {
				width: 18px;
				height: 18px;
				accent-color: #8b5cf6;
			}
			
			.checkbox-label {
				display: flex;
				align-items: center;
				gap: 8px;
				cursor: pointer;
				font-weight: 500;
			}
			
			.checkbox-label input[type="checkbox"] {
				width: 18px;
				height: 18px;
				accent-color: #8b5cf6;
			}
			
			.address-subtitle {
				font-size: 16px;
				font-weight: 600;
				margin: 20px 0 16px 0;
				color: #2c3e50;
			}
			
			.card-title-with-checkbox {
				display: flex;
				justify-content: space-between;
				align-items: center;
				margin-bottom: 0px;
				border-bottom: 1px solid #e5e7eb;
				padding: 16px 24px;
			}
			
			.card-title-with-checkbox .msf-card-title {
				margin: 0;
				border-bottom: none;
				padding: 0;
			}
			
			.card-title-with-link {
				display: flex;
				justify-content: space-between;
				align-items: center;
				margin-bottom: 0px;
				border-bottom: 1px solid #e5e7eb;
				padding: 16px 24px;
			}
			
			.card-title-with-link .msf-card-title {
				margin: 0;
				border-bottom: none;
				padding: 0;
			}
			
			.checkbox-label.right-aligned {
				margin-left: auto;
				font-size: 14px;
				font-weight: 500;
			}
			
			/* Toggle Switch Styles */
			.toggle-switch {
				position: relative;
				display: inline-block;
				width: 50px;
				height: 24px;
				margin-left: 8px;
			}
			
			.toggle-switch input {
				opacity: 0;
				width: 0;
				height: 0;
			}
			
			.toggle-slider {
				position: absolute;
				cursor: pointer;
				top: 0;
				left: 0;
				right: 0;
				bottom: 0;
				background-color: #ccc;
				transition: .4s;
				border-radius: 24px;
			}
			
			.toggle-slider:before {
				position: absolute;
				content: "";
				height: 18px;
				width: 18px;
				left: 3px;
				bottom: 3px;
				background-color: white;
				transition: .4s;
				border-radius: 50%;
				box-shadow: 0 2px 4px rgba(0,0,0,0.2);
			}
			
			input:checked + .toggle-slider {
				background-color: #8b5cf6;
			}
			
			input:checked + .toggle-slider:before {
				transform: translateX(26px);
			}
			
			.toggle-label {
				display: flex;
				align-items: center;
				gap: 8px;
			}
			
			.add-customer-link {
				display: flex;
				align-items: center;
				gap: 8px;
			}
			
			.or-text {
				color: #6c757d;
				font-size: 14px;
				font-weight: 500;
			}
			
			.add-customer-link a {
				color: #8b5cf6;
				text-decoration: underline;
				font-weight: 500;
				font-size: 14px;
				transition: all 0.3s ease;
				cursor: pointer;
			}
			
			.add-customer-link a:hover {
				color: #7c3aed;
				text-decoration: none;
			}
			
			.new-customer-fields {
				margin-top: 20px;
				padding-top: 20px;
				border-top: 1px solid #e9ecef;
				animation: slideDown 0.3s ease-out;
			}
			
			@keyframes slideDown {
				from {
					opacity: 0;
					transform: translateY(-10px);
				}
				to {
					opacity: 1;
					transform: translateY(0);
				}
			}
			
			.msf-table {
				width: 100%;
				border-collapse: collapse;
				margin-top: 16px;
			}
			
			.msf-table th,
			.msf-table td {
				padding: 12px;
				text-align: left;
				border-bottom: 1px solid #e9ecef;
			}
			
			.msf-table th {
				background-color: #f8f9fa;
				font-weight: 600;
				color: #495057;
				font-size: 14px;
			}
			
			.msf-table td {
				font-size: 14px;
				color: #495057;
			}
			
			.quantity-input {
				width: 80px;
				text-align: center;
			}
			
			.delete-btn {
				background: none;
				border: none;
				cursor: pointer;
				font-size: 16px;
				padding: 4px;
				border-radius: 4px;
				transition: background-color 0.2s;
			}
			
			.delete-btn:hover {
				background-color: #f8f9fa;
			}
			
			.coupon-group {
				display: flex;
				gap: 8px;
			}
			
			.coupon-group .msf-form-control {
				flex: 1;
			}
			
			.apply-btn {
				background-color: #8b5cf6;
				color: white;
				border: none;
				padding: 12px 20px;
				border-radius: 8px;
				font-weight: 500;
				cursor: pointer;
				transition: background-color 0.3s;
			}
			
			.apply-btn:hover {
				background-color: #7c3aed;
			}
			
			.fee-group {
				display: flex;
				gap: 8px;
				align-items: center;
			}
			
			.fee-group .msf-form-control {
				flex: 1;
			}
			
			.add-fee-btn {
				width: 40px;
				height: 40px;
				border-radius: 50%;
				background-color: #8b5cf6;
				color: white;
				border: none;
				font-size: 18px;
				cursor: pointer;
				transition: background-color 0.3s;
			}
			
			.add-fee-btn:hover {
				background-color: #7c3aed;
			}
			
			.order-summary {
				background-color: #f8f9fa;
				padding: 16px;
				border-radius: 8px;
			}
			
			.summary-line {
				display: flex;
				justify-content: space-between;
				margin-bottom: 12px;
				font-size: 14px;
			}
			
			.summary-line.discount span:last-child {
				color: #28a745;
			}
			
			.summary-line.total {
				border-top: 2px solid #e9ecef;
				padding-top: 12px;
				margin-top: 12px;
				font-size: 16px;
			}
			
			.action-buttons {
				display: flex;
				flex-direction: row;
				gap: 12px;
				margin: 0 0 24px 0;
			}
			
			.create-order-btn {
				background-color: #8b5cf6;
				color: white;
				border: none;
				border-radius: 8px;
				font-size: 16px;
				font-weight: 600;
				cursor: pointer;
				transition: background-color 0.3s;
				flex: 1;
				height: 50px;
				display: flex;
				align-items: center;
				justify-content: center;
			}
			
			.create-order-btn:hover {
				background-color: #7c3aed;
			}
			
			.save-draft-btn {
				background-color: #f8f9fa;
				color: #495057;
				border: 2px solid #e9ecef;
				border-radius: 8px;
				font-size: 16px;
				font-weight: 500;
				cursor: pointer;
				transition: all 0.3s;
				flex: 1;
				height: 50px;
				display: flex;
				align-items: center;
				justify-content: center;
			}
			
			.save-draft-btn:hover {
				background-color: #e9ecef;
				border-color: #dee2e6;
			}
			
			/* Order Notes Styles */
			.card-title-with-controls {
				display: flex;
				justify-content: space-between;
				align-items: center;
				margin-bottom: 0px;
				border-bottom: 1px solid #e5e7eb;
				padding: 16px 24px;
			}
			
			.card-title-with-controls .msf-card-title {
				margin: 0;
				border-bottom: none;
				padding: 0;
			}
			
			.note-controls {
				display: flex;
				gap: 4px;
			}
			
			.note-control-btn {
				background: none;
				border: none;
				color: #6c757d;
				font-size: 12px;
				cursor: pointer;
				padding: 2px 4px;
				border-radius: 2px;
				transition: color 0.2s;
			}
			
			.note-control-btn:hover {
				color: #495057;
			}
			
			.existing-notes {
				margin-bottom: 20px;
			}
			
			.note-item {
				margin-bottom: 16px;
			}
			
			.note-bubble {
				padding: 12px 16px;
				border-radius: 8px;
				margin-bottom: 8px;
				font-size: 14px;
				line-height: 1.4;
				position: relative;
			}
			
			.note-bubble-blue {
				background-color: #e3f2fd;
				color: #1976d2;
				border-left: 4px solid #2196f3;
			}
			
			.note-bubble-gray {
				background-color: #f5f5f5;
				color: #424242;
				border-left: 4px solid #9e9e9e;
			}
			
			.note-meta {
				display: flex;
				justify-content: space-between;
				align-items: center;
				font-size: 12px;
				color: #6c757d;
			}
			
			.note-date {
				font-style: italic;
			}
			
			.delete-note-link {
				color: #dc3545;
				text-decoration: underline;
				font-size: 12px;
				cursor: pointer;
			}
			
			.delete-note-link:hover {
				color: #c82333;
			}
			
			.add-note-section {
				border-top: 1px solid #e5e7eb;
				padding-top: 20px;
			}
			
			.add-note-header {
				display: flex;
				align-items: center;
				gap: 8px;
				margin-bottom: 16px;
			}
			
			.add-note-title {
				font-size: 16px;
				font-weight: 600;
				margin: 0;
				color: #374151;
			}
			
			.help-icon {
				width: 16px;
				height: 16px;
				border-radius: 50%;
				background-color: #6c757d;
				color: white;
				display: flex;
				align-items: center;
				justify-content: center;
				font-size: 12px;
				font-weight: bold;
				cursor: help;
			}
			
			.note-textarea {
				resize: vertical;
				min-height: 80px;
			}
			
			.note-options {
				display: flex;
				justify-content: space-between;
				align-items: end;
				gap: 16px;
				margin-top: 16px;
			}
			
			.note-options .msf-form-group {
				flex: 1;
				margin-bottom: 0;
			}
			
			.add-note-btn {
				background-color: #8b5cf6;
				color: white;
				border: none;
				padding: 12px 20px;
				border-radius: 6px;
				font-weight: 500;
				cursor: pointer;
				transition: background-color 0.3s;
				white-space: nowrap;
			}
			
			.add-note-btn:hover {
				background-color: #7c3aed;
			}
			
			/* Payment Section Styles */
			.general-section {
				margin-bottom: 24px;
				padding-bottom: 20px;
				border-bottom: 1px solid #e5e7eb;
			}
			
			.section-subtitle {
				font-size: 16px;
				font-weight: 600;
				margin: 0 0 16px 0;
				color: #374151;
			}
			
			.date-time-group {
				display: flex;
				align-items: center;
				gap: 8px;
			}
			
			.date-input {
				flex: 1;
				min-width: 120px;
			}
			
			.time-input {
				width: 50px;
				text-align: center;
			}
			
			.date-separator,
			.time-separator {
				color: #6c757d;
				font-weight: 500;
				font-size: 14px;
			}
			
			.payment-method-section {
				margin-top: 20px;
			}
			
			@media (max-width: 768px) {
				.col-md-8, .col-md-4 {
					flex: 0 0 100%;
					max-width: 100%;
				}
				
				.customer-type-group {
					flex-direction: column;
					gap: 12px;
				}
				
				.coupon-group, .fee-group {
					flex-direction: column;
				}
				
				.action-buttons {
					flex-direction: column;
				}
				
				.date-time-group {
					flex-direction: column;
					gap: 8px;
				}
				
				.date-input, .time-input {
					width: 100%;
				}
			}
			</style>
			
			<script>
			document.addEventListener('DOMContentLoaded', function() {
				const addCustomerLink = document.getElementById('add-new-customer-link');
				const newCustomerFields = document.getElementById('new-customer-fields');
				const orText = document.querySelector('.or-text');
				
				addCustomerLink.addEventListener('click', function(e) {
					e.preventDefault();
					
					if (newCustomerFields.style.display === 'none') {
						newCustomerFields.style.display = 'block';
						addCustomerLink.textContent = '<?php echo esc_js( __( 'hide new customer form', 'shop-front' ) ); ?>';
						orText.textContent = '<?php echo esc_js( __( 'Or', 'shop-front' ) ); ?>';
					} else {
						newCustomerFields.style.display = 'none';
						addCustomerLink.textContent = '<?php echo esc_js( __( 'add a new customer', 'shop-front' ) ); ?>';
						orText.textContent = '<?php echo esc_js( __( 'Or', 'shop-front' ) ); ?>';
					}
				});
			});
			</script>
			
			<div class="order-creation-form">
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
								<input type="text" class="msf-form-control" id="customer-search" placeholder="<?php echo esc_attr__( 'Search for existing customer', 'shop-front' ); ?>">
								<i class="search-icon">🔍</i>
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
						<div class="msf-card">
							<h3 class="msf-card-title"><?php esc_html_e( 'Products', 'shop-front' ); ?></h3>
							<div class="msf-card-content">
								<div class="msf-form-group search-group">
								<input type="text" class="msf-form-control" id="product-search" placeholder="<?php echo esc_attr__( 'Search for products to add', 'shop-front' ); ?>">
								<i class="search-icon">🔍</i>
							</div>
							<div class="products-table">
								<table class="msf-table">
									<thead>
										<tr>
											<th><?php esc_html_e( 'Product', 'shop-front' ); ?></th>
											<th><?php esc_html_e( 'Quantity', 'shop-front' ); ?></th>
											<th><?php esc_html_e( 'Price', 'shop-front' ); ?></th>
											<th><?php esc_html_e( 'Total', 'shop-front' ); ?></th>
											<th></th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td><?php esc_html_e( 'Classic T-Shirt', 'shop-front' ); ?></td>
											<td><input type="number" class="msf-form-control quantity-input" value="2" min="1"></td>
											<td>$25.00</td>
											<td>$50.00</td>
											<td><button type="button" class="delete-btn">🗑️</button></td>
										</tr>
										<tr>
											<td><?php esc_html_e( 'Denim Jeans', 'shop-front' ); ?></td>
											<td><input type="number" class="msf-form-control quantity-input" value="1" min="1"></td>
											<td>$75.00</td>
											<td>$75.00</td>
											<td><button type="button" class="delete-btn">🗑️</button></td>
										</tr>
									</tbody>
								</table>
							</div>
							</div>
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
												<button type="button" class="apply-btn"><?php esc_html_e( 'Apply', 'shop-front' ); ?></button>
											</div>
										</div>
										<div class="msf-form-group">
											<div class="fee-group">
												<input type="text" class="msf-form-control" placeholder="<?php echo esc_attr__( 'Fee', 'shop-front' ); ?>">
												<input type="number" class="msf-form-control" placeholder="<?php echo esc_attr__( 'Amount', 'shop-front' ); ?>" step="0.01">
												<button type="button" class="add-fee-btn">+</button>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="msf-card">
									<h3 class="msf-card-title"><?php esc_html_e( 'Order Summary', 'shop-front' ); ?></h3>
									<div class="msf-card-content">
										<div class="order-summary">
											<div class="summary-line">
												<span><?php esc_html_e( 'Subtotal', 'shop-front' ); ?></span>
												<span>$125.00</span>
											</div>
											<div class="summary-line discount">
												<span><?php esc_html_e( 'Discount', 'shop-front' ); ?></span>
												<span>-$0.00</span>
											</div>
											<div class="summary-line">
												<span><?php esc_html_e( 'Shipping', 'shop-front' ); ?></span>
												<span>$10.00</span>
											</div>
											<div class="summary-line">
												<span><?php esc_html_e( 'Taxes (5%)', 'shop-front' ); ?></span>
												<span>$6.25</span>
											</div>
											<div class="summary-line total">
												<span><strong><?php esc_html_e( 'Total', 'shop-front' ); ?></strong></span>
												<span><strong>$141.25</strong></span>
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
										<div class="msf-form-group">
											<label for="billing_address_line_1"><?php esc_html_e( 'Address Line 1', 'shop-front' ); ?></label>
											<input type="text" class="msf-form-control" id="billing_address_line_1" name="billing_address_line_1" value="123 Main St">
										</div>
										<div class="msf-form-group">
											<label for="billing_address_line_2"><?php esc_html_e( 'Address Line 2 (Optional)', 'shop-front' ); ?></label>
											<input type="text" class="msf-form-control" id="billing_address_line_2" name="billing_address_line_2" placeholder="<?php echo esc_attr__( 'Apartment, studio, or floor', 'shop-front' ); ?>">
										</div>
										<div class="row">
											<div class="col-md-6">
												<div class="msf-form-group">
													<label for="billing_city"><?php esc_html_e( 'City', 'shop-front' ); ?></label>
													<input type="text" class="msf-form-control" id="billing_city" name="billing_city" value="Anytown">
												</div>
											</div>
											<div class="col-md-6">
												<div class="msf-form-group">
													<label for="billing_state"><?php esc_html_e( 'State / Province', 'shop-front' ); ?></label>
													<input type="text" class="msf-form-control" id="billing_state" name="billing_state" value="CA">
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-6">
												<div class="msf-form-group">
													<label for="billing_zip"><?php esc_html_e( 'ZIP / Postal Code', 'shop-front' ); ?></label>
													<input type="text" class="msf-form-control" id="billing_zip" name="billing_zip" value="12345">
												</div>
											</div>
											<div class="col-md-6">
												<div class="msf-form-group">
													<label for="billing_country"><?php esc_html_e( 'Country', 'shop-front' ); ?></label>
													<select class="msf-form-control" id="billing_country" name="billing_country">
														<option value="US" selected><?php esc_html_e( 'United States', 'shop-front' ); ?></option>
														<option value="CA"><?php esc_html_e( 'Canada', 'shop-front' ); ?></option>
														<option value="GB"><?php esc_html_e( 'United Kingdom', 'shop-front' ); ?></option>
													</select>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="msf-card">
									<div class="card-title-with-checkbox">
										<h3 class="msf-card-title"><?php esc_html_e( 'Shipping Address', 'shop-front' ); ?></h3>
										<label class="toggle-label right-aligned">
											<span><?php esc_html_e( 'Same as billing address', 'shop-front' ); ?></span>
											<div class="toggle-switch">
												<input type="checkbox" name="shipping_same_as_billing" checked>
												<span class="toggle-slider"></span>
											</div>
										</label>
									</div>
									<div class="msf-card-content">
										<div class="msf-form-group">
											<label for="shipping_address_line_1"><?php esc_html_e( 'Address Line 1', 'shop-front' ); ?></label>
											<input type="text" class="msf-form-control" id="shipping_address_line_1" name="shipping_address_line_1" value="123 Main St">
										</div>
										<div class="msf-form-group">
											<label for="shipping_address_line_2"><?php esc_html_e( 'Address Line 2 (Optional)', 'shop-front' ); ?></label>
											<input type="text" class="msf-form-control" id="shipping_address_line_2" name="shipping_address_line_2" placeholder="<?php echo esc_attr__( 'Apartment, studio, or floor', 'shop-front' ); ?>">
										</div>
										<div class="row">
											<div class="col-md-6">
												<div class="msf-form-group">
													<label for="shipping_city"><?php esc_html_e( 'City', 'shop-front' ); ?></label>
													<input type="text" class="msf-form-control" id="shipping_city" name="shipping_city" value="Anytown">
												</div>
											</div>
											<div class="col-md-6">
												<div class="msf-form-group">
													<label for="shipping_state"><?php esc_html_e( 'State / Province', 'shop-front' ); ?></label>
													<input type="text" class="msf-form-control" id="shipping_state" name="shipping_state" value="CA">
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-6">
												<div class="msf-form-group">
													<label for="shipping_zip"><?php esc_html_e( 'ZIP / Postal Code', 'shop-front' ); ?></label>
													<input type="text" class="msf-form-control" id="shipping_zip" name="shipping_zip" value="12345">
												</div>
											</div>
											<div class="col-md-6">
												<div class="msf-form-group">
													<label for="shipping_country"><?php esc_html_e( 'Country', 'shop-front' ); ?></label>
													<select class="msf-form-control" id="shipping_country" name="shipping_country">
														<option value="US" selected><?php esc_html_e( 'United States', 'shop-front' ); ?></option>
														<option value="CA"><?php esc_html_e( 'Canada', 'shop-front' ); ?></option>
														<option value="GB"><?php esc_html_e( 'United Kingdom', 'shop-front' ); ?></option>
													</select>
												</div>
											</div>
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
										<option value="pending"><?php esc_html_e( 'Pending payment', 'shop-front' ); ?></option>
										<option value="processing"><?php esc_html_e( 'Processing', 'shop-front' ); ?></option>
										<option value="completed"><?php esc_html_e( 'Completed', 'shop-front' ); ?></option>
										<option value="cancelled"><?php esc_html_e( 'Cancelled', 'shop-front' ); ?></option>
										<option value="refunded"><?php esc_html_e( 'Refunded', 'shop-front' ); ?></option>
									</select>
								</div>
								<div class="msf-form-group">
									<label for="payment_method"><?php esc_html_e( 'Order Actions', 'shop-front' ); ?></label>
									<select class="msf-form-control" id="payment_method" name="payment_method">
										<option value=""><?php esc_html_e( 'Select a payment method', 'shop-front' ); ?></option>
										<option value="credit_card"><?php esc_html_e( 'Credit Card', 'shop-front' ); ?></option>
										<option value="paypal"><?php esc_html_e( 'PayPal', 'shop-front' ); ?></option>
										<option value="bank_transfer"><?php esc_html_e( 'Bank Transfer', 'shop-front' ); ?></option>
									</select>
								</div>
							</div>
						</div>

						<!-- Order Notes Section -->
						<div class="msf-card">
							<h3 class="msf-card-title"><?php esc_html_e( 'Order notes', 'shop-front' ); ?></h3>
							<div class="msf-card-content">
								<!-- Existing Notes -->
								<div class="existing-notes">
									<div class="note-item">
										<div class="note-bubble note-bubble-blue">
											<?php esc_html_e( 'We highly recommend serving your entire website over an HTTPS connection', 'shop-front' ); ?>
										</div>
										<div class="note-meta">
											<span class="note-date"><?php esc_html_e( 'added on September 19, 2025 at 11:41 am by admin', 'shop-front' ); ?></span>
											<a href="#" class="delete-note-link"><?php esc_html_e( 'Delete note', 'shop-front' ); ?></a>
										</div>
									</div>
									
									<div class="note-item">
										<div class="note-bubble note-bubble-gray">
											<?php esc_html_e( 'Your store does not appear to be using a secure connection.', 'shop-front' ); ?>
										</div>
										<div class="note-meta">
											<span class="note-date"><?php esc_html_e( 'added on September 19, 2025 at 11:40 am by admin', 'shop-front' ); ?></span>
											<a href="#" class="delete-note-link"><?php esc_html_e( 'Delete note', 'shop-front' ); ?></a>
										</div>
									</div>
								</div>
								
								<!-- Add Note Section -->
								<div class="add-note-section">
									<div class="add-note-header">
										<h4 class="add-note-title"><?php esc_html_e( 'Add note', 'shop-front' ); ?></h4>
										<span class="help-icon">?</span>
									</div>
									<div class="msf-form-group">
										<textarea class="msf-form-control note-textarea" placeholder="<?php echo esc_attr__( 'Enter your note here...', 'shop-front' ); ?>" rows="4"></textarea>
									</div>
									<div class="note-options">
										<div class="msf-form-group">
											<label for="note-visibility"><?php esc_html_e( 'Note to customer', 'shop-front' ); ?></label>
											<select class="msf-form-control" id="note-visibility">
												<option value="internal"><?php esc_html_e( 'Internal note', 'shop-front' ); ?></option>
												<option value="customer"><?php esc_html_e( 'Note to customer', 'shop-front' ); ?></option>
											</select>
										</div>
										<button type="button" class="add-note-btn"><?php esc_html_e( 'Add', 'shop-front' ); ?></button>
									</div>
								</div>
							</div>
						</div>

					</div>
				</div>
			</div>
		</main>
	</div>
</div>
<?php do_action( 'msf_dashboard_wrapper_end' ); ?>