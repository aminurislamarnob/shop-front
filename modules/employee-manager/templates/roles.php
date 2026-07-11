<?php
/**
 * Employee Manager — roles editor screen.
 *
 * @package StoreSuite
 */

use PluginizeLab\StoreSuite\Modules\EmployeeManager\Roles;
use PluginizeLab\StoreSuite\Modules\EmployeeManager\Capabilities;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$storesuite_roles = Roles::all();
$storesuite_areas = Capabilities::all_areas();

do_action( 'storesuite_dashboard_wrapper_start' );
?>
<div class="my-storesuite-container">
	<aside id="storesuite-dashboard-sidebar" class="my-storesuite-sidebar" role="navigation" aria-label="<?php esc_attr_e( 'Store dashboard navigation', 'storesuite' ); ?>">
		<?php do_action( 'storesuite_dashboard_navigation' ); ?>
	</aside>
	<div class="my-storesuite-wrapper">
		<?php do_action( 'storesuite_dashboard_content_before' ); ?>
		<main class="my-storesuite-page-content">
			<?php do_action( 'storesuite_dashboard_before_main_content' ); ?>

			<?php
			storesuite_get_template_part(
				'dashboard-title',
				null,
				array( 'page_title' => __( 'Team', 'storesuite' ) )
			);
			require __DIR__ . '/partials/tabs.php';
			?>

			<div class="row">
				<div class="col-md-7">
					<div class="storesuite-table-responsive">
						<table class="my-storesuite-tbl storesuite-list-table">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Role', 'storesuite' ); ?></th>
									<th><?php esc_html_e( 'Permissions', 'storesuite' ); ?></th>
									<th><?php esc_html_e( 'Type', 'storesuite' ); ?></th>
									<th><?php esc_html_e( 'Actions', 'storesuite' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $storesuite_roles as $storesuite_role ) : ?>
									<tr class="single-product-item storesuite-list-row"
										data-slug="<?php echo esc_attr( $storesuite_role['slug'] ); ?>"
										data-label="<?php echo esc_attr( $storesuite_role['label'] ); ?>"
										data-areas="<?php echo esc_attr( implode( ',', $storesuite_role['areas'] ) ); ?>">
										<td data-title="<?php esc_attr_e( 'Role', 'storesuite' ); ?>"><?php echo esc_html( $storesuite_role['label'] ); ?></td>
										<td data-title="<?php esc_attr_e( 'Permissions', 'storesuite' ); ?>">
											<?php
											$storesuite_labels = array();
											foreach ( $storesuite_role['areas'] as $storesuite_area ) {
												if ( isset( $storesuite_areas[ $storesuite_area ] ) ) {
													$storesuite_labels[] = $storesuite_areas[ $storesuite_area ];
												}
											}
											echo esc_html( implode( ', ', $storesuite_labels ) );
											?>
										</td>
										<td data-title="<?php esc_attr_e( 'Type', 'storesuite' ); ?>">
											<?php if ( $storesuite_role['is_custom'] ) : ?>
												<span class="storesuite-badge storesuite-badge-info"><?php esc_html_e( 'Custom', 'storesuite' ); ?></span>
											<?php else : ?>
												<span class="storesuite-badge"><?php esc_html_e( 'Predefined', 'storesuite' ); ?></span>
											<?php endif; ?>
										</td>
										<td data-title="<?php esc_attr_e( 'Actions', 'storesuite' ); ?>">
											<?php if ( $storesuite_role['is_custom'] ) : ?>
												<div class="action-buttons">
													<button type="button" class="my-storesuite-button edit storesuite-edit-role"><?php esc_html_e( 'Edit', 'storesuite' ); ?></button>
													<button type="button" class="my-storesuite-button delete storesuite-delete-role"><?php esc_html_e( 'Delete', 'storesuite' ); ?></button>
												</div>
											<?php else : ?>
												<span class="storesuite-text-color-light">&mdash;</span>
											<?php endif; ?>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>

				<div class="col-md-5">
					<div class="storesuite-card storesuite-card-with-header">
						<h3 class="storesuite-card-title" id="storesuite-role-form-title"><?php esc_html_e( 'Add custom role', 'storesuite' ); ?></h3>
						<div class="storesuite-card-content">
							<form id="storesuite-role-form">
								<input type="hidden" name="slug" value="" />
								<div class="storesuite-form-group">
									<label for="storesuite-role-label">
										<?php esc_html_e( 'Role name', 'storesuite' ); ?>
										<span class="req">*</span>
									</label>
									<input type="text" class="storesuite-form-control" id="storesuite-role-label" name="label" required />
								</div>

								<div class="storesuite-form-group">
									<label><?php esc_html_e( 'Permissions', 'storesuite' ); ?></label>
									<?php foreach ( $storesuite_areas as $storesuite_area_key => $storesuite_area_label ) : ?>
										<div class="storesuite-form-switch storesuite-form-group">
											<input type="checkbox"
												class="storesuite-form-control"
												id="storesuite-area-<?php echo esc_attr( $storesuite_area_key ); ?>"
												name="areas[]"
												value="<?php echo esc_attr( $storesuite_area_key ); ?>"
												<?php echo 'access_dashboard' === $storesuite_area_key ? 'checked disabled' : ''; ?> />
											<label for="storesuite-area-<?php echo esc_attr( $storesuite_area_key ); ?>"><?php echo esc_html( $storesuite_area_label ); ?></label>
										</div>
									<?php endforeach; ?>
									<small class="storesuite-form-text"><?php esc_html_e( 'Dashboard access is always granted so the role can sign in.', 'storesuite' ); ?></small>
								</div>

								<div class="storesuite-form-group text-md-end">
									<button type="button" class="my-storesuite-button my-storesuite-button-light" id="storesuite-role-reset"><?php esc_html_e( 'Clear', 'storesuite' ); ?></button>
									<button type="submit" class="my-storesuite-button"><?php esc_html_e( 'Save Role', 'storesuite' ); ?></button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</main>
	</div>
</div>
<?php do_action( 'storesuite_dashboard_wrapper_end' ); ?>
