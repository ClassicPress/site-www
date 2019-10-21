<?php

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

add_filter( 'incompatible_sql_modes', function( $incompatible_modes ) {
	$incompatible_modes[] = 'ANSI';
	return $incompatible_modes;
} );
