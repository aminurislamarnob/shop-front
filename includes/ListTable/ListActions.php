<?php

namespace PluginizeLab\StoreSuite\ListTable;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shared bulk delete and quick edit handlers for the taxonomy/attribute list pages
 * (categories, tags, brands, attributes and attribute terms).
 *
 * Mirrors the product list bulk action and quick edit experience, but for terms
 * and global attributes.
 */
class ListActions {

	/**
	 * The constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_storesuite_bulk_delete_terms', array( $this, 'handle_bulk_delete_terms' ) );
		add_action( 'wp_ajax_storesuite_get_list_quick_edit_form', array( $this, 'handle_get_quick_edit_form' ) );
		add_action( 'wp_ajax_storesuite_save_list_quick_edit', array( $this, 'handle_save_quick_edit' ) );
	}

	/**
	 * Resolve the taxonomy for a given list object type.
	 *
	 * @param string $object_type One of category|tag|brand|attribute_term.
	 * @param string $taxonomy    Raw taxonomy (only used for attribute terms).
	 * @return string Empty string when the object type/taxonomy is not allowed.
	 */
	private function get_taxonomy_for_object_type( $object_type, $taxonomy = '' ) {
		switch ( $object_type ) {
			case 'category':
				return 'product_cat';
			case 'tag':
				return 'product_tag';
			case 'brand':
				return 'product_brand';
			case 'attribute_term':
				if ( $taxonomy && taxonomy_exists( $taxonomy ) && 0 === strpos( $taxonomy, 'pa_' ) ) {
					return $taxonomy;
				}
				return '';
			default:
				return '';
		}
	}

	/**
	 * AJAX: bulk delete selected terms (categories, tags, brands or attribute terms).
	 *
	 * @return void
	 */
	public function handle_bulk_delete_terms() {
		// Verify the nonce (shared delete nonce used across StoreSuite delete actions).
		if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['security'] ) ), '_storesuite_delete_nonce_' ) ) {
			wp_send_json_error( array( 'error' => __( 'Nonce verification failed', 'storesuite' ) ), 403 );
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to perform this action.', 'storesuite' ) ), 403 );
		}

		$object_type = isset( $_POST['object_type'] ) ? sanitize_key( wp_unslash( $_POST['object_type'] ) ) : '';
		$taxonomy    = isset( $_POST['taxonomy'] ) ? sanitize_text_field( wp_unslash( $_POST['taxonomy'] ) ) : '';
		$taxonomy    = $this->get_taxonomy_for_object_type( $object_type, $taxonomy );

		if ( '' === $taxonomy ) {
			wp_send_json_error( array( 'error' => __( 'Invalid request.', 'storesuite' ) ), 400 );
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Array sanitized below.
		$ids = isset( $_POST['ids'] ) ? (array) wp_unslash( $_POST['ids'] ) : array();
		$ids = array_map( 'absint', $ids );
		$ids = array_values( array_unique( array_filter( $ids ) ) );

		if ( empty( $ids ) ) {
			wp_send_json_error( array( 'error' => __( 'No items selected.', 'storesuite' ) ), 400 );
		}

		$deleted = 0;
		foreach ( $ids as $term_id ) {
			if ( ! term_exists( $term_id, $taxonomy ) ) {
				continue;
			}

			$result = wp_delete_term( $term_id, $taxonomy );
			if ( ! is_wp_error( $result ) && false !== $result ) {
				++$deleted;
			}
		}

		wp_send_json_success(
			array(
				'message' => sprintf(
					/* translators: %d: number of items deleted */
					_n( '%d item deleted.', '%d items deleted.', $deleted, 'storesuite' ),
					$deleted
				),
				'deleted' => $deleted,
			)
		);
	}

	/**
	 * AJAX: return the quick edit form HTML for a single list item.
	 *
	 * @return void
	 */
	public function handle_get_quick_edit_form() {
		check_ajax_referer( 'storesuite_list_quick_edit_form', 'security' );

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'storesuite' ) ), 403 );
		}

		$object_type = isset( $_POST['object_type'] ) ? sanitize_key( wp_unslash( $_POST['object_type'] ) ) : '';
		$item_id     = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
		$taxonomy    = isset( $_POST['taxonomy'] ) ? sanitize_text_field( wp_unslash( $_POST['taxonomy'] ) ) : '';

		if ( ! $item_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request.', 'storesuite' ) ), 400 );
		}

		if ( 'attribute' === $object_type ) {
			$html = $this->get_attribute_quick_edit_form_html( $item_id );
		} else {
			$resolved_taxonomy = $this->get_taxonomy_for_object_type( $object_type, $taxonomy );
			if ( '' === $resolved_taxonomy ) {
				wp_send_json_error( array( 'message' => __( 'Invalid request.', 'storesuite' ) ), 400 );
			}
			$html = $this->get_term_quick_edit_form_html( $object_type, $resolved_taxonomy, $item_id );
		}

		if ( '' === $html ) {
			wp_send_json_error( array( 'message' => __( 'Item not found.', 'storesuite' ) ), 404 );
		}

		wp_send_json_success( array( 'html' => $html ) );
	}

	/**
	 * Build the quick edit form HTML for a term-based item.
	 *
	 * @param string $object_type One of category|tag|brand|attribute_term.
	 * @param string $taxonomy    Resolved taxonomy.
	 * @param int    $term_id     Term ID.
	 * @return string
	 */
	private function get_term_quick_edit_form_html( $object_type, $taxonomy, $term_id ) {
		$term = get_term( $term_id, $taxonomy );
		if ( ! $term instanceof \WP_Term ) {
			return '';
		}

		$template_map = array(
			'category'       => 'categories/category-quick-edit-form',
			'tag'            => 'tags/tag-quick-edit-form',
			'brand'          => 'brands/brand-quick-edit-form',
			'attribute_term' => 'attributes/attribute-term-quick-edit-form',
		);

		if ( ! isset( $template_map[ $object_type ] ) ) {
			return '';
		}

		ob_start();
		storesuite_get_template_part(
			$template_map[ $object_type ],
			'',
			array(
				'object_type' => $object_type,
				'taxonomy'    => $taxonomy,
				'term'        => $term,
			)
		);
		return ob_get_clean();
	}

	/**
	 * Build the quick edit form HTML for a global attribute.
	 *
	 * @param int $attribute_id Attribute ID.
	 * @return string
	 */
	private function get_attribute_quick_edit_form_html( $attribute_id ) {
		$attribute = wc_get_attribute( $attribute_id );
		if ( ! $attribute ) {
			return '';
		}

		ob_start();
		storesuite_get_template_part(
			'attributes/attribute-quick-edit-form',
			'',
			array(
				'object_type' => 'attribute',
				'attribute'   => $attribute,
			)
		);
		return ob_get_clean();
	}

	/**
	 * AJAX: save a quick edit submission.
	 *
	 * @return void
	 */
	public function handle_save_quick_edit() {
		check_ajax_referer( 'storesuite_list_quick_edit', 'security' );

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'storesuite' ) ), 403 );
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Field-level sanitization below.
		$data = isset( $_POST['data'] ) ? wp_unslash( $_POST['data'] ) : null;
		if ( ! is_array( $data ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request.', 'storesuite' ) ), 400 );
		}

		$object_type = isset( $data['object_type'] ) ? sanitize_key( $data['object_type'] ) : '';
		$item_id     = isset( $data['id'] ) ? absint( $data['id'] ) : 0;

		if ( ! $item_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request.', 'storesuite' ) ), 400 );
		}

		if ( 'attribute' === $object_type ) {
			$this->save_attribute_quick_edit( $item_id, $data );
			return;
		}

		$taxonomy = isset( $data['taxonomy'] ) ? sanitize_text_field( $data['taxonomy'] ) : '';
		$taxonomy = $this->get_taxonomy_for_object_type( $object_type, $taxonomy );
		if ( '' === $taxonomy ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request.', 'storesuite' ) ), 400 );
		}

		$this->save_term_quick_edit( $object_type, $taxonomy, $item_id, $data );
	}

	/**
	 * Persist a term quick edit and send the JSON response.
	 *
	 * @param string               $object_type Object type.
	 * @param string               $taxonomy    Taxonomy.
	 * @param int                  $term_id     Term ID.
	 * @param array<string, mixed> $data        Submitted field map.
	 * @return void
	 */
	private function save_term_quick_edit( $object_type, $taxonomy, $term_id, array $data ) {
		if ( ! term_exists( $term_id, $taxonomy ) ) {
			wp_send_json_error( array( 'message' => __( 'Item not found.', 'storesuite' ) ), 404 );
		}

		$name = isset( $data['name'] ) ? sanitize_text_field( $data['name'] ) : '';
		if ( '' === $name ) {
			wp_send_json_error( array( 'message' => __( 'Name is required.', 'storesuite' ) ), 422 );
		}

		$slug        = isset( $data['slug'] ) ? sanitize_title( $data['slug'] ) : '';
		$description = isset( $data['description'] ) ? sanitize_textarea_field( $data['description'] ) : '';

		$args = array(
			'name'        => $name,
			'description' => $description,
		);

		if ( '' !== $slug ) {
			$args['slug'] = $slug;
		}

		// Parent only applies to hierarchical taxonomies (categories, brands).
		if ( is_taxonomy_hierarchical( $taxonomy ) && isset( $data['parent'] ) ) {
			$parent = absint( $data['parent'] );
			// A term cannot be its own parent.
			$args['parent'] = ( $parent === (int) $term_id ) ? 0 : $parent;
		}

		$result = wp_update_term( $term_id, $taxonomy, $args );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 422 );
		}

		wp_send_json_success( array( 'message' => __( 'Changes saved.', 'storesuite' ) ) );
	}

	/**
	 * Persist a global attribute quick edit and send the JSON response.
	 *
	 * @param int                  $attribute_id Attribute ID.
	 * @param array<string, mixed> $data         Submitted field map.
	 * @return void
	 */
	private function save_attribute_quick_edit( $attribute_id, array $data ) {
		if ( ! wc_get_attribute( $attribute_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Item not found.', 'storesuite' ) ), 404 );
		}

		$label = isset( $data['name'] ) ? wc_clean( $data['name'] ) : '';
		if ( '' === $label ) {
			wp_send_json_error( array( 'message' => __( 'Name is required.', 'storesuite' ) ), 422 );
		}

		$args = array(
			'name'         => $label,
			'slug'         => isset( $data['slug'] ) ? wc_sanitize_taxonomy_name( $data['slug'] ) : '',
			'type'         => isset( $data['attribute_type'] ) ? wc_clean( $data['attribute_type'] ) : 'select',
			'order_by'     => isset( $data['attribute_orderby'] ) ? wc_clean( $data['attribute_orderby'] ) : 'menu_order',
			'has_archives' => ! empty( $data['attribute_public'] ),
		);

		$result = wc_update_attribute( $attribute_id, $args );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 422 );
		}

		wp_send_json_success( array( 'message' => __( 'Changes saved.', 'storesuite' ) ) );
	}
}
