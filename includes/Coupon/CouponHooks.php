<?php

namespace PluginizeLab\ShopFront\Coupon;

/**
 * Plugin coupon hooks class
 */
class CouponHooks {
	/**
	 * The constructor.
	 */
	public function __construct() {
		// Add custom hooks and filters for coupons here.
		add_filter( 'msf_coupon_discount_types', array( $this, 'get_discount_types' ), 10 );
	}

	/**
	 * Get available discount types for coupons.
	 *
	 * @param array $types Existing discount types.
	 *
	 * @return array
	 */
	public function get_discount_types( $types ) {
		return wc_get_coupon_types();
	}
}
