<?php
/**
 * Product categories handler
 *
 * @package ShopFront
 */

namespace PluginizeLab\ShopFront\ProductCategory;

use PluginizeLab\ShopFront\Cache;

/**
 * Categories class
 */
class Categories {

	/**
	 * The constructor.
	 */
	public function __construct() {
		add_action( 'msf_product_category_created', array( $this, 'clear_cache' ) );
		add_action( 'msf_product_category_updated', array( $this, 'clear_cache' ) );
		add_action( 'msf_product_category_deleted', array( $this, 'clear_cache' ) );
	}

	/**
	 * Build category hierarchy recursively.
	 *
	 * @param int $parent_id Parent category ID.
	 * @return array
	 */
	private function build_hierarchy( $parent_id = 0 ) {
		$categories = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
				'parent'     => $parent_id,
			)
		);

		if ( empty( $categories ) ) {
			return array();
		}

		$result = array();

		foreach ( $categories as $category ) {
			$result[ $category->term_id ] = array(
				'category'      => $category,
				'subcategories' => $this->build_hierarchy( $category->term_id ),
			);
		}

		return $result;
	}

	/**
	 * Flatten hierarchy to ordered list with depth.
	 *
	 * @param array  $hierarchy Category hierarchy.
	 * @param int    $depth Current depth level.
	 * @param object $parent_category Parent category object.
	 * @return array
	 */
	private function flatten( $hierarchy, $depth = 0, $parent_category = null ) {
		$result = array();

		foreach ( $hierarchy as $data ) {
			$result[] = array(
				'category' => $data['category'],
				'depth'    => $depth,
				'parent'   => ( $depth > 0 && $parent_category ) ? $parent_category : null,
			);

			if ( ! empty( $data['subcategories'] ) ) {
				$result = array_merge( $result, $this->flatten( $data['subcategories'], $depth + 1, $data['category'] ) );
			}
		}

		return $result;
	}

	/**
	 * Get all categories as flat list (with caching).
	 *
	 * @param string $search_term Search term to filter categories.
	 * @return array
	 */
	private function get_all_flat( $search_term = '' ) {
		// Don't use cache when searching
		if ( ! empty( $search_term ) ) {
			$hierarchy = $this->build_hierarchy();
			$all       = $this->flatten( $hierarchy );
			return $this->filter_by_search( $all, $search_term );
		}

		$cached = Cache::get( 'flat_categories' );

		if ( false !== $cached ) {
			return $cached;
		}

		$hierarchy = $this->build_hierarchy();
		$all       = $this->flatten( $hierarchy );

		Cache::set( 'flat_categories', $all, HOUR_IN_SECONDS );

		return $all;
	}

	/**
	 * Filter categories by search term.
	 * Searches in name, slug, and description (similar to WooCommerce admin).
	 *
	 * @param array  $categories Array of categories.
	 * @param string $search_term Search term.
	 * @return array Filtered categories.
	 */
	private function filter_by_search( $categories, $search_term ) {
		if ( empty( $search_term ) ) {
			return $categories;
		}

		$search_term = strtolower( $search_term );
		$filtered    = array();

		foreach ( $categories as $category_data ) {
			$category = $category_data['category'];

			// Search in name, slug, and description
			$name        = strtolower( $category->name );
			$slug        = strtolower( $category->slug );
			$description = strtolower( $category->description );

			if ( strpos( $name, $search_term ) !== false ||
				strpos( $slug, $search_term ) !== false ||
				strpos( $description, $search_term ) !== false ) {
				$filtered[] = $category_data;
			}
		}

		return $filtered;
	}

	/**
	 * Clear categories cache.
	 *
	 * @return void
	 */
	public function clear_cache() {
		Cache::delete( 'flat_categories' );
	}

	/**
	 * Get paginated categories.
	 *
	 * @param int    $per_page Items per page.
	 * @param int    $page Current page number.
	 * @param string $search_term Search term to filter categories.
	 * @return object
	 */
	public function get_paginated_categories_with_children( $per_page = 10, $page = 1, $search_term = '' ) {
		$all        = $this->get_all_flat( $search_term );
		$total      = count( $all );
		$max_pages  = ceil( $total / $per_page );
		$offset     = ( $page - 1 ) * $per_page;
		$categories = array_slice( $all, $offset, $per_page );

		return (object) array(
			'categories'    => $categories,
			'total'         => $total,
			'max_num_pages' => $max_pages,
			'current_page'  => $page,
			'per_page'      => $per_page,
			'search_term'   => $search_term,
		);
	}
}
