<?php
/**
 * Global "Generate with AI" launcher and bundle modal.
 *
 * Rendered on the Add New Product page header. The launcher opens a modal where
 * the merchant describes the product once and gets the title, long description
 * and short description drafted together, ready to review and insert.
 *
 * @package StoreSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<button type="button" class="my-storesuite-button storesuite-ai-bundle-launch">
	<svg class="storesuite-ai-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
		<path d="M19.5,24a1,1,0,0,1-.929-.628l-.844-2.113-2.116-.891a1.007,1.007,0,0,1,.035-1.857l2.088-.791.837-2.092a1.008,1.008,0,0,1,1.858,0l.841,2.1,2.1.841a1.007,1.007,0,0,1,0,1.858l-2.1.841-.841,2.1A1,1,0,0,1,19.5,24ZM10,21a2,2,0,0,1-1.936-1.413L6.45,14.54,1.387,12.846a2.032,2.032,0,0,1,.052-3.871L6.462,7.441,8.154,2.387A1.956,1.956,0,0,1,10.108,1a2,2,0,0,1,1.917,1.439l1.532,5.015,5.03,1.61a2.042,2.042,0,0,1,0,3.872h0l-5.039,1.612-1.612,5.039A2,2,0,0,1,10,21Zm.112-17.977L8.2,8.564a1,1,0,0,1-.656.64L2.023,10.888l5.541,1.917a1,1,0,0,1,.636.643l1.77,5.53,1.83-5.53a1,1,0,0,1,.648-.648l5.53-1.769a.072.072,0,0,0,.02-.009L12.448,9.2a1,1,0,0,1-.652-.661Zm8.17,8.96h0ZM20.5,7a1,1,0,0,1-.97-.757l-.357-1.43L17.74,4.428a1,1,0,0,1,.034-1.94l1.4-.325L19.53.757a1,1,0,0,1,1.94,0l.354,1.418,1.418.355a1,1,0,0,1,0,1.94l-1.418.355L21.47,6.243A1,1,0,0,1,20.5,7Z"/>
	</svg>
	<?php esc_html_e( 'Generate with AI', 'storesuite' ); ?>
</button>

<div id="storesuite-ai-bundle-modal" class="storesuite-product-bulk-modal-overlay" hidden aria-hidden="true">
	<div
		class="storesuite-product-bulk-modal-dialog"
		role="dialog"
		aria-modal="true"
		aria-labelledby="storesuite-ai-bundle-title"
		tabindex="-1"
	>
		<div class="storesuite-product-bulk-modal-header">
			<h2 id="storesuite-ai-bundle-title" class="storesuite-product-bulk-modal-title">
				<?php esc_html_e( 'Generate product with AI', 'storesuite' ); ?>
			</h2>
			<button type="button" class="storesuite-product-bulk-modal-close my-storesuite-button storesuite-button-ghost" aria-label="<?php esc_attr_e( 'Close', 'storesuite' ); ?>">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false">
					<path d="M18,6h0a1,1,0,0,0-1.414,0L12,10.586,7.414,6A1,1,0,0,0,6,6H6a1,1,0,0,0,0,1.414L10.586,12,6,16.586A1,1,0,0,0,6,18h0a1,1,0,0,0,1.414,0L12,13.414,16.586,18A1,1,0,0,0,18,18h0a1,1,0,0,0,0-1.414L13.414,12,18,7.414A1,1,0,0,0,18,6Z"/>
				</svg>
			</button>
		</div>
		<div class="storesuite-product-bulk-edit-scroll">
			<div class="storesuite-ai-bundle-step storesuite-ai-bundle-hint-step">
				<div class="storesuite-ai-modal-subhead">
					<p class="storesuite-ai-modal-subtitle"><?php esc_html_e( 'Describe your product in a few words and AI will draft the title, description and short description for you.', 'storesuite' ); ?></p>
				</div>
				<div class="storesuite-form-group">
					<textarea id="storesuite-ai-bundle-hint" class="storesuite-form-control" rows="3" placeholder="<?php esc_attr_e( 'e.g. handmade ceramic coffee mug, 350ml, matte black, dishwasher safe', 'storesuite' ); ?>"></textarea>
				</div>
			</div>

			<div class="storesuite-ai-bundle-step storesuite-ai-bundle-result-step" hidden>
				<div class="storesuite-form-group">
					<label class="storesuite-form-label" for="storesuite-ai-bundle-result-title">
						<?php esc_html_e( 'Product Title', 'storesuite' ); ?>
						<button type="button" class="storesuite-ai-field-regenerate" data-field="title"><svg class="storesuite-ai-icon" aria-hidden="true" focusable="false"><use href="#storesuite-icon-ai-magic"></use></svg> <?php esc_html_e( 'Regenerate', 'storesuite' ); ?></button>
					</label>
					<input type="text" id="storesuite-ai-bundle-result-title" class="storesuite-form-control" />
				</div>
				<div class="storesuite-form-group">
					<label class="storesuite-form-label" for="storesuite-ai-bundle-result-short">
						<?php esc_html_e( 'Short Description', 'storesuite' ); ?>
						<button type="button" class="storesuite-ai-field-regenerate" data-field="short_description"><svg class="storesuite-ai-icon" aria-hidden="true" focusable="false"><use href="#storesuite-icon-ai-magic"></use></svg> <?php esc_html_e( 'Regenerate', 'storesuite' ); ?></button>
					</label>
					<textarea id="storesuite-ai-bundle-result-short" class="storesuite-form-control" rows="3"></textarea>
				</div>
				<div class="storesuite-form-group">
					<label class="storesuite-form-label" for="storesuite-ai-bundle-result-description">
						<?php esc_html_e( 'Product Description', 'storesuite' ); ?>
						<button type="button" class="storesuite-ai-field-regenerate" data-field="description"><svg class="storesuite-ai-icon" aria-hidden="true" focusable="false"><use href="#storesuite-icon-ai-magic"></use></svg> <?php esc_html_e( 'Regenerate', 'storesuite' ); ?></button>
					</label>
					<textarea id="storesuite-ai-bundle-result-description" class="storesuite-form-control" rows="8"></textarea>
				</div>
			</div>
		</div>
		<div class="storesuite-product-bulk-modal-footer">
			<button type="button" class="my-storesuite-button storesuite-button-neutral-panel storesuite-ai-bundle-regenerate" hidden>
				<?php esc_html_e( 'Regenerate', 'storesuite' ); ?>
			</button>
			<button type="button" class="my-storesuite-button storesuite-ai-bundle-generate">
				<?php esc_html_e( 'Generate', 'storesuite' ); ?>
			</button>
			<button type="button" class="my-storesuite-button storesuite-ai-bundle-insert" hidden>
				<?php esc_html_e( 'Insert all', 'storesuite' ); ?>
			</button>
		</div>
	</div>
</div>
