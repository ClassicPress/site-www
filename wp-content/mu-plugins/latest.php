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

function cpnet_rest_latest( $request ) {
	switch ( $request['project'] ) {
		case 'core':
			$github_params = [
				'user' => 'ClassicPress',
				'repo' => 'ClassicPress-release',
			];
			break;
		case 'migration-plugin':
			$github_params = [
				'user' => 'ClassicPress',
				'repo' => 'ClassicPress-Migration-Plugin',
			];
			break;
		default:
			return new WP_Error(
				'rest_invalid_param',
				"Invalid value for argument 'project' (allowed: 'core', 'migration-plugin')"
			);
	}

	if ( ! in_array( $request['format'], [ 'json', 'zip' ], true ) ) {
		return new WP_Error(
			'rest_invalid_param',
			"Invalid value for argument 'format' (allowed: 'json', 'zip')"
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

	if ( $request['format'] === 'zip' ) {
		wp_redirect( $zip_url );
		die();
	}

	$data = $github_params;
	$data['version'] = $release_data['tag_name'];
	$data['published_at'] = $release_data['published_at'];
	$data['github_web_url'] = $release_data['html_url'];
	$data['github_api_url'] = $release_data['url'];
	$data['zip_url'] = $zip_url;

	return $data;
}
