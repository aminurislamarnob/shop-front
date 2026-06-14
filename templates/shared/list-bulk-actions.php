<?php
/**
 * Shared bulk actions toolbar for the StoreSuite list pages.
 *
 * @package StoreSuite
 *
 * @var string $object_type Object type (category|tag|brand|attribute_term).
 * @var string $taxonomy    Taxonomy (only required for attribute terms).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$storesuite_bulk_taxonomy = isset( $taxonomy ) ? $taxonomy : '';
?>
<div class="storesuite-form-group d-flex align-items-center storesuite-bulk-product-actions storesuite-list-bulk-actions" data-object-type="<?php echo esc_attr( $object_type ); ?>" data-taxonomy="<?php echo esc_attr( $storesuite_bulk_taxonomy ); ?>">
	<select class="storesuite-form-control storesuite-bulk-action-select">
		<option value="-1"><?php esc_html_e( 'Bulk actions', 'storesuite' ); ?></option>
		<option value="delete"><?php esc_html_e( 'Delete', 'storesuite' ); ?></option>
	</select>
	<button type="button" class="my-storesuite-button storesuite-bulk-apply"><?php esc_html_e( 'Apply', 'storesuite' ); ?></button>
</div>
