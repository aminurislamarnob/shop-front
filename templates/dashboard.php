<?php
/**
 * Dashboard Template
 *
 * @package StoreSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'storesuite_dashboard_wrapper_start' );
?>
<div class="my-storesuite-container">
	<aside id="storesuite-dashboard-sidebar" class="my-storesuite-sidebar" role="navigation" aria-label="<?php esc_attr_e( 'Store dashboard navigation', 'storesuite' ); ?>">
		<?php do_action( 'storesuite_dashboard_navigation' ); ?>
	</aside>
	<div class="my-storesuite-wrapper">
		<?php do_action( 'storesuite_dashboard_content_before' ); ?>
		<main class="my-storesuite-page-content storesuite-main-dashboard">
			<?php do_action( 'storesuite_dashboard_before_main_content' ); ?>
			<div class="storesuite-dashboard-title-wrapper">
				<?php
				$storesuite_dashboard_start = isset( $_GET['storesuite_dashboard_start'] ) ? sanitize_text_field( wp_unslash( $_GET['storesuite_dashboard_start'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended --  Nonce not required: read-only date range filter for display; no state change.
				$storesuite_dashboard_end   = isset( $_GET['storesuite_dashboard_end'] ) ? sanitize_text_field( wp_unslash( $_GET['storesuite_dashboard_end'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended --  Nonce not required: read-only date range filter for display; no state change.
				$storesuite_dashboard_label = '';

				if ( $storesuite_dashboard_start && $storesuite_dashboard_end ) {
					$start_ts = strtotime( $storesuite_dashboard_start );
					$end_ts   = strtotime( $storesuite_dashboard_end );

					if ( $start_ts && $end_ts ) {
						if ( $end_ts < $start_ts ) {
							$tmp                        = $start_ts;
							$start_ts                   = $end_ts;
							$end_ts                     = $tmp;
							$tmp                        = $storesuite_dashboard_start;
							$storesuite_dashboard_start = $storesuite_dashboard_end;
							$storesuite_dashboard_end   = $tmp;
						}
						$storesuite_dashboard_label = sprintf(
							'%s – %s',
							date_i18n( 'M j, Y', $start_ts ),
							date_i18n( 'M j, Y', $end_ts )
						);
					}
				}

				if ( '' === $storesuite_dashboard_label ) {
					// Default label mirrors backend: current month.
					$tz       = function_exists( 'wp_timezone' ) ? wp_timezone() : new DateTimeZone( 'UTC' );
					$now_dt   = new DateTimeImmutable( 'now', $tz );
					$start_dt = $now_dt->modify( 'first day of this month' )->setTime( 0, 0, 0 );
					$end_dt   = $now_dt->modify( 'last day of this month' )->setTime( 23, 59, 59 );

					$storesuite_dashboard_label = sprintf(
						'%s – %s',
						date_i18n( 'M j, Y', $start_dt->getTimestamp() ),
						date_i18n( 'M j, Y', $end_dt->getTimestamp() )
					);
				}
				?>
				<div class="storesuite-dashboard-title">
					<h3 class="storesuite-page-main-title">
						<?php echo esc_html__( 'Dashboard', 'storesuite' ); ?>
						<small>(<?php echo esc_html( $storesuite_dashboard_label ); ?>)</small>
					</h3>
				</div>
				<div class="storesuite-dashboard-date-range-picker">
					<form method="get" class="storesuite-dashboard-date-range-form">
						<label for="storesuite_dashboard_range" class="screen-reader-text">
							<?php esc_html_e( 'Date range', 'storesuite' ); ?>
						</label>
						<input
							type="text"
							id="storesuite_dashboard_range"
							class="storesuite-form-control storesuite-dashboard-date-field"
							placeholder="<?php esc_attr_e( 'Select date range', 'storesuite' ); ?>"
							autocomplete="off"
							value="<?php echo esc_attr( $storesuite_dashboard_start && $storesuite_dashboard_end ? $storesuite_dashboard_start . ' - ' . $storesuite_dashboard_end : '' ); ?>"
						/>
						<input
							type="hidden"
							id="storesuite_dashboard_start"
							name="storesuite_dashboard_start"
							value="<?php echo esc_attr( $storesuite_dashboard_start ); ?>"
						/>
						<input
							type="hidden"
							id="storesuite_dashboard_end"
							name="storesuite_dashboard_end"
							value="<?php echo esc_attr( $storesuite_dashboard_end ); ?>"
						/>
						<button type="submit" class="my-storesuite-button storesuite-dashboard-date-apply">
							<?php esc_html_e( 'Apply', 'storesuite' ); ?>
						</button>
					</form>
				</div>
			</div>
			<div class="row">
				<div class="col-12">
					<?php do_action( 'storesuite_dashboard_home_widgets' ); ?>
				</div>
			</div>
			<div class="row">
				<?php do_action( 'storesuite_dashboard_item_solds_widgets' ); ?>
			</div>
		</main>
	</div>
</div>
<?php do_action( 'storesuite_dashboard_wrapper_end' ); ?>