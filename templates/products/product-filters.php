<?php
/**
 * Product List Filters
 *
 * @package StoreSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get current filter values
$current_category = isset( $_GET['product_cat'] ) ? absint( $_GET['product_cat'] ) : '';
$current_type     = isset( $_GET['product_type'] ) ? sanitize_text_field( wp_unslash( $_GET['product_type'] ) ) : '';
$current_stock    = isset( $_GET['stock_status'] ) ? sanitize_text_field( wp_unslash( $_GET['stock_status'] ) ) : '';
$current_brand    = isset( $_GET['product_brand'] ) ? absint( $_GET['product_brand'] ) : '';
$current_search   = isset( $_GET['search_by'] ) ? sanitize_text_field( wp_unslash( $_GET['search_by'] ) ) : '';
?>

<div class="msf-product-filters">
	<form method="get" class="msf-filters-form">
		<?php if ( ! empty( $current_search ) ) : ?>
			<input type="hidden" name="search_by" value="<?php echo esc_attr( $current_search ); ?>">
		<?php endif; ?>
		
		<div class="row justify-content-end">
			<!-- Filter by Category -->
			<div class="col-md-auto">
				<div class="msf-form-group">
			<select name="product_cat" class="msf-form-control">
			<option value=""><?php esc_html_e( 'Filter by category', 'storesuite' ); ?></option>
			<?php
			$categories = get_terms(
				array(
					'taxonomy'   => 'product_cat',
					'hide_empty' => true,
					'orderby'    => 'name',
					'order'      => 'ASC',
				)
			);
			if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
				foreach ( $categories as $category ) {
					printf(
						'<option value="%s" %s>%s (%d)</option>',
						esc_attr( $category->term_id ),
						selected( $current_category, $category->term_id, false ),
						esc_html( $category->name ),
						absint( $category->count )
					);
				}
			}
			?>
				</select>
				</div>
			</div>

			<!-- Filter by Product Type -->
			<div class="col-md-auto">
				<div class="msf-form-group">
			<select name="product_type" class="msf-form-control">
			<option value=""><?php esc_html_e( 'Filter by product type', 'storesuite' ); ?></option>
			<?php
			$product_types = wc_get_product_types();
			foreach ( $product_types as $type_key => $type_label ) {
				printf(
					'<option value="%s" %s>%s</option>',
					esc_attr( $type_key ),
					selected( $current_type, $type_key, false ),
					esc_html( $type_label )
				);
			}
			?>
				</select>
				</div>
			</div>

			<!-- Filter by Stock Status -->
			<div class="col-md-auto">
				<div class="msf-form-group">
			<select name="stock_status" class="msf-form-control">
			<option value=""><?php esc_html_e( 'Filter by stock status', 'storesuite' ); ?></option>
			<?php
			$stock_statuses = wc_get_product_stock_status_options();
			foreach ( $stock_statuses as $status_key => $status_label ) {
				printf(
					'<option value="%s" %s>%s</option>',
					esc_attr( $status_key ),
					selected( $current_stock, $status_key, false ),
					esc_html( $status_label )
				);
			}
			?>
				</select>
				</div>
			</div>

			<!-- Filter by Brand -->
			<div class="col-md-auto">
				<div class="msf-form-group">
			<select name="product_brand" class="msf-form-control">
			<option value=""><?php esc_html_e( 'Filter by brand', 'storesuite' ); ?></option>
			<?php
			$brands = get_terms(
				array(
					'taxonomy'   => 'product_brand',
					'hide_empty' => true,
					'orderby'    => 'name',
					'order'      => 'ASC',
				)
			);
			if ( ! empty( $brands ) && ! is_wp_error( $brands ) ) {
				foreach ( $brands as $brand ) {
					printf(
						'<option value="%s" %s>%s (%d)</option>',
						esc_attr( $brand->term_id ),
						selected( $current_brand, $brand->term_id, false ),
						esc_html( $brand->name ),
						absint( $brand->count )
					);
				}
			}
			?>
				</select>
				</div>
			</div>
			<div class="col-md-auto">
				<button type="submit" class="my-storesuite-button"><?php esc_html_e( 'Filter', 'storesuite' ); ?></button>
			</div>
		</div>

	</form>
</div>
