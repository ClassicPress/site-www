<?php
/**
 * REST API Coupons controller
 *
 * Handles requests to the /coupons endpoint.
 *
 * @package ClassicCommerce/API
 * @since   WC-2.6.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * REST API Coupons controller class.
 *
 * @package ClassicCommerce/API
 * @extends WC_REST_Coupons_V2_Controller
 */
class WC_REST_Coupons_Controller extends WC_REST_Coupons_V2_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc/v3';
}
