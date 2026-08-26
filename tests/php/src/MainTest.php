<?php
/**
 * Access-control tests for the Main service class.
 *
 * The wp-admin blocking and login redirect paths call wp_safe_redirect() and
 * exit, so they cannot run inside a test process; those flows belong to the
 * Playwright e2e suite. Everything with a return value is covered here.
 *
 * @package StoreSuite\Tests
 */

namespace PluginizeLab\StoreSuite\Test;

/**
 * @covers \PluginizeLab\StoreSuite\Main
 * @group storesuite-access-control
 */
class MainTest extends StoreSuiteTestCase {

	/**
	 * The Main instance registered in the plugin container.
	 *
	 * @return \PluginizeLab\StoreSuite\Main
	 */
	private function main() {
		return \pluginizelab_storesuite()->storesuite_main;
	}

	public function test_admin_bar_hidden_when_admin_access_is_prevented() {
		wp_set_current_user( $this->customer_id );

		$this->set_storesuite_option( 'storesuite_prevent_admin_access', 'yes' );
		$this->assertFalse( $this->main()->hide_admin_bar( true ) );

		$this->set_storesuite_option( 'storesuite_prevent_admin_access', 'no' );
		$this->assertTrue( $this->main()->hide_admin_bar( true ) );
	}

	public function test_admin_bar_untouched_for_logged_out_visitors() {
		wp_set_current_user( 0 );
		$this->set_storesuite_option( 'storesuite_prevent_admin_access', 'yes' );

		$this->assertTrue( $this->main()->hide_admin_bar( true ) );
		$this->assertFalse( $this->main()->hide_admin_bar( false ) );
	}

	public function test_my_account_dashboard_button_requires_manage_woocommerce() {
		$this->create_dashboard_page();

		wp_set_current_user( $this->shop_manager_id );
		ob_start();
		$this->main()->add_storesuite_dashboard_btn();
		$html = ob_get_clean();
		$this->assertStringContainsString( 'storesuite-dashboard-btn', $html );

		wp_set_current_user( $this->customer_id );
		ob_start();
		$this->main()->add_storesuite_dashboard_btn();
		$html = ob_get_clean();
		$this->assertSame( '', $html, 'Customers must not see the StoreSuite dashboard button.' );
	}
}
