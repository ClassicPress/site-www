<?php

/**
 * Plugin Name: Classic Commerce tweaks for www.classicpress.net
 */


// https://stackoverflow.com/questions/50107409/make-checkout-addresses-fields-not-required-in-woocommerce
// https://docs.woocommerce.com/document/tutorial-customising-checkout-fields-using-actions-and-filters/
add_filter( 'woocommerce_checkout_fields', function( $fields ) {
	unset( $fields['billing']['billing_address_1'] );
	unset( $fields['billing']['billing_address_2'] );
	unset( $fields['billing']['billing_city'] );
	unset( $fields['billing']['billing_postcode'] );
	unset( $fields['billing']['billing_state'] );

	return $fields;
} );


// https://github.com/woocommerce/woocommerce/blob/2de494e/includes/class-wc-order.php#L173
add_filter( 'woocommerce_order_item_needs_processing', function( $needs_processing, $product, $id ) {
	// By default, CC will switch orders from 'payment pending' to 'processing'
	// when paid, unless the product is both virtual and downloadable.  None of
	// our virtual items (donations) require processing, so switch them to
	// 'completed' right away.
	if ( $product->is_virtual() ) {
		return false;
	}

	return $needs_processing;
}, 10, 3 );
