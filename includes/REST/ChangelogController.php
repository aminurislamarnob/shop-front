<?php

namespace PluginizeLab\StoreSuite\REST;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_REST_Controller;
use WP_REST_Server;

/**
 * Changelog REST API controller.
 *
 * Reads the plugin changelog from readme.txt and exposes it to the
 * admin settings app.
 */
class ChangelogController extends WP_REST_Controller {

	/**
	 * The namespace of this controller's route.
	 *
	 * @var string
	 */
	protected $namespace;

	/**
	 * The base of this controller's route.
	 *
	 * @var string
	 */
	protected $rest_base;

	/**
	 * Constructor.
	 *
	 * Sets the namespace and rest base for the controller.
	 */
	public function __construct() {
		$this->namespace = 'storesuite/v1';
		$this->rest_base = 'changelog';
	}

	/**
	 * Register the routes for the objects of the controller.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_changelog' ),
					'permission_callback' => array( $this, 'get_changelog_permissions_check' ),
					'args'                => array(),
				),
			)
		);
	}

	/**
	 * Get the changelog parsed from readme.txt.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error The response or error object.
	 */
	public function get_changelog( $request ) {
		return rest_ensure_response(
			array(
				'current_version' => STORESUITE_PLUGIN_VERSION,
				'releases'        => $this->parse_readme_changelog(),
			)
		);
	}

	/**
	 * Parse the "== Changelog ==" section of readme.txt into releases.
	 *
	 * @return array[] List of releases: [ 'version' => string, 'entries' => string[] ].
	 */
	private function parse_readme_changelog() {
		$readme_file = trailingslashit( STORESUITE_DIR ) . 'readme.txt';

		if ( ! is_readable( $readme_file ) ) {
			return array();
		}

		$readme = file_get_contents( $readme_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		if ( false === $readme || ! preg_match( '/^==\s*Changelog\s*==\s*$(.*?)(?=^==\s[^=]|\z)/msi', $readme, $matches ) ) {
			return array();
		}

		$releases = array();
		$blocks   = preg_split( '/^=\s*(.+?)\s*=\s*$/m', trim( $matches[1] ), -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );

		// preg_split with DELIM_CAPTURE yields [ version, body, version, body, ... ].
		$count = count( $blocks );
		for ( $i = 0; $i + 1 < $count; $i += 2 ) {
			$entries = array();

			foreach ( preg_split( '/\r\n|\r|\n/', $blocks[ $i + 1 ] ) as $line ) {
				$line = trim( $line );

				if ( '' === $line ) {
					continue;
				}

				$entries[] = preg_replace( '/^\*\s*/', '', $line );
			}

			if ( ! empty( $entries ) ) {
				$releases[] = array(
					'version' => $blocks[ $i ],
					'entries' => $entries,
				);
			}
		}

		return $releases;
	}

	/**
	 * Check if a given request has access to get the changelog.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool True if the request has access, false otherwise.
	 */
	public function get_changelog_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}
}
