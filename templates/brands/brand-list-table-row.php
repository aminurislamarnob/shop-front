<?php
/**
 * MSFC brand list table row
 *
 * @package ShopFront
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$brand = $args['brand'];
?>

<tr id="brand-row-<?php echo esc_attr( $brand->term_id ); ?>">
	<td class="brand-name">
		<?php echo esc_html( $brand->name ); ?>
	</td>
	<td class="brand-description">
		<?php echo esc_html( wp_trim_words( $brand->description, 10, '...' ) ); ?>
	</td>
	<td class="brand-slug">
		<?php echo esc_html( $brand->slug ); ?>
	</td>
	<td class="brand-count">
		<?php echo esc_html( $brand->count ); ?>
	</td>
	<td class="text-right" data-title="<?php esc_attr_e( 'Actions', 'shop-front' ); ?>">
		<div class="msfc-dropdown">
			<span class="msfc-dropdown-icon">
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-three-dots" viewBox="0 0 16 16">
					<path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3"/>
				</svg>
			</span>
			<ul class="msfc-dropdown-menu">
				<li>
					<a href="<?php echo esc_url( get_category_link( $brand->term_id ) ); ?>" class="dropdown-link">
						<?php echo esc_html__( 'View', 'shop-front' ); ?>
					</a>
				</li>
				<li>
					<a href="<?php echo esc_url( sprintf( msfc_get_navigation_url( 'edit-brand' ) . '%s', $brand->term_id ) ); ?>" class="dropdown-link"><?php echo esc_html__( 'Edit', 'shop-front' ); ?></a>
				</li>
				<li>
					<button @click="deleteBrand(<?php echo esc_attr( $brand->term_id ); ?>)" type="button" class="inline-button dropdown-link">
						<?php echo esc_html__( 'Delete', 'shop-front' ); ?>
					</button>
				</li>
			</ul>
		</div>
	</td>
</tr> 