<?php
/**
 * Plugin Name: Donations for ClassicPress
 * Plugin URI: https://github.com/timbocode/cc-donations
 * Description: Frontend interface for donations and subscriptions
 * Version: 1.0.0
 * Author: timbocode
 * Author URI: https://github.com/timbocode
 * Text Domain: cp-donations
 * Domain Path: /languages/
 * Requires at least: 1.0.0
 * Tested up to: 5.5
 * WC requires at least: 3.5.3
 * WC tested up to: 3.5.3
 * CC requires at least: 1.0.0
 * CC tested up to: 1.0.0
 * License: GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
 

defined( 'ABSPATH' ) || exit;

! defined( 'CPDO_VERSION' ) && define( 'CPDO_VERSION', '1.0.0' );
! defined( 'CPDO_URI' ) && define( 'CPDO_URI', plugin_dir_url( __FILE__ ) );
! defined( 'CPDO_PATH' ) && define( 'CPDO_PATH', plugin_dir_path( __FILE__ ) );


if ( !file_exists( WP_PLUGIN_DIR . '/classic-commerce/classic-commerce.php' ) || !is_plugin_active( 'classic-commerce/classic-commerce.php' ) ) {
	add_action( 'admin_notices', 'cc_active_notice' );
	return;
}


if ( ! function_exists( 'cpdo_init' ) ) {
	add_action( 'plugins_loaded', 'cpdo_init', 11 );

	function cpdo_init() {
		if ( ! function_exists( 'WC' ) ) {
			add_action( 'admin_notices', 'cpdo_notice_wc' );
			return;
		}

		if ( ! class_exists( 'CP_Donations' ) && class_exists( 'WC_Product' ) ) {

			class CP_Donations {

				function __construct() {
					add_action( 'admin_menu', array( $this, 'cpdo_admin_menu' ) );
					add_action( 'wp_enqueue_scripts', array( $this, 'cpdo_enqueue_scripts' ), 99 );
					add_action( 'woocommerce_before_variations_form', array( $this, 'cpdo_before_variations_form' ) );
					add_action( 'woocommerce_before_single_product', array( $this, 'cpdo_remove_wc_hooks' ) );
					
					$this->cpdo_set_options();
				}
				
				// TODO: Add to settings page
				function cpdo_set_options() {
					add_option( '_cpdo_donations_page', '2153' );			// The page where the donations are displayed
					add_option( '_cpdo_donations_product_id', '3031' );		// The subscriptions product
				}


				function cpdo_admin_menu() {
					$page_title = 'Donations for ClassicPress';
					$menu_title = 'CP Donations';
					$capability = 'manage_options';
					$menu_slug  = 'cp-donations';
					$function   = array( $this, 'cpdo_admin_settings_page');
					$icon_url   = 'dashicons-money';
					$position   = 30; 

					add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
				}


				function cpdo_remove_wc_hooks() {
					remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
					remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
					remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
					remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
				}


				function cpdo_admin_settings_page() {
				?>
					<div class="cpdonations_settings_page wrap">
						<h1 class="cpdonations_settings_page_title"><?php echo esc_html__( 'Donations for ClassicPress', 'cp-donations' ) . ' ' . CPDO_VERSION; ?></h1>
						<div class="cpdonations_settings_page_content">
							<p>No settings at present but there might be some day.</p>
						</div>
					</div>
				<?php
				}


				function cpdo_enqueue_scripts() {
					wp_enqueue_style( 'cpdo-frontend', CPDO_URI . 'assets/css/frontend.css', false, CPDO_VERSION );
					wp_enqueue_script( 'cpdo-frontend', CPDO_URI . 'assets/js/frontend.js', array( 'jquery' ), CPDO_VERSION, true );
				}


				function cpdo_before_variations_form() {
					global $product;
					$product_id = $product->get_id();
					$this->cpdo_variations_form( $product );
				}


				static function cpdo_data_attributes( $attrs ) {
					$attrs_arr = array();
					foreach ( $attrs as $key => $attr ) {
						$attrs_arr[] = 'data-' . sanitize_title( $key ) . '="' . esc_attr( $attr ) . '"';
					}
					return implode( ' ', $attrs_arr );
				}


				public static function cpdo_variations_form( $product ) {
					$product_id = $product->get_id();

					$df_attrs_arr = array();
					$df_attrs     = $product->get_default_attributes();

					if ( ! empty( $df_attrs ) ) {
						foreach ( $df_attrs as $key => $val ) {
							$df_attrs_arr[ 'attribute_' . $key ] = $val;
						}
					}

					$children = $product->get_children();

					if ( is_array( $children ) && count( $children ) > 0 ) {
						// Choose an option
						echo '<div class="cpdo-variations cpdo-variations-default" data-click="0" >';

						foreach ( $children as $child ) {
							$child_product = wc_get_product( $child );
							if ( startsWith( $child_product->get_sku(), 'recurring' ) ) {
								$recurring[] = $child;
							}
							else {
								$oneoff[] = $child;
							}
						}

						echo '<div class="cpdo-col cpdo-col-1">';
						echo '<h4>Recurring donations</h4>';
						self::cpdo_display_donations( $recurring, $product_id );
						echo '</div>';

						echo '<div  class="cpdo-col cpdo-col-2">';
						echo '<h4>One-time donations</h4>';
						self::cpdo_display_donations( $oneoff, $product_id );
						echo '</div>';

						echo '</div><!-- /cpdo-variations -->';
					}
				}


				public static function cpdo_display_donations( $don_data, $product_id ) {
					foreach ($don_data as $child ) {
						$child_product = wc_get_product( $child );

						if ( ! $child_product || ! $child_product->variation_is_visible() ) {
							continue;
						}

						$child_attrs	= htmlspecialchars( json_encode( $child_product->get_variation_attributes() ), ENT_QUOTES, 'UTF-8' );
						$child_class	= 'cpdo-variation cpdo-variation-radio';
						$child_name		= wc_get_formatted_variation( $child_product, true, false, false );

						$data_attrs = apply_filters( 'cpdo_data_attributes', array(
							'id'            => $child,
							'sku'           => $child_product->get_sku(),
							'attrs'         => $child_attrs,
							'price'         => wc_get_price_to_display( $child_product ),
							'regular-price' => wc_get_price_to_display( $child_product, array( 'price' => $child_product->get_regular_price() ) ),
						), $child_product );

						echo '<div class="' . esc_attr( $child_class ) . '" ' . self::cpdo_data_attributes( $data_attrs ) . '>';
						echo apply_filters( 'cpdo_variation_radio_selector', '<div class="cpdo-variation-selector"><input type="radio" name="cpdo_variation_' . $product_id . '"/></div>', $product_id );
						echo '<div class="cpdo-variation-info">';
						echo '<div class="cpdo-variation-name">' . $child_name . '</div>';
						echo '</div><!-- /cpdo-variation-name -->';
						echo '</div><!-- /cpdo-variation-info -->';
					}
				}
			}

			new CP_Donations();
		}
	}
}


function startsWith($string, $startString) { 
	$len = strlen($startString); 
	return (substr($string, 0, $len) === $startString); 
} 


function cpdo_remove_quantity_field( $return, $product ) {
	return true;
}
add_filter( 'woocommerce_is_sold_individually', 'cpdo_remove_quantity_field', 10, 2 );


// Override Template Parts. Props @ozfiddler
function override_woocommerce_template_part( $template, $slug, $name ) {
    $template_directory = plugin_dir_path( __FILE__ ) . 'templates/';
    if ( $name ) {
        $path = $template_directory . "{$slug}-{$name}.php";
    } else {
        $path = $template_directory . "{$slug}.php";
    }
    return file_exists( $path ) ? $path : $template;
}
add_filter( 'wc_get_template_part', 'override_woocommerce_template_part', 10, 3 );


// Override Templates. Props @ozfiddler
function override_woocommerce_template( $template, $template_name, $template_path ) {
    $template_directory = plugin_dir_path( __FILE__ ) . 'templates/';
    $path = $template_directory . $template_name;
    return file_exists( $path ) ? $path : $template;
}
add_filter( 'woocommerce_locate_template', 'override_woocommerce_template', 10, 3 );


if ( ! function_exists( 'cc_active_notice' ) ) {
	function cc_active_notice() {
		echo '<div class="notice error is-dismissible">';
		echo '<p><strong>';
		echo esc_html__( 'Donations for ClassicPress: Classic Commerce must be installed and active.', 'cp-donations' );
		echo '</strong></p>';
		echo '</div>';
	}
}


if ( ! function_exists( 'cpdo_notice_wc' ) ) {
	function cpdo_notice_wc() {
		?>
        <div class="error">
            <p><strong>Donations for ClassicPress</strong> requires Classic Commerce version 1.0.0 or greater.</p>
        </div>
		<?php
	}
}
