<?php
/**
 * CP Donations Settings
 */

defined( 'ABSPATH' ) || exit;

/**
 * Settings for API.
 */
if ( class_exists( 'CPD_Custom_Amount_Admin_Settings', false ) ) {
	return new CPD_Custom_Amount_Admin_Settings();
}

if ( ! class_exists( 'CPD_Custom_Amount_Admin_Settings' ) ) {

	class CPD_Custom_Amount_Admin_Settings extends WC_Settings_Page {

		public function __construct() {
			$this->id    = 'cpd';
			$this->label = __( 'CP Donations', 'cp_donations_domain' );

			add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
		}

		// Settings array
		public function get_settings() {
			return apply_filters(
				$this->id . '_settings',
				[
					[
						'title' => __( 'General', 'cp_donations_domain' ),
						'type'  => 'title',
						'desc'  => __( 'General CP Donations settings', 'cp_donations_domain' ),
						'id'    => 'cpd_options_general',
					],
					[
						'title'    => __( 'Checkout message (US Only)', 'cp_donations_domain' ),
						'desc'     => __( 'Checkout message shown to visitors from the United States only', 'cp_donations_domain' ),
						'id'       => 'cpd_checkout_msg',
						'type'     => 'textarea',
						'css'      => 'min-width:600px;',
						'desc_tip' => true,
					],
					[
						'type' => 'sectionend',
						'id'   => 'cpd_options_general',
					],
					[
						'title' => __( 'Custom Amount Setup', 'cp_donations_domain' ),
						'type'  => 'title',
						'desc'  => __( 'Settings specific to custom amounts', 'cp_donations_domain' ),
						'id'    => 'cpd_options',
					],
					[
						'title'    => __( 'Minimum Amount text', 'cp_donations_domain' ),
						'desc'     => __( 'This is the text to display before the minimum accepted amount.', 'cp_donations_domain' ),
						'id'       => 'cpd_minimum_text',
						'type'     => 'text',
						'css'      => 'min-width:300px;',
						'default'  => __( 'Minimum amount:', 'cp_donations_domain' ),
						'desc_tip' => true,
					],
					[
						'title'    => __( 'Custom Amount text', 'cp_donations_domain' ),
						'desc'     => __( 'This is the text that appears above the CP Donations input field.', 'cp_donations_domain' ),
						'id'       => 'cpd_label_text',
						'type'     => 'text',
						'css'      => 'min-width:300px;',
						'default'  => __( 'Custom amount', 'cp_donations_domain' ),
						'desc_tip' => true,
					],
					[
						'title'    => __( 'Add to Cart button text', 'cp_donations_domain' ),
						'desc'     => __( 'This is the text that appears on the Add to Cart button. Leave blank to inherit the default add to cart text.', 'cp_donations_domain' ),
						'id'       => 'cpd_button_add_to_cart_text',
						'type'     => 'text',
						'css'      => 'min-width:300px;',
						'default'     => __( 'Donate Now', 'cp_donations_domain' ),
						'placeholder' => __( 'Donate Now', 'cp_donations_domain' ),
						'desc_tip' => true,
					],
					[
						'title'    => __( 'Place Order button text', 'cp_donations_domain' ),
						'desc'     => __( 'This is the text that appears on the Place Order button on the checkout page. Leave blank to inherit the default text.', 'cp_donations_domain' ),
						'id'       => 'cpd_button_place_order_text',
						'type'     => 'text',
						'css'      => 'min-width:300px;',
						'default'     => __( 'Confirm', 'cp_donations_domain' ),
						'placeholder' => __( 'Confirm', 'cp_donations_domain' ),
						'desc_tip' => true,
					],
					[
						'type' => 'sectionend',
						'id'   => 'cpd_options',
					],
				]
			);
		}
	}

}

return new CPD_Custom_Amount_Admin_Settings();
