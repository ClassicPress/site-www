<?php
/**
 * Plugin Name: Get Latest WP Version Supported by CP Migration Plugin
 * Description: Gets the latest version of WordPress supported by the CP Migration Plugin. [cp_migration_get_wp_ver]
 * Version: 1.0.0
 * Author: Tim Hughes
 * Author URI: https://github.com/timbocode/
 * Plugin URI: https://github.com/timbocode/cp-migration-get-latest-ver
 * Text Domain: cp_migration_get_wp_ver
 * License: GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 1.0.0
 */

namespace timbocode\MigrationGetWPVer;

defined( 'ABSPATH' ) || exit;

class MigrationGetWPVer {


	public static function register_cp_migration_shortcode() {
		add_shortcode( 'cp_migration_get_wp_ver', [ __CLASS__, 'create_cp_migration_shortcode' ] );
	}


	public static function create_cp_migration_shortcode() {
		// Get the migration plugin data from API. Check cache first.
		$migration_plugin_data = self::get_migration_plugin_data_cached( 'cp_migration_get_wp_ver' );
		
		if ( is_wp_error( $migration_plugin_data ) ) {
			return esc_html( $migration_plugin_data->get_error_message() );
		}

		return esc_html( $migration_plugin_data['wordpress']['max'] );
	}
	

	public static function get_migration_plugin_data_cached( $trans_name ) {
		// Get transient data if exists
		$migration_data = get_transient( $trans_name );

		if ( empty( $migration_data ) ) {
			//If no transient set, fetch data from API
			$migration_data = self::get_migration_plugin_data();

			if ( is_wp_error( $migration_data ) ) {
				return $migration_data;
			}

			// Save migration data to transient
			set_transient( $trans_name, $migration_data, 60 * MINUTE_IN_SECONDS );
		}

		return $migration_data;
	}
	

	public static function get_migration_plugin_data() {
		$api_url = 'https://api-v1.classicpress.net/migration/';
		
		// Make API call
		$response = wp_remote_get( esc_url_raw( $api_url ) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Parse the JSON response from the API into an array of data.
		$body	= wp_remote_retrieve_body( $response );
		$json	= json_decode( $body, true );
		$code	= wp_remote_retrieve_response_code( $response );

		if ( empty( $json ) || $code !== 200 ) {
			return new \WP_Error(
				'invalid_data',
				'Invalid response from API',
				[
					'code' => $code,
					'body' => empty( $json ) ? $body : $json,
				]
			);
		}

		return $json;
	}

}

MigrationGetWPVer::register_cp_migration_shortcode();
