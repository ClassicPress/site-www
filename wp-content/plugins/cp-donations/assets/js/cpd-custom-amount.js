/**
 * Script to validate prices before adding to cart.
 */

/* global cpd_custom_amount_params */


// Format the price with accounting.js.
function woocommerce_cpd_format_price( price, currency_symbol, format ) {
	if ( typeof currency_symbol === 'undefined' ) {
		currency_symbol = '';
	}

	if ( typeof format === 'undefined' ) {
		format = false;
	}

	var currency_format = format ? cpd_custom_amount_params.currency_format : '%v';

	return accounting.formatMoney(
		price,
		{
			symbol: currency_symbol,
			decimal: cpd_custom_amount_params.currency_format_decimal_sep,
			thousand: cpd_custom_amount_params.currency_format_thousand_sep,
			precision: cpd_custom_amount_params.currency_format_num_decimals,
			format: currency_format
		}
	).trim();
}

// Get absolute value of price and turn price into float decimal.
function woocommerce_cpd_unformat_price( price ) {
	return Math.abs( parseFloat( accounting.unformat( price, cpd_custom_amount_params.currency_format_decimal_sep ) ) );
}


// Container script object getter.
jQuery.fn.cpd_get_script_object = function() {
	var $el = jQuery( this );

	if ( typeof( $el.data( 'cpd_script_obj' ) ) !== 'undefined' ) {
		return $el.data( 'cpd_script_obj' );
	}

	return false;
};


( function( $ ) {

	// Main form object.
	var cpdForm = function( $cart ) {
		if ( cpd_script_object = $cart.cpd_get_script_object() ) {
			return cpd_script_object;
		}

		this.$el				= $cart;
		this.$add_to_cart		= $cart.find( '.single_add_to_cart_button' );
		this.$addons_totals		= this.$el.find( '#product-addons-total' );

		this.show_addons_totals	= false;
		this.cpdProducts		= [];
		this.update_cpd_timer	= false;

		this.$el.trigger( 'cpd-initializing', [ this ] );

		this.updateForm			= this.updateForm.bind( this );

		this.$add_to_cart.on( 'click', { cpdForm: this }, this.onSubmit );
		this.$el.on( 'cpd-initialized', { cpdForm: this }, this.updateForm )
		this.$el.on( 'cpd-updated', { cpdForm: this }, this.updateForm );

		this.initIntegrations();

		this.$el.data( 'cpd_script_obj', this );

		// Initialize update.
		this.$el.trigger( 'cpd-initialized', [ this ] );
	}


	// Get all child item objects.
	cpdForm.prototype.getProducts = function() {
		var form = this;

		this.$el.find( '.cpd' ).each(
			function( index ) {
				var $cpd			= $( this ),
				cpd_script_object	= $cpd.cpd_get_script_object();

				// Initialize any objects that don't yet exist.
				if ( ! cpd_script_object ) {
					  cpd_script_object = new cpdProduct( $cpd );
				}
				form.cpdProducts[ index ] = cpd_script_object;
			}
		);

		return form.cpdProducts;
	};


	// Initialize integrations.
	cpdForm.prototype.initIntegrations = function() {
		if ( this.$el.hasClass( 'variations_form' ) ) {
			new CPD_Variations_Integration( this );
		}
	}


	// Schedules an update of the form  when an CPD price is changed
	cpdForm.prototype.updateForm = function( e, triggered_by ) {
		var form = e.data.cpdForm;

		clearTimeout( form.update_cpd_timer );

		form.update_cpd_timer = setTimeout(
			function() {
				form.updateFormTask( triggered_by );
			},
			10
		);
	};


	// Update the form.
	cpdForm.prototype.updateFormTask = function( triggeredBy ) {
		var current_price	= false;
		var attr_name		= false;
		var cpdProducts		= this.getProducts();

		// If triggered by form update, only get a single instance. Unsure how this will work with Bundles/Grouped.
		if ( 'undefined' === typeof triggeredBy && 'undefined' !== typeof cpdProducts && cpdProducts.length ) {
			triggeredBy = cpdProducts.shift();
		}

		if ( 'undefined' !== typeof triggeredBy && 'undefined' !== typeof triggeredBy.$price_input ) {
			attr_name		= triggeredBy.$price_input.attr( 'name' );
			current_price	= triggeredBy.user_price;

			// Always add the price to the button as data for AJAX add to cart.
			this.$add_to_cart.data( attr_name, current_price );

			// Update Addons.
			this.$addons_totals.data( 'price', current_price );
			this.$el.trigger( 'woocommerce-product-addons-update' );
		}

		// Change button status.
		if ( this.isValid() ) {
			this.$add_to_cart.removeClass( 'cpd-disabled' );
			this.$el.trigger( 'cpd-valid', [ this ] );
		} else {
			this.$add_to_cart.addClass( 'cpd-disabled' );
			this.$el.trigger( 'cpd-invalid', [ this ] );
		}
	};


	// Validate on submit.
	cpdForm.prototype.onSubmit = function( e ) {
		var form = e.data.cpdForm;

		if ( ! form.isValid( 'submit' ) ) {
			e.preventDefault();
			e.stopImmediatePropagation();
			return false;
		}
	};


	// Are all CPD fields valid?
	cpdForm.prototype.isValid = function( event_type ) {
		var valid = true;

		this.getProducts().forEach(
			function (cpdProduct) {
				// Revalidate on submit.
				if ( 'submit' === event_type ) {
					cpdProduct.$el.trigger( 'cpd-update' );
				}

				if ( ! cpdProduct.isValid() ) {
					valid = false;
					return true;
				}
			}
		);

		return valid;
	};


	// Shuts down events, actions and filters managed by this script object.
	cpdForm.prototype.shutdown = function() {
		this.$el.find( '*' ).off();
	};


	var cpdProduct = function( $cpd ) {
		if ( cpd_script_object = $cpd.cpd_get_script_object() ) {
			return cpd_script_object;
		}

		var self				= this;

		// Objects.
		self.$el				= $cpd;
		self.$cart				= $cpd.closest( '.cart' );
		self.$form				= $cpd.closest( '.cart' ).not( '.product, [data-bundled_item_id]' );
		self.$error				= $cpd.find( '.woocommerce-cpd-message' );
		self.$error_content		= self.$error.find( 'ul.woocommerce-error' );
		self.$label				= $cpd.find( 'label' );
		self.$price_input		= $cpd.find( '.cpd-input' );
		self.$minimum			= $cpd.find( '.minimum-price' );

		// Variables.
		self.form				= self.$form.cpd_get_script_object();
		self.min_price			= parseFloat( $cpd.data( 'min-price' ) );
		self.raw_price			= self.$price_input.val();
		self.user_price			= woocommerce_cpd_unformat_price( self.raw_price );
		self.error_messages		= [];
		self.optional			= false;
		self.initialized		= false;

		// Methods.
		self.onUpdate			= self.onUpdate.bind( self );
		self.validate			= self.validate.bind( self );

		// Events.
		this.$el.on( 'change', '.cpd-input', { cpdProduct: this }, this.onChange );
		this.$el.on( 'keypress', '.cpd-input', { cpdProduct: this }, this.onKeypress );
		this.$el.on( 'cpd-update', { cpdProduct: this }, this.onUpdate );

		// Store reference in the DOM.
		self.$el.data( 'cpd_script_obj', self );

		// Trigger immediately.
		self.$el.trigger( 'cpd-update', [ self ] );
	};


	// Relay change event to the custom update event.
	cpdProduct.prototype.onChange = function( e ) {
		e.data.cpdProduct.$el.trigger( 'cpd-update', [ e.data.cpdProduct ] );
	};


	// Prevent submit on pressing Enter key.
	cpdProduct.prototype.onKeypress = function( e ) {
		if ( 'Enter' === e.key ) {
			e.preventDefault();
			e.data.cpdProduct.$el.trigger( 'cpd-update', [ e.data.cpdProduct ] );
		}
	};


	// Handle update.
	cpdProduct.prototype.onUpdate = function( e, args ) {
		var self = this;

		// Force revalidation.
		if ( 'undefined' !== typeof args && args.hasOwnProperty( 'force' ) && true === args.force ) {
			this.initialized = false;
		}

		// Current values.
		this.raw_price	= this.$price_input.val().trim() ? this.$price_input.val().trim() : '';
		this.user_price	= woocommerce_cpd_unformat_price( this.raw_price );

		// Maybe auto-format the input.
		if ( '' !== this.raw_price ) {
			this.$price_input.val( woocommerce_cpd_format_price( this.user_price ) );
		}

		// Validate this!
		this.validate();

		// Add price to CPD div for compatibility.
		this.$el.data( 'price', this.user_price );

		if ( this.isValid() ) {
			// Remove error state class.
			this.$el.removeClass( 'cpd-error' );
			// Remove error messages.
			this.$error.slideUp();
			this.$el.trigger( 'cpd-valid-item', [ this ] );
		} else {
			var $messages	= $( '<ul/>' );
			var messages	= this.getErrorMessages();

			if ( messages.length > 0 ) {
				$.each(
					messages,
					function( i, message ) {
						$messages.append( $( '<li/>' ).html( message ) );
					}
				);
			}

			this.$error_content.html( $messages.html() );
			this.$el.trigger( 'cpd-invalid-item', [ this ] );
		}

		if ( this.isInitialized() && ! this.isValid() ) {
			this.$el.addClass( 'cpd-error' );
			this.$error.slideDown(
				function() {
					self.$price_input.focus().select();
				}
			);
		}

		// New trigger.
		this.$el.trigger( 'cpd-updated', [ this ] );

		// Mark the product as initialized.
		this.initialized = true;
	};


	// Validate all the prices.
	cpdProduct.prototype.validate = function() {
		// Skip validate if the price has not changed.
		if ( ! this.priceChanged() ) {
			return true;
		}

		// Reset validation messages.
		this.resetMessages();
		this.$el.data( 'cpd-valid', true );

		// Skip validation for optional products, ex: grouped/bundled.
		if ( this.isOptional() ) {
			return true;
		}

		// Begin building the error message.
		var error_message	= this.$el.data( 'minimum-error' );
		var error_tag		= "%%MINIMUM%%";
		var error_price		= '';

		if ( this.min_price && this.user_price < this.min_price ) {
			error_price = woocommerce_cpd_format_price( this.min_price, cpd_custom_amount_params.currency_format_symbol, true );
			this.addErrorMessage( error_message.replace( error_tag, error_price ) );

			// Check empty input.
		} else if ( '' === this.raw_price ) {
			error_message = this.$el.data( 'empty-error' );
			this.addErrorMessage( error_message.replace( error_tag, error_price ) );
		}

		if ( ! this.isValid() ) {
			this.$el.data( 'cpd-valid', false );
		}
	};


	// Has this price changed?
	cpdProduct.prototype.priceChanged = function() {
		$changed = true;

		if ( ! this.$el.is( ':visible' ) ) {
			$changed = false;
		} else if ( this.isInitialized() && this.raw_price === this.user_price && this.user_price === this.$el.data( 'price' ) ) {
			$changed = false;
		}

		return $changed;
	};


	// Is this price valid?
	cpdProduct.prototype.isValid = function() {
		return ! this.$el.is( ':visible' ) || this.isOptional() || ! this.error_messages.length;
	};


	// Is this product optional?
	cpdProduct.prototype.isOptional = function() {
		return this.$el.data( 'optional' ) === 'yes' && this.$el.data( 'optional_status' ) === false;
	}


	// Is this product initialized?
	cpdProduct.prototype.isInitialized = function() {
		return this.initialized;
	}


	// Add validation message.
	cpdProduct.prototype.addErrorMessage = function( message ) {
		this.error_messages.push( message.toString() );
	};


	// Get validation messages.
	cpdProduct.prototype.getErrorMessages = function( type ) {
		return this.error_messages;
	};


	// Reset messages on update start.
	cpdProduct.prototype.resetMessages = function() {
		this.error_messages = [];
	};


	// Get the user price.
	cpdProduct.prototype.getPrice = function() {
		return this.user_price;
	};


	// Variable Product Integration.
	function CPD_Variations_Integration( form ) {
		var self = this;

		// Assume in a variable product there's only 1 CPD field.
		var cpd = form.getProducts().shift();

		// The add to cart text.
		var default_add_to_cart_text = form.$add_to_cart.html();

		// Init.
		this.integrate = function() {
			form.$el.on( 'found_variation', self.onFoundVariation );
			form.$el.on( 'reset_image', self.resetVariations );
			form.$el.on( 'click', '.reset_variations', self.resetVariations );
		}

		// When variation is found, decide if it is CPD or not.
		this.onFoundVariation = function( event, variation ) {
			// Hide any existing error message.
			cpd.$error.slideUp();

			// If CPD show the price input and tweak the data attributes.
			if ( typeof variation.is_cpd != undefined && variation.is_cpd == true ) {
				// Switch add to cart button text if variation is CPD.
				form.$add_to_cart.html( variation.add_to_cart_text );

				// Get the prices out of data attributes.
				var display_price = typeof variation.display_price !== 'undefined' && variation.display_price ? variation.display_price : '';

				// Set the CPD attributes for JS validation.
				cpd.min_price = typeof variation.minimum_price !== 'undefined' && variation.minimum_price ? parseFloat( variation.minimum_price ) : '';

				// Maybe auto-format the input.
				if ( '' !== display_price.trim() ) {
					cpd.$price_input.val( woocommerce_cpd_format_price( display_price ) );
				} else {
					cpd.$price_input.val( '' );
				}

				// Maybe switch the label.
				if ( cpd.$label.length ) {
					var label = 'undefined' !== variation.price_label ? variation.price_label : '';

					if ( label ) {
						cpd.$label.html( label ).show();
					} else {
						cpd.$label.empty().hide();
					}
				}

				// Maybe show minimum price html.
				if ( cpd.$minimum.length ) {
					var minimum_price_html = 'undefined' !== variation.minimum_price_html ? variation.minimum_price_html : '';

					if ( minimum_price_html ) {
						cpd.$minimum.html( minimum_price_html ).show();
					} else {
						cpd.$minimum.empty().hide();
					}
				}

				// Show the input.
				cpd.$el.slideDown();

				// Trigger update.
				cpd.initialized = false;
				cpd.$el.trigger( 'cpd-update' );
			} else {
				// If not CPD, hide the price input.
				self.resetVariations();
			}
		}

		// Hide CPD errors when attributes are reset.
		this.resetVariations = function() {
			form.$add_to_cart.html( default_add_to_cart_text ).removeClass( 'cpd-disabled' );
			cpd.$el.slideUp().removeClass( 'cpd-error' );
			cpd.initialized = false;
			cpd.$error_content.empty();
			cpd.$price_input.val( '' );
		}

		this.integrate();
	}


	/*-----------------------------------------------------------------*/
	/*  Initialization.                                                */
	/*-----------------------------------------------------------------*/

	jQuery( function( $ ) {

			// Script initialization on '.cart' elements.
			$.fn.cpd_form = function() {
				var $cart			= $( this ),
				cpd_script_object	= $cart.cpd_get_script_object();

				if ( ! $cart.hasClass( 'cart' ) ) {
					return false;
				}

				// If the script object already exists, then we need to shut it down first before re-initializing.
				if ( cpd_script_object) {
					$cart.data( 'cpd_script_obj' ).shutdown();
				}

				// Launch the form object.
				new cpdForm( $cart );

				return this;
			};

			// Initialize CPD scripts.
			$( 'form.cart' ).each(
				function() {
					$( this ).cpd_form();
				}
			);

			new cpdForm( $( 'form.cart' ) );
		}
	);

} )( jQuery );
