<?php

/**
 * Plugin Name:   WPScan
 * Plugin URI:    http://wordpress.org/plugins/wpscan/
 * Description:   Scans your system to find vulnerabilities listed in the WPScan Vulnerability Database.
 * Version:       1.4
 * Author:        WPScan Team
 * Author URI:    https://wpscan.org/
 * License:       GPLv3
 * License URI:   https://www.gnu.org/licenses/gpl.html
 * Text Domain:   wpscan
 */

// File Security Check
defined( 'ABSPATH' ) or die( "No script kiddies please!" );

// Config
define( 'WPSCAN_API_URL', 'https://wpvulndb.com/api/v3' );
define( 'WPSCAN_SIGN_UP_URL', 'https://wpvulndb.com/users/sign_up' );
define( 'WPSCAN_PROFILE_URL', 'https://wpvulndb.com/users/edit' );
define( 'WPSCAN_PLUGIN_FILE', __FILE__ );

//Includes
require_once 'includes/class-wpscan.php';
require_once 'includes/class-settings.php';
require_once 'includes/class-summary.php';
require_once 'includes/class-notification.php';
require_once 'includes/class-admin-bar.php';
require_once 'includes/class-dashboard.php';
require_once 'includes/class-report.php';

// Activating
register_activation_hook( __FILE__, array( 'WPScan', 'activate' ) );

// Deactivating
register_deactivation_hook( __FILE__, array( 'WPScan', 'deactivate' ) );

// Initialize
add_action( 'init', array( 'WPScan', 'init' ) );
