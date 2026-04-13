<?php
/**
 * Quick edit product: modal shell (fields loaded via AJAX).
 *
 * @package StoreSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="storesuite-product-quick-edit-modal" class="storesuite-product-bulk-modal-overlay storesuite-product-quick-edit-modal" hidden aria-hidden="true">
	<div
		class="storesuite-product-bulk-modal-dialog storesuite-product-quick-edit-modal-dialog"
		role="dialog"
		aria-modal="true"
		aria-labelledby="storesuite-product-quick-edit-title"
		tabindex="-1"
	>
		<div class="storesuite-product-bulk-modal-header">
			<h2 id="storesuite-product-quick-edit-title" class="storesuite-product-bulk-modal-title">
				<?php esc_html_e( 'Quick edit', 'storesuite' ); ?>
			</h2>
			<button type="button" class="storesuite-product-quick-edit-modal-close my-storesuite-button storesuite-button-ghost" aria-label="<?php esc_attr_e( 'Close', 'storesuite' ); ?>">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false">
					<path d="M18,6h0a1,1,0,0,0-1.414,0L12,10.586,7.414,6A1,1,0,0,0,6,6H6a1,1,0,0,0,0,1.414L10.586,12,6,16.586A1,1,0,0,0,6,18h0a1,1,0,0,0,1.414,0L12,13.414,16.586,18A1,1,0,0,0,18,18h0a1,1,0,0,0,0-1.414L13.414,12,18,7.414A1,1,0,0,0,18,6Z"/>
				</svg>
			</button>
		</div>

		<div class="storesuite-product-bulk-edit-scroll storesuite-product-quick-edit-modal-scroll" id="storesuite-quick-edit-modal-body">
		</div>

		<div class="storesuite-product-bulk-modal-footer">
			<button type="button" class="my-storesuite-button storesuite-button-neutral-panel storesuite-product-quick-edit-modal-cancel">
				<?php esc_html_e( 'Cancel', 'storesuite' ); ?>
			</button>
			<div class="storesuite-product-quick-edit-update-wrap">
				<button type="button" class="my-storesuite-button storesuite-product-quick-edit-submit">
					<?php esc_html_e( 'Update', 'storesuite' ); ?>
				</button>
			</div>
		</div>
	</div>
</div>
