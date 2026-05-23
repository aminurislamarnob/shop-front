<?php
/**
 * Product quick edit fields (used in the StoreSuite quick edit modal via AJAX).
 *
 * @package StoreSuite
 *
 * @var int               $product_id   Product post ID.
 * @var \WC_Product       $product      Product object.
 * @var \WP_Post|null $product_post Product post (optional; loaded if missing).
 */

use Automattic\WooCommerce\Enums\CatalogVisibility;
use Automattic\WooCommerce\Enums\ProductType;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $product_post ) || ! $product_post instanceof \WP_Post ) {
	$product_post = get_post( $product_id );
}
if ( ! $product_post instanceof \WP_Post ) {
	return;
}

$post_title   = $product_post->post_title;
$post_status  = $product_post->post_status;
$ptype        = $product->get_type();
$reviews_ok   = $product->get_reviews_allowed();
$is_virtual   = $product->get_virtual();
$manage_stock = $product->get_manage_stock() && ProductType::GROUPED !== $ptype;

$dec_sep = wc_get_price_decimal_separator();
$fmt     = static function ( $raw ) use ( $dec_sep ) {
	return str_replace( '.', $dec_sep, (string) $raw );
};

$shipping_class_terms = get_terms(
	array(
		'taxonomy'   => 'product_shipping_class',
		'hide_empty' => false,
	)
);
if ( is_wp_error( $shipping_class_terms ) ) {
	$shipping_class_terms = array();
}

$product_types = function_exists( 'wc_get_product_types' ) ? wc_get_product_types() : array();
$has_subscription = is_array( $product_types ) && array_key_exists( 'subscription', $product_types );
$show_prices      = in_array( $ptype, array( ProductType::SIMPLE, ProductType::EXTERNAL ), true )
	|| ( $has_subscription && 'subscription' === $ptype );
$show_shipping_class = ( ( ProductType::SIMPLE === $ptype && ! $is_virtual ) || ProductType::VARIABLE === $ptype );

$product_tag_objs = get_the_terms( $product_id, 'product_tag' );
if ( empty( $product_tag_objs ) || is_wp_error( $product_tag_objs ) ) {
	$product_tag_objs = array();
}
$product_tag_ids = wp_list_pluck( $product_tag_objs, 'term_id' );

$all_tags = get_terms(
	array(
		'taxonomy'   => 'product_tag',
		'orderby'    => 'name',
		'order'      => 'ASC',
		'hide_empty' => false,
	)
);
if ( is_wp_error( $all_tags ) ) {
	$all_tags = array();
}

$assigned_cat_ids = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );
if ( is_wp_error( $assigned_cat_ids ) ) {
	$assigned_cat_ids = array();
}

$all_categories = get_categories(
	array(
		'orderby'    => 'name',
		'order'      => 'ASC',
		'taxonomy'   => 'product_cat',
		'hide_empty' => false,
	)
);

$post_statuses = apply_filters( 'storesuite_product_quick_edit_post_statuses', storesuite_get_post_status(), $product_id );

$stock_statuses = array(
	'instock'    => __( 'In stock', 'storesuite' ),
	'outofstock' => __( 'Out of stock', 'storesuite' ),
);

$backorder_options = wc_get_product_backorder_options();

$visibilities = apply_filters(
	'woocommerce_product_visibility_options',
	array(
		CatalogVisibility::VISIBLE => __( 'Catalog &amp; search', 'storesuite' ),
		CatalogVisibility::CATALOG => __( 'Catalog', 'storesuite' ),
		CatalogVisibility::SEARCH  => __( 'Search', 'storesuite' ),
		CatalogVisibility::HIDDEN  => __( 'Hidden', 'storesuite' ),
	)
);

$inline_options = array(
	'is_sku_enabled'        => wc_product_sku_enabled(),
	'is_weight_enabled'     => wc_product_weight_enabled(),
	'is_dimensions_enabled' => wc_product_dimensions_enabled(),
	'can_manage_stock'      => 'yes' === get_option( 'woocommerce_manage_stock' ),
);

?>
<div class="storesuite-product-quick-edit-form-root">
	<fieldset class="storesuite-product-quick-edit-fieldset">
		<div class="row">
			<div class="col-md-6 storesuite-quick-edit-column">
				<div class="storesuite-form-group">
					<label class="storesuite-form-label" for="storesuite-qe-title-<?php echo esc_attr( (string) $product_id ); ?>"><?php esc_html_e( 'Title', 'storesuite' ); ?></label>
					<input type="text" id="storesuite-qe-title-<?php echo esc_attr( (string) $product_id ); ?>" class="storesuite-form-control" data-field-name="post_title" value="<?php echo esc_attr( $post_title ); ?>">
				</div>

				<div class="storesuite-form-group">
					<label class="storesuite-form-label" for="storesuite-qe-cat-<?php echo esc_attr( (string) $product_id ); ?>"><?php esc_html_e( 'Select Category', 'storesuite' ); ?></label>
					<select id="storesuite-qe-cat-<?php echo esc_attr( (string) $product_id ); ?>" class="storesuite-form-control storesuite-inline-quick-edit-select2" multiple="multiple" data-field-name="chosen_product_cat" data-placeholder="<?php esc_attr_e( 'Select category', 'storesuite' ); ?>" data-allow_clear="true">
						<?php foreach ( $all_categories as $category ) : ?>
							<?php
							$cat_selected = in_array( (int) $category->term_id, array_map( 'intval', $assigned_cat_ids ), true );
							?>
							<option value="<?php echo esc_attr( (string) $category->term_id ); ?>" <?php selected( $cat_selected, true ); ?>><?php echo esc_html( $category->name ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="storesuite-form-group">
					<label class="storesuite-form-label" for="storesuite-qe-tags-<?php echo esc_attr( (string) $product_id ); ?>"><?php esc_html_e( 'Tags', 'storesuite' ); ?></label>
					<select id="storesuite-qe-tags-<?php echo esc_attr( (string) $product_id ); ?>" class="storesuite-form-control storesuite-inline-quick-edit-select2" multiple="multiple" data-field-name="product_tag" data-placeholder="<?php esc_attr_e( 'Select tags', 'storesuite' ); ?>" data-allow_clear="true">
						<?php foreach ( $all_tags as $tag_term ) : ?>
							<?php if ( $tag_term instanceof \WP_Term ) : ?>
								<option value="<?php echo esc_attr( (string) $tag_term->term_id ); ?>" <?php selected( in_array( (int) $tag_term->term_id, array_map( 'intval', $product_tag_ids ), true ), true ); ?>><?php echo esc_html( $tag_term->name ); ?></option>
							<?php endif; ?>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="storesuite-form-group">
					<label class="storesuite-form-label" for="storesuite-qe-status-<?php echo esc_attr( (string) $product_id ); ?>"><?php esc_html_e( 'Status', 'storesuite' ); ?></label>
					<?php if ( 'pending' === $post_status ) : ?>
						<span class="storesuite-badge storesuite-badge-warning"><?php esc_html_e( 'Pending review', 'storesuite' ); ?></span>
						<input type="hidden" data-field-name="post_status" value="pending">
					<?php else : ?>
						<select id="storesuite-qe-status-<?php echo esc_attr( (string) $product_id ); ?>" class="storesuite-form-control" data-field-name="post_status">
							<?php foreach ( $post_statuses as $slug => $label ) : ?>
								<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $post_status, $slug ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
					<?php endif; ?>
				</div>

				<?php if ( $inline_options['is_sku_enabled'] ) : ?>
					<div class="storesuite-form-group">
						<label class="storesuite-form-label"><?php esc_html_e( 'SKU', 'storesuite' ); ?></label>
						<div class="storesuite-quick-edit-control">
							<input type="text" class="storesuite-form-control" data-field-name="sku" value="<?php echo esc_attr( (string) $product->get_sku() ); ?>">
						</div>
					</div>
				<?php endif; ?>

				<?php if ( $show_prices ) : ?>
					<div class="storesuite-form-group">
						<label class="storesuite-form-label"><?php esc_html_e( 'Price', 'storesuite' ); ?></label>
						<div class="storesuite-quick-edit-control">
							<input type="text" class="storesuite-form-control" data-field-name="_regular_price" value="<?php echo esc_attr( $fmt( $product->get_regular_price() ) ); ?>">
						</div>
					</div>
					<div class="storesuite-form-group">
						<label class="storesuite-form-label"><?php esc_html_e( 'Sale', 'storesuite' ); ?></label>
						<div class="storesuite-quick-edit-control">
							<input type="text" class="storesuite-form-control" data-field-name="_sale_price" value="<?php echo esc_attr( $fmt( $product->get_sale_price() ) ); ?>">
						</div>
					</div>
				<?php endif; ?>

				<?php do_action( 'storesuite_quick_edit_before_column_1_ends', $product_id ); ?>
			</div>

			<div class="col-md-6 storesuite-quick-edit-column">
				<?php if ( $inline_options['is_weight_enabled'] ) : ?>
					<div class="storesuite-form-group">
						<label class="storesuite-form-label"><?php esc_html_e( 'Weight', 'storesuite' ); ?></label>
						<div class="storesuite-quick-edit-control">
							<input type="text" class="storesuite-form-control" data-field-name="weight" value="<?php echo esc_attr( (string) $product->get_weight() ); ?>">
						</div>
					</div>
				<?php endif; ?>

				<?php if ( $inline_options['is_dimensions_enabled'] ) : ?>
					<div class="storesuite-form-group storesuite-quick-edit-dimensions-row">
						<label class="storesuite-form-label"><?php esc_html_e( 'L/W/H', 'storesuite' ); ?></label>
						<div class="storesuite-quick-edit-control storesuite-quick-edit-dimensions-inputs">
							<input type="text" class="storesuite-form-control" data-field-name="length" value="<?php echo esc_attr( (string) $product->get_length() ); ?>" placeholder="<?php esc_attr_e( 'Length', 'storesuite' ); ?>">
							<input type="text" class="storesuite-form-control" data-field-name="width" value="<?php echo esc_attr( (string) $product->get_width() ); ?>" placeholder="<?php esc_attr_e( 'Width', 'storesuite' ); ?>">
							<input type="text" class="storesuite-form-control" data-field-name="height" value="<?php echo esc_attr( (string) $product->get_height() ); ?>" placeholder="<?php esc_attr_e( 'Height', 'storesuite' ); ?>">
						</div>
					</div>
				<?php endif; ?>

				<?php if ( $show_shipping_class ) : ?>
					<div class="storesuite-form-group">
						<label class="storesuite-form-label"><?php esc_html_e( 'Shipping class', 'storesuite' ); ?></label>
						<div class="storesuite-quick-edit-control">
							<select class="storesuite-form-control" data-field-name="shipping_class_id">
								<option value="_no_shipping_class"><?php esc_html_e( 'No shipping class', 'storesuite' ); ?></option>
								<?php
								$ship_id = $product->get_shipping_class_id();
								foreach ( $shipping_class_terms as $shipping_term ) {
									if ( ! $shipping_term instanceof \WP_Term ) {
										continue;
									}
									echo '<option value="' . esc_attr( $shipping_term->slug ) . '"' . selected( (int) $ship_id, (int) $shipping_term->term_id, false ) . '>' . esc_html( $shipping_term->name ) . '</option>';
								}
								?>
							</select>
						</div>
					</div>
				<?php endif; ?>

				<div class="storesuite-form-group">
					<label class="storesuite-form-label"><?php esc_html_e( 'Visibility', 'storesuite' ); ?></label>
					<div class="storesuite-quick-edit-control">
						<select class="storesuite-form-control" data-field-name="_visibility">
							<?php foreach ( $visibilities as $vslug => $vlabel ) : ?>
								<option value="<?php echo esc_attr( $vslug ); ?>" <?php selected( $product->get_catalog_visibility(), $vslug ); ?>><?php echo esc_html( $vlabel ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>

				<?php if ( ( ProductType::SIMPLE === $ptype || ProductType::VARIABLE === $ptype ) && $inline_options['can_manage_stock'] ) : ?>
					<div class="storesuite-form-group storesuite-quick-edit-switch-row">
						<div class="storesuite-quick-edit-control storesuite-form-group storesuite-form-switch">
							<input type="checkbox" id="storesuite-qe-manage-stock-<?php echo esc_attr( (string) $product_id ); ?>" class="storesuite-form-control" data-field-name="manage_stock" value="1" data-field-toggler <?php checked( $manage_stock ); ?> <?php disabled( ProductType::GROUPED === $ptype ); ?>>
							<label for="storesuite-qe-manage-stock-<?php echo esc_attr( (string) $product_id ); ?>"><?php esc_html_e( 'Manage stock?', 'storesuite' ); ?></label>
						</div>
					</div>

					<div class="storesuite-form-group storesuite-stock-qty-row<?php echo $manage_stock ? '' : ' storesuite-hide'; ?>" data-field-toggle="manage_stock" data-field-show-on="true">
						<label class="storesuite-form-label"><?php esc_html_e( 'Stock quantity', 'storesuite' ); ?></label>
						<div class="storesuite-quick-edit-control">
							<input type="text" class="storesuite-form-control" data-field-name="stock_quantity" value="<?php echo esc_attr( null !== $product->get_stock_quantity() ? (string) $product->get_stock_quantity() : '' ); ?>">
						</div>
					</div>

					<div class="storesuite-form-group storesuite-stock-status-row<?php echo $manage_stock ? ' storesuite-hide' : ''; ?>" data-field-toggle="manage_stock" data-field-show-on="false">
						<label class="storesuite-form-label"><?php esc_html_e( 'In stock?', 'storesuite' ); ?></label>
						<div class="storesuite-quick-edit-control">
							<select class="storesuite-form-control" data-field-name="stock_status">
								<?php foreach ( $stock_statuses as $skey => $slabel ) : ?>
									<option value="<?php echo esc_attr( $skey ); ?>" <?php selected( $product->get_stock_status(), $skey ); ?>><?php echo esc_html( $slabel ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>

					<div class="storesuite-form-group storesuite-backorder-row<?php echo $manage_stock ? '' : ' storesuite-hide'; ?>" data-field-toggle="manage_stock" data-field-show-on="true">
						<label class="storesuite-form-label"><?php esc_html_e( 'Backorders?', 'storesuite' ); ?></label>
						<div class="storesuite-quick-edit-control">
							<select class="storesuite-form-control" data-field-name="backorders">
								<?php foreach ( $backorder_options as $bkey => $blabel ) : ?>
									<option value="<?php echo esc_attr( $bkey ); ?>" <?php selected( $product->get_backorders(), $bkey ); ?>><?php echo esc_html( $blabel ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
				<?php elseif ( ProductType::GROUPED === $ptype ) : ?>
					<div class="storesuite-form-group">
						<label class="storesuite-form-label"><?php esc_html_e( 'In stock?', 'storesuite' ); ?></label>
						<div class="storesuite-quick-edit-control">
							<select class="storesuite-form-control" data-field-name="stock_status">
								<?php foreach ( $stock_statuses as $skey => $slabel ) : ?>
									<option value="<?php echo esc_attr( $skey ); ?>" <?php selected( $product->get_stock_status(), $skey ); ?>><?php echo esc_html( $slabel ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
				<?php endif; ?>

					<div class="storesuite-form-group storesuite-quick-edit-switch-row">
						<div class="storesuite-quick-edit-control storesuite-form-group storesuite-form-switch">
							<input type="checkbox" id="storesuite-qe-reviews-<?php echo esc_attr( (string) $product_id ); ?>" class="storesuite-form-control" data-field-name="reviews_allowed" value="1" <?php checked( $reviews_ok, true ); ?>>
							<label for="storesuite-qe-reviews-<?php echo esc_attr( (string) $product_id ); ?>"><?php esc_html_e( 'Enable reviews', 'storesuite' ); ?></label>
						</div>
					</div>

				<?php do_action( 'storesuite_quick_edit_before_column_2_ends', $product_id, $inline_options ); ?>
			</div>
		</div>

		<?php do_action( 'storesuite_after_quick_edit_form_fields', $product_id ); ?>

		<input type="hidden" data-field-name="ID" value="<?php echo esc_attr( (string) $product_id ); ?>">
		<input type="hidden" data-field-name="product_type" value="<?php echo esc_attr( $ptype ); ?>">
		<input type="hidden" data-field-name="woocommerce_quick_edit" value="1">
		<input type="hidden" data-field-name="woocommerce_quick_edit_nonce" value="<?php echo esc_attr( wp_create_nonce( 'woocommerce_quick_edit_nonce' ) ); ?>">

	</fieldset>
</div>
