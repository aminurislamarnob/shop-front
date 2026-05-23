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
	<aside id="storesuite-dashboard-sidebar" class="my-storesuite-sidebar" role="navigation" aria-label="<?php esc_attr_e( 'Store dashboard navigation', 'storesuite' ); ?>">
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
									<div class="storesuite-password-field">
										<input type="password" class="storesuite-form-control" name="password_current" id="password_current" autocomplete="current-password" />
										<button
											type="button"
											class="storesuite-password-toggle"
											data-target="#password_current"
											data-show-label="<?php esc_attr_e( 'Show password', 'storesuite' ); ?>"
											data-hide-label="<?php esc_attr_e( 'Hide password', 'storesuite' ); ?>"
											aria-label="<?php esc_attr_e( 'Show password', 'storesuite' ); ?>"
											title="<?php esc_attr_e( 'Show password', 'storesuite' ); ?>"
										>
											<span class="storesuite-password-icon storesuite-password-icon-show" aria-hidden="true">
												<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="24" height="24"><path d="M23.271,9.419C21.72,6.893,18.192,2.655,12,2.655S2.28,6.893.729,9.419a4.908,4.908,0,0,0,0,5.162C2.28,17.107,5.808,21.345,12,21.345s9.72-4.238,11.271-6.764A4.908,4.908,0,0,0,23.271,9.419Zm-1.705,4.115C20.234,15.7,17.219,19.345,12,19.345S3.766,15.7,2.434,13.534a2.918,2.918,0,0,1,0-3.068C3.766,8.3,6.781,4.655,12,4.655s8.234,3.641,9.566,5.811A2.918,2.918,0,0,1,21.566,13.534Z"/><path d="M12,7a5,5,0,1,0,5,5A5.006,5.006,0,0,0,12,7Zm0,8a3,3,0,1,1,3-3A3,3,0,0,1,12,15Z"/></svg>
											</span>
											<span class="storesuite-password-icon storesuite-password-icon-hide storesuite-hide" aria-hidden="true">
												<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><g id="_01_align_center" data-name="01 align center"><path d="M23.821,11.181v0a15.736,15.736,0,0,0-4.145-5.44l3.032-3.032L21.293,1.293,18,4.583A11.783,11.783,0,0,0,12,3C4.5,3,1.057,9.261.179,11.181a1.969,1.969,0,0,0,0,1.64,15.736,15.736,0,0,0,4.145,5.44L1.293,21.293l1.414,1.414L6,19.417A11.783,11.783,0,0,0,12,21c7.5,0,10.943-6.261,11.821-8.181A1.968,1.968,0,0,0,23.821,11.181ZM2,12.011C2.75,10.366,5.693,5,12,5a9.847,9.847,0,0,1,4.518,1.068L14.753,7.833a4.992,4.992,0,0,0-6.92,6.92L5.754,16.832A13.647,13.647,0,0,1,2,12.011ZM15,12a3,3,0,0,1-3,3,2.951,2.951,0,0,1-1.285-.3L14.7,10.715A2.951,2.951,0,0,1,15,12ZM9,12a3,3,0,0,1,3-3,2.951,2.951,0,0,1,1.285.3L9.3,13.285A2.951,2.951,0,0,1,9,12Zm3,7a9.847,9.847,0,0,1-4.518-1.068l1.765-1.765a4.992,4.992,0,0,0,6.92-6.92l2.078-2.078A13.584,13.584,0,0,1,22,12C21.236,13.657,18.292,19,12,19Z"/></g></svg>
											</span>
										</button>
									</div>
								</div>
								<div class="storesuite-form-group">
									<label for="password_1"><?php esc_html_e( 'New password (leave blank to leave unchanged)', 'storesuite' ); ?></label>
									<div class="storesuite-password-field">
										<input type="password" class="storesuite-form-control" name="password_1" id="password_1" autocomplete="new-password" />
										<button
											type="button"
											class="storesuite-password-toggle"
											data-target="#password_1"
											data-show-label="<?php esc_attr_e( 'Show password', 'storesuite' ); ?>"
											data-hide-label="<?php esc_attr_e( 'Hide password', 'storesuite' ); ?>"
											aria-label="<?php esc_attr_e( 'Show password', 'storesuite' ); ?>"
											title="<?php esc_attr_e( 'Show password', 'storesuite' ); ?>"
										>
											<span class="storesuite-password-icon storesuite-password-icon-show" aria-hidden="true">
												<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="24" height="24"><path d="M23.271,9.419C21.72,6.893,18.192,2.655,12,2.655S2.28,6.893.729,9.419a4.908,4.908,0,0,0,0,5.162C2.28,17.107,5.808,21.345,12,21.345s9.72-4.238,11.271-6.764A4.908,4.908,0,0,0,23.271,9.419Zm-1.705,4.115C20.234,15.7,17.219,19.345,12,19.345S3.766,15.7,2.434,13.534a2.918,2.918,0,0,1,0-3.068C3.766,8.3,6.781,4.655,12,4.655s8.234,3.641,9.566,5.811A2.918,2.918,0,0,1,21.566,13.534Z"/><path d="M12,7a5,5,0,1,0,5,5A5.006,5.006,0,0,0,12,7Zm0,8a3,3,0,1,1,3-3A3,3,0,0,1,12,15Z"/></svg>
											</span>
											<span class="storesuite-password-icon storesuite-password-icon-hide storesuite-hide" aria-hidden="true">
												<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><g id="_01_align_center" data-name="01 align center"><path d="M23.821,11.181v0a15.736,15.736,0,0,0-4.145-5.44l3.032-3.032L21.293,1.293,18,4.583A11.783,11.783,0,0,0,12,3C4.5,3,1.057,9.261.179,11.181a1.969,1.969,0,0,0,0,1.64,15.736,15.736,0,0,0,4.145,5.44L1.293,21.293l1.414,1.414L6,19.417A11.783,11.783,0,0,0,12,21c7.5,0,10.943-6.261,11.821-8.181A1.968,1.968,0,0,0,23.821,11.181ZM2,12.011C2.75,10.366,5.693,5,12,5a9.847,9.847,0,0,1,4.518,1.068L14.753,7.833a4.992,4.992,0,0,0-6.92,6.92L5.754,16.832A13.647,13.647,0,0,1,2,12.011ZM15,12a3,3,0,0,1-3,3,2.951,2.951,0,0,1-1.285-.3L14.7,10.715A2.951,2.951,0,0,1,15,12ZM9,12a3,3,0,0,1,3-3,2.951,2.951,0,0,1,1.285.3L9.3,13.285A2.951,2.951,0,0,1,9,12Zm3,7a9.847,9.847,0,0,1-4.518-1.068l1.765-1.765a4.992,4.992,0,0,0,6.92-6.92l2.078-2.078A13.584,13.584,0,0,1,22,12C21.236,13.657,18.292,19,12,19Z"/></g></svg>
											</span>
										</button>
									</div>
								</div>
								<div class="storesuite-form-group">
									<label for="password_2"><?php esc_html_e( 'Confirm new password', 'storesuite' ); ?></label>
									<div class="storesuite-password-field">
										<input type="password" class="storesuite-form-control" name="password_2" id="password_2" autocomplete="new-password" />
										<button
											type="button"
											class="storesuite-password-toggle"
											data-target="#password_2"
											data-show-label="<?php esc_attr_e( 'Show password', 'storesuite' ); ?>"
											data-hide-label="<?php esc_attr_e( 'Hide password', 'storesuite' ); ?>"
											aria-label="<?php esc_attr_e( 'Show password', 'storesuite' ); ?>"
											title="<?php esc_attr_e( 'Show password', 'storesuite' ); ?>"
										>
											<span class="storesuite-password-icon storesuite-password-icon-show" aria-hidden="true">
												<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="24" height="24"><path d="M23.271,9.419C21.72,6.893,18.192,2.655,12,2.655S2.28,6.893.729,9.419a4.908,4.908,0,0,0,0,5.162C2.28,17.107,5.808,21.345,12,21.345s9.72-4.238,11.271-6.764A4.908,4.908,0,0,0,23.271,9.419Zm-1.705,4.115C20.234,15.7,17.219,19.345,12,19.345S3.766,15.7,2.434,13.534a2.918,2.918,0,0,1,0-3.068C3.766,8.3,6.781,4.655,12,4.655s8.234,3.641,9.566,5.811A2.918,2.918,0,0,1,21.566,13.534Z"/><path d="M12,7a5,5,0,1,0,5,5A5.006,5.006,0,0,0,12,7Zm0,8a3,3,0,1,1,3-3A3,3,0,0,1,12,15Z"/></svg>
											</span>
											<span class="storesuite-password-icon storesuite-password-icon-hide storesuite-hide" aria-hidden="true">
												<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><g id="_01_align_center" data-name="01 align center"><path d="M23.821,11.181v0a15.736,15.736,0,0,0-4.145-5.44l3.032-3.032L21.293,1.293,18,4.583A11.783,11.783,0,0,0,12,3C4.5,3,1.057,9.261.179,11.181a1.969,1.969,0,0,0,0,1.64,15.736,15.736,0,0,0,4.145,5.44L1.293,21.293l1.414,1.414L6,19.417A11.783,11.783,0,0,0,12,21c7.5,0,10.943-6.261,11.821-8.181A1.968,1.968,0,0,0,23.821,11.181ZM2,12.011C2.75,10.366,5.693,5,12,5a9.847,9.847,0,0,1,4.518,1.068L14.753,7.833a4.992,4.992,0,0,0-6.92,6.92L5.754,16.832A13.647,13.647,0,0,1,2,12.011ZM15,12a3,3,0,0,1-3,3,2.951,2.951,0,0,1-1.285-.3L14.7,10.715A2.951,2.951,0,0,1,15,12ZM9,12a3,3,0,0,1,3-3,2.951,2.951,0,0,1,1.285.3L9.3,13.285A2.951,2.951,0,0,1,9,12Zm3,7a9.847,9.847,0,0,1-4.518-1.068l1.765-1.765a4.992,4.992,0,0,0,6.92-6.92l2.078-2.078A13.584,13.584,0,0,1,22,12C21.236,13.657,18.292,19,12,19Z"/></g></svg>
											</span>
										</button>
									</div>
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
