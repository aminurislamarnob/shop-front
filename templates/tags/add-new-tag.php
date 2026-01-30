<?php
/**
 * MSFC tag add page
 *
 * @package StoreSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'storesuite_dashboard_wrapper_start' );
?>
<div class="my-storesuite-container">
	<aside class="my-storesuite-sidebar">
		<?php do_action( 'storesuite_dashboard_navigation' ); ?>
	</aside>
	<div class="my-storesuite-wrapper">
		<?php do_action( 'storesuite_dashboard_content_before' ); ?>
		<main class="my-storesuite-page-content">
			<?php do_action( 'storesuite_dashboard_before_main_content' ); ?>
			<div class="row">
				<div class="col-md-6">
					<div class="msf-card">
						<form id="msfc-add-tag">
						<div class="msf-form-group">
							<label for="name"><?php esc_html_e( 'Tag Name', 'storesuite' ); ?> <span class="req"><?php esc_html_e( '*', 'storesuite' ); ?></span></label>
							<input type="text" class="msf-form-control" id="name" name="name" placeholder="<?php echo esc_attr__( 'Product tag name', 'storesuite' ); ?>">
						</div>
						<div class="msf-form-group">
							<label for="slug"><?php esc_html_e( 'Slug', 'storesuite' ); ?></label>
							<input type="text" class="msf-form-control" id="slug" name="slug" placeholder="<?php echo esc_attr__( 'Tag slug', 'storesuite' ); ?>">
							<small class="msf-form-text"><?php esc_html_e( 'The "slug" is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.', 'storesuite' ); ?></small>
						</div>
						<div class="msf-form-group">
							<label for="description"><?php esc_html_e( 'Tag Description', 'storesuite' ); ?></label>
							<textarea class="msf-form-control" id="description" name="description" placeholder="<?php echo esc_attr__( 'Product tag description', 'storesuite' ); ?>" rows="3"></textarea>
						</div>
							<div class="msf-form-submission-group">
								<?php wp_nonce_field( '_storesuite_add_product_tag_', 'storesuite_add_product_tag_nonce' ); ?>
								<input type="hidden" name="action" value="storesuite_add_product_tag">
								<div class="msf-button-group">
									<button class="my-storesuite-button" name="save_product_tag" type="submit"><?php esc_html_e( 'Add New Tag', 'storesuite' ); ?></button>
									<a href="<?php echo esc_url( storesuite_get_navigation_url( 'tags' ) ); ?>" class="my-storesuite-button my-storesuite-button-light"><?php esc_html_e( 'Back', 'storesuite' ); ?></a>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</main>
	</div>
</div>
<?php do_action( 'storesuite_dashboard_wrapper_end' ); ?>