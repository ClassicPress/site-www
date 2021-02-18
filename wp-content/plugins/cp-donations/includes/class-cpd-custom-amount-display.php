<?php
/**
 * Handle some front-end display functions
 */

defined( 'ABSPATH' ) || exit;

class CPD_Custom_Amount_Display {


	protected static $instance = null;


	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	public function __construct() {
		add_action( 'woocommerce_after_order_notes', array( $this, 'display_usa_tax_checkout_message' ) );
		add_action( 'woocommerce_after_checkout_form', array( $this, 'display_usa_tax_checkout_message_js' ) );
		add_filter( 'woocommerce_order_button_text', array( $this, 'change_place_order_button_text' ) );
	}


	public static function display_usa_tax_checkout_message() {
		$us_tax_msg = get_option( 'cpd_checkout_msg' );
		if ( $us_tax_msg != '' ) {
			echo '<div class="cpd_tax_message woocommerce-info" style="display:none">' . wp_kses_post( $us_tax_msg ) . '</div>';
		}
	}


	public static function display_usa_tax_checkout_message_js() {
    ?>
    <script>
        jQuery( document ).ready(function($) {
            var billing_country = 'US';	// Display tax message for US only

            $( 'select#billing_country' ).change(function() {
                selected_country = $( 'select#billing_country' ).val();

                if ( billing_country === selected_country ) {
                    $( '.cpd_tax_message' ).fadeIn( 'slow' );
                }
                else {
                    $( '.cpd_tax_message' ).hide();
                }
            });

        });
    </script>
    <?php
	}



	// Changes the Place Order button text on the checkout page
	public static function change_place_order_button_text( $text ) {
		global $post;

		$cpd_text = trim( apply_filters( 'cpd_place_order_text', get_option( 'cpd_button_place_order_text' ) ) );
		if ( '' !== $cpd_text ) {
			$text = $cpd_text;
		}

		return $text;
	}


} // End class.
