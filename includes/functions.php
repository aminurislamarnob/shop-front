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
    $template      = locate_template( [ pluginizelab_shop_front()->template_path() . $template_path ] );

    /**
     * Change template directory path filter
     */
    $template_path = apply_filters( 'msf_set_template_path', SHOP_FRONT_TEMPLATE_DIR, $template, $args );

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

/**
 * Is_msf_endpoint_url - Check if an endpoint is showing.
 *
 * @param string|false $endpoint Whether endpoint.
 * @return bool
 */
if ( ! function_exists( 'is_msf_endpoint_url' ) ) {
	function is_msf_endpoint_url( $endpoint = false ) {
		global $wp;

		$msf_endpoints = pluginizelab_shop_front()->get_msf_query()->query_vars;

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

/**
 * Get user-friendly post status based on post
 *
 * @param string $status
 *
 * @return string|array
 */
function msf_get_post_status( $status = '' ) {
    $statuses = apply_filters(
        'msf_get_post_status', [
            'publish' => __( 'Online', 'shop-front' ),
            'draft'   => __( 'Draft', 'shop-front' ),
            'pending' => __( 'Pending Review', 'shop-front' ),
            'future'  => __( 'Scheduled', 'shop-front' ),
        ]
    );

    if ( $status ) {
        return isset( $statuses[ $status ] ) ? $statuses[ $status ] : '';
    }

    return $statuses;
}

/**
 * Get user friendly post status label based class
 *
 * @param string $status
 *
 * @return string|array
 */
function msf_get_post_status_label_class( $status = '' ) {
    $labels = apply_filters(
        'msf_get_post_status_label_class', [
            'publish' => 'msf-label-success',
            'draft'   => 'msf-label-default',
            'pending' => 'msf-label-danger',
            'future'  => 'msf-label-warning',
        ]
    );

    if ( $status ) {
        return isset( $labels[ $status ] ) ? $labels[ $status ] : '';
    }

    return $labels;
}


/**
 * Get product type
 *
 * @param object $product
 *
 * @return string
 */
function msf_get_product_type( $product ) {
    $product_type = $product->get_type();
    if ( $product_type === 'grouped' ) {
        echo '<span class="product-type grouped">' . esc_html__( 'Grouped', 'shop-front' ) . '</span>';
    } elseif ( $product_type === 'external' ) {
        echo '<span class="product-type external">' . esc_html__( 'External', 'shop-front' ) . '</span>';
    } elseif ( $product_type === 'simple' ) {
        if ( $product->is_virtual() ) {
            echo '<span class="product-type virtual">' . esc_html__( 'Virtual', 'shop-front' ) . '</span>';
        } elseif ( $product->is_downloadable() ) {
            echo '<span class="product-type downloadable">' . esc_html__( 'Downloadable', 'shop-front' ) . '</span>';
        } else {
            echo '<span class="product-type simple">' . esc_html__( 'Simple', 'shop-front' ) . '</span>';
        }
    } elseif ( $product_type === 'variable' ) {
        echo '<span class="product-type variable">' . esc_html__( 'Variable', 'shop-front' ) . '</span>';
    }
}
