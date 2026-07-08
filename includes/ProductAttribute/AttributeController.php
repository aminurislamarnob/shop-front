<?php
/**
 * Product attributes & terms controller
 *
 * @package StoreSuite
 */

namespace PluginizeLab\StoreSuite\ProductAttribute;

use Exception;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles CRUD for global product attributes and their terms.
 */
class AttributeController {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_storesuite_add_product_attribute', array( $this, 'handle_add_attribute' ) );
		add_action( 'wp_ajax_storesuite_edit_product_attribute', array( $this, 'handle_edit_attribute' ) );
		add_action( 'wp_ajax_storesuite_delete_product_attribute', array( $this, 'handle_delete_attribute' ) );

		add_action( 'wp_ajax_storesuite_add_attribute_term', array( $this, 'handle_add_term' ) );
		add_action( 'wp_ajax_storesuite_edit_attribute_term', array( $this, 'handle_edit_term' ) );
		add_action( 'wp_ajax_storesuite_delete_attribute_term', array( $this, 'handle_delete_term' ) );
		add_action( 'storesuite_attributes_toolbar_add_button', array( $this, 'render_toolbar_add_button' ) );
		add_action( 'storesuite_dashboard_title_after', array( $this, 'render_title_add_button' ) );
	}

	public function render_toolbar_add_button() {
		$this->render_add_button(
			storesuite_get_navigation_url( 'add-new-attribute' ),
			__( 'Add Attribute', 'storesuite' )
		);
	}

	public function render_title_add_button() {
		$query = pluginizelab_storesuite()->get_storesuite_query();
		if ( ! $query || 'attributes' !== $query->get_current_endpoint() ) {
			return;
		}
		$this->render_add_button(
			storesuite_get_navigation_url( 'add-new-attribute' ),
			__( 'Add Attribute', 'storesuite' ),
			'storesuite-title-action'
		);
	}

	private function render_add_button( $url, $label, $extra_class = '' ) {
		$class = trim( 'my-storesuite-button ' . $extra_class );
		?>
		<a href="<?php echo esc_url( $url ); ?>" class="<?php echo esc_attr( $class ); ?>">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false">
				<path d="M23,11H13V1a1,1,0,0,0-1-1h0a1,1,0,0,0-1,1V11H1a1,1,0,0,0-1,1H0a1,1,0,0,0,1,1H11V23a1,1,0,0,0,1,1h0a1,1,0,0,0,1-1V13H23a1,1,0,0,0,1-1h0A1,1,0,0,0,23,11Z"/>
			</svg>
			<?php echo esc_html( $label ); ?>
		</a>
		<?php
	}

	/**
	 * Handle add global product attribute.
	 */
	public function handle_add_attribute() {
		// Verify the nonce.
		if ( ! isset( $_POST['storesuite_add_product_attribute_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['storesuite_add_product_attribute_nonce'] ) ), '_storesuite_add_product_attribute_' ) ) {
			wp_send_json_error( array( 'error' => __( 'Nonce verification failed.', 'storesuite' ) ) );
		}

		// Check user permissions.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to perform this action.', 'storesuite' ) ) );
		}

		$attribute_label = isset( $_POST['attribute_label'] ) ? wc_clean( wp_unslash( $_POST['attribute_label'] ) ) : '';
		$attribute_name  = isset( $_POST['attribute_name'] ) ? wc_sanitize_taxonomy_name( wp_unslash( $_POST['attribute_name'] ) ) : '';
		$type            = isset( $_POST['attribute_type'] ) ? wc_clean( wp_unslash( $_POST['attribute_type'] ) ) : 'select';
		$order_by        = isset( $_POST['attribute_orderby'] ) ? wc_clean( wp_unslash( $_POST['attribute_orderby'] ) ) : 'menu_order';
		$public          = isset( $_POST['attribute_public'] ) ? 1 : 0;

		if ( '' === $attribute_label ) {
			wp_send_json_error( array( 'error' => __( 'Attribute name is required.', 'storesuite' ) ) );
		}

		if ( '' === $attribute_name ) {
			$attribute_name = wc_sanitize_taxonomy_name( $attribute_label );
		}

		// Check if attribute already exists.
		$taxonomy_name = wc_attribute_taxonomy_name( $attribute_name );
		if ( wc_attribute_taxonomy_id_by_name( $taxonomy_name ) ) {
			wp_send_json_error(
				array(
					'error' => __( 'An attribute with this slug already exists. Please choose a different slug.', 'storesuite' ),
				)
			);
		}

		$args = array(
			'name'         => $attribute_label,
			'slug'         => $attribute_name,
			'type'         => $type,
			'order_by'     => $order_by,
			'has_archives' => (bool) $public,
		);

		$attribute_id = wc_create_attribute( $args );

		if ( is_wp_error( $attribute_id ) ) {
			wp_send_json_error( array( 'error' => $attribute_id->get_error_message() ) );
		}

		wp_send_json_success(
			array(
				'message' => __( 'Attribute successfully created.', 'storesuite' ),
			)
		);
	}

	/**
	 * Handle edit global product attribute.
	 */
	public function handle_edit_attribute() {
		// Verify the nonce.
		if ( ! isset( $_POST['storesuite_edit_product_attribute_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['storesuite_edit_product_attribute_nonce'] ) ), '_storesuite_edit_product_attribute_' ) ) {
			wp_send_json_error( array( 'error' => __( 'Nonce verification failed.', 'storesuite' ) ) );
		}

		// Check user permissions.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to perform this action.', 'storesuite' ) ) );
		}

		$attribute_id    = isset( $_POST['attribute_id'] ) ? absint( $_POST['attribute_id'] ) : 0;
		$attribute_label = isset( $_POST['attribute_label'] ) ? wc_clean( wp_unslash( $_POST['attribute_label'] ) ) : '';
		$attribute_name  = isset( $_POST['attribute_name'] ) ? wc_sanitize_taxonomy_name( wp_unslash( $_POST['attribute_name'] ) ) : '';
		$type            = isset( $_POST['attribute_type'] ) ? wc_clean( wp_unslash( $_POST['attribute_type'] ) ) : 'select';
		$order_by        = isset( $_POST['attribute_orderby'] ) ? wc_clean( wp_unslash( $_POST['attribute_orderby'] ) ) : 'menu_order';
		$public          = isset( $_POST['attribute_public'] ) ? 1 : 0;

		if ( ! $attribute_id ) {
			wp_send_json_error( array( 'error' => __( 'Invalid attribute ID.', 'storesuite' ) ) );
		}

		if ( '' === $attribute_label ) {
			wp_send_json_error( array( 'error' => __( 'Attribute name is required.', 'storesuite' ) ) );
		}

		$attribute = wc_get_attribute( $attribute_id );

		if ( ! $attribute ) {
			wp_send_json_error( array( 'error' => __( 'Attribute not found.', 'storesuite' ) ) );
		}

		$args = array(
			'name'         => $attribute_label,
			'slug'         => $attribute_name,
			'type'         => $type,
			'order_by'     => $order_by,
			'has_archives' => (bool) $public,
		);

		$result = wc_update_attribute( $attribute_id, $args );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'error' => $result->get_error_message() ) );
		}

		wp_send_json_success(
			array(
				'message' => __( 'Attribute successfully updated.', 'storesuite' ),
			)
		);
	}

	/**
	 * Handle delete global product attribute.
	 */
	public function handle_delete_attribute() {
		// Verify the nonce.
		if ( ! isset( $_POST['storesuite_delete_product_attribute_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['storesuite_delete_product_attribute_nonce'] ) ), '_storesuite_delete_nonce_' ) ) {
			wp_send_json_error( array( 'error' => __( 'Nonce verification failed.', 'storesuite' ) ) );
		}

		// Check user permissions.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to perform this action.', 'storesuite' ) ) );
		}

		$attribute_id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

		if ( ! $attribute_id ) {
			wp_send_json_error( array( 'error' => __( 'Invalid attribute ID.', 'storesuite' ) ) );
		}

		$deleted = wc_delete_attribute( $attribute_id );

		if ( ! $deleted ) {
			wp_send_json_error( array( 'error' => __( 'Failed to delete attribute.', 'storesuite' ) ) );
		}

		wp_send_json_success(
			array(
				'message' => __( 'Attribute successfully deleted.', 'storesuite' ),
			)
		);
	}

	/**
	 * Handle add term for a given attribute taxonomy.
	 */
	public function handle_add_term() {
		// Verify the nonce.
		if ( ! isset( $_POST['storesuite_add_attribute_term_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['storesuite_add_attribute_term_nonce'] ) ), '_storesuite_add_attribute_term_' ) ) {
			wp_send_json_error( array( 'error' => __( 'Nonce verification failed.', 'storesuite' ) ) );
		}

		// Check user permissions.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to perform this action.', 'storesuite' ) ) );
		}

		$taxonomy = isset( $_POST['taxonomy'] ) ? sanitize_text_field( wp_unslash( $_POST['taxonomy'] ) ) : '';
		$name     = isset( $_POST['term_name'] ) ? sanitize_text_field( wp_unslash( $_POST['term_name'] ) ) : '';
		$slug     = isset( $_POST['term_slug'] ) ? sanitize_title( wp_unslash( $_POST['term_slug'] ) ) : '';
		$desc     = isset( $_POST['term_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['term_description'] ) ) : '';

		if ( '' === $taxonomy || '' === $name ) {
			wp_send_json_error( array( 'error' => __( 'Name and taxonomy are required.', 'storesuite' ) ) );
		}

		if ( '' === $slug ) {
			$slug = sanitize_title( $name );
		}

		if ( term_exists( $slug, $taxonomy ) ) {
			wp_send_json_error( array( 'error' => __( 'A term with that slug already exists.', 'storesuite' ) ) );
		}

		$result = wp_insert_term(
			$name,
			$taxonomy,
			array(
				'slug'        => $slug,
				'description' => $desc,
			)
		);

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'error' => $result->get_error_message() ) );
		}

		$term = get_term( $result['term_id'], $taxonomy );

		wp_send_json_success(
			array(
				'message'  => __( 'Term successfully created.', 'storesuite' ),
				'term_id'  => $term->term_id,
				'taxonomy' => $taxonomy,
			)
		);
	}

	/**
	 * Handle edit attribute term.
	 */
	public function handle_edit_term() {
		// Verify the nonce.
		if ( ! isset( $_POST['storesuite_edit_attribute_term_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['storesuite_edit_attribute_term_nonce'] ) ), '_storesuite_edit_attribute_term_' ) ) {
			wp_send_json_error( array( 'error' => __( 'Nonce verification failed.', 'storesuite' ) ) );
		}

		// Check user permissions.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to perform this action.', 'storesuite' ) ) );
		}

		$taxonomy = isset( $_POST['taxonomy'] ) ? sanitize_text_field( wp_unslash( $_POST['taxonomy'] ) ) : '';
		$term_id  = isset( $_POST['term_id'] ) ? absint( $_POST['term_id'] ) : 0;
		$name     = isset( $_POST['term_name'] ) ? sanitize_text_field( wp_unslash( $_POST['term_name'] ) ) : '';
		$slug     = isset( $_POST['term_slug'] ) ? sanitize_title( wp_unslash( $_POST['term_slug'] ) ) : '';
		$desc     = isset( $_POST['term_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['term_description'] ) ) : '';

		if ( ! $term_id || '' === $taxonomy ) {
			wp_send_json_error( array( 'error' => __( 'Invalid term or taxonomy.', 'storesuite' ) ) );
		}

		if ( '' === $name ) {
			wp_send_json_error( array( 'error' => __( 'Term name is required.', 'storesuite' ) ) );
		}

		if ( '' === $slug ) {
			$slug = sanitize_title( $name );
		}

		// Ensure slug is unique within taxonomy.
		$existing = get_term_by( 'slug', $slug, $taxonomy );
		if ( $existing && (int) $existing->term_id !== $term_id ) {
			wp_send_json_error( array( 'error' => __( 'A term with that slug already exists.', 'storesuite' ) ) );
		}

		$term = wp_update_term(
			$term_id,
			$taxonomy,
			array(
				'name'        => $name,
				'slug'        => $slug,
				'description' => $desc,
			)
		);

		if ( is_wp_error( $term ) ) {
			wp_send_json_error( array( 'error' => $term->get_error_message() ) );
		}

		wp_send_json_success(
			array(
				'message' => __( 'Term successfully updated.', 'storesuite' ),
			)
		);
	}

	/**
	 * Handle delete attribute term.
	 */
	public function handle_delete_term() {
		// Verify the nonce.
		if ( ! isset( $_POST['storesuite_delete_attribute_term_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['storesuite_delete_attribute_term_nonce'] ) ), '_storesuite_delete_nonce_' ) ) {
			wp_send_json_error( array( 'error' => __( 'Nonce verification failed.', 'storesuite' ) ) );
		}

		// Check user permissions.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to perform this action.', 'storesuite' ) ) );
		}

		$taxonomy = isset( $_POST['taxonomy'] ) ? sanitize_text_field( wp_unslash( $_POST['taxonomy'] ) ) : '';
		$term_id  = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

		if ( ! $term_id || '' === $taxonomy ) {
			wp_send_json_error( array( 'error' => __( 'Invalid term or taxonomy.', 'storesuite' ) ) );
		}

		$result = wp_delete_term( $term_id, $taxonomy );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'error' => $result->get_error_message() ) );
		} elseif ( false === $result ) {
			wp_send_json_error( array( 'error' => __( 'Failed to delete term.', 'storesuite' ) ) );
		}

		wp_send_json_success(
			array(
				'message' => __( 'Term successfully deleted.', 'storesuite' ),
			)
		);
	}
}
