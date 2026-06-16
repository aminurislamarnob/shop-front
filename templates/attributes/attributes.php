<?php
/**
 * StoreSuite attributes list page
 *
 * @package StoreSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'storesuite_dashboard_wrapper_start' );

$attributes = wc_get_attribute_taxonomies();

?>
<div class="my-storesuite-container">
	<aside class="my-storesuite-sidebar">
		<?php do_action( 'storesuite_dashboard_navigation' ); ?>
	</aside>
	<div class="my-storesuite-wrapper">
		<?php do_action( 'storesuite_dashboard_content_before' ); ?>
		<main class="my-storesuite-page-content">
			<?php do_action( 'storesuite_dashboard_before_main_content' ); ?>

			<div class="storesuite-table-header-part">
				<div class="row">
					<div class="col-md-6">
					</div>
					<div class="col-md-6 text-right storesuite-toolbar-add">
						<?php do_action( 'storesuite_attributes_toolbar_add_button' ); ?>
					</div>
				</div>
			</div>

			<div class="storesuite-table-responsive">
				<table class="my-storesuite-tbl my-storesuite-product-list-table my-storesuite-attributes-table storesuite-list-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Name', 'storesuite' ); ?></th>
							<th><?php esc_html_e( 'Slug', 'storesuite' ); ?></th>
							<th><?php esc_html_e( 'Type', 'storesuite' ); ?></th>
							<th><?php esc_html_e( 'Order by', 'storesuite' ); ?></th>
							<th><?php esc_html_e( 'Terms', 'storesuite' ); ?></th>
							<th class="text-right"><?php esc_html_e( 'Action', 'storesuite' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( empty( $attributes ) ) : ?>
							<tr>
								<td colspan="6">
									<?php
									storesuite_get_template_part(
										'not-found',
										'',
										array(
											'title' => esc_html__( 'No attributes found!', 'storesuite' ),
											'desc'  => esc_html__( 'There is nothing to display at the moment. Please try adding an attribute.', 'storesuite' ),
										)
									);
									?>
								</td>
							</tr>
						<?php else : ?>
							<?php foreach ( $attributes as $attribute ) : ?>
								<?php
								$taxonomy = wc_attribute_taxonomy_name( $attribute->attribute_name );
								$terms    = get_terms(
									array(
										'taxonomy'   => $taxonomy,
										'hide_empty' => false,
									)
								);
								$terms_count = is_array( $terms ) ? count( $terms ) : 0;
								$term_names  = array();
								if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
									foreach ( $terms as $term ) {
										$term_names[] = $term->name;
									}
								}
								?>
								<?php
								// Human-readable order by label, similar to WooCommerce.
								$order_by_raw   = $attribute->attribute_orderby;
								$order_by_label = '';
								switch ( $order_by_raw ) {
									case 'menu_order':
										$order_by_label = __( 'Custom ordering', 'storesuite' );
										break;
									case 'name':
										$order_by_label = __( 'Name', 'storesuite' );
										break;
									case 'name_num':
										$order_by_label = __( 'Name (numeric)', 'storesuite' );
										break;
									case 'id':
										$order_by_label = __( 'Term ID', 'storesuite' );
										break;
									default:
										$order_by_label = $order_by_raw;
										break;
								}
								?>
								<tr class="storesuite-list-row" id="attribute-row-<?php echo esc_attr( $attribute->attribute_id ); ?>">
									<td data-title="<?php esc_attr_e( 'Name', 'storesuite' ); ?>"><a href="<?php echo esc_url( add_query_arg( array( 'taxonomy' => $taxonomy ), storesuite_get_navigation_url( 'attribute-terms' ) ) ); ?>"><?php echo esc_html( $attribute->attribute_label ); ?></a></td>
									<td data-title="<?php esc_attr_e( 'Slug', 'storesuite' ); ?>"><?php echo esc_html( $attribute->attribute_name ); ?></td>
									<td data-title="<?php esc_attr_e( 'Type', 'storesuite' ); ?>"><?php echo esc_html( wc_get_attribute_types()[ $attribute->attribute_type ] ?? $attribute->attribute_type ); ?></td>
									<td data-title="<?php esc_attr_e( 'Order by', 'storesuite' ); ?>"><?php echo esc_html( $order_by_label ); ?></td>
									<td data-title="<?php esc_attr_e( 'Terms', 'storesuite' ); ?>">
										<?php
										if ( ! empty( $term_names ) ) {
											// Show comma-separated term names, similar to Woo admin.
											echo esc_html( implode( ', ', $term_names ) );
										} else {
											esc_html_e( 'No terms', 'storesuite' );
										}
										?>
										<div class="storesuite-configure-terms-link">
											<a href="<?php echo esc_url( add_query_arg( array( 'taxonomy' => $taxonomy ), storesuite_get_navigation_url( 'attribute-terms' ) ) ); ?>">
												<?php esc_html_e( 'Configure terms', 'storesuite' ); ?>
											</a>
										</div>
									</td>
									<td class="text-right" data-title="<?php esc_attr_e( 'Action', 'storesuite' ); ?>">
										<div class="storesuite-dropdown">
											<span class="storesuite-dropdown-icon">
												<svg width="20" height="20" fill="currentColor" aria-hidden="true" focusable="false"><use href="#storesuite-icon-three-dots"></use></svg>
											</span>
											<ul class="storesuite-dropdown-menu">
												<li>
													<a href="<?php echo esc_url( add_query_arg( array( 'taxonomy' => $taxonomy ), storesuite_get_navigation_url( 'attribute-terms' ) ) ); ?>" class="dropdown-link">
														<?php esc_html_e( 'Configure terms', 'storesuite' ); ?>
													</a>
												</li>
												<li>
													<a href="<?php echo esc_url( sprintf( storesuite_get_navigation_url( 'edit-attribute' ) . '%s', $attribute->attribute_id ) ); ?>" class="dropdown-link">
														<?php esc_html_e( 'Edit', 'storesuite' ); ?>
													</a>
												</li>
												<li>
													<button type="button" class="inline-button dropdown-link storesuite-item-quick-edit" data-object-type="attribute" data-id="<?php echo esc_attr( $attribute->attribute_id ); ?>">
														<?php esc_html_e( 'Quick edit', 'storesuite' ); ?>
													</button>
												</li>
												<li>
													<button type="button" class="inline-button dropdown-link storesuite-delete-attribute" data-attribute-id="<?php echo esc_attr( $attribute->attribute_id ); ?>">
														<?php esc_html_e( 'Delete', 'storesuite' ); ?>
													</button>
												</li>
											</ul>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
			<?php storesuite_get_template_part( 'shared/list-quick-edit-modal' ); ?>
		</main>
	</div>
</div>
<?php
do_action( 'storesuite_dashboard_wrapper_end' );

