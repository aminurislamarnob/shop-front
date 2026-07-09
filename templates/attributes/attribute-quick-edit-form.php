<?php
/**
 * Global attribute quick edit fields (loaded into the shared quick edit modal via AJAX).
 *
 * @package StoreSuite
 *
 * @var string $object_type Object type ("attribute").
 * @var object $attribute   Attribute object from wc_get_attribute().
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$storesuite_attribute_id    = (int) $attribute->id;
$storesuite_attribute_label = $attribute->name;
$storesuite_attribute_slug  = preg_replace( '/^pa_/', '', $attribute->slug );
$storesuite_attribute_type  = $attribute->type;
$storesuite_attribute_order = $attribute->order_by;
$storesuite_attribute_pub   = (int) $attribute->has_archives;
?>
<div class="storesuite-list-quick-edit-form-root">
	<fieldset class="storesuite-product-quick-edit-fieldset">
		<div class="storesuite-form-group">
			<label class="storesuite-form-label" for="storesuite-qe-name-<?php echo esc_attr( (string) $storesuite_attribute_id ); ?>"><?php esc_html_e( 'Name', 'storesuite' ); ?></label>
			<input type="text" id="storesuite-qe-name-<?php echo esc_attr( (string) $storesuite_attribute_id ); ?>" class="storesuite-form-control" data-field-name="name" value="<?php echo esc_attr( $storesuite_attribute_label ); ?>">
		</div>

		<div class="storesuite-form-group">
			<label class="storesuite-form-label" for="storesuite-qe-slug-<?php echo esc_attr( (string) $storesuite_attribute_id ); ?>"><?php esc_html_e( 'Slug', 'storesuite' ); ?></label>
			<input type="text" id="storesuite-qe-slug-<?php echo esc_attr( (string) $storesuite_attribute_id ); ?>" class="storesuite-form-control" data-field-name="slug" value="<?php echo esc_attr( $storesuite_attribute_slug ); ?>">
		</div>

		<div class="storesuite-form-group storesuite-form-switch">
			<input type="checkbox" id="storesuite-qe-public-<?php echo esc_attr( (string) $storesuite_attribute_id ); ?>" class="storesuite-form-control" data-field-name="attribute_public" value="1" <?php checked( $storesuite_attribute_pub, 1 ); ?>>
			<label for="storesuite-qe-public-<?php echo esc_attr( (string) $storesuite_attribute_id ); ?>"><?php esc_html_e( 'Enable archives?', 'storesuite' ); ?></label>
		</div>

		<div class="storesuite-form-group">
			<label class="storesuite-form-label" for="storesuite-qe-orderby-<?php echo esc_attr( (string) $storesuite_attribute_id ); ?>"><?php esc_html_e( 'Default sort order', 'storesuite' ); ?></label>
			<select id="storesuite-qe-orderby-<?php echo esc_attr( (string) $storesuite_attribute_id ); ?>" class="storesuite-form-control" data-field-name="attribute_orderby">
				<option value="menu_order" <?php selected( $storesuite_attribute_order, 'menu_order' ); ?>><?php esc_html_e( 'Custom ordering', 'storesuite' ); ?></option>
				<option value="name" <?php selected( $storesuite_attribute_order, 'name' ); ?>><?php esc_html_e( 'Name', 'storesuite' ); ?></option>
				<option value="name_num" <?php selected( $storesuite_attribute_order, 'name_num' ); ?>><?php esc_html_e( 'Name (numeric)', 'storesuite' ); ?></option>
				<option value="id" <?php selected( $storesuite_attribute_order, 'id' ); ?>><?php esc_html_e( 'Term ID', 'storesuite' ); ?></option>
			</select>
		</div>

		<input type="hidden" data-field-name="id" value="<?php echo esc_attr( (string) $storesuite_attribute_id ); ?>">
		<input type="hidden" data-field-name="object_type" value="<?php echo esc_attr( $object_type ); ?>">
		<input type="hidden" data-field-name="attribute_type" value="<?php echo esc_attr( $storesuite_attribute_type ); ?>">
	</fieldset>
</div>
