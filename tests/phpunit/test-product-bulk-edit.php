<?php
/**
 * Tests for the product bulk actions (issue #187).
 *
 * @package StoreSuite\Tests
 */

/**
 * @covers \PluginizeLab\StoreSuite\Product\ProductBulkEdit
 */
class Test_Product_Bulk_Edit extends WP_Ajax_UnitTestCase {

	/**
	 * Create a simple product.
	 *
	 * @param string $name Product name.
	 * @return WC_Product_Simple
	 */
	private function create_simple_product( $name = 'Bulk Test Product' ) {
		$product = new WC_Product_Simple();
		$product->set_name( $name );
		$product->set_regular_price( '10' );
		$product->save();

		return $product;
	}

	/**
	 * Dispatch a bulk AJAX action and return the decoded JSON response.
	 *
	 * @param string $action      AJAX action name (also the nonce action).
	 * @param array  $post_fields Request fields.
	 * @return array
	 */
	private function do_bulk_ajax( $action, $post_fields ) {
		// _last_response accumulates across _handleAjax calls; start fresh.
		$this->_last_response = '';

		$_POST = array_merge(
			array(
				'security' => wp_create_nonce( $action ),
			),
			$post_fields
		);

		try {
			$this->_handleAjax( $action );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		// Strip any debug output printed before or after the JSON payload.
		$raw   = $this->_last_response;
		$start = strpos( $raw, '{' );
		$end   = strrpos( $raw, '}' );
		if ( false !== $start && false !== $end && $end >= $start ) {
			$raw = substr( $raw, $start, $end - $start + 1 );
		}

		$decoded = json_decode( $raw, true );
		if ( null === $decoded ) {
			$this->fail( 'Non-JSON AJAX response (' . strlen( $this->_last_response ) . ' bytes), tail: ' . substr( $this->_last_response, -600 ) );
		}

		return $decoded;
	}

	public function test_bulk_trash_moves_products_to_trash_and_skips_non_products() {
		$this->_setRole( 'administrator' );
		$product_a = $this->create_simple_product( 'Trash A' );
		$product_b = $this->create_simple_product( 'Trash B' );
		$page_id   = static::factory()->post->create( array( 'post_type' => 'page' ) );

		$response = $this->do_bulk_ajax(
			'storesuite_bulk_trash_products',
			array(
				'product_ids' => array( $product_a->get_id(), $product_b->get_id(), $page_id ),
			)
		);

		$this->assertTrue( $response['success'] );
		$this->assertSame( 2, $response['data']['trashed'] );
		$this->assertSame( 'trash', get_post_status( $product_a->get_id() ) );
		$this->assertSame( 'trash', get_post_status( $product_b->get_id() ) );
		$this->assertNotSame( 'trash', get_post_status( $page_id ), 'Non-product posts must be skipped.' );
	}

	public function test_bulk_delete_removes_products_permanently() {
		$this->_setRole( 'administrator' );
		$product = $this->create_simple_product( 'Delete Me' );

		$response = $this->do_bulk_ajax(
			'storesuite_bulk_delete_products',
			array(
				'product_ids' => array( $product->get_id() ),
			)
		);

		$this->assertTrue( $response['success'] );
		$this->assertSame( 1, $response['data']['deleted'] );
		$this->assertNull( get_post( $product->get_id() ) );
	}

	public function test_bulk_edit_adds_and_removes_categories() {
		$this->_setRole( 'administrator' );
		$product = $this->create_simple_product( 'Category Target' );
		$term    = wp_insert_term( 'Bulk Cat', 'product_cat' );
		$term_id = $term['term_id'];

		// Add.
		$response = $this->do_bulk_ajax(
			'storesuite_bulk_edit_products',
			array(
				'post'                   => array( $product->get_id() ),
				'post_type'              => 'product',
				'_status'                => '-1',
				'storesuite_bulk_cat_op' => 'add',
				'storesuite_bulk_cats'   => array( $term_id ),
			)
		);

		$this->assertTrue( $response['success'] );
		$this->assertTrue( has_term( $term_id, 'product_cat', $product->get_id() ) );

		// Remove.
		$response = $this->do_bulk_ajax(
			'storesuite_bulk_edit_products',
			array(
				'post'                   => array( $product->get_id() ),
				'post_type'              => 'product',
				'_status'                => '-1',
				'storesuite_bulk_cat_op' => 'remove',
				'storesuite_bulk_cats'   => array( $term_id ),
			)
		);

		$this->assertTrue( $response['success'] );
		$this->assertFalse( has_term( $term_id, 'product_cat', $product->get_id() ) );
	}

	public function test_bulk_trash_requires_capability() {
		$this->_setRole( 'administrator' );
		$product = $this->create_simple_product( 'Protected' );

		$this->_setRole( 'subscriber' );
		$response = $this->do_bulk_ajax(
			'storesuite_bulk_trash_products',
			array(
				'product_ids' => array( $product->get_id() ),
			)
		);

		// Subscriber lacks delete_post on the product, so nothing may be trashed.
		$this->assertSame( 'publish', get_post_status( $product->get_id() ) );
		$this->assertTrue( empty( $response['data']['trashed'] ) );
	}

	public function test_bulk_edit_requires_capability() {
		$this->_setRole( 'administrator' );
		$product = $this->create_simple_product( 'Edit Protected' );

		$this->_setRole( 'subscriber' );
		$response = $this->do_bulk_ajax(
			'storesuite_bulk_edit_products',
			array(
				'post'      => array( $product->get_id() ),
				'post_type' => 'product',
				'_status'   => 'draft',
			)
		);

		$this->assertFalse( $response['success'] );
		$this->assertSame( 'publish', get_post_status( $product->get_id() ) );
	}
}
