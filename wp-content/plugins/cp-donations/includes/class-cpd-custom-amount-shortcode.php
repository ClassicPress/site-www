<?php
/**
 * Shortcode for displaying on post or page
 */

defined( 'ABSPATH' ) || exit;

class CPD_Custom_Amount_Shortcode {


	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_scripts' ) );
		add_shortcode( 'cp_donation', array( __CLASS__, 'cpd_custom_shortcode' ) );

		// If the Subscriptio plugin is installed and active, remove some hooks that alter the layout of the "thank you" page.
		if ( is_plugin_active( 'subscriptio/subscriptio.php' ) ) {
			remove_action('woocommerce_order_details_after_order_table', 'woocommerce_order_again_button');
			add_filter( 'subscriptio_display_order_related_subscriptions', '__return_false' ) ;
		}
	}


	public static function register_scripts() {
		wp_register_script( 'cpd-frontend', CP_Donations()->plugin_url() . '/assets/js/frontend.js', array( 'jquery' ), CPD_VERSION, true );
		wp_register_script( 'accounting', CP_Donations()->plugin_url() . '/assets/js/accounting.js', array( 'jquery' ), '0.4.2', true );
		wp_register_script( 'cpd-custom-amount', CP_Donations()->plugin_url() . '/assets/js/cpd-custom-amount.js', array( 'jquery', 'accounting' ), CPD_VERSION, true );
		wp_register_style( 'cpd-custom-amount', CP_Donations()->plugin_url() . '/assets/css/cpd-custom-amount.css', false, CPD_VERSION );
	}


	// Add body class to help with styling
	public static function add_cpd_body_class( $classes ) {
		$classes[] = 'cpd-custom-amount';
		return $classes;
	}


	// Add Shortcode. Adapted from product_page shortcode in Classic Commerce.
	public static function cpd_custom_shortcode( $atts ) {
		if ( empty( $atts ) ) {
			return '';
		}

		if ( ! isset( $atts['product_id'] ) ) {
			return '';
		}

		$product_id	= $atts['product_id'];
		$product	= wc_get_product( $product_id );

		$args = array(
			'posts_per_page'		=> 1,
			'post_type'				=> 'product',
			'post_status'			=> 'publish',
			'ignore_sticky_posts'	=> 1,
			'no_found_rows'			=> 1,
		);

		if ( isset( $atts['sku'] ) ) {
			$args['meta_query'][] = array(
				'key'		=> '_sku',
				'value'		=> sanitize_text_field( $atts['sku'] ),
				'compare'	=> '=',
			);

			$args['post_type'] = array( 'product', 'product_variation' );
		}

		if ( isset( $atts['product_id'] ) ) {
			$args['p'] = absint( $atts['product_id'] );
		}


		self::do_cpd_shortcode_actions($product);

		$single_product = new WP_Query( $args );

		$preselected_id = '0';

		// Check if sku is a variation.
		if ( $single_product->have_posts() && 'product_variation' === $single_product->post->post_type ) {

			$variation  = new WC_Product_Variation( $single_product->post->ID );
			$attributes = $variation->get_attributes();

			// Set preselected id to be used by JS to provide context.
			$preselected_id = $single_product->post->ID;

			// Get the parent product object.
			$args = array(
				'posts_per_page'		=> 1,
				'post_type'				=> 'product',
				'post_status'			=> 'publish',
				'ignore_sticky_posts'	=> 1,
				'no_found_rows'			=> 1,
				'p'						=> $single_product->post->post_parent,
			);

			$single_product = new WP_Query( $args );
		?>
			<script>
				jQuery( document ).ready( function( $ ) {
					var $variations_form = $( '[data-product-page-preselected-id="<?php echo esc_attr( $preselected_id ); ?>"]' ).find( 'form.variations_form' );

					<?php foreach ( $attributes as $attr => $value ) { ?>
						$variations_form.find( 'select[name="<?php echo esc_attr( $attr ); ?>"]' ).val( '<?php echo esc_js( $value ); ?>' );
					<?php } ?>
				});
			</script>
		<?php
		}

		// For "is_single" to always make load comments_template() for reviews.
		$single_product->is_single = true;

		ob_start();

		global $wp_query;

		// Backup query object so following loops think this is a product page.
		$previous_wp_query	= $wp_query;
		$wp_query			= $single_product;

		while ( $single_product->have_posts() ) {
			$single_product->the_post()
			?>
			<div class="cpd-product-single single-product" data-product-page-preselected-id="<?php echo esc_attr( $preselected_id ); ?>">
				<?php wc_get_template_part( 'content', 'single-product' ); ?>
			</div>
			<?php
		}

		// Restore $previous_wp_query and reset post data.
		$wp_query = $previous_wp_query;
		wp_reset_postdata();

		return '<div class="woocommerce">' . ob_get_clean() . '</div>';
	}


	public static function do_cpd_shortcode_actions($product) {
		global $post;

		remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
		remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );

		// Remove title e.g. "Donate to ClassicPress"
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
		// Remove price e.g. "From $5.00 / month"
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
		// Hide product excerpt
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
		// Remove SKU & product categories
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );

		remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
		remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
		remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );

		wp_enqueue_script( 'wc-single-product' );
		wp_enqueue_script( 'cpd-custom-amount' );
		wp_enqueue_script( 'cpd-frontend' );
		wp_enqueue_script( 'accounting' );
		wp_enqueue_style( 'cpd-frontend' );
		wp_enqueue_style( 'cpd-custom-amount' );

		add_action( 'woocommerce_before_variations_form', array( __CLASS__, 'cpd_before_variations_form' ) );

		add_action( 'cpd_after_price_input', array( __CLASS__, 'display_error_holder' ), 30 );

		add_filter( 'woocommerce_product_single_add_to_cart_text', array( __CLASS__, 'single_add_to_cart_text' ), 10, 2 );

		// Post class.
		add_filter( 'post_class', array( __CLASS__, 'add_post_class' ), 30, 3 );

		// Variable products.
		add_action( 'woocommerce_single_variation', array( __CLASS__, 'display_variable_price_input' ), 12 );

		// See if prices should be shown for each variation after selection.
		add_filter( 'woocommerce_available_variation', array( __CLASS__, 'available_variation' ), 10, 3 );

		add_filter( 'woocommerce_is_sold_individually', '__return_true', 10, 2 );

		add_filter( 'body_class', array( __CLASS__, 'cpd_add_class_to_body' ) );
	}



	public static function cpd_add_class_to_body( $classes ) {
		global $post;

		if( self::is_cpd_product( $post ) ) {
			if( is_checkout() ) {
				$classes[] = 'cpd_checkout';
			}
		}
		return $classes;
	}


	public static function is_cpd_product( $post ) {
		if( isset($post->post_content) && has_shortcode( $post->post_content, 'cp_donation' ) ) {
			return true;
		}
		return false;
	}


	// Load price input script.
	public static function cpd_scripts() {
		wp_enqueue_script( 'accounting' );
		wp_enqueue_script( 'cpd-custom-amount' );

		$params = array(
			'currency_format_num_decimals'	=> wc_get_price_decimals(),
			'currency_format_symbol'		=> get_woocommerce_currency_symbol(),
			'currency_format_decimal_sep'	=> wc_get_price_decimal_separator(),
			'currency_format_thousand_sep'	=> wc_get_price_thousand_separator(),
			'currency_format'				=> str_replace( array( '%1$s', '%2$s' ), array( '%s', '%v' ), get_woocommerce_price_format() ), // For accounting.js.
			'i18n_subscription_string'		=> __( '%price / %period', 'cp_donations_domain' ),
		);

		wp_localize_script( 'cpd-custom-amount', 'cpd_custom_amount_params', apply_filters( 'cpd_script_params', $params ) );
	}


	/**
	 * ---------------------------------------------------------------------------------
	 * Convert select to radio buttons
	 * ---------------------------------------------------------------------------------
	 */

	public static function cpd_before_variations_form() {
		global $product;
		self::cpd_variations_form( $product );
	}


	public static function cpd_data_attributes( $attrs ) {
		$attrs_arr = [];
		foreach ( $attrs as $key => $attr ) {
			$attrs_arr[] = 'data-' . sanitize_title( $key ) . '="' . esc_attr( $attr ) . '"';
		}
		return implode( ' ', $attrs_arr );
	}


	public static function cpd_variations_form( $product ) {
		$product_id		= $product->get_id();
		$df_attrs_arr	= [];
		$df_attrs		= $product->get_default_attributes();
		$recurring		= [];
		$oneoff			= [];

		if ( ! empty( $df_attrs ) ) {
			foreach ( $df_attrs as $key => $val ) {
				$df_attrs_arr[ 'attribute_' . $key ] = $val;
			}
		}

		$children = $product->get_children();

		if ( is_array( $children ) && count( $children ) > 0 ) {
			echo '<div class="cpd-variations cpd-variations-default" data-click="0" >';

			foreach ( $children as $child ) {
				$child_product = wc_get_product( $child );
				if ( CPD_Custom_Amount_Helpers::is_recurring( $child_product->get_sku() ) ) {
					$recurring[] = $child;
				}
				else {
					$oneoff[] = $child;
				}
			}

			$cols = ( ! empty( $recurring ) && ! empty( $oneoff ) ) ? 2 : 1;

			if ( $cols > 1 ) {
				echo '<div class="cpd-col cpd-col-1">';
				echo '<h4>Recurring donations</h4>';
				self::cpd_display_donations( $recurring, $product_id );
				echo '</div>';

				echo '<div class="cpd-col cpd-col-2">';
				echo '<h4>One-time donations</h4>';
				self::cpd_display_donations( $oneoff, $product_id );
				echo '</div>';
			}
			else {
				echo '<div class="cpd-col">';
				if ( ! empty( $oneoff ) ) {
					echo '<h4>One-time donations</h4>';
					self::cpd_display_donations( $oneoff, $product_id );
				}
				else {
					echo '<h4>Recurring donations</h4>';
					self::cpd_display_donations( $recurring, $product_id );
				}
				echo '</div>';
			}

			self::display_price_input();

			echo '</div><!-- /cpd-variations -->';
		}
	}


	public static function cpd_display_donations( $don_data, $product_id ) {
		if ( ! empty($don_data) ) {
			foreach ($don_data as $child ) {

				$child_product		= wc_get_product( $child );
				$is_custom_amount	= $child_product->get_meta( 'cpd_custom_amount_enabled' );
				$custom_html		= '';
				$custom_min_html	= '';
				$data_attrs_arr		= [];
				$html				= '';

				if ( ! $child_product ) {
					continue;
				}

				if ( $is_custom_amount == 'yes' ) {
					$child_attrs	= htmlspecialchars( json_encode( $child_product->get_variation_attributes() ), ENT_QUOTES, 'UTF-8' );
					$min_amount		= $child_product->get_meta( 'cpd_custom_amount_min', true );
					$currency		= get_woocommerce_currency_symbol();
					$permonth		= '';
					$data_attrs_arr	= [
						'id'			=> $child,
						'sku'			=> $child_product->get_sku(),
						'attrs'			=> $child_attrs,
						'price'			=> wc_get_price_to_display( $child_product ),
						'custom-amount'	=> $child_product->get_meta( 'cpd_custom_amount_enabled' ),
						'min-amount'	=> $min_amount,
					];
					$data_attrs		= apply_filters( 'cpd_data_attributes', $data_attrs_arr, $child_product );
					$child_name		= wc_get_formatted_variation( $child_product, true, false );

					if ( CPD_Custom_Amount_Helpers::is_recurring( $child_product->get_sku() ) ) {
						$permonth = '&nbsp;/month';
					}

					$html .= '<div class="cpd-variation cpd-variation-radio-custom" ' . self::cpd_data_attributes( $data_attrs ) . '>';
					$html .= '<div class="cpd-variation-selector"><input value="custom" class="custom-amount-selector" type="radio" name="cpd_variation_' . $product_id . '"/></div>';
					$html .= '<div class="cpd-variation-info">';
					$html .= '<div class="cpd-variation-name">' . esc_attr( $child_name ) . '</div>';
					if ( $min_amount ) {
						$html .= '<div class="cpd-custom-amount-min">(Minimum amount: ' . $currency . $min_amount . ')</div>';
					}
					$html .= '</div>';
					$html .= '</div>';
				} else {
					$child_attrs	= htmlspecialchars( json_encode( $child_product->get_variation_attributes() ), ENT_QUOTES, 'UTF-8' );
					$data_attrs_arr	= [
						'id'			=> $child,
						'sku'			=> $child_product->get_sku(),
						'attrs'			=> $child_attrs,
						'price'			=> wc_get_price_to_display( $child_product ),
						'regular-price'	=> wc_get_price_to_display( $child_product, array( 'price' => $child_product->get_regular_price() ) ),
					];

					$data_attrs		= apply_filters( 'cpd_data_attributes', $data_attrs_arr, $child_product );
					$child_name		= wc_get_formatted_variation( $child_product, true, false );

					$html .= '<div class="cpd-variation cpd-variation-radio" ' . self::cpd_data_attributes( $data_attrs ) . '>';
					$html .= '<div class="cpd-variation-selector"><input value="fixed" class="custom-amount-selector" type="radio" name="cpd_variation_' . $product_id . '"/></div>';
					$html .= '<div class="cpd-variation-info">';
					$html .= '<div class="cpd-variation-name">' . esc_attr( $child_name ) . '</div>';
					$html .= '</div>';
					$html .= '</div>';
				}

				echo $html;
			}
		}
	}


	// Loads the custom-amount-input.php template and displays Custom Amount text input box
	public static function display_price_input( $product = false, $suffix = false ) {
		$product = CPD_Custom_Amount_Helpers::maybe_get_product_instance( $product );
		if ( ! $product ) {
			global $product;
		}

		// Quit if not CPD.
		if ( ! $product || ( 'woocommerce_single_variation' !== current_action() && ! CPD_Custom_Amount_Helpers::is_cpd( $product ) && ! apply_filters( 'cpd_force_display_price_input', false, $product ) ) ) {
			return;
		}

		$price    = CPD_Custom_Amount_Helpers::get_price_value_attr( $product, $suffix );
		$input_id = 'cpd-1';
		$defaults = [
			'input_id'			=> $input_id,
			'input_name'		=> 'cpd' . $suffix,
			'input_value'		=> CPD_Custom_Amount_Helpers::format_price( $price ),
			'input_label'		=> CPD_Custom_Amount_Helpers::get_price_input_label_text( $product ),
			'classes'			=> [ 'input-text', 'amount', 'cpd-input', 'text' ],
			'placeholder'		=> '',
			'custom_attributes'	=> [],
		];

		//Filter cpd_price_input_attributes
		$args = apply_filters( 'cpd_price_input_attributes', $defaults, $product, $suffix );

		// Parse args so defaults cannot be unset.
		wp_parse_args( $args, $defaults );

		// Load the CPD scripts.
		self::cpd_scripts();

		$args['product_id']			= $product->get_id();
		$args['cpd_product']		= $product;
		$args['prefix']				= $suffix;
		$args['suffix']				= $suffix;
		$args['updating_cart_key']	= isset( $_GET['update-price'] ) && WC()->cart->find_product_in_cart( sanitize_key( $_GET['update-price'] ) ) ? sanitize_key( $_GET['update-price'] ) : '';
		$args['_cpdnonce']			= isset( $_GET['_cpdnonce'] ) ? sanitize_key( $_GET['_cpdnonce'] ) : '';

		// Get the price input template.
		$template_path = CP_Donations()->plugin_path() . '/templates/';
		wc_get_template( 'single-product/custom-amount-input.php', $args, false, $template_path );
	}


	// Changes the add to cart button text on the donations page
	// Does not affect product pages
	public static function single_add_to_cart_text( $text, $product ) {
		global $post;

		$cpd_text = trim( apply_filters( 'cpd_single_add_to_cart_text', get_option( 'cpd_button_add_to_cart_text', '' ), $product ) );
		if ( '' !== $cpd_text ) {
			$text = $cpd_text;
		}

		if ( isset( $_GET['update-price'] ) && isset( $_GET['_cpdnonce'] ) && wp_verify_nonce( sanitize_key( $_GET['_cpdnonce'] ), 'cpd-nonce' ) ) {
			$updating_cart_key = wc_clean( wp_unslash( $_GET['update-price'] ) );
			if ( isset( WC()->cart->cart_contents[ $updating_cart_key ] ) ) {
				$text = apply_filters( 'cpd_single_update_cart_text', __( 'Update Cart', 'cp_donations_domain' ), $product );
			}
		}

		return $text;
	}


	// Add cpd data to variation form on donations page. Only for custom amount variations.
	public static function available_variation( $variations, $product, $variation ) {
		if( CPD_Custom_Amount_Helpers::is_cpd_variation( $variation ) ) {
			$cpd_data['is_cpd']					= CPD_Custom_Amount_Helpers::is_cpd( $variation );
			$cpd_data['minimum_price']			= CPD_Custom_Amount_Helpers::get_minimum_price( $variation );
			$cpd_data['initial_price']			= CPD_Custom_Amount_Helpers::get_initial_price( $variation );
			$cpd_data['price_label']			= CPD_Custom_Amount_Helpers::get_price_input_label_text( $variation );
			$cpd_data['display_price']			= CPD_Custom_Amount_Helpers::get_price_value_attr( $variation );
			$cpd_data['display_regular_price']	= $cpd_data['display_price'];
			$cpd_data['price_html']				= apply_filters( 'woocommerce_show_variation_price', false, $product, $variation ) ? '<span class="price">' . $variation->get_price() . '</span>' : '';
			$cpd_data['minimum_price_html']		= CPD_Custom_Amount_Helpers::get_minimum_price_html( $variation );
			$cpd_data['add_to_cart_text']		= $variation->single_add_to_cart_text();
			return array_merge( $variations, $cpd_data );
		}
		return $variations;
	}



	// Call the Price Input Template for Variable products.
	public static function display_variable_price_input( $product = false, $suffix = false ) {
		self::display_price_input( $product, $suffix );
	}


	// Show the empty error-holding div.
	// Displays a message if price entered is lower than the minimum acceptable.
	public static function display_error_holder() {
		printf( '<div id="cpd-error-1" class="woocommerce-cpd-message" aria-live="assertive" style="display: none"><ul class="woocommerce-error wc-cpd-error"></ul></div>' );
	}


	// Add cpd to post class.
	public static function add_post_class( $classes, $class = '', $post_id = '' ) {
		if ( ! $post_id || get_post_type( $post_id ) !== 'product' ) {
			return $classes;
		}

		if ( CPD_Custom_Amount_Helpers::is_cpd( $post_id ) || CPD_Custom_Amount_Helpers::has_cpd_variation( $post_id ) ) {
			$classes[] = 'cpd-product';
		}

		return $classes;
	}


}//end class

CPD_Custom_Amount_Shortcode::init();
