<?php

namespace evowebdev;

defined( 'ABSPATH' ) or die( '' );

/**
	 * Plugin Name:	Fix Unsafe Link Target
	 * Description: Add <code>rel="noreferrer noopener"</code> to links with <code>target="_blank"</code> addressing a security vulnerability which can be exploited by malicious websites. Adapted from 'Allow Unsafe Link Target' plugin which removed rel from target=_blank links entirely (https://wordpress.org/plugins/allow-unsafe-link-target/).
	 * Version:     1.0.0
	 * Author:      Evo Web Dev
	 * Author URI:  https://evowebdev.com
 */

if( !class_exists( 'FixUnsafeLinkTarget' ) ) {	
	class FixUnsafeLinkTarget {	
		public function __construct() {		
			add_filter( 'the_content', [$this, 'fix_unsafe_link_target'] );		
		}	
		public function fix_unsafe_link_target( $content ) {		
			return str_replace( [' rel="noopener"'], ' rel="noreferrer noopener"', $content );	
		}
	}
}
new FixUnsafeLinkTarget;
