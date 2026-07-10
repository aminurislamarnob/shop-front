<?php
/**
 * Employee Manager — employees list screen.
 *
 * Rendered by Module::load_template() for the `/employees/` endpoint.
 *
 * @package StoreSuite
 */

use PluginizeLab\StoreSuite\Modules\EmployeeManager\EmployeeManager;
use PluginizeLab\StoreSuite\Modules\EmployeeManager\Roles;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only list filters.
$storesuite_search = isset( $_GET['search_by'] ) ? sanitize_text_field( wp_unslash( $_GET['search_by'] ) ) : '';
$storesuite_paged  = isset( $_GET['paged'] ) ? max( 1, absint( wp_unslash( $_GET['paged'] ) ) ) : ( get_query_var( 'paged' ) ? (int) get_query_var( 'paged' ) : 1 );
// phpcs:enable WordPress.Security.NonceVerification.Recommended

$storesuite_per_page = (int) apply_filters( 'storesuite_employees_per_page', 20 );
$storesuite_result   = EmployeeManager::list(
	array(
		'search'   => $storesuite_search,
		'paged'    => $storesuite_paged,
		'per_page' => $storesuite_per_page,
	)
);
$storesuite_roles = Roles::all();

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

			// Screen tab navigation (shared across the Team sub-screens).
			require __DIR__ . '/partials/tabs.php';
			?>

			<div class="storesuite-table-header-part">
				<div class="row align-items-center">
					<div class="col-md">
						<form action="" method="get" class="storesuite-search-form">
							<input type="hidden" name="view" value="employees" />
							<div class="storesuite-table-search-input">
								<div class="storesuite-table-search-icon">
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><path d="M23.707,22.293l-5.969-5.969a10.016,10.016,0,1,0-1.414,1.414l5.969,5.969a1,1,0,0,0,1.414-1.414ZM10,18a8,8,0,1,1,8-8A8.009,8.009,0,0,1,10,18Z"/></svg>
								</div>
								<input type="text" name="search_by" placeholder="<?php esc_attr_e( 'Search employees', 'storesuite' ); ?>" value="<?php echo esc_attr( $storesuite_search ); ?>" />
							</div>
						</form>
					</div>
					<div class="col-md-auto text-md-end">
						<button type="button" class="my-storesuite-button" id="storesuite-add-employee-trigger">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><path d="M23,11H13V1a1,1,0,0,0-2,0V11H1a1,1,0,0,0,0,2H11V23a1,1,0,0,0,2,0V13H23a1,1,0,0,0,0-2Z"/></svg>
							<?php esc_html_e( 'Add Employee', 'storesuite' ); ?>
						</button>
					</div>
				</div>
			</div>

			<div class="storesuite-table-responsive">
				<table class="my-storesuite-tbl storesuite-list-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Name', 'storesuite' ); ?></th>
							<th><?php esc_html_e( 'Email', 'storesuite' ); ?></th>
							<th><?php esc_html_e( 'Role', 'storesuite' ); ?></th>
							<th><?php esc_html_e( 'Status', 'storesuite' ); ?></th>
							<th><?php esc_html_e( 'Last login', 'storesuite' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'storesuite' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( empty( $storesuite_result['items'] ) ) : ?>
							<tr>
								<td colspan="6">
									<?php esc_html_e( 'No employees yet. Add your first team member to get started.', 'storesuite' ); ?>
								</td>
							</tr>
						<?php else : ?>
							<?php foreach ( $storesuite_result['items'] as $storesuite_emp ) : ?>
								<tr class="single-product-item storesuite-list-row"
									data-user-id="<?php echo esc_attr( $storesuite_emp['user_id'] ); ?>"
									data-first-name="<?php echo esc_attr( get_user_meta( $storesuite_emp['user_id'], 'first_name', true ) ); ?>"
									data-last-name="<?php echo esc_attr( get_user_meta( $storesuite_emp['user_id'], 'last_name', true ) ); ?>"
									data-role="<?php echo esc_attr( $storesuite_emp['role'] ); ?>">
									<td data-title="<?php esc_attr_e( 'Name', 'storesuite' ); ?>"><?php echo esc_html( $storesuite_emp['name'] ); ?></td>
									<td data-title="<?php esc_attr_e( 'Email', 'storesuite' ); ?>"><?php echo esc_html( $storesuite_emp['email'] ); ?></td>
									<td data-title="<?php esc_attr_e( 'Role', 'storesuite' ); ?>"><?php echo esc_html( $storesuite_emp['role_label'] ); ?></td>
									<td data-title="<?php esc_attr_e( 'Status', 'storesuite' ); ?>">
										<?php if ( 'suspended' === $storesuite_emp['status'] ) : ?>
											<span class="storesuite-badge storesuite-badge-danger"><?php esc_html_e( 'Suspended', 'storesuite' ); ?></span>
										<?php else : ?>
											<span class="storesuite-badge storesuite-badge-success"><?php esc_html_e( 'Active', 'storesuite' ); ?></span>
										<?php endif; ?>
									</td>
									<td data-title="<?php esc_attr_e( 'Last login', 'storesuite' ); ?>">
										<?php echo $storesuite_emp['last_login'] ? esc_html( date_i18n( get_option( 'date_format' ), strtotime( $storesuite_emp['last_login'] ) ) ) : '&mdash;'; ?>
									</td>
									<td data-title="<?php esc_attr_e( 'Actions', 'storesuite' ); ?>">
										<div class="action-buttons">
											<button type="button" class="my-storesuite-button edit storesuite-edit-employee"><?php esc_html_e( 'Edit', 'storesuite' ); ?></button>
											<?php if ( 'suspended' === $storesuite_emp['status'] ) : ?>
												<button type="button" class="my-storesuite-button view storesuite-toggle-suspend" data-status="active"><?php esc_html_e( 'Activate', 'storesuite' ); ?></button>
											<?php else : ?>
												<button type="button" class="my-storesuite-button edit storesuite-toggle-suspend" data-status="suspended"><?php esc_html_e( 'Suspend', 'storesuite' ); ?></button>
											<?php endif; ?>
											<button type="button" class="my-storesuite-button delete storesuite-delete-employee"><?php esc_html_e( 'Remove', 'storesuite' ); ?></button>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>

			<?php
			if ( $storesuite_result['total_pages'] > 1 ) {
				storesuite_get_template_part(
					'pagination',
					null,
					array(
						'total_items'  => $storesuite_result['total'],
						'total_pages'  => $storesuite_result['total_pages'],
						'current_page' => $storesuite_paged,
						'per_page'     => $storesuite_per_page,
					)
				);
			}
			?>

			<?php require __DIR__ . '/partials/employee-form-modal.php'; ?>
		</main>
	</div>
</div>
<?php do_action( 'storesuite_dashboard_wrapper_end' ); ?>
