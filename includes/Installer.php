<?php
namespace PluginizeLab\ShopFront;

class Installer {

	public function __construct() {
		add_filter( 'display_post_states', array( $this, 'add_display_post_states' ), 10, 2 );
	}

	/**
	 * Add a post display state for special MSF pages.
	 *
	 * @param array $post_states
	 * @param object $post
	 * @return array
	 */
	public function add_display_post_states( $post_states, $post ) {
		if ( Helper::msfc_get_page_id( 'myshopdashboard' ) === $post->ID ) {
			$post_states['msf_page_for_dashboard'] = __( 'My Shop Dashboard Page', 'shop-front' );
		}
		return $post_states;
	}

    /**
     * Create plugin main page with shortcode
     *
     * @return void
     */
    public static function create_plugin_page() {
        $my_shop_front_dashboard_shortcode = apply_filters( 'my_shop_front_dashboard_shortcode_tag', 'my_shop_front_dashboard' );

        /**
		 * Determines which pages are created during install.
		 */
		$pages = apply_filters(
			'my_shop_front_create_pages',
			array(
				'myshopdashboard' => array(
					'name'    => _x( 'my-shop-dashboard', 'Page slug', 'shop-front' ),
					'title'   => _x( 'My Shop Dashboard', 'Page title', 'shop-front' ),
					'content' => '<!-- wp:shortcode -->[' . $my_shop_front_dashboard_shortcode . ']<!-- /wp:shortcode -->',
				),
			)
		);

		foreach ( $pages as $key => $page ) {
			self::msf_create_page(
				esc_sql( $page['name'] ),
				'my_shop_front_' . $key . '_page_id',
				$page['title'],
				$page['content'],
				! empty( $page['parent'] ) ? wc_get_page_id( $page['parent'] ) : '',
				! empty( $page['post_status'] ) ? $page['post_status'] : 'publish'
			);
		}
    }

	/**
	 * Create a page and store the ID in an option.
	 *
	 * @param mixed  $slug Slug for the new page.
	 * @param string $option Option name to store the page's ID.
	 * @param string $page_title (default: '') Title for the new page.
	 * @param string $page_content (default: '') Content for the new page.
	 * @param int    $post_parent (default: 0) Parent for the new page.
	 * @param string $post_status (default: publish) The post status of the new page.
	 * @return int page ID.
	 */
	public static function msf_create_page( $slug, $option = '', $page_title = '', $page_content = '', $post_parent = 0, $post_status = 'publish' ) {
		global $wpdb;

		$option_value = get_option( $option );

		if ( $option_value > 0 ) {
			$page_object = get_post( $option_value );

			if ( $page_object && 'page' === $page_object->post_type && ! in_array( $page_object->post_status, array( 'pending', 'trash', 'future', 'auto-draft' ), true ) ) {
				// Valid page is already in place.
				return $page_object->ID;
			}
		}

		if ( strlen( $page_content ) > 0 ) {
			// Search for an existing page with the specified page content (typically a shortcode).
			$shortcode        = str_replace( array( '<!-- wp:shortcode -->', '<!-- /wp:shortcode -->' ), '', $page_content );
			$valid_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status NOT IN ( 'pending', 'trash', 'future', 'auto-draft' ) AND post_content LIKE %s LIMIT 1;", "%{$shortcode}%" ) );
		} else {
			// Search for an existing page with the specified page slug.
			$valid_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status NOT IN ( 'pending', 'trash', 'future', 'auto-draft' )  AND post_name = %s LIMIT 1;", $slug ) );
		}

		/* phpcs:disable */
		$valid_page_found = apply_filters( 'my_shop_front_create_page_id', $valid_page_found, $slug, $page_content );
		/* phpcs: enable */

		if ( $valid_page_found ) {
			if ( $option ) {
				update_option( $option, $valid_page_found );
			}
			return $valid_page_found;
		}

		// Search for a matching valid trashed page.
		if ( strlen( $page_content ) > 0 ) {
			// Search for an existing page with the specified page content (typically a shortcode).
			$trashed_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status = 'trash' AND post_content LIKE %s LIMIT 1;", "%{$page_content}%" ) );
		} else {
			// Search for an existing page with the specified page slug.
			$trashed_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status = 'trash' AND post_name = %s LIMIT 1;", $slug ) );
		}

		if ( $trashed_page_found ) {
			$page_id   = $trashed_page_found;
			$page_data = array(
			'ID'          => $page_id,
			'post_status' => $post_status,
			);
			wp_update_post( $page_data );
		} else {
			$page_data = array(
				'post_status'    => $post_status,
				'post_type'      => 'page',
				'post_author'    => 1,
				'post_name'      => $slug,
				'post_title'     => $page_title,
				'post_content'   => $page_content,
				'post_parent'    => $post_parent,
				'comment_status' => 'closed',
			);
			$page_id   = wp_insert_post( $page_data );

			/* phpcs:disable */
			do_action( 'my_shop_front_page_created', $page_id, $page_data );
			/* phpcs: enable */
		}

		if ( $option ) {
			update_option( $option, $page_id );
		}

		return $page_id;
	}
}
