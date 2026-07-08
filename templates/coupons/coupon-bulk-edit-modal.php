<?php
/**
 * Bulk edit coupons: modal form. Coupon bulk edit only changes the post status,
 * matching WooCommerce's own coupon bulk edit.
 *
 * @package StoreSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$storesuite_coupon_bulk_statuses = array(
	'-1'      => __( '— No change —', 'storesuite' ),
	'publish' => __( 'Published', 'storesuite' ),
	'pending' => __( 'Pending Review', 'storesuite' ),
	'draft'   => __( 'Draft', 'storesuite' ),
	'private' => __( 'Private', 'storesuite' ),
);
?>
<div id="storesuite-coupon-bulk-edit-modal" class="storesuite-product-bulk-modal-overlay" hidden aria-hidden="true">
	<div
		class="storesuite-product-bulk-modal-dialog"
		role="dialog"
		aria-modal="true"
		aria-labelledby="storesuite-coupon-bulk-edit-title"
		tabindex="-1"
	>
		<div class="storesuite-product-bulk-modal-header">
			<h2 id="storesuite-coupon-bulk-edit-title" class="storesuite-product-bulk-modal-title">
				<?php esc_html_e( 'Bulk edit coupons', 'storesuite' ); ?>
			</h2>
			<button type="button" class="storesuite-coupon-bulk-modal-close my-storesuite-button storesuite-button-ghost" aria-label="<?php esc_attr_e( 'Close', 'storesuite' ); ?>">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false">
					<path d="M18,6h0a1,1,0,0,0-1.414,0L12,10.586,7.414,6A1,1,0,0,0,6,6H6a1,1,0,0,0,0,1.414L10.586,12,6,16.586A1,1,0,0,0,6,18h0a1,1,0,0,0,1.414,0L12,13.414,16.586,18A1,1,0,0,0,18,18h0a1,1,0,0,0,0-1.414L13.414,12,18,7.414A1,1,0,0,0,18,6Z"/>
				</svg>
			</button>
		</div>
		<form id="storesuite-coupon-bulk-edit-form" method="post" class="storesuite-product-bulk-edit-form">
			<div id="storesuite-bulk-edit-coupon-ids" class="storesuite-bulk-edit-post-ids" aria-hidden="true"></div>

			<div class="storesuite-product-bulk-edit-scroll">
				<div class="storesuite-product-bulk-edit-core">
					<div class="row">
						<div class="col-md-12">
							<div class="storesuite-form-group">
								<label for="storesuite-coupon-bulk-_status" class="storesuite-form-label"><?php esc_html_e( 'Status', 'storesuite' ); ?></label>
								<select name="_status" id="storesuite-coupon-bulk-_status" class="storesuite-form-control">
									<?php foreach ( $storesuite_coupon_bulk_statuses as $storesuite_status_value => $storesuite_status_text ) : ?>
										<option value="<?php echo esc_attr( (string) $storesuite_status_value ); ?>"><?php echo esc_html( $storesuite_status_text ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="storesuite-product-bulk-modal-footer">
				<button type="button" class="my-storesuite-button storesuite-button-neutral-panel storesuite-coupon-bulk-modal-cancel">
					<?php esc_html_e( 'Cancel', 'storesuite' ); ?>
				</button>
				<input type="submit" name="bulk_edit" class="my-storesuite-button storesuite-coupon-bulk-edit-submit" value="<?php esc_attr_e( 'Update', 'storesuite' ); ?>" />
			</div>
		</form>
	</div>
</div>
