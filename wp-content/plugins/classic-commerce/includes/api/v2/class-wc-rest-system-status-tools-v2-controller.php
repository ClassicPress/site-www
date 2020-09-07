<?php
/**
 * REST API WC System Status Tools Controller
 *
 * Handles requests to the /system_status/tools/* endpoints.
 *
 * @package ClassicCommerce/API
 * @since   WC-3.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * System status tools controller.
 *
 * @package ClassicCommerce/API
 * @extends WC_REST_Controller
 */
class WC_REST_System_Status_Tools_V2_Controller extends WC_REST_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc/v2';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'system_status/tools';

	/**
	 * Register the routes for /system_status/tools/*.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace, '/' . $this->rest_base, array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace, '/' . $this->rest_base . '/(?P<id>[\w-]+)', array(
				'args'   => array(
					'id' => array(
						'description' => __( 'Unique identifier for the resource.', 'classic-commerce' ),
						'type'        => 'string',
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Check whether a given request has permission to view system status tools.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! wc_rest_check_manager_permissions( 'system_status', 'read' ) ) {
			return new WP_Error( 'woocommerce_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'classic-commerce' ), array( 'status' => rest_authorization_required_code() ) );
		}
		return true;
	}

	/**
	 * Check whether a given request has permission to view a specific system status tool.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_item_permissions_check( $request ) {
		if ( ! wc_rest_check_manager_permissions( 'system_status', 'read' ) ) {
			return new WP_Error( 'woocommerce_rest_cannot_view', __( 'Sorry, you cannot view this resource.', 'classic-commerce' ), array( 'status' => rest_authorization_required_code() ) );
		}
		return true;
	}

	/**
	 * Check whether a given request has permission to execute a specific system status tool.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function update_item_permissions_check( $request ) {
		if ( ! wc_rest_check_manager_permissions( 'system_status', 'edit' ) ) {
			return new WP_Error( 'woocommerce_rest_cannot_update', __( 'Sorry, you cannot update resource.', 'classic-commerce' ), array( 'status' => rest_authorization_required_code() ) );
		}
		return true;
	}

	/**
	 * A list of available tools for use in the system status section.
	 * 'button' becomes 'action' in the API.
	 *
	 * @return array
	 */
	public function get_tools() {
		$tools = array(
			'clear_transients'                   => array(
				'name'   => __( 'Classic Commerce transients', 'classic-commerce' ),
				'button' => __( 'Clear transients', 'classic-commerce' ),
				'desc'   => __( 'This tool will clear the product/shop transients cache.', 'classic-commerce' ),
			),
			'clear_expired_transients'           => array(
				'name'   => __( 'Expired transients', 'classic-commerce' ),
				'button' => __( 'Clear transients', 'classic-commerce' ),
				'desc'   => __( 'This tool will clear ALL expired transients.', 'classic-commerce' ),
			),
			'delete_orphaned_variations'         => array(
				'name'   => __( 'Orphaned variations', 'classic-commerce' ),
				'button' => __( 'Delete orphaned variations', 'classic-commerce' ),
				'desc'   => __( 'This tool will delete all variations which have no parent.', 'classic-commerce' ),
			),
			'clear_expired_download_permissions' => array(
				'name'   => __( 'Used-up download permissions', 'classic-commerce' ),
				'button' => __( 'Clean up download permissions', 'classic-commerce' ),
				'desc'   => __( 'This tool will delete expired download permissions and permissions with 0 remaining downloads.', 'classic-commerce' ),
			),
			'add_order_indexes'                  => array(
				'name'   => __( 'Order address indexes', 'classic-commerce' ),
				'button' => __( 'Index orders', 'classic-commerce' ),
				'desc'   => __( 'This tool will add address indexes to orders that do not have them yet. This improves order search results.', 'classic-commerce' ),
			),
			'recount_terms'                      => array(
				'name'   => __( 'Term counts', 'classic-commerce' ),
				'button' => __( 'Recount terms', 'classic-commerce' ),
				'desc'   => __( 'This tool will recount product terms - useful when changing your settings in a way which hides products from the catalog.', 'classic-commerce' ),
			),
			'reset_roles'                        => array(
				'name'   => __( 'Capabilities', 'classic-commerce' ),
				'button' => __( 'Reset capabilities', 'classic-commerce' ),
				'desc'   => __( 'This tool will reset the admin, customer and shop_manager roles to default. Use this if your users cannot access all of the Classic Commerce admin pages.', 'classic-commerce' ),
			),
			'clear_sessions'                     => array(
				'name'   => __( 'Clear customer sessions', 'classic-commerce' ),
				'button' => __( 'Clear', 'classic-commerce' ),
				'desc'   => sprintf(
					'<strong class="red">%1$s</strong> %2$s',
					__( 'Note:', 'classic-commerce' ),
					__( 'This tool will delete all customer session data from the database, including current carts and saved carts in the database.', 'classic-commerce' )
				),
			),
			'install_pages'                      => array(
				'name'   => __( 'Create default Classic Commerce pages', 'classic-commerce' ),
				'button' => __( 'Create pages', 'classic-commerce' ),
				'desc'   => sprintf(
					'<strong class="red">%1$s</strong> %2$s',
					__( 'Note:', 'classic-commerce' ),
					__( 'This tool will install all the missing Classic Commerce pages. Pages already defined and set up will not be replaced.', 'classic-commerce' )
				),
			),
			'delete_taxes'                       => array(
				'name'   => __( 'Delete Classic Commerce tax rates', 'classic-commerce' ),
				'button' => __( 'Delete tax rates', 'classic-commerce' ),
				'desc'   => sprintf(
					'<strong class="red">%1$s</strong> %2$s',
					__( 'Note:', 'classic-commerce' ),
					__( 'This option will delete ALL of your tax rates, use with caution. This action cannot be reversed.', 'classic-commerce' )
				),
			),
			'regenerate_thumbnails'              => array(
				'name'   => __( 'Regenerate shop thumbnails', 'classic-commerce' ),
				'button' => __( 'Regenerate', 'classic-commerce' ),
				'desc'   => __( 'This will regenerate all shop thumbnails to match your theme and/or image settings.', 'classic-commerce' ),
			),
			'db_update_routine'                  => array(
				'name'   => __( 'Update database', 'classic-commerce' ),
				'button' => __( 'Update database', 'classic-commerce' ),
				'desc'   => sprintf(
					'<strong class="red">%1$s</strong> %2$s',
					__( 'Note:', 'classic-commerce' ),
					__( 'This tool will update your Classic Commerce database to the latest version. Please ensure you make sufficient backups before proceeding.', 'classic-commerce' )
				),
			)
		);

		if ( ! apply_filters( 'woocommerce_background_image_regeneration', true ) ) {
			unset( $tools['regenerate_thumbnails'] );
		}

		return apply_filters( 'woocommerce_debug_tools', $tools );
	}

	/**
	 * Get a list of system status tools.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		$tools = array();
		foreach ( $this->get_tools() as $id => $tool ) {
			$tools[] = $this->prepare_response_for_collection(
				$this->prepare_item_for_response(
					array(
						'id'          => $id,
						'name'        => $tool['name'],
						'action'      => $tool['button'],
						'description' => $tool['desc'],
					), $request
				)
			);
		}

		$response = rest_ensure_response( $tools );
		return $response;
	}

	/**
	 * Return a single tool.
	 *
	 * @param  WP_REST_Request $request Request data.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {
		$tools = $this->get_tools();
		if ( empty( $tools[ $request['id'] ] ) ) {
			return new WP_Error( 'woocommerce_rest_system_status_tool_invalid_id', __( 'Invalid tool ID.', 'classic-commerce' ), array( 'status' => 404 ) );
		}
		$tool = $tools[ $request['id'] ];
		return rest_ensure_response(
			$this->prepare_item_for_response(
				array(
					'id'          => $request['id'],
					'name'        => $tool['name'],
					'action'      => $tool['button'],
					'description' => $tool['desc'],
				), $request
			)
		);
	}

	/**
	 * Update (execute) a tool.
	 *
	 * @param  WP_REST_Request $request Request data.
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_item( $request ) {
		$tools = $this->get_tools();
		if ( empty( $tools[ $request['id'] ] ) ) {
			return new WP_Error( 'woocommerce_rest_system_status_tool_invalid_id', __( 'Invalid tool ID.', 'classic-commerce' ), array( 'status' => 404 ) );
		}

		$tool = $tools[ $request['id'] ];
		$tool = array(
			'id'          => $request['id'],
			'name'        => $tool['name'],
			'action'      => $tool['button'],
			'description' => $tool['desc'],
		);

		$execute_return = $this->execute_tool( $request['id'] );
		$tool           = array_merge( $tool, $execute_return );

		/**
		 * Fires after a Classic Commerce REST system status tool has been executed.
		 *
		 * @param array           $tool    Details about the tool that has been executed.
		 * @param WP_REST_Request $request The current WP_REST_Request object.
		 */
		do_action( 'woocommerce_rest_insert_system_status_tool', $tool, $request );

		$request->set_param( 'context', 'edit' );
		$response = $this->prepare_item_for_response( $tool, $request );
		return rest_ensure_response( $response );
	}

	/**
	 * Prepare a tool item for serialization.
	 *
	 * @param  array           $item     Object.
	 * @param  WP_REST_Request $request  Request object.
	 * @return WP_REST_Response $response Response data.
	 */
	public function prepare_item_for_response( $item, $request ) {
		$context = empty( $request['context'] ) ? 'view' : $request['context'];
		$data    = $this->add_additional_fields_to_object( $item, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		$response = rest_ensure_response( $data );

		$response->add_links( $this->prepare_links( $item['id'] ) );

		return $response;
	}

	/**
	 * Get the system status tools schema, conforming to JSON Schema.
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'system_status_tool',
			'type'       => 'object',
			'properties' => array(
				'id'          => array(
					'description' => __( 'A unique identifier for the tool.', 'classic-commerce' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_title',
					),
				),
				'name'        => array(
					'description' => __( 'Tool name.', 'classic-commerce' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'action'      => array(
					'description' => __( 'What running the tool will do.', 'classic-commerce' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'description' => array(
					'description' => __( 'Tool description.', 'classic-commerce' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'success'     => array(
					'description' => __( 'Did the tool run successfully?', 'classic-commerce' ),
					'type'        => 'boolean',
					'context'     => array( 'edit' ),
				),
				'message'     => array(
					'description' => __( 'Tool return message.', 'classic-commerce' ),
					'type'        => 'string',
					'context'     => array( 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}

	/**
	 * Prepare links for the request.
	 *
	 * @param string $id ID.
	 * @return array
	 */
	protected function prepare_links( $id ) {
		$base  = '/' . $this->namespace . '/' . $this->rest_base;
		$links = array(
			'item' => array(
				'href'       => rest_url( trailingslashit( $base ) . $id ),
				'embeddable' => true,
			),
		);

		return $links;
	}

	/**
	 * Get any query params needed.
	 *
	 * @return array
	 */
	public function get_collection_params() {
		return array(
			'context' => $this->get_context_param( array( 'default' => 'view' ) ),
		);
	}

	/**
	 * Actually executes a tool.
	 *
	 * @param  string $tool Tool.
	 * @return array
	 */
	public function execute_tool( $tool ) {
		global $wpdb;
		$ran = true;
		switch ( $tool ) {
			case 'clear_transients':
				wc_delete_product_transients();
				wc_delete_shop_order_transients();

				$attribute_taxonomies = wc_get_attribute_taxonomies();

				if ( $attribute_taxonomies ) {
					foreach ( $attribute_taxonomies as $attribute ) {
						delete_transient( 'wc_layered_nav_counts_pa_' . $attribute->attribute_name );
					}
				}

				WC_Cache_Helper::get_transient_version( 'shipping', true );
				$message = __( 'Product transients cleared', 'classic-commerce' );
				break;

			case 'clear_expired_transients':
				/* translators: %d: amount of expired transients */
				$message = sprintf( __( '%d transients rows cleared', 'classic-commerce' ), wc_delete_expired_transients() );
				break;

			case 'delete_orphaned_variations':
				// Delete orphans.
				$result = absint(
					$wpdb->query(
						"DELETE products
					FROM {$wpdb->posts} products
					LEFT JOIN {$wpdb->posts} wp ON wp.ID = products.post_parent
					WHERE wp.ID IS NULL AND products.post_type = 'product_variation';"
					)
				);
				/* translators: %d: amount of orphaned variations */
				$message = sprintf( __( '%d orphaned variations deleted', 'classic-commerce' ), $result );
				break;

			case 'clear_expired_download_permissions':
				// Delete expired download permissions and ones with 0 downloads remaining.
				$result = absint(
					$wpdb->query(
						$wpdb->prepare(
							"DELETE FROM {$wpdb->prefix}woocommerce_downloadable_product_permissions
							WHERE ( downloads_remaining != '' AND downloads_remaining = 0 ) OR ( access_expires IS NOT NULL AND access_expires < %s )",
							date( 'Y-m-d', current_time( 'timestamp' ) )
						)
					)
				);
				/* translators: %d: amount of permissions */
				$message = sprintf( __( '%d permissions deleted', 'classic-commerce' ), $result );
				break;

			case 'add_order_indexes':
				/*
				 * Add billing and shipping address indexes containing the customer name for orders
				 * that don't have address indexes yet.
				 */
				$sql   = "INSERT INTO {$wpdb->postmeta}( post_id, meta_key, meta_value )
					SELECT post_id, '%s', GROUP_CONCAT( meta_value SEPARATOR ' ' )
					FROM {$wpdb->postmeta}
					WHERE meta_key IN ( '%s', '%s' )
					AND post_id IN ( SELECT DISTINCT post_id FROM {$wpdb->postmeta}
						WHERE post_id NOT IN ( SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='%s' )
						AND post_id IN ( SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='%s' ) )
					GROUP BY post_id";
				$rows  = $wpdb->query( $wpdb->prepare( $sql, '_billing_address_index', '_billing_first_name', '_billing_last_name', '_billing_address_index', '_billing_last_name' ) ); // WPCS: unprepared SQL ok.
				$rows += $wpdb->query( $wpdb->prepare( $sql, '_shipping_address_index', '_shipping_first_name', '_shipping_last_name', '_shipping_address_index', '_shipping_last_name' ) ); // WPCS: unprepared SQL ok.

				/* translators: %d: amount of indexes */
				$message = sprintf( __( '%d indexes added', 'classic-commerce' ), $rows );
				break;

			case 'reset_roles':
				// Remove then re-add caps and roles.
				WC_Install::remove_roles();
				WC_Install::create_roles();
				$message = __( 'Roles successfully reset', 'classic-commerce' );
				break;

			case 'recount_terms':
				$product_cats = get_terms(
					'product_cat', array(
						'hide_empty' => false,
						'fields'     => 'id=>parent',
					)
				);
				_wc_term_recount( $product_cats, get_taxonomy( 'product_cat' ), true, false );
				$product_tags = get_terms(
					'product_tag', array(
						'hide_empty' => false,
						'fields'     => 'id=>parent',
					)
				);
				_wc_term_recount( $product_tags, get_taxonomy( 'product_tag' ), true, false );
				$message = __( 'Terms successfully recounted', 'classic-commerce' );
				break;

			case 'clear_sessions':
				$wpdb->query( "TRUNCATE {$wpdb->prefix}woocommerce_sessions" );
				$result = absint( $wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key='_woocommerce_persistent_cart_" . get_current_blog_id() . "';" ) ); // WPCS: unprepared SQL ok.
				wp_cache_flush();
				/* translators: %d: amount of sessions */
				$message = sprintf( __( 'Deleted all active sessions, and %d saved carts.', 'classic-commerce' ), absint( $result ) );
				break;

			case 'install_pages':
				WC_Install::create_pages();
				$message = __( 'All missing Classic Commerce pages successfully installed', 'classic-commerce' );
				break;

			case 'delete_taxes':
				$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}woocommerce_tax_rates;" );
				$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}woocommerce_tax_rate_locations;" );
				WC_Cache_Helper::incr_cache_prefix( 'taxes' );
				$message = __( 'Tax rates successfully deleted', 'classic-commerce' );
				break;
        
			case 'regenerate_thumbnails':
				WC_Regenerate_Images::queue_image_regeneration();
				$message = __( 'Thumbnail regeneration has been scheduled to run in the background.', 'classic-commerce' );
				break;

			case 'db_update_routine':
				$blog_id = get_current_blog_id();
				// Used to fire an action added in WP_Background_Process::_construct() that calls WP_Background_Process::handle_cron_healthcheck().
				// This method will make sure the database updates are executed even if cron is disabled. Nothing will happen if the updates are already running.
				do_action( 'wp_' . $blog_id . '_wc_updater_cron' );
				$message = __( 'Database upgrade routine has been scheduled to run in the background.', 'classic-commerce' );
				break;

			default:
				$tools = $this->get_tools();
				if ( isset( $tools[ $tool ]['callback'] ) ) {
					$callback = $tools[ $tool ]['callback'];
					$return   = call_user_func( $callback );
					if ( is_string( $return ) ) {
						$message = $return;
					} elseif ( false === $return ) {
						$callback_string = is_array( $callback ) ? get_class( $callback[0] ) . '::' . $callback[1] : $callback;
						$ran             = false;
						/* translators: %s: callback string */
						$message = sprintf( __( 'There was an error calling %s', 'classic-commerce' ), $callback_string );
					} else {
						$message = __( 'Tool ran.', 'classic-commerce' );
					}
				} else {
					$ran     = false;
					$message = __( 'There was an error calling this tool. There is no callback present.', 'classic-commerce' );
				}
				break;
		}

		return array(
			'success' => $ran,
			'message' => $message,
		);
	}
}
