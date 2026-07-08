<?php

namespace PluginizeLab\StoreSuite\Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin order controller class
 */
class ProductHooks {
	/**
	 * The constructor.
	 */
	public function __construct() {
		add_filter( 'storesuite_product_types', array( $this, 'set_product_types' ), 10 );
		add_filter( 'storesuite_product_statuses', array( $this, 'set_product_statuses' ), 10 );
		// Priority 5 so the link renders before the AI bundle launcher (10),
		// keeping it next to the page title.
		add_action( 'storesuite_dashboard_title_after', array( $this, 'render_view_product_link' ), 5 );
	}

	/**
	 * Render a "view product" link beside the Edit Product page title.
	 *
	 * Hooked on `storesuite_dashboard_title_after`; only renders on the Edit
	 * Product page and opens the live product page in a new tab.
	 *
	 * @return void
	 */
	public function render_view_product_link() {
		$query = pluginizelab_storesuite()->get_storesuite_query();
		if ( ! $query || 'edit-product' !== $query->get_current_endpoint() ) {
			return;
		}

		$product_id = absint( get_query_var( 'edit-product' ) );
		$permalink  = $product_id ? get_permalink( $product_id ) : '';
		if ( ! $permalink ) {
			return;
		}
		?>
		<a class="storesuite-title-view-link" href="<?php echo esc_url( $permalink ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'View product', 'storesuite' ); ?>" title="<?php esc_attr_e( 'View product', 'storesuite' ); ?>">
			<svg class="storesuite-title-view-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
				<path d="M18.498,9.474c-.549,.059-.947,.552-.888,1.101,.109,1.016,.164,2.038,.164,3.037,0,3.036-.522,5.54-.781,6.595-1.095,.258-3.707,.791-6.604,.791s-5.511-.533-6.606-.791c-.259-1.048-.779-3.537-.779-6.594,0-3.036,.522-5.54,.781-6.595,1.095-.258,3.707-.791,6.604-.791,1.008,0,2.073,.064,3.163,.191,.544,.069,1.045-.329,1.109-.877,.064-.548-.329-1.045-.877-1.109-1.167-.136-2.309-.205-3.395-.205-4.078,0-7.545,.961-7.691,1.002-.329,.093-.588,.347-.687,.673-.042,.136-1.008,3.384-1.008,7.71,0,4.363,.967,7.578,1.008,7.713,.1,.325,.358,.578,.686,.67,.145,.041,3.604,1.002,7.691,1.002s7.545-.961,7.691-1.002c.329-.093,.588-.347,.687-.673,.042-.136,1.008-3.383,1.008-7.71,0-1.07-.059-2.163-.176-3.25-.059-.549-.552-.95-1.101-.888Z"/>
				<path d="M22.072,1.848c-.098-.087-.212-.154-.334-.197-.794-.316-3.679-1.256-7.464-.028-.525,.17-.813,.734-.643,1.26,.171,.525,.734,.815,1.26,.643,1.888-.612,3.477-.587,4.585-.414L9.682,12.904c-.391,.391-.38,1.013,0,1.414,.547,.576,1.219,.195,1.414,0L20.893,4.521c.162,1.077,.191,2.675-.419,4.593-.167,.526,.159,1.303,.954,1.304,.424,0,.817-.272,.953-.697,1.37-4.306,.064-7.541-.308-7.873Z"/>
			</svg>
		</a>
		<?php
	}

	/**
	 * Set default product types
	 *
	 * @param array $product_types
	 *
	 * @return array
	 */
	public function set_product_types( $product_types ) {
		$product_types = array(
			'simple'   => __( 'Simple', 'storesuite' ),
			'variable' => __( 'Variable', 'storesuite' ),
			'external' => __( 'External/Affiliate product', 'storesuite' ),
			'grouped'  => __( 'Group Product', 'storesuite' ),
		);
		return $product_types;
	}

	/**
	 * Set default product statuses
	 *
	 * @param array $product_statuses
	 *
	 * @return array
	 */
	public function set_product_statuses() {
		$product_statuses = array(
			'publish' => __( 'Publish', 'storesuite' ),
			'draft'   => __( 'Draft', 'storesuite' ),
			'pending' => __( 'Pending Review', 'storesuite' ),
		);
		return $product_statuses;
	}
}
