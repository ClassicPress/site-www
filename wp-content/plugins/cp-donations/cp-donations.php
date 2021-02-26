<?php
/**
 * Plugin Name: Donations for ClassicPress
 * Plugin URI: https://github.com/timbocode/cc-donations
 * Description: Frontend interface for donations and subscriptions
 * Version: 2.0.1
 * Author: timbocode
 * Author URI: https://github.com/timbocode
 * Text Domain: cp_donations_domain
 * Domain Path: /languages/
 * Requires at least: 1.0.0
 * Requires PHP: 7.2
 * WC requires at least: 3.5.3
 * WC tested up to: 3.5.3
 * CC requires at least: 1.0.0
 * CC tested up to: 1.0.0
 * License: GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */


defined( 'ABSPATH' ) || exit;


/**
 * Main CP_Donations class.
 */
class CP_Donations {

	public $version = '2.0.1';
	public $db_version = '1';
	private $min_cp_version = '1.0.2';
	private $php_version = '7.0';

	protected static $instance = null;


	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	public function __construct() {
		$this->cpd_define_constants();
		include_once $this->plugin_path() . '/includes/class-cpd-admin-notices.php';

		if ( ! $this->cpd_requirements() ) {
			return;
		}

		$this->cpd_includes();
		$this->cpd_main_actions();

		CPD_Custom_Amount_Display::instance();
		CPD_Custom_Amount_Cart::instance();

		//do_action( 'cpd_loaded' );
	}


	public function cpd_define_constants() {
		if ( ! defined( 'CC_PLUGIN_DIR' ) ) {
			define( 'CC_PLUGIN_DIR', plugin_dir_path( __DIR__ ) . 'classic-commerce' );
		}
		if ( ! defined( 'CPD_PLUGIN_FILE' ) ) {
			define( 'CPD_PLUGIN_FILE', __FILE__ );
		}
		if ( ! defined( 'CPD_VERSION' ) ) {
			define( 'CPD_VERSION', $this->version );
		}
	}


	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', CPD_PLUGIN_FILE ) );
	}


	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( CPD_PLUGIN_FILE ) );
	}


	public function cpd_requirements() {
		if ( ! class_exists( 'WC_Product' ) || ! function_exists( 'WC' ) ) {
			$notice = sprintf(__( 'Donations for ClassicPress: Classic Commerce must be installed and active.', 'cp_donations_domain' ) );
			CPD_Admin_Notices::add_notice( $notice, 'error' );
			return false;
		}

		if ( ! file_exists( WP_PLUGIN_DIR . '/classic-commerce/classic-commerce.php' ) || !is_plugin_active( 'classic-commerce/classic-commerce.php' ) ) {
			$notice = sprintf(__( 'Donations for ClassicPress: Classic Commerce must be installed and active.', 'cp_donations_domain' ) );
			CPD_Admin_Notices::add_notice( $notice, 'error' );
			return false;
		}

		// Check ClassicPress version.
		if ( version_compare( get_bloginfo( 'version' ), $this->min_cp_version, '<' ) ) {
			$notice = sprintf(
				// Translators: %s ClassicPress version number.
				__( 'CP Donations requires ClassicPress version %s or above. Please update ClassicPress.', 'cp_donations_domain' ),
				$this->min_cp_version
			);
			CPD_Admin_Notices::add_notice( $notice, 'error' );
			return false;
		}

		// Check PHP version.
		if ( version_compare( phpversion(), $this->php_version, '<' ) ) {
			$notice = sprintf(
				// Translators: %s PHP version number.
				__( 'CP Donations requires PHP version %s or above. Please update PHP.', 'cp_donations_domain' ),
				$this->php_version
			);
			CPD_Admin_Notices::add_notice( $notice, 'error' );
			return false;
		}

		return true;
	}


	public function cpd_includes() {
		include_once $this->plugin_path() . '/includes/class-cpd-custom-amount-helpers.php';

		// Admin
		if ( is_admin() ) {
			include_once $this->plugin_path() . '/includes/class-cpd-admin-notices.php';
			include_once $this->plugin_path() . '/includes/class-cpd-custom-amount-admin.php';
			include_once $this->plugin_path() . '/includes/class-cpd-custom-amount-product-meta.php';
		}
		include_once $this->plugin_path() . '/includes/class-cpd-custom-amount-display.php';
		include_once $this->plugin_path() . '/includes/class-cpd-custom-amount-shortcode.php';
		include_once $this->plugin_path() . '/includes/class-cpd-custom-amount-cart.php';

		// TODO: Add Update Client
		include_once $this->plugin_path() . '/includes/class-cpd-update-client.php';
	}


	private function cpd_main_actions() {
		add_filter( 'woocommerce_add_to_cart_redirect', array( $this, 'cpd_add_to_cart_redirect' ) );
		add_action( 'wp_loaded', array( $this, 'cpd_woocommerce_empty_cart_action' ), 20 );
		add_filter( 'wc_add_to_cart_message_html', '__return_null' );
		add_filter( 'default_checkout_billing_country', '__return_empty_string' );
	}


	// Redirects to checkout page after add to cart / donate button clicked
	function cpd_add_to_cart_redirect() {
		global $woocommerce;
        return wc_get_checkout_url();
	}


	function cpd_woocommerce_empty_cart_action() {
		if ( isset( $_GET['empty_cart'] ) && 'yes' === esc_html( $_GET['empty_cart'] ) ) {
			WC()->cart->empty_cart();
			$referer  = wp_get_referer() ? esc_url( remove_query_arg( 'empty_cart' ) ) : wc_get_cart_url();
			wp_safe_redirect( $referer );
		}
	}

}  // End class



// Returns the main instance of CP_Donations.
function CP_Donations() {
	return CP_Donations::instance();
}
// Let's go.
CP_Donations();

