<?php
/**
 * Add some content to the help tab
 *
 * @package     ClassicCommerce/Admin
 * @version     WC-2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'WC_Admin_Help', false ) ) {
	return new WC_Admin_Help();
}

/**
 * WC_Admin_Help Class.
 */
class WC_Admin_Help {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		add_action( 'current_screen', array( $this, 'add_tabs' ), 50 );
	}

	/**
	 * Add help tabs.
	 */
	public function add_tabs() {
		$screen = get_current_screen();

		if ( ! $screen || ! in_array( $screen->id, wc_get_screen_ids() ) ) {
			return;
		}

		$screen->add_help_tab(
			array(
				'id'      => 'woocommerce_support_tab',
				'title'   => __( 'Help &amp; Support', 'classic-commerce' ),
				'content' =>
					'<h2>' . __( 'Help &amp; Support', 'classic-commerce' ) . '</h2>' .
					'<p>' . sprintf(
						/* translators: %s: Documentation URL */
						__( 'Should you need help understanding, using, or extending Classic Commerce, <a href="%s">please refer to the WooCommerce documentation</a>. You will find all kinds of resources including snippets, tutorials and much more.', 'classic-commerce' ),
						'https://docs.woocommerce.com/documentation/plugins/woocommerce/'
					) . '</p>' .
					'<p>' . sprintf(
						/* translators: %s: Forum URL */
						__( 'For further assistance with Classic Commerce you can use the <a href="%1$s">ClassicPress community forum</a>.', 'classic-commerce' ),
						'https://forums.classicpress.net/tags/classic-commerce/'
					) . '</p>' .
					'<p>' . __( 'Before asking for help we recommend using the system status page to identify any problems with your configuration. You should make a copy of this report to add to your support request.', 'classic-commerce' ) . '</p>' .
					'<p><a href="' . admin_url( 'admin.php?page=wc-status' ) . '" class="button button-primary">' . __( 'System status', 'classic-commerce' ) . '</a> <a href="https://forums.classicpress.net/tags/classic-commerce/" class="button">' . __( 'Community forum', 'classic-commerce' ) . '</a></p>',
			)
		);

		$screen->add_help_tab(
			array(
				'id'      => 'woocommerce_bugs_tab',
				'title'   => __( 'Found a bug?', 'classic-commerce' ),
				'content' =>
					'<h2>' . __( 'Found a bug?', 'classic-commerce' ) . '</h2>' .
					/* translators: 1: GitHub issues URL 2: System status report URL */
					'<p>' . sprintf( __( 'If you find a bug within Classic Commerce core you can create a ticket via <a href="%1$s">Github issues</a>. To help us solve your issue, please be as descriptive as possible and include your <a href="%2$s">system status report</a>.', 'classic-commerce' ), 'https://github.com/ClassicPress-plugins/classic-commerce/issues', admin_url( 'admin.php?page=wc-status' ) ) . '</p>' .
					'<p><a href="https://github.com/ClassicPress-plugins/classic-commerce/issues" class="button button-primary">' . __( 'Report a bug', 'classic-commerce' ) . '</a> <a href="' . admin_url( 'admin.php?page=wc-status' ) . '" class="button">' . __( 'System status', 'classic-commerce' ) . '</a></p>',
			)
		);

		$screen->add_help_tab(
			array(
				'id'      => 'woocommerce_onboard_tab',
				'title'   => __( 'Setup wizard', 'classic-commerce' ),
				'content' =>
					'<h2>' . __( 'Setup wizard', 'classic-commerce' ) . '</h2>' .
					'<p>' . __( 'If you need to access the setup wizard again, please click on the button below.', 'classic-commerce' ) . '</p>' .
					'<p><a href="' . admin_url( 'index.php?page=wc-setup' ) . '" class="button button-primary">' . __( 'Setup wizard', 'classic-commerce' ) . '</a></p>',
			)
		);

		$screen->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'classic-commerce' ) . '</strong></p>' .
			'<p><a href="https://github.com/ClassicPress-plugins/classic-commerce/" target="_blank">' . __( 'Github project', 'classic-commerce' ) . '</a></p>' .
			'<p><a href="https://classicpress.net/" target="_blank">' . __( 'About ClassicPress', 'classic-commerce' ) . '</a></p>' .
			'<p><a href="https://woocommerce.com/product-category/woocommerce-extensions/" target="_blank">' . __( 'Extensions', 'classic-commerce' ) . '</a></p>'
		);
	}
}

return new WC_Admin_Help();
