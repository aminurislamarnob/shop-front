<?php

namespace PluginizeLab\StoreSuite\Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin order controller class
 */
class ProductHooks {
	/**
	 * The constructor.
	 */
	public function __construct() {
		add_filter( 'storesuite_product_types', array( $this, 'set_product_types' ), 10 );
		add_filter( 'storesuite_product_statuses', array( $this, 'set_product_statuses' ), 10 );
	}

	/**
	 * Set default product types
	 *
	 * @param array $product_types
	 *
	 * @return array
	 */
	public function set_product_types( $product_types ) {
		$product_types = array(
			'simple'   => __( 'Simple', 'storesuite' ),
			'variable' => __( 'Variable', 'storesuite' ),
			'external' => __( 'External/Affiliate product', 'storesuite' ),
			'grouped'  => __( 'Group Product', 'storesuite' ),
		);
		return $product_types;
	}

	/**
	 * Set default product statuses
	 *
	 * @param array $product_statuses
	 *
	 * @return array
	 */
	public function set_product_statuses() {
		$product_statuses = array(
			'publish' => __( 'Publish', 'storesuite' ),
			'draft'   => __( 'Draft', 'storesuite' ),
			'pending' => __( 'Pending Review', 'storesuite' ),
		);
		return $product_statuses;
	}
}
