<?php

/**
 * WP Mobile Menu options class
 *
 * This will manage the plugin options.
 *
 * @package WP Mobile Menu
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License Version 3
 *
 * @since 1.0
 */
if ( !class_exists( 'WP_Mobile_Menu' ) ) {
    die;
}
/**
 *
 * Class WP_Mobile_Menu_Options.
 *
 * @since 2.0
 */
class WP_Mobile_Menu_Options
{
    /**
     *
     * Class Constructor.
     *
     * @since 2.0
     */
    public function __construct()
    {
        $this->init_options();
    }
    
    /**
     *
     * Initiliaze the Options.
     *
     * @since 2.0
     */
    private function init_options()
    {
        add_action( 'tf_create_options', array( $this, 'create_plugin_options' ) );
    }
    
    /**
     *
     * Create Plugin options.
     *
     * @since 2.0
     */
    public function create_plugin_options()
    {
        global  $mm_fs ;
        $prefix = '';
        $menus = get_terms( 'nav_menu', array(
            'hide_empty' => true,
        ) );
        $menus_options = array();
        $menus_options[''] = __( 'Choose one menu', 'mobile-menu' );
        $icons_positions = array();
        $icon_types = array();
        // Initialize Titan with my special unique namespace.
        $titan = TitanFramework::getInstance( 'mobmenu' );
        foreach ( $menus as $menu ) {
            $menus_options[$menu->name] = $menu->name;
        }
        $icon_types = array(
            'image' => __( 'Image', 'mobile-menu' ),
            'icon'  => __( 'Icon', 'mobile-menu' ),
        );
        $display_type = array(
            'slideout-push' => __( 'Slideout Push Content', 'mobile-menu' ),
            'slideout-over' => __( 'Slideout Over Content', 'mobile-menu' ),
        );
        $default_header_elements = array(
            'left-menu'  => 'Left Menu',
            'logo'       => 'Logo',
            'right-menu' => 'Right Menu',
        );
        $right_menu_elements = array(
            'logo'       => 'Logo',
            'search'     => 'Search',
            'right-menu' => 'Right Menu',
        );
        $left_menu_elements = array(
            'logo'      => 'Logo',
            'search'    => 'Search',
            'left-menu' => 'Left Menu',
        );
        // Create my admin options panel.
        $panel = $titan->createAdminPanel( array(
            'name'  => 'Mobile Menu Options',
            'title' => __( 'Mobile Menu Options', 'mobile-menu' ),
            'icon'  => 'dashicons-smartphone',
        ) );
        // Premium options.
        // Create options in My Meta Box.
        $mobmenu_metabox = $titan->createMetaBox( array(
            'name'      => __( 'WP Mobile Menu Meta options', 'mobile-menu' ),
            'post_type' => array( 'page' ),
        ) );
        global  $mm_fs ;
        // Create WP Mobile Menu Meta Box options.
        $custom_html = '<style>.dashicons-yes {color:#008000;font-size: 3em;padding-right: 20px;margin-top: -10px;}';
        $custom_html .= '.mm-button-business-upgrade {margin-top: 20px!important;background-color: #FF015C!important;border-color: #fff;color: #fff!important;margin: 20px 10px 20px 10px;text-transform: uppercase;min-height: 35px;padding-top: 5px;border-radius: 6px;}';
        $custom_html .= '</style><p>The features below are available in the Premium versions of WP Mobile Menu.</p>';
        $custom_html .= '<p><span class="dashicons dashicons-yes"></span><strong>' . __( 'Alternative Left Menu', 'mobile-menu' ) . '</strong></p>';
        $custom_html .= '<p><span class="dashicons dashicons-yes"></span><strong>' . __( 'Alternative Right Menu', 'mobile-menu' ) . '</strong></p>';
        $custom_html .= '<p><span class="dashicons dashicons-yes"></span><strong>' . __( 'Alternative Footer Menu', 'mobile-menu' ) . '</strong></p>';
        $custom_html .= '<p><span class="dashicons dashicons-yes"></span><strong>' . __( 'Use page title', 'mobile-menu' ) . '</strong></p>';
        $custom_html .= '<p><a href="' . $mm_fs->get_upgrade_url() . '&cta=metabox-settings" class="button mm-button-business-upgrade">' . __( 'Upgrade Now!', 'mobile-menu' ) . '</a></p>';
        $custom_html .= '</div></div>';
        // Page Metabox Upgrade Content.
        $mobmenu_metabox->createOption( array(
            'name'   => '',
            'type'   => 'custom',
            'custom' => $custom_html,
        ) );
        // Only proceed if we are in the plugin page.
        
        if ( !is_admin() || isset( $_GET['page'] ) && 'mobile-menu-options' === sanitize_text_field( $_GET['page'] ) ) {
            // Create General Options panel.
            $general_tab = $panel->createTab( array(
                'name' => __( 'General Options', 'mobile-menu' ),
            ) );
            // Create Header Options panel.
            $header_tab = $panel->createTab( array(
                'name' => __( 'Header', 'mobile-menu' ),
            ) );
            $this->create_footer_options_upsell( $panel, $titan );
            // Create Left Menu Options panel.
            $left_menu_tab = $panel->createTab( array(
                'name' => __( 'Left Menu', 'mobile-menu' ),
            ) );
            // Create Right Menu Options panel.
            $right_menu_tab = $panel->createTab( array(
                'name' => __( 'Right Menu', 'mobile-menu' ),
            ) );
            // Create Woocommerce options upsell.
            $this->create_woocommerce_options_upsell( $panel, $titan );
            // Create Color Options panel.
            $colors_tab = $panel->createTab( array(
                'name' => __( 'Colors', 'mobile-menu' ),
            ) );
            // Create Fonts panel.
            $fonts_tab = $panel->createTab( array(
                'name' => __( 'Fonts', 'mobile-menu' ),
            ) );
            // Create Documentation panel.
            $documentation_tab = $panel->createTab( array(
                'name' => __( 'Documentation', 'mobile-menu' ),
            ) );
            // Check if it's HTTPS.
            
            if ( is_ssl() ) {
                $doc_url = 'https://wpmobilemenu.com/documentation-iframe/';
            } else {
                $doc_url = 'http://wpmobilemenu.com/documentation-iframe/';
            }
            
            // Documentation IFrame.
            $documentation_tab->createOption( array(
                'type' => 'iframe',
                'url'  => $doc_url,
            ) );
            // Width trigger.
            $general_tab->createOption( array(
                'name'    => __( 'Mobile Menu Visibility(Width Trigger)', 'mobile-menu' ),
                'id'      => 'width_trigger',
                'type'    => 'number',
                'desc'    => __( 'The Mobile menu will appear at this window size. Place it at 5000 to be always visible. ', 'mobile-menu' ),
                'default' => '1024',
                'max'     => '5000',
                'min'     => '479',
                'unit'    => 'px',
            ) );
            $general_tab->createOption( array(
                'type' => 'note',
                'desc' => __( 'The Width trigger field is very important because it determines the width that will show the Mobile Menu. If you want it always visible set it to 5000px', 'mobile-menu' ),
            ) );
            // Enable/Disable only in Mobile Devices.
            $general_tab->createOption( array(
                'name'     => __( 'Enable only in Mobile devices', 'mobile-menu' ),
                'id'       => 'only_mobile_devices',
                'type'     => 'enable',
                'default'  => false,
                'desc'     => __( 'Enable only in Mobiles devices. This will disable the Mobile Menu Visibilty option above (using resolution width trigger).', 'mobile-menu' ),
                'enabled'  => __( 'On', 'mobile-menu' ),
                'disabled' => __( 'Off', 'mobile-menu' ),
            ) );
            // Enable/Disable Testing Mode.
            $general_tab->createOption( array(
                'name'     => __( 'Enable Testing Mode (only visible for admins).', 'mobile-menu' ),
                'id'       => 'only_testing_mode',
                'type'     => 'enable',
                'default'  => false,
                'desc'     => __( 'Enable only for admin users. This will disable the Mobile Menu for all the visitors of your site except the administrator users.', 'mobile-menu' ),
                'enabled'  => __( 'On', 'mobile-menu' ),
                'disabled' => __( 'Off', 'mobile-menu' ),
            ) );
            // Enable/Disable Left Header Menu.
            $general_tab->createOption( array(
                'name'     => __( 'Enable Left Menu', 'mobile-menu' ),
                'id'       => 'enable_left_menu',
                'type'     => 'enable',
                'default'  => true,
                'desc'     => __( 'Enable or disable the WP Mobile Menu Left Menu.', 'mobile-menu' ),
                'enabled'  => __( 'On', 'mobile-menu' ),
                'disabled' => __( 'Off', 'mobile-menu' ),
            ) );
            // Enable/Disable Right Header Menu.
            $general_tab->createOption( array(
                'name'     => __( 'Enable Right Menu', 'mobile-menu' ),
                'id'       => 'enable_right_menu',
                'type'     => 'enable',
                'default'  => false,
                'desc'     => __( 'Enable or disable the WP Mobile Menu without deactivate the plugin.', 'mobile-menu' ),
                'enabled'  => __( 'On', 'mobile-menu' ),
                'disabled' => __( 'Off', 'mobile-menu' ),
            ) );
            $general_tab->createOption( array(
                'name' => __( 'Hide Original Theme Menu', 'mobile-menu' ),
                'type' => 'heading',
            ) );
            $general_tab->createOption( array(
                'type' => 'note',
                'desc' => __( 'If you need help identifying the correct elements just create a new ticket in our <a href="https://www.wpmobilemenu.com/support-contact/?utm_source=wprepo-dash&utm_medium=user%20website&utm_campaign=hide_original_menu_help" target="_blank">support page</a> with your site url and a screenshot of the element you want to hide. We reply fast.', 'mobile-menu' ),
            ) );
            // Hide Html Elements.
            $general_tab->createOption( array(
                'name'    => __( 'Hide Elements', 'mobile-menu' ),
                'id'      => 'hide_elements',
                'type'    => 'text',
                'default' => '',
                'desc'    => __( '<p>This will hide the desired elements when the Mobile menu is trigerred at the chosen width.</p><p>You can use css class or IDs.</p><p> Example: .menu , #nav</p>', 'mobile-menu' ),
            ) );
            $general_tab->createOption( array(
                'name'    => __( 'Hide elements by default', 'mobile-menu' ),
                'id'      => 'default_hided_elements',
                'type'    => 'multicheck',
                'desc'    => __( 'Check the desired elements', 'mobile-menu' ),
                'options' => array(
                '1' => '.nav',
                '2' => '.main-navigation',
                '3' => '.genesis-nav-menu',
                '4' => '#main-header',
                '5' => '#et-top-navigation',
                '6' => '.site-header',
                '7' => '.site-branding',
                '8' => '.ast-mobile-menu-buttons',
                '9' => '.storefront-handheld-footer-bar',
            ),
                'default' => array(
                '1',
                '2',
                '3',
                '4',
                '5',
                '6',
                '7',
                '8',
                '9'
            ),
            ) );
            $general_tab->createOption( array(
                'name' => __( 'Miscelaneous Options', 'mobile-menu' ),
                'type' => 'heading',
            ) );
            // Menu Display Type.
            $general_tab->createOption( array(
                'name'    => __( 'Menu Display Type', 'mobile-menu' ),
                'id'      => 'menu_display_type',
                'type'    => 'select',
                'desc'    => __( 'Choose the display type for the mobile menu.', 'mobile-menu' ),
                'options' => $display_type,
                'default' => 'slideout-over',
            ) );
            // Automatically Close Sub Menus.
            $general_tab->createOption( array(
                'name'     => __( 'Automatically Close Submenus', 'mobile-menu' ),
                'id'       => 'autoclose_submenus',
                'type'     => 'enable',
                'desc'     => __( 'When you open a submenu it automatically closes the other submenus that are open.', 'mobile-menu' ),
                'default'  => false,
                'enabled'  => __( 'On', 'mobile-menu' ),
                'disabled' => __( 'Off', 'mobile-menu' ),
            ) );
            // Menu Border Style.
            $general_tab->createOption( array(
                'name'    => __( 'Menu Items Border Size', 'mobile-menu' ),
                'id'      => 'menu_items_border_size',
                'type'    => 'number',
                'default' => '0',
                'desc'    => __( 'Choose the size of the menu items border.<a href="/wp-admin/admin.php?page=mobile-menu-options&tab=colors" target="_blank">Click here</a> to adjust the color.', 'mobile-menu' ),
                'max'     => '5',
                'min'     => '0',
                'unit'    => 'px',
            ) );
            // Close Menu Icon Font.
            $general_tab->createOption( array(
                'name'    => __( 'Close Icon', 'mobile-menu' ),
                'id'      => 'close_icon_font',
                'type'    => 'text',
                'desc'    => __( '<div class="mobmenu-icon-holder"></div><a href="#" class="mobmenu-icon-picker button">Select menu icon</a>', 'mobile-menu' ),
                'default' => 'cancel-1',
            ) );
            // Close Menu Icon Font Size.
            $general_tab->createOption( array(
                'name'    => __( 'Close Icon Font Size', 'mobile-menu' ),
                'id'      => 'close_icon_font_size',
                'type'    => 'number',
                'desc'    => __( 'Enter the Close Icon Font Size', 'mobile-menu' ),
                'default' => '30',
                'max'     => '100',
                'min'     => '5',
                'unit'    => 'px',
            ) );
            // Submenu Open Icon Font.
            $general_tab->createOption( array(
                'name'    => __( 'Submenu Open Icon', 'mobile-menu' ),
                'id'      => 'submenu_open_icon_font',
                'type'    => 'text',
                'desc'    => __( '<div class="mobmenu-icon-holder"></div><a href="#" class="mobmenu-icon-picker button">Select menu icon</a>', 'mobile-menu' ),
                'default' => 'down-open',
            ) );
            // Submenu Close Icon Font.
            $general_tab->createOption( array(
                'name'    => __( 'Submenu Close Icon', 'mobile-menu' ),
                'id'      => 'submenu_close_icon_font',
                'type'    => 'text',
                'desc'    => __( '<div class="mobmenu-icon-holder"></div><a href="#" class="mobmenu-icon-picker button">Select menu icon</a>', 'mobile-menu' ),
                'default' => 'up-open',
            ) );
            // Submenu Icon Font Size.
            $general_tab->createOption( array(
                'name'    => __( 'Submenu Icon Font Size', 'mobile-menu' ),
                'id'      => 'submenu_icon_font_size',
                'type'    => 'number',
                'desc'    => __( 'Enter the Submenu Icon Font Size', 'mobile-menu' ),
                'default' => '25',
                'max'     => '100',
                'min'     => '0',
                'unit'    => 'px',
            ) );
            $general_tab->createOption( array(
                'name' => __( 'Advanced Options', 'mobile-menu' ),
                'type' => 'heading',
            ) );
            // Sticky Html Elements.
            $general_tab->createOption( array(
                'name'    => __( 'Sticky Html Elements', 'mobile-menu' ),
                'id'      => 'sticky_elements',
                'type'    => 'text',
                'default' => '',
                'desc'    => __( '<p>If you are having issues with sticky elements that dont assume a sticky behaviour, enter the ids or class name that identify that element.</p>', 'mobile-menu' ),
            ) );
            // Custom css.
            $general_tab->createOption( array(
                'name' => __( 'Custom CSS', 'mobile-menu' ),
                'id'   => 'custom_css',
                'type' => 'code',
                'desc' => __( 'Put your custom CSS rules here', 'mobile-menu' ),
                'lang' => 'css',
            ) );
            // Custom js.
            $general_tab->createOption( array(
                'name' => __( 'Custom JS', 'mobile-menu' ),
                'id'   => 'custom_js',
                'type' => 'code',
                'desc' => __( 'Put your custom JS rules here', 'mobile-menu' ),
                'lang' => 'javascript',
            ) );
            $general_tab->createOption( array(
                'name' => __( 'Import and Export', 'mobile-menu' ),
                'type' => 'heading',
            ) );
            // Export settings.
            $general_tab->createOption( array(
                'name'   => __( 'Export Settings', 'mobile-menu' ),
                'type'   => 'custom',
                'custom' => '<button class="button button-secondary export-mobile-menu-settings">' . __( 'Export', 'mobile-menu' ) . '</button>',
            ) );
            // Import settings.
            $general_tab->createOption( array(
                'name'   => __( 'Import Settings', 'mobile-menu' ),
                'type'   => 'custom',
                'custom' => '<button class="button button-secondary import-mobile-menu-settings">' . __( 'Import', 'mobile-menu' ) . '</button>',
            ) );
            // Header Main Options.
            $header_tab->createOption( array(
                'name' => __( 'Main options', 'mobile-menu' ),
                'type' => 'heading',
            ) );
            // Enable/Disable Sticky Header.
            $header_tab->createOption( array(
                'name'     => __( 'Sticky Header', 'mobile-menu' ),
                'id'       => 'enabled_sticky_header',
                'type'     => 'enable',
                'default'  => true,
                'desc'     => __( 'Choose if you want to have the Header Fixed or scrolling with the content.', 'mobile-menu' ),
                'enabled'  => __( 'Yes', 'mobile-menu' ),
                'disabled' => __( 'No', 'mobile-menu' ),
            ) );
            // Enable/Disable Naked Header.
            $header_tab->createOption( array(
                'name'     => __( 'Naked Header', 'mobile-menu' ),
                'id'       => 'enabled_naked_header',
                'type'     => 'enable',
                'default'  => false,
                'desc'     => __( 'Choose if you want to display a naked header with no background color(transparent).', 'mobile-menu' ),
                'enabled'  => __( 'Yes', 'mobile-menu' ),
                'disabled' => __( 'No', 'mobile-menu' ),
            ) );
            // Enable/Disable Logo Url.
            $header_tab->createOption( array(
                'name'     => __( 'Disable Logo/Text', 'mobile-menu' ),
                'id'       => 'disabled_logo_text',
                'type'     => 'enable',
                'default'  => false,
                'desc'     => __( 'Choose if you want to disable the logo/text so it will only display the menu icons in the header.', 'mobile-menu' ),
                'enabled'  => __( 'Yes', 'mobile-menu' ),
                'disabled' => __( 'No', 'mobile-menu' ),
            ) );
            $header_tab->createOption( array(
                'name' => __( 'Header options', 'mobile-menu' ),
                'type' => 'heading',
            ) );
            // Header Shadow.
            $header_tab->createOption( array(
                'name'     => __( 'Header Shadow.', 'mobile-menu' ),
                'id'       => 'header_shadow',
                'type'     => 'enable',
                'default'  => false,
                'desc'     => __( 'Choose if you want to enable the header shadow at the bottom of the header.', 'mobile-menu' ),
                'enabled'  => __( 'Yes', 'mobile-menu' ),
                'disabled' => __( 'No', 'mobile-menu' ),
            ) );
            // Header Height.
            $header_tab->createOption( array(
                'name'    => __( 'Header Height', 'mobile-menu' ),
                'id'      => 'header_height',
                'type'    => 'number',
                'desc'    => __( 'Enter the height of the header', 'mobile-menu' ),
                'default' => '50',
                'max'     => '500',
                'min'     => '20',
                'unit'    => 'px',
            ) );
            // Header Text.
            $header_tab->createOption( array(
                'name'    => __( 'Header Text', 'mobile-menu' ),
                'id'      => 'header_text',
                'type'    => 'text',
                'desc'    => __( 'Enter the desired text for the Mobile Header. If not specified it will use the site title.', 'mobile-menu' ),
                'default' => '',
            ) );
            // Header Text Font Size.
            $fonts_tab->createOption( array(
                'name'    => __( 'Header Text Font Size', 'mobile-menu' ),
                'id'      => 'header_font_size',
                'type'    => 'number',
                'desc'    => __( 'Enter the header text font size', 'mobile-menu' ),
                'default' => '20',
                'max'     => '100',
                'min'     => '5',
                'unit'    => 'px',
            ) );
            // Header Logo/Text Alignment.
            $header_tab->createOption( array(
                'name'    => 'Header Logo/Text Alignment',
                'id'      => 'header_text_align',
                'type'    => 'select',
                'desc'    => 'Chose the header Logo/Text alignment.',
                'options' => array(
                'left'   => __( 'Left', 'mobile-menu' ),
                'center' => __( 'Center', 'mobile-menu' ),
                'right'  => __( 'Right', 'mobile-menu' ),
            ),
                'default' => 'center',
            ) );
            // Header Logo/Text Left Margin.
            $header_tab->createOption( array(
                'name'    => __( 'Header Logo/Text Left Margin', 'mobile-menu' ),
                'id'      => 'header_text_left_margin',
                'type'    => 'number',
                'desc'    => __( 'Enter the header Logo/Text left margin (only used whit Header Left Alignment)', 'mobile-menu' ),
                'default' => '20',
                'max'     => '200',
                'min'     => '0',
                'unit'    => 'px',
            ) );
            // Header Logo/Text Right Margin.
            $header_tab->createOption( array(
                'name'    => __( 'Header Logo/Text Right Margin', 'mobile-menu' ),
                'id'      => 'header_text_right_margin',
                'type'    => 'number',
                'desc'    => __( 'Enter the header Logo/Text right margin (only used whit Header Right Alignment)', 'mobile-menu' ),
                'default' => '20',
                'max'     => '200',
                'min'     => '0',
                'unit'    => 'px',
            ) );
            $header_tab->createOption( array(
                'name' => __( 'Logo options', 'mobile-menu' ),
                'type' => 'heading',
            ) );
            // Enable/Disable Site Logo.
            $header_tab->createOption( array(
                'name'     => __( 'Site Logo', 'mobile-menu' ),
                'id'       => 'enabled_logo',
                'type'     => 'enable',
                'default'  => false,
                'desc'     => __( 'Choose if you want to display an image has logo or text instead.', 'mobile-menu' ),
                'enabled'  => __( 'Logo', 'mobile-menu' ),
                'disabled' => __( 'Text', 'mobile-menu' ),
            ) );
            $header_branding = array(
                'logo' => __( 'Logo', 'mobile-menu' ),
                'text' => __( 'Text', 'mobile-menu' ),
            );
            
            if ( $titan->getOption( 'enabled_logo' ) ) {
                $default_header_branding = 'logo';
            } else {
                $default_header_branding = 'text';
            }
            
            // Use the page title in the Header or Header Banner(global Option).
            $header_tab->createOption( array(
                'name'    => __( 'Site Logo', 'mobile-menu' ),
                'id'      => 'header_branding',
                'type'    => 'select',
                'desc'    => __( 'Chose the Header Branding ( Logo/Text ).', 'mobile-menu' ),
                'options' => $header_branding,
                'default' => $default_header_branding,
            ) );
            // Site Logo Image.
            $header_tab->createOption( array(
                'name'    => __( 'Logo', 'mobile-menu' ),
                'id'      => 'logo_img',
                'type'    => 'upload',
                'desc'    => __( 'Upload your logo image', 'mobile-menu' ),
                'default' => '',
            ) );
            // Header Height.
            $header_tab->createOption( array(
                'name'    => __( 'Logo Height', 'mobile-menu' ),
                'id'      => 'logo_height',
                'type'    => 'number',
                'desc'    => __( 'Enter the height of the logo', 'mobile-menu' ),
                'default' => '',
                'max'     => '500',
                'min'     => '0',
                'unit'    => 'px',
            ) );
            // Site Logo Retina Image.
            $header_tab->createOption( array(
                'name'    => __( 'Retina Logo', 'mobile-menu' ),
                'id'      => 'logo_img_retina',
                'type'    => 'upload',
                'desc'    => __( 'Upload your logo image for retina devices', 'mobile-menu' ),
                'default' => '',
            ) );
            // Enable/Disable Logo Url.
            $header_tab->createOption( array(
                'name'     => __( 'Disable Logo URL ', 'mobile-menu' ),
                'id'       => 'disabled_logo_url',
                'type'     => 'enable',
                'default'  => false,
                'desc'     => __( 'Choose if you want to disable the logo url to avoid being redirect to the homepage or alternative home url when touching the header logo.', 'mobile-menu' ),
                'enabled'  => __( 'Yes', 'mobile-menu' ),
                'disabled' => __( 'No', 'mobile-menu' ),
            ) );
            // Alternative Site URL.
            $header_tab->createOption( array(
                'name'    => __( 'Alternative Logo URL', 'mobile-menu' ),
                'id'      => 'logo_url',
                'type'    => 'text',
                'desc'    => __( 'Enter you alternative logo URL. If you leave it blank it will use the Site URL.', 'mobile-menu' ),
                'default' => '',
            ) );
            // Logo/text Top Margin.
            $header_tab->createOption( array(
                'name'    => __( 'Logo/Text Top Margin', 'mobile-menu' ),
                'id'      => 'logo_top_margin',
                'type'    => 'number',
                'desc'    => __( 'Enter the logo/text top margin', 'mobile-menu' ),
                'default' => '0',
                'max'     => '450',
                'min'     => '0',
                'unit'    => 'px',
            ) );
            $def_value = $titan->getOption( 'header_font_size' );
            
            if ( $def_value > 0 ) {
                $def_value .= 'px';
            } else {
                $def_value = '';
            }
            
            $fonts_tab->createOption( array(
                'name'                => __( 'Header Menu Font', 'mobile-menu' ),
                'id'                  => 'header_menu_font',
                'type'                => 'font',
                'desc'                => __( 'Select a style', 'mobile-menu' ),
                'show_font_weight'    => true,
                'show_font_style'     => true,
                'show_letter_spacing' => true,
                'show_text_transform' => true,
                'show_font_variant'   => false,
                'show_text_shadow'    => false,
                'show_color'          => false,
                'show_line_height'    => false,
                'default'             => array(
                'font-family' => 'Roboto',
                'font-size'   => $def_value,
            ),
            ) );
            // Left Menu.
            $left_menu_tab->createOption( array(
                'name'    => __( 'Left Menu', 'mobile-menu' ),
                'id'      => 'left_menu',
                'type'    => 'select',
                'desc'    => __( 'Select the menu that will open in the left side.', 'mobile-menu' ),
                'options' => $menus_options,
                'default' => $titan->getOption( 'left_menu' ),
            ) );
            // Click Menu Parent link to open Sub menu.
            $left_menu_tab->createOption( array(
                'name'     => __( 'Parent Link open submenu', 'mobile-menu' ),
                'id'       => 'left_menu_parent_link_submenu',
                'type'     => 'enable',
                'default'  => false,
                'desc'     => __( 'Choose if you want to open the submenu by click in the Parent Menu item.', 'mobile-menu' ),
                'enabled'  => __( 'Yes', 'mobile-menu' ),
                'disabled' => __( 'No', 'mobile-menu' ),
            ) );
            $left_menu_tab->createOption( array(
                'name' => __( 'Menu Icon', 'mobile-menu' ),
                'type' => 'heading',
            ) );
            // Text After Left Icon.
            $left_menu_tab->createOption( array(
                'name'    => __( 'Text After Icon', 'mobile-menu' ),
                'id'      => 'left_menu_text',
                'type'    => 'text',
                'desc'    => __( 'Enter the text that will appear after the Icon.', 'mobile-menu' ),
                'default' => '',
            ) );
            // Text After Left Icon Font Options.
            $fonts_tab->createOption( array(
                'name'                => __( 'Text After Icon Font', 'mobile-menu' ),
                'id'                  => 'text_after_left_icon_font',
                'type'                => 'font',
                'desc'                => __( 'Select a style', 'mobile-menu' ),
                'show_font_weight'    => true,
                'show_font_style'     => true,
                'show_line_height'    => true,
                'show_letter_spacing' => true,
                'show_text_transform' => true,
                'show_font_variant'   => false,
                'show_text_shadow'    => false,
                'show_color'          => true,
                'default'             => array(
                'line-height' => '1.5em',
                'font-family' => 'Dosis',
            ),
            ) );
            // Icon Action Option.
            $left_menu_tab->createOption( array(
                'name'     => __( 'Icon Action', 'mobile-menu' ),
                'id'       => 'left_menu_icon_action',
                'type'     => 'enable',
                'default'  => true,
                'desc'     => __( 'Open the Left Menu Panel or open a Link url.', 'mobile-menu' ),
                'enabled'  => __( 'Open Menu', 'mobile-menu' ),
                'disabled' => __( 'Open Link Url', 'mobile-menu' ),
            ) );
            // Icon URL.
            $left_menu_tab->createOption( array(
                'name'    => __( 'Icon Link URL', 'mobile-menu' ),
                'id'      => 'left_icon_url',
                'type'    => 'text',
                'desc'    => __( 'Enter the Icon Link Url.', 'mobile-menu' ),
                'default' => '',
            ) );
            // Icon URL Target.
            $left_menu_tab->createOption( array(
                'name'     => __( 'Icon Link Url Target', 'mobile-menu' ),
                'id'       => 'left_icon_url_target',
                'type'     => 'enable',
                'default'  => true,
                'desc'     => __( 'Choose it the link will open in the same window or in the new window.', 'mobile-menu' ),
                'enabled'  => 'Self',
                'disabled' => 'Blank',
            ) );
            
            if ( true === $titan->getOption( 'left_menu_icon_opt' ) ) {
                $icon_type = 'image';
            } else {
                $icon_type = 'icon';
            }
            
            // Icon Image/text Option.
            $left_menu_tab->createOption( array(
                'name'    => __( 'Icon Type', 'mobile-menu' ),
                'id'      => 'left_menu_icon_new',
                'type'    => 'select',
                'default' => $icon_type,
                'desc'    => __( 'Choose if you want to display an image, icon or an animated icon.', 'mobile-menu' ),
                'options' => $icon_types,
            ) );
            // Left Menu Icon Font.
            $left_menu_tab->createOption( array(
                'name'    => __( 'Icon Font', 'mobile-menu' ),
                'id'      => 'left_menu_icon_font',
                'type'    => 'text',
                'desc'    => __( '<div class="mobmenu-icon-holder"></div><a href="#" class="mobmenu-icon-picker button">Select menu icon</a>', 'mobile-menu' ),
                'default' => 'menu',
            ) );
            // Left Menu Icon Font Size.
            $left_menu_tab->createOption( array(
                'name'    => __( 'Icon Font Size', 'mobile-menu' ),
                'id'      => 'left_icon_font_size',
                'type'    => 'number',
                'desc'    => __( 'Enter the Left Icon Font Size', 'mobile-menu' ),
                'default' => '30',
                'max'     => '100',
                'min'     => '0',
                'unit'    => 'px',
            ) );
            // Left Menu Icon.
            $left_menu_tab->createOption( array(
                'name'        => __( 'Icon Image', 'mobile-menu' ),
                'id'          => 'left_menu_icon',
                'type'        => 'upload',
                'placeholder' => 'Click here to select the icon',
                'desc'        => __( 'Upload your left menu icon image', 'mobile-menu' ),
                'default'     => 'menu',
            ) );
            // Left Menu Icon Top Margin.
            $left_menu_tab->createOption( array(
                'name'    => __( 'Icon Top Margin', 'mobile-menu' ),
                'id'      => 'left_icon_top_margin',
                'type'    => 'number',
                'desc'    => __( 'Enter the Left Icon Top Margin', 'mobile-menu' ),
                'default' => '0',
                'max'     => '450',
                'min'     => '0',
                'unit'    => 'px',
            ) );
            // Left Menu Icon Left Margin.
            $left_menu_tab->createOption( array(
                'name'    => __( 'Icon Left Margin', 'mobile-menu' ),
                'id'      => 'left_icon_left_margin',
                'type'    => 'number',
                'desc'    => __( 'Enter the Left Icon Left Margin', 'mobile-menu' ),
                'default' => '5',
                'max'     => '450',
                'min'     => '0',
                'unit'    => 'px',
            ) );
            $left_menu_tab->createOption( array(
                'name' => __( 'Left Panel options', 'mobile-menu' ),
                'type' => 'heading',
            ) );
            // Left Menu Background Image.
            $left_menu_tab->createOption( array(
                'name' => __( 'Panel Background Image', 'mobile-menu' ),
                'id'   => 'left_menu_bg_image',
                'type' => 'upload',
                'desc' => __( 'Upload your left menu background image(this will override the Background color option)', 'mobile-menu' ),
            ) );
            // Left Menu Background Image Opacity.
            $left_menu_tab->createOption( array(
                'name'    => __( 'Panel Background Image Opacity', 'mobile-menu' ),
                'id'      => 'left_menu_bg_opacity',
                'type'    => 'number',
                'desc'    => __( 'Enter the Left Background image opacity', 'mobile-menu' ),
                'default' => '100',
                'max'     => '100',
                'min'     => '10',
                'step'    => '10',
                'unit'    => '%',
            ) );
            // Left Menu Background Image Size.
            $left_menu_tab->createOption( array(
                'name'    => __( 'Panel Background Image Size', 'mobile-menu' ),
                'id'      => 'left_menu_bg_image_size',
                'type'    => 'upload',
                'type'    => 'select',
                'desc'    => __( 'Select the Background image size type. <a href="https://www.w3schools.com/cssref/css3_pr_background-size.asp" target="_blank">See the CSS Documentation</a>', 'mobile-menu' ),
                'options' => array(
                'auto'    => __( 'Auto', 'mobile-menu' ),
                'contain' => __( 'Contain', 'mobile-menu' ),
                'cover'   => __( 'Cover', 'mobile-menu' ),
                'inherit' => __( 'Inherit', 'mobile-menu' ),
                'initial' => __( 'Initial', 'mobile-menu' ),
                'unset'   => __( 'Unset', 'mobile-menu' ),
            ),
                'default' => 'cover',
            ) );
            // Left Menu Gradient css.
            $left_menu_tab->createOption( array(
                'name'    => __( 'Panel Background Gradient Css', 'mobile-menu' ),
                'id'      => 'left_menu_bg_gradient',
                'type'    => 'text',
                'desc'    => __( '<a href="https://webgradients.com/" target="_blank">Click here</a> to get your desired Gradient, just press the copy button and paste in this field.', 'mobile-menu' ),
                'default' => '',
            ) );
            // Left Menu Panel Width Units.
            $left_menu_tab->createOption( array(
                'name'     => __( 'Menu Panel Width Units', 'mobile-menu' ),
                'id'       => 'left_menu_width_units',
                'type'     => 'enable',
                'default'  => true,
                'desc'     => __( 'Choose the width units.', 'mobile-menu' ),
                'enabled'  => 'Pixels',
                'disabled' => __( 'Percentage', 'mobile-menu' ),
            ) );
            // Left Menu Panel Width.
            $left_menu_tab->createOption( array(
                'name'    => __( 'Menu Panel Width(Pixels)', 'mobile-menu' ),
                'id'      => 'left_menu_width',
                'type'    => 'number',
                'desc'    => __( 'Enter the Left Menu Panel Width', 'mobile-menu' ),
                'default' => '270',
                'max'     => '1000',
                'min'     => '50',
                'unit'    => 'px',
            ) );
            // Left Menu Panel Width.
            $left_menu_tab->createOption( array(
                'name'    => __( 'Menu Panel Width(Percentage)', 'mobile-menu' ),
                'id'      => 'left_menu_width_percentage',
                'type'    => 'number',
                'desc'    => __( 'Enter the Left Menu Panel Width', 'mobile-menu' ),
                'default' => '70',
                'max'     => '90',
                'min'     => '0',
                'unit'    => '%',
            ) );
            // Left Menu Content Padding.
            $left_menu_tab->createOption( array(
                'name'    => __( 'Left Menu Content Padding', 'mobile-menu' ),
                'id'      => 'left_menu_content_padding',
                'type'    => 'number',
                'desc'    => __( 'Enter the Left Menu Content Padding', 'mobile-menu' ),
                'default' => '10',
                'max'     => '30',
                'min'     => '0',
                'step'    => '1',
                'unit'    => '%',
            ) );
            // Left Menu Font.
            $fonts_tab->createOption( array(
                'name'                => __( 'Left Menu Font', 'mobile-menu' ),
                'id'                  => 'left_menu_font',
                'type'                => 'font',
                'desc'                => __( 'Select a style', 'mobile-menu' ),
                'show_font_weight'    => true,
                'show_font_style'     => true,
                'show_line_height'    => true,
                'show_letter_spacing' => true,
                'show_text_transform' => true,
                'show_font_variant'   => false,
                'show_text_shadow'    => false,
                'show_color'          => false,
                'default'             => array(
                'line-height' => '1.5em',
                'font-family' => 'Dosis',
            ),
            ) );
            // Right Menu.
            $right_menu_tab->createOption( array(
                'name'    => __( 'Right Menu', 'mobile-menu' ),
                'id'      => 'right_menu',
                'type'    => 'select',
                'desc'    => __( 'Select the menu that will open in the right side.', 'mobile-menu' ),
                'options' => $menus_options,
                'default' => $titan->getOption( 'right_menu' ),
            ) );
            // Click Menu Parent link to open Sub menu.
            $right_menu_tab->createOption( array(
                'name'     => __( 'Parent Link open submenu', 'mobile-menu' ),
                'id'       => 'right_menu_parent_link_submenu',
                'type'     => 'enable',
                'default'  => false,
                'desc'     => __( 'Choose if you want to open the submenu by click in the Parent Menu item.', 'mobile-menu' ),
                'enabled'  => __( 'Yes', 'mobile-menu' ),
                'disabled' => __( 'No', 'mobile-menu' ),
            ) );
            // Icon Heading.
            $right_menu_tab->createOption( array(
                'name' => __( 'Menu Icon', 'mobile-menu' ),
                'type' => 'heading',
            ) );
            // Text Before Right Icon.
            $right_menu_tab->createOption( array(
                'name'    => __( 'Text Before Icon', 'mobile-menu' ),
                'id'      => 'right_menu_text',
                'type'    => 'text',
                'desc'    => __( 'Enter the text that will appear before the Icon.', 'mobile-menu' ),
                'default' => '',
            ) );
            // Icon Action Option.
            $right_menu_tab->createOption( array(
                'name'     => __( 'Icon Action', 'mobile-menu' ),
                'id'       => 'right_menu_icon_action',
                'type'     => 'enable',
                'default'  => true,
                'desc'     => __( 'Open the Right Menu Panel or open a Link url.', 'mobile-menu' ),
                'enabled'  => __( 'Open Menu', 'mobile-menu' ),
                'disabled' => __( 'Open Link Url', 'mobile-menu' ),
            ) );
            // Text Before Right Icon Font Options.
            $fonts_tab->createOption( array(
                'name'                => __( 'Text Before Icon Font', 'mobile-menu' ),
                'id'                  => 'text_before_right_icon_font',
                'type'                => 'font',
                'desc'                => __( 'Select a style', 'mobile-menu' ),
                'show_font_weight'    => true,
                'show_font_size'      => true,
                'show_font_style'     => true,
                'show_line_height'    => true,
                'show_letter_spacing' => true,
                'show_text_transform' => true,
                'show_font_variant'   => false,
                'show_text_shadow'    => false,
                'show_color'          => false,
                'default'             => array(
                'line-height' => '1.5em',
                'font-family' => 'Dosis',
            ),
            ) );
            // Icon URL.
            $right_menu_tab->createOption( array(
                'name'    => __( 'Icon Link URL', 'mobile-menu' ),
                'id'      => 'right_icon_url',
                'type'    => 'text',
                'desc'    => __( 'Enter the Icon Link Url.', 'mobile-menu' ),
                'default' => '',
            ) );
            // Icon URL Target.
            $right_menu_tab->createOption( array(
                'name'     => __( 'Icon Link Url Target', 'mobile-menu' ),
                'id'       => 'right_icon_url_target',
                'type'     => 'enable',
                'default'  => true,
                'desc'     => __( 'Choose it the link will open in the same window or in the new window.', 'mobile-menu' ),
                'enabled'  => 'Self',
                'disabled' => 'Blank',
            ) );
            
            if ( true === $titan->getOption( 'right_menu_icon_opt' ) ) {
                $icon_type = 'image';
            } else {
                $icon_type = 'icon';
            }
            
            // Icon Image/text Option.
            $right_menu_tab->createOption( array(
                'name'    => __( 'Icon Type', 'mobile-menu' ),
                'id'      => 'right_menu_icon_new',
                'type'    => 'select',
                'default' => $icon_type,
                'desc'    => __( 'Choose if you want to display an image, icon or an animated icon.', 'mobile-menu' ),
                'options' => $icon_types,
            ) );
            // Right Menu Icon Font.
            $right_menu_tab->createOption( array(
                'name'    => __( 'Icon Font', 'mobile-menu' ),
                'id'      => 'right_menu_icon_font',
                'type'    => 'text',
                'desc'    => __( '<div class="mobmenu-icon-holder"></div><a href="#" class="mobmenu-icon-picker button">Select menu icon</a>', 'mobile-menu' ),
                'default' => 'menu',
            ) );
            // Right Menu Icon Font Size.
            $right_menu_tab->createOption( array(
                'name'    => __( 'Icon Font Size', 'mobile-menu' ),
                'id'      => 'right_icon_font_size',
                'type'    => 'number',
                'desc'    => __( 'Enter the Right Icon Font Size', 'mobile-menu' ),
                'default' => '30',
                'max'     => '100',
                'min'     => '0',
                'unit'    => 'px',
            ) );
            // Right Menu Icon.
            $right_menu_tab->createOption( array(
                'name'    => __( 'Icon Image', 'mobile-menu' ),
                'id'      => 'right_menu_icon',
                'type'    => 'upload',
                'desc'    => __( 'Upload your right menu icon image', 'mobile-menu' ),
                'default' => 'menu',
            ) );
            // Right Menu Icon Top Margin.
            $right_menu_tab->createOption( array(
                'name'    => __( 'Icon Top Margin', 'mobile-menu' ),
                'id'      => 'right_icon_top_margin',
                'type'    => 'number',
                'desc'    => __( 'Enter the Right Icon Top Margin', 'mobile-menu' ),
                'default' => '0',
                'max'     => '450',
                'min'     => '0',
                'unit'    => 'px',
            ) );
            // Right Menu Icon Right Margin.
            $right_menu_tab->createOption( array(
                'name'    => __( 'Icon Right Margin', 'mobile-menu' ),
                'id'      => 'right_icon_right_margin',
                'type'    => 'number',
                'desc'    => __( 'Enter the Right Icon Right Margin', 'mobile-menu' ),
                'default' => '5',
                'max'     => '450',
                'min'     => '0',
                'unit'    => 'px',
            ) );
            // Background Heading.
            $right_menu_tab->createOption( array(
                'name' => __( 'Right Panel options', 'mobile-menu' ),
                'type' => 'heading',
            ) );
            // Right Menu Background Image.
            $right_menu_tab->createOption( array(
                'name' => __( 'Panel Background Image', 'mobile-menu' ),
                'id'   => 'right_menu_bg_image',
                'type' => 'upload',
                'desc' => __( 'upload your right menu background image(this will override the Background color option)', 'mobile-menu' ),
            ) );
            // Right Menu Background Image Opacity.
            $right_menu_tab->createOption( array(
                'name'    => __( 'Panel Background Image Opacity', 'mobile-menu' ),
                'id'      => 'right_menu_bg_opacity',
                'type'    => 'number',
                'desc'    => __( 'Enter the Right Background image opacity', 'mobile-menu' ),
                'default' => '100',
                'max'     => '100',
                'min'     => '10',
                'step'    => '10',
                'unit'    => '%',
            ) );
            // Left Menu Background Image Size.
            $right_menu_tab->createOption( array(
                'name'    => __( 'Panel Background Image Size', 'mobile-menu' ),
                'id'      => 'right_menu_bg_image_size',
                'type'    => 'select',
                'desc'    => __( 'Select the Background image size type. <a href="https://www.w3schools.com/cssref/css3_pr_background-size.asp" target="_blank">See the CSS Documentation</a>', 'mobile-menu' ),
                'options' => array(
                'auto'    => __( 'Auto', 'mobile-menu' ),
                'contain' => __( 'Contain', 'mobile-menu' ),
                'cover'   => __( 'Cover', 'mobile-menu' ),
                'inherit' => __( 'Inherit', 'mobile-menu' ),
                'initial' => __( 'Initial', 'mobile-menu' ),
                'unset'   => __( 'Unset', 'mobile-menu' ),
            ),
                'default' => 'cover',
            ) );
            // Right Menu Gradient css.
            $right_menu_tab->createOption( array(
                'name'    => __( 'Panel Background Gradient Css', 'mobile-menu' ),
                'id'      => 'right_menu_bg_gradient',
                'type'    => 'text',
                'desc'    => __( '<a href="https://webgradients.com/" target="_blank">Click here</a> to get your desired Gradient, just press the copy button and paste in this field.', 'mobile-menu' ),
                'default' => '',
            ) );
            // Right Menu Panel Width Units.
            $right_menu_tab->createOption( array(
                'name'     => __( 'Menu Panel Width Units', 'mobile-menu' ),
                'id'       => 'right_menu_width_units',
                'type'     => 'enable',
                'default'  => true,
                'desc'     => __( 'Choose the width units.', 'mobile-menu' ),
                'enabled'  => __( 'Pixels', 'mobile-menu' ),
                'disabled' => __( 'Percentage', 'mobile-menu' ),
            ) );
            // Right Menu Panel Width.
            $right_menu_tab->createOption( array(
                'name'    => __( 'Menu Panel Width(Pixels)', 'mobile-menu' ),
                'id'      => 'right_menu_width',
                'type'    => 'number',
                'desc'    => __( 'Enter the Right Menu Panel Width', 'mobile-menu' ),
                'default' => '270',
                'max'     => '450',
                'min'     => '50',
                'unit'    => 'px',
            ) );
            // Right Menu Panel Width.
            $right_menu_tab->createOption( array(
                'name'    => __( 'Menu Panel Width(Percentage)', 'mobile-menu' ),
                'id'      => 'right_menu_width_percentage',
                'type'    => 'number',
                'desc'    => __( 'Enter the Right Menu Panel Width', 'mobile-menu' ),
                'default' => '70',
                'max'     => '90',
                'min'     => '0',
                'unit'    => '%',
            ) );
            // Right Menu Content Padding.
            $right_menu_tab->createOption( array(
                'name'    => __( 'Right Menu Content Padding', 'mobile-menu' ),
                'id'      => 'right_menu_content_padding',
                'type'    => 'number',
                'desc'    => __( 'Enter the Right Menu Content Padding', 'mobile-menu' ),
                'default' => '10',
                'max'     => '30',
                'min'     => '0',
                'step'    => '1',
                'unit'    => '%',
            ) );
            // Right Menu Font.
            $fonts_tab->createOption( array(
                'name'                => __( 'Right Menu Font', 'mobile-menu' ),
                'id'                  => 'right_menu_font',
                'type'                => 'font',
                'desc'                => __( 'Select a style', 'mobile-menu' ),
                'show_font_weight'    => true,
                'show_font_style'     => true,
                'show_line_height'    => true,
                'show_letter_spacing' => true,
                'show_text_transform' => true,
                'show_font_size'      => true,
                'show_font_variant'   => false,
                'show_text_shadow'    => false,
                'show_color'          => false,
                'default'             => array(
                'line-height' => '1.5em',
                'font-family' => 'Dosis',
            ),
            ) );
            // Header Left Menu Section.
            $colors_tab->createOption( array(
                'name' => __( 'General', 'mobile-menu' ),
                'type' => 'heading',
            ) );
            // Overlay Background color.
            $colors_tab->createOption( array(
                'name'    => __( 'Overlay Background Color', 'mobile-menu' ),
                'id'      => 'overlay_bg_color',
                'type'    => 'color',
                'desc'    => '',
                'alpha'   => true,
                'default' => 'rgba(0, 0, 0, 0.83)',
            ) );
            // Menu Items Border color.
            $colors_tab->createOption( array(
                'name'    => __( 'Menu Items Border Color', 'mobile-menu' ),
                'id'      => 'menu_items_border_color',
                'type'    => 'color',
                'desc'    => '',
                'alpha'   => true,
                'default' => 'rgba(0, 0, 0, 0.83)',
            ) );
            // Header Left Menu Section.
            $colors_tab->createOption( array(
                'name' => __( 'Header', 'mobile-menu' ),
                'type' => 'heading',
            ) );
            // Header Background color.
            $colors_tab->createOption( array(
                'name'    => __( 'Header Background Color', 'mobile-menu' ),
                'id'      => 'header_bg_color',
                'type'    => 'color',
                'desc'    => '',
                'alpha'   => true,
                'default' => '#f7f7f7',
            ) );
            // Header Text color.
            $colors_tab->createOption( array(
                'name'    => __( 'Header Text Color', 'mobile-menu' ),
                'id'      => 'header_text_color',
                'type'    => 'color',
                'desc'    => '',
                'alpha'   => true,
                'default' => '#222',
            ) );
            // Header Left Menu Section.
            $colors_tab->createOption( array(
                'name' => __( 'Left Menu', 'mobile-menu' ),
                'type' => 'heading',
            ) );
            // Left Menu Icon color.
            $colors_tab->createOption( array(
                'name'    => __( 'Left Menu Icon Color', 'mobile-menu' ),
                'id'      => 'left_menu_icon_color',
                'type'    => 'color',
                'desc'    => '',
                'alpha'   => true,
                'default' => '#666',
            ) );
            // Header Text After Left Icon.
            $colors_tab->createOption( array(
                'name'    => __( 'Text After Left Icon', 'mobile-menu' ),
                'id'      => 'header_text_after_icon',
                'type'    => 'color',
                'desc'    => '',
                'alpha'   => true,
                'default' => '#222',
            ) );
            // Left Panel Background color.
            $colors_tab->createOption( array(
                'name'    => __( 'Background Color', 'mobile-menu' ),
                'id'      => 'left_panel_bg_color',
                'type'    => 'color',
                'desc'    => '',
                'alpha'   => true,
                'default' => '#F7F7F7',
            ) );
            // Left Panel Text color.
            $colors_tab->createOption( array(
                'name'    => __( 'Text Color', 'mobile-menu' ),
                'id'      => 'left_panel_text_color',
                'type'    => 'color',
                'desc'    => '',
                'alpha'   => true,
                'default' => '#666',
            ) );
            // Left Panel Background Hover Color.
            $colors_tab->createOption( array(
                'name'    => __( 'Background Hover Color', 'mobile-menu' ),
                'id'      => 'left_panel_hover_bgcolor',
                'type'    => 'color',
                'desc'    => '',
                'alpha'   => true,
                'default' => '#666',
            ) );
            // Left Panel Text color Hover.
            $colors_tab->createOption( array(
                'name'    => __( 'Hover Text Color', 'mobile-menu' ),
                'id'      => 'left_panel_hover_text_color',
                'type'    => 'color',
                'desc'    => '',
                'alpha'   => true,
                'default' => '#FFF',
            ) );
            // Left Panel Sub-menu Background Color.
            $colors_tab->createOption( array(
                'name'    => __( 'Submenu Background Color', 'mobile-menu' ),
                'id'      => 'left_panel_submenu_bgcolor',
                'type'    => 'color',
                'desc'    => '',
                'alpha'   => true,
                'default' => '#3a3a3a',
            ) );
            // Left Panel Sub-menu Text Color.
            $colors_tab->createOption( array(
                'name'    => __( 'Submenu Text Color', 'mobile-menu' ),
                'id'      => 'left_panel_submenu_text_color',
                'type'    => 'color',
                'desc'    => '',
                'alpha'   => true,
                'default' => '#fff',
            ) );
            // Left Panel Cancel Button Color.
            $colors_tab->createOption( array(
                'name'    => __( 'Cancel Button Color', 'mobile-menu' ),
                'id'      => 'left_panel_cancel_button_color',
                'type'    => 'color',
                'desc'    => '',
                'alpha'   => true,
                'default' => '#666',
            ) );
            // Header Right Menu Section.
            $colors_tab->createOption( array(
                'name' => __( 'Right Menu', 'mobile-menu' ),
                'type' => 'heading',
            ) );
            // Right Menu Icon color.
            $colors_tab->createOption( array(
                'name'    => __( 'Right Menu Icon Color', 'mobile-menu' ),
                'id'      => 'right_menu_icon_color',
                'type'    => 'color',
                'desc'    => '',
                'alpha'   => true,
                'default' => '#222',
            ) );
            // Header Text Before Right Icon.
            $colors_tab->createOption( array(
                'name'    => __( 'Text Before Right Icon', 'mobile-menu' ),
                'id'      => 'header_text_before_icon',
                'type'    => 'color',
                'desc'    => '',
                'alpha'   => true,
                'default' => '#222',
            ) );
            // Right Panel Background color.
            $colors_tab->createOption( array(
                'name'    => __( 'Background Color', 'mobile-menu' ),
                'id'      => 'right_panel_bg_color',
                'type'    => 'color',
                'desc'    => '',
                'alpha'   => true,
                'default' => '#F7F7F7',
            ) );
            // Right Panel Text color.
            $colors_tab->createOption( array(
                'name'    => __( 'Text Color', 'mobile-menu' ),
                'id'      => 'right_panel_text_color',
                'type'    => 'color',
                'desc'    => '',
                'alpha'   => true,
                'default' => '#666',
            ) );
            // Right Panel Background Hover Color.
            $colors_tab->createOption( array(
                'name'    => __( 'Background Hover Color', 'mobile-menu' ),
                'id'      => 'right_panel_hover_bgcolor',
                'type'    => 'color',
                'desc'    => '',
                'alpha'   => true,
                'default' => '#666',
            ) );
            // Right Panel Text color Hover.
            $colors_tab->createOption( array(
                'name'    => __( 'Hover Text Color', 'mobile-menu' ),
                'id'      => 'right_panel_hover_text_color',
                'type'    => 'color',
                'desc'    => '',
                'alpha'   => true,
                'default' => '#FFF',
            ) );
            // Right Panel Sub-menu Background Color.
            $colors_tab->createOption( array(
                'name'    => __( 'Submenu Background Color', 'mobile-menu' ),
                'id'      => 'right_panel_submenu_bgcolor',
                'type'    => 'color',
                'desc'    => '',
                'alpha'   => true,
                'default' => '#3a3a3a',
            ) );
            // Right Panel Sub-menu Text Color.
            $colors_tab->createOption( array(
                'name'    => __( 'Submenu Text Color', 'mobile-menu' ),
                'id'      => 'right_panel_submenu_text_color',
                'type'    => 'color',
                'desc'    => '',
                'alpha'   => true,
                'default' => '#fff',
            ) );
            // Right Panel Cancel Button Color.
            $colors_tab->createOption( array(
                'name'    => __( 'Cancel Button Color', 'mobile-menu' ),
                'id'      => 'right_panel_cancel_button_color',
                'type'    => 'color',
                'desc'    => '',
                'alpha'   => true,
                'default' => '#666',
            ) );
            // 3rd Level Right Menu Section.
            $colors_tab->createOption( array(
                'name' => __( '3rd Level Menu Colors', 'mobile-menu' ),
                'type' => 'heading',
            ) );
            // Left Panel 3rd Level Left Menu Items Text color.
            $colors_tab->createOption( array(
                'name'    => __( 'Left Menu Text Color', 'mobile-menu' ),
                'id'      => 'left_panel_3rd_menu_text_color',
                'type'    => 'color',
                'desc'    => '',
                'alpha'   => true,
                'default' => '#fff',
            ) );
            // Left Panel 3rd Level Left Menu Items Text color Hover.
            $colors_tab->createOption( array(
                'name'    => __( 'Left Menu Text Color Hover', 'mobile-menu' ),
                'id'      => 'left_panel_3rd_menu_text_color_hover',
                'type'    => 'color',
                'desc'    => '',
                'alpha'   => true,
                'default' => '#ccc',
            ) );
            // Left Panel 3rd Level Left Menu Items Background color.
            $colors_tab->createOption( array(
                'name'    => __( 'Left Menu Background Color', 'mobile-menu' ),
                'id'      => 'left_panel_3rd_menu_bg_color',
                'type'    => 'color',
                'desc'    => '',
                'alpha'   => true,
                'default' => '#222',
            ) );
            // Left Panel 3rd Level Left Menu Items Background color Hover.
            $colors_tab->createOption( array(
                'name'    => __( 'Left Menu Background Color Hover', 'mobile-menu' ),
                'id'      => 'left_panel_3rd_menu_bg_color_hover',
                'type'    => 'color',
                'desc'    => '',
                'alpha'   => true,
                'default' => '#666',
            ) );
            // Right Panel 3rd Level Right Menu Items Text color.
            $colors_tab->createOption( array(
                'name'    => __( 'Right Menu Text Color', 'mobile-menu' ),
                'id'      => 'right_panel_3rd_menu_text_color',
                'type'    => 'color',
                'desc'    => '',
                'alpha'   => true,
                'default' => '#fff',
            ) );
            // Right Panel 3rd Level Right Menu Items Text color Hover.
            $colors_tab->createOption( array(
                'name'    => __( 'Right Menu Text Color Hover', 'mobile-menu' ),
                'id'      => 'right_panel_3rd_menu_text_color_hover',
                'type'    => 'color',
                'desc'    => '',
                'alpha'   => true,
                'default' => '#ccc',
            ) );
            // Right Panel 3rd Level Right Menu Items Background color.
            $colors_tab->createOption( array(
                'name'    => __( 'Right Menu Background Color', 'mobile-menu' ),
                'id'      => 'right_panel_3rd_menu_bg_color',
                'type'    => 'color',
                'desc'    => '',
                'alpha'   => true,
                'default' => '#222',
            ) );
            // Right Panel 3rd Level Right Menu Items Background color Hover.
            $colors_tab->createOption( array(
                'name'    => __( 'Right Menu Background Color Hover', 'mobile-menu' ),
                'id'      => 'right_panel_3rd_menu_bg_color_hover',
                'type'    => 'color',
                'desc'    => '',
                'alpha'   => true,
                'default' => '#666',
            ) );
            $panel->createOption( array(
                'type' => 'save',
            ) );
        }
    
    }
    
    /**
     *
     * Create Woocommerce options upsell.
     *
     * @since 2.6
     *
     * @param type   $panel Panel Options.
     * @param Object $titan TitanFramework object that is being edited.
     */
    public function create_woocommerce_options_upsell( $panel, $titan )
    {
        global  $mm_fs ;
        // Create Woocommerce Options panel.
        $mm_woo_tab = $panel->createTab( array(
            'name' => __( 'Woocommerce', 'mobile-menu' ),
        ) );
        $custom_html = '<div class="mm-business-features-holder"><div class="mm-bussiness-features"><h3>' . __( 'WooCommerce Features (Business Version)', 'mobile-menu' ) . '</h3>';
        $custom_html .= '<p><span class="dashicons dashicons-yes"></span><strong>Menu Cart Icon</strong> - Product counter notification buble, upload the desired icon.</p>';
        $custom_html .= '<p><span class="dashicons dashicons-yes"></span><strong>Mobile Product Filter</strong> - Advanced product filter for mobile users.</p>';
        $custom_html .= '<p><span class="dashicons dashicons-yes"></span><strong>Show only products</strong> - In the Header Live Search.</p>';
        $custom_html .= '<p><span class="dashicons dashicons-yes"></span><strong>Cart total in footer</strong> - Cart total in all pages or only in WooCommerce pages.</p>';
        $custom_html .= '<p><span class="dashicons dashicons-yes"></span><strong>Sliding Cart</strong> - Easily see what is in the Cart.</p>';
        $custom_html .= '<p><span class="dashicons dashicons-yes"></span><strong>Account links in Sliding Cart</strong> - Easy access to the account area.</p>';
        $custom_html .= '<p><span class="dashicons dashicons-yes"></span><strong>Checkout and View Cart</strong> - Inside the sliding cart it will increase the conversion rate and avoid less abandoned carts.</p>';
        $custom_html .= '<p><a href="' . $mm_fs->get_upgrade_url() . '&cta=woo-settings#" class="button mm-button-business-upgrade">' . __( 'Upgrade to Business!', 'mobile-menu' ) . '</a></p>';
        $custom_html .= '<p>Not sure if it has the right features?  <a href="' . $mm_fs->get_trial_url() . '">' . esc_html( 'Start a Free trial', 'mobile-menu' ) . '</a></p>';
        $custom_html .= '</div>';
        $custom_html .= '<div class="mm-business-image"><a href="https://shopdemo.wpmobilemenu.com/?utm_source=wprepo-dash&utm_medium=user%20website&utm_campaign=upsell_link" target="_blank"><img src="' . plugins_url( 'demo-content/assets/shopdemo-mobile-menu.png', __FILE__ ) . '">';
        $custom_html .= '</a><p><a href="https://shopdemo.wpmobilemenu.com/?utm_source=wprepo-dash&utm_medium=user%20website&utm_campaign=upsell_link"> ' . esc_html( 'See Demo Site', 'mobile-menu' ) . '</a></div></div>';
        // Woocommerce Tab Upgrade Content.
        $mm_woo_tab->createOption( array(
            'name'   => '',
            'type'   => 'custom',
            'custom' => $custom_html,
        ) );
    }
    
    /**
     *
     * Create Footer options upsell.
     *
     * @since 2.6
     *
     * @param type   $panel Panel Options.
     * @param Object $titan TitanFramework object that is being edited.
     */
    public function create_footer_options_upsell( $panel, $titan )
    {
        global  $mm_fs ;
        // Create Footer Options panel.
        $footer_tab = $panel->createTab( array(
            'name' => __( 'Footer', 'mobile-menu' ),
        ) );
        $custom_html = '<div class="mm-business-features-holder"><div class="mm-bussiness-features"><h3>' . __( 'Footer Features', 'mobile-menu' ) . '</h3>';
        $custom_html .= '<p><span class="dashicons dashicons-yes"></span><strong>Fixed Footer Bar</strong></p>';
        $custom_html .= '<p><span class="dashicons dashicons-yes"></span><strong>Auto-hide on Scroll</strong></p>';
        $custom_html .= '<p><span class="dashicons dashicons-yes"></span><strong>Highlight current page</strong></p>';
        $custom_html .= '<p><span class="dashicons dashicons-yes"></span><strong>4 Different Styles</strong></p>';
        $custom_html .= '<p><a href="' . $mm_fs->get_upgrade_url() . '&cta=footer-settings#" class="button mm-button-business-upgrade">' . __( 'Upgrade now!', 'mobile-menu' ) . '</a></p>';
        $custom_html .= '<p>Not sure if it has the right features?  <a href="' . $mm_fs->get_trial_url() . '">' . esc_html( 'Start a Free trial', 'mobile-menu' ) . '</a></p>';
        $custom_html .= '</div>';
        $custom_html .= '<div class="mm-business-image"><a href="https://prodemo.wpmobilemenu.com/?utm_source=wprepo-dash&utm_medium=user%20website&utm_campaign=upsell_link" target="_blank"><img src="' . plugins_url( 'demo-content/assets/prodemo-mobile-menu.png', __FILE__ ) . '">';
        $custom_html .= '</a><p><a href="https://prodemo.wpmobilemenu.com/?utm_source=wprepo-dash&utm_medium=user%20website&utm_campaign=upsell_link"> ' . esc_html( 'See Demo Site', 'mobile-menu' ) . '</a></div></div>';
        // Footer Tab Upgrade Content.
        $footer_tab->createOption( array(
            'name'   => '',
            'type'   => 'custom',
            'custom' => $custom_html,
        ) );
    }

}