<?php
/**
 * MSFC product add/edit form
 *
 * @package ShopFront
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Initialize variables with default values.
$product_id      = 0;
$product         = null;
$product_title   = '';
$product_content = '';
$product_excerpt = '';
$product_status  = 'publish';
$product_type    = 'simple';
$is_edit_mode    = false;

// Product meta defaults.
$regular_price         = '';
$sale_price            = '';
$sale_price_dates_from = '';
$sale_price_dates_to   = '';
$sku                   = '';
$manage_stock          = 'no';
$stock_quantity        = '';
$low_stock_amount      = '';
$backorders            = 'no';
$stock_status          = 'instock';
$sold_individually     = 'no';
$weight                = '';
$length                = '';
$width                 = '';
$height                = '';
$shipping_class_id     = '';
$upsell_ids            = array();
$crosssell_ids         = array();
$visibility            = 'visible';
$product_menu_order    = 0;
$featured              = 'no';
$purchase_note         = '';
$product_categories    = array();
$product_brand         = '';
$product_tags_array    = array();
$is_reviews_allowed    = false;
$thumbnail_id          = 0;
$gallery_image_ids     = array();
$global_unique_id      = '';

// Check if this is edit mode.
if ( array_key_exists( 'edit-product', $query_vars ) && ! empty( $query_vars['edit-product'] ) ) {
	$product_id   = absint( $query_vars['edit-product'] );
	$product      = wc_get_product( $product_id );
	$is_edit_mode = true;

	if ( $product ) {
		// Basic product data.
		$product_title   = $product->get_name();
		$product_content = $product->get_description();
		$product_excerpt = $product->get_short_description();
		$product_status  = $product->get_status();
		$product_type    = $product->get_type();

		// Pricing.
		$regular_price         = $product->get_regular_price();
		$sale_price            = $product->get_sale_price();
		$sale_price_dates_from = $product->get_date_on_sale_from() ? date( 'Y-m-d', $product->get_date_on_sale_from()->getTimestamp() ) : '';
		$sale_price_dates_to   = $product->get_date_on_sale_to() ? date( 'Y-m-d', $product->get_date_on_sale_to()->getTimestamp() ) : '';

		// Inventory.
		$sku               = $product->get_sku();
		$manage_stock      = $product->get_manage_stock() ? 'yes' : 'no';
		$stock_quantity    = $product->get_stock_quantity();
		$low_stock_amount  = $product->get_low_stock_amount();
		$backorders        = $product->get_backorders();
		$stock_status      = $product->get_stock_status();
		$sold_individually = $product->get_sold_individually() ? 'yes' : 'no';

		// Shipping.
		$weight            = $product->get_weight();
		$length            = $product->get_length();
		$width             = $product->get_width();
		$height            = $product->get_height();
		$shipping_class_id = $product->get_shipping_class_id();

		// Linked products.
		$upsell_ids    = $product->get_upsell_ids();
		$crosssell_ids = $product->get_cross_sell_ids();

		// Others.
		$visibility         = $product->get_catalog_visibility();
		$product_menu_order = $product->get_menu_order();
		$featured           = $product->get_featured() ? 'yes' : 'no';
		$purchase_note      = $product->get_purchase_note();

		// Images.
		$thumbnail_id      = $product->get_image_id();
		$gallery_image_ids = $product->get_gallery_image_ids();

		// Taxonomies.
		$product_categories = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );
		$product_tags_array = wp_get_post_terms( $product_id, 'product_tag', array( 'fields' => 'ids' ) );

		// Get brand.
		$brand_terms = wp_get_post_terms( $product_id, 'product_brand', array( 'fields' => 'ids' ) );
		if ( ! is_wp_error( $brand_terms ) && ! empty( $brand_terms ) ) {
			$product_brand = $brand_terms[0];
		}

		// Get product post.
		$is_reviews_allowed = $product->get_reviews_allowed( 'edit' );

		// Get meta data.
		$global_unique_id = $product->get_global_unique_id( 'edit' );
	}
}
$product_types    = apply_filters( 'msf_product_types', array( 'simple' => __( 'Simple', 'shop-front' ) ) );
$product_statuses = apply_filters( 'msf_product_statuses', array( 'publish' => __( 'Simple', 'shop-front' ) ) );
$product_brands   = pluginizelab_shop_front()->msf_product_brands->get_product_brands();
?>
<div x-data="productAddFormHandler()">
	<form id="msfc-add-product" @submit.prevent="handleProductSubmission" method="POST">
		<div class="row">
			<div class="col-md-8">
				<div class="msf-card msf-mb-24">
					<div class="msf-card-content">
						<div class="msf-form-group">
							<label for="product_title"><?php esc_html_e( 'Product Title', 'msfc-wfm' ); ?> <span class="req"><?php esc_html_e( '*', 'msfc-wfm' ); ?></span></strong></label>
							<input type="text" class="msf-form-control" id="product_title" name="product_title" placeholder="<?php echo esc_attr__( 'Product name', 'msfc-wfm' ); ?>" value="<?php echo esc_attr( $product_title ); ?>">
						</div>
						<div class="msf-form-group">
							<label for="product_description"><?php esc_html_e( 'Product Description', 'msfc-wfm' ); ?> <span class="req"><?php esc_html_e( '*', 'msfc-wfm' ); ?></span></strong></label>
							<?php
							$editor_id = 'product_description';
							$settings  = array(
								'wpautop'       => true,
								'media_buttons' => false,
								'textarea_name' => $editor_id,
								'textarea_rows' => get_option( 'default_post_edit_rows', 10 ),
								'tabindex'      => '',
								'editor_css'    => '',
								'editor_class'  => '',
								'teeny'         => true,
								'dfw'           => true,
								'tinymce'       => true,
								'quicktags'     => false,
							);
							wp_editor( htmlspecialchars_decode( wp_kses_post( $product_content ), ENT_NOQUOTES ), $editor_id, $settings );
							?>
						</div>
						<div class="msf-form-group">
							<label for="product_short_description"><?php esc_html_e( 'Prduct Short Description', 'msfc-wfm' ); ?></strong></label>
							<textarea class="msf-form-control" id="product_short_description" name="product_short_description" placeholder="<?php echo esc_attr__( 'Product short description', 'msfc-wfm' ); ?>" rows="4"><?php echo esc_textarea( $product_excerpt ); ?></textarea>
						</div>
						<div class="msf-form-group">
							<div class="row">
								<div class="col-md-3">
									<label for="product_thumbnail_id"><?php esc_html_e( 'Prouduct Image', 'msfc-wfm' ); ?></label>
									<?php
									$thumbnail_url = '';
									if ( $thumbnail_id ) {
										$thumbnail_url = wp_get_attachment_image_url( $thumbnail_id, 'thumbnail' );
									}
									?>
									<input type="hidden" id="product_thumbnail_id" name="product_thumbnail_id" value="<?php echo esc_attr( $thumbnail_id ); ?>">
									<input type="hidden" id="product_thumbnail_url" name="product_thumbnail_url" value="<?php echo esc_url( $thumbnail_url ); ?>">
									<div id="product-single-image" class="image-drop-container<?php echo esc_attr( $thumbnail_id ? ' image-drop-bg' : '' ); ?>">
										<div id="product_thumb_img" class="preview-image">
											<?php if ( $thumbnail_url ) : ?>
												<img src="<?php echo esc_url( $thumbnail_url ); ?>" alt="<?php esc_attr_e( 'Product thumbnail', 'shop-front' ); ?>">
											<?php endif; ?>
										</div> 
										<div class="image-drop-text">
											<i class="las la-image"></i>
											<?php if ( $thumbnail_id ) : ?>
												<span><?php esc_html_e( 'Remove Image', 'msfc-wfm' ); ?></span>
											<?php else : ?>
												<span><?php esc_html_e( 'Upload Image', 'msfc-wfm' ); ?></span>
											<?php endif; ?>
										</div>
									</div>
								</div>
								<div class="col-md-9">
									<label for="product_image_gallery"><?php esc_html_e( 'Prouduct Gallery Images', 'msfc-wfm' ); ?></label>
									<?php
									$gallery_ids_string = '';
									$gallery_urls       = array();
									if ( ! empty( $gallery_image_ids ) ) {
										$gallery_ids_string = implode( ',', $gallery_image_ids );
										foreach ( $gallery_image_ids as $gallery_id ) {
											$gallery_urls[] = wp_get_attachment_image_url( $gallery_id, 'thumbnail' );
										}
									}
									?>
									<input type="hidden" id="product_image_gallery" name="product_image_gallery" value="<?php echo esc_attr( $gallery_ids_string ); ?>">
									<input type="hidden" id="product_image_gallery_url" name="product_image_gallery_url" value="<?php echo esc_attr( implode( ',', $gallery_urls ) ); ?>">
									<div class="product-gallery-images-wrapper
									<?php
									if ( empty( $gallery_image_ids ) ) {
										echo esc_attr( ' gallery-has-no-image' ); }
									?>
									">
										<div id="product_gallery_img" class="preview-image privew-gimages">
											<?php if ( ! empty( $gallery_image_ids ) ) : ?>
												<?php foreach ( $gallery_image_ids as $gallery_id ) : ?>
													<?php $gallery_url = wp_get_attachment_image_url( $gallery_id, 'thumbnail' ); ?>
													<?php if ( $gallery_url ) : ?>
														<div class="preview-image-box">
															<a href="#" class="remove-gallery-image" data-id="<?php echo esc_attr( $gallery_id ); ?>"><?php esc_html_e( '×', 'shop-front' ); ?></a>
															<img src="<?php echo esc_url( $gallery_url ); ?>" alt="<?php esc_attr_e( 'Gallery image', 'shop-front' ); ?>">
														</div>
													<?php endif; ?>
												<?php endforeach; ?>
											<?php endif; ?>
										</div>
										<div id="product-gallery-images" class="image-drop-container
										<?php
										if ( ! empty( $gallery_image_ids ) ) {
											echo esc_attr( ' sm-gallery-image-uploader' ); }
										?>
										">
											<div class="image-drop-text">
												<span class="add-gl-img-icon">
													<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="24" height="24">
														<path d="M23,11H13V1a1,1,0,0,0-1-1h0a1,1,0,0,0-1,1V11H1a1,1,0,0,0-1,1H0a1,1,0,0,0,1,1H11V23a1,1,0,0,0,1,1h0a1,1,0,0,0,1-1V13H23a1,1,0,0,0,1-1h0A1,1,0,0,0,23,11Z"></path>
													</svg>
												</span>
												<span class="add-gl-img-text"><?php esc_html_e( 'Upload Gallery Images', 'msfc-wfm' ); ?></span>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="msf-form-group">
									<label for="product_category"><?php esc_html_e( 'Select Category', 'msfc-wfm' ); ?> <span class="req"><?php esc_html_e( '*', 'msfc-wfm' ); ?></span></label>
									<select class="msf-form-control msf-select2" id="product_category" name="product_category[]" data-placeholder="<?php esc_attr_e( 'Select category', 'msfc-wfm' ); ?>" data-allow_clear="true" multiple>
										<?php
										$all_categories = get_categories(
											array(
												'orderby'  => 'name',
												'order'    => 'ASC',
												'taxonomy' => 'product_cat',
												'hide_empty' => false,
											)
										);
										foreach ( $all_categories as $category ) {
											$selected = in_array( $category->term_id, $product_categories, true ) ? 'selected' : '';
											?>
										<option value="<?php echo esc_attr( $category->term_id ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $category->name ); ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="msf-card msf-card-with-header msf-mb-24">
					<h3 class="msf-card-title"><?php esc_html_e( 'Pricing', 'shop-front' ); ?></h3>
					<div class="msf-card-content">
						<div class="row">
							<div class="col-md-6">
								<div class="msf-form-group">
									<label for="regular_price"><?php esc_html_e( 'Regular Price', 'msfc-wfm' ); ?> <span class="req"><?php esc_html_e( '*', 'msfc-wfm' ); ?></span></label>
									<input type="number" class="msf-form-control" id="regular_price" name="regular_price" step="any" value="<?php echo esc_attr( $regular_price ); ?>">
								</div>
							</div>
							<div class="col-md-6">
								<div class="msf-form-group">
									<div class="row">
										<div class="col-md-8">
											<label for="sale_price"><?php esc_html_e( 'Sale Price', 'msfc-wfm' ); ?></label>
										</div>
										<div class="col-md-4 text-right">
											<a href="#" class="sale_schedule"><?php esc_html_e( 'Schedule', 'msfc-wfm' ); ?></a>
											<a href="#" class="cancel_sale_schedule"><?php esc_html_e( 'Cancel', 'msfc-wfm' ); ?></a>
										</div>
									</div>
									<input type="number" class="msf-form-control" id="sale_price" name="sale_price" step="any" value="<?php echo esc_attr( $sale_price ); ?>">
								</div>
							</div>
							<div class="col-md-12">
								<div class="row sale_price_dates_fields">
									<div class="col-md-6">
										<div class="msf-form-group">
											<label for="_sale_price_dates_from"><?php esc_html_e( 'Sale Price Date From', 'msfc-wfm' ); ?></label>
											<input type="text" class="msf-form-control" id="_sale_price_dates_from" name="_sale_price_dates_from" value="<?php echo esc_attr( $sale_price_dates_from ); ?>">
										</div>
									</div>
									<div class="col-md-6">
										<div class="msf-form-group">
											<label for="_sale_price_dates_to"><?php esc_html_e( 'Sale Price Date To', 'msfc-wfm' ); ?></label>
											<input type="text" class="msf-form-control" id="_sale_price_dates_to" name="_sale_price_dates_to" value="<?php echo esc_attr( $sale_price_dates_to ); ?>">
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="msf-card msf-card-with-header msf-mb-24">
					<h3 class="msf-card-title"><?php esc_html_e( 'Inventory', 'shop-front' ); ?></h3>
					<div class="msf-card-content">
						<div class="row">
							<div class="col-md-6">
								<div class="msf-form-group">
									<label for="_sku"><?php esc_html_e( 'SKU', 'msfc-wfm' ); ?></label>
									<input type="text" class="msf-form-control" id="_sku" name="_sku" value="<?php echo esc_attr( $sku ); ?>">
								</div>
							</div>
							<div class="col-md-6">
								<div class="msf-form-group">
									<label for="_global_unique_id"><?php esc_html_e( 'GTIN, UPC, EAN, or ISBN', 'msfc-wfm' ); ?></label>
									<input type="text" class="msf-form-control" id="_global_unique_id" name="_global_unique_id" value="<?php echo esc_attr( $global_unique_id ); ?>">
								</div>
							</div>
							<div class="col-md-12">
								<div class="msf-form-group msf-form-switch">
									<input type="checkbox" class="msf-form-control" id="_manage_stock" name="_manage_stock" value="yes" <?php checked( $manage_stock, 'yes' ); ?>>
									<label for="_manage_stock"><?php esc_html_e( 'Enable product stock management', 'msfc-wfm' ); ?></label>
								</div>
							</div>
							<div class="col-md-12 show_if_stock_management">
								<div class="row">
									<div class="col-md-6">
										<div class="msf-form-group">
											<label for="_stock_quantity"><?php esc_html_e( 'Quantity', 'msfc-wfm' ); ?></label>
											<input type="number" class="msf-form-control" id="_stock_quantity" name="_stock_quantity" step="any" value="<?php echo esc_attr( $stock_quantity ? $stock_quantity : 1 ); ?>">
										</div>
									</div>
									<div class="col-md-6">
										<div class="msf-form-group">
											<label for="_low_stock_amount"><?php esc_html_e( 'Low Stock Threshold', 'msfc-wfm' ); ?></label>
											<input type="number" class="msf-form-control" id="_low_stock_amount" name="_low_stock_amount" step="any" value="<?php echo esc_attr( $low_stock_amount ); ?>">
										</div>
									</div>
									<div class="col-md-6">
										<div class="msf-form-group">
											<label for="_backorders"><?php esc_html_e( 'Allow Backorders?', 'msfc-wfm' ); ?></label>
											<select class="msf-form-control" id="_backorders" name="_backorders">
												<option value="no" <?php selected( $backorders, 'no' ); ?>><?php esc_html_e( 'Do not allow', 'msfc-wfm' ); ?></option>
												<option value="notify" <?php selected( $backorders, 'notify' ); ?>><?php esc_html_e( 'Allow but notify customer', 'msfc-wfm' ); ?></option>
												<option value="yes" <?php selected( $backorders, 'yes' ); ?>><?php esc_html_e( 'Allow', 'msfc-wfm' ); ?></option>
											</select>
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-6 _stock_status_field">
								<div class="msf-form-group">
									<label for="_stock_status"><?php esc_html_e( 'Stock Status', 'msfc-wfm' ); ?></label>
									<select class="msf-form-control" id="_stock_status" name="_stock_status">
										<?php foreach ( wc_get_product_stock_status_options() as $key => $value ) { ?>
										<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $stock_status, $key ); ?>><?php echo esc_html( $value ); ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class="col-md-12">
								<div class="msf-form-group msf-form-switch">
									<input type="checkbox" class="msf-form-control" id="_sold_individually" name="_sold_individually" value="yes" <?php checked( $sold_individually, 'yes' ); ?>>
									<label for="_sold_individually"><?php esc_html_e( 'Limit Purchases to 1 Item Per Order?', 'msfc-wfm' ); ?></label>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="msf-card msf-card-with-header msf-mb-24">
					<h3 class="msf-card-title"><?php esc_html_e( 'Shipping', 'shop-front' ); ?></h3>
					<div class="msf-card-content">
						<div class="row">
							<div class="col-md-4">
								<div class="msf-form-group">
									<label for="weight"><?php esc_html_e( 'Weight', 'msfc-wfm' ); ?> (<?php echo esc_html( get_option( 'woocommerce_weight_unit' ) ); ?>)</label>
									<input type="text" class="msf-form-control" id="weight" name="weight" placeholder="<?php echo esc_attr__( 'Weight in decimal form', 'msfc-wfm' ); ?>" value="<?php echo esc_attr( $weight ); ?>">
								</div>
							</div>
							<div class="col-md-8">
								<div class="msf-form-group">
									<label for="length"><?php esc_html_e( 'Dimensions', 'msfc-wfm' ); ?> (<?php echo esc_html( get_option( 'woocommerce_dimension_unit' ) ); ?>)</label>
									<div class="row">
										<div class="col-md-4">
											<input type="number" class="msf-form-control" id="length" name="length" placeholder="<?php echo esc_attr__( 'Length', 'msfc-wfm' ); ?>" step="any" value="<?php echo esc_attr( $length ); ?>">
										</div>
										<div class="col-md-4">
											<input type="number" class="msf-form-control" id="width" name="width" placeholder="<?php echo esc_attr__( 'Width', 'msfc-wfm' ); ?>" step="any" value="<?php echo esc_attr( $width ); ?>">
										</div>
										<div class="col-md-4">
											<input type="number" class="msf-form-control" id="height" name="height" placeholder="<?php echo esc_attr__( 'Height', 'msfc-wfm' ); ?>" step="any" value="<?php echo esc_attr( $height ); ?>">
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-12">
								<div class="msf-form-group">
								<?php
									$shipping_class_args = array(
										'taxonomy'         => 'product_shipping_class',
										'hide_empty'       => 0,
										'show_option_none' => __( 'No shipping class', 'woocommerce' ),
										'name'             => 'product_shipping_class',
										'id'               => 'product_shipping_class',
										'selected'         => $shipping_class_id,
										'class'            => 'msf-form-control',
										'orderby'          => 'name',
									);
									?>
									<label for="product_shipping_class"><?php esc_html_e( 'Shipping Class', 'msfc-wfm' ); ?></label>
									<?php wp_dropdown_categories( $shipping_class_args ); ?>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="msf-card msf-card-with-header msf-mb-24">
					<h3 class="msf-card-title"><?php esc_html_e( 'Linked Products', 'shop-front' ); ?></h3>
					<div class="msf-card-content">
						<div class="row">
							<div class="col-md-6">
								<div class="msf-form-group search-group">
									<label for="upsell_ids"><?php esc_html_e( 'Upsells', 'msfc-wfm' ); ?></label>
									<?php
										// phpcs:enable WordPress.Security.NonceVerification.Recommended
										$excluded_product_types = array_diff( array_keys( wc_get_product_types() ), array( 'simple', 'variable' ) );
									?>
									<select class="msf-form-control wc-product-search" id="upsell_ids" name="upsell_ids[]" data-action="woocommerce_json_search_products_and_variations" data-exclude_type="<?php echo esc_attr( implode( ',', $excluded_product_types ) ); ?>" data-display_stock="true" data-placeholder="<?php esc_attr_e( 'Select product&hellip;', 'woocommerce' ); ?>" data-allow_clear="true" multiple>
										<?php
										if ( ! empty( $upsell_ids ) ) {
											foreach ( $upsell_ids as $upsell_id ) {
												$upsell_product = wc_get_product( $upsell_id );
												if ( $upsell_product ) {
													echo '<option value="' . esc_attr( $upsell_id ) . '" selected="selected">' . esc_html( $upsell_product->get_name() ) . '</option>';
												}
											}
										}
										?>
									</select>
								</div>
							</div>
							<div class="col-md-6">
								<div class="msf-form-group">
									<label for="crosssell_ids"><?php esc_html_e( 'Cross-sells', 'msfc-wfm' ); ?></label>
									<select class="msf-form-control wc-product-search" id="crosssell_ids" name="crosssell_ids[]" data-action="woocommerce_json_search_products_and_variations" data-exclude_type="<?php echo esc_attr( implode( ',', $excluded_product_types ) ); ?>" data-display_stock="true" data-placeholder="<?php esc_attr_e( 'Select product&hellip;', 'woocommerce' ); ?>" data-allow_clear="true" multiple>
										<?php
										if ( ! empty( $crosssell_ids ) ) {
											foreach ( $crosssell_ids as $crosssell_id ) {
												$crosssell_product = wc_get_product( $crosssell_id );
												if ( $crosssell_product ) {
													echo '<option value="' . esc_attr( $crosssell_id ) . '" selected="selected">' . esc_html( $crosssell_product->get_name() ) . '</option>';
												}
											}
										}
										?>
									</select>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="msf-card msf-card-with-header msf-mb-24">
					<h3 class="msf-card-title"><?php esc_html_e( 'Others', 'shop-front' ); ?></h3>
					<div class="msf-card-content">
						<div class="row">
							<div class="col-md-6">
								<div class="msf-form-group">
									<label for="_visibility"><?php esc_html_e( 'Catalog Visibility', 'msfc-wfm' ); ?></label>
									<select class="msf-form-control" id="_visibility" name="_visibility">
										<option value="visible" <?php selected( $visibility, 'visible' ); ?>><?php esc_html_e( 'Shop and search results', 'msfc-wfm' ); ?></option>
										<option value="catalog" <?php selected( $visibility, 'catalog' ); ?>><?php esc_html_e( 'Shop only', 'msfc-wfm' ); ?></option>
										<option value="search" <?php selected( $visibility, 'search' ); ?>><?php esc_html_e( 'Search results only', 'msfc-wfm' ); ?></option>
										<option value="hidden" <?php selected( $visibility, 'hidden' ); ?>><?php esc_html_e( 'Hidden', 'msfc-wfm' ); ?></option>
									</select>
								</div>
							</div>
							<div class="col-md-6">
								<div class="msf-form-group">
									<label for="menu_order"><?php esc_html_e( 'Menu Order', 'msfc-wfm' ); ?></label>
									<input type="number" class="msf-form-control" id="menu_order" name="menu_order" value="<?php echo esc_attr( $product_menu_order ); ?>">
								</div>
							</div>
							<div class="col-md-12">
								<div class="msf-form-group msf-form-switch">
									<input type="checkbox" class="msf-form-control" id="_featured" name="_featured" value="yes" <?php checked( $featured, 'yes' ); ?>>
									<label for="_featured"><?php esc_html_e( 'Mark this product as featured.', 'msfc-wfm' ); ?></label>
								</div>
							</div>
							<div class="col-md-12">
								<div class="msf-form-group">
									<label for="_purchase_note"><?php esc_html_e( 'Purchase Note', 'msfc-wfm' ); ?></strong></label>
									<textarea class="msf-form-control" id="_purchase_note" name="_purchase_note" rows="2" cols="20"><?php echo esc_textarea( $purchase_note ); ?></textarea>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="msf-card msf-card-with-header">
					<h3 class="msf-card-title"><?php esc_html_e( 'General Informations', 'shop-front' ); ?></h3>
					<div class="msf-card-content">
						<div class="msf-form-group">
							<label for="post_type"><?php esc_html_e( 'Type', 'msfc-wfm' ); ?> <span class="req"><?php esc_html_e( '*', 'msfc-wfm' ); ?></span></label>
							<select class="msf-form-control" id="post_type" name="post_type">
								<?php foreach ( $product_types as $key => $value ) : ?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $product_type, $key ); ?>><?php echo esc_html( $value ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="msf-form-group">
							<label for="post_status"><?php esc_html_e( 'Status', 'msfc-wfm' ); ?> <span class="req"><?php esc_html_e( '*', 'msfc-wfm' ); ?></span></label>
							<select class="msf-form-control" id="post_status" name="post_status">
								<?php foreach ( $product_statuses as $key => $value ) : ?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $product_status, $key ); ?>><?php echo esc_html( $value ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="msf-form-group">
							<label for="product_brand"><?php esc_html_e( 'Brand', 'msfc-wfm' ); ?></label>
							<select class="msf-form-control" id="product_brand" name="product_brand">
									<option value=""><?php echo esc_html__( 'Select brand', 'msfc-wfm' ); ?></option>
								<?php foreach ( $product_brands as $brand ) : ?>
									<option value="<?php echo esc_attr( $brand->term_id ); ?>" <?php selected( $product_brand, $brand->term_id ); ?>><?php echo esc_html( $brand->name ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="msf-form-group">
							<label for="product_tags"><?php esc_html_e( 'Tags', 'msfc-wfm' ); ?></label>
							<select class="msf-form-control msf-select2" id="product_tags" name="product_tags[]" data-placeholder="<?php esc_attr_e( 'Select tags', 'msfc-wfm' ); ?>" data-allow_clear="true" multiple>
								<?php
								$all_tags = get_terms(
									array(
										'taxonomy'   => 'product_tag',
										'orderby'    => 'name',
										'order'      => 'ASC',
										'hide_empty' => false,
									)
								);
								if ( ! is_wp_error( $all_tags ) && ! empty( $all_tags ) ) {
									foreach ( $all_tags as $tag ) {
										$selected = in_array( $tag->term_id, $product_tags_array, true ) ? 'selected' : '';
										?>
								<option value="<?php echo esc_attr( $tag->term_id ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $tag->name ); ?></option>
										<?php
									}
								}
								?>
							</select>
						</div>
						<div class="msf-form-group">
							<label for="comment_status"><?php esc_html_e( 'Enable Reviews?', 'msfc-wfm' ); ?></label>
							<select class="msf-form-control" id="comment_status" name="comment_status">
								<option value="yes" <?php selected( $is_reviews_allowed, true ); ?>><?php esc_html_e( 'Yes', 'msfc-wfm' ); ?></option>
								<option value="no" <?php selected( $is_reviews_allowed, false ); ?>><?php esc_html_e( 'No', 'msfc-wfm' ); ?></option>
							</select>
						</div>
					</div>
				</div>
				<div class="form-group">
					<?php if ( $is_edit_mode ) : ?>
						<?php wp_nonce_field( '_msfc_edit_product_', 'msfc_edit_product_nonce' ); ?>
						<input type="hidden" name="product_id" value="<?php echo esc_attr( $product_id ); ?>">
						<input type="hidden" name="action" value="msfc_edit_product_action">
					<?php else : ?>
						<?php wp_nonce_field( '_msfc_add_product_', 'msfc_add_product_nonce' ); ?>
						<input type="hidden" name="action" value="msfc_add_product_action">
					<?php endif; ?>
					<div class="msf-button-group">
						<button class="my-shop-front-button" name="save_product" type="submit">
							<?php echo $is_edit_mode ? esc_html__( 'Update Product', 'shop-front' ) : esc_html__( 'Add Product', 'shop-front' ); ?>
						</button>
						<a href="<?php echo esc_url( msfc_get_navigation_url( 'products' ) ); ?>" class="my-shop-front-button my-shop-front-button-light"><?php esc_html_e( 'Back', 'shop-front' ); ?></a>
					</div>
				</div>
			</div>
		</div>
	</form>
	<!-- Success and Error Messages -->
	<div x-show="message" x-text="message" class="alert"></div>
	<div x-show="error" x-text="error" class="alert alert-danger"></div>
</div>