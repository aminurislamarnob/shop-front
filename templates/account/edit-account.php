<?php
/**
 * Edit account form – same fields as WooCommerce My Account, StoreSuite form styling.
 *
 * @package StoreSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$user = get_user_by( 'id', get_current_user_id() );
if ( ! $user ) {
	return;
}

do_action( 'storesuite_dashboard_wrapper_start' );
?>
<div class="my-storesuite-container">
	<aside class="my-storesuite-sidebar">
		<?php do_action( 'storesuite_dashboard_navigation' ); ?>
	</aside>
	<div class="my-storesuite-wrapper">
		<?php do_action( 'storesuite_dashboard_content_before' ); ?>
		<main class="my-storesuite-page-content">
			<?php do_action( 'storesuite_dashboard_before_main_content' ); ?>

			<?php
			if ( function_exists( 'wc_print_notices' ) ) {
				wc_print_notices();
			}
			?>

			<?php do_action( 'woocommerce_before_edit_account_form' ); ?>

			<form id="storesuite-edit-account-form" class="storesuite-edit-account-form edit-account" action="" method="post" <?php do_action( 'woocommerce_edit_account_form_tag' ); ?>>
				<?php do_action( 'woocommerce_edit_account_form_start' ); ?>

				<div class="row">
					<div class="col-md-8">
						<div class="storesuite-card storesuite-card-with-header storesuite-mb-24">
							<h3 class="storesuite-card-title"><?php esc_html_e( 'Account details', 'storesuite' ); ?></h3>
							<div class="storesuite-card-content">
								<div class="row">
									<div class="col-md-6">
										<div class="storesuite-form-group">
											<label for="account_first_name"><?php esc_html_e( 'First name', 'storesuite' ); ?> <span class="req">*</span></label>
											<input type="text" class="storesuite-form-control" name="account_first_name" id="account_first_name" autocomplete="given-name" value="<?php echo esc_attr( $user->first_name ); ?>" />
										</div>
									</div>
									<div class="col-md-6">
										<div class="storesuite-form-group">
											<label for="account_last_name"><?php esc_html_e( 'Last name', 'storesuite' ); ?> <span class="req">*</span></label>
											<input type="text" class="storesuite-form-control" name="account_last_name" id="account_last_name" autocomplete="family-name" value="<?php echo esc_attr( $user->last_name ); ?>" />
										</div>
									</div>
								</div>

								<div class="storesuite-form-group">
									<label for="account_display_name"><?php esc_html_e( 'Display name', 'storesuite' ); ?> <span class="req">*</span></label>
									<input type="text" class="storesuite-form-control" name="account_display_name" id="account_display_name" value="<?php echo esc_attr( $user->display_name ); ?>" />
									<small class="storesuite-form-text"><?php esc_html_e( 'This will be how your name will be displayed in the account section and in reviews', 'storesuite' ); ?></small>
								</div>

								<div class="storesuite-form-group">
									<label for="account_email"><?php esc_html_e( 'Email address', 'storesuite' ); ?> <span class="req">*</span></label>
									<input type="email" class="storesuite-form-control" name="account_email" id="account_email" autocomplete="email" value="<?php echo esc_attr( $user->user_email ); ?>" />
								</div>

								<div class="storesuite-form-group storesuite-form-switch">
									<input type="checkbox" class="storesuite-form-control" id="show_password_change" name="show_password_change" value="yes">
									<label for="show_password_change"><?php esc_html_e( 'Change Password', 'storesuite' ); ?></label>
								</div>

								<?php do_action( 'woocommerce_edit_account_form_fields' ); ?>
							</div>
						</div>

						<div id="storesuite-edit-account-password-card" class="storesuite-card storesuite-card-with-header storesuite-mb-24">
							<h3 class="storesuite-card-title"><?php esc_html_e( 'Password change', 'storesuite' ); ?></h3>
							<div class="storesuite-card-content">
								<div class="storesuite-form-group">
									<label for="password_current"><?php esc_html_e( 'Current password (leave blank to leave unchanged)', 'storesuite' ); ?></label>
									<input type="password" class="storesuite-form-control" name="password_current" id="password_current" autocomplete="current-password" />
								</div>
								<div class="storesuite-form-group">
									<label for="password_1"><?php esc_html_e( 'New password (leave blank to leave unchanged)', 'storesuite' ); ?></label>
									<input type="password" class="storesuite-form-control" name="password_1" id="password_1" autocomplete="new-password" />
								</div>
								<div class="storesuite-form-group">
									<label for="password_2"><?php esc_html_e( 'Confirm new password', 'storesuite' ); ?></label>
									<input type="password" class="storesuite-form-control" name="password_2" id="password_2" autocomplete="new-password" />
								</div>
							</div>
						</div>
					</div>
				</div>

				<?php do_action( 'woocommerce_edit_account_form' ); ?>

				<div class="storesuite-form-submission-group">
					<?php wp_nonce_field( '_storesuite_save_account_details_', 'storesuite_save_account_details_nonce' ); ?>
					<input type="hidden" name="action" value="storesuite_save_account_details" />
					<div class="storesuite-button-group">
						<button type="submit" class="my-storesuite-button" name="save_account_details" value="<?php esc_attr_e( 'Save changes', 'storesuite' ); ?>"><?php esc_html_e( 'Save changes', 'storesuite' ); ?></button>
					</div>
				</div>

				<?php do_action( 'woocommerce_edit_account_form_end' ); ?>
			</form>

			<?php do_action( 'woocommerce_after_edit_account_form' ); ?>
		</main>
	</div>
</div>
<?php do_action( 'storesuite_dashboard_wrapper_end' ); ?>
