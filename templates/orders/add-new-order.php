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
			
			<style>
			.select2-container {
				box-sizing: border-box;
				display: inline-block;
				margin: 0;
				position: relative;
				vertical-align: middle;
				width: 100%!important;
			}
			.select2-container .select2-selection--single {
				height: 38px;
				background-color: #fff;
				border: 1px solid #7e8993;
				border-radius: 4px;
				box-sizing: border-box;
				cursor: pointer;
				display: block;
				margin: 0 0 -4px;
				user-select: none;
				-webkit-user-select: none;
			}
			.select2-container .select2-selection--single .select2-selection__rendered {
				line-height: 38px;
				padding: 0 24px 0 8px;
				color: #444;
				display: block;
				overflow: hidden;
				text-overflow: ellipsis;
				white-space: nowrap;
			}

			.select2-dropdown {
				border-color: var(--primary-bg);
			}

			.select2-dropdown--below {
				display: inline-block;
				box-shadow: none;
			}

			.select2-dropdown--above {
				box-shadow: 0 0 0 1px var(--primary-bg),0 -2px 1px rgba(0,0,0,.1);
			}

			.select2-selection--single .select2-selection__rendered:hover {
				color: var(--primary-bg);
			}

			.select2-container.select2-container--focus .select2-selection--single, .select2-container.select2-container--open .select2-selection--multiple, .select2-container.select2-container--open .select2-selection--single {
				border-color: var(--msf-input-border-color);
				box-shadow: none!important;
				outline: none;
			}
			.select2-container.select2-container--open .select2-selection--single{
				border-bottom-left-radius: 0px!important;
				border-bottom-right-radius: 0px!important;
				border-color: var(--primary-bg)!important;
			}

			.select2-container--default .select2-results__option--highlighted[aria-selected], .select2-container--default .select2-results__option--highlighted[data-selected] {
				background-color: var(--primary-bg);
				color: #fff;
				border: 1px solid var(--primary-bg);
			}

			.select2-container--default .select2-results__option, .select2-container--default .select2-results__option:focus, .select2-container--default .select2-results__option:visited, .select2-container--default .select2-results__option:active{
				box-shadow: none!important;
				outline: none!important;
				border-radius: 0px;
				cursor: pointer;
			}
			span.select2-selection__clear {
				margin-right: 5px;
			}

			.select2-container--default .select2-results__option:last-child{
				border-bottom-left-radius: 6px!important;
				border-bottom-right-radius: 6px!important;
			}
			
			.select2-container .select2-dropdown, .select2-container .select2-selection {
				background-color: #fff;
				border: 1px solid var(--msf-input-border-color)!important;
				border-radius: 8px !important;
			}
			.select2-container.select2-container--open .select2-dropdown--below {
				border-top: none!important;
				border-top-left-radius: 0!important;
				border-top-right-radius: 0!important;
				border-color: var(--primary-bg)!important;
			}
			.select2-results__option {
				margin: 0;
				padding: 6px;
				font-size: 14px;
			}
			.select2-search--dropdown {
				display: block;
				padding: 4px;
			}
			.select2-search--dropdown .select2-search__field {
				padding: 4px;
				width: 100%;
				box-sizing: border-box;
				font-size: 14px;
				border: 1px solid var(--msf-input-border-color);
				padding: 8px 16px;
				height: 38px;
				border-radius: 8px;
			}
			.select2-search--dropdown .select2-search__field:focus {
				outline: none;
				border-color: var(--primary-bg);
				box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
			}
			.order-creation-form select#customer_user {
				width: 1px;
				border: none;
			}
			.order-creation-form .msf-card {
				background: #fff;
				margin-bottom: 24px;
				box-shadow: 0 1px 3px 0 #0000001a, 0 1px 2px -1px #0000001a;
				border-radius: 8px;
				overflow: hidden;
				padding: 0;
			}
			
			.order-creation-form .msf-card-content {
				padding: 24px;
			}
			
			.order-creation-form .msf-card-title {
				font-size: 16px;
				font-weight: 500;
				margin: 0;
				color: #374151;
				padding: 16px 24px;
				border-bottom: 1px solid #e5e7eb;
			}
			
			.order-creation-form .msf-form-group {
				margin-bottom: 16px;
				position: relative;
			}
			
			.order-creation-form .msf-form-group label {
				display: block;
				margin-bottom: 6px;
				font-weight: 500;
				color: #495057;
				font-size: 14px;
			}
			
			.order-creation-form .msf-form-control {
				width: 100%;
				border-radius: 8px;
				font-size: 14px;
				transition: border-color 0.3s ease, box-shadow 0.3s ease;
				background-color: #fff;
			}
			
			.order-creation-form .msf-form-control:focus {
				outline: none;
				border-color: #8b5cf6;
				box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
			}
			
			.order-creation-form .search-group {
				position: relative;
			}
			
			.order-creation-form .search-icon {
				position: absolute;
				right: 12px;
				top: 50%;
				transform: translateY(-50%);
				color: #6c757d;
				font-size: 16px;
				pointer-events: none;
			}
			
			.order-creation-form .separator {
				text-align: center;
				margin: 20px 0;
				position: relative;
			}
			
			.order-creation-form .separator::before {
				content: '';
				position: absolute;
				top: 50%;
				left: 0;
				right: 0;
				height: 1px;
				background-color: #e9ecef;
			}
			
			.order-creation-form .separator span {
				background: white;
				padding: 0 16px;
				color: #6c757d;
				font-size: 14px;
			}
			
			.order-creation-form .customer-type-group {
				display: flex;
				gap: 20px;
				margin-bottom: 20px;
			}
			
			.order-creation-form .radio-label {
				display: flex;
				align-items: center;
				gap: 8px;
				cursor: pointer;
				font-weight: 500;
			}
			
			.order-creation-form .radio-label input[type="radio"] {
				width: 18px;
				height: 18px;
				accent-color: #8b5cf6;
			}
			
			.order-creation-form .checkbox-label {
				display: flex;
				align-items: center;
				gap: 8px;
				cursor: pointer;
				font-weight: 500;
			}
			
			.order-creation-form .checkbox-label input[type="checkbox"] {
				width: 18px;
				height: 18px;
				accent-color: #8b5cf6;
			}
			
			.order-creation-form .address-subtitle {
				font-size: 16px;
				font-weight: 600;
				margin: 20px 0 16px 0;
				color: #2c3e50;
			}
			
			.order-creation-form .card-title-with-checkbox {
				display: flex;
				justify-content: space-between;
				align-items: center;
				margin-bottom: 0;
				border-bottom: 1px solid #e5e7eb;
				padding: 16px 24px;
			}
			
			.order-creation-form .card-title-with-checkbox .msf-card-title {
				margin: 0;
				border-bottom: none;
				padding: 0;
			}
			
			.order-creation-form .card-title-with-link {
				display: flex;
				justify-content: space-between;
				align-items: center;
				margin-bottom: 0;
				border-bottom: 1px solid #e5e7eb;
				padding: 16px 24px;
			}
			
			.order-creation-form .card-title-with-link .msf-card-title {
				margin: 0;
				border-bottom: none;
				padding: 0;
			}
			
			.order-creation-form .checkbox-label.right-aligned {
				margin-left: auto;
				font-size: 14px;
				font-weight: 500;
			}
			
			.order-creation-form .toggle-switch {
				position: relative;
				display: inline-block;
				width: 50px;
				height: 24px;
				margin-left: 8px;
			}
			
			.order-creation-form .toggle-switch input {
				opacity: 0;
				width: 0;
				height: 0;
			}
			
			.order-creation-form .toggle-slider {
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
			
			.order-creation-form .toggle-slider:before {
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
			
			.order-creation-form input:checked + .toggle-slider {
				background-color: #8b5cf6;
			}
			
			.order-creation-form input:checked + .toggle-slider:before {
				transform: translateX(26px);
			}
			
			.order-creation-form .toggle-label {
				display: flex;
				align-items: center;
				gap: 8px;
			}
			
			.order-creation-form .add-customer-link {
				display: flex;
				align-items: center;
				gap: 8px;
			}
			
			.order-creation-form .or-text {
				color: #6c757d;
				font-size: 14px;
				font-weight: 500;
			}
			
			.order-creation-form .add-customer-link a {
				color: #8b5cf6;
				text-decoration: underline;
				font-weight: 500;
				font-size: 14px;
				transition: all 0.3s ease;
				cursor: pointer;
			}
			
			.order-creation-form .add-customer-link a:hover {
				color: #7c3aed;
				text-decoration: none;
			}
			
			.order-creation-form .new-customer-fields {
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
			
			.order-creation-form .msf-table {
				width: 100%;
				border-collapse: collapse;
				margin-top: 16px;
			}
			
			.order-creation-form .msf-table th,
			.order-creation-form .msf-table td {
				padding: 12px;
				text-align: left;
				border-bottom: 1px solid #e9ecef;
			}
			
			.order-creation-form .msf-table th {
				background-color: #f8f9fa;
				font-weight: 600;
				color: #495057;
				font-size: 14px;
			}
			
			.order-creation-form .msf-table td {
				font-size: 14px;
				color: #495057;
			}
			
			.order-creation-form .quantity-input {
				width: 80px;
				text-align: center;
			}
			
			.order-creation-form .delete-btn {
				background: none;
				border: none;
				cursor: pointer;
				font-size: 16px;
				padding: 4px;
				border-radius: 4px;
				transition: background-color 0.2s;
			}
			
			.order-creation-form .delete-btn:hover {
				background-color: #f8f9fa;
			}
			
			.order-creation-form .coupon-group {
				display: flex;
				gap: 8px;
			}
			
			.order-creation-form .coupon-group .msf-form-control {
				flex: 1;
			}
			
			.order-creation-form .apply-btn {
				background-color: #8b5cf6;
				color: white;
				border: none;
				padding: 12px 20px;
				border-radius: 8px;
				font-weight: 500;
				cursor: pointer;
				transition: background-color 0.3s;
			}
			
			.order-creation-form .apply-btn:hover {
				background-color: #7c3aed;
			}
			
			.order-creation-form .fee-group {
				display: flex;
				gap: 8px;
				align-items: center;
			}
			
			.order-creation-form .fee-group .msf-form-control {
				flex: 1;
			}
			
			.order-creation-form .add-fee-btn {
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
			
			.order-creation-form .add-fee-btn:hover {
				background-color: #7c3aed;
			}
			
			.order-creation-form .order-summary {
				background-color: #f8f9fa;
				padding: 16px;
				border-radius: 8px;
			}
			
			.order-creation-form .summary-line {
				display: flex;
				justify-content: space-between;
				margin-bottom: 12px;
				font-size: 14px;
			}
			
			.order-creation-form .summary-line.discount span:last-child {
				color: #28a745;
			}
			
			.order-creation-form .summary-line.total {
				border-top: 2px solid #e9ecef;
				padding-top: 12px;
				margin-top: 12px;
				font-size: 16px;
			}
			
			.order-creation-form .action-buttons {
				display: flex;
				flex-direction: row;
				gap: 12px;
				margin: 0 0 24px 0;
			}
			
			.order-creation-form .create-order-btn,
			.order-creation-form .save-draft-btn {
				border-radius: 8px;
				font-size: 16px;
				cursor: pointer;
				flex: 1;
				height: 50px;
				display: flex;
				align-items: center;
				justify-content: center;
				transition: all 0.3s ease;
			}
			
			.order-creation-form .create-order-btn {
				background-color: #8b5cf6;
				color: #fff;
				border: none;
				font-weight: 600;
			}
			
			.order-creation-form .create-order-btn:hover {
				background-color: #7c3aed;
			}
			
			.order-creation-form .save-draft-btn {
				background-color: #f8f9fa;
				color: #495057;
				border: 2px solid #e9ecef;
				font-weight: 500;
			}
			
			.order-creation-form .save-draft-btn:hover {
				background-color: #e9ecef;
				border-color: #dee2e6;
			}
			
			.order-creation-form .existing-notes {
				margin-bottom: 20px;
			}
			
			.order-creation-form .note {
				margin-bottom: 16px;
			}
			
			.order-creation-form .note_content {
				padding: 12px 16px;
				border-radius: 8px;
				margin-bottom: 8px;
				font-size: 14px;
				line-height: 1.4;
				position: relative;
				background-color: #f5f5f5;
				color: #424242;
				border-left: 4px solid #9e9e9e;
			}
			.order-creation-form .note_content p{
				margin: 0;
			}
			
			/* .order-creation-form .note_content-blue {
				background-color: #e3f2fd;
				color: #1976d2;
				border-left: 4px solid #2196f3;
			}
			
			.order-creation-form .note_content-gray {
				background-color: #f5f5f5;
				color: #424242;
				border-left: 4px solid #9e9e9e;
			} */
			
			.order-creation-form .meta {
				display: flex;
				justify-content: space-between;
				align-items: center;
				font-size: 12px;
				color: #6c757d;
			}
			
			.order-creation-form .exact-date {
				font-style: italic;
			}
			
			.order-creation-form .delete_note {
				color: #dc3545;
				text-decoration: underline;
				font-size: 12px;
				cursor: pointer;
			}
			
			.order-creation-form .delete_note:hover {
				color: #c82333;
			}
			
			.order-creation-form .add-note-section {
				border-top: 1px solid #e5e7eb;
				padding-top: 20px;
			}
			
			.order-creation-form .add-note-header {
				margin-bottom: 16px;
			}
			
			.order-creation-form .add-note-title {
				font-size: 16px;
				font-weight: 600;
				margin: 0;
				color: #374151;
			}
			
			.order-creation-form .help-icon {
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
			
			.order-creation-form .note-textarea {
				resize: vertical;
				min-height: 80px;
			}
			
			.order-creation-form .note-options {
				display: flex;
				justify-content: space-between;
				align-items: end;
				gap: 16px;
				margin-top: 16px;
			}
			
			.order-creation-form .note-options .msf-form-group {
				flex: 1;
				margin-bottom: 0;
			}
			
			.order-creation-form .add-note-btn {
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
			
			.order-creation-form .add-note-btn:hover {
				background-color: #7c3aed;
			}
			
			.order-creation-form .date-time-group {
				display: flex;
				align-items: center;
				gap: 8px;
			}
			
			.order-creation-form .date-input {
				flex: 1;
				min-width: 120px;
			}
			
			.order-creation-form .time-input {
				width: 50px;
				text-align: center;
			}
			
			.order-creation-form .date-separator,
			.order-creation-form .time-separator {
				color: #6c757d;
				font-weight: 500;
				font-size: 14px;
			}
			
			@media (max-width: 768px) {
				.order-creation-form .col-md-8, 
				.order-creation-form .col-md-4 {
					flex: 0 0 100%;
					max-width: 100%;
				}
				
				.order-creation-form .customer-type-group {
					flex-direction: column;
					gap: 12px;
				}
				
				.order-creation-form .coupon-group, 
				.order-creation-form .fee-group {
					flex-direction: column;
				}
				
				.order-creation-form .action-buttons {
					flex-direction: column;
				}
				
				.order-creation-form .date-time-group {
					flex-direction: column;
					gap: 8px;
				}
				
				.order-creation-form .date-input, 
				.order-creation-form .time-input {
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