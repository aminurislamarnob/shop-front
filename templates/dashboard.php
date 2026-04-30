<?php
/**
 * Dashboard Template
 *
 * @package StoreSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'storesuite_dashboard_wrapper_start' );
?>
<div class="my-storesuite-container">
	<aside id="storesuite-dashboard-sidebar" class="my-storesuite-sidebar" role="navigation" aria-label="<?php esc_attr_e( 'Store dashboard navigation', 'storesuite' ); ?>">
		<?php do_action( 'storesuite_dashboard_navigation' ); ?>
	</aside>
	<div class="my-storesuite-wrapper storesuite-analytics-wrapper">
		<?php do_action( 'storesuite_dashboard_content_before' ); ?>
		<main class="my-storesuite-page-content storesuite-analytics-page">
			<?php do_action( 'storesuite_dashboard_before_main_content' ); ?>
			<div id="storesuite-analytics-app"></div>
		</main>
	</div>
</div>
<?php do_action( 'storesuite_dashboard_wrapper_end' ); ?>