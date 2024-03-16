<?php
/**
 * Get template part for my shop front
 *
 * Looks at the theme directory first
 */
function msf_get_template_part( $slug, $name = '', $args = [] ) {
    $defaults = [
        'pro' => false,
    ];

    $args = wp_parse_args( $args, $defaults );

    if ( $args && is_array( $args ) ) {
        extract( $args ); // phpcs:ignore
    }

    $template = '';

    // Look in yourtheme/my-shop-front/slug-name.php and yourtheme/my-shop-front/slug.php
    $template_path = ! empty( $name ) ? "{$slug}-{$name}.php" : "{$slug}.php";
    $template      = locate_template( [ welabs_my_shop_front()->template_path() . $template_path ] );

    /**
     * Change template directory path filter
     */
    $template_path = apply_filters( 'msf_set_template_path', MY_SHOP_FRONT_TEMPLATE_DIR, $template, $args );

    // Get default slug-name.php
    if ( ! $template && $name && file_exists( $template_path . "/{$slug}-{$name}.php" ) ) {
        $template = $template_path . "/{$slug}-{$name}.php";
    }

    if ( ! $template && ! $name && file_exists( $template_path . "/{$slug}.php" ) ) {
        $template = $template_path . "/{$slug}.php";
    }

    // Allow 3rd party plugin filter template file from their plugin
    $template = apply_filters( 'msf_get_template_part', $template, $slug, $name );

    if ( $template ) {
        include $template;
    }
}




if ( ! function_exists( 'is_msf_endpoint_url' ) ) {

	/**
	 * Is_msf_endpoint_url - Check if an endpoint is showing.
	 *
	 * @param string|false $endpoint Whether endpoint.
	 * @return bool
	 */
	function is_msf_endpoint_url( $endpoint = false ) {
		global $wp;

		$msf_endpoints = welabs_my_shop_front()->get_msf_query()->query_vars;

		if ( false !== $endpoint ) {
			if ( ! isset( $msf_endpoints[ $endpoint ] ) ) {
				return false;
			} else {
				$endpoint_var = $msf_endpoints[ $endpoint ];
			}

			return isset( $wp->query_vars[ $endpoint_var ] );
		} else {
			foreach ( $msf_endpoints as $key => $value ) {
				if ( isset( $wp->query_vars[ $key ] ) ) {
					return true;
				}
			}

			return false;
		}
	}
}
