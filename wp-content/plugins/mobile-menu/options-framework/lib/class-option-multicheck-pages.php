<?php

if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
class TitanFrameworkOptionMulticheckPages extends TitanFrameworkOptionMulticheck {

	public $defaultSecondarySettings = array(
		'options' => array(),
		'select_all' => false
	);

	private static $allPages;

	/*
	 * Display for options and meta
	 */
	public function display() {

		// Remember the pages so as not to perform any more lookups
		if ( ! isset( self::$allPages ) ) {
			self::$allPages = get_pages();
		}

		$this->settings['options'] = array();
		foreach ( self::$allPages as $page ) {
			$title = $page->post_title;
			if ( empty( $title ) ) {
				$title = sprintf( __( 'Untitled %s', TF_I18NDOMAIN ), '(ID #' . $page->ID . ')' );
			}
			$this->settings['options'][ $page->ID ] = $title;
		}

		parent::display();
	}

}
