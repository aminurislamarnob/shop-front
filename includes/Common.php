<?php

namespace PluginizeLab\ShopFront;

class Common {
	/**
	 * The constructor.
	 */
	public function __construct() {
		add_filter( 'page_template', array( $this, 'msf_register_page_template' ) );
		add_filter( 'body_class', array( $this, 'msf_add_body_class' ) );
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

	/**
	 * Add body class for MSF dashboard page.
	 *
	 * @param  array $classes Array of body classes.
	 * @return array
	 */
	public function msf_add_body_class( $classes ) {
		if ( is_msf_dashboard_page() ) {
			$classes[] = 'msf-main-dashboard';
		}

		return $classes;
	}
}
