<?php
/**
 * MSFC tag add page.
 * ***/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'msf_dashboard_wrapper_start' );
?>
<div class="my-shop-front-container">
	<div class="row">
		<div class="col-md-2">
			<?php do_action( 'msf_dashboard_navigation' ); ?>
		</div>
		<div class="col-md-10">
			<div x-data="tagAddFormHandler()">
				<form id="msfc-add-tag" @submit.prevent="handleTagSubmission">
					<div class="form-group">
						<label for="name"><?php esc_html_e( 'Tag Name', 'shop-front' ); ?> <span class="req"><?php esc_html_e( '*', 'shop-front' ); ?></span></label>
						<input type="text" class="form-control" id="name" name="name" placeholder="<?php echo esc_attr__( 'Product tag name', 'shop-front' ); ?>" aria-required="true">
					</div>
					<div class="form-group">
						<label for="description"><?php esc_html_e( 'Tag Description', 'shop-front' ); ?></label>
						<textarea class="form-control" id="description" name="description" placeholder="<?php echo esc_attr__( 'Product tag description', 'shop-front' ); ?>" rows="3"></textarea>
					</div>
					<div class="form-group">
						<?php wp_nonce_field( '_msfc_add_product_tag_', 'msfc_add_product_tag_nonce' ); ?>
						<input type="hidden" name="action" value="msfc_add_product_tag">
						<button class="msfc-wfm-button msfc-submit-btn" name="save_product_tag" type="submit"><?php esc_html_e( 'Add New Tag', 'shop-front' ); ?></button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<?php do_action( 'msf_dashboard_wrapper_end' ); ?>