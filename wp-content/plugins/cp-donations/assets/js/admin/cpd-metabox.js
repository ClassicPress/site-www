/**
 * Script for editing the prices in the product data metabox.
 */

(function($) {

	$.extend(
		{
			move_cpd_meta_fields: function() {
				$( '.options_group.show_if_cpd' ).insertBefore( '.options_group.pricing' );
			},
			add_class_to_regular_price: function() {
				$( '.options_group.pricing' ).addClass( 'hide_if_cpd' );
			},
			toggle_regular_price_class: function( is_cpd ) {
				if ( is_cpd ) {
					$( '.options_group.pricing' ).removeClass( 'show_if_simple' );
				} else {
					$( '.options_group.pricing' ).addClass( 'show_if_simple' );
				}
			},
			show_hide_cpd_elements: function() {
				var product_type = $( 'select#product-type' ).val();
				var is_cpd       = $( '#_cpd' ).prop( 'checked' );

				$.toggle_regular_price_class( is_cpd );

				switch ( true ) {
					case cpd_metabox.simple_types.indexOf( product_type ) > -1:
						$.show_hide_cpd_prices( is_cpd, true );
						break;
					case cpd_metabox.variable_types.indexOf( product_type ) > -1:
						$.show_hide_cpd_prices( false );
						$.move_cpd_variation_fields();
						$.show_hide_cpd_meta_for_variable_products();
						break;
					default:
						$.show_hide_cpd_prices( false );
					break;
				}
			},
			show_hide_cpd_prices: function( show, restore ) {
				// For simple and sub types we'll want to restore the regular price inputs.
				restore = typeof restore !== 'undefined' ? restore : false;

				if ( show ) {
					$( '.show_if_cpd' ).show();
					$( '.hide_if_cpd' ).hide();
				} else {
					$( '.show_if_cpd' ).hide();
					if ( restore ) {
						$( '.hide_if_cpd' ).show();
					}
				}
			},
			add_class_to_variable_price: function() {
				$( '.woocommerce_variation .variable_pricing' ).addClass( 'hide_if_variable_cpd' );
			},
			move_cpd_variation_fields: function() {
				$( '#variable_product_options .variable_cpd_pricing' ).not( '.cpd_moved' ).each(
					function() {
						$( this ).insertAfter( $( this ).siblings( '.variable_pricing' ) ).addClass( 'cpd_moved' );
					}
				);
			},
			show_hide_cpd_variable_meta: function() {
				$.show_hide_cpd_meta_for_variable_products();
			},
			show_hide_cpd_meta_for_variable_products: function() {
				$( '.variation_is_cpd' ).each(
					function( index ) {
						var $variable_pricing = $( this ).closest( '.woocommerce_variation' ).find( '.variable_pricing' );
						var $cpd_pricing = $( this ).closest( '.woocommerce_variation' ).find( '.variable_cpd_pricing' );

						// Hide or display on load.
						if ( $( this ).prop( 'checked' ) ) {
							  $cpd_pricing.show();
							  $variable_pricing.hide();
						} else {
							$cpd_pricing.hide();
							$variable_pricing.removeAttr( 'style' );
						}
					}
				);
			},
		}
	); // End extend.

	// Move the simple inputs into the same location as the normal pricing section.
	if ( $( '.options_group.pricing' ).length > 0) {
		$.move_cpd_meta_fields();
		$.add_class_to_regular_price();
		$.show_hide_cpd_elements();
	}

	// Adjust fields when the product type is changed.
	$( 'body' ).on(
		'woocommerce-product-type-change',
		function() {
			$.show_hide_cpd_elements();
		}
	);

	// Adjust the fields when CPD status is changed.
	$( 'input#_cpd' ).on(
		'change',
		function() {
			$.show_hide_cpd_elements();
		}
	);

	// WC 2.4 compat: handle variable products on load.
	$( '#woocommerce-product-data' ).on(
		'woocommerce_variations_loaded',
		function() {
			$.add_class_to_variable_price();
			$.move_cpd_variation_fields();
			$.show_hide_cpd_variable_meta();
		}
	);

	// When a variation is added.
	$( '#variable_product_options' ).on(
		'woocommerce_variations_added',
		function() {
			$.add_class_to_variable_price();
			$.move_cpd_variation_fields();
			$.show_hide_cpd_variable_meta();
		}
	);

	// Hide/display variable cpd prices on single cpd checkbox change.
	$( '#variable_product_options' ).on(
		'change',
		'.variation_is_cpd',
		function(event) {
			$.show_hide_cpd_variable_meta();
		}
	);

	// Hide/display variable cpd prices on bulk cpd checkbox change.
	$( 'select.variation_actions' ).on(
		'woocommerce_variable_bulk_cpd_toggle',
		function(event) {
			$.show_hide_cpd_variable_meta();
		}
	);

})( jQuery );
