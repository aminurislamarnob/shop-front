<?php

namespace PluginizeLab\ShopFront;

class TemplateParts {
	/**
	 * The constructor.
	 */
	public function __construct() {
		add_action( 'msf_dashboard_content_before', array( $this, 'dashboard_header_template' ), 1 );
	}

	/**
	 * Load the dashboard header template.
	 *
	 * @return void
	 */
	public function dashboard_header_template() {
		msf_get_template_part( 'dashboard-header' );
	}
}
