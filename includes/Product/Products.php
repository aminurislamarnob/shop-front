<?php
/**
 * Products handler
 *
 * @package ShopFront
 */

namespace PluginizeLab\ShopFront\Product;

/**
 * Products class
 */
class Products {

	/**
	 * Get paginated products with search functionality.
	 *
	 * @param int    $page Current page number.
	 * @param string $search_term Search term to filter products.
	 * @return object
	 */
	public function get_paginated_products( $page = 1, $search_term = '' ) {
		// Get configuration from filters
		$statuses = apply_filters( 'msf_product_listing_post_statuses', array( 'publish', 'draft', 'pending', 'future' ) );
		$per_page = apply_filters( 'msf_products_per_page', 10 );

		$query = array(
			'posts_per_page' => $per_page,
			'post_type'      => 'product',
			'post_status'    => $statuses,
			'paged'          => $page,
		);

		// Add search functionality (searches in title, content, excerpt, and SKU)
		if ( ! empty( $search_term ) ) {
			$query['s'] = $search_term;

			// Add filter to extend search to SKU field
			add_filter(
				'posts_search',
				function ( $where ) use ( $search_term ) {
					return $this->extend_search_to_sku( $where, $search_term );
				},
				10,
				1
			);
		}

		$product_query = new \WP_Query( $query );

		// Remove filter after query
		if ( ! empty( $search_term ) ) {
			remove_all_filters( 'posts_search' );
		}

		return (object) array(
			'products'      => $product_query,
			'found_posts'   => $product_query->found_posts,
			'max_num_pages' => $product_query->max_num_pages,
			'current_page'  => $page,
			'per_page'      => $per_page,
			'search_term'   => $search_term,
		);
	}

	/**
	 * Extend product search to include SKU field.
	 *
	 * @param string $where SQL WHERE clause.
	 * @param string $search_term Search term.
	 * @return string Modified WHERE clause.
	 */
	private function extend_search_to_sku( $where, $search_term ) {
		global $wpdb;

		if ( empty( $where ) || empty( $search_term ) ) {
			return $where;
		}

		// Search for products by SKU
		$search_ids = array();
		$wild       = '%';
		$find       = $search_term;
		$like       = $wild . $wpdb->esc_like( $find ) . $wild;

		// Get product IDs that match the SKU
		$sku_to_id = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_sku' AND meta_value LIKE %s", $like ) );

		if ( $sku_to_id && count( $sku_to_id ) > 0 ) {
			$search_ids = array_filter( array_map( 'absint', $sku_to_id ) );
		}

		// Add product IDs to the WHERE clause with OR logic
		if ( count( $search_ids ) > 0 ) {
			$where = str_replace( ')))', ") OR ({$wpdb->posts}.ID IN (" . implode( ',', $search_ids ) . '))))', $where );
		}

		return $where;
	}
}
