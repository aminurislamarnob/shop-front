<?php

namespace PluginizeLab\StoreSuite;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Common {
	/**
	 * The constructor.
	 */
	public function __construct() {
		add_filter( 'page_template', array( $this, 'storesuite_register_page_template' ) );
		add_filter( 'body_class', array( $this, 'storesuite_add_body_class' ) );
	}

	/**
	 * Register the page template for the endpoint.
	 *
	 * @param  string $template The template path.
	 * @return string
	 */
	public function storesuite_register_page_template( $template ) {
		if ( is_page() && storesuite_is_dashboard_page() ) {
			$custom_template = STORESUITE_TEMPLATE_DIR . '/page-template.php';
			if ( file_exists( $custom_template ) ) {
				return $custom_template;
			}
		}

		return $template;
	}

	/**
	 * Add body class for StoreSuite dashboard page.
	 *
	 * @param  array $classes Array of body classes.
	 * @return array
	 */
	public function storesuite_add_body_class( $classes ) {
		if ( storesuite_is_dashboard_page() ) {
			$classes[] = 'storesuite-main-dashboard';

			// Expose the active predefined palette for palette-specific styling.
			if ( 'predefined' === storesuite_get_option_by_key( 'storesuite_color_palette_mode' ) ) {
				$palette = storesuite_get_option_by_key( 'storesuite_color_palette_name' );
				if ( ! empty( $palette ) ) {
					$classes[] = 'storesuite-palette-' . sanitize_html_class( $palette );
				}
			}
		}

		return $classes;
	}
}
