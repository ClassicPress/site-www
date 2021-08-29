<?php
/**
 * Iframe option
 *
 * @package Titan Framework
 */

if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly.
}

/**
 * Iframe option class
 *
 * @since 1.0
 */
class TitanFrameworkOptionIframe extends TitanFrameworkOption {

	/**
	 * Default settings specific to this option
	 * @var array
	 */
	public $defaultSecondarySettings = array(
		'url' => '',
		'height' => '400', // In pixels.
	);

	/**
	 * Display for options and meta
	 */
	public function display() {

		$this->echoOptionHeader();

		printf( '<iframe frameborder="0" src="%s" style="height: %spx; width:100%%;"></iframe>',
			$this->settings['url'],
			$this->settings['height']
		);
		$this->echoOptionFooter();

	}

}
