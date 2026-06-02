<?php
/**
 * StoreSuite edit attribute term page
 *
 * @package StoreSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'storesuite_dashboard_wrapper_start' );

$taxonomy = isset( $_GET['taxonomy'] ) ? sanitize_text_field( wp_unslash( $_GET['taxonomy'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only.
$term_id  = isset( $_GET['term_id'] ) ? absint( wp_unslash( $_GET['term_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only.

if ( empty( $taxonomy ) || ! taxonomy_exists( $taxonomy ) || ! $term_id ) {
	echo '<div class="my-storesuite-container"><div class="my-storesuite-wrapper"><main class="my-storesuite-page-content">';
	wp_kses_post( esc_html_e( 'Invalid term.', 'storesuite' ) );
	echo '</main></div></div>';
	do_action( 'storesuite_dashboard_wrapper_end' );
	return;
}

$term = get_term( $term_id, $taxonomy );

if ( ! $term || is_wp_error( $term ) ) {
	echo '<div class="my-storesuite-container"><div class="my-storesuite-wrapper"><main class="my-storesuite-page-content">';
	wp_kses_post( esc_html_e( 'Term not found.', 'storesuite' ) );
	echo '</main></div></div>';
	do_action( 'storesuite_dashboard_wrapper_end' );
	return;
}

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
						<h3 class="storesuite-card-title">
							<?php esc_html_e( 'Edit term', 'storesuite' ); ?>
						</h3>
						<div class="storesuite-card-content">
							<form id="storesuite-edit-attribute-term">
								<div class="storesuite-form-group">
									<label for="term_name">
										<?php esc_html_e( 'Name', 'storesuite' ); ?>
										<span class="req">*</span>
									</label>
									<input type="text" class="storesuite-form-control" id="term_name" name="term_name" value="<?php echo esc_attr( $term->name ); ?>" placeholder="<?php esc_attr_e( 'Term name (e.g. Blue)', 'storesuite' ); ?>">
								</div>
								<div class="storesuite-form-group">
									<label for="term_slug"><?php esc_html_e( 'Slug', 'storesuite' ); ?></label>
									<input type="text" class="storesuite-form-control" id="term_slug" name="term_slug" value="<?php echo esc_attr( $term->slug ); ?>" placeholder="<?php esc_attr_e( 'Term slug (optional)', 'storesuite' ); ?>">
								</div>
								<div class="storesuite-form-group">
									<label for="term_description"><?php esc_html_e( 'Description', 'storesuite' ); ?></label>
									<textarea class="storesuite-form-control" id="term_description" name="term_description" rows="3" placeholder="<?php esc_attr_e( 'Optional term description.', 'storesuite' ); ?>"><?php echo esc_textarea( $term->description ); ?></textarea>
								</div>
								<div class="storesuite-form-submission-group">
									<?php wp_nonce_field( '_storesuite_edit_attribute_term_', 'storesuite_edit_attribute_term_nonce' ); ?>
									<input type="hidden" name="action" value="storesuite_edit_attribute_term">
									<input type="hidden" name="taxonomy" value="<?php echo esc_attr( $taxonomy ); ?>">
									<input type="hidden" name="term_id" value="<?php echo esc_attr( $term_id ); ?>">
									<div class="storesuite-button-group">
										<button class="my-storesuite-button" type="submit">
											<?php esc_html_e( 'Update term', 'storesuite' ); ?>
										</button>
										<a href="<?php echo esc_url( add_query_arg( array( 'taxonomy' => $taxonomy ), storesuite_get_navigation_url( 'attribute-terms' ) ) ); ?>" class="my-storesuite-button my-storesuite-button-light">
											<?php esc_html_e( 'Back to terms', 'storesuite' ); ?>
										</a>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</main>
	</div>
</div>
<?php
do_action( 'storesuite_dashboard_wrapper_end' );

