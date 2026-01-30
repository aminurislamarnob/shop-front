<?php

namespace PluginizeLab\StoreSuite\Shortcodes;

use PluginizeLab\StoreSuite\Shortcodes\Dashboard;

class Shortcodes {

	private $shortcodes = array();

	/**
	 *  Register My StoreSuite shortcodes
	 *
	 * @return void
	 */
	public function __construct() {
		$this->shortcodes = apply_filters(
			'storesuite_shortcodes',
			array(
				'storesuite_dashboard' => new Dashboard(),
			)
		);
	}

	/**
	 * Get registered shortcode classes
	 *
	 * @return array
	 */
	public function get_shortcodes() {
		return $this->shortcodes;
	}
}
