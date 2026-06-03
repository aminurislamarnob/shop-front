<?php
/**
 * Modules REST API controller.
 *
 * @package StoreSuite
 */

namespace PluginizeLab\StoreSuite\REST;

use WP_Error;
use WP_REST_Controller;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Exposes the StoreSuite module registry to the admin React app.
 *
 * Routes:
 *   GET  /storesuite/v1/modules
 *   POST /storesuite/v1/modules/{slug}/activate
 *   POST /storesuite/v1/modules/{slug}/deactivate
 */
class ModulesController extends WP_REST_Controller {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->namespace = 'storesuite/v1';
		$this->rest_base = 'modules';
	}

	/**
	 * Register routes.
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
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'permissions_check' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<slug>[A-Za-z0-9_\-]+)/activate',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'activate_item' ),
					'permission_callback' => array( $this, 'permissions_check' ),
					'args'                => array(
						'slug' => array(
							'type'     => 'string',
							'required' => true,
						),
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<slug>[A-Za-z0-9_\-]+)/deactivate',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'deactivate_item' ),
					'permission_callback' => array( $this, 'permissions_check' ),
					'args'                => array(
						'slug' => array(
							'type'     => 'string',
							'required' => true,
						),
					),
				),
			)
		);
	}

	/**
	 * GET handler — list every discovered module with metadata + active state.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_items( $request ) {
		$manager = $this->get_manager();
		$items   = array();

		foreach ( $manager->get_all() as $slug => $module ) {
			$items[] = array(
				'slug'        => $slug,
				'name'        => $module->get_name(),
				'description' => $module->get_description(),
				'version'     => $module->get_version(),
				'active'      => $manager->is_active( $slug ),
			);
		}

		return rest_ensure_response( $items );
	}

	/**
	 * POST handler — activate a module.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|WP_Error
	 */
	public function activate_item( $request ) {
		$slug = (string) $request->get_param( 'slug' );

		if ( ! $this->get_manager()->activate( $slug ) ) {
			return new WP_Error(
				'storesuite_module_not_found',
				__( 'Module not found.', 'storesuite' ),
				array( 'status' => 404 )
			);
		}

		return $this->item_response( $slug );
	}

	/**
	 * POST handler — deactivate a module.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|WP_Error
	 */
	public function deactivate_item( $request ) {
		$slug = (string) $request->get_param( 'slug' );

		if ( ! $this->get_manager()->deactivate( $slug ) ) {
			return new WP_Error(
				'storesuite_module_not_found',
				__( 'Module not found.', 'storesuite' ),
				array( 'status' => 404 )
			);
		}

		return $this->item_response( $slug );
	}

	/**
	 * Build a single-item response after a state change.
	 *
	 * @param string $slug Module slug.
	 * @return \WP_REST_Response
	 */
	private function item_response( $slug ) {
		$manager = $this->get_manager();
		$modules = $manager->get_all();
		$module  = $modules[ $slug ];

		return rest_ensure_response(
			array(
				'slug'        => $slug,
				'name'        => $module->get_name(),
				'description' => $module->get_description(),
				'version'     => $module->get_version(),
				'active'      => $manager->is_active( $slug ),
			)
		);
	}

	/**
	 * Permission check — same gate as the rest of the admin app.
	 *
	 * @return bool
	 */
	public function permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Resolve the module manager from the StoreSuite container.
	 *
	 * @return \PluginizeLab\StoreSuite\Module\Manager
	 */
	private function get_manager() {
		return pluginizelab_storesuite()->modules;
	}
}
