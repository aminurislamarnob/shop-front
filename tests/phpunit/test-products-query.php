<?php
/**
 * Tests for the products list query layer: filters and sorting (issue #189).
 *
 * @package StoreSuite\Tests
 */

use PluginizeLab\StoreSuite\Product\Products;

/**
 * @covers \PluginizeLab\StoreSuite\Product\Products
 */
class Test_Products_Query extends WP_UnitTestCase {

	/**
	 * Create a simple product.
	 *
	 * @param string      $name  Product name.
	 * @param string      $price Regular price ('' to leave unpriced).
	 * @param string|null $sku   Optional SKU.
	 * @return WC_Product_Simple
	 */
	private function create_product( $name, $price = '10', $sku = null ) {
		$product = new WC_Product_Simple();
		$product->set_name( $name );
		if ( '' !== $price ) {
			$product->set_regular_price( $price );
		}
		if ( null !== $sku ) {
			$product->set_sku( $sku );
		}
		$product->save();

		return $product;
	}

	/**
	 * Run the query and return the matched product IDs.
	 *
	 * @param array  $filters     Filters array.
	 * @param string $search_term Search term.
	 * @return int[]
	 */
	private function query_ids( $filters = array(), $search_term = '' ) {
		$result = ( new Products() )->get_paginated_products( 1, $search_term, $filters );

		return wp_list_pluck( $result->products->posts, 'ID' );
	}

	public function test_status_filter_honors_allow_list() {
		$published = $this->create_product( 'Published One' );
		$draft_id  = $this->create_product( 'Draft One' )->get_id();
		wp_update_post(
			array(
				'ID'          => $draft_id,
				'post_status' => 'draft',
			)
		);

		$ids = $this->query_ids( array( 'status' => 'draft' ) );
		$this->assertContains( $draft_id, $ids );
		$this->assertNotContains( $published->get_id(), $ids );

		// A status outside the allow-list is ignored: both products stay visible.
		$ids = $this->query_ids( array( 'status' => 'trash' ) );
		$this->assertContains( $published->get_id(), $ids );
		$this->assertContains( $draft_id, $ids );
	}

	public function test_price_range_filters() {
		$cheap  = $this->create_product( 'Cheap', '5' )->get_id();
		$medium = $this->create_product( 'Medium', '20' )->get_id();
		$dear   = $this->create_product( 'Expensive', '80' )->get_id();

		$ids = $this->query_ids(
			array(
				'price_min' => '10',
				'price_max' => '50',
			)
		);
		$this->assertSame( array( $medium ), array_values( $ids ) );

		$ids = $this->query_ids( array( 'price_min' => '10' ) );
		$this->assertContains( $medium, $ids );
		$this->assertContains( $dear, $ids );
		$this->assertNotContains( $cheap, $ids );

		$ids = $this->query_ids( array( 'price_max' => '10' ) );
		$this->assertSame( array( $cheap ), array_values( $ids ) );
	}

	public function test_created_date_range_filter() {
		$old_id = static::factory()->post->create(
			array(
				'post_type'   => 'product',
				'post_status' => 'publish',
				'post_date'   => '2020-01-15 10:00:00',
			)
		);
		$new_id = static::factory()->post->create(
			array(
				'post_type'   => 'product',
				'post_status' => 'publish',
				'post_date'   => '2024-06-15 10:00:00',
			)
		);

		$ids = $this->query_ids(
			array(
				'date_from' => '2024-01-01',
				'date_to'   => '2024-12-31',
			)
		);
		$this->assertContains( $new_id, $ids );
		$this->assertNotContains( $old_id, $ids );
	}

	public function test_price_sorting_keeps_products_without_price_meta() {
		$low  = $this->create_product( 'Low', '5' )->get_id();
		$high = $this->create_product( 'High', '50' )->get_id();

		$grouped = new WC_Product_Grouped();
		$grouped->set_name( 'No Price Grouped' );
		$grouped->save();

		$ids = $this->query_ids(
			array(
				'orderby' => 'price',
				'order'   => 'asc',
			)
		);

		$this->assertContains( $grouped->get_id(), $ids, 'Products without _price must not be dropped by the price sort.' );
		$this->assertLessThan( array_search( $high, $ids, true ), array_search( $low, $ids, true ) );
	}

	public function test_unknown_orderby_is_ignored() {
		$this->create_product( 'Any Product' );

		$result = ( new Products() )->get_paginated_products(
			1,
			'',
			array(
				'orderby' => 'evil_column',
				'order'   => 'asc',
			)
		);

		$this->assertSame( 1, $result->found_posts );
	}

	public function test_search_matches_sku() {
		$this->create_product( 'Alpha Widget', '10', 'AAA-111' );
		$target = $this->create_product( 'Beta Widget', '10', 'ZZZ-999' )->get_id();

		$ids = $this->query_ids( array(), 'ZZZ-999' );
		$this->assertContains( $target, $ids );
	}
}
