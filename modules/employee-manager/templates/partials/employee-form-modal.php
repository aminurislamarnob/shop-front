<?php
/**
 * Employee Manager — add/edit employee modal.
 *
 * Expects $storesuite_roles (Roles::all()) in scope.
 *
 * @package StoreSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$storesuite_roles = isset( $storesuite_roles ) ? $storesuite_roles : array();
?>
<div class="storesuite-product-bulk-modal-overlay storesuite-modal-fade" id="storesuite-employee-modal" hidden>
	<div class="storesuite-card storesuite-card-with-header">
		<h3 class="storesuite-card-title" id="storesuite-employee-modal-title"><?php esc_html_e( 'Add Employee', 'storesuite' ); ?></h3>
		<div class="storesuite-card-content">
			<form id="storesuite-employee-form">
				<input type="hidden" name="user_id" value="" />

				<div class="row">
					<div class="col-md-6">
						<div class="storesuite-form-group">
							<label for="storesuite-emp-first-name"><?php esc_html_e( 'First name', 'storesuite' ); ?></label>
							<input type="text" class="storesuite-form-control" id="storesuite-emp-first-name" name="first_name" />
						</div>
					</div>
					<div class="col-md-6">
						<div class="storesuite-form-group">
							<label for="storesuite-emp-last-name"><?php esc_html_e( 'Last name', 'storesuite' ); ?></label>
							<input type="text" class="storesuite-form-control" id="storesuite-emp-last-name" name="last_name" />
						</div>
					</div>
				</div>

				<div class="storesuite-form-group" data-field="email">
					<label for="storesuite-emp-email">
						<?php esc_html_e( 'Email', 'storesuite' ); ?>
						<span class="req">*</span>
					</label>
					<input type="email" class="storesuite-form-control" id="storesuite-emp-email" name="email" required />
					<small class="storesuite-form-text"><?php esc_html_e( 'A setup email is sent so the employee can choose their own password.', 'storesuite' ); ?></small>
				</div>

				<div class="storesuite-form-group">
					<label for="storesuite-emp-role">
						<?php esc_html_e( 'Role', 'storesuite' ); ?>
						<span class="req">*</span>
					</label>
					<select class="storesuite-form-control" id="storesuite-emp-role" name="role" required>
						<option value=""><?php esc_html_e( 'Select a role…', 'storesuite' ); ?></option>
						<?php foreach ( $storesuite_roles as $storesuite_role ) : ?>
							<option value="<?php echo esc_attr( $storesuite_role['slug'] ); ?>"><?php echo esc_html( $storesuite_role['label'] ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="storesuite-form-group text-md-end">
					<button type="button" class="my-storesuite-button my-storesuite-button-light" id="storesuite-employee-cancel"><?php esc_html_e( 'Cancel', 'storesuite' ); ?></button>
					<button type="submit" class="my-storesuite-button"><?php esc_html_e( 'Save Employee', 'storesuite' ); ?></button>
				</div>
			</form>
		</div>
	</div>
</div>
