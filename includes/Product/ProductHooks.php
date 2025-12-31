<?php

namespace PluginizeLab\ShopFront\Product;

/**
 * Plugin order controller class
 */
class ProductHooks {
	/**
     * The constructor.
     */
    public function __construct() {
		add_filter( 'msf_product_types', array( $this, 'set_product_types' ), 10 );
		add_filter( 'msf_product_statuses', array( $this, 'set_product_statuses' ), 10 );
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
            'simple'   => __( 'Simple', 'dokan' ),
            'variable' => __( 'Variable', 'dokan' ),
            'external' => __( 'External/Affiliate product', 'dokan' ),
            'grouped' => __( 'Group Product', 'dokan' ),
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
	public function set_product_statuses(){
		$product_statuses = array(
            'publish'   => __( 'Publish', 'dokan' ),
            'draft' => __( 'Draft', 'dokan' ),
            'pending' => __( 'Pending Review', 'dokan' ),
        );
        return $product_statuses;
	}
}