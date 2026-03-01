<?php
/**
 * Dashboard Store Performance Template
 *
 * This template displays the store performance KPI section on the dashboard.
 *
 * @package StoreSuite
 * @version 1.0.0
 *
 * @var array                            $stats_config   Stats configuration array.
 * @var string                           $label          Date range label.
 * @var array|\WP_Error                  $current_values Current period KPI values.
 * @var array                            $prev_values    Previous period KPI values.
 * @var \PluginizeLab\StoreSuite\Dashboard $dashboard      Dashboard instance for helper methods.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="storesuite-card storesuite-card-with-header storesuite-store-performance">
	<h2 class="storesuite-card-title">
		<?php echo esc_html__( 'Store performance', 'storesuite' ); ?>
	</h2>
	<div class="storesuite-card-content">
		<?php if ( is_wp_error( $current_values ) ) : ?>
			<p><?php echo esc_html( $current_values->get_error_message() ); ?></p>
		<?php else : ?>
			<div class="storesuite-kpi-summary">
				<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-0">
					<?php foreach ( $stats_config as $stat_config ) : ?>
						<?php
						$stat_key = isset( $stat_config['stat'] ) ? (string) $stat_config['stat'] : '';
						if ( '' === $stat_key ) {
							continue;
						}

						$value       = $current_values[ $stat_key ] ?? null;
						$prev_value  = is_array( $prev_values ) ? ( $prev_values[ $stat_key ] ?? null ) : null;
						$change      = $dashboard->calculate_percent_change( $value, $prev_value );
						$change_text = null === $change ? esc_html__( '—', 'storesuite' ) : sprintf( '%s%%', number_format_i18n( $change, 0 ) );
						$change_dir  = 'na';
						if ( null !== $change ) {
							$change_dir = 'flat';
							if ( $change > 0 ) {
								$change_dir = 'up';
							} elseif ( $change < 0 ) {
								$change_dir = 'down';
							}
						}
						?>
						<div class="col">
							<div class="storesuite-kpi-card storesuite-kpi-card--<?php echo esc_attr( $change_dir ); ?>">
								<div class="storesuite-kpi-label"><?php echo esc_html( $stat_config['label'] ?? $stat_key ); ?></div>
								<div class="storesuite-kpi-row">
									<div class="storesuite-kpi-value">
										<?php echo wp_kses_post( $dashboard->format_value( $value, $stat_config['format'] ?? 'number' ) ); ?>
									</div>
									<div class="storesuite-kpi-change"><?php echo esc_html( $change_text ); ?></div>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
