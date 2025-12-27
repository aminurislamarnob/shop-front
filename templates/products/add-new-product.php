<?php
/**
 * MSFC product add page
 *
 * @package ShopFront
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
global $post;

if ( isset( $post->ID ) && $post->ID && 'product' === $post->post_type ) {
    $product_id      = $post->ID;
    $product_title   = $post->post_title;
    $product_content = $post->post_content;
    $product_excerpt = $post->post_excerpt;
    $product_status  = $post->post_status;
    $product      = wc_get_product( $post_id );
}

// $terms            = wp_get_object_terms( $product_id, 'product_type' );
$product_type     = 'simple';
$product_status  = ( ! empty ( $product_status ) ) ? $product_status : 'publish';


$product_types    = apply_filters( 'msf_product_types', array( 'simple' => __( 'Simple', 'shop-front' ) ) );
$product_statuses    = apply_filters( 'msf_product_statuses', array( 'publish' => __( 'Simple', 'shop-front' ) ) );
$product_brands = pluginizelab_shop_front()->msf_product_brands->get_product_brands();

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
			<div x-data="productAddFormHandler()">
				<form id="msfc-add-product" @submit.prevent="handleProductSubmission" method="POST">
					<div class="row">
						<div class="col-md-8">
							<div class="msf-card msf-mb-24">
								<div class="msf-card-content">
									<div class="msf-form-group">
										<label for="product_title"><?php esc_html_e( 'Product Title', 'msfc-wfm' ); ?> <span class="req"><?php esc_html_e( '*', 'msfc-wfm' ); ?></span></strong></label>
										<input type="text" class="msf-form-control" id="product_title" name="product_title" placeholder="<?php echo esc_attr__( 'Product name', 'msfc-wfm' ); ?>">
									</div>
									<div class="msf-form-group">
										<label for="product_description"><?php esc_html_e( 'Product Description', 'msfc-wfm' ); ?> <span class="req"><?php esc_html_e( '*', 'msfc-wfm' ); ?></span></strong></label>
										<?php
										$content   = '';
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
										wp_editor( htmlspecialchars_decode( wp_kses_post( $content ), ENT_NOQUOTES ), $editor_id, $settings );
										?>
									</div>
									<div class="msf-form-group">
										<label for="product_short_description"><?php esc_html_e( 'Prduct Short Description', 'msfc-wfm' ); ?></strong></label>
										<textarea class="msf-form-control" id="product_short_description" name="product_short_description" placeholder="<?php echo esc_attr__( 'Product short description', 'msfc-wfm' ); ?>" rows="4"></textarea>
									</div>
									<div class="msf-form-group">
										<div class="row">
											<div class="col-md-4">
												<label for="product_thumbnail_id"><?php esc_html_e( 'Upload Prouduct Image', 'msfc-wfm' ); ?></label>
												<input type="hidden" id="product_thumbnail_id" name="product_thumbnail_id">
												<input type="hidden" id="product_thumbnail_url" name="product_thumbnail_url">
												<div id="product-single-image" class="image-drop-container">
													<div id="product_thumb_img" class="preview-image"></div> 
													<div class="image-drop-text">
														<i class="las la-image"></i>
														<span><span><?php esc_html_e( 'Click Here', 'msfc-wfm' ); ?></span> <?php esc_html_e( 'To Upload Image', 'msfc-wfm' ); ?></span>
													</div>
												</div>
											</div>
											<div class="col-md-8">
												<label for="product_image_gallery"><?php esc_html_e( 'Upload Prouduct Gallery Images', 'msfc-wfm' ); ?></label>
												<input type="hidden" id="product_image_gallery" name="product_image_gallery">
												<input type="hidden" id="product_image_gallery_url" name="product_image_gallery_url">
												<div id="product-gallery-images" class="image-drop-container">
													<div id="product_gallery_img" class="preview-image privew-gimages"></div>
													<div class="image-drop-text">
														<i class="las la-image"></i>
														<span><span><?php esc_html_e( 'Click Here', 'msfc-wfm' ); ?></span> <?php esc_html_e( 'To Upload Gallery Images', 'msfc-wfm' ); ?></span>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12">
											<div class="msf-form-group">
												<label for="product_category"><?php esc_html_e( 'Select Category', 'msfc-wfm' ); ?> <span class="req"><?php esc_html_e( '*', 'msfc-wfm' ); ?></span></label>
												<select class="msf-form-control" id="product_category" name="product_category">
													<option value=""><?php esc_html_e( '-- Select category --', 'msfc-wfm' ); ?></option>
													<?php
													$product_categories = get_categories(
														array(
															'orderby'    => 'name',
															'order'      => 'ASC',
															'taxonomy'   => 'product_cat',
															'hide_empty' => false,
														)
													);
													foreach ( $product_categories as $product_category ) {
														?>
													<option value="<?php echo esc_attr( $product_category->term_id ); ?>"><?php echo esc_html( $product_category->name ); ?></option>
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
												<input type="number" class="msf-form-control" id="regular_price" name="regular_price" step="any">
											</div>
										</div>
										<div class="col-md-6">
											<div class="msf-form-group">
												<label for="sale_price"><?php esc_html_e( 'Sale Price', 'msfc-wfm' ); ?></label>
												<input type="number" class="msf-form-control" id="sale_price" name="sale_price" step="any">
											</div>
										</div>
										<div class="col-md-6">
											<div class="msf-form-group">
												<label for="_sale_price_dates_from"><?php esc_html_e( 'Sale Price Date From', 'msfc-wfm' ); ?></label>
												<input type="text" class="msf-form-control" id="_sale_price_dates_from" name="_sale_price_dates_from">
											</div>
										</div>
										<div class="col-md-6">
											<div class="msf-form-group">
												<label for="_sale_price_dates_to"><?php esc_html_e( 'Sale Price Date To', 'msfc-wfm' ); ?></label>
												<input type="text" class="msf-form-control" id="_sale_price_dates_to" name="_sale_price_dates_to">
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
												<input type="text" class="msf-form-control" id="_sku" name="_sku">
											</div>
										</div>
										<div class="col-md-6">
											<div class="msf-form-group">
												<label for="_global_unique_id"><?php esc_html_e( 'GTIN, UPC, EAN, or ISBN', 'msfc-wfm' ); ?></label>
												<input type="text" class="msf-form-control" id="_global_unique_id" name="_global_unique_id">
											</div>
										</div>
										<div class="col-md-12">
											<div class="msf-form-group msf-form-switch">
												<input type="checkbox" class="msf-form-control" id="_manage_stock" name="_manage_stock" value="yes">
												<label for="_manage_stock"><?php esc_html_e( 'Enable product stock management', 'msfc-wfm' ); ?></label>
											</div>
										</div>
										<div class="col-md-12 show_if_stock_management">
											<div class="row">
												<div class="col-md-6">
													<div class="msf-form-group">
														<label for="_stock_quantity"><?php esc_html_e( 'Quantity', 'msfc-wfm' ); ?></label>
														<input type="number" class="msf-form-control" id="_stock_quantity" name="_stock_quantity" step="any" value="1">
													</div>
												</div>
												<div class="col-md-6">
													<div class="msf-form-group">
														<label for="_low_stock_amount"><?php esc_html_e( 'Low Stock Threshold', 'msfc-wfm' ); ?></label>
														<input type="number" class="msf-form-control" id="_low_stock_amount" name="_low_stock_amount" step="any">
													</div>
												</div>
												<div class="col-md-6">
													<div class="msf-form-group">
														<label for="_backorders"><?php esc_html_e( 'Allow Backorders?', 'msfc-wfm' ); ?></label>
														<select class="msf-form-control" id="_backorders" name="_backorders">
															<option value="no"><?php esc_html_e( 'Do not allow', 'msfc-wfm' ); ?></option>
															<option value="notify"><?php esc_html_e( 'Allow but notify customer', 'msfc-wfm' ); ?></option>
															<option value="yes"><?php esc_html_e( 'Allow', 'msfc-wfm' ); ?></option>
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
													<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
													<?php } ?>
												</select>
											</div>
										</div>
										<div class="col-md-12">
											<div class="msf-form-group msf-form-switch">
												<input type="checkbox" class="msf-form-control" id="_sold_individually" name="_sold_individually" value="yes">
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
												<input type="text" class="msf-form-control" id="weight" name="weight" placeholder="<?php echo esc_attr__( 'Weight in decimal form', 'msfc-wfm' ); ?>">
											</div>
										</div>
										<div class="col-md-8">
											<div class="msf-form-group">
												<label for="length"><?php esc_html_e( 'Dimensions', 'msfc-wfm' ); ?> (<?php echo esc_html( get_option( 'woocommerce_dimension_unit' ) ); ?>)</label>
												<div class="row">
													<div class="col-md-4">
														<input type="number" class="msf-form-control" id="length" name="length" placeholder="<?php echo esc_attr__( 'Length', 'msfc-wfm' ); ?>" step="any">
													</div>
													<div class="col-md-4">
														<input type="number" class="msf-form-control" id="width" name="width" placeholder="<?php echo esc_attr__( 'Width', 'msfc-wfm' ); ?>" step="any">
													</div>
													<div class="col-md-4">
														<input type="number" class="msf-form-control" id="height" name="height" placeholder="<?php echo esc_attr__( 'Height', 'msfc-wfm' ); ?>" step="any">
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
													// 'selected'         => $product_object->get_shipping_class_id( 'edit' ),
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
												<option value="<?php echo esc_attr( $brand->term_id ); ?>" <?php //selected( $product_brand, $key ); ?>><?php echo esc_html( $brand->name ); ?></option>
											<?php endforeach; ?>
										</select>
									</div>
									<div class="msf-form-group">
										<label for="product_tags"><?php esc_html_e( 'Tags', 'msfc-wfm' ); ?> <small><?php esc_html_e( '(Tags must be comma separeted)', 'msfc-wfm' ); ?></small></label>
										<input type="text" class="msf-form-control" id="product_tags" name="product_tags" placeholder="<?php echo esc_attr__( 'Ex: shirt, t-shirt, men', 'msfc-wfm' ); ?>">
									</div>
									<div class="msf-form-group">
										<label for="comment_status"><?php esc_html_e( 'Enable Reviews?', 'msfc-wfm' ); ?></label>
										<select class="msf-form-control" id="comment_status" name="comment_status">
											<option value="open"><?php esc_html_e( 'Yes', 'msfc-wfm' ); ?></option>
											<option value="close"><?php esc_html_e( 'No', 'msfc-wfm' ); ?></option>
										</select>
									</div>
								</div>
							</div>
							<div class="form-group">
								<?php wp_nonce_field( '_msfc_add_product_', 'msfc_add_product_nonce' ); ?>
								<input type="hidden" name="action" value="msfc_add_product_action">
								<div class="msf-button-group">
									<button class="my-shop-front-button" name="save_product" type="submit"><?php esc_html_e( 'Add Product', 'shop-front' ); ?></button>
									<a href="<?php echo esc_url( msfc_get_navigation_url( 'products' ) ); ?>" class="my-shop-front-button my-shop-front-button-light">Back</a>
								</div>
							</div>
						</div>
					</div>
				</form>
				<!-- Success and Error Messages -->
				<div x-show="message" x-text="message" class="alert"></div>
				<div x-show="error" x-text="error" class="alert alert-danger"></div>
			</div>
		</main>
	</div>
</div>
<?php do_action( 'msf_dashboard_wrapper_end' ); ?>