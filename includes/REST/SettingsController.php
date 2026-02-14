<?php

namespace PluginizeLab\StoreSuite\REST;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_REST_Controller;
use WP_REST_Server;

/**
 * Admin settings REST API controller.
 */
class SettingsController extends WP_REST_Controller {

	/**
	 * The namespace of this controller's route.
	 *
	 * @var string
	 */
	protected $namespace;

	/**
	 * The base of this controller's route.
	 *
	 * @var string
	 */
	protected $rest_base;

	/**
	 * Constructor.
	 *
	 * Sets the namespace and rest base for the controller.
	 */
	public function __construct() {
		$this->namespace = 'storesuite/v1';
		$this->rest_base = 'settings';
	}

	/**
	 * Register the routes for the objects of the controller.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_settings' ),
					'permission_callback' => array( $this, 'get_settings_permissions_check' ),
					'args'                => array(),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'update_settings' ),
					'permission_callback' => array( $this, 'update_settings_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
				),
			)
		);
	}

	/**
	 * Get the settings.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error The response or error object.
	 */
	public function get_settings( $request ) {
		$settings = get_option( 'storesuite_settings', array() );

		return rest_ensure_response( $settings );
	}

	/**
	 * Update the settings.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error The response or error object.
	 */
	public function update_settings( $request ) {
		$storesuite_settings = get_option( 'storesuite_settings', array() );

		if ( $request->has_param( 'storesuite_dashboard_page_id' ) ) {
			$storesuite_settings['storesuite_dashboard_page_id'] = sanitize_text_field( $request->get_param( 'storesuite_dashboard_page_id' ) );
		}

		if ( $request->has_param( 'storesuite_prevent_admin_access' ) ) {
			$val = $request->get_param( 'storesuite_prevent_admin_access' );
			$storesuite_settings['storesuite_prevent_admin_access'] = sanitize_text_field( $val );
		}

		$perf_keys = array(
			'storesuite_show_perf_revenue_total_sales',
			'storesuite_show_perf_revenue_gross_sales',
			'storesuite_show_perf_revenue_net_revenue',
			'storesuite_show_perf_orders_orders_count',
			'storesuite_show_perf_orders_avg_order_value',
			'storesuite_show_perf_products_items_sold',
			'storesuite_show_perf_variations_items_sold',
			'storesuite_show_perf_revenue_refunds',
			'storesuite_show_perf_coupons_orders_count',
			'storesuite_show_perf_coupons_amount',
			'storesuite_show_perf_taxes_total_tax',
			'storesuite_show_perf_taxes_order_tax',
			'storesuite_show_perf_taxes_shipping_tax',
			'storesuite_show_perf_revenue_shipping',
			'storesuite_show_perf_downloads_download_count',
		);
		foreach ( $perf_keys as $key ) {
			if ( $request->has_param( $key ) ) {
				$val                         = $request->get_param( $key );
				$storesuite_settings[ $key ] = sanitize_text_field( $val );
			}
		}

		$widget_keys = array(
			'storesuite_show_widget_top_products_items_sold',
			'storesuite_show_widget_top_categories_items_sold',
			'storesuite_show_widget_top_customers_total_spend',
			'storesuite_show_widget_top_coupons_orders_count',
		);
		foreach ( $widget_keys as $key ) {
			if ( $request->has_param( $key ) ) {
				$val                         = $request->get_param( $key );
				$storesuite_settings[ $key ] = sanitize_text_field( $val );
			}
		}

		if ( $request->has_param( 'storesuite_product_per_page' ) ) {
			$storesuite_settings['storesuite_product_per_page'] = sanitize_text_field( $request->get_param( 'storesuite_product_per_page' ) );
		}

		if ( $request->has_param( 'storesuite_order_per_page' ) ) {
			$storesuite_settings['storesuite_order_per_page'] = sanitize_text_field( $request->get_param( 'storesuite_order_per_page' ) );
		}

		if ( $request->has_param( 'storesuite_category_per_page' ) ) {
			$storesuite_settings['storesuite_category_per_page'] = sanitize_text_field( $request->get_param( 'storesuite_category_per_page' ) );
		}

		if ( $request->has_param( 'storesuite_tag_per_page' ) ) {
			$storesuite_settings['storesuite_tag_per_page'] = sanitize_text_field( $request->get_param( 'storesuite_tag_per_page' ) );
		}

		if ( $request->has_param( 'storesuite_brand_per_page' ) ) {
			$storesuite_settings['storesuite_brand_per_page'] = sanitize_text_field( $request->get_param( 'storesuite_brand_per_page' ) );
		}

		if ( $request->has_param( 'storesuite_coupon_per_page' ) ) {
			$storesuite_settings['storesuite_coupon_per_page'] = sanitize_text_field( $request->get_param( 'storesuite_coupon_per_page' ) );
		}

		$color_keys = array(
			'storesuite_color_button_text',
			'storesuite_color_button_background',
			'storesuite_color_button_hover_text',
			'storesuite_color_button_hover_background',
			'storesuite_text_color',
			'storesuite_title_text_color',
			'storesuite_lite_text_color',
			'storesuite_icon_color',
			'storesuite_color_sidebar_menu_text',
			'storesuite_color_sidebar_background',
			'storesuite_color_sidebar_active_text',
			'storesuite_color_sidebar_active_background',
			'storesuite_color_border',
			'storesuite_color_lite_bg',
		);
		foreach ( $color_keys as $key ) {
			if ( $request->has_param( $key ) ) {
				$storesuite_settings[ $key ] = sanitize_hex_color( $request->get_param( $key ) ) ?: sanitize_text_field( $request->get_param( $key ) );
			}
		}

		update_option( 'storesuite_settings', $storesuite_settings );

		return $this->get_settings( $request );
	}

	/**
	 * Check if a given request has access to get the settings.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool True if the request has access, false otherwise.
	 */
	public function get_settings_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Check if a given request has access to update the settings.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool True if the request has access, false otherwise.
	 */
	public function update_settings_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Get the schema for a single item, if any.
	 *
	 * @return array
	 */
	public function get_item_schema() {
		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'settings',
			'type'       => 'object',
			'properties' => array(
				'storesuite_dashboard_page_id'             => array(
					'description' => __( 'Dashboard Page.', 'storesuite' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
				),
				'storesuite_prevent_admin_access'          => array(
					'description' => __( 'Prevent vendors from accessing wp-admin. If HPOS is enabled, admin access is blocked regardless.', 'storesuite' ),
					'type'        => 'string',
					'enum'        => array( 'yes', 'no' ),
					'context'     => array( 'view', 'edit' ),
				),
				'storesuite_show_perf_revenue_total_sales' => array(
					'description' => __( 'Show Total sales performance box.', 'storesuite' ),
					'type'        => 'string',
					'enum'        => array( 'yes', 'no' ),
					'context'     => array( 'view', 'edit' ),
				),
				'storesuite_show_perf_revenue_gross_sales' => array(
					'description' => __( 'Show Gross sales performance box.', 'storesuite' ),
					'type'        => 'string',
					'enum'        => array( 'yes', 'no' ),
					'context'     => array( 'view', 'edit' ),
				),
				'storesuite_show_perf_revenue_net_revenue' => array(
					'description' => __( 'Show Net sales performance box.', 'storesuite' ),
					'type'        => 'string',
					'enum'        => array( 'yes', 'no' ),
					'context'     => array( 'view', 'edit' ),
				),
				'storesuite_show_perf_orders_orders_count' => array(
					'description' => __( 'Show Orders performance box.', 'storesuite' ),
					'type'        => 'string',
					'enum'        => array( 'yes', 'no' ),
					'context'     => array( 'view', 'edit' ),
				),
				'storesuite_show_perf_orders_avg_order_value' => array(
					'description' => __( 'Show Average order value performance box.', 'storesuite' ),
					'type'        => 'string',
					'enum'        => array( 'yes', 'no' ),
					'context'     => array( 'view', 'edit' ),
				),
				'storesuite_show_perf_products_items_sold' => array(
					'description' => __( 'Show Products sold performance box.', 'storesuite' ),
					'type'        => 'string',
					'enum'        => array( 'yes', 'no' ),
					'context'     => array( 'view', 'edit' ),
				),
				'storesuite_show_perf_variations_items_sold' => array(
					'description' => __( 'Show Variations sold performance box.', 'storesuite' ),
					'type'        => 'string',
					'enum'        => array( 'yes', 'no' ),
					'context'     => array( 'view', 'edit' ),
				),
				'storesuite_show_perf_revenue_refunds'     => array(
					'description' => __( 'Show Returns performance box.', 'storesuite' ),
					'type'        => 'string',
					'enum'        => array( 'yes', 'no' ),
					'context'     => array( 'view', 'edit' ),
				),
				'storesuite_show_perf_coupons_orders_count' => array(
					'description' => __( 'Show Discounted orders performance box.', 'storesuite' ),
					'type'        => 'string',
					'enum'        => array( 'yes', 'no' ),
					'context'     => array( 'view', 'edit' ),
				),
				'storesuite_show_perf_coupons_amount'      => array(
					'description' => __( 'Show Net discount amount performance box.', 'storesuite' ),
					'type'        => 'string',
					'enum'        => array( 'yes', 'no' ),
					'context'     => array( 'view', 'edit' ),
				),
				'storesuite_show_perf_taxes_total_tax'     => array(
					'description' => __( 'Show Total tax performance box.', 'storesuite' ),
					'type'        => 'string',
					'enum'        => array( 'yes', 'no' ),
					'context'     => array( 'view', 'edit' ),
				),
				'storesuite_show_perf_taxes_order_tax'     => array(
					'description' => __( 'Show Order tax performance box.', 'storesuite' ),
					'type'        => 'string',
					'enum'        => array( 'yes', 'no' ),
					'context'     => array( 'view', 'edit' ),
				),
				'storesuite_show_perf_taxes_shipping_tax'  => array(
					'description' => __( 'Show Shipping tax performance box.', 'storesuite' ),
					'type'        => 'string',
					'enum'        => array( 'yes', 'no' ),
					'context'     => array( 'view', 'edit' ),
				),
				'storesuite_show_perf_revenue_shipping'    => array(
					'description' => __( 'Show Shipping performance box.', 'storesuite' ),
					'type'        => 'string',
					'enum'        => array( 'yes', 'no' ),
					'context'     => array( 'view', 'edit' ),
				),
				'storesuite_show_perf_downloads_download_count' => array(
					'description' => __( 'Show Downloads performance box.', 'storesuite' ),
					'type'        => 'string',
					'enum'        => array( 'yes', 'no' ),
					'context'     => array( 'view', 'edit' ),
				),
				'storesuite_product_per_page'              => array(
					'description' => __( 'Products Per Page.', 'storesuite' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'storesuite_order_per_page'                => array(
					'description' => __( 'Orders Per Page.', 'storesuite' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'storesuite_category_per_page'             => array(
					'description' => __( 'Categories Per Page.', 'storesuite' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'storesuite_tag_per_page'                  => array(
					'description' => __( 'Tags Per Page.', 'storesuite' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'storesuite_brand_per_page'                => array(
					'description' => __( 'Brands Per Page.', 'storesuite' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'storesuite_coupon_per_page'               => array(
					'description' => __( 'Coupons Per Page.', 'storesuite' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'storesuite_color_button_text'             => array(
					'description' => __( 'Button text color.', 'storesuite' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'storesuite_color_button_background'       => array(
					'description' => __( 'Button background color.', 'storesuite' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'storesuite_color_button_border'           => array(
					'description' => __( 'Button border color.', 'storesuite' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'storesuite_color_button_hover_text'       => array(
					'description' => __( 'Button hover text color.', 'storesuite' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'storesuite_color_button_hover_background' => array(
					'description' => __( 'Button hover background color.', 'storesuite' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'storesuite_color_button_hover_border'     => array(
					'description' => __( 'Button hover border color.', 'storesuite' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'storesuite_color_sidebar_menu_text'       => array(
					'description' => __( 'Dashboard sidebar menu text color.', 'storesuite' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'storesuite_color_sidebar_background'      => array(
					'description' => __( 'Dashboard sidebar background color.', 'storesuite' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'storesuite_color_sidebar_active_text'     => array(
					'description' => __( 'Dashboard sidebar active/hover menu text color.', 'storesuite' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'storesuite_color_sidebar_active_background' => array(
					'description' => __( 'Dashboard sidebar active menu background color.', 'storesuite' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'storesuite_show_widget_top_products_items_sold' => array(
					'description' => __( 'Show Top products - Items sold widget.', 'storesuite' ),
					'type'        => 'string',
					'enum'        => array( 'yes', 'no' ),
					'context'     => array( 'view', 'edit' ),
				),
				'storesuite_show_widget_top_categories_items_sold' => array(
					'description' => __( 'Show Top categories - Items sold widget.', 'storesuite' ),
					'type'        => 'string',
					'enum'        => array( 'yes', 'no' ),
					'context'     => array( 'view', 'edit' ),
				),
				'storesuite_show_widget_top_customers_total_spend' => array(
					'description' => __( 'Show Top customers - Total spend widget.', 'storesuite' ),
					'type'        => 'string',
					'enum'        => array( 'yes', 'no' ),
					'context'     => array( 'view', 'edit' ),
				),
				'storesuite_show_widget_top_coupons_orders_count' => array(
					'description' => __( 'Show Top coupons - Number of orders widget.', 'storesuite' ),
					'type'        => 'string',
					'enum'        => array( 'yes', 'no' ),
					'context'     => array( 'view', 'edit' ),
				),
			),
		);
	}
}
