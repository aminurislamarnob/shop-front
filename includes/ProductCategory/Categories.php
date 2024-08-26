<?php

namespace PluginizeLab\ShopFront\ProductCategory;

/**
 * Plugin product categories class
 */
class Categories {
	/**
	 * Recursive function to get woocommerce parent and its subcategories.
	 *
	 * @return array
	 */
	public function get_product_parent_and_subcategories_recursively( $parent_id = 0 ) {
		$categories = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
				'parent'     => $parent_id,
			)
		);

		$categories_hierarchy = array();

		foreach ( $categories as $category ) {
			$subcategories = $this->get_product_parent_and_subcategories_recursively( $category->term_id );

			$categories_hierarchy[ $category->term_id ] = array(
				'category'      => $category,
				'subcategories' => $subcategories,
			);
		}

		return $categories_hierarchy;
	}

	/**
	 * Recursive function to display category table each row..
	 *
	 * @return void
	 */
	public function display_categories_recursively( $categories_hierarchy, $depth = 0 ) {
		foreach ( $categories_hierarchy as $category_id => $data ) {
			$category      = $data['category'];
			$subcategories = $data['subcategories'];
			$dash_prefix   = str_repeat( '— ', $depth );

			$template_args = array(
				'category'    => $category,
				'dash_prefix' => $dash_prefix,
			);
			msf_get_template_part( 'categories/category-list-table-row', '', $template_args );

			if ( ! empty( $subcategories ) ) {
				$this->display_categories_recursively( $subcategories, $depth + 1 );
			}
		}
	}
}
