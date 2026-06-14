<?php
/**
 * StoreSuite attribute terms page
 *
 * @package StoreSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'storesuite_dashboard_wrapper_start' );

$taxonomy = isset( $_GET['taxonomy'] ) ? sanitize_text_field( wp_unslash( $_GET['taxonomy'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only.

if ( empty( $taxonomy ) || ! taxonomy_exists( $taxonomy ) ) {
	echo '<div class="my-storesuite-container"><div class="my-storesuite-wrapper"><main class="my-storesuite-page-content">';
	wp_kses_post( esc_html_e( 'Invalid attribute.', 'storesuite' ) );
	echo '</main></div></div>';
	do_action( 'storesuite_dashboard_wrapper_end' );
	return;
}

$attribute_label = '';

// Find matching attribute taxonomy to get human-readable label.
$taxonomies = wc_get_attribute_taxonomies();
if ( ! empty( $taxonomies ) ) {
	foreach ( $taxonomies as $attr ) {
		$attr_taxonomy = wc_attribute_taxonomy_name( $attr->attribute_name );
		if ( $attr_taxonomy === $taxonomy ) {
			$attribute_label = $attr->attribute_label;
			break;
		}
	}
}

// Detect edit context.
$term_id   = isset( $_GET['term_id'] ) ? absint( wp_unslash( $_GET['term_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only.
$is_edit   = $term_id > 0;
$edit_term = null;

if ( $is_edit ) {
	$edit_term = get_term( $term_id, $taxonomy );
	if ( ! $edit_term || is_wp_error( $edit_term ) ) {
		$is_edit   = false;
		$edit_term = null;
	}
}

$terms = get_terms(
	array(
		'taxonomy'   => $taxonomy,
		'hide_empty' => false,
	)
);

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
				<div class="col-md-4">
					<div class="storesuite-card">
						<div class="storesuite-card-content">
							<form id="<?php echo $is_edit ? 'storesuite-edit-attribute-term' : 'storesuite-add-attribute-term'; ?>">
								<div class="storesuite-form-group">
									<label for="term_name">
										<?php esc_html_e( 'Name', 'storesuite' ); ?>
										<span class="req">*</span>
									</label>
									<input type="text" class="storesuite-form-control" id="term_name" name="term_name" value="<?php echo $is_edit && $edit_term ? esc_attr( $edit_term->name ) : ''; ?>" placeholder="<?php esc_attr_e( 'Term name (e.g. Blue)', 'storesuite' ); ?>">
								</div>
								<div class="storesuite-form-group">
									<label for="term_slug"><?php esc_html_e( 'Slug', 'storesuite' ); ?></label>
									<input type="text" class="storesuite-form-control" id="term_slug" name="term_slug" value="<?php echo $is_edit && $edit_term ? esc_attr( $edit_term->slug ) : ''; ?>" placeholder="<?php esc_attr_e( 'Term slug (optional)', 'storesuite' ); ?>">
								</div>
								<div class="storesuite-form-group">
									<label for="term_description"><?php esc_html_e( 'Description', 'storesuite' ); ?></label>
									<textarea class="storesuite-form-control" id="term_description" name="term_description" rows="3" placeholder="<?php esc_attr_e( 'Optional term description.', 'storesuite' ); ?>"><?php echo $is_edit && $edit_term ? esc_textarea( $edit_term->description ) : ''; ?></textarea>
								</div>
								<div class="storesuite-form-submission-group">
									<?php if ( $is_edit ) : ?>
										<?php wp_nonce_field( '_storesuite_edit_attribute_term_', 'storesuite_edit_attribute_term_nonce' ); ?>
										<input type="hidden" name="action" value="storesuite_edit_attribute_term">
										<input type="hidden" name="term_id" value="<?php echo esc_attr( $term_id ); ?>">
									<?php else : ?>
										<?php wp_nonce_field( '_storesuite_add_attribute_term_', 'storesuite_add_attribute_term_nonce' ); ?>
										<input type="hidden" name="action" value="storesuite_add_attribute_term">
									<?php endif; ?>
									<input type="hidden" name="taxonomy" value="<?php echo esc_attr( $taxonomy ); ?>">
									<div class="storesuite-button-group">
										<button class="my-storesuite-button" type="submit">
											<?php echo $is_edit ? esc_html__( 'Update term', 'storesuite' ) : esc_html__( 'Add term', 'storesuite' ); ?>
										</button>
										<?php if ( $is_edit ) : ?>
										<a href="<?php echo esc_url( add_query_arg( array( 'taxonomy' => $taxonomy ), storesuite_get_navigation_url( 'attribute-terms' ) ) ); ?>" class="my-storesuite-button my-storesuite-button-light">
											<?php esc_html_e( 'Back to terms', 'storesuite' ); ?>
										</a>
									<?php else : ?>
										<a href="<?php echo esc_url( storesuite_get_navigation_url( 'attributes' ) ); ?>" class="my-storesuite-button my-storesuite-button-light">
											<?php esc_html_e( 'Back to attributes', 'storesuite' ); ?>
										</a>
									<?php endif; ?>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
				<div class="col-md-8">
					<div class="storesuite-card">
						<div class="storesuite-card-content">
							<div class="storesuite-table-header-part">
								<?php
								storesuite_get_template_part(
									'shared/list-bulk-actions',
									'',
									array(
										'object_type' => 'attribute_term',
										'taxonomy'    => $taxonomy,
									)
								);
								?>
							</div>
							<div class="storesuite-table-responsive">
								<table class="my-storesuite-tbl my-storesuite-product-list-table storesuite-attribute-terms-table">
									<thead>
										<tr>
											<th class="check-column">
												<?php storesuite_get_template_part( 'shared/list-bulk-checkbox', '', array( 'is_all' => true ) ); ?>
											</th>
											<th><?php esc_html_e( 'Name', 'storesuite' ); ?></th>
											<th><?php esc_html_e( 'Slug', 'storesuite' ); ?></th>
											<th><?php esc_html_e( 'Count', 'storesuite' ); ?></th>
											<th class="text-right"><?php esc_html_e( 'Action', 'storesuite' ); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php if ( empty( $terms ) || is_wp_error( $terms ) ) : ?>
											<tr>
												<td colspan="5">
													<?php
													storesuite_get_template_part(
														'not-found',
														'',
														array(
															'title' => esc_html__( 'No terms found!', 'storesuite' ),
															'desc'  => esc_html__( 'There is nothing to display at the moment. Please try adding a term.', 'storesuite' ),
														)
													);
													?>
												</td>
											</tr>
										<?php else : ?>
											<?php foreach ( $terms as $term ) : ?>
															<?php
															storesuite_get_template_part(
																'attributes/attribute-term-row',
																'',
																array(
																	'term'     => $term,
																	'taxonomy' => $taxonomy,
																)
															);
															?>
											<?php endforeach; ?>
										<?php endif; ?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php storesuite_get_template_part( 'shared/list-quick-edit-modal' ); ?>
		</main>
	</div>
</div>
<?php
do_action( 'storesuite_dashboard_wrapper_end' );

