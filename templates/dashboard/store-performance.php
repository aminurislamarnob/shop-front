<?php
/**
 * Dashboard Store Performance Template
 *
 * This template displays the store performance KPI section on the dashboard.
 *
 * @package ShopFront
 * @version 1.0.0
 *
 * @var array                            $stats_config   Stats configuration array.
 * @var string                           $label          Date range label.
 * @var array|\WP_Error                  $current_values Current period KPI values.
 * @var array                            $prev_values    Previous period KPI values.
 * @var \PluginizeLab\ShopFront\Dashboard $dashboard      Dashboard instance for helper methods.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="msf-card msf-card-with-header msf-store-performance">
	<h2 class="msf-card-title">
		<?php echo esc_html__( 'Store performance', 'shop-front' ); ?>
		<small>(<?php echo esc_html( $label ); ?>)</small>
	</h2>
	<div class="msf-card-content">
		<?php if ( is_wp_error( $current_values ) ) : ?>
			<p><?php echo esc_html( $current_values->get_error_message() ); ?></p>
		<?php else : ?>
			<div class="msf-kpi-summary">
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
						$change_text = null === $change ? esc_html__( '—', 'shop-front' ) : sprintf( '%s%%', number_format_i18n( $change, 0 ) );
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
							<div class="msf-kpi-card msf-kpi-card--<?php echo esc_attr( $change_dir ); ?>">
								<div class="msf-kpi-label"><?php echo esc_html( $stat_config['label'] ?? $stat_key ); ?></div>
								<div class="msf-kpi-row">
									<div class="msf-kpi-value">
										<?php echo $dashboard->format_value( $value, $stat_config['format'] ?? 'number' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									</div>
									<div class="msf-kpi-change"><?php echo esc_html( $change_text ); ?></div>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
