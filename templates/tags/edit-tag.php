<?php
/**
 * MSFC tag edit page.
 * ***/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tag_id = msf_get_id_from_query_vars( 'tags' );
$tag_id = absint( $tag_id );

do_action( 'msf_dashboard_wrapper_start' );
?>
<div class="my-shop-front-container">
	<div class="row">
		<div class="col-md-2">
			<?php do_action( 'msf_dashboard_navigation' ); ?>
		</div>
		<div class="col-md-10">
		<?php
		if ( $tag_id ) {
			$product_tag = get_term( $tag_id, 'product_tag' );

			if ( ! $product_tag || is_wp_error( $product_tag ) ) {
				echo '<div class="alert alert-danger">' . esc_html__( 'Tag not found.', 'shop-front' ) . '</div>';
				return;
			}
			?>
			<div x-data="tagEditFormHandler(<?php echo esc_attr( $product_tag->term_id ); ?>)">
				<form id="msfc-edit-tag" @submit.prevent="handleTagEditSubmission">
					<div class="form-group">
						<label for="name"><?php esc_html_e( 'Tag Name', 'shop-front' ); ?> <span class="req"><?php esc_html_e( '*', 'shop-front' ); ?></span></label>
						<input type="text" class="form-control" id="name" name="name" placeholder="<?php echo esc_attr__( 'Product tag name', 'shop-front' ); ?>" aria-required="true" value="<?php echo esc_attr( $product_tag->name ); ?>">
					</div>
					<div class="form-group">
						<label for="description"><?php esc_html_e( 'Tag Description', 'shop-front' ); ?></label>
						<textarea class="form-control" id="description" name="description" placeholder="<?php echo esc_attr__( 'Product tag description', 'shop-front' ); ?>" rows="3"><?php echo esc_attr( $product_tag->description ); ?></textarea>
					</div>
					<div class="form-group">
					<?php wp_nonce_field( '_msfc_edit_product_tag_', 'msfc_edit_product_tag_nonce' ); ?>
						<input type="hidden" name="action" value="msfc_edit_product_tag">
						<input type="hidden" name="tag_id" value="<?php echo esc_attr( $product_tag->term_id ); ?>">
						<button class="msfc-wfm-button msfc-submit-btn" name="save_product_tag" type="submit"><?php esc_html_e( 'Save Changes', 'shop-front' ); ?></button>
					</div>
				</form>
			</div>
			<?php } ?>
		</div>
	</div>
</div>
<?php do_action( 'msf_dashboard_wrapper_end' ); ?>