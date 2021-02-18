<?php
/**
 * Static helper functions for interacting with products
 */

defined( 'ABSPATH' ) || exit;


class CPD_Custom_Amount_Helpers {

	// Supported product types.
	// The cpd product type is how the ajax add to cart functionality is disabled in old version of WC.
	private static $simple_supported_types = array( 'simple', 'variation' );


	// Supported variable product types.
	private static $variable_supported_types = array( 'variable' );


	// Get supported "simple" types.
	public static function get_simple_supported_types() {
		return apply_filters( 'cpd_simple_supported_types', self::$simple_supported_types );
	}

	// Get supported "variable" types.
	public static function get_variable_supported_types() {
		return apply_filters( 'cpd_variable_supported_types', self::$variable_supported_types );
	}


	// Verify this is a CPD product.
	public static function is_cpd( $product ) {
		$product = self::maybe_get_product_instance( $product );
		if ( ! $product ) {
			return false;
		}

		$is_cpd = $product->is_type( self::get_simple_supported_types() ) && wc_string_to_bool( $product->get_meta( 'cpd_custom_amount_enabled' ) ) ? true : false;

		return apply_filters( 'cpd_is_cpd', $is_cpd, $product->get_id(), $product );
	}


	// Verify this is a CPD product variation.
	public static function is_cpd_variation( $variation ) {
		$is_custom_amount	= false;
		$is_supported_type	= false;

		if ( ! $variation ) {
			return false;
		}

		if ( $variation->get_meta( 'cpd_custom_amount_enabled' ) ) {
			$is_custom_amount = true;
		}

		if ( $variation->is_type( self::get_simple_supported_types() ) ) {
			$is_supported_type = true;
		}

		$is_cpd_variation = ( $is_custom_amount && $is_supported_type ) ? true : false;

		return $is_cpd_variation;
	}


	public static function is_recurring( $string ) {
		$var_type = 'recurring';
		$len = strlen($var_type);
		return (substr($string, 0, $len) === $var_type);
	}


	// Get the minimum price.
	public static function get_minimum_price( $product ) {
		$product = self::maybe_get_product_instance( $product );
		if ( ! $product ) {
			return false;
		}

		$minimum = $product->get_meta( 'cpd_custom_amount_min', true, 'edit' );
		if ( ! is_numeric( $minimum ) ) {
			$minimum = false;
		}

		// Filter the raw minimum price
		return apply_filters( 'cpd_raw_minimum_price', $minimum, $product->get_id(), $product );
	}


	// Determine if variable product has CPD variations.
	public static function has_cpd_variation( $product ) {
		$product = self::maybe_get_product_instance( $product );
		if ( ! $product ) {
			return false;
		}

		$has_cpd_variation = $product->is_type( self::get_variable_supported_types() ) && wc_string_to_bool( $product->get_meta( '_has_cpd', true, 'edit' ) ) ? true : false;

		return apply_filters( 'cpd_has_cpd_variations', $has_cpd_variation, $product );
	}


	// Are we hiding the From price for variable products.
	public static function is_variable_price_hidden( $product ) {
		$product = self::maybe_get_product_instance( $product );
		if ( ! $product ) {
			return false;
		}

		$is_hidden = $product && $product->get_meta( '_cpd_hide_variable_price' ) === 'yes' ? true : false;

		return apply_filters( 'cpd_is_variable_price_hidden', $is_hidden, $product->get_id(), $product );
	}


	// Remove thousands separators
	public static function standardize_number( $value ) {
		$value = trim( str_replace( wc_get_price_thousand_separator(), '', stripslashes( $value ) ) );

		return wc_format_decimal( $value );
	}


	// Get the minimum price string.
	public static function get_minimum_price_html( $product ) {
		$product = self::maybe_get_product_instance( $product );

		// Start the price string.
		$html = '';

		// If not cpd quit early.
		if ( ! self::is_cpd( $product ) ) {
			return $html;
		}

		// Get the minimum price.
		$minimum = self::get_minimum_price( $product );

		if ( false !== $minimum ) {
			// Default minimum text.
			$default_text = _x( 'Minimum price: %PRICE%', 'CP Donations default minimum text', 'cp_donations_domain' );

			// Get the minimum text option.
			$minimum_text = stripslashes( get_option( 'cpd_minimum_text', $default_text ) );

			// Replace placeholders.
			$html = str_replace( '%PRICE%', wc_price( $minimum ), $minimum_text );
		}

		return apply_filters( 'cpd_minimum_price_html', $html, $product );
	}


	// Get the "CP Donations" label string.
	public static function get_price_input_label_text( $product ) {
		$product = self::maybe_get_product_instance( $product );

		// Start the string.
		$text = '';

		// If not cpd quit early.
		if ( ! self::is_cpd( $product ) ) {
			return $text;
		}

		$currency_symbol = get_woocommerce_currency_symbol();

		$text = sprintf(
			// Translators: %1$s is the label text and %2$s is the currency symbol.
			_x( '%1$s ( %2$s )', 'CP Donations input label', 'cp_donations_domain' ),
			esc_html( get_option( 'cpd_label_text', __( 'CP Donations', 'cp_donations_domain' ) ) ),
			$currency_symbol
		);

		return apply_filters( 'cpd_price_input_label_text', $text, $product );
	}


	// Format a price string.
	public static function get_price_string( $product, $type = 'minimum', $show_null_as_zero = false, $show_raw_price = false ) {
		// Start the price string.
		$html = '';

		$product = self::maybe_get_product_instance( $product );

		switch ( $type ) {
			case 'minimum-variation':
				$price = self::get_minimum_variation_price( $product );
				break;
			case 'minimum':
				$price = self::get_minimum_price( $product );
				break;
			default:
				break;
		}

		if ( $show_null_as_zero || '' !== $price ) {
			$price = $show_raw_price ? $price : wc_price( $price );
			$html = $price;
		}

		return apply_filters( 'cpd_price_string', $html, $product, $price );
	}


	// Get Price Value Attribute.
	public static function get_price_value_attr( $product, $suffix = false ) {
		$product = self::maybe_get_product_instance( $product );
		$posted  = self::get_posted_price( $product, $suffix );

		if ( '' !== $posted ) {
			$price = $posted;
		} else {
			$price = self::get_initial_price( $product );
		}

		return $price;
	}


	// Get Posted Price.
	public static function get_posted_price( $product = false, $suffix = false ) {
		$product = self::maybe_get_product_instance( $product );

		// @TODO: The $product is now useless, so we can deprecate that in the future? // Leave in Filter.
		$posted_price = isset( $_REQUEST[ 'cpd' . $suffix ] ) ? self::standardize_number( sanitize_text_field( wp_unslash( $_REQUEST[ 'cpd' . $suffix ] ) ) ) : '';

		return apply_filters( 'cpd_get_posted_price', $posted_price, $product, $suffix );
	}


	// Get Initial Price
	public static function get_initial_price( $product ) {
		$product = self::maybe_get_product_instance( $product );

		return apply_filters( 'cpd_get_initial_price', '', $product );
	}


	// Format price with local decimal point.
	// Similar to wc_price().
	public static function format_price( $price ) {
		$decimals           = wc_get_price_decimals();
		$decimal_separator  = wc_get_price_decimal_separator();
		$thousand_separator = wc_get_price_thousand_separator();

		if ( '' !== $price ) {
			$price = apply_filters( 'raw_woocommerce_price', floatval( $price ) );
			$price = apply_filters( 'formatted_woocommerce_price', number_format( $price, $decimals, $decimal_separator, $thousand_separator ), $price, $decimals, $decimal_separator, $thousand_separator );

			if ( apply_filters( 'cpd_price_trim_zeros', false ) && $decimals > 0 ) {
				$price = wc_trim_zeros( $price );
			}
		}

		return $price;
	}


	// Get data attributes for use in cpd-custom-amount.js
	public static function get_data_attributes( $product, $suffix = null ) {
		// Get product object.
		$product = self::maybe_get_product_instance( $product );
		$price   = (float) self::get_price_value_attr( $product, $suffix );
		$minimum = self::get_minimum_price( $product );
		$attributes = array(
			'minimum-error'      => self::error_message( 'minimum_js' ),
			'empty-error'        => self::error_message( 'empty' ),
			'initial-price'      => self::get_initial_price( $product ),
			'min-price'          => $minimum && $minimum > 0 ? (float) $minimum : 0,
		);

		/**
		 * Filter cpd_data_attributes
		 */
		$attributes = apply_filters( 'cpd_data_attributes', $attributes, $product, $suffix );
		$data_string = '';

		foreach ( $attributes as $key => $attribute ) {
			$data_string .= sprintf( 'data-%s="%s" ', esc_attr( $key ), esc_attr( $attribute ) );
		}

		return $data_string;
	}


	// The error message template.
	public static function get_error_message_template( $id = null, $context = '' ) {
		$errors = apply_filters(
			'cpd_error_message_templates',
			array(
				'invalid-product' => __( 'This is not a valid product.', 'cp_donations_domain' ),
				'invalid'         => __( '&quot;%%TITLE%%&quot; could not be added to the cart. Please enter a valid, positive number.', 'cp_donations_domain' ),
				'minimum'         => __( '&quot;%%TITLE%%&quot; could not be added to the cart. Please enter at least %%MINIMUM%%.', 'cp_donations_domain' ),
				'minimum_js'      => __( 'Please enter at least %%MINIMUM%%.', 'cp_donations_domain' ),
				'empty'           => __( 'Please enter an amount.', 'cp_donations_domain' ),
				'minimum-cart'    => __( '&quot;%%TITLE%%&quot; cannot be purchased. Please enter at least %%MINIMUM%%.', 'cp_donations_domain' ),
			)
		);

		if ( isset( $errors[ $id . '-' . $context ] ) ) {
			$template = $errors[ $id . '-' . $context ];
		} elseif ( isset( $errors[ $id ] ) ) {
			$template = $errors[ $id ];
		} else {
			$template = '';
		}

		return $template;
	}


	// Get error message.
	public static function error_message( $id, $tags = array(), $product = null, $context = '' ) {
		$message = self::get_error_message_template( $id, $context );

		foreach ( $tags as $tag => $value ) {
			$message = str_replace( $tag, $value, $message );
		}

		return apply_filters( 'cpd_error_message', $message, $id, $tags, $product );
	}


	// Wrapper to check whether we have a product ID or product and if we have the former, return the later.
	public static function maybe_get_product_instance( $product ) {
		if ( ! is_object( $product ) || ! is_a( $product, 'WC_Product' ) ) {
			$product = wc_get_product( $product );
		}

		return $product;
	}


	// Get the Suffix
	public static function get_suffix( $cpd_id ) {
		return apply_filters( 'cpd_field_suffix', '', $cpd_id );
	}


} //end class
