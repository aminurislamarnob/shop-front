<?php
/**
 * Single attribute term list-table row.
 *
 * Shared by the server-rendered list (attribute-terms.php) and the AJAX
 * response when a new term is created, so both produce identical markup.
 *
 * @package StoreSuite
 *
 * @var \WP_Term $term     Attribute term.
 * @var string   $taxonomy Attribute taxonomy (pa_*).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$storesuite_term_url = esc_url(
	add_query_arg(
		array(
			'taxonomy' => $taxonomy,
			'term_id'  => $term->term_id,
		),
		storesuite_get_navigation_url( 'attribute-terms' )
	)
);
?>
<tr class="storesuite-list-row" id="term-row-<?php echo esc_attr( $term->term_id ); ?>">
	<td class="check-column">
		<?php storesuite_get_template_part( 'shared/list-bulk-checkbox', '', array( 'value' => $term->term_id ) ); ?>
	</td>
	<td data-title="<?php esc_attr_e( 'Name', 'storesuite' ); ?>"><a href="<?php echo $storesuite_term_url; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above. ?>"><?php echo esc_html( $term->name ); ?></a></td>
	<td data-title="<?php esc_attr_e( 'Slug', 'storesuite' ); ?>"><?php echo esc_html( $term->slug ); ?></td>
	<td data-title="<?php esc_attr_e( 'Count', 'storesuite' ); ?>"><?php echo esc_html( $term->count ); ?></td>
	<td class="text-right" data-title="<?php esc_attr_e( 'Action', 'storesuite' ); ?>">
		<div class="storesuite-dropdown">
			<span class="storesuite-dropdown-icon">
				<svg width="20" height="20" fill="currentColor" aria-hidden="true" focusable="false"><use href="#storesuite-icon-three-dots"></use></svg>
			</span>
			<ul class="storesuite-dropdown-menu">
				<li>
					<a href="<?php echo $storesuite_term_url; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above. ?>" class="dropdown-link">
						<?php esc_html_e( 'Edit', 'storesuite' ); ?>
					</a>
				</li>
				<li>
					<button type="button" class="inline-button dropdown-link storesuite-item-quick-edit" data-object-type="attribute_term" data-id="<?php echo esc_attr( $term->term_id ); ?>" data-taxonomy="<?php echo esc_attr( $taxonomy ); ?>">
						<?php esc_html_e( 'Quick edit', 'storesuite' ); ?>
					</button>
				</li>
				<li>
					<button type="button" class="inline-button dropdown-link storesuite-delete-attribute-term" data-term-id="<?php echo esc_attr( $term->term_id ); ?>" data-taxonomy="<?php echo esc_attr( $taxonomy ); ?>">
						<?php esc_html_e( 'Delete', 'storesuite' ); ?>
					</button>
				</li>
			</ul>
		</div>
	</td>
</tr>
