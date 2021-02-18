<?php
/**
 * Cart functions
 */

defined( 'ABSPATH' ) || exit;

class CPD_Custom_Amount_Cart {

	protected static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_cart_item_data' ), 5, 3 );
		add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'get_cart_item_from_session' ), 11, 2 );
	}


	// Add cart session data.
	// Adds custom price to cart instead of minimum price.
	public function add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
		$cpd_id			= $variation_id ? $variation_id : $product_id;
		$suffix			= CPD_Custom_Amount_Helpers::get_suffix( $cpd_id );
		$product		= CPD_Custom_Amount_Helpers::maybe_get_product_instance( $cpd_id );

		// get_posted_price() removes the thousands separators.
		$posted_price	= CPD_Custom_Amount_Helpers::get_posted_price( $product, $suffix );

		if ( CPD_Custom_Amount_Helpers::is_cpd( $cpd_id ) && $posted_price ) {
			$cart_item_data['cpd'] = (float) $posted_price;
		}

		return $cart_item_data;
	}


	// Adjust the product based on cart session data.
	// Displays custom amount in cart instead of minimum amount.
	public function get_cart_item_from_session( $cart_item, $values ) {
		if ( isset( $values['cpd'] ) ) {
			$cart_item['cpd'] = $values['cpd'];
			$cart_item = $this->set_cart_item( $cart_item );
		}
		return $cart_item;
	}


	// Set the price of the item in the cart.
	public function set_cart_item( $cart_item ) {
		if ( isset( $cart_item['data'] ) ) {
			$product = $cart_item['data'];
			$product->set_price( $cart_item['cpd'] );
			$product->set_regular_price( $cart_item['cpd'] );
		}
		return $cart_item;
	}


} // End class.
