<?php
/**
 * AI image generation modal for the product form.
 *
 * Describe a product image, preview the AI result, then insert it as the product
 * image. Loaded by ProductImageAI on the add/edit product page when image
 * generation is available.
 *
 * @package StoreSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div id="storesuite-ai-image-modal" class="storesuite-product-bulk-modal-overlay" hidden aria-hidden="true">
	<div
		class="storesuite-product-bulk-modal-dialog"
		role="dialog"
		aria-modal="true"
		aria-labelledby="storesuite-ai-image-title"
		tabindex="-1"
	>
		<div class="storesuite-product-bulk-modal-header">
			<h2 id="storesuite-ai-image-title" class="storesuite-product-bulk-modal-title">
				<?php esc_html_e( 'Generate product image with AI', 'storesuite' ); ?>
			</h2>
			<button type="button" class="storesuite-product-bulk-modal-close my-storesuite-button storesuite-button-ghost" aria-label="<?php esc_attr_e( 'Close', 'storesuite' ); ?>">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false">
					<path d="M18,6h0a1,1,0,0,0-1.414,0L12,10.586,7.414,6A1,1,0,0,0,6,6H6a1,1,0,0,0,0,1.414L10.586,12,6,16.586A1,1,0,0,0,6,18h0a1,1,0,0,0,1.414,0L12,13.414,16.586,18A1,1,0,0,0,18,18h0a1,1,0,0,0,0-1.414L13.414,12,18,7.414A1,1,0,0,0,18,6Z"/>
				</svg>
			</button>
		</div>
		<div class="storesuite-product-bulk-edit-scroll">
			<div class="storesuite-ai-modal-subhead">
				<p class="storesuite-ai-modal-subtitle"><?php esc_html_e( 'Describe the product image you want and AI will create it. Review it, then insert it as the product image.', 'storesuite' ); ?></p>
			</div>
			<div class="storesuite-form-group">
				<textarea id="storesuite-ai-image-prompt" class="storesuite-form-control" rows="3" placeholder="<?php esc_attr_e( 'e.g. a matte black ceramic coffee mug on a light wooden table', 'storesuite' ); ?>"></textarea>
			</div>
			<div class="storesuite-ai-image-preview" hidden>
				<img src="" alt="<?php esc_attr_e( 'AI generated product image preview', 'storesuite' ); ?>" />
			</div>
			<div class="storesuite-ai-image-skeleton storesuite-skeleton storesuite-skeleton-image" hidden aria-hidden="true"></div>
		</div>
		<div class="storesuite-product-bulk-modal-footer">
			<button type="button" class="my-storesuite-button storesuite-button-neutral-panel storesuite-ai-image-regenerate" hidden>
				<?php esc_html_e( 'Regenerate', 'storesuite' ); ?>
			</button>
			<button type="button" class="my-storesuite-button storesuite-ai-image-submit">
				<?php esc_html_e( 'Generate', 'storesuite' ); ?>
			</button>
			<button type="button" class="my-storesuite-button storesuite-ai-image-insert" hidden>
				<?php esc_html_e( 'Insert', 'storesuite' ); ?>
			</button>
		</div>
	</div>
</div>
