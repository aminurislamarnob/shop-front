<?php
/**
 * Inventory Manager — REST controller (low-stock widget feed).
 *
 * @package StoreSuite
 */

namespace PluginizeLab\StoreSuite\Modules\InventoryManager;

use PluginizeLab\StoreSuite\Cache;
use WP_REST_Controller;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Exposes the low-stock list at `storesuite/v1/inventory/low-stock` for the
 * dashboard-home widget. Cached briefly and busted on stock changes.
 */
class RestController extends WP_REST_Controller {

	const CACHE_KEY = 'inventory_low_stock';

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->namespace = 'storesuite/v1';
		$this->rest_base = 'inventory';
	}

	/**
	 * Register routes + cache-busting on stock changes. Called on rest_api_init.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/low-stock',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_low_stock' ),
					'permission_callback' => array( $this, 'permissions_check' ),
				),
			)
		);
	}

	/**
	 * Bust the cache when stock changes. Registered from Module::boot().
	 *
	 * @return void
	 */
	public static function register_cache_busting() {
		$bust = function () {
			if ( class_exists( Cache::class ) ) {
				Cache::delete( self::CACHE_KEY );
			}
		};
		add_action( 'woocommerce_product_set_stock', $bust );
		add_action( 'woocommerce_variation_set_stock', $bust );
	}

	/**
	 * @return bool
	 */
	public function permissions_check( $request ) {
		unset( $request );
		return storesuite_current_user_can( 'manage_inventory' );
	}

	/**
	 * GET the low-stock list (cached 5 minutes).
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response
	 */
	public function get_low_stock( $request ) {
		$limit = $request->get_param( 'per_page' ) ? absint( $request->get_param( 'per_page' ) ) : 20;

		if ( class_exists( Cache::class ) ) {
			$cached = Cache::get( self::CACHE_KEY );
			if ( is_array( $cached ) ) {
				return rest_ensure_response( array_slice( $cached, 0, $limit ) );
			}
		}

		$result = StockRepository::get_paginated(
			array(
				'low_only' => true,
				'per_page' => 100,
				'paged'    => 1,
			)
		);

		$items = array();
		foreach ( $result['items'] as $item ) {
			$items[] = array(
				'id'        => $item['id'],
				'name'      => $item['name'],
				'sku'       => $item['sku'],
				'stock_qty' => $item['stock_qty'],
				'low_stock' => $item['low_stock'],
			);
		}

		if ( class_exists( Cache::class ) ) {
			Cache::set( self::CACHE_KEY, $items, 5 * MINUTE_IN_SECONDS );
		}

		return rest_ensure_response( array_slice( $items, 0, $limit ) );
	}
}
