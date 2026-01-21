<?php
/**
 * Product tags handler
 *
 * @package ShopFront
 */

namespace PluginizeLab\ShopFront\ProductTag;

/**
 * Tags class
 */
class Tags {

	/**
	 * Get paginated tags.
	 *
	 * @param int    $per_page Items per page.
	 * @param int    $page Current page number.
	 * @param string $search_term Search term to filter tags.
	 * @return object
	 */
	public function get_paginated_tags( $per_page = 10, $page = 1, $search_term = '' ) {
		$offset = ( $page - 1 ) * $per_page;

		$args = array(
			'taxonomy'   => 'product_tag',
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
		);

		// Add search parameter if provided
		if ( ! empty( $search_term ) ) {
			$args['search'] = $search_term;
		}

		// Get total count for pagination.
		$total_tags = wp_count_terms( $args );
		$total      = is_wp_error( $total_tags ) ? 0 : $total_tags;

		// Get paginated tags.
		$args['number'] = $per_page;
		$args['offset'] = $offset;
		$tags           = get_terms( $args );

		$tags      = is_wp_error( $tags ) ? array() : $tags;
		$max_pages = ceil( $total / $per_page );

		return (object) array(
			'tags'          => $tags,
			'total'         => $total,
			'max_num_pages' => $max_pages,
			'current_page'  => $page,
			'per_page'      => $per_page,
			'search_term'   => $search_term,
		);
	}

	/**
	 * Get tag by ID.
	 *
	 * @param int $tag_id Tag ID.
	 * @return WP_Term|WP_Error|null
	 */
	public function get_tag_by_id( $tag_id ) {
		return get_term( $tag_id, 'product_tag' );
	}
}
