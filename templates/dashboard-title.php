<?php
/**
 * Dashboard Title Template
 *
 * @package StoreSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div class="storesuite-dashboard-title-wrapper">
	<div class="storesuite-dashboard-title">
		<h3 class="storesuite-page-main-title">
			<?php echo esc_html( $page_title ); ?>
		</h3>
	</div>

	<?php if ( ! empty( $parent_endpoint_title ) && ! empty( $parent_endpoint_url ) ) : ?>
		<div class="storesuite-dashboard-braedcrumb">
			<ul>
				<li>
					<a href="<?php echo esc_url( storesuite_get_navigation_url() ); ?>"><?php echo esc_html__( 'Dashboard', 'storesuite' ); ?></a>
				</li>
				<li>
					<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="24" height="24">
						<path d="M15.4,9.88,10.81,5.29a1,1,0,0,0-1.41,0,1,1,0,0,0,0,1.42L14,11.29a1,1,0,0,1,0,1.42L9.4,17.29a1,1,0,0,0,1.41,1.42l4.59-4.59A3,3,0,0,0,15.4,9.88Z"/>
					</svg>
					<a href="<?php echo esc_url( $parent_endpoint_url ); ?>"><?php echo esc_html( $parent_endpoint_title ); ?></a>
				</li>
				<li>
					<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="24" height="24">
						<path d="M15.4,9.88,10.81,5.29a1,1,0,0,0-1.41,0,1,1,0,0,0,0,1.42L14,11.29a1,1,0,0,1,0,1.42L9.4,17.29a1,1,0,0,0,1.41,1.42l4.59-4.59A3,3,0,0,0,15.4,9.88Z"/>
					</svg>
					<span><?php echo esc_html( $page_title ); ?></span>
				</li>
			</ul>
		</div>
	<?php endif; ?>
</div>