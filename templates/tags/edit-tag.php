<?php
/**
 * MSFC tag edit page.
 *
 * @package ShopFront
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tag_id = get_query_var( 'edit-tag' );
$tag_id = absint( $tag_id );

do_action( 'msf_dashboard_wrapper_start' );
?>
<div class="my-shop-front-container">
	<aside class="my-shop-front-sidebar">
		<?php do_action( 'msf_dashboard_navigation' ); ?>
	</aside>
	<div class="my-shop-front-wrapper">
		<?php do_action( 'msf_dashboard_content_before' ); ?>
		<main class="my-shop-front-page-content">
			<?php do_action( 'msf_dashboard_before_main_content' ); ?>
			<div class="row">
				<div class="col-md-6">
					<div class="msf-card">
					<?php
					if ( $tag_id ) {

						$product_tag = get_term( $tag_id, 'product_tag' );

						if ( ! $product_tag || is_wp_error( $product_tag ) ) {
							echo '<div class="alert alert-danger">' . esc_html__( 'Tag not found.', 'shop-front' ) . '</div>';
							return;
						}
						?>
						<form id="msfc-edit-tag">
							<div class="msf-form-group">
								<label for="name"><?php esc_html_e( 'Tag Name', 'shop-front' ); ?> <span class="req"><?php esc_html_e( '*', 'shop-front' ); ?></span></label>
								<input type="text" class="msf-form-control" id="name" name="name" placeholder="<?php echo esc_attr__( 'Product tag name', 'shop-front' ); ?>" value="<?php echo esc_attr( $product_tag->name ); ?>" required>
							</div>
							<div class="msf-form-group">
								<label for="description"><?php esc_html_e( 'Tag Description', 'shop-front' ); ?></label>
								<textarea class="msf-form-control" id="description" name="description" placeholder="<?php echo esc_attr__( 'Product tag description', 'shop-front' ); ?>" rows="3"><?php echo esc_textarea( $product_tag->description ); ?></textarea>
							</div>
							<div class="msf-form-submission-group">
								<?php wp_nonce_field( '_msfc_edit_product_tag_', 'msfc_edit_product_tag_nonce' ); ?>
								<input type="hidden" name="action" value="msfc_edit_product_tag">
								<input type="hidden" name="tag_id" value="<?php echo esc_attr( $product_tag->term_id ); ?>">
								<div class="msf-button-group">
									<button class="my-shop-front-button" name="save_product_tag" type="submit"><?php esc_html_e( 'Save Changes', 'shop-front' ); ?></button>
									<a href="<?php echo esc_url( msfc_get_navigation_url( 'tags' ) ); ?>" class="my-shop-front-button my-shop-front-button-light"><?php esc_html_e( 'Back', 'shop-front' ); ?></a>
								</div>
							</div>
						</form>
						<?php } ?>
					</div>
				</div>
			</div>
		</main>
	</div>
</div>
<?php do_action( 'msf_dashboard_wrapper_end' ); ?>