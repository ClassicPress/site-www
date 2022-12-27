<?php
/**
Check that code was called from ClassicPress with uninstallation constant declared.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Options to remove.
$options = array(
	'azrcrv-r',
);

global $wpdb;

if ( ! is_multisite() ) {
	// Remove from single site.

	foreach ( $options as $option ) {
		delete_option( $option );
	}
} else {
	// Remove from multi site.

	$site_ids         = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
	$original_site_id = get_current_site_id();

	foreach ( $site_ids as $site_id ) {
		switch_to_site( $site_id );

		foreach ( $options as $option ) {
			delete_option( $option );
		}
	}

	switch_to_site( $original_site_id );

	foreach ( $options as $option ) {
		delete_site_option( $option );
	}
}

