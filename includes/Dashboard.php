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
		add_action( 'msf_dashboard_item_solds_widgets', array( $this, 'render_top_products_items_sold' ), 20 );
		add_action( 'msf_dashboard_item_solds_widgets', array( $this, 'render_top_categories_items_sold' ), 30 );
		add_action( 'msf_dashboard_item_solds_widgets', array( $this, 'render_top_customers_total_spend' ), 40 );
		add_action( 'msf_dashboard_item_solds_widgets', array( $this, 'render_top_coupons_orders_count' ), 50 );
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

		$template_args = array(
			'stats_config'   => $stats_config,
			'label'          => $label,
			'current_values' => $current_values,
			'prev_values'    => $prev_values,
			'dashboard'      => $this,
		);

		msf_get_template_part( 'dashboard/store-performance', '', $template_args );
	}

	/**
	 * Render "Top products - Items sold" leaderboard table.
	 *
	 * @return void
	 */
	public function render_top_products_items_sold() {
		$enabled = apply_filters( 'msf_dashboard_enable_top_products_items_sold', true );
		if ( true !== $enabled ) {
			return;
		}

		$date_range = apply_filters( 'msf_dashboard_top_products_items_sold_date_range', $this->get_store_performance_date_range() );
		$start      = isset( $date_range['start'] ) ? (string) $date_range['start'] : '';
		$end        = isset( $date_range['end'] ) ? (string) $date_range['end'] : '';
		$label      = isset( $date_range['label'] ) ? (string) $date_range['label'] : '';

		$per_page = (int) apply_filters( 'msf_dashboard_top_products_items_sold_per_page', 5 );
		$per_page = max( 1, $per_page );

		$rows = $this->get_top_products_items_sold_rows( $start, $end, $per_page );

		$template_args = array(
			'label'     => $label,
			'rows'      => $rows,
			'dashboard' => $this,
		);

		msf_get_template_part( 'dashboard/top-products-items-sold', '', $template_args );
	}

	/**
	 * Render "Top categories - Items sold" leaderboard table.
	 *
	 * @return void
	 */
	public function render_top_categories_items_sold() {
		$enabled = apply_filters( 'msf_dashboard_enable_top_categories_items_sold', true );
		if ( true !== $enabled ) {
			return;
		}

		$date_range = apply_filters( 'msf_dashboard_top_categories_items_sold_date_range', $this->get_store_performance_date_range() );
		$start      = isset( $date_range['start'] ) ? (string) $date_range['start'] : '';
		$end        = isset( $date_range['end'] ) ? (string) $date_range['end'] : '';
		$label      = isset( $date_range['label'] ) ? (string) $date_range['label'] : '';

		$per_page = (int) apply_filters( 'msf_dashboard_top_categories_items_sold_per_page', 5 );
		$per_page = max( 1, $per_page );

		$rows = $this->get_top_categories_items_sold_rows( $start, $end, $per_page );

		$template_args = array(
			'label'     => $label,
			'rows'      => $rows,
			'dashboard' => $this,
		);

		msf_get_template_part( 'dashboard/top-categories-items-sold', '', $template_args );
	}

	/**
	 * Render "Top customers - Total spend" leaderboard table.
	 *
	 * @return void
	 */
	public function render_top_customers_total_spend() {
		$enabled = apply_filters( 'msf_dashboard_enable_top_customers_total_spend', true );
		if ( true !== $enabled ) {
			return;
		}

		$date_range = apply_filters( 'msf_dashboard_top_customers_total_spend_date_range', $this->get_store_performance_date_range() );
		$start      = isset( $date_range['start'] ) ? (string) $date_range['start'] : '';
		$end        = isset( $date_range['end'] ) ? (string) $date_range['end'] : '';
		$label      = isset( $date_range['label'] ) ? (string) $date_range['label'] : '';

		$per_page = (int) apply_filters( 'msf_dashboard_top_customers_total_spend_per_page', 5 );
		$per_page = max( 1, $per_page );

		$rows = $this->get_top_customers_total_spend_rows( $start, $end, $per_page );

		$template_args = array(
			'label'     => $label,
			'rows'      => $rows,
			'dashboard' => $this,
		);

		msf_get_template_part( 'dashboard/top-customers-total-spend', '', $template_args );
	}

	/**
	 * Render "Top coupons - Number of orders" leaderboard table.
	 *
	 * @return void
	 */
	public function render_top_coupons_orders_count() {
		$enabled = apply_filters( 'msf_dashboard_enable_top_coupons_orders_count', true );
		if ( true !== $enabled ) {
			return;
		}

		$date_range = apply_filters( 'msf_dashboard_top_coupons_orders_count_date_range', $this->get_store_performance_date_range() );
		$start      = isset( $date_range['start'] ) ? (string) $date_range['start'] : '';
		$end        = isset( $date_range['end'] ) ? (string) $date_range['end'] : '';
		$label      = isset( $date_range['label'] ) ? (string) $date_range['label'] : '';

		$per_page = (int) apply_filters( 'msf_dashboard_top_coupons_orders_count_per_page', 5 );
		$per_page = max( 1, $per_page );

		$rows = $this->get_top_coupons_orders_count_rows( $start, $end, $per_page );

		$template_args = array(
			'label'     => $label,
			'rows'      => $rows,
			'dashboard' => $this,
		);

		msf_get_template_part( 'dashboard/top-coupons-orders-count', '', $template_args );
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

		// Default: current month.
		$start_dt = $now->modify( 'first day of this month' )->setTime( 0, 0, 0 );
		$end_dt   = $now->modify( 'last day of this month' )->setTime( 23, 59, 59 );
		$label    = __( 'This month', 'shop-front' );

		// Override from dashboard date range picker when both dates are present and valid.
		$start_param = isset( $_GET['msf_dashboard_start'] ) ? \sanitize_text_field( \wp_unslash( $_GET['msf_dashboard_start'] ) ) : '';
		$end_param   = isset( $_GET['msf_dashboard_end'] ) ? \sanitize_text_field( \wp_unslash( $_GET['msf_dashboard_end'] ) ) : '';

		if ( '' !== $start_param && '' !== $end_param ) {
			try {
				$start_input = new \DateTimeImmutable( $start_param, $tz );
				$end_input   = new \DateTimeImmutable( $end_param, $tz );

				if ( $end_input < $start_input ) {
					// Swap if user accidentally picked reversed range.
					$tmp         = $start_input;
					$start_input = $end_input;
					$end_input   = $tmp;
				}

				$start_dt = $start_input->setTime( 0, 0, 0 );
				$end_dt   = $end_input->setTime( 23, 59, 59 );
				$label    = sprintf(
					'%s – %s',
					$start_input->format( 'M j, Y' ),
					$end_input->format( 'M j, Y' )
				);
			} catch ( \Exception $e ) {
				storesuite_log(
					sprintf(
						'Error to get store performance date range: %s',
						$e->getMessage()
					),
					'error'
				);
			}
		}

		$start = $start_dt->format( 'Y-m-d H:i:s' );
		$end   = $end_dt->format( 'Y-m-d H:i:s' );

		$args = apply_filters(
			'msf_dashboard_store_performance_date_range',
			array(
				'start' => $start,
				'end'   => $end,
				'label' => $label,
			)
		);

		return array(
			'start' => isset( $args['start'] ) ? (string) $args['start'] : $start,
			'end'   => isset( $args['end'] ) ? (string) $args['end'] : $end,
			'label' => isset( $args['label'] ) ? (string) $args['label'] : $label,
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
	public function format_value( $value, string $format ): string {
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
	public function calculate_percent_change( $current, $previous ) {
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

	/**
	 * Get top products rows for "Items sold" leaderboard.
	 *
	 * @param string $start Period start datetime string.
	 * @param string $end   Period end datetime string.
	 * @param int    $limit Number of rows to return.
	 * @return array<int,array<string,mixed>>|\WP_Error
	 */
	protected function get_top_products_items_sold_rows( string $start, string $end, int $limit ) {
		$limit = max( 1, $limit );

		// Prefer WooCommerce Analytics DataStore (gives us product_id + numeric values).
		$data_store_class = '\Automattic\WooCommerce\Admin\API\Reports\Products\DataStore';
		if ( class_exists( $data_store_class ) ) {
			try {
				$data_store = new $data_store_class();
				$args       = apply_filters(
					'msf_dashboard_top_products_items_sold_query_args',
					array(
						'orderby'       => 'items_sold',
						'order'         => 'desc',
						'after'         => $start,
						'before'        => $end,
						'per_page'      => $limit,
						'extended_info' => true,
					),
					$start,
					$end,
					$limit
				);

				$result = is_object( $data_store ) && is_callable( array( $data_store, 'get_data' ) ) ? $data_store->get_data( $args ) : null;
				if ( is_object( $result ) && isset( $result->data ) && is_array( $result->data ) ) {
					$rows = array();
					foreach ( $result->data as $product ) {
						if ( ! is_array( $product ) ) {
							continue;
						}

						$rows[] = array(
							'product_id'   => isset( $product['product_id'] ) ? (int) $product['product_id'] : 0,
							'product_name' => isset( $product['extended_info']['name'] ) ? (string) $product['extended_info']['name'] : '',
							'items_sold'   => isset( $product['items_sold'] ) ? (float) $product['items_sold'] : 0,
							'net_revenue'  => isset( $product['net_revenue'] ) ? (float) $product['net_revenue'] : 0,
						);
					}

					return $rows;
				}
			} catch ( \Exception $e ) {
				storesuite_log(
					sprintf(
						'Error fetching top products: %s',
						$e->getMessage()
					),
					'error'
				);

				// Return empty array on error (no REST fallback).
				return array();
			}
		}

		// Return empty array if class doesn't exist or no data returned.
		return array();
	}

	/**
	 * Get top categories rows for "Items sold" leaderboard.
	 *
	 * @param string $start Period start datetime string.
	 * @param string $end   Period end datetime string.
	 * @param int    $limit Number of rows to return.
	 * @return array<int,array<string,mixed>>|\WP_Error
	 */
	protected function get_top_categories_items_sold_rows( string $start, string $end, int $limit ) {
		$limit = max( 1, $limit );

		// Prefer WooCommerce Analytics DataStore (gives us category_id + numeric values).
		$data_store_class = '\Automattic\WooCommerce\Admin\API\Reports\Categories\DataStore';
		if ( class_exists( $data_store_class ) ) {
			try {
				$data_store = new $data_store_class();
				$args       = apply_filters(
					'msf_dashboard_top_categories_items_sold_query_args',
					array(
						'orderby'       => 'items_sold',
						'order'         => 'desc',
						'after'         => $start,
						'before'        => $end,
						'per_page'      => $limit,
						'extended_info' => true,
					),
					$start,
					$end,
					$limit
				);

				$result = is_object( $data_store ) && is_callable( array( $data_store, 'get_data' ) ) ? $data_store->get_data( $args ) : null;
				if ( is_object( $result ) && isset( $result->data ) && is_array( $result->data ) ) {
					$rows = array();
					foreach ( $result->data as $category ) {
						if ( ! is_array( $category ) ) {
							continue;
						}

						$rows[] = array(
							'category_id'   => isset( $category['category_id'] ) ? (int) $category['category_id'] : 0,
							'category_name' => isset( $category['extended_info']['name'] ) ? (string) $category['extended_info']['name'] : '',
							'items_sold'    => isset( $category['items_sold'] ) ? (float) $category['items_sold'] : 0,
							'net_revenue'   => isset( $category['net_revenue'] ) ? (float) $category['net_revenue'] : 0,
						);
					}

					return $rows;
				}
			} catch ( \Exception $e ) {
				storesuite_log(
					sprintf(
						'Error fetching top categories: %s',
						$e->getMessage()
					),
					'error'
				);

				// Return empty array on error (no REST fallback).
				return array();
			}
		}

		// Return empty array if class doesn't exist or no data returned.
		return array();
	}

	/**
	 * Get top customers rows for "Total spend" leaderboard.
	 *
	 * @param string $start Period start datetime string.
	 * @param string $end   Period end datetime string.
	 * @param int    $limit Number of rows to return.
	 * @return array<int,array<string,mixed>>|\WP_Error
	 */
	protected function get_top_customers_total_spend_rows( string $start, string $end, int $limit ) {
		$limit = max( 1, $limit );

		// Prefer WooCommerce Analytics DataStore (gives us customer_id + numeric values).
		$data_store_class = '\Automattic\WooCommerce\Admin\API\Reports\Customers\DataStore';
		if ( class_exists( $data_store_class ) ) {
			try {
				$data_store = new $data_store_class();
				$args       = apply_filters(
					'msf_dashboard_top_customers_total_spend_query_args',
					array(
						'orderby'      => 'total_spend',
						'order'        => 'desc',
						'order_after'  => $start,
						'order_before' => $end,
						'per_page'     => $limit,
					),
					$start,
					$end,
					$limit
				);

				$result = is_object( $data_store ) && is_callable( array( $data_store, 'get_data' ) ) ? $data_store->get_data( $args ) : null;
				if ( is_object( $result ) && isset( $result->data ) && is_array( $result->data ) ) {
					$rows = array();
					foreach ( $result->data as $customer ) {
						if ( ! is_array( $customer ) ) {
							continue;
						}

						$rows[] = array(
							'customer_id'   => isset( $customer['id'] ) ? (int) $customer['id'] : 0,
							'customer_name' => isset( $customer['name'] ) ? (string) $customer['name'] : '',
							'orders_count'  => isset( $customer['orders_count'] ) ? (int) $customer['orders_count'] : 0,
							'total_spend'   => isset( $customer['total_spend'] ) ? (float) $customer['total_spend'] : 0,
						);
					}

					return $rows;
				}
			} catch ( \Exception $e ) {
				storesuite_log(
					sprintf(
						'Error fetching top customers: %s',
						$e->getMessage()
					),
					'error'
				);

				// Return empty array on error (no REST fallback).
				return array();
			}
		}

		// Return empty array if class doesn't exist or no data returned.
		return array();
	}

	/**
	 * Get top coupons rows for "Number of orders" leaderboard.
	 *
	 * @param string $start Period start datetime string.
	 * @param string $end   Period end datetime string.
	 * @param int    $limit Number of rows to return.
	 * @return array<int,array<string,mixed>>|\WP_Error
	 */
	protected function get_top_coupons_orders_count_rows( string $start, string $end, int $limit ) {
		$limit = max( 1, $limit );

		// Prefer WooCommerce Analytics DataStore (gives us coupon_id + numeric values).
		$data_store_class = '\Automattic\WooCommerce\Admin\API\Reports\Coupons\DataStore';
		if ( class_exists( $data_store_class ) ) {
			try {
				$data_store = new $data_store_class();
				$args       = apply_filters(
					'msf_dashboard_top_coupons_orders_count_query_args',
					array(
						'orderby'       => 'orders_count',
						'order'         => 'desc',
						'after'         => $start,
						'before'        => $end,
						'per_page'      => $limit,
						'extended_info' => true,
					),
					$start,
					$end,
					$limit
				);

				$result = is_object( $data_store ) && is_callable( array( $data_store, 'get_data' ) ) ? $data_store->get_data( $args ) : null;
				if ( is_object( $result ) && isset( $result->data ) && is_array( $result->data ) ) {
					$rows = array();
					foreach ( $result->data as $coupon ) {
						if ( ! is_array( $coupon ) ) {
							continue;
						}

						$rows[] = array(
							'coupon_id'    => isset( $coupon['coupon_id'] ) ? (int) $coupon['coupon_id'] : 0,
							'coupon_code'  => isset( $coupon['extended_info']['code'] ) ? (string) $coupon['extended_info']['code'] : '',
							'orders_count' => isset( $coupon['orders_count'] ) ? (int) $coupon['orders_count'] : 0,
							'amount'       => isset( $coupon['amount'] ) ? (float) $coupon['amount'] : 0,
						);
					}

					return $rows;
				}
			} catch ( \Exception $e ) {
				storesuite_log(
					sprintf(
						'Error fetching top coupons: %s',
						$e->getMessage()
					),
					'error'
				);

				// Return empty array on error.
				return array();
			}
		}

		// Return empty array if class doesn't exist or no data returned.
		return array();
	}
}
