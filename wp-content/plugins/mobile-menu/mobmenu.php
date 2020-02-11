<?php

/**
 * Plugin Name: Mobile Menu
 * Description: An easy to use WordPress responsive mobile menu. Keep your mobile visitors engaged.
 * Version: 2.8
 * Plugin URI: https://www.wpmobilemenu.com/
 * Author: Rui Guerreiro
 * Author URI: https://www.jedipress.com/
 * Tested up to: 5.3
 * Text Domain: mobile-menu
 * Domain Path: /languages/
 * License: GPLv2
 *
 */
if ( !defined( 'ABSPATH' ) ) {
    die;
}
define( 'WP_MOBILE_MENU_VERSION', '2.8' );
define( 'WP_MOBILE_MENU_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'WP_MOBILE_MENU_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
if ( !class_exists( 'WP_Mobile_Menu' ) ) {
    /**
     * Main Mobile Menu class
     */
    class WP_Mobile_Menu
    {
        public  $mm_fs ;
        public  $mobmenu_core ;
        /**
         * Constructor
         *
         * @since 1.0
         */
        public function __construct()
        {
        }
        
        public function wp_mobile_menu_custom_admin_notice()
        {
            ?>

				<div class="wp-mobile-menu-notice notice notice-success is-dismissible" data-ajax-nonce="<?php 
            echo  wp_create_nonce( 'wp-mobile-menu-security-nonce' ) ;
            ?>">
					<span class="dashicons dashicons-warning"></span>

					<?php 
            _e( '<strong>Getting Started with WP Mobile Menu - </strong> You can start with an already existing demo just go to General Options-> Import and Export. Check our knowledgebase <a href="https://www.wpmobilemenu.com/knowledgebase/?utm_source=wprepo-dash&utm_medium=user%20website&utm_campaign=getting-started-notice" target="_blank" >site</a> as a starting point.', 'mobile-menu' );
            ?>
				</div>

		<?php 
        }
        
        /**
         * Init WP Mobile Menu
         *
         * @since 1.0
         */
        public function init_mobile_menu()
        {
            global  $hook ;
            // Init Freemius.
            $this->mm_fs = $this->mm_fs();
            // Uninstall Action.
            $this->mm_fs->add_action( 'after_uninstall', array( $this, 'mm_fs_uninstall_cleanup' ) );
            // Signal that parent SDK was initiated.
            do_action( 'mm_fs_loaded' );
            // Include Required files.
            $this->include_required_files();
            // Instanciate the Menu Options.
            new WP_Mobile_Menu_Options();
            // Instanciate the Mobile Menu Core Functions.
            $this->mobmenu_core = new WP_Mobile_Menu_Core();
            // Hooks.
            if ( is_admin() ) {
                // Admin Scripts.
                add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
            }
            // Sidebar Menu Widgets.
            add_action( 'wp_loaded', array( $this->mobmenu_core, 'register_sidebar' ) );
            // Register Menus.
            add_action( 'init', array( $this->mobmenu_core, 'register_menus' ) );
            // Load frontend assets.
            if ( !is_admin() ) {
                add_action( 'init', array( $this, 'load_frontend_assets' ) );
            }
            // Load Translation Text Domain.
            add_action( 'plugins_loaded', array( $this, 'mm_load_textdomain' ) );
            // Load Ajax actions.
            $this->load_ajax_actions();
        }
        
        /**
         * Load Text Domain
         *
         * @since 2.6
         */
        public function mm_load_textdomain()
        {
            load_plugin_textdomain( 'mobile-menu', false, basename( dirname( __FILE__ ) ) . '/languages/' );
        }
        
        /**
         * Init Freemius Settings
         *
         * @since 1.0
         */
        public function mm_fs()
        {
            global  $mm_fs ;
            
            if ( !isset( $this->mm_fs ) ) {
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/freemius/start.php';
                $mm_fs = fs_dynamic_init( array(
                    'id'              => '235',
                    'slug'            => 'mobile-menu',
                    'type'            => 'plugin',
                    'public_key'      => 'pk_1ec93edfb66875251b62505b96489',
                    'premium_suffix'  => 'Premium',
                    'is_premium'      => false,
                    'has_addons'      => false,
                    'has_paid_plans'  => true,
                    'has_affiliation' => 'selected',
                    'trial'           => array(
                    'days'               => 14,
                    'is_require_payment' => true,
                ),
                    'menu'            => array(
                    'slug' => 'mobile-menu-options',
                ),
                    'is_live'         => true,
                ) );
            }
            
            return $mm_fs;
        }
        
        /**
         * Uninstall Cleanup
         *
         * @since 2.7.3
         */
        public function mm_fs_uninstall_cleanup()
        {
        }
        
        /**
         * Include required files
         *
         * @since 1.0
         */
        private function include_required_files()
        {
            require_once dirname( __FILE__ ) . '/options-framework/titan-framework/titan-framework-embedder.php';
            require_once dirname( __FILE__ ) . '/includes/class-wp-mobile-menu-core.php';
            require_once dirname( __FILE__ ) . '/includes/class-wp-mobile-menu-options.php';
            require_once dirname( __FILE__ ) . '/includes/class-wp-mobile-menu-walker-nav-menu.php';
        }
        
        /**
         * Load Frontend Assets
         *
         * @since 1.0
         */
        public function load_frontend_assets()
        {
            $mobmenu_options = TitanFramework::getInstance( 'mobmenu' );
            $is_mobile_only = $mobmenu_options->getOption( 'only_mobile_devices' );
            $is_testing_mode = $mobmenu_options->getOption( 'only_testing_mode' );
            
            if ( $is_testing_mode && current_user_can( 'administrator' ) || !$is_testing_mode && (!$is_mobile_only || $is_mobile_only && wp_is_mobile()) ) {
                // Enqueue Html to the Footer.
                add_action( 'wp_footer', array( $this->mobmenu_core, 'load_menu_html_markup' ) );
                // Frontend Scripts.
                add_action( 'wp_enqueue_scripts', array( $this->mobmenu_core, 'frontend_enqueue_scripts' ), 100 );
                // Add menu display type class to the body.
                add_action( 'body_class', array( $this->mobmenu_core, 'mobmenu_add_body_class' ) );
                // If it's the Business (Woocommerce) plan.
                if ( $this->mm_fs->is_plan( 'woocommerce_pro' ) ) {
                    // Add to cart fragments to update the mini cart.
                    add_filter(
                        'woocommerce_add_to_cart_fragments',
                        array( $this->mobmenu_core, 'mobmenu_cart_fragments__premium_only' ),
                        100,
                        1
                    );
                }
            }
        
        }
        
        /**
         * Load Ajax actions
         *
         * @since 1.0
         */
        private function load_ajax_actions()
        {
            add_action( 'wp_ajax_get_icons_html', array( $this->mobmenu_core, 'get_icons_html' ) );
            add_action( 'wp_ajax_nopriv_get_icons_html', array( $this->mobmenu_core, 'get_icons_html' ) );
            add_action( 'wp_ajax_dismiss_wp_mobile_notice', array( $this->mobmenu_core, 'dismiss_wp_mobile_notice' ) );
            add_action( 'wp_ajax_nopriv_dismiss_wp_mobile_notice', array( $this->mobmenu_core, 'dismiss_wp_mobile_notice' ) );
            add_action( 'wp_ajax_save_menu_item_icon', array( $this->mobmenu_core, 'save_menu_item_icon' ) );
            add_action( 'wp_ajax_nopriv_save_menu_item_icon', array( $this->mobmenu_core, 'save_menu_item_icon' ) );
            add_action( 'wp_ajax_mobile_menu_search__premium_only', array( $this->mobmenu_core, 'mobile_menu_search__premium_only' ) );
            add_action( 'wp_ajax_nopriv_mobile_menu_search__premium_only', array( $this->mobmenu_core, 'mobile_menu_search__premium_only' ) );
        }
        
        /** Admin Scripts. **/
        public function admin_enqueue_scripts( $hook )
        {
            global  $mm_fs ;
            global  $post_type ;
            
            if ( 'toplevel_page_mobile-menu-options' === $hook && !$mm_fs->is__premium_only() ) {
                if ( !get_option( 'wp_mobile_menu_banner_dismissed' ) ) {
                    add_action( 'admin_notices', array( $this, 'wp_mobile_menu_custom_admin_notice' ) );
                }
                wp_enqueue_style( 'cssmobmenu-admin', plugins_url( 'includes/css/mobmenu-admin.css', __FILE__ ) );
            }
            
            
            if ( 'nav-menus.php' === $hook || 'toplevel_page_mobile-menu-options' === $hook ) {
                wp_enqueue_style( 'cssmobmenu-icons', plugins_url( 'includes/css/mobmenu-icons.css', __FILE__ ) );
                wp_enqueue_style( 'cssmobmenu-admin', plugins_url( 'includes/css/mobmenu-admin.css', __FILE__ ) );
                wp_register_script( 'mobmenu-admin-js', plugins_url( 'includes/js/mobmenu-admin.js', __FILE__ ), array( 'jquery' ) );
                wp_enqueue_script( 'mobmenu-admin-js' );
            }
            
            // Export Settings.
            
            if ( 'toplevel_page_mobile-menu-options' === $hook ) {
                // Export settings.
                if ( isset( $_GET['mobmenu-action'] ) && 'download-settings' === sanitize_text_field( $_GET['mobmenu-action'] ) ) {
                    $this->mobmenu_core->mobile_menu_export_settings();
                }
                // Import settings.
                if ( isset( $_GET['mobmenu-action'] ) && 'import-settings' === sanitize_text_field( $_GET['mobmenu-action'] ) && 'mobile-menu-options' === sanitize_text_field( $_GET['page'] ) ) {
                    add_action( 'mobile_menu_importer_page', array( $this->mobmenu_core, 'mobile_menu_import_settings' ), 1 );
                }
            }
        
        }
    
    }
}
// Instanciate the WP_Mobile_Menu.
$mobile_menu_instance = new WP_Mobile_Menu();
$mobile_menu_instance->init_mobile_menu();