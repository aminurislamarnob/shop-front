<?php

namespace PluginizeLab\StoreSuite\ProductCategory;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin product categories controller class
 */
class CategoryController {
	/**
	 * The constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_storesuite_add_product_category', array( $this, 'handle_add_category' ) );
		add_action( 'wp_ajax_storesuite_edit_product_category', array( $this, 'handle_edit_category' ) );
		add_action( 'wp_ajax_storesuite_delete_product_category', array( $this, 'handle_delete_category' ) );
		add_action( 'storesuite_categories_toolbar_add_button', array( $this, 'render_toolbar_add_button' ) );
		add_action( 'storesuite_dashboard_title_after', array( $this, 'render_title_add_button' ) );
	}

	public function render_toolbar_add_button() {
		$this->render_add_button(
			storesuite_get_navigation_url( 'add-new-category' ),
			__( 'Add Category', 'storesuite' )
		);
	}

	public function render_title_add_button() {
		$query = pluginizelab_storesuite()->get_storesuite_query();
		if ( ! $query || 'categories' !== $query->get_current_endpoint() ) {
			return;
		}
		$this->render_add_button(
			storesuite_get_navigation_url( 'add-new-category' ),
			__( 'Add Category', 'storesuite' ),
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
	 * Handle the AJAX request for adding a new product category.
	 */
	public function handle_add_category() {

		// Verify the nonce.
		if ( ! isset( $_POST['storesuite_add_product_category_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['storesuite_add_product_category_nonce'] ) ), '_storesuite_add_product_category_' ) ) {
			wp_send_json_error( array( 'error' => __( 'Nonce verification failed', 'storesuite' ) ) );
		}

		// Check user permissions.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to perform this action.', 'storesuite' ) ) );
		}

		// Validate inputs.
		$category_name   = isset( $_POST['product_category_name'] ) ? sanitize_text_field( wp_unslash( $_POST['product_category_name'] ) ) : '';
		$parent_category = isset( $_POST['product_parent_category'] ) ? sanitize_text_field( wp_unslash( $_POST['product_parent_category'] ) ) : '';
		$description     = isset( $_POST['product_category_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['product_category_description'] ) ) : '';
		$thumbnail_id    = isset( $_POST['product_category_thumbnail_id'] ) ? absint( $_POST['product_category_thumbnail_id'] ) : 0;
		$display_type    = isset( $_POST['display_type'] ) ? sanitize_text_field( wp_unslash( $_POST['display_type'] ) ) : '';
		$category_slug   = isset( $_POST['product_category_slug'] ) ? sanitize_title( wp_unslash( $_POST['product_category_slug'] ) ) : '';

		if ( empty( $category_name ) ) {
			wp_send_json_error( array( 'error' => __( 'Category Name is required', 'storesuite' ) ) );
		}

		// Generate slug from name if not provided.
		if ( empty( $category_slug ) ) {
			$category_slug = sanitize_title( $category_name );
		}

		// Check if slug already exists.
		$existing_term = get_term_by( 'slug', $category_slug, 'product_cat' );
		if ( $existing_term ) {
			wp_send_json_error( array( 'error' => __( 'Category slug already exists. Please choose a different slug.', 'storesuite' ) ) );
		}

		// Check for parent category.
		$parent_term = $parent_category ? get_term_by( 'slug', $parent_category, 'product_cat' ) : null;
		$parent_id   = $parent_term ? $parent_term->term_id : 0;

		// Create a new category.
		$new_category = wp_insert_term(
			$category_name,
			'product_cat',
			array(
				'slug'        => $category_slug,
				'parent'      => $parent_id,
				'description' => $description,
			)
		);

		if ( is_wp_error( $new_category ) ) {
			wp_send_json_error( array( 'error' => $new_category->get_error_message() ) );
		}

		// Save the category thumbnail if provided.
		if ( $thumbnail_id > 0 ) {
			update_term_meta( $new_category['term_id'], 'thumbnail_id', $thumbnail_id );
		}

		// Save the display type if provided.
		if ( ! empty( $display_type ) ) {
			update_term_meta( $new_category['term_id'], 'display_type', $display_type );
		}

		do_action( 'storesuite_product_category_created', $new_category );

		wp_send_json_success( array( 'message' => __( 'Category successfully created', 'storesuite' ) ) );
	}

	/**
	 * Handle the AJAX request for editing an existing product category.
	 */
	public function handle_edit_category() {

		// Verify the nonce.
		if ( ! isset( $_POST['storesuite_edit_product_category_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['storesuite_edit_product_category_nonce'] ) ), '_storesuite_edit_product_category_' ) ) {
			wp_send_json_error( array( 'error' => __( 'Nonce verification failed', 'storesuite' ) ) );
		}

		// Check user permissions.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to perform this action.', 'storesuite' ) ) );
		}

		// Validate inputs.
		$category_id     = isset( $_POST['category_id'] ) ? absint( $_POST['category_id'] ) : 0;
		$category_name   = isset( $_POST['product_category_name'] ) ? sanitize_text_field( wp_unslash( $_POST['product_category_name'] ) ) : '';
		$parent_category = isset( $_POST['product_parent_category'] ) ? sanitize_text_field( wp_unslash( $_POST['product_parent_category'] ) ) : '';
		$description     = isset( $_POST['product_category_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['product_category_description'] ) ) : '';
		$thumbnail_id    = isset( $_POST['product_category_thumbnail_id'] ) ? absint( $_POST['product_category_thumbnail_id'] ) : 0;
		$display_type    = isset( $_POST['display_type'] ) ? sanitize_text_field( wp_unslash( $_POST['display_type'] ) ) : '';
		$category_slug   = isset( $_POST['product_category_slug'] ) ? sanitize_title( wp_unslash( $_POST['product_category_slug'] ) ) : '';

		if ( empty( $category_id ) ) {
			wp_send_json_error( array( 'error' => __( 'Category ID is required', 'storesuite' ) ) );
		}

		if ( empty( $category_name ) ) {
			wp_send_json_error( array( 'error' => __( 'Category Name is required', 'storesuite' ) ) );
		}

		// Get current category.
		$current_category = get_term( $category_id, 'product_cat' );
		if ( ! $current_category || is_wp_error( $current_category ) ) {
			wp_send_json_error( array( 'error' => __( 'Category not found', 'storesuite' ) ) );
		}

		// Generate slug from name if not provided.
		if ( empty( $category_slug ) ) {
			$category_slug = sanitize_title( $category_name );
		}

		// Check if slug already exists (but allow it if it's the current category's slug).
		if ( $category_slug !== $current_category->slug ) {
			$existing_term = get_term_by( 'slug', $category_slug, 'product_cat' );
			if ( $existing_term && $existing_term->term_id !== $category_id ) {
				wp_send_json_error( array( 'error' => __( 'Category slug already exists. Please choose a different slug.', 'storesuite' ) ) );
			}
		}

		// Check for parent category.
		$parent_term = $parent_category ? get_term_by( 'slug', $parent_category, 'product_cat' ) : null;
		$parent_id   = $parent_term ? $parent_term->term_id : 0;

		// Update the category.
		$updated_category = wp_update_term(
			$category_id,
			'product_cat',
			array(
				'name'        => $category_name,
				'slug'        => $category_slug,
				'parent'      => $parent_id,
				'description' => $description,
			)
		);

		if ( is_wp_error( $updated_category ) ) {
			wp_send_json_error( array( 'error' => $updated_category->get_error_message() ) );
		}

		// Update the category thumbnail.
		if ( $thumbnail_id > 0 ) {
			update_term_meta( $category_id, 'thumbnail_id', $thumbnail_id );
		} else {
			delete_term_meta( $category_id, 'thumbnail_id' );
		}

		// Update the display type.
		if ( ! empty( $display_type ) ) {
			update_term_meta( $category_id, 'display_type', $display_type );
		} else {
			delete_term_meta( $category_id, 'display_type' );
		}

		do_action( 'storesuite_product_category_updated', $updated_category );

		wp_send_json_success( array( 'message' => __( 'Category successfully updated', 'storesuite' ) ) );
	}

	/**
	 * Handle the AJAX request for deleting an existing product category.
	 */
	public function handle_delete_category() {
		// Verify the nonce.
		if ( ! isset( $_POST['storesuite_delete_product_category_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['storesuite_delete_product_category_nonce'] ) ), '_storesuite_delete_nonce_' ) ) {
			wp_send_json_error( array( 'error' => __( 'Nonce verification failed', 'storesuite' ) ) );
		}

		// Check user permissions.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to perform this action.', 'storesuite' ) ) );
		}

		// Validate category ID.
		$category_id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		if ( ! $category_id || ! term_exists( $category_id, 'product_cat' ) ) {
			wp_send_json_error( array( 'error' => __( 'Invalid category ID', 'storesuite' ) ) );
		}

		// Attempt to delete the category.
		$result = wp_delete_term( $category_id, 'product_cat' );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'error' => $result->get_error_message() ) );
		} elseif ( $result === false ) {
			wp_send_json_error( array( 'error' => __( 'Failed to delete category', 'storesuite' ) ) );
		}

		do_action( 'storesuite_product_category_deleted', $category_id );

		wp_send_json_success( array( 'message' => __( 'Category successfully deleted', 'storesuite' ) ) );
	}
}
