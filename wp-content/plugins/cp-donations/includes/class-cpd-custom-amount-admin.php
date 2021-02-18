<?php
/**
 * Handle admin display
 *
 * Adds a setting tab, quick edit, bulk edit, loads metabox class.
 */

defined( 'ABSPATH' ) || exit;

class CPD_Custom_Amount_Admin {

	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'admin_includes' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'meta_box_script' ), 20 );
		// Edit Products screen.
		add_filter( 'woocommerce_get_price_html', array( __CLASS__, 'admin_price_html' ), 20, 2 );
		// Product Filters.
		add_filter( 'woocommerce_product_filters', array( __CLASS__, 'product_filters' ) );

		// Admin Settings via settings API.
		add_filter( 'woocommerce_get_settings_pages', array( __CLASS__, 'add_settings_page' ) );
	}


	public static function admin_includes() {
		include_once CP_Donations()->plugin_path() . '/includes/class-cpd-custom-amount-product-meta.php';
	}


	// Javascript to handle the CPD metabox options
	public static function meta_box_script( $hook ) {
		// Check if on edit post page (post.php or new-post.php).
		if ( ! in_array( $hook, array( 'post-new.php', 'post.php' ) ) ) {
			return;
		}

		// Now check to see if the $post type is 'product'.
		global $post;
		if ( ! isset( $post ) || 'product' !== $post->post_type ) {
			return;
		}

		wp_enqueue_script( 'cpd_metabox', CP_Donations()->plugin_url() . '/assets/js/admin/cpd-metabox.js', array( 'jquery' ), CP_Donations()->version, true );

		$strings = [
			'enter_value'    => __( 'Enter a value', 'cp_donations_domain' ),
			'price_adjust'   => __( 'Enter a value (fixed or %)', 'cp_donations_domain' ),
			'simple_types'   => CPD_Custom_Amount_Helpers::get_simple_supported_types(),
			'variable_types' => CPD_Custom_Amount_Helpers::get_variable_supported_types(),
		];

		wp_localize_script( 'cpd_metabox', 'cpd_metabox', $strings );
	}


	/**
	 * ---------------------------------------------------------------------------------
	 * Product Overview - edit columns
	 * ---------------------------------------------------------------------------------
	 */

	// Change price in edit screen to CPD
	public static function admin_price_html( $price, $product ) {
		if ( CPD_Custom_Amount_Helpers::is_cpd( $product ) && ! isset( $product->is_filtered_price_html ) ) {
			$price = wc_get_price_html_from_text() . CPD_Custom_Amount_Helpers::get_price_string( $product, 'minimum', true );
		} elseif ( CPD_Custom_Amount_Helpers::has_cpd_variation( $product ) && ! isset( $product->is_filtered_price_html ) ) {
			$price = wc_get_price_html_from_text() . CPD_Custom_Amount_Helpers::get_price_string( $product, 'minimum-variation', true );
		}

		return $price;
	}


	// Add CPD as option to product filters in admin
	public static function product_filters( $output ) {
		global $wp_query;

		$startpos = strpos( $output, '<select name="product_type"' );

		if ( false !== $startpos ) {
			$endpos = strpos( $output, '</select>', $startpos );

			if ( false !== $endpos ) {
				$current	= isset( $wp_query->query['product_type'] ) ? $wp_query->query['product_type'] : false;
				$cpd_option	= sprintf(
					'<option value="custom-amount" %s > %s</option>',
					selected( 'custom-amount', $current, false ),
					__( 'CP Donations', 'cp_donations_domain' )
				);

				$output = substr_replace( $output, $cpd_option, $endpos, 0 );
			}
		}

		return $output;
	}


	// Include the admin settings page class
	public static function add_settings_page( $settings ) {
		$settings[] = include_once CP_Donations()->plugin_path() . '/includes/class-cpd-custom-amount-settings.php';
		class_alias( 'CPD_Custom_Amount_Admin_Settings', 'CPD_Custom_Amount_Settings' );
		return $settings;
	}

}
CPD_Custom_Amount_Admin::init();
