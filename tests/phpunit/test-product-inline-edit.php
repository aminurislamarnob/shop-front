<?php
/**
 * Tests for the inline cell edit AJAX endpoint (issue #186).
 *
 * @package StoreSuite\Tests
 */

/**
 * @covers \PluginizeLab\StoreSuite\Product\ProductInlineEdit
 */
class Test_Product_Inline_Edit extends WP_Ajax_UnitTestCase {

	const ACTION = 'storesuite_product_inline_cell_edit';

	/**
	 * Create a simple product.
	 *
	 * @param array $args Optional overrides.
	 * @return WC_Product_Simple
	 */
	private function create_simple_product( $args = array() ) {
		$product = new WC_Product_Simple();
		$product->set_name( isset( $args['name'] ) ? $args['name'] : 'Inline Test Product' );
		$product->set_regular_price( isset( $args['regular_price'] ) ? $args['regular_price'] : '20' );

		if ( isset( $args['sku'] ) ) {
			$product->set_sku( $args['sku'] );
		}
		if ( ! empty( $args['manage_stock'] ) ) {
			$product->set_manage_stock( true );
			$product->set_stock_quantity( isset( $args['stock'] ) ? $args['stock'] : 5 );
		}

		$product->save();

		return $product;
	}

	/**
	 * Dispatch the inline edit action and return the decoded JSON response.
	 *
	 * @param array $post_fields Request fields (product_id, field, value, ...).
	 * @return array
	 */
	private function do_inline_edit( $post_fields ) {
		$_POST = array_merge(
			array(
				'security' => wp_create_nonce( self::ACTION ),
			),
			$post_fields
		);

		try {
			$this->_handleAjax( self::ACTION );
		} catch ( WPAjaxDieContinueException $e ) {
			// wp_send_json_* ends with an empty wp_die(); this is the expected control flow.
			unset( $e );
		}

		// Strip any debug notices printed before the JSON payload.
		$raw   = $this->_last_response;
		$start = strpos( $raw, '{' );
		if ( false !== $start ) {
			$raw = substr( $raw, $start );
		}

		return json_decode( $raw, true );
	}

	public function test_price_edit_updates_regular_and_sale_price() {
		$this->_setRole( 'administrator' );
		$product = $this->create_simple_product();

		$response = $this->do_inline_edit(
			array(
				'product_id'    => $product->get_id(),
				'field'         => 'price',
				'regular_price' => '30',
				'sale_price'    => '25',
			)
		);

		$this->assertTrue( $response['success'] );
		$this->assertStringContainsString( 'product-row-' . $product->get_id(), $response['data']['row'] );

		$updated = wc_get_product( $product->get_id() );
		$this->assertSame( '30', $updated->get_regular_price() );
		$this->assertSame( '25', $updated->get_sale_price() );
	}

	public function test_sale_price_must_be_lower_than_regular_price() {
		$this->_setRole( 'administrator' );
		$product = $this->create_simple_product();

		$response = $this->do_inline_edit(
			array(
				'product_id'    => $product->get_id(),
				'field'         => 'price',
				'regular_price' => '30',
				'sale_price'    => '30',
			)
		);

		$this->assertFalse( $response['success'] );

		$updated = wc_get_product( $product->get_id() );
		$this->assertSame( '20', $updated->get_regular_price(), 'A rejected edit must not persist anything.' );
	}

	public function test_invalid_price_is_rejected() {
		$this->_setRole( 'administrator' );
		$product = $this->create_simple_product();

		$response = $this->do_inline_edit(
			array(
				'product_id'    => $product->get_id(),
				'field'         => 'price',
				'regular_price' => 'not-a-number',
				'sale_price'    => '',
			)
		);

		$this->assertFalse( $response['success'] );
	}

	public function test_stock_quantity_edit_when_stock_is_managed() {
		$this->_setRole( 'administrator' );
		$product = $this->create_simple_product(
			array(
				'manage_stock' => true,
				'stock'        => 5,
			)
		);

		$response = $this->do_inline_edit(
			array(
				'product_id' => $product->get_id(),
				'field'      => 'stock_quantity',
				'value'      => '12',
			)
		);

		$this->assertTrue( $response['success'] );
		$this->assertSame( 12, wc_get_product( $product->get_id() )->get_stock_quantity() );
	}

	public function test_stock_quantity_edit_rejected_when_stock_not_managed() {
		$this->_setRole( 'administrator' );
		$product = $this->create_simple_product();

		$response = $this->do_inline_edit(
			array(
				'product_id' => $product->get_id(),
				'field'      => 'stock_quantity',
				'value'      => '12',
			)
		);

		$this->assertFalse( $response['success'] );
	}

	public function test_status_edit_accepts_whitelisted_status_only() {
		$this->_setRole( 'administrator' );
		$product = $this->create_simple_product();

		$response = $this->do_inline_edit(
			array(
				'product_id' => $product->get_id(),
				'field'      => 'status',
				'value'      => 'draft',
			)
		);
		$this->assertTrue( $response['success'] );
		$this->assertSame( 'draft', get_post_status( $product->get_id() ) );

		$response = $this->do_inline_edit(
			array(
				'product_id' => $product->get_id(),
				'field'      => 'status',
				'value'      => 'private',
			)
		);
		$this->assertFalse( $response['success'], 'Statuses outside publish/draft/pending must be rejected.' );
	}

	public function test_duplicate_sku_is_rejected() {
		$this->_setRole( 'administrator' );
		$this->create_simple_product(
			array(
				'name' => 'Product A',
				'sku'  => 'DUP-SKU',
			)
		);
		$product_b = $this->create_simple_product(
			array(
				'name' => 'Product B',
				'sku'  => 'OTHER-SKU',
			)
		);

		$response = $this->do_inline_edit(
			array(
				'product_id' => $product_b->get_id(),
				'field'      => 'sku',
				'value'      => 'DUP-SKU',
			)
		);

		$this->assertFalse( $response['success'] );
		$this->assertSame( 'OTHER-SKU', wc_get_product( $product_b->get_id() )->get_sku() );
	}

	public function test_stock_status_edit_only_when_stock_not_managed() {
		$this->_setRole( 'administrator' );
		$unmanaged = $this->create_simple_product();

		$response = $this->do_inline_edit(
			array(
				'product_id' => $unmanaged->get_id(),
				'field'      => 'stock_status',
				'value'      => 'outofstock',
			)
		);
		$this->assertTrue( $response['success'] );
		$this->assertSame( 'outofstock', wc_get_product( $unmanaged->get_id() )->get_stock_status() );

		$managed = $this->create_simple_product(
			array(
				'manage_stock' => true,
				'stock'        => 3,
			)
		);

		$response = $this->do_inline_edit(
			array(
				'product_id' => $managed->get_id(),
				'field'      => 'stock_status',
				'value'      => 'outofstock',
			)
		);
		$this->assertFalse( $response['success'], 'Stock status is derived from quantity on managed products.' );
	}

	public function test_user_without_capability_is_rejected() {
		$this->_setRole( 'administrator' );
		$product = $this->create_simple_product();

		$this->_setRole( 'subscriber' );
		$response = $this->do_inline_edit(
			array(
				'product_id' => $product->get_id(),
				'field'      => 'sku',
				'value'      => 'HACK',
			)
		);

		$this->assertFalse( $response['success'] );
		$this->assertSame( '', wc_get_product( $product->get_id() )->get_sku() );
	}

	public function test_inventory_context_returns_inventory_row_markup() {
		$this->_setRole( 'administrator' );
		$product = $this->create_simple_product();

		$response = $this->do_inline_edit(
			array(
				'product_id' => $product->get_id(),
				'field'      => 'sku',
				'value'      => 'INV-1',
				'context'    => 'inventory',
			)
		);

		$this->assertTrue( $response['success'] );
		$this->assertStringContainsString( 'single-inventory-item', $response['data']['row'] );
	}
}
