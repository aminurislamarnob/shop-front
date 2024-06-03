<?php
namespace PluginizeLab\ShopFront;

class Rewrites {
	/**
	 * Query vars to add to wp.
	 *
	 * @var array
	 */
	public $query_vars = array();

	/**
	 * Store base slug
	 *
	 * @var string
	 */
	public $store_front_base = '';

	/**
	 * Hook into the functions
	 */
	public function __construct() {
		$this->store_front_base = 'my-shop-dashboard'; // need to set dynamically from options table.
		add_action( 'init', array( $this, 'add_endpoints' ) );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );
		add_filter( 'woocommerce_get_query_vars', array( $this, 'resolve_wocommerce_my_acc_query_conflict' ) );
		$this->init_query_vars();
	}


	/**
	 * Init query vars by loading options.
	 */
	public function init_query_vars() {
		// Query vars to add to WP.
		$this->query_vars = apply_filters(
			'my_shop_front_query_var_filter',
			array(
				'products'         => get_option( 'msf_myshop_products_endpoint', 'products' ),
				'add-new-product'  => get_option( 'msf_myshop_new_product_endpoint', 'add-new-product' ),
				'edit-product'     => get_option( 'msf_myshop_edit_product_endpoint', 'edit-product' ),
				'orders'           => get_option( 'msf_myshop_orders_endpoint', 'orders' ),
				'order-details'    => get_option( 'msf_myshop_order_details_endpoint', 'order-details' ),
				'categories'       => get_option( 'msf_myshop_categories_endpoint', 'categories' ),
				'add-new-category' => get_option( 'msf_myshop_new_category_endpoint', 'add-new-category' ),
				'edit-category'    => get_option( 'msf_myshop_edit_category_endpoint', 'edit-category' ),
			)
		);
	}

	/**
	 * Resolve query var conflicts with WooCommerce My Account
	 *
	 * @param array $query_vars
	 *
	 * @return array
	 */
	public function resolve_wocommerce_my_acc_query_conflict( $query_vars ) {
		global $post;

		$dashboard_id = apply_filters( 'msf_get_dashboard_page_id', Helper::msfc_get_page_id( 'myshopdashboard' ) );

		if ( ! empty( $post->ID ) && apply_filters( 'msf_get_current_page_id', $post->ID ) === absint( $dashboard_id ) ) {
			unset( $query_vars['orders'] );
		}

		return $query_vars;
	}

	/**
	 * Add endpoints for query vars.
	 */
	public function add_endpoints() {
		$mask = EP_PAGES;

		foreach ( $this->query_vars as $key => $var ) {
			if ( ! empty( $var ) ) {
				add_rewrite_endpoint( $var, $mask );
			}
		}

		// Add rewrite rule for product list pagination.
		add_rewrite_rule(
			$this->store_front_base . '/products/page/([^/]+)/?$',
			'index.php?pagename=' . $this->store_front_base . '&products=1&paged=$matches[1]',
			'top'
		);
	}

	/**
	 * Add query vars.
	 *
	 * @param array $vars Query vars.
	 * @return array
	 */
	public function add_query_vars( $vars ) {
		foreach ( $this->query_vars as $key => $var ) {
			$vars[] = $key;
		}
		return $vars;
	}

	/**
	 * Get page title for an endpoint.
	 *
	 * @param string $endpoint Endpoint key.
	 * @param string $action Optional action or variation within the endpoint.
	 * @return string The page title.
	 */
	public function get_endpoint_title( $endpoint, $action = '' ) {
		global $wp;

		switch ( $endpoint ) {
			case 'products':
				$title = __( 'All Products', 'shop-front' );
				break;
			case 'add-new-product':
				$title = __( 'Add New Product', 'shop-front' );
				break;
			case 'edit-product':
				$title = __( 'Edit Product', 'shop-front' );
				break;
			case 'orders':
				if ( ! empty( $wp->query_vars['orders'] ) ) {
					/* translators: %s: page */
					$title = sprintf( __( 'Orders (page %d)', 'shop-front' ), intval( $wp->query_vars['orders'] ) );
				} else {
					$title = __( 'Orders', 'shop-front' );
				}
				break;
			case 'order-details':
				$order = wc_get_order( $wp->query_vars['view-order'] );
				/* translators: %s: order number */
				$title = ( $order ) ? sprintf( __( 'Order #%s', 'shop-front' ), $order->get_order_number() ) : '';
				break;
			case 'categories':
				$title = __( 'Product Categories', 'shop-front' );
				break;
			case 'add-new-category':
				$title = __( 'Add New Category', 'shop-front' );
				break;
			case 'edit-category':
				$title = __( 'Edit Category', 'shop-front' );
				break;
			default:
				$title = '';
				break;
		}

		/**
		 * Filters the page title used for my-account endpoints.
		 *
		 * @param string $title Default title.
		 * @param string $endpoint Endpoint key.
		 * @param string $action Optional action or variation within the endpoint.
		 */
		return apply_filters( 'msf_endpoint_' . $endpoint . '_title', $title, $endpoint, $action );
	}

	/**
	 * Get query current active query var.
	 *
	 * @return string
	 */
	public function get_current_endpoint() {
		global $wp;

		foreach ( $this->query_vars as $key => $value ) {
			if ( isset( $wp->query_vars[ $key ] ) ) {
				return $key;
			}
		}
		return '';
	}
}
