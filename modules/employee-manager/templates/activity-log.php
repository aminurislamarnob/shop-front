<?php
/**
 * Employee Manager — activity log screen.
 *
 * @package StoreSuite
 */

use PluginizeLab\StoreSuite\Modules\EmployeeManager\ActivityLogger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only log filters.
$storesuite_filter_user   = isset( $_GET['emp'] ) ? absint( wp_unslash( $_GET['emp'] ) ) : 0;
$storesuite_filter_action = isset( $_GET['act'] ) ? sanitize_text_field( wp_unslash( $_GET['act'] ) ) : '';
$storesuite_paged         = isset( $_GET['paged'] ) ? max( 1, absint( wp_unslash( $_GET['paged'] ) ) ) : ( get_query_var( 'paged' ) ? (int) get_query_var( 'paged' ) : 1 );
// phpcs:enable WordPress.Security.NonceVerification.Recommended

$storesuite_per_page = (int) apply_filters( 'storesuite_activity_log_per_page', 30 );
$storesuite_log      = ActivityLogger::query(
	array(
		'user_id'  => $storesuite_filter_user,
		'action'   => $storesuite_filter_action,
		'paged'    => $storesuite_paged,
		'per_page' => $storesuite_per_page,
	)
);

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
			$current_view = 'activity';
			require __DIR__ . '/partials/tabs.php';
			?>

			<div class="storesuite-table-responsive">
				<table class="my-storesuite-tbl storesuite-list-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'When', 'storesuite' ); ?></th>
							<th><?php esc_html_e( 'Employee', 'storesuite' ); ?></th>
							<th><?php esc_html_e( 'Action', 'storesuite' ); ?></th>
							<th><?php esc_html_e( 'Details', 'storesuite' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( empty( $storesuite_log['items'] ) ) : ?>
							<tr>
								<td colspan="4"><?php esc_html_e( 'No activity recorded yet.', 'storesuite' ); ?></td>
							</tr>
						<?php else : ?>
							<?php foreach ( $storesuite_log['items'] as $storesuite_entry ) : ?>
								<?php $storesuite_entry_user = get_userdata( (int) $storesuite_entry['user_id'] ); ?>
								<tr class="single-product-item storesuite-list-row">
									<td data-title="<?php esc_attr_e( 'When', 'storesuite' ); ?>">
										<?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $storesuite_entry['created_at'] ) ) ); ?>
									</td>
									<td data-title="<?php esc_attr_e( 'Employee', 'storesuite' ); ?>">
										<?php echo esc_html( $storesuite_entry_user ? $storesuite_entry_user->display_name : __( 'Unknown', 'storesuite' ) ); ?>
									</td>
									<td data-title="<?php esc_attr_e( 'Action', 'storesuite' ); ?>">
										<span class="storesuite-badge"><?php echo esc_html( $storesuite_entry['action'] ); ?></span>
									</td>
									<td data-title="<?php esc_attr_e( 'Details', 'storesuite' ); ?>"><?php echo esc_html( $storesuite_entry['summary'] ); ?></td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>

			<?php
			if ( $storesuite_log['total_pages'] > 1 ) {
				storesuite_get_template_part(
					'pagination',
					null,
					array(
						'total_items'  => $storesuite_log['total'],
						'total_pages'  => $storesuite_log['total_pages'],
						'current_page' => $storesuite_paged,
						'per_page'     => $storesuite_per_page,
					)
				);
			}
			?>
		</main>
	</div>
</div>
<?php do_action( 'storesuite_dashboard_wrapper_end' ); ?>
