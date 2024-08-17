<?php
/**
 * MSFC category List Page
 * ***/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'msf_dashboard_wrapper_start' );
?>
<div class="my-shop-front-container">
	<div class="row">
		<div class="col-md-2">
			<?php do_action( 'msf_dashboard_navigation' ); ?>
		</div>
		<div class="col-md-10">
			Category List
		</div>
	</div>
</div>
<?php do_action( 'msf_dashboard_wrapper_end' ); ?>