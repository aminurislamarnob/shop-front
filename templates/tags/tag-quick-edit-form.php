<?php
/**
 * Tag quick edit fields (loaded into the shared quick edit modal via AJAX).
 *
 * @package StoreSuite
 *
 * @var string   $object_type Object type ("tag").
 * @var string   $taxonomy    Taxonomy ("product_tag").
 * @var \WP_Term $term        Tag term.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$storesuite_term_id = (int) $term->term_id;
?>
<div class="storesuite-list-quick-edit-form-root">
	<fieldset class="storesuite-product-quick-edit-fieldset">
		<div class="storesuite-form-group">
			<label class="storesuite-form-label" for="storesuite-qe-name-<?php echo esc_attr( (string) $storesuite_term_id ); ?>"><?php esc_html_e( 'Name', 'storesuite' ); ?></label>
			<input type="text" id="storesuite-qe-name-<?php echo esc_attr( (string) $storesuite_term_id ); ?>" class="storesuite-form-control" data-field-name="name" value="<?php echo esc_attr( $term->name ); ?>">
		</div>

		<div class="storesuite-form-group">
			<label class="storesuite-form-label" for="storesuite-qe-slug-<?php echo esc_attr( (string) $storesuite_term_id ); ?>"><?php esc_html_e( 'Slug', 'storesuite' ); ?></label>
			<input type="text" id="storesuite-qe-slug-<?php echo esc_attr( (string) $storesuite_term_id ); ?>" class="storesuite-form-control" data-field-name="slug" value="<?php echo esc_attr( $term->slug ); ?>">
		</div>

		<div class="storesuite-form-group">
			<label class="storesuite-form-label" for="storesuite-qe-desc-<?php echo esc_attr( (string) $storesuite_term_id ); ?>"><?php esc_html_e( 'Description', 'storesuite' ); ?></label>
			<textarea id="storesuite-qe-desc-<?php echo esc_attr( (string) $storesuite_term_id ); ?>" class="storesuite-form-control" rows="3" data-field-name="description"><?php echo esc_textarea( $term->description ); ?></textarea>
		</div>

		<input type="hidden" data-field-name="id" value="<?php echo esc_attr( (string) $storesuite_term_id ); ?>">
		<input type="hidden" data-field-name="object_type" value="<?php echo esc_attr( $object_type ); ?>">
		<input type="hidden" data-field-name="taxonomy" value="<?php echo esc_attr( $taxonomy ); ?>">
	</fieldset>
</div>
