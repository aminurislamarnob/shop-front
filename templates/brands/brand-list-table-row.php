<?php
/**
 * MSFC brand list table row
 *
 * @package StoreSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<tr id="brand-row-<?php echo esc_attr( $brand->term_id ); ?>">
	<td class="brand-image" data-title="<?php esc_attr_e( 'Image', 'storesuite' ); ?>">
		<?php
		$thumbnail_id = absint( get_term_meta( $brand->term_id, 'thumbnail_id', true ) );
		if ( $thumbnail_id ) {
			$image_url = wp_get_attachment_image_url( $thumbnail_id, 'thumbnail' );
			if ( $image_url ) {
				?>
				<img src="<?php echo esc_url( $image_url ); ?>" class="my-storesuite-thumb" alt="<?php echo esc_attr( $brand->name ); ?>">
				<?php
			} else {
				?>
				<img src="<?php echo esc_url( wc_placeholder_img_src( 'thumbnail' ) ); ?>" class="my-storesuite-thumb" alt="<?php esc_attr_e( 'Placeholder', 'storesuite' ); ?>">
				<?php
			}
		} else {
			?>
			<img src="<?php echo esc_url( wc_placeholder_img_src( 'thumbnail' ) ); ?>" class="my-storesuite-thumb" alt="<?php esc_attr_e( 'Placeholder', 'storesuite' ); ?>">
			<?php
		}
		?>
	</td>
	<td class="brand-name">
		<?php echo wp_kses_post( $dash_prefix ); ?><?php echo esc_html( $brand->name ); ?>
	</td>
	<td class="brand-description">
		<?php echo esc_html( wp_trim_words( $brand->description, 10, '...' ) ); ?>
	</td>
	<td class="brand-parent">
		<?php echo esc_html( $parent ? $parent->name : '-' ); ?>
	</td>
	<td class="brand-slug">
		<?php echo esc_html( $brand->slug ); ?>
	</td>
	<td class="brand-count">
		<?php echo esc_html( $brand->count ); ?>
	</td>
	<td class="text-right" data-title="<?php esc_attr_e( 'Actions', 'storesuite' ); ?>">
		<div class="msfc-dropdown">
			<span class="msfc-dropdown-icon">
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-three-dots" viewBox="0 0 16 16">
					<path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3"/>
				</svg>
			</span>
			<ul class="msfc-dropdown-menu">
				<li>
					<a href="<?php echo esc_url( get_category_link( $brand->term_id ) ); ?>" class="dropdown-link">
						<?php echo esc_html__( 'View', 'storesuite' ); ?>
					</a>
				</li>
				<li>
					<a href="<?php echo esc_url( sprintf( storesuite_get_navigation_url( 'edit-brand' ) . '%s', $brand->term_id ) ); ?>" class="dropdown-link"><?php echo esc_html__( 'Edit', 'storesuite' ); ?></a>
				</li>
				<li>
					<button type="button" class="inline-button dropdown-link msfc-delete-brand" data-brand-id="<?php echo esc_attr( $brand->term_id ); ?>">
						<?php echo esc_html__( 'Delete', 'storesuite' ); ?>
					</button>
				</li>
			</ul>
		</div>
	</td>
</tr> 