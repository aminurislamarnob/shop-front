<?php
/**
 * Shared term quick edit fields (loaded into the shared quick edit modal via AJAX).
 *
 * Used by every term-based list page (categories, tags, brands and attribute
 * terms). The parent selector is only rendered for hierarchical taxonomies.
 *
 * @package StoreSuite
 *
 * @var string   $object_type Object type (category|tag|brand|attribute_term).
 * @var string   $taxonomy    Taxonomy.
 * @var \WP_Term $term        Term.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$storesuite_term_id        = (int) $term->term_id;
$storesuite_is_hierarchical = is_taxonomy_hierarchical( $taxonomy );

if ( $storesuite_is_hierarchical ) {
	// Build the parent options, excluding the term itself and its descendants.
	$storesuite_exclude   = array_merge( array( $storesuite_term_id ), get_term_children( $storesuite_term_id, $taxonomy ) );
	$storesuite_all_terms = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
			'exclude'    => $storesuite_exclude,
		)
	);
	if ( is_wp_error( $storesuite_all_terms ) ) {
		$storesuite_all_terms = array();
	}
}
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

		<?php if ( $storesuite_is_hierarchical ) : ?>
			<div class="storesuite-form-group">
				<label class="storesuite-form-label" for="storesuite-qe-parent-<?php echo esc_attr( (string) $storesuite_term_id ); ?>"><?php esc_html_e( 'Parent', 'storesuite' ); ?></label>
				<select id="storesuite-qe-parent-<?php echo esc_attr( (string) $storesuite_term_id ); ?>" class="storesuite-form-control" data-field-name="parent">
					<option value="0"><?php esc_html_e( 'None', 'storesuite' ); ?></option>
					<?php foreach ( $storesuite_all_terms as $storesuite_parent_term ) : ?>
						<option value="<?php echo esc_attr( (string) $storesuite_parent_term->term_id ); ?>" <?php selected( (int) $term->parent, (int) $storesuite_parent_term->term_id ); ?>><?php echo esc_html( $storesuite_parent_term->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		<?php endif; ?>

		<div class="storesuite-form-group">
			<label class="storesuite-form-label" for="storesuite-qe-desc-<?php echo esc_attr( (string) $storesuite_term_id ); ?>"><?php esc_html_e( 'Description', 'storesuite' ); ?></label>
			<textarea id="storesuite-qe-desc-<?php echo esc_attr( (string) $storesuite_term_id ); ?>" class="storesuite-form-control" rows="3" data-field-name="description"><?php echo esc_textarea( $term->description ); ?></textarea>
		</div>

		<input type="hidden" data-field-name="id" value="<?php echo esc_attr( (string) $storesuite_term_id ); ?>">
		<input type="hidden" data-field-name="object_type" value="<?php echo esc_attr( $object_type ); ?>">
		<input type="hidden" data-field-name="taxonomy" value="<?php echo esc_attr( $taxonomy ); ?>">
	</fieldset>
</div>
