<?php
/**
 * Product brands handler
 *
 * @package ShopFront
 */

namespace PluginizeLab\ShopFront\ProductBrand;

use PluginizeLab\ShopFront\Cache;

/**
 * Plugin product brands class
 */
class Brands {

	/**
	 * The constructor.
	 */
	public function __construct() {
		add_action( 'msf_product_brand_created', array( $this, 'clear_cache' ) );
		add_action( 'msf_product_brand_updated', array( $this, 'clear_cache' ) );
		add_action( 'msf_product_brand_deleted', array( $this, 'clear_cache' ) );
	}

	/**
	 * Build brand hierarchy recursively.
	 *
	 * @param int $parent_id Parent brand ID.
	 * @return array
	 */
	private function build_hierarchy( $parent_id = 0 ) {
		$brands = get_terms(
			array(
				'taxonomy'   => 'product_brand',
				'hide_empty' => false,
				'parent'     => $parent_id,
			)
		);

		if ( empty( $brands ) ) {
			return array();
		}

		$result = array();

		foreach ( $brands as $brand ) {
			$result[ $brand->term_id ] = array(
				'brand'     => $brand,
				'subbrands' => $this->build_hierarchy( $brand->term_id ),
			);
		}

		return $result;
	}

	/**
	 * Flatten hierarchy to ordered list with depth.
	 *
	 * @param array  $hierarchy Brand hierarchy.
	 * @param int    $depth Current depth level.
	 * @param object $parent_brand Parent brand object.
	 * @return array
	 */
	private function flatten( $hierarchy, $depth = 0, $parent_brand = null ) {
		$result = array();

		foreach ( $hierarchy as $data ) {
			$result[] = array(
				'brand'  => $data['brand'],
				'depth'  => $depth,
				'parent' => ( $depth > 0 && $parent_brand ) ? $parent_brand : null,
			);

			if ( ! empty( $data['subbrands'] ) ) {
				$result = array_merge( $result, $this->flatten( $data['subbrands'], $depth + 1, $data['brand'] ) );
			}
		}

		return $result;
	}

	/**
	 * Get all brands as flat list (with caching).
	 *
	 * @return array
	 */
	private function get_all_flat() {
		$cached = Cache::get( 'flat_brands' );

		if ( false !== $cached ) {
			return $cached;
		}

		$hierarchy = $this->build_hierarchy();
		$all       = $this->flatten( $hierarchy );

		Cache::set( 'flat_brands', $all, HOUR_IN_SECONDS );

		return $all;
	}

	/**
	 * Clear brands cache.
	 *
	 * @return void
	 */
	public function clear_cache() {
		Cache::delete( 'flat_brands' );
	}

	/**
	 * Get paginated brands.
	 *
	 * @param int $per_page Items per page.
	 * @param int $page Current page number.
	 * @return object
	 */
	public function get_paginated_brands_with_children( $per_page = 10, $page = 1 ) {
		$all       = $this->get_all_flat();
		$total     = count( $all );
		$max_pages = ceil( $total / $per_page );
		$offset    = ( $page - 1 ) * $per_page;
		$brands    = array_slice( $all, $offset, $per_page );

		return (object) array(
			'brands'        => $brands,
			'total'         => $total,
			'max_num_pages' => $max_pages,
			'current_page'  => $page,
			'per_page'      => $per_page,
		);
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

		if ( is_wp_error( $brands ) ) {
			return array();
		}

		return $brands;
	}
}
