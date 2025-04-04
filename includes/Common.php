<?php

namespace PluginizeLab\ShopFront;

class Common {
	/**
	 * The constructor.
	 */
	public function __construct() {
		add_filter( 'page_template', array( $this, 'msf_register_page_template' ) );
	}

	/**
	 * Register the page template for the endpoint.
	 *
	 * @param  string $template The template path.
	 * @return string
	 */
	public function msf_register_page_template( $template ) {
		if ( is_page() && is_msf_dashboard_page() ) {
			$custom_template = SHOP_FRONT_TEMPLATE_DIR . '/page-template.php';
			if ( file_exists( $custom_template ) ) {
				return $custom_template;
			}
		}

		return $template;
	}
}
