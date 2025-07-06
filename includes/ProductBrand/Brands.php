<?php

namespace PluginizeLab\ShopFront\ProductBrand;

/**
 * Plugin product brands class
 */
class Brands {
	/**
	 * Get all product brands.
	 *
	 * @return array
	 */
	public function get_product_brands() {
		$brands = get_terms(
			array(
				'taxonomy'   => 'product_brand',
				'hide_empty' => false,
				'orderby'    => 'name',
				'order'      => 'ASC',
			)
		);

		return is_wp_error( $brands ) ? array() : $brands;
	}

	/**
	 * Display brand table rows.
	 *
	 * @return void
	 */
	public function display_brands() {
		$brands = $this->get_product_brands();

		foreach ( $brands as $brand ) {
			$template_args = array(
				'brand' => $brand,
			);
			msf_get_template_part( 'brands/brand-list-table-row', '', $template_args );
		}
	}

	/**
	 * Get brand by ID.
	 *
	 * @param int $brand_id Brand ID.
	 * @return WP_Term|WP_Error|null
	 */
	public function get_brand_by_id( $brand_id ) {
		return get_term( $brand_id, 'product_brand' );
	}

	/**
	 * Get brand count.
	 *
	 * @return int
	 */
	public function get_brand_count() {
		$brands = $this->get_product_brands();
		return count( $brands );
	}
} 