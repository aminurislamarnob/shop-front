<?php
/**
 * Single product row for the StoreSuite products list (used on first paint and after quick edit save).
 *
 * @package StoreSuite
 *
 * @var int         $product_id Product post ID.
 * @var \WC_Product $product    Product object.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$storesuite_wfm_thumb = get_the_post_thumbnail_url( $product_id, 'thumbnail' );
if ( empty( $storesuite_wfm_thumb ) ) {
	$storesuite_wfm_thumb = wc_placeholder_img_src( 'thumbnail' );
}
?>
<tr class="single-product-item" id="product-row-<?php echo esc_attr( (string) $product_id ); ?>">
	<td class="check-column">
		<label class="my-storesuite-checkbox">
			<input type="checkbox" name="bulk_product_ids[]" id="cb-select-<?php echo esc_attr( (string) $product_id ); ?>" value="<?php echo esc_attr( (string) $product_id ); ?>" class="my-storesuite-checkbox-input">
			<span class="my-storesuite-checkbox-back"></span>
			<span class="my-storesuite-tick">
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
					<path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z"/>
				</svg>
			</span>
		</label>
	</td>
	<td data-title="<?php esc_attr_e( 'Image', 'storesuite' ); ?>">
		<img src="<?php echo esc_url( $storesuite_wfm_thumb ); ?>" class="my-storesuite-thumb" alt="<?php echo esc_attr( get_the_title( $product_id ) ); ?>">
	</td>
	<td class="tbl-product-name" data-title="<?php esc_attr_e( 'Name', 'storesuite' ); ?>">
		<a href="<?php echo esc_url( get_the_permalink( $product_id ) ); ?>"><?php echo esc_html( get_the_title( $product_id ) ); ?></a>
	</td>
	<td data-title="<?php esc_attr_e( 'Category', 'storesuite' ); ?>">
		<?php echo wp_kses_post( wc_get_product_category_list( $product_id, ', ', '', '' ) ); ?>
	</td>
	<td data-title="<?php esc_attr_e( 'Status', 'storesuite' ); ?>">
		<span class="storesuite-badge storesuite-badge-<?php echo esc_attr( storesuite_get_post_status_class( get_post_status( $product_id ) ) ); ?>">
			<?php echo esc_html( storesuite_get_post_status( get_post_status( $product_id ) ) ); ?>
		</span>
	</td>
	<td data-title="<?php esc_attr_e( 'SKU', 'storesuite' ); ?>">
		<?php
		if ( $product->get_sku() ) {
			echo esc_html( $product->get_sku() );
		} else {
			echo '<span class="no-sku">&ndash;</span>';
		}
		?>
	</td>
	<td data-title="<?php esc_attr_e( 'Stock', 'storesuite' ); ?>">
		<?php
		$stock_count = '';
		if ( $product->managing_stock() ) {
			$stock_count = '(' . $product->get_stock_quantity() . ')';
		}

		if ( $product->is_on_backorder() ) {
			echo '<span class="storesuite-badge storesuite-badge-warning">' . esc_html__( 'On backorder', 'storesuite' ) . '</span>';
		} elseif ( $product->is_in_stock() ) {
			echo '<span class="storesuite-badge storesuite-badge-success">' . esc_html__( 'In stock', 'storesuite' ) . esc_html( $stock_count ) . '</span>';
		} else {
			echo '<span class="storesuite-badge storesuite-badge-danger">' . esc_html__( 'Out of stock', 'storesuite' ) . '</span>';
		}
		?>
	</td>
	<td data-title="<?php esc_attr_e( 'Price', 'storesuite' ); ?>">
		<?php echo wp_kses_post( $product->get_price_html() ); ?>
	</td>
	<td data-title="<?php esc_attr_e( 'Type', 'storesuite' ); ?>">
		<?php storesuite_get_product_type( $product ); ?>
	</td>
	<td class="text-right" data-title="<?php esc_attr_e( 'Actions', 'storesuite' ); ?>">
		<div class="storesuite-dropdown">
			<span class="storesuite-dropdown-icon">
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-three-dots" viewBox="0 0 16 16">
					<path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3"/>
				</svg>
			</span>
			<ul class="storesuite-dropdown-menu">
				<li>
					<a href="<?php echo esc_url( get_permalink( $product_id ) ); ?>" target="_blank" class="dropdown-link"><?php esc_html_e( 'View', 'storesuite' ); ?></a>
				</li>
				<li>
					<a href="<?php echo esc_url( sprintf( storesuite_get_navigation_url( 'edit-product' ) . '%s', $product_id ) ); ?>" class="dropdown-link"><?php esc_html_e( 'Edit', 'storesuite' ); ?></a>
				</li>
				<?php if ( current_user_can( 'edit_post', $product_id ) ) : ?>
				<li>
					<button type="button" class="inline-button dropdown-link storesuite-item-inline-edit" data-product-id="<?php echo esc_attr( (string) $product_id ); ?>"><?php esc_html_e( 'Quick edit', 'storesuite' ); ?></button>
				</li>
				<?php endif; ?>
				<li>
					<button type="button" class="inline-button dropdown-link storesuite-delete-product" data-product-id="<?php echo esc_attr( (string) $product_id ); ?>"><?php esc_html_e( 'Delete', 'storesuite' ); ?></button>
				</li>
			</ul>
		</div>
	</td>
</tr>
