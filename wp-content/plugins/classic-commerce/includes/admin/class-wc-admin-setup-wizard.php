<?php
/**
 * Setup Wizard Class
 *
 * Takes new users through some basic steps to setup their store.
 *
 * @package     ClassicCommerce/Admin
 * @version     WC-2.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Admin_Setup_Wizard class.
 */
class WC_Admin_Setup_Wizard {

	/**
	 * Current step
	 *
	 * @var string
	 */
	private $step = '';

	/**
	 * Steps for the setup wizard
	 *
	 * @var array
	 */
	private $steps = array();

	/**
	 * Actions to be executed after the HTTP response has completed
	 *
	 * @var array
	 */
	private $deferred_actions = array();

	/**
	 * Tweets user can optionally send after install
	 *
	 * @var array
	 */
	private $tweets = array(
		'Someone give me woo-t, I just set up a new store',
		'Someone give me high five, I just set up a new store',
	);

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		if ( apply_filters( 'woocommerce_enable_setup_wizard', true ) && current_user_can( 'manage_woocommerce' ) ) {
			add_action( 'admin_menu', array( $this, 'admin_menus' ) );
			add_action( 'admin_init', array( $this, 'setup_wizard' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}
	}

	/**
	 * Add admin menus/screens.
	 */
	public function admin_menus() {
		add_dashboard_page( '', '', 'manage_options', 'wc-setup', '' );
	}

	/**
	 * The theme "extra" should only be shown if the current user can modify themes
	 * and the store doesn't already have a Classic Commerce theme.
	 *
	 * @return boolean
	 */
	protected function should_show_theme() {
		$support_woocommerce = current_theme_supports( 'woocommerce' ) && ! $this->is_default_theme();

		return (
			current_user_can( 'install_themes' ) &&
			current_user_can( 'switch_themes' ) &&
			! is_multisite() &&
			! $support_woocommerce
		);
	}

	/**
	 * Is the user using a default WP theme?
	 *
	 * @return boolean
	 */
	protected function is_default_theme() {
		return wc_is_active_theme(
			array(
				'classicpress-twentyseventeen',
				'classicpress-twentysixteen',
				'classicpress-twentyfifteen',
				'twentyseventeen',
				'twentysixteen',
				'twentyfifteen',
			)
		);
	}

	/**
	 * Should we display the 'Recommended' step?
	 * True if at least one of the recommendations will be displayed.
	 *
	 * @return boolean
	 */
	protected function should_show_recommended_step() {
		return $this->should_show_theme();
	}

	/**
	 * Register/enqueue scripts and styles for the Setup Wizard.
	 *
	 * Hooked onto 'admin_enqueue_scripts'.
	 */
	public function enqueue_scripts() {
		$suffix          = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array( 'jquery' ), '2.70', true );
		wp_register_script( 'selectWoo', WC()->plugin_url() . '/assets/js/selectWoo/selectWoo.full' . $suffix . '.js', array( 'jquery' ), '1.0.5' );
		wp_register_script( 'wc-enhanced-select', WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select' . $suffix . '.js', array( 'jquery', 'selectWoo' ), WC_VERSION );
		wp_localize_script(
			'wc-enhanced-select',
			'wc_enhanced_select_params',
			array(
				'i18n_no_matches'           => _x( 'No matches found', 'enhanced select', 'classic-commerce' ),
				'i18n_ajax_error'           => _x( 'Loading failed', 'enhanced select', 'classic-commerce' ),
				'i18n_input_too_short_1'    => _x( 'Please enter 1 or more characters', 'enhanced select', 'classic-commerce' ),
				'i18n_input_too_short_n'    => _x( 'Please enter %qty% or more characters', 'enhanced select', 'classic-commerce' ),
				'i18n_input_too_long_1'     => _x( 'Please delete 1 character', 'enhanced select', 'classic-commerce' ),
				'i18n_input_too_long_n'     => _x( 'Please delete %qty% characters', 'enhanced select', 'classic-commerce' ),
				'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', 'classic-commerce' ),
				'i18n_selection_too_long_n' => _x( 'You can only select %qty% items', 'enhanced select', 'classic-commerce' ),
				'i18n_load_more'            => _x( 'Loading more results&hellip;', 'enhanced select', 'classic-commerce' ),
				'i18n_searching'            => _x( 'Searching&hellip;', 'enhanced select', 'classic-commerce' ),
				'ajax_url'                  => admin_url( 'admin-ajax.php' ),
				'search_products_nonce'     => wp_create_nonce( 'search-products' ),
				'search_customers_nonce'    => wp_create_nonce( 'search-customers' ),
			)
		);
		wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
		wp_enqueue_style( 'wc-setup', WC()->plugin_url() . '/assets/css/wc-setup.css', array( 'dashicons', 'install' ), WC_VERSION );

		wp_register_script( 'wc-setup', WC()->plugin_url() . '/assets/js/admin/wc-setup' . $suffix . '.js', array( 'jquery', 'wc-enhanced-select', 'jquery-blockui', 'wp-util', 'jquery-tiptip' ), WC_VERSION );
		wp_localize_script(
			'wc-setup',
			'wc_setup_params',
			array(
				'states'                  => WC()->countries->get_states(),
				'current_step'            => isset( $this->steps[ $this->step ] ) ? $this->step : false,
				'i18n'                    => array(
					'extra_plugins' => array(
						'payment' => array(),
					),
				),
			)
		);
	}

	/**
	 * Show the setup wizard.
	 */
	public function setup_wizard() {
		if ( empty( $_GET['page'] ) || 'wc-setup' !== $_GET['page'] ) { // WPCS: CSRF ok, input var ok.
			return;
		}
		$default_steps = array(
			'store_setup' => array(
				'name'    => __( 'Store setup', 'classic-commerce' ),
				'view'    => array( $this, 'wc_setup_store_setup' ),
				'handler' => array( $this, 'wc_setup_store_setup_save' ),
			),
			'payment'     => array(
				'name'    => __( 'Payment', 'classic-commerce' ),
				'view'    => array( $this, 'wc_setup_payment' ),
				'handler' => array( $this, 'wc_setup_payment_save' ),
			),
			'shipping'    => array(
				'name'    => __( 'Shipping', 'classic-commerce' ),
				'view'    => array( $this, 'wc_setup_shipping' ),
				'handler' => array( $this, 'wc_setup_shipping_save' ),
			),
			'next_steps'  => array(
				'name'    => __( 'Ready!', 'classic-commerce' ),
				'view'    => array( $this, 'wc_setup_ready' ),
				'handler' => '',
			),
		);

		// Hide recommended step if nothing is going to be shown there.
		if ( ! $this->should_show_recommended_step() ) {
			unset( $default_steps['recommended'] );
		}

		// Hide shipping step if the store is selling digital products only.
		if ( 'virtual' === get_option( 'woocommerce_product_type' ) ) {
			unset( $default_steps['shipping'] );
		}

		// Hide activate section when the user does not have capabilities to install plugins, think multiside admins not being a super admin.
		if ( ! current_user_can( 'install_plugins' ) ) {
			unset( $default_steps['activate'] );
		}

		$this->steps = apply_filters( 'woocommerce_setup_wizard_steps', $default_steps );
		$this->step  = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) ); // WPCS: CSRF ok, input var ok.

		// @codingStandardsIgnoreStart
		if ( ! empty( $_POST['save_step'] ) && isset( $this->steps[ $this->step ]['handler'] ) ) {
			call_user_func( $this->steps[ $this->step ]['handler'], $this );
		}
		// @codingStandardsIgnoreEnd

		ob_start();
		$this->setup_wizard_header();
		$this->setup_wizard_steps();
		$this->setup_wizard_content();
		$this->setup_wizard_footer();
		exit;
	}

	/**
	 * Get the URL for the next step's screen.
	 *
	 * @param string $step  slug (default: current step).
	 * @return string       URL for next step if a next step exists.
	 *                      Admin URL if it's the last step.
	 *                      Empty string on failure.
	 * @since WC-3.0.0
	 */
	public function get_next_step_link( $step = '' ) {
		if ( ! $step ) {
			$step = $this->step;
		}

		$keys = array_keys( $this->steps );
		if ( end( $keys ) === $step ) {
			return admin_url();
		}

		$step_index = array_search( $step, $keys, true );
		if ( false === $step_index ) {
			return '';
		}

		return add_query_arg( 'step', $keys[ $step_index + 1 ], remove_query_arg( 'activate_error' ) );
	}

	/**
	 * Setup Wizard Header.
	 */
	public function setup_wizard_header() {
		set_current_screen();
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta name="viewport" content="width=device-width" />
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<title><?php esc_html_e( 'Classic Commerce &rsaquo; Setup Wizard', 'classic-commerce' ); ?></title>
			<?php do_action( 'admin_enqueue_scripts' ); ?>
			<?php wp_print_scripts( 'wc-setup' ); ?>
			<?php do_action( 'admin_print_styles' ); ?>
			<?php do_action( 'admin_head' ); ?>
		</head>
		<body class="wc-setup wp-core-ui">
			<h1 id="wc-logo"><a href="https://github.com/ClassicPress-plugins/classic-commerce/"><img src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/classic-commerce-logo.png" alt="Classic Commerce" /></a></h1>
		<?php
	}

	/**
	 * Setup Wizard Footer.
	 */
	public function setup_wizard_footer() {
		?>
			<?php if ( 'store_setup' === $this->step ) : ?>
				<a class="wc-setup-footer-links" href="<?php echo esc_url( admin_url() ); ?>"><?php esc_html_e( 'Not right now', 'classic-commerce' ); ?></a>
			<?php elseif ( 'recommended' === $this->step || 'activate' === $this->step ) : ?>
				<a class="wc-setup-footer-links" href="<?php echo esc_url( $this->get_next_step_link() ); ?>"><?php esc_html_e( 'Skip this step', 'classic-commerce' ); ?></a>
			<?php endif; ?>
			</body>
		</html>
		<?php
	}

	/**
	 * Output the steps.
	 */
	public function setup_wizard_steps() {
		$output_steps      = $this->steps;

		?>
		<ol class="wc-setup-steps">
			<?php
			foreach ( $output_steps as $step_key => $step ) {
				$is_completed = array_search( $this->step, array_keys( $this->steps ), true ) > array_search( $step_key, array_keys( $this->steps ), true );

				if ( $step_key === $this->step ) {
					?>
					<li class="active"><?php echo esc_html( $step['name'] ); ?></li>
					<?php
				} elseif ( $is_completed ) {
					?>
					<li class="done">
						<a href="<?php echo esc_url( add_query_arg( 'step', $step_key, remove_query_arg( 'activate_error' ) ) ); ?>"><?php echo esc_html( $step['name'] ); ?></a>
					</li>
					<?php
				} else {
					?>
					<li><?php echo esc_html( $step['name'] ); ?></li>
					<?php
				}
			}
			?>
		</ol>
		<?php
	}

	/**
	 * Output the content for the current step.
	 */
	public function setup_wizard_content() {
		echo '<div class="wc-setup-content">';
		if ( ! empty( $this->steps[ $this->step ]['view'] ) ) {
			call_user_func( $this->steps[ $this->step ]['view'], $this );
		}
		echo '</div>';
	}

	/**
	 * Initial "store setup" step.
	 * Location, product type, page setup.
	 */
	public function wc_setup_store_setup() {
		$address        = WC()->countries->get_base_address();
		$address_2      = WC()->countries->get_base_address_2();
		$city           = WC()->countries->get_base_city();
		$state          = WC()->countries->get_base_state();
		$country        = WC()->countries->get_base_country();
		$postcode       = WC()->countries->get_base_postcode();
		$currency       = get_option( 'woocommerce_currency', 'GBP' );
		$product_type   = get_option( 'woocommerce_product_type', 'both' );
		$sell_in_person = get_option( 'woocommerce_sell_in_person', 'none_selected' );

		if ( empty( $country ) ) {
			$user_location = WC_Geolocation::geolocate_ip();
			$country       = $user_location['country'];
			$state         = $user_location['state'];
		}

		$locale_info         = include WC()->plugin_path() . '/i18n/locale-info.php';
		$currency_by_country = wp_list_pluck( $locale_info, 'currency_code' );
		?>
		<form method="post" class="address-step">
			<?php wp_nonce_field( 'wc-setup' ); ?>
			<p class="store-setup"><?php esc_html_e( 'The following wizard will help you configure your store and get you started quickly.', 'classic-commerce' ); ?></p>

			<div class="store-address-container">

				<label for="store_country" class="location-prompt"><?php esc_html_e( 'Where is your store based?', 'classic-commerce' ); ?></label>
				<select id="store_country" name="store_country" required data-placeholder="<?php esc_attr_e( 'Choose a country&hellip;', 'classic-commerce' ); ?>" aria-label="<?php esc_attr_e( 'Country', 'classic-commerce' ); ?>" class="location-input wc-enhanced-select dropdown">
					<?php foreach ( WC()->countries->get_countries() as $code => $label ) : ?>
						<option <?php selected( $code, $country ); ?> value="<?php echo esc_attr( $code ); ?>"><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>

				<label class="location-prompt" for="store_address"><?php esc_html_e( 'Address', 'classic-commerce' ); ?></label>
				<input type="text" id="store_address" class="location-input" name="store_address" required value="<?php echo esc_attr( $address ); ?>" />

				<label class="location-prompt" for="store_address_2"><?php esc_html_e( 'Address line 2', 'classic-commerce' ); ?></label>
				<input type="text" id="store_address_2" class="location-input" name="store_address_2" value="<?php echo esc_attr( $address_2 ); ?>" />

				<div class="city-and-postcode">
					<div>
						<label class="location-prompt" for="store_city"><?php esc_html_e( 'City', 'classic-commerce' ); ?></label>
						<input type="text" id="store_city" class="location-input" name="store_city" required value="<?php echo esc_attr( $city ); ?>" />
					</div>
					<div class="store-state-container hidden">
						<label for="store_state" class="location-prompt">
							<?php esc_html_e( 'State', 'classic-commerce' ); ?>
						</label>
						<select id="store_state" name="store_state" data-placeholder="<?php esc_attr_e( 'Choose a state&hellip;', 'classic-commerce' ); ?>" aria-label="<?php esc_attr_e( 'State', 'classic-commerce' ); ?>" class="location-input wc-enhanced-select dropdown"></select>
					</div>
					<div>
						<label class="location-prompt" for="store_postcode"><?php esc_html_e( 'Postcode / ZIP', 'classic-commerce' ); ?></label>
						<input type="text" id="store_postcode" class="location-input" name="store_postcode" required value="<?php echo esc_attr( $postcode ); ?>" />
					</div>
				</div>
			</div>

			<div class="store-currency-container">
			<label class="location-prompt" for="currency_code">
				<?php esc_html_e( 'What currency do you accept payments in?', 'classic-commerce' ); ?>
			</label>
			<select
				id="currency_code"
				name="currency_code"
				required
				data-placeholder="<?php esc_attr_e( 'Choose a currency&hellip;', 'classic-commerce' ); ?>"
				class="location-input wc-enhanced-select dropdown"
			>
				<option value=""><?php esc_html_e( 'Choose a currency&hellip;', 'classic-commerce' ); ?></option>
				<?php foreach ( get_woocommerce_currencies() as $code => $name ) : ?>
					<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $currency, $code ); ?>>
						<?php
						$symbol = get_woocommerce_currency_symbol( $code );

						if ( $symbol === $code ) {
							/* translators: 1: currency name 2: currency code */
							echo esc_html( sprintf( __( '%1$s (%2$s)', 'classic-commerce' ), $name, $code ) );
						} else {
							/* translators: 1: currency name 2: currency symbol, 3: currency code */
							echo esc_html( sprintf( __( '%1$s (%2$s %3$s)', 'classic-commerce' ), $name, get_woocommerce_currency_symbol( $code ), $code ) );
						}
						?>
					</option>
				<?php endforeach; ?>
			</select>
			<script type="text/javascript">
				var wc_setup_currencies = JSON.parse( decodeURIComponent( '<?php echo rawurlencode( wp_json_encode( $currency_by_country ) ); ?>' ) );
				var wc_base_state       = "<?php echo esc_js( $state ); ?>";
			</script>
			</div>

			<div class="product-type-container">
			<label class="location-prompt" for="product_type">
				<?php esc_html_e( 'What type of products do you plan to sell?', 'classic-commerce' ); ?>
			</label>
			<select id="product_type" name="product_type" required class="location-input wc-enhanced-select dropdown">
				<option value="both" <?php selected( $product_type, 'both' ); ?>><?php esc_html_e( 'I plan to sell both physical and digital products', 'classic-commerce' ); ?></option>
				<option value="physical" <?php selected( $product_type, 'physical' ); ?>><?php esc_html_e( 'I plan to sell physical products', 'classic-commerce' ); ?></option>
				<option value="virtual" <?php selected( $product_type, 'virtual' ); ?>><?php esc_html_e( 'I plan to sell digital products', 'classic-commerce' ); ?></option>
			</select>
			</div>

			<input
				type="checkbox"
				id="woocommerce_sell_in_person"
				name="sell_in_person"
				value="yes"
				<?php checked( $sell_in_person, true ); ?>
			/>
			<label class="location-prompt" for="woocommerce_sell_in_person">
				<?php esc_html_e( 'I will also be selling products or services in person.', 'classic-commerce' ); ?>
			</label>

			<p class="wc-setup-actions step">
				<button type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( "Let's go!", 'classic-commerce' ); ?>" name="save_step"><?php esc_html_e( "Let's go!", 'classic-commerce' ); ?></button>
			</p>
		</form>
		<?php
	}

	/**
	 * Save initial store settings.
	 */
	public function wc_setup_store_setup_save() {
		check_admin_referer( 'wc-setup' );

		// phpcs:disable WordPress.VIP.SuperGlobalInputUsage.AccessDetected, WordPress.VIP.ValidatedSanitizedInput.InputNotValidated, WordPress.VIP.ValidatedSanitizedInput.MissingUnslash
		$address        = sanitize_text_field( $_POST['store_address'] );
		$address_2      = sanitize_text_field( $_POST['store_address_2'] );
		$city           = sanitize_text_field( $_POST['store_city'] );
		$country        = sanitize_text_field( $_POST['store_country'] );
		$state          = isset( $_POST['store_state'] ) ? sanitize_text_field( $_POST['store_state'] ) : false;
		$postcode       = sanitize_text_field( $_POST['store_postcode'] );
		$currency_code  = sanitize_text_field( $_POST['currency_code'] );
		$product_type   = sanitize_text_field( $_POST['product_type'] );
		$sell_in_person = isset( $_POST['sell_in_person'] ) && ( 'yes' === sanitize_text_field( $_POST['sell_in_person'] ) );
		// phpcs:enable

		if ( ! $state ) {
			$state = '*';
		}

		update_option( 'woocommerce_store_address', $address );
		update_option( 'woocommerce_store_address_2', $address_2 );
		update_option( 'woocommerce_store_city', $city );
		update_option( 'woocommerce_default_country', $country . ':' . $state );
		update_option( 'woocommerce_store_postcode', $postcode );
		update_option( 'woocommerce_currency', $currency_code );
		update_option( 'woocommerce_product_type', $product_type );
		update_option( 'woocommerce_sell_in_person', $sell_in_person );

		$locale_info = include WC()->plugin_path() . '/i18n/locale-info.php';

		if ( isset( $locale_info[ $country ] ) ) {
			update_option( 'woocommerce_weight_unit', $locale_info[ $country ]['weight_unit'] );
			update_option( 'woocommerce_dimension_unit', $locale_info[ $country ]['dimension_unit'] );

			// Set currency formatting options based on chosen location and currency.
			if ( $locale_info[ $country ]['currency_code'] === $currency_code ) {
				update_option( 'woocommerce_currency_pos', $locale_info[ $country ]['currency_pos'] );
				update_option( 'woocommerce_price_decimal_sep', $locale_info[ $country ]['decimal_sep'] );
				update_option( 'woocommerce_price_num_decimals', $locale_info[ $country ]['num_decimals'] );
				update_option( 'woocommerce_price_thousand_sep', $locale_info[ $country ]['thousand_sep'] );
			}
		}

		WC_Install::create_pages();
		wp_safe_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}

	/**
	 * Finishes replying to the client, but keeps the process running for further (async) code execution.
	 *
	 * @see https://core.trac.wordpress.org/ticket/41358 .
	 */
	protected function close_http_connection() {
		// Only 1 PHP process can access a session object at a time, close this so the next request isn't kept waiting.
		// @codingStandardsIgnoreStart
		if ( session_id() ) {
			session_write_close();
		}
		// @codingStandardsIgnoreEnd

		wc_set_time_limit( 0 );

		// fastcgi_finish_request is the cleanest way to send the response and keep the script running, but not every server has it.
		if ( is_callable( 'fastcgi_finish_request' ) ) {
			fastcgi_finish_request();
		} else {
			// Fallback: send headers and flush buffers.
			if ( ! headers_sent() ) {
				header( 'Connection: close' );
			}
			@ob_end_flush(); // @codingStandardsIgnoreLine.
			flush();
		}
	}

	/**
	 * Function called after the HTTP request is finished, so it's executed without the client having to wait for it.
	 *
	 * @see WC_Admin_Setup_Wizard::install_plugin
	 * @see WC_Admin_Setup_Wizard::install_theme
	 */
	public function run_deferred_actions() {
		$this->close_http_connection();
		foreach ( $this->deferred_actions as $action ) {
			call_user_func_array( $action['func'], $action['args'] );

			// Clear the background installation flag if this is a plugin.
			if (
				isset( $action['func'][1] ) &&
				'background_installer' === $action['func'][1] &&
				isset( $action['args'][0] )
			) {
				delete_option( 'woocommerce_setup_background_installing_' . $action['args'][0] );
			}
		}
	}

	/**
	 * Helper method to queue the background install of a plugin.
	 *
	 * @param string $plugin_id  Plugin id used for background install.
	 * @param array  $plugin_info Plugin info array containing name and repo-slug, and optionally file if different from [repo-slug].php.
	 */
	protected function install_plugin( $plugin_id, $plugin_info ) {
		// Make sure we don't trigger multiple simultaneous installs.
		if ( get_option( 'woocommerce_setup_background_installing_' . $plugin_id ) ) {
			return;
		}

		$plugin_file = isset( $plugin_info['file'] ) ? $plugin_info['file'] : $plugin_info['repo-slug'] . '.php';
		if ( is_plugin_active( $plugin_info['repo-slug'] . '/' . $plugin_file ) ) {
			return;
		}

		if ( empty( $this->deferred_actions ) ) {
			add_action( 'shutdown', array( $this, 'run_deferred_actions' ) );
		}

		array_push(
			$this->deferred_actions,
			array(
				'func' => array( 'WC_Install', 'background_installer' ),
				'args' => array( $plugin_id, $plugin_info ),
			)
		);

		// Set the background installation flag for this plugin.
		update_option( 'woocommerce_setup_background_installing_' . $plugin_id, true );
	}


	/**
	 * Helper method to queue the background install of a theme.
	 *
	 * @param string $theme_id  Theme id used for background install.
	 */
	protected function install_theme( $theme_id ) {
		if ( empty( $this->deferred_actions ) ) {
			add_action( 'shutdown', array( $this, 'run_deferred_actions' ) );
		}
		array_push(
			$this->deferred_actions,
			array(
				'func' => array( 'WC_Install', 'theme_background_installer' ),
				'args' => array( $theme_id ),
			)
		);
	}

	/**
	 * Retrieve info for missing WooCommerce Services.
	 *
	 * @return array
	 */
	protected function get_wcs_requisite_plugins() {
		$plugins = array();
		return $plugins;
	}

	/**
	 * Get shipping methods based on country code.
	 *
	 * @param string $country_code Country code.
	 * @param string $currency_code Currency code.
	 * @return array
	 */
	protected function get_wizard_shipping_methods( $country_code, $currency_code ) {
		$shipping_methods = array(
			'flat_rate'     => array(
				'name'        => __( 'Flat Rate', 'classic-commerce' ),
				'description' => __( 'Set a fixed price to cover shipping costs.', 'classic-commerce' ),
				'settings'    => array(
					'cost' => array(
						'type'          => 'text',
						'default_value' => __( 'Cost', 'classic-commerce' ),
						'description'   => __( 'What would you like to charge for flat rate shipping?', 'classic-commerce' ),
						'required'      => true,
					),
				),
			),
			'free_shipping' => array(
				'name'        => __( 'Free Shipping', 'classic-commerce' ),
				'description' => __( "Don't charge for shipping.", 'classic-commerce' ),
			),
		);

		return $shipping_methods;
	}

	/**
	 * Render the available shipping methods for a given country code.
	 *
	 * @param string $country_code Country code.
	 * @param string $currency_code Currency code.
	 * @param string $input_prefix Input prefix.
	 */
	protected function shipping_method_selection_form( $country_code, $currency_code, $input_prefix ) {
		$selected          = 'flat_rate';
		$shipping_methods  = $this->get_wizard_shipping_methods( $country_code, $currency_code );
		?>
		<div class="wc-wizard-shipping-method-select">
			<div class="wc-wizard-shipping-method-dropdown">
				<select
					id="<?php echo esc_attr( "{$input_prefix}[method]" ); ?>"
					name="<?php echo esc_attr( "{$input_prefix}[method]" ); ?>"
					class="method wc-enhanced-select"
					data-plugins="<?php echo wc_esc_json( wp_json_encode( $this->get_wcs_requisite_plugins() ) ); ?>"
				>
				<?php foreach ( $shipping_methods as $method_id => $method ) : ?>
					<option value="<?php echo esc_attr( $method_id ); ?>" <?php selected( $selected, $method_id ); ?>><?php echo esc_html( $method['name'] ); ?></option>
				<?php endforeach; ?>
				</select>
			</div>
			<div class="shipping-method-descriptions">
				<?php foreach ( $shipping_methods as $method_id => $method ) : ?>
					<p class="shipping-method-description <?php echo esc_attr( $method_id ); ?> <?php echo $method_id !== $selected ? 'hide' : ''; ?>">
						<?php echo esc_html( $method['description'] ); ?>
					</p>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="shipping-method-settings">
		<?php foreach ( $shipping_methods as $method_id => $method ) : ?>
			<?php
			if ( empty( $method['settings'] ) ) {
				continue;
			}
			?>
			<div class="shipping-method-setting <?php echo esc_attr( $method_id ); ?> <?php echo $method_id !== $selected ? 'hide' : ''; ?>">
			<?php foreach ( $method['settings'] as $setting_id => $setting ) : ?>
				<?php $method_setting_id = "{$input_prefix}[{$method_id}][{$setting_id}]"; ?>
				<input
					type="<?php echo esc_attr( $setting['type'] ); ?>"
					placeholder="<?php echo esc_attr( $setting['default_value'] ); ?>"
					id="<?php echo esc_attr( $method_setting_id ); ?>"
					name="<?php echo esc_attr( $method_setting_id ); ?>"
					class="<?php echo esc_attr( $setting['required'] ? 'shipping-method-required-field' : '' ); ?>"
					<?php echo ( $method_id === $selected && $setting['required'] ) ? 'required' : ''; ?>
				/>
				<p class="description">
					<?php echo esc_html( $setting['description'] ); ?>
				</p>
			<?php endforeach; ?>
			</div>
		<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Render a product weight unit dropdown.
	 *
	 * @return string
	 */
	protected function get_product_weight_selection() {
		$weight_unit = get_option( 'woocommerce_weight_unit' );
		ob_start();
		?>
		<span class="wc-setup-shipping-unit">
			<select id="weight_unit" name="weight_unit" class="wc-enhanced-select">
				<option value="kg" <?php selected( $weight_unit, 'kg' ); ?>><?php esc_html_e( 'Kilograms', 'classic-commerce' ); ?></option>
				<option value="g" <?php selected( $weight_unit, 'g' ); ?>><?php esc_html_e( 'Grams', 'classic-commerce' ); ?></option>
				<option value="lbs" <?php selected( $weight_unit, 'lbs' ); ?>><?php esc_html_e( 'Pounds', 'classic-commerce' ); ?></option>
				<option value="oz" <?php selected( $weight_unit, 'oz' ); ?>><?php esc_html_e( 'Ounces', 'classic-commerce' ); ?></option>
			</select>
		</span>
		<?php

		return ob_get_clean();
	}

	/**
	 * Render a product dimension unit dropdown.
	 *
	 * @return string
	 */
	protected function get_product_dimension_selection() {
		$dimension_unit = get_option( 'woocommerce_dimension_unit' );
		ob_start();
		?>
		<span class="wc-setup-shipping-unit">
			<select id="dimension_unit" name="dimension_unit" class="wc-enhanced-select">
				<option value="m" <?php selected( $dimension_unit, 'm' ); ?>><?php esc_html_e( 'Meters', 'classic-commerce' ); ?></option>
				<option value="cm" <?php selected( $dimension_unit, 'cm' ); ?>><?php esc_html_e( 'Centimeters', 'classic-commerce' ); ?></option>
				<option value="mm" <?php selected( $dimension_unit, 'mm' ); ?>><?php esc_html_e( 'Millimeters', 'classic-commerce' ); ?></option>
				<option value="in" <?php selected( $dimension_unit, 'in' ); ?>><?php esc_html_e( 'Inches', 'classic-commerce' ); ?></option>
				<option value="yd" <?php selected( $dimension_unit, 'yd' ); ?>><?php esc_html_e( 'Yards', 'classic-commerce' ); ?></option>
			</select>
		</span>
		<?php

		return ob_get_clean();
	}

	/**
	 * Shipping.
	 */
	public function wc_setup_shipping() {
		$country_code          = WC()->countries->get_base_country();
		$country_name          = WC()->countries->countries[ $country_code ];
		$prefixed_country_name = WC()->countries->estimated_for_prefix( $country_code ) . $country_name;
		$currency_code         = get_woocommerce_currency();
		$existing_zones        = WC_Shipping_Zones::get_zones();
		$intro_text            = '';

		if ( empty( $existing_zones ) ) {
			$intro_text = sprintf(
				/* translators: %s: country name including the 'the' prefix if needed */
				__( "We've created two Shipping Zones - for %s and for the rest of the world. Below you can set Flat Rate shipping costs for these Zones or offer Free Shipping.", 'classic-commerce' ),
				$prefixed_country_name
			);
		}

		?>
		<h1><?php esc_html_e( 'Shipping', 'classic-commerce' ); ?></h1>
		<?php if ( $intro_text ) : ?>
			<p><?php echo wp_kses_post( $intro_text ); ?></p>
		<?php endif; ?>
		<form method="post">
			<?php if ( empty( $existing_zones ) ) : ?>
				<ul class="wc-wizard-services shipping">
					<li class="wc-wizard-service-item">
						<div class="wc-wizard-service-name">
							<p><?php echo esc_html_e( 'Shipping Zone', 'classic-commerce' ); ?></p>
						</div>
						<div class="wc-wizard-service-description">
							<p><?php echo esc_html_e( 'Shipping Method', 'classic-commerce' ); ?></p>
						</div>
					</li>
					<li class="wc-wizard-service-item">
						<div class="wc-wizard-service-name">
							<p><?php echo esc_html( $country_name ); ?></p>
						</div>
						<div class="wc-wizard-service-description">
							<?php $this->shipping_method_selection_form( $country_code, $currency_code, 'shipping_zones[domestic]' ); ?>
						</div>
						<div class="wc-wizard-service-enable">
							<span class="wc-wizard-service-toggle">
								<input id="shipping_zones[domestic][enabled]" type="checkbox" name="shipping_zones[domestic][enabled]" value="yes" checked="checked" class="wc-wizard-shipping-method-enable" data-plugins="true" />
								<label for="shipping_zones[domestic][enabled]">
							</span>
						</div>
					</li>
					<li class="wc-wizard-service-item">
						<div class="wc-wizard-service-name">
							<p><?php echo esc_html_e( 'Locations not covered by your other zones', 'classic-commerce' ); ?></p>
						</div>
						<div class="wc-wizard-service-description">
							<?php $this->shipping_method_selection_form( $country_code, $currency_code, 'shipping_zones[intl]' ); ?>
						</div>
						<div class="wc-wizard-service-enable">
							<span class="wc-wizard-service-toggle">
								<input id="shipping_zones[intl][enabled]" type="checkbox" name="shipping_zones[intl][enabled]" value="yes" checked="checked" class="wc-wizard-shipping-method-enable" data-plugins="true" />
								<label for="shipping_zones[intl][enabled]">
							</span>
						</div>
					</li>
				</ul>
			<?php endif; ?>

			</ul>

			<div class="wc-setup-shipping-units">
				<p>
					<?php
						echo wp_kses(
							sprintf(
								/* translators: %1$s: weight unit dropdown, %2$s: dimension unit dropdown */
								esc_html__( 'We\'ll use %1$s for product weight and %2$s for product dimensions.', 'classic-commerce' ),
								$this->get_product_weight_selection(),
								$this->get_product_dimension_selection()
							),
							array(
								'span' => array(
									'class' => array(),
								),
								'select' => array(
									'id'    => array(),
									'name'  => array(),
									'class' => array(),
								),
								'option' => array(
									'value'    => array(),
									'selected' => array(),
								),
							)
						);
					?>
				</p>
			</div>

			<p class="wc-setup-actions step">
				<button type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Continue', 'classic-commerce' ); ?>" name="save_step"><?php esc_html_e( 'Continue', 'classic-commerce' ); ?></button>
				<?php wp_nonce_field( 'wc-setup' ); ?>
			</p>
		</form>
		<?php
	}

	/**
	 * Save shipping options.
	 */
	public function wc_setup_shipping_save() {
		check_admin_referer( 'wc-setup' );

		// @codingStandardsIgnoreStart
		$setup_domestic   = isset( $_POST['shipping_zones']['domestic']['enabled'] ) && ( 'yes' === $_POST['shipping_zones']['domestic']['enabled'] );
		$domestic_method  = isset( $_POST['shipping_zones']['domestic']['method'] ) ? sanitize_text_field( wp_unslash( $_POST['shipping_zones']['domestic']['method'] ) ) : '';
		$setup_intl       = isset( $_POST['shipping_zones']['intl']['enabled'] ) && ( 'yes' === $_POST['shipping_zones']['intl']['enabled'] );
		$intl_method      = isset( $_POST['shipping_zones']['intl']['method'] ) ? sanitize_text_field( wp_unslash( $_POST['shipping_zones']['intl']['method'] ) ) : '';
		$weight_unit      = sanitize_text_field( wp_unslash( $_POST['weight_unit'] ) );
		$dimension_unit   = sanitize_text_field( wp_unslash( $_POST['dimension_unit'] ) );
		$existing_zones   = WC_Shipping_Zones::get_zones();
		// @codingStandardsIgnoreEnd

		update_option( 'woocommerce_ship_to_countries', '' );
		update_option( 'woocommerce_weight_unit', $weight_unit );
		update_option( 'woocommerce_dimension_unit', $dimension_unit );

		// For now, limit this setup to the first run.
		if ( ! empty( $existing_zones ) ) {
			wp_safe_redirect( esc_url_raw( $this->get_next_step_link() ) );
			exit;
		}

		/*
		 * If enabled, create a shipping zone containing the country the
		 * store is located in, with the selected method preconfigured.
		 */
		if ( $setup_domestic ) {
			$country = WC()->countries->get_base_country();

			$zone = new WC_Shipping_Zone( null );
			$zone->set_zone_order( 0 );
			$zone->add_location( $country, 'country' );
			$instance_id = $zone->add_shipping_method( $domestic_method );
			$zone->save();

			// Save chosen shipping method settings (using REST controller for convenience).
			if ( isset( $instance_id ) && ! empty( $_POST['shipping_zones']['domestic'][ $domestic_method ] ) ) { // WPCS: input var ok.
				$method_controller = new WC_REST_Shipping_Zone_Methods_Controller();
				// @codingStandardsIgnoreStart
				$method_controller->update_item( array(
					'zone_id'     => $zone->get_id(),
					'instance_id' => $instance_id,
					'settings'    => wp_unslash( $_POST['shipping_zones']['domestic'][ $domestic_method ] ),
				) );
				// @codingStandardsIgnoreEnd
			}
		}

		// If enabled, set the selected method for the "rest of world" zone.
		if ( $setup_intl ) {
			$zone        = new WC_Shipping_Zone( 0 );
			$instance_id = $zone->add_shipping_method( $intl_method );

			$zone->save();

			// Save chosen shipping method settings (using REST controller for convenience).
			if ( isset( $instance_id ) && ! empty( $_POST['shipping_zones']['intl'][ $intl_method ] ) ) { // WPCS: input var ok.
				$method_controller = new WC_REST_Shipping_Zone_Methods_Controller();
				// @codingStandardsIgnoreStart
				$method_controller->update_item( array(
					'zone_id'     => $zone->get_id(),
					'instance_id' => $instance_id,
					'settings'    => wp_unslash( $_POST['shipping_zones']['intl'][ $intl_method ] ),
				) );
				// @codingStandardsIgnoreEnd
			}
		}

		// Notify the user that no shipping methods are configured.
		if ( ! $setup_domestic && ! $setup_intl ) {
			WC_Admin_Notices::add_notice( 'no_shipping_methods' );
		}

		wp_safe_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}

	/**
	 * Simple array of "manual" gateways to show in wizard.
	 *
	 * @return array
	 */
	protected function get_wizard_manual_payment_gateways() {
		$gateways = array(
			'cheque' => array(
				'name'        => _x( 'Check payments', 'Check payment method', 'classic-commerce' ),
				'description' => __( 'A simple offline gateway that lets you accept a check as method of payment.', 'classic-commerce' ),
				'image'       => '',
				'class'       => '',
			),
			'bacs'   => array(
				'name'        => __( 'Bank transfer (BACS) payments', 'classic-commerce' ),
				'description' => __( 'A simple offline gateway that lets you accept BACS payment.', 'classic-commerce' ),
				'image'       => '',
				'class'       => '',
			),
			'cod'    => array(
				'name'        => __( 'Cash on delivery', 'classic-commerce' ),
				'description' => __( 'A simple offline gateway that lets you accept cash on delivery.', 'classic-commerce' ),
				'image'       => '',
				'class'       => '',
			),
		);

		return $gateways;
	}

	/**
	 * Display service item in list.
	 *
	 * @param int   $item_id Item ID.
	 * @param array $item_info Item info array.
	 */
	public function display_service_item( $item_id, $item_info ) {
		$item_class = 'wc-wizard-service-item';
		if ( isset( $item_info['class'] ) ) {
			$item_class .= ' ' . $item_info['class'];
		}

		$previously_saved_settings = get_option( 'woocommerce_' . $item_id . '_settings' );

		// Show the user-saved state if it was previously saved.
		// Otherwise, rely on the item info.
		if ( is_array( $previously_saved_settings ) ) {
			$should_enable_toggle = isset( $previously_saved_settings['enabled'] ) && 'yes' === $previously_saved_settings['enabled'];
		} else {
			$should_enable_toggle = isset( $item_info['enabled'] ) && $item_info['enabled'];
		}

		$plugins = null;
		if ( isset( $item_info['repo-slug'] ) ) {
			$plugin  = array(
				'slug' => $item_info['repo-slug'],
				'name' => $item_info['name'],
			);
			$plugins = array( $plugin );
		}

		?>
		<li class="<?php echo esc_attr( $item_class ); ?>">
			<div class="wc-wizard-service-name">
				<?php if ( ! empty( $item_info['image'] ) ) : ?>
					<img src="<?php echo esc_attr( $item_info['image'] ); ?>" alt="<?php echo esc_attr( $item_info['name'] ); ?>" />
				<?php else : ?>
					<p><?php echo esc_html( $item_info['name'] ); ?></p>
				<?php endif; ?>
			</div>
			<div class="wc-wizard-service-description">
				<?php echo wp_kses_post( wpautop( $item_info['description'] ) ); ?>
				<?php if ( ! empty( $item_info['settings'] ) ) : ?>
					<div class="wc-wizard-service-settings <?php echo $should_enable_toggle ? '' : 'hide'; ?>">
						<?php foreach ( $item_info['settings'] as $setting_id => $setting ) : ?>
							<?php
							$is_checkbox = 'checkbox' === $setting['type'];

							if ( $is_checkbox ) {
								$checked = false;
								if ( isset( $previously_saved_settings[ $setting_id ] ) ) {
									$checked = 'yes' === $previously_saved_settings[ $setting_id ];
								} elseif ( false === $previously_saved_settings && isset( $setting['default'] ) ) {
									$checked = 'yes' === $setting['default'];
								}
							}
							if ( 'email' === $setting['type'] ) {
								$value = empty( $previously_saved_settings[ $setting_id ] )
									? $setting['value']
									: $previously_saved_settings[ $setting_id ];
							}
							?>
							<?php $input_id = $item_id . '_' . $setting_id; ?>
							<div class="<?php echo esc_attr( 'wc-wizard-service-setting-' . $input_id ); ?>">
								<label
									for="<?php echo esc_attr( $input_id ); ?>"
									class="<?php echo esc_attr( $input_id ); ?>"
								>
									<?php echo esc_html( $setting['label'] ); ?>
								</label>
								<input
									type="<?php echo esc_attr( $setting['type'] ); ?>"
									id="<?php echo esc_attr( $input_id ); ?>"
									class="<?php echo esc_attr( 'payment-' . $setting['type'] . '-input' ); ?>"
									name="<?php echo esc_attr( $input_id ); ?>"
									value="<?php echo esc_attr( isset( $value ) ? $value : $setting['value'] ); ?>"
									placeholder="<?php echo esc_attr( $setting['placeholder'] ); ?>"
									<?php echo ( $setting['required'] ) ? 'required' : ''; ?>
									<?php echo $is_checkbox ? checked( isset( $checked ) && $checked, true, false ) : ''; ?>
									data-plugins="<?php echo wc_esc_json( wp_json_encode( isset( $setting['plugins'] ) ? $setting['plugins'] : null ) ); ?>"
								/>
								<?php if ( ! empty( $setting['description'] ) ) : ?>
									<span class="wc-wizard-service-settings-description"><?php echo esc_html( $setting['description'] ); ?></span>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
			<div class="wc-wizard-service-enable">
				<span class="wc-wizard-service-toggle <?php echo esc_attr( $should_enable_toggle ? '' : 'disabled' ); ?>">
					<input
						id="wc-wizard-service-<?php echo esc_attr( $item_id ); ?>"
						type="checkbox"
						name="wc-wizard-service-<?php echo esc_attr( $item_id ); ?>-enabled"
						value="yes" <?php checked( $should_enable_toggle ); ?>
						data-plugins="<?php echo wc_esc_json( wp_json_encode( $plugins ) ); ?>"
					/>
					<label for="wc-wizard-service-<?php echo esc_attr( $item_id ); ?>">
				</span>
			</div>
		</li>
		<?php
	}

	/**
	 * Payment Step.
	 */
	public function wc_setup_payment() {
		$manual_gateways   = $this->get_wizard_manual_payment_gateways();
		?>
		<h1><?php esc_html_e( 'Payment', 'classic-commerce' ); ?></h1>
		<form method="post" class="wc-wizard-payment-gateway-form">
			<p>
				<?php
				printf(
					wp_kses(
						/* translators: %s: Link */
						__( 'Classic Commerce can accept both online and offline payments. A <a href="%1$s" target="_blank">basic PayPal option</a> is already included. This can be enabled and configured from the Payments tab under Settings. <a href="%2$s" target="_blank">Additional payment methods</a> can be installed later.', 'classic-commerce' ),
						array(
							'a' => array(
								'href'   => array(),
								'target' => array(),
							),
						)
					),
					esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=paypal' ) ),
					esc_url( admin_url( 'admin.php?page=wc-addons' ) )
				);
				?>
			</p>
			<ul class="wc-wizard-services manual">
				<li class="wc-wizard-services-list-toggle closed">
					<div class="wc-wizard-service-name">
						<?php esc_html_e( 'Offline Payments', 'classic-commerce' ); ?>
					</div>
					<div class="wc-wizard-service-description">
						<?php esc_html_e( 'Collect payments from customers offline.', 'classic-commerce' ); ?>
					</div>
					<div class="wc-wizard-service-enable">
						<input class="wc-wizard-service-list-toggle" id="wc-wizard-service-list-toggle" type="checkbox">
						<label for="wc-wizard-service-list-toggle"></label>
					</div>
				</li>
				<?php
				foreach ( $manual_gateways as $gateway_id => $gateway ) {
					$this->display_service_item( $gateway_id, $gateway );
				}
				?>
			</ul>
			<p class="wc-setup-actions step">
				<button type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Continue', 'classic-commerce' ); ?>" name="save_step"><?php esc_html_e( 'Continue', 'classic-commerce' ); ?></button>
				<?php wp_nonce_field( 'wc-setup' ); ?>
			</p>
		</form>
		<?php
	}

	/**
	 * Payment Step save.
	 */
	public function wc_setup_payment_save() {
		check_admin_referer( 'wc-setup' );

		$gateways = $this->get_wizard_manual_payment_gateways();

		foreach ( $gateways as $gateway_id => $gateway ) {
			// If repo-slug is defined, download and install plugin from .org.
			if ( ! empty( $gateway['repo-slug'] ) && ! empty( $_POST[ 'wc-wizard-service-' . $gateway_id . '-enabled' ] ) ) { // WPCS: CSRF ok, input var ok.
				$this->install_plugin( $gateway_id, $gateway );
			}

			$settings = array( 'enabled' => ! empty( $_POST[ 'wc-wizard-service-' . $gateway_id . '-enabled' ] ) ? 'yes' : 'no' );  // WPCS: CSRF ok, input var ok.

			// @codingStandardsIgnoreStart
			if ( ! empty( $gateway['settings'] ) ) {
				foreach ( $gateway['settings'] as $setting_id => $setting ) {
					$settings[ $setting_id ] = 'yes' === $settings['enabled'] && isset( $_POST[ $gateway_id . '_' . $setting_id ] )
						? wc_clean( wp_unslash( $_POST[ $gateway_id . '_' . $setting_id ] ) )
						: false;
				}
			}

			$settings_key = 'woocommerce_' . $gateway_id . '_settings';
			$previously_saved_settings = array_filter( (array) get_option( $settings_key, array() ) );
			update_option( $settings_key, array_merge( $previously_saved_settings, $settings ) );
		}

		wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}

	protected function display_recommended_item( $item_info ) {
		$type        = $item_info['type'];
		$title       = $item_info['title'];
		$description = $item_info['description'];
		$img_url     = $item_info['img_url'];
		$img_alt     = $item_info['img_alt'];
		?>
		<li class="recommended-item checkbox">
			<input
				id="<?php echo esc_attr( 'wc_recommended_' . $type ); ?>"
				type="checkbox"
				name="<?php echo esc_attr( 'setup_' . $type ); ?>"
				value="yes"
				checked
				data-plugins="<?php echo wc_esc_json( wp_json_encode( isset( $item_info['plugins'] ) ? $item_info['plugins'] : null ) ); ?>"
			/>
			<label for="<?php echo esc_attr( 'wc_recommended_' . $type ); ?>">
				<img
					src="<?php echo esc_url( $img_url ); ?>"
					class="<?php echo esc_attr( 'recommended-item-icon-' . $type ); ?> recommended-item-icon"
					alt="<?php echo esc_attr( $img_alt ); ?>" />
				<div class="recommended-item-description-container">
					<h3><?php echo esc_html( $title ); ?></h3>
					<p><?php echo wp_kses( $description, array(
						'a' => array(
							'href'   => array(),
							'target' => array(),
							'rel'    => array(),
						),
						'em' => array(),
					) ); ?></p>
				</div>
			</label>
		</li>
		<?php
	}

	/**
	 * Recommended step
	 */
	public function wc_setup_recommended() {
		?>
		<h1><?php esc_html_e( 'Recommended for all Classic Commerce stores', 'classic-commerce' ); ?></h1>

		<form method="post">
			<p class="wc-setup-actions step">
				<button type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Continue', 'classic-commerce' ); ?>" name="save_step"><?php esc_html_e( 'Continue', 'classic-commerce' ); ?></button>
				<?php wp_nonce_field( 'wc-setup' ); ?>
			</p>
		</form>
		<?php
	}

	/**
	 * Final step.
	 */
	public function wc_setup_ready() {
		// We've made it! Don't prompt the user to run the wizard again.
		WC_Admin_Notices::remove_notice( 'install' );

		$videos_url   = 'https://docs.woocommerce.com/document/woocommerce-guided-tour-videos/';
		$docs_url     = 'https://docs.woocommerce.com/documentation/plugins/woocommerce/getting-started/';
		$help_text    = sprintf(
			/* translators: %1$s: link to videos, %2$s: link to docs */
			__( 'Watch <a href="%1$s" target="_blank">guided tour videos</a> to learn more about Classic Commerce, or visit WooCommerce.com to learn more about <a href="%2$s" target="_blank">getting started</a>.', 'classic-commerce' ),
			$videos_url,
			$docs_url
		);
		?>
		<h1><?php esc_html_e( "You're ready to start selling!", 'classic-commerce' ); ?></h1>

		<ul class="wc-wizard-next-steps">
			<li class="wc-wizard-next-step-item">
				<div class="wc-wizard-next-step-description">
					<p class="next-step-heading"><?php esc_html_e( 'Next step', 'classic-commerce' ); ?></p>
					<h3 class="next-step-description"><?php esc_html_e( 'Create some products', 'classic-commerce' ); ?></h3>
					<p class="next-step-extra-info"><?php esc_html_e( "You're ready to add products to your store.", 'classic-commerce' ); ?></p>
				</div>
				<div class="wc-wizard-next-step-action">
					<p class="wc-setup-actions step">
						<a class="button button-primary button-large" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=product&tutorial=true' ) ); ?>">
							<?php esc_html_e( 'Create a product', 'classic-commerce' ); ?>
						</a>
					</p>
				</div>
			</li>
			<li class="wc-wizard-next-step-item">
				<div class="wc-wizard-next-step-description">
					<p class="next-step-heading"><?php esc_html_e( 'Have an existing store?', 'classic-commerce' ); ?></p>
					<h3 class="next-step-description"><?php esc_html_e( 'Import products', 'classic-commerce' ); ?></h3>
					<p class="next-step-extra-info"><?php esc_html_e( 'Transfer existing products to your new store — just import a CSV file.', 'classic-commerce' ); ?></p>
				</div>
				<div class="wc-wizard-next-step-action">
					<p class="wc-setup-actions step">
						<a class="button button-large" href="<?php echo esc_url( admin_url( 'edit.php?post_type=product&page=product_importer' ) ); ?>">
							<?php esc_html_e( 'Import products', 'classic-commerce' ); ?>
						</a>
					</p>
				</div>
			</li>
			<li class="wc-wizard-additional-steps">
				<div class="wc-wizard-next-step-description">
					<p class="next-step-heading"><?php esc_html_e( 'You can also:', 'classic-commerce' ); ?></p>
				</div>
				<div class="wc-wizard-next-step-action">
					<p class="wc-setup-actions step">
						<a class="button button-large" href="<?php echo esc_url( admin_url() ); ?>">
							<?php esc_html_e( 'Visit Dashboard', 'classic-commerce' ); ?>
						</a>
						<a class="button button-large" href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings' ) ); ?>">
							<?php esc_html_e( 'Review Settings', 'classic-commerce' ); ?>
						</a>
						<a class="button button-large" href="<?php echo esc_url( add_query_arg( array( 'autofocus' => array( 'panel' => 'woocommerce' ), 'url' => wc_get_page_permalink( 'shop' ) ), admin_url( 'customize.php' ) ) ); ?>">
							<?php esc_html_e( 'View &amp; Customize', 'classic-commerce' ); ?>
						</a>
					</p>
				</div>
			</li>
		</ul>
		<p class="next-steps-help-text"><?php echo wp_kses_post( $help_text ); ?></p>
		<?php
	}
}

new WC_Admin_Setup_Wizard();