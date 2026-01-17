<?php
/**
 * MSFC tag add page
 *
 * @package ShopFront
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
						<form id="msfc-add-tag">
							<div class="msf-form-group">
								<label for="name"><?php esc_html_e( 'Tag Name', 'shop-front' ); ?> <span class="req"><?php esc_html_e( '*', 'shop-front' ); ?></span></label>
								<input type="text" class="msf-form-control" id="name" name="name" placeholder="<?php echo esc_attr__( 'Product tag name', 'shop-front' ); ?>" required>
							</div>
							<div class="msf-form-group">
								<label for="description"><?php esc_html_e( 'Tag Description', 'shop-front' ); ?></label>
								<textarea class="msf-form-control" id="description" name="description" placeholder="<?php echo esc_attr__( 'Product tag description', 'shop-front' ); ?>" rows="3"></textarea>
							</div>
							<div class="msf-form-submission-group">
								<?php wp_nonce_field( '_msfc_add_product_tag_', 'msfc_add_product_tag_nonce' ); ?>
								<input type="hidden" name="action" value="msfc_add_product_tag">
								<div class="msf-button-group">
									<button class="my-shop-front-button" name="save_product_tag" type="submit"><?php esc_html_e( 'Add New Tag', 'shop-front' ); ?></button>
									<a href="<?php echo esc_url( msfc_get_navigation_url( 'tags' ) ); ?>" class="my-shop-front-button my-shop-front-button-light"><?php esc_html_e( 'Back', 'shop-front' ); ?></a>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</main>
	</div>
</div>
<?php do_action( 'msf_dashboard_wrapper_end' ); ?>