<?php
/**
 * Dashboard Template
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
		<main class="my-shop-front-page-content msf-main-dashboard">
			<?php do_action( 'msf_dashboard_before_main_content' ); ?>
			<div class="msf-dashboard-title-wrapper">
				<?php
				$msf_dashboard_start = isset( $_GET['msf_dashboard_start'] ) ? sanitize_text_field( wp_unslash( $_GET['msf_dashboard_start'] ) ) : '';
				$msf_dashboard_end   = isset( $_GET['msf_dashboard_end'] ) ? sanitize_text_field( wp_unslash( $_GET['msf_dashboard_end'] ) ) : '';
				$msf_dashboard_label = '';

				if ( $msf_dashboard_start && $msf_dashboard_end ) {
					$start_ts = strtotime( $msf_dashboard_start );
					$end_ts   = strtotime( $msf_dashboard_end );

					if ( $start_ts && $end_ts ) {
						if ( $end_ts < $start_ts ) {
							$tmp                 = $start_ts;
							$start_ts            = $end_ts;
							$end_ts              = $tmp;
							$tmp                 = $msf_dashboard_start;
							$msf_dashboard_start = $msf_dashboard_end;
							$msf_dashboard_end   = $tmp;
						}
						$msf_dashboard_label = sprintf(
							'%s – %s',
							date_i18n( 'M j, Y', $start_ts ),
							date_i18n( 'M j, Y', $end_ts )
						);
					}
				}

				if ( '' === $msf_dashboard_label ) {
					// Default label mirrors backend: current month.
					$tz       = function_exists( 'wp_timezone' ) ? wp_timezone() : new DateTimeZone( 'UTC' );
					$now_dt   = new DateTimeImmutable( 'now', $tz );
					$start_dt = $now_dt->modify( 'first day of this month' )->setTime( 0, 0, 0 );
					$end_dt   = $now_dt->modify( 'last day of this month' )->setTime( 23, 59, 59 );

					$msf_dashboard_label = sprintf(
						'%s – %s',
						date_i18n( 'M j, Y', $start_dt->getTimestamp() ),
						date_i18n( 'M j, Y', $end_dt->getTimestamp() )
					);
				}
				?>
				<div class="msf-dashboard-title">
					<h3 class="msf-page-main-title">
						<?php echo esc_html__( 'Dashboard', 'shop-front' ); ?>
						<small>(<?php echo esc_html( $msf_dashboard_label ); ?>)</small>
					</h3>
				</div>
				<div class="msf-dashboard-date-range-picker">
					<form method="get" class="msf-dashboard-date-range-form">
						<label for="msf_dashboard_range" class="screen-reader-text">
							<?php esc_html_e( 'Date range', 'shop-front' ); ?>
						</label>
						<input
							type="text"
							id="msf_dashboard_range"
							class="msf-form-control msf-dashboard-date-field"
							placeholder="<?php esc_attr_e( 'Select date range', 'shop-front' ); ?>"
							autocomplete="off"
							value="<?php echo esc_attr( $msf_dashboard_start && $msf_dashboard_end ? $msf_dashboard_start . ' - ' . $msf_dashboard_end : '' ); ?>"
						/>
						<input
							type="hidden"
							id="msf_dashboard_start"
							name="msf_dashboard_start"
							value="<?php echo esc_attr( $msf_dashboard_start ); ?>"
						/>
						<input
							type="hidden"
							id="msf_dashboard_end"
							name="msf_dashboard_end"
							value="<?php echo esc_attr( $msf_dashboard_end ); ?>"
						/>
						<button type="submit" class="my-shop-front-button msf-dashboard-date-apply">
							<?php esc_html_e( 'Apply', 'shop-front' ); ?>
						</button>
					</form>
				</div>
			</div>
			<div class="row">
				<div class="col-12">
					<?php do_action( 'msf_dashboard_home_widgets' ); ?>
				</div>
			</div>
			<div class="row">
				<?php do_action( 'msf_dashboard_item_solds_widgets' ); ?>
			</div>
		</main>
	</div>
</div>
<?php do_action( 'msf_dashboard_wrapper_end' ); ?>