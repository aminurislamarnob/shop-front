<?php
/**
 * Variations empty-state.
 *
 * Shown inside the variations container when a variable product has no
 * variations yet. Mirrors the WooCommerce admin "no variations" design.
 *
 * @package StoreSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wc_images_url      = defined( 'WC_ADMIN_IMAGES_FOLDER_URL' ) ? WC_ADMIN_IMAGES_FOLDER_URL : plugins_url( 'assets/images', WC_PLUGIN_FILE );
$background_img_url = $wc_images_url . '/product_data/no-variation-background-image.svg';
$arrow_img_url      = $wc_images_url . '/product_data/no-variation-arrow.svg';
?>
<div class="storesuite-variation-empty">
	<div class="storesuite-variation-empty-arrow">
		<img src="<?php echo esc_url( $arrow_img_url ); ?>" alt="" />
	</div>
	<img class="storesuite-variation-empty-bg" src="<?php echo esc_url( $background_img_url ); ?>" alt="" />
	<p>
		<?php esc_html_e( 'No variations yet. Generate them from all added attributes or add a new variation manually.', 'storesuite' ); ?>
	</p>
</div>
