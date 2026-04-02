<?php
/**
 * Product variations wrapper template.
 *
 * @var WC_Product|null $product
 * @var int             $product_id
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$variation_attributes = array();
$total_variations     = 0;
$per_page             = 15;

if ( $product && $product->is_type( 'variable' ) ) {
	$variation_attributes = array_filter( $product->get_attributes( 'edit' ), 'wc_attributes_array_filter_variation' );
	$total_variations     = count( $product->get_children() );
}
?>
<div class="storesuite-card storesuite-card-with-header storesuite-mb-24 show_if_variable" id="storesuite-product-variations">
	<h3 class="storesuite-card-title">
		<?php esc_html_e( 'Variations', 'storesuite' ); ?>
		<small class="storesuite-text-muted storesuite-variation-count">
			<?php
			if ( $total_variations ) {
				printf(
					/* translators: %d: variation count */
					esc_html( _n( '%d variation', '%d variations', $total_variations, 'storesuite' ) ),
					absint( $total_variations )
				);
			}
			?>
		</small>
	</h3>
	<div class="storesuite-card-content">
		<?php if ( empty( $variation_attributes ) ) : ?>
			<p class="storesuite-no-variation-attrs">
				<?php esc_html_e( 'Add attributes in the Attributes section and mark them "Used for variations" to manage variations.', 'storesuite' ); ?>
			</p>
		<?php else : ?>
			<div class="storesuite-variation-toolbar">
				<div class="row">
					<div class="col-md-6">
						<select id="storesuite-variation-actions" class="storesuite-form-control">
							<option value="add_variation"><?php esc_html_e( 'Add variation', 'storesuite' ); ?></option>
							<option value="generate_all"><?php esc_html_e( 'Create variations from all attributes', 'storesuite' ); ?></option>
							<option value="delete_all"><?php esc_html_e( 'Delete all variations', 'storesuite' ); ?></option>
						</select>
					</div>
					<div class="col-md-6">
						<button type="button" id="storesuite-do-variation-action" class="my-storesuite-button my-storesuite-button-sm">
							<?php esc_html_e( 'Go', 'storesuite' ); ?>
						</button>
						<button type="button" id="storesuite-save-variations-btn" class="my-storesuite-button my-storesuite-button-sm my-storesuite-button-light" disabled>
							<?php esc_html_e( 'Save changes', 'storesuite' ); ?>
						</button>
					</div>
				</div>
			</div>

			<div id="storesuite-variations-container"
				data-product-id="<?php echo esc_attr( $product_id ); ?>"
				data-total="<?php echo esc_attr( $total_variations ); ?>"
				data-per-page="<?php echo esc_attr( $per_page ); ?>"
				data-page="1">
			</div>
		<?php endif; ?>
	</div>
</div>
