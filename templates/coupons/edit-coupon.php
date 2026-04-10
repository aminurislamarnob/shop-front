<?php
/**
 * StoreSuite coupon edit page
 *
 * @package StoreSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$coupon_id = get_query_var( 'edit-coupon' );
$coupon_id = absint( $coupon_id );

do_action( 'storesuite_dashboard_wrapper_start' );
?>
<div class="my-storesuite-container">
	<aside id="storesuite-dashboard-sidebar" class="my-storesuite-sidebar" role="navigation" aria-label="<?php esc_attr_e( 'Store dashboard navigation', 'storesuite' ); ?>">
		<?php do_action( 'storesuite_dashboard_navigation' ); ?>
	</aside>
	<div class="my-storesuite-wrapper">
		<?php do_action( 'storesuite_dashboard_content_before' ); ?>
		<main class="my-storesuite-page-content">
			<?php do_action( 'storesuite_dashboard_before_main_content' ); ?>
			<?php
			global $wp;
			$query_vars = $wp->query_vars;

			if ( $coupon_id ) {
				$coupon = new WC_Coupon( $coupon_id );

				if ( ! $coupon->get_id() ) {
					echo '<div class="alert alert-danger">' . esc_html__( 'Coupon not found.', 'storesuite' ) . '</div>';
				} else {
					do_action( 'storesuite_dashboard_coupon_edit_form', $query_vars );
				}
			} else {
				echo '<div class="alert alert-danger">' . esc_html__( 'Invalid coupon ID.', 'storesuite' ) . '</div>';
			}
			?>
		</main>
	</div>
</div>
<?php do_action( 'storesuite_dashboard_wrapper_end' ); ?>
