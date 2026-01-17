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
	 * @param int $per_page Items per page.
	 * @param int $page Current page number.
	 * @return object
	 */
	public function get_paginated_tags( $per_page = 10, $page = 1 ) {
		$offset = ( $page - 1 ) * $per_page;

		// Get total count for pagination.
		$total_tags = wp_count_terms(
			array(
				'taxonomy'   => 'product_tag',
				'hide_empty' => false,
			)
		);

		$total = is_wp_error( $total_tags ) ? 0 : $total_tags;

		// Get paginated tags.
		$tags = get_terms(
			array(
				'taxonomy'   => 'product_tag',
				'hide_empty' => false,
				'orderby'    => 'name',
				'order'      => 'ASC',
				'number'     => $per_page,
				'offset'     => $offset,
			)
		);

		$tags      = is_wp_error( $tags ) ? array() : $tags;
		$max_pages = ceil( $total / $per_page );

		return (object) array(
			'tags'          => $tags,
			'total'         => $total,
			'max_num_pages' => $max_pages,
			'current_page'  => $page,
			'per_page'      => $per_page,
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
