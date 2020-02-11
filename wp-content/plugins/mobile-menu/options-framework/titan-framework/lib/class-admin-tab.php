<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}

class TitanFrameworkAdminTab
{
    /**
     * Default settings specific for this container
     *
     * @var array
     */
    private  $defaultSettings = array(
        'name' => '',
        'id'   => '',
        'desc' => '',
    ) ;
    public  $options = array() ;
    public  $settings ;
    public  $owner ;
    function __construct( $settings, $owner )
    {
        $this->owner = $owner;
        $this->settings = array_merge( $this->defaultSettings, $settings );
        if ( empty($this->settings['id']) ) {
            $this->settings['id'] = str_replace( ' ', '-', trim( strtolower( $this->settings['name'] ) ) );
        }
    }
    
    public function isActiveTab()
    {
        return $this->settings['id'] == $this->owner->getActiveTab()->settings['id'];
    }
    
    public function createOption( $settings )
    {
        if ( !apply_filters( 'tf_create_option_continue_' . $this->owner->owner->optionNamespace, true, $settings ) ) {
            return null;
        }
        $obj = TitanFrameworkOption::factory( $settings, $this );
        $this->options[] = $obj;
        do_action( 'tf_create_option_' . $this->owner->owner->optionNamespace, $obj );
        return $obj;
    }
    
    public function displayTab()
    {
        $url = add_query_arg( array(
            'page' => $this->owner->settings['id'],
            'tab'  => $this->settings['id'],
        ), remove_query_arg( array( 'message', 'mobmenu-action' ) ) );
        $tab_submenus = $this->displayTabSubmenus();
        ?>
		<a href="<?php 
        echo  esc_url( $url ) ;
        ?>" class="nav-tab <?php 
        echo  ( $this->isActiveTab() ? 'nav-tab-active' : '' ) ;
        ?>"><?php 
        echo  $this->settings['name'] ;
        echo  $tab_submenus ;
        ?></a>
		<?php 
    }
    
    public function displayTabSubmenus()
    {
        global  $mm_fs ;
        $output = '';
        $li_elements = '';
        $general_options_arr = array(
            array(
            'name' => __( 'Hide Theme Menu', 'mobile-menu' ),
            'url'  => 'hide-original-theme-menu',
        ),
            array(
            'name' => __( 'Miscelaneous Options', 'mobile-menu' ),
            'url'  => 'miscelaneous-options',
        ),
            array(
            'name' => __( 'Advanced Options', 'mobile-menu' ),
            'url'  => 'advanced-options',
        ),
            array(
            'name' => __( 'Import and Export', 'mobile-menu' ),
            'url'  => 'import-and-export',
        )
        );
        $header_arr = array( array(
            'name' => __( 'Header Options', 'mobile-menu' ),
            'url'  => 'header-options',
        ), array(
            'name' => __( 'Logo', 'mobile-menu' ),
            'url'  => 'logo-options',
        ) );
        $woocommerce_arr = [];
        $right_menu_arr = array( array(
            'name' => __( 'Menu Icon', 'mobile-menu' ),
            'url'  => 'menu-icon',
        ), array(
            'name' => __( 'Right Panel', 'mobile-menu' ),
            'url'  => 'right-panel-options',
        ) );
        $left_menu_arr = array( array(
            'name' => __( 'Menu Icon', 'mobile-menu' ),
            'url'  => 'menu-icon',
        ), array(
            'name' => __( 'Left Panel', 'mobile-menu' ),
            'url'  => 'left-panel-options',
        ) );
        $colors_arr = array(
            array(
            'name' => __( 'General', 'mobile-menu' ),
            'url'  => 'general',
        ),
            array(
            'name' => __( 'Header', 'mobile-menu' ),
            'url'  => 'header',
        ),
            array(
            'name' => __( 'Left Menu', 'mobile-menu' ),
            'url'  => 'left-menu',
        ),
            array(
            'name' => __( 'Right Menu', 'mobile-menu' ),
            'url'  => 'right-menu',
        ),
            array(
            'name' => __( '3rd Level Menu', 'mobile-menu' ),
            'url'  => '3rd-level-menu-colors',
        )
        );
        // Define the settings submenu.
        $submenu_options = array(
            'general-options' => $general_options_arr,
            'header'          => $header_arr,
            'left-menu'       => $left_menu_arr,
            'right-menu'      => $right_menu_arr,
            'woocommerce'     => $woocommerce_arr,
            'colors'          => $colors_arr,
        );
        // Create the submenu.
        
        if ( isset( $submenu_options[$this->settings['id']] ) ) {
            foreach ( $submenu_options[$this->settings['id']] as $items ) {
                $li_elements .= '<li data-link-id="' . $items['url'] . '">' . $items['name'] . '</li>';
            }
            $output = '<ul>' . $li_elements . '</ul>';
        }
        
        return $output;
    }
    
    public function displayOptions()
    {
        foreach ( $this->options as $option ) {
            $option->display();
        }
    }

}