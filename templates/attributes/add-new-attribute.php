<?php
/**
 * StoreSuite add new attribute page
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
					<div class="storesuite-card">
						<form id="storesuite-add-attribute">
							<div class="storesuite-form-group">
								<label for="attribute_label">
									<?php esc_html_e( 'Name', 'storesuite' ); ?>
									<span class="req">*</span>
								</label>
								<input type="text" class="storesuite-form-control" id="attribute_label" name="attribute_label" value="" placeholder="<?php esc_attr_e( 'Attribute name (e.g. Color)', 'storesuite' ); ?>">
							</div>

							<div class="storesuite-form-group">
								<label for="attribute_name">
									<?php esc_html_e( 'Slug', 'storesuite' ); ?>
								</label>
								<input type="text" class="storesuite-form-control" id="attribute_name" name="attribute_name" value="" placeholder="<?php esc_attr_e( 'Attribute slug (optional)', 'storesuite' ); ?>">
								<small class="storesuite-form-text">
									<?php esc_html_e( 'Unique slug used as the attribute taxonomy name. If left empty, it will be generated from the name.', 'storesuite' ); ?>
								</small>
							</div>

							<div class="storesuite-form-group storesuite-form-switch">
								<input type="checkbox" id="attribute_public" name="attribute_public" value="1">
								<label for="attribute_public">
									<?php esc_html_e( 'Enable archives?', 'storesuite' ); ?>
								</label>
							</div>
							<small class="storesuite-form-text">
								<?php esc_html_e( 'Enable this if you want this attribute to have product archives and be available in layered nav filters.', 'storesuite' ); ?>
							</small>

							<div class="storesuite-form-group">
								<label for="attribute_orderby">
									<?php esc_html_e( 'Default sort order', 'storesuite' ); ?>
								</label>
								<select id="attribute_orderby" name="attribute_orderby" class="storesuite-form-control">
									<option value="menu_order">
										<?php esc_html_e( 'Custom ordering', 'storesuite' ); ?>
									</option>
									<option value="name">
										<?php esc_html_e( 'Name', 'storesuite' ); ?>
									</option>
									<option value="name_num">
										<?php esc_html_e( 'Name (numeric)', 'storesuite' ); ?>
									</option>
									<option value="id">
										<?php esc_html_e( 'Term ID', 'storesuite' ); ?>
									</option>
								</select>
							</div>

							<div class="storesuite-form-submission-group">
								<?php wp_nonce_field( '_storesuite_add_product_attribute_', 'storesuite_add_product_attribute_nonce' ); ?>
								<input type="hidden" name="action" value="storesuite_add_product_attribute">
								<div class="storesuite-button-group">
									<button class="my-storesuite-button" type="submit">
										<?php esc_html_e( 'Add Attribute', 'storesuite' ); ?>
									</button>
									<a href="<?php echo esc_url( storesuite_get_navigation_url( 'attributes' ) ); ?>" class="my-storesuite-button my-storesuite-button-light">
										<?php esc_html_e( 'Back', 'storesuite' ); ?>
									</a>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</main>
	</div>
</div>
<?php
do_action( 'storesuite_dashboard_wrapper_end' );
