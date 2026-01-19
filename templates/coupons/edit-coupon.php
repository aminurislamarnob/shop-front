<?php
/**
 * MSFC coupon edit page
 *
 * @package ShopFront
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$coupon_id = get_query_var( 'edit-coupon' );
$coupon_id = absint( $coupon_id );

do_action( 'msf_dashboard_wrapper_start' );
?>
<div class="my-shop-front-container">
	<aside class="my-shop-front-sidebar">
		<?php do_action( 'msf_dashboard_navigation' ); ?>
	</aside>
	<div class="my-shop-front-wrapper">
		<?php do_action( 'msf_dashboard_content_before' ); ?>
		<main class="my-shop-front-page-content">
			<?php do_action( 'msf_dashboard_before_main_content' ); ?>
			<?php
			global $wp;
			$query_vars = $wp->query_vars;

			if ( $coupon_id ) {
				$coupon = new WC_Coupon( $coupon_id );

				if ( ! $coupon->get_id() ) {
					echo '<div class="alert alert-danger">' . esc_html__( 'Coupon not found.', 'shop-front' ) . '</div>';
				} else {
					do_action( 'msf_dashboard_coupon_edit_form', $query_vars );
				}
			} else {
				echo '<div class="alert alert-danger">' . esc_html__( 'Invalid coupon ID.', 'shop-front' ) . '</div>';
			}
			?>
		</main>
	</div>
</div>
<?php do_action( 'msf_dashboard_wrapper_end' ); ?>
