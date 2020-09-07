<?php
/**
 * Plugin Name: CC Compatibility for Woo Addons
 * Description: A compatibility plugin for some WooCommerce addons to work with Classic Commerce.
 * Author: Classic Commerce Research Team
 * Version: 9999.0
 * Requires at least: 1.0.0
 * Tested up to: 1.1.2
 * Author URI: https://www.classiccommerce.cc/
 * 
 * Contributors: SAThemba, ZigPress
 */

defined( 'ABSPATH' ) || exit;

// Load the Update Client to manage updates for the CC Compatibility for Woo Addons plugin
include_once dirname( __FILE__ ) . '/includes/UpdateClient.class.php';

define( 'CCWOOADDONSCOMPAT_VERSION', '9999.0' ); // DO NOT change the version in the plugin header or the Earth will fall on you :P 

define( 'CCWOOADDONSCOMPAT__FILE__', __FILE__ );
define( 'CCWOOADDONSCOMPAT_PATH', plugin_dir_path( CCWOOADDONSCOMPAT__FILE__ ) );

if( !defined( 'CCWOOADDONSCOMPAT_PLUGIN_BASE' ) ) {
	define( 'CCWOOADDONSCOMPAT_PLUGIN_BASE', plugin_basename( CCWOOADDONSCOMPAT__FILE__ ) );
}

function ccwooaddonscompat_hide_view_details( $plugin_meta, $plugin_file, $plugin_data, $status ) {
	if( CCWOOADDONSCOMPAT_PLUGIN_BASE == $plugin_file ) {
		unset( $plugin_meta[2] );		
	}
	return $plugin_meta;
}
add_filter( 'plugin_row_meta', 'ccwooaddonscompat_hide_view_details', 10, 4 );
