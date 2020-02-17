<?php

use bahiirwa\LatestGithubRelease\LatestGithubRelease;

add_action( 'rest_api_init', function() {
	register_rest_route( 'cp/v1', 'latest', [
		'methods'  => 'GET',
		'callback' => 'cpnet_rest_latest',
		'args'     => [
			'project' => [
				'type'    => 'string',
				'default' => 'core',
			],
			'format'  => [
				'type'    => 'string',
				'default' => 'json',
			],
		],
	] );
} );

/**
 * Returns information about the latest ClassicPress release, or redirects to a
 * URL that can be used to download the release.
 *
 * The 'project' argument can be either 'core' or 'migration-plugin'.
 *
 * The 'format' argument can be either 'json' or 'zip', or additionally
 * 'tar.gz' for the 'core' project.
 *
 * For 'core', GitHub builds a .zip and a .tar.gz file for us for each release.
 *
 * For 'migration-plugin', we build the .zip file ourselves, so the .tar.gz
 * file is not available.
 */
function cpnet_rest_latest( $request ) {
	switch ( $request['project'] ) {
		case 'core':
			$github_params = [
				'user' => 'ClassicPress',
				'repo' => 'ClassicPress-release',
			];
			$formats = [ 'json', 'zip', 'tar.gz' ];
			break;
		case 'migration-plugin':
			$github_params = [
				'user' => 'ClassicPress',
				'repo' => 'ClassicPress-Migration-Plugin',
			];
			$formats = [ 'json', 'zip' ];
			break;
		default:
			return new WP_Error(
				'rest_invalid_param',
				"Argument 'project' must be one of: 'core', 'migration-plugin'"
			);
	}

	if ( ! in_array( $request['format'], $formats, true ) ) {
		return new WP_Error(
			'rest_invalid_param',
			"Argument 'format' must be one of: 'json', 'zip', 'tar.gz' (core only)"
		);
	}

	$release_data = LatestGithubRelease::get_release_data_cached( $github_params );
	if ( is_wp_error( $release_data ) ) {
		return $release_data;
	}

	$zip_url = LatestGithubRelease::get_zip_url_for_release( $release_data );
	if ( is_wp_error( $zip_url ) ) {
		return $zip_url;
	}

	if ( $request['project'] === 'core' ) {
		$tar_gz_url = preg_replace( '#\.zip$#', '.tar.gz', $zip_url );
	} else {
		$tar_gz_url = null;
	}

	if ( $request['format'] === 'zip' ) {
		wp_redirect( $zip_url );
		die();
	}

	if ( $request['format'] === 'tar.gz' && $tar_gz_url ) {
		wp_redirect( $tar_gz_url );
		die();
	}

	$data = $github_params;
	$data['version'] = $release_data['tag_name'];
	$data['published_at'] = $release_data['published_at'];
	$data['github_web_url'] = $release_data['html_url'];
	$data['github_api_url'] = $release_data['url'];
	$data['zip_url'] = $zip_url;
	if ( $tar_gz_url ) {
		$data['tar_gz_url'] = $tar_gz_url;
	}

	return $data;
}
