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
	<svg class="storesuite-ai-icon" aria-hidden="true" focusable="false"><use href="#storesuite-icon-ai-magic"></use></svg>
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
				<svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><use href="#storesuite-icon-close"></use></svg>
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
				<?php
				/*
				 * The three drafted fields share the same label + pager + regenerate +
				 * skeleton structure, differing only by key, control, row count and the
				 * number of skeleton lines. The ids/data-field keys below are referenced
				 * by product-ai.js, so keep them stable.
				 */
				$storesuite_ai_bundle_fields = array(
					'title'             => array(
						'id'             => 'storesuite-ai-bundle-result-title',
						'label'          => __( 'Product Title', 'storesuite' ),
						'control'        => 'input',
						'rows'           => 0,
						'skeleton_lines' => 1,
					),
					'short_description' => array(
						'id'             => 'storesuite-ai-bundle-result-short',
						'label'          => __( 'Short Description', 'storesuite' ),
						'control'        => 'textarea',
						'rows'           => 3,
						'skeleton_lines' => 3,
					),
					'description'       => array(
						'id'             => 'storesuite-ai-bundle-result-description',
						'label'          => __( 'Product Description', 'storesuite' ),
						'control'        => 'textarea',
						'rows'           => 8,
						'skeleton_lines' => 6,
					),
				);
				foreach ( $storesuite_ai_bundle_fields as $storesuite_ai_field_key => $storesuite_ai_field ) :
					?>
					<div class="storesuite-form-group">
						<label class="storesuite-form-label" for="<?php echo esc_attr( $storesuite_ai_field['id'] ); ?>">
							<?php echo esc_html( $storesuite_ai_field['label'] ); ?>
							<span class="storesuite-ai-field-actions">
								<span class="storesuite-ai-field-pager" data-field="<?php echo esc_attr( $storesuite_ai_field_key ); ?>" hidden>
									<button type="button" class="storesuite-ai-field-prev" aria-label="<?php esc_attr_e( 'Previous suggestion', 'storesuite' ); ?>"><svg class="storesuite-ai-pager-icon" aria-hidden="true" focusable="false"><use href="#storesuite-icon-chevron-left"></use></svg></button>
									<span class="storesuite-ai-field-pager-status" aria-live="polite">1/1</span>
									<button type="button" class="storesuite-ai-field-next" aria-label="<?php esc_attr_e( 'Next suggestion', 'storesuite' ); ?>"><svg class="storesuite-ai-pager-icon" aria-hidden="true" focusable="false"><use href="#storesuite-icon-chevron-right"></use></svg></button>
								</span>
								<button type="button" class="storesuite-ai-field-regenerate" data-field="<?php echo esc_attr( $storesuite_ai_field_key ); ?>"><svg class="storesuite-ai-icon" aria-hidden="true" focusable="false"><use href="#storesuite-icon-ai-magic"></use></svg> <?php esc_html_e( 'Regenerate', 'storesuite' ); ?></button>
							</span>
						</label>
						<?php if ( 'input' === $storesuite_ai_field['control'] ) : ?>
							<input type="text" id="<?php echo esc_attr( $storesuite_ai_field['id'] ); ?>" class="storesuite-form-control" />
						<?php else : ?>
							<textarea id="<?php echo esc_attr( $storesuite_ai_field['id'] ); ?>" class="storesuite-form-control" rows="<?php echo esc_attr( $storesuite_ai_field['rows'] ); ?>"></textarea>
						<?php endif; ?>
						<div class="storesuite-ai-skeleton storesuite-skeleton-lines" data-field="<?php echo esc_attr( $storesuite_ai_field_key ); ?>" hidden aria-hidden="true">
							<?php for ( $storesuite_ai_line = 0; $storesuite_ai_line < $storesuite_ai_field['skeleton_lines']; $storesuite_ai_line++ ) : ?>
								<div class="storesuite-skeleton"></div>
							<?php endfor; ?>
						</div>
					</div>
				<?php endforeach; ?>
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
