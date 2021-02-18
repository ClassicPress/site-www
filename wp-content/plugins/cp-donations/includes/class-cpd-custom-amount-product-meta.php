<?php
/**
 * CP Donations product data metabox
 *
 * Adds a settings tab and saves meta data.
 */

defined( 'ABSPATH' ) || exit;


class CPD_Custom_Amount_Product_Meta {

	/**
	 * Bootstraps the class and hooks required actions & filters.
	 */
	public static function init() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'cpd_load_scripts' ) );
		// Product Meta boxes.
		add_filter( 'product_type_options', array( __CLASS__, 'product_type_options' ) );
		add_action( 'woocommerce_product_options_general_product_data', array( __CLASS__, 'add_to_metabox' ) );
		add_action( 'cpd_custom_amount_pricing', array( __CLASS__, 'add_minimum_amount_inputs' ), 20, 2 );
		
		// The woocommerce_admin_process_product_object hook allows the $product object to be modified before it is saved.
		add_action( 'woocommerce_admin_process_product_object', array( __CLASS__, 'save_product_meta' ) );

		// Variable Product.
		add_action( 'woocommerce_variation_options', array( __CLASS__, 'product_variations_options' ), 10, 3 );
		add_action( 'woocommerce_product_after_variable_attributes', array( __CLASS__, 'add_to_variations_metabox' ), 10, 3 );
		add_action( 'cpd_options_variation_pricing', array( __CLASS__, 'add_minimum_amount_inputs' ), 20, 2 );

		add_action( 'woocommerce_save_product_variation', array( __CLASS__, 'save_product_variation' ), 30, 2 );
	}
	
	public static function cpd_load_scripts() {
		wp_enqueue_style( 'cpd-custom-amount', CP_Donations()->plugin_url() . '/assets/css/cpd-custom-amount.css', false, CPD_VERSION );
	}


	/**
	 * ---------------------------------------------------------------------------------
	 * Write Panel / metabox
	 * ---------------------------------------------------------------------------------
	 */

	/**
	 * Add checkbox to product data metabox title
	 */
	public static function product_type_options( $options ) {
		$wrapper_classes = CPD_Custom_Amount_Helpers::get_simple_supported_types();
		array_walk(
			$wrapper_classes,
			function( &$x ) {
				$x = 'show_if_' . $x;
			}
		);

		$options['cpd'] = array(
			'id'			=> 'cpd_custom_amount_enabled',
			'wrapper_class'	=> implode( ' ', $wrapper_classes ),
			'label'			=> __( 'Custom Amount', 'cp_donations_domain' ),
			'description'	=> __( 'Customers are allowed to determine their own amount.', 'cp_donations_domain' ),
			'default'		=> 'no',
		);

		return $options;
	}

	/**
	 * Metabox display callback.
	 */
	public static function add_to_metabox() {
		global $post, $thepostid, $product_object;
		?>
		<div class="options_group show_if_cpd">
			<?php do_action( 'cpd_custom_amount_pricing', $product_object, '' ); ?>
		</div>
		<?php
	}


	/**
	 * Add minimum inputs to product metabox
	 */
	public static function add_minimum_amount_inputs( $product_object, $loop = false ) {
		// Minimum amount.
		woocommerce_wp_text_input(
			array(
				'id'            => is_int( $loop ) ? "cpd_custom_amount_min[$loop]" : 'cpd_custom_amount_min',
				'class'			=> 'wc_input_price short',
				'wrapper_class'	=> is_int( $loop ) ? 'form-row form-row-first' : '',
				'label'         => __( 'Minimum Amount', 'cp_donations_domain' ) . ' (' . get_woocommerce_currency_symbol() . ')',
				'desc_tip'      => true,
				'description'   => __( 'Lowest acceptable amount for product. Leave blank to not enforce a minimum.', 'cp_donations_domain' ),
				'data_type'     => 'price',
				'value'         => $product_object->get_meta( 'cpd_custom_amount_min', true ),
			)
		);
	}

	/**
	 * Save extra meta info
	 */
	public static function save_product_meta( $product ) {
		$minimum   = '';

		if ( isset( $_POST['cpd_custom_amount_enabled'] ) && in_array( $product->get_type(), CPD_Custom_Amount_Helpers::get_simple_supported_types() ) ) {
			$product->update_meta_data( 'cpd_custom_amount_enabled', 'yes' );
			$product->delete_meta_data( '_has_cpd' );
		} else {
			$product->delete_meta_data( 'cpd_custom_amount_enabled' );
		}

		$minimum = isset( $_POST['cpd_custom_amount_min'] ) ? wc_format_decimal( wc_clean( wp_unslash( $_POST['cpd_custom_amount_min'] ) ) ) : '';
		$product->update_meta_data( 'cpd_custom_amount_min', $minimum );

		// Set the regular price as the min price to enable WC to sort by price.
		if ( 'yes' === $product->get_meta( 'cpd_custom_amount_enabled', true ) ) {
			$product->set_price( $minimum );
			$product->set_regular_price( $minimum );
		}

		// adding an action to trigger the product sync.
		do_action( 'cpd_variable_product_sync_data', $product );
	}


	/**
	 * Add CPD checkbox to each variation
	 */
	public static function product_variations_options( $loop, $variation_data, $variation ) {
		$variation_object = wc_get_product( $variation->ID );
		$cpd_custom_amount_enabled = $variation_object->get_meta( 'cpd_custom_amount_enabled', 'edit' );
		?>
		<label class="tips" data-tip="<?php esc_html_e( 'Enable or disable custom amount for this variation', 'cp_donations_domain' ); ?>">
			<?php esc_html_e( 'Custom amount', 'cp-donations' ); ?>
			<input type="checkbox" class="checkbox variation_is_cpd" name="cpd_custom_amount_enabled[<?php echo esc_attr( $loop ); ?>]" <?php checked( $cpd_custom_amount_enabled, 'yes' ); ?> />
		</label>
		<?php
	}


	/**
	 * Add CPD price inputs to each variation
	 */
	public static function add_to_variations_metabox( $loop, $variation_data, $variation ) {
		$variation_object = wc_get_product( $variation->ID );
		?>
		<div class="variable_cpd_pricing">
			<?php do_action( 'cpd_options_variation_pricing', $variation_object, $loop ); ?>
		</div>
		<?php
	}


	/**
	 * Save extra meta info for variable products
	 */
	public static function save_product_variation( $variation, $i ) {
		if ( is_numeric( $variation ) ) {
			$variation = wc_get_product( $variation );
		}

		$cpd_custom_amount_min = '';

		// Set CPD status.
		$variation_is_cpd = isset( $_POST['cpd_custom_amount_enabled'][ $i ] ) ? 'yes' : 'no';
		$variation->update_meta_data( 'cpd_custom_amount_enabled', $variation_is_cpd );

		// Save minimum amount.
		$cpd_custom_amount_min = isset( $_POST['cpd_custom_amount_min'] ) && isset( $_POST['cpd_custom_amount_min'][ $i ] ) ? wc_format_decimal( wc_clean( wp_unslash( $_POST['cpd_custom_amount_min'][ $i ] ) ) ) : '';
		$variation->update_meta_data( 'cpd_custom_amount_min', $cpd_custom_amount_min );

		// If CPD, set prices to minimum.
		if ( 'yes' === $variation_is_cpd ) {
			$new_price = '' === $cpd_custom_amount_min ? 0 : $cpd_custom_amount_min;
			$variation->set_price( $new_price );
			$variation->set_regular_price( $new_price );
		}

		$variation->save();
	}

}

CPD_Custom_Amount_Product_Meta::init();
