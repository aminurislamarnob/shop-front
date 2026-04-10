<?php
/**
 * Dashboard Logo Template
 *
 * @package StoreSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$logo_id  = absint( storesuite_get_option_by_key( 'storesuite_dashboard_sidebar_logo_id' ) );
$icon_id  = absint( storesuite_get_option_by_key( 'storesuite_dashboard_sidebar_icon_id' ) );
$logo_url = ( $logo_id && wp_attachment_is_image( $logo_id ) ) ? wp_get_attachment_image_url( $logo_id, 'medium' ) : '';
$icon_url = ( $icon_id && wp_attachment_is_image( $icon_id ) ) ? wp_get_attachment_image_url( $icon_id, 'thumbnail' ) : '';
?>
<div class="storesuite-sidebar-logo">
	<?php if ( $logo_url ) : ?>
		<img class="storesuite-sidebar-logo-image" src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
	<?php endif; ?>
	<?php if ( $icon_url ) : ?>
		<img class="storesuite-sidebar-icon-image" src="<?php echo esc_url( $icon_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
	<?php endif; ?>
	<h1 class="storesuite-sidebar-site-title<?php echo $logo_url ? ' screen-reader-text' : ''; ?>"><?php bloginfo( 'title' ); ?></h1>
</div>
