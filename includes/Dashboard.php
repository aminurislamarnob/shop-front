<?php

/**
 * Dashboard service.
 *
 * @package ShopFront
 */

namespace PluginizeLab\ShopFront;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dashboard class.
 *
 * Handles dashboard widgets/sections rendering and data preparation.
 */
class Dashboard {
	/**
	 * Constructor.
	 */
	public function __construct() {
		// Render dashboard widgets inside the dashboard template.
		add_action( 'msf_dashboard_home_widgets', array( $this, 'render_store_performance' ), 10 );
	}

	/**
	 * Render Store Performance KPI section.
	 *
	 * @return void
	 */
	public function render_store_performance() {
		$stats_config = $this->get_store_performance_stats();

		$date_range = $this->get_store_performance_date_range();
		$start      = $date_range['start'];
		$end        = $date_range['end'];
		$label      = $date_range['label'];

		$prev_range = $this->get_previous_date_range( $start, $end );

		$current_values = $this->get_kpi_values( $start, $end, $stats_config );
		$prev_values    = ( $prev_range['start'] && $prev_range['end'] ) ? $this->get_kpi_values( $prev_range['start'], $prev_range['end'], $stats_config ) : array();
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
								$change      = $this->calculate_percent_change( $value, $prev_value );
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
												<?php echo $this->format_value( $value, $stat_config['format'] ?? 'number' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
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
		<?php
	}

	/**
	 * Get Store Performance stat definitions.
	 *
	 * @return array[]
	 */
	protected function get_store_performance_stats(): array {
		$stats = array(
			array(
				'stat'   => 'revenue/total_sales',
				'label'  => __( 'Total sales', 'shop-front' ),
				'format' => 'currency',
			),
			array(
				'stat'   => 'revenue/gross_sales',
				'label'  => __( 'Gross sales', 'shop-front' ),
				'format' => 'currency',
			),
			array(
				'stat'   => 'revenue/net_revenue',
				'label'  => __( 'Net sales', 'shop-front' ),
				'format' => 'currency',
			),
			array(
				'stat'   => 'orders/orders_count',
				'label'  => __( 'Orders', 'shop-front' ),
				'format' => 'number',
			),
			array(
				'stat'   => 'orders/avg_order_value',
				'label'  => __( 'Average order value', 'shop-front' ),
				'format' => 'currency',
			),
			array(
				'stat'   => 'products/items_sold',
				'label'  => __( 'Products sold', 'shop-front' ),
				'format' => 'number',
			),
			array(
				'stat'   => 'variations/items_sold',
				'label'  => __( 'Variations sold', 'shop-front' ),
				'format' => 'number',
			),
			array(
				'stat'   => 'revenue/refunds',
				'label'  => __( 'Returns', 'shop-front' ),
				'format' => 'currency',
			),
			array(
				'stat'   => 'coupons/orders_count',
				'label'  => __( 'Discounted orders', 'shop-front' ),
				'format' => 'number',
			),
			array(
				'stat'   => 'coupons/amount',
				'label'  => __( 'Net discount amount', 'shop-front' ),
				'format' => 'currency',
			),
			array(
				'stat'   => 'taxes/total_tax',
				'label'  => __( 'Total tax', 'shop-front' ),
				'format' => 'currency',
			),
			array(
				'stat'   => 'taxes/order_tax',
				'label'  => __( 'Order tax', 'shop-front' ),
				'format' => 'currency',
			),
			array(
				'stat'   => 'taxes/shipping_tax',
				'label'  => __( 'Shipping tax', 'shop-front' ),
				'format' => 'currency',
			),
			array(
				'stat'   => 'revenue/shipping',
				'label'  => __( 'Shipping', 'shop-front' ),
				'format' => 'currency',
			),
			array(
				'stat'   => 'downloads/download_count',
				'label'  => __( 'Downloads', 'shop-front' ),
				'format' => 'number',
			),
		);

		/**
		 * Filter Store Performance stats list.
		 *
		 * @param array[] $stats Stats config.
		 */
		return apply_filters( 'msf_dashboard_store_performance_stats', $stats );
	}

	/**
	 * Get date range for Store Performance.
	 *
	 * Internally this class uses `start`/`end` naming, but the WooCommerce analytics
	 * REST endpoints use `after`/`before` params. We only map at request time.
	 *
	 * @return array{start:string,end:string,label:string}
	 */
	protected function get_store_performance_date_range(): array {
		$tz  = function_exists( 'wp_timezone' ) ? wp_timezone() : new \DateTimeZone( 'UTC' );
		$now = new \DateTimeImmutable( 'now', $tz );

		$start_dt = $now->modify( 'first day of this month' )->setTime( 0, 0, 0 );
		$end_dt   = $now->setTime( 23, 59, 59 );

		$start = $start_dt->format( 'Y-m-d H:i:s' );
		$end   = $end_dt->format( 'Y-m-d H:i:s' );

		$args = apply_filters(
			'msf_dashboard_store_performance_date_range',
			array(
				'start' => $start,
				'end'   => $end,
				'label' => __( 'This month', 'shop-front' ),
			)
		);

		return array(
			'start' => isset( $args['start'] ) ? (string) $args['start'] : $start,
			'end'   => isset( $args['end'] ) ? (string) $args['end'] : $end,
			'label' => isset( $args['label'] ) ? (string) $args['label'] : __( 'This month', 'shop-front' ),
		);
	}

	/**
	 * Get previous date range of equal length.
	 *
	 * @param string $start Current period start.
	 * @param string $end   Current period end.
	 * @return array{start:string,end:string}
	 */
	protected function get_previous_date_range( string $start, string $end ): array {
		try {
			$tz = function_exists( 'wp_timezone' ) ? wp_timezone() : new \DateTimeZone( 'UTC' );

			$start_dt = new \DateTime( $start, $tz );
			$end_dt   = new \DateTime( $end, $tz );

			$period_seconds = max( 0, $end_dt->getTimestamp() - $start_dt->getTimestamp() );
			$prev_end_dt    = clone $start_dt;
			$prev_end_dt->setTimestamp( $prev_end_dt->getTimestamp() - 1 );
			$prev_start_dt = clone $prev_end_dt;
			$prev_start_dt->setTimestamp( $prev_start_dt->getTimestamp() - $period_seconds );

			return array(
				'start' => $prev_start_dt->format( 'Y-m-d H:i:s' ),
				'end'   => $prev_end_dt->format( 'Y-m-d H:i:s' ),
			);
		} catch ( \Exception $e ) {
			return array(
				'start' => '',
				'end'   => '',
			);
		}
	}

	/**
	 * Fetch KPI values from WooCommerce Analytics performance indicators endpoint.
	 *
	 * @param string  $start ISO datetime string.
	 * @param string  $end   ISO datetime string.
	 * @param array[] $stats  Stats config list.
	 * @return array<string,mixed>|\WP_Error
	 */
	protected function get_kpi_values( string $start, string $end, array $stats ) {
		if ( ! class_exists( 'WP_REST_Request' ) || ! function_exists( 'rest_do_request' ) ) {
			return new \WP_Error( 'msf_rest_unavailable', __( 'REST API is not available.', 'shop-front' ) );
		}

		$stat_keys = array();
		foreach ( $stats as $item ) {
			if ( ! is_array( $item ) || ! isset( $item['stat'] ) ) {
				continue;
			}

			$stat = (string) $item['stat'];
			if ( $stat !== '' ) {
				$stat_keys[] = $stat;
			}
		}

		if ( empty( $stat_keys ) ) {
			return new \WP_Error( 'msf_empty_stats', __( 'No performance stats configured.', 'shop-front' ) );
		}

		$request = new \WP_REST_Request( 'GET', '/wc-analytics/reports/performance-indicators' );
		$request->set_query_params(
			array(
				// WooCommerce Analytics expects `after`/`before`.
				'before' => $end,
				'after'  => $start,
				'stats'  => $stat_keys,
			)
		);

		$response = rest_do_request( $request );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( ! is_callable( array( $response, 'get_status' ) ) || 200 !== $response->get_status() ) {
			return new \WP_Error( 'msf_store_performance_failed', __( 'Sorry, fetching store performance failed.', 'shop-front' ) );
		}

		$data = $response->get_data();
		if ( ! is_array( $data ) ) {
			return new \WP_Error( 'msf_store_performance_invalid', __( 'Invalid store performance response.', 'shop-front' ) );
		}

		$values = array();
		foreach ( $data as $indicator ) {
			if ( ! is_array( $indicator ) || ! isset( $indicator['stat'] ) ) {
				continue;
			}
			$values[ (string) $indicator['stat'] ] = isset( $indicator['value'] ) ? $indicator['value'] : null;
		}

		return $values;
	}

	/**
	 * Format a KPI value for output.
	 *
	 * @param mixed  $value  Raw value.
	 * @param string $format currency|number
	 * @return string
	 */
	protected function format_value( $value, string $format ): string {
		if ( null === $value ) {
			return esc_html__( '—', 'shop-front' );
		}

		if ( 'currency' === $format && function_exists( 'wc_price' ) ) {
			return wp_kses_post( call_user_func( 'wc_price', (float) $value ) );
		}

		if ( is_numeric( $value ) ) {
			return esc_html( number_format_i18n( (float) $value ) );
		}

		return esc_html( (string) $value );
	}

	/**
	 * Calculate percent change between two numeric values.
	 *
	 * @param mixed $current  Current period value.
	 * @param mixed $previous Previous period value.
	 * @return float|null
	 */
	protected function calculate_percent_change( $current, $previous ) {
		$current  = is_numeric( $current ) ? (float) $current : null;
		$previous = is_numeric( $previous ) ? (float) $previous : null;

		if ( null === $current || null === $previous ) {
			return null;
		}

		if ( 0.0 === $previous ) {
			return ( 0.0 === $current ) ? 0.0 : null;
		}

		return ( ( $current - $previous ) / abs( $previous ) ) * 100;
	}
}

