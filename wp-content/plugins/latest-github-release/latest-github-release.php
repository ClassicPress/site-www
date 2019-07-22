<?php
/**
 * Plugin Name: Latest Github Release
 * Description: Automatically add a download link to the latest Github repo release zips with a shortcode like [latest_github_release user="Github" repo="years-since"]
 * Version: 1.0.0
 * Author: Laurence Bahiirwa
 * Author URI: https://omukiguy.com
 * Plugin URI: https://github.com/bahiirwa/latest-github-release
 * Text Domain: latest_github_release
 * 
 * This is free software released under the terms of the General Public License,
 * version 2, or later. It is distributed WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. Full
 * text of the license is available at https://www.gnu.org/licenses/gpl-2.0.txt.
 * 
 */

namespace laurenbahiirwa\LatestGithubRelease;


// Prevent direct access.
if (!defined('ABSPATH')) {
	die();
}

class LatestGithubRelease {

	/**
	 * Add action to Process shortcodes.
	 *
	 * @since 0.1.0
	 *
	 */
	public function register() {
	
		add_shortcode('latest_github_release', array($this, 'process_shortcode'));

	}

	/**
	 * Process shortcode.
	 *
	 * This public function processes the cp_release_link shortcode into HTML markup.
	 *
	 * @since 0.1.0
	 *
	 * @param array $atts Shortcode arguments.
	 * @return string <a href="url" class="cp-release-link" target="_blank">$atts[name] . ' ' . $atts[type]</a>
	 */
	public function process_shortcode($atts) {
		
		// Default values for when not passed in shortcode.
		$defaults = [
			'repo' => '',
			'user' => '',
			// set default button name to Download
			'name' => 'Download Zip',
		];

		// Replace any missing shortcode arguments with defaults.
		$atts = shortcode_atts(
			$defaults,
			$atts,
			'latest_github_release');

		$trans_name = 'lg_release_zip_link_' . $atts['repo'];

		// Get any existing copy of our transient data		
		if ( !empty( true == get_transient($trans_name) ) ) {
			return '<a href="' . get_transient($trans_name) . '" class="cp-release-link" target="_blank">' . $atts['name'] . '</a>';
		}

		else {
			// Get Release API URL with the user & repo names
			$combine_link =	'https://api.github.com/repos/' . $atts['user'] . '/' . $atts['repo'] . '/releases/latest';
			
			// Pass the Release API URL with the transient name
			$final_url = $this->run_link_processor($combine_link, $atts['repo']);

			//If the repo has no releases, it returns no links so, Echo message and exit.
			if (empty($final_url)) {
				return;
			}
			return '<a href="' . $final_url . '" class="cp-release-link" rel="noopener" target="_blank">' . $atts['name'] . '</a>';
		}

	}

	/**
	 * Process the chosen type of option for the release zip
	 *
	 * @since 0.1.0
	 *
	 * @param array $atts Shortcode arguments.
	 * @return string Link URL to zip release file if no url_link is set in Shortcode
	 * 
	 */
	public function run_link_processor($zip_link, $attribute_name) {

		// Make API Call.
		$response = wp_remote_get( esc_url_raw($zip_link) );
		// Error catch for failed API Call.
		if ( is_wp_error( $response ) ) {
			echo "Something went wrong";
			echo '<br>';
			var_dump($response);
		} 
		else {
			/* Will result in $api_response being an array of data,
			parsed from the JSON response of the API listed above */
			$api_response = json_decode( wp_remote_retrieve_body( $response ), true );
			// Catch Zipball_url link. If the repo has no releases, it returns no links so, Echo message and exit.
			if (empty($api_response['zipball_url'])) {
				// Return error message.
				echo '<p style="color: red;">' . $attribute_name . ' ' . esc_html__( 'repository has no releases. Talk to the Repository Admin.', 'my-text-domain' ) . '</p>';
				return;
			}
			
			$link_core_return_url = $api_response['zipball_url'];
			// Set 5 minute expiry trnasient with the DB to reduce network calls. Save API link for zip
			set_transient('lg_release_zip_link_' . $attribute_name, $link_core_return_url, 5 * MINUTE_IN_SECONDS );
			// Return link
			return $link_core_return_url;

		}

	}

	/**
	 * On deactivation. Clear the links transient created in DB.
	 *
	 * @since 0.1.0
	 *
	 * @param array $atts Shortcode arguments.
	 * 
	 */
	public function deactivation($atts) {

		$transient_to_delete = 'lg_release_zip_link_' . $atts['repo'];
		if ( true == get_transient( $transient_to_delete ) ) {
			delete_transient( $transient_to_delete );
		}
		
	}

}

// On Activation. Start the Plugin class.
$CP_release_link = new LatestGithubRelease;
$CP_release_link->register();

register_deactivation_hook(__FILE__, array( 'LatestGithubRelease', 'deactivation' ) );