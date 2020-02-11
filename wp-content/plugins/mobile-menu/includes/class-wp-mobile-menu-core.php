<?php

if ( !class_exists( 'WP_Mobile_Menu' ) ) {
    die;
}
/**
 * WP Mobile Menu Core Functions.
 * This will implement the core functionalities.
 *
 * @since 2.0.0
 */
class WP_Mobile_Menu_Core
{
    /**
     * @var object
     */
    public  $titan ;
    /**
     * @var String
     */
    public  $menu_display_type ;
    /**
     * Add Body Class
     *
     * @since 2.0
     */
    public function mobmenu_add_body_class( $classes )
    {
        $titan = TitanFramework::getInstance( 'mobmenu' );
        $display_type = $titan->getOption( 'menu_display_type' );
        $lpanel_elements = $titan->getOption( 'left_menu_content_position' );
        $rpanel_elements = $titan->getOption( 'right_menu_content_position' );
        // If the User profile is being used at the Top of the left panel.
        if ( 'user-profile' === $lpanel_elements[0] ) {
            $classes[] = 'left-mobmenu-user-profile';
        }
        // If the User profile is being used at the Top of the right panel.
        if ( 'user-profile' === $rpanel_elements[0] ) {
            $classes[] = 'right-mobmenu-user-profile';
        }
        if ( '' === $display_type || !$display_type ) {
            $display_type = 'slideout-over';
        }
        if ( true === $display_type || '1' === $display_type ) {
            $display_type = 'slideout-push';
        }
        // Add the class of the animation display type.
        switch ( $display_type ) {
            case 'slideout-push':
                $menu_display_type = 'mob-menu-slideout';
                break;
            case 'slideout-over':
                $menu_display_type = 'mob-menu-slideout-over';
                break;
            case 'slideout-top':
                $menu_display_type = 'mob-menu-slideout-top';
                break;
            case 'overlay':
                $menu_display_type = 'mob-menu-overlay';
                break;
        }
        $this->menu_display_type = $menu_display_type;
        $classes[] = $menu_display_type;
        // Check if the Auto-hide Header option is on so it can be added a new class.
        if ( $titan->getOption( 'autohide_header' ) ) {
            array_push( $classes, 'mob-menu-autohide-header' );
        }
        // Check if the Auto-hide Footer option is on so it can be added a new class.
        if ( $titan->getOption( 'autohide_footer' ) ) {
            array_push( $classes, 'mob-menu-autohide-footer' );
        }
        // Check if the Sliding Menus option is on so it can be added a new class.
        if ( $titan->getOption( 'sliding_submenus' ) ) {
            array_push( $classes, 'mob-menu-sliding-menus' );
        }
        return $classes;
    }
    
    /**
     * Frontend Scripts.
     */
    public function frontend_enqueue_scripts()
    {
        global  $mm_fs ;
        // Enqueue the common free scripts.
        $this->frontend_free_enqueue_scripts();
    }
    
    /**
     * Frontend Free version Scripts.
     */
    public function frontend_free_enqueue_scripts()
    {
        // Load the Free assets.
        wp_register_script(
            'mobmenujs',
            plugins_url( 'js/mobmenu.js', __FILE__ ),
            array( 'jquery' ),
            WP_MOBILE_MENU_VERSION
        );
        wp_enqueue_script( 'mobmenujs' );
        wp_enqueue_style(
            'cssmobmenu-icons',
            plugins_url( 'css/mobmenu-icons.css', __FILE__ ),
            '',
            WP_MOBILE_MENU_VERSION
        );
        wp_enqueue_style(
            'cssmobmenu',
            plugins_url( 'css/mobmenu.css', __FILE__ ),
            '',
            WP_MOBILE_MENU_VERSION
        );
    }
    
    /**
     * Dismiss the WP Mobile Menu Pro Banner.
     */
    public function dismiss_wp_mobile_notice()
    {
        if ( check_ajax_referer( 'wp-mobile-menu-security-nonce', 'security' ) ) {
            update_option( 'wp_mobile_menu_banner_dismissed', 'yes' );
        }
        wp_die();
    }
    
    /**
     * Build the icons HTML.
     */
    public function get_icons_html()
    {
        $menu_title = '';
        if ( isset( $_POST['menu_item_id'] ) ) {
            $menu_item_id = absint( $_POST['menu_item_id'] );
        }
        if ( isset( $_POST['menu_id'] ) ) {
            $menu_id = absint( $_POST['menu_id'] );
        }
        if ( isset( $_POST['menu_title'] ) ) {
            $menu_title = sanitize_text_field( $_POST['menu_title'] );
        }
        if ( isset( $_POST['full_content'] ) ) {
            $full_content = sanitize_text_field( $_POST['full_content'] );
        }
        $seleted_icon = sanitize_text_field( get_post_meta( $menu_item_id, '_mobmenu_icon', true ) );
        
        if ( !empty($seleted_icon) ) {
            $selected = ' data-selected-icon="' . $seleted_icon . '" ';
        } else {
            $selected = '';
        }
        
        $icons = $this->get_icons_list();
        
        if ( 'yes' === $full_content ) {
            $output = '<div class="mobmenu-icons-overlay"></div><div class="mobmenu-icons-content" data-menu-id="' . $menu_id . '" data-menu-item-id="' . $menu_item_id . '">';
            $output .= '<div id="mobmenu-modal-header"><h2>' . $menu_title . ' - Menu Item Icon</h2><div class="mobmenu-icons-close-overlay"><span class="mobmenu-item mobmenu-close-overlay mob-icon-cancel-7"></span></div>';
            $output .= '<div class="mobmenu-icons-search"><input type="text" name="mobmenu_search_icons" id="mobmenu_search_icons" value="" placeholder="Search"><span class="mobmenu-item mob-icon-search-7"></span></div>';
            $output .= '<div class="mobmenu-icons-remove-selected">' . __( 'Remove Icon Selection', 'mobile-menu' ) . '</div>';
            $output .= '</div><div id="mobmenu-modal-body"><div class="mobmenu-icons-holder" ' . $selected . '>';
            // Loop through all the icons to create the icons list.
            foreach ( $icons as $icon ) {
                $output .= '<span class="mobmenu-item mob-icon-' . $icon . '" data-icon-key="' . $icon . '"></span>';
            }
            $output .= '</div></div>';
        } else {
            $output = '<div class="mobmenu-icons-holder" ' . $selected . ' data-title="' . esc_attr( $menu_title ) . '" - Menu Item Icon" >';
        }
        
        echo  $output ;
        wp_die();
    }
    
    /**
     * Build the WP Mobile Menu Html Markup.
     */
    public function load_menu_html_markup()
    {
        global  $mm_fs ;
        global  $woocommerce ;
        $left_logged_in_user = false;
        $right_logged_in_user = false;
        $titan = TitanFramework::getInstance( 'mobmenu' );
        $mobmenu_depth = 3;
        $mobmenu_parent_link = '';
        $header_search = '';
        $header_cart = '';
        $header_shop_filter = '';
        $left_menu_content = '';
        $right_menu_content = '';
        $shop_filter = '';
        $header_ajax_search = '';
        $close_icon = $titan->getOption( 'close_icon_font' );
        $submenu_open_icon_font = $titan->getOption( 'submenu_open_icon_font' );
        $submenu_close_icon_font = $titan->getOption( 'submenu_close_icon_font' );
        $mm_open_cart_menu = '';
        $logo_content = '';
        $menu_display_type = 'mob-menu-slideout';
        $output = '';
        $output .= '<div class="mobmenu-overlay"></div>';
        $header_text = $titan->getOption( 'header_text' );
        if ( '' === $header_text ) {
            $header_text = get_bloginfo();
        }
        $sticky_el_data_detach = '';
        $autoclose_menus_el_data = '';
        // Check for Sticky elements and add them to the HTML.
        if ( $titan->getOption( 'sticky_elements' ) ) {
            $sticky_el_data_detach = 'data-detach-el="' . $titan->getOption( 'sticky_elements' ) . '"';
        }
        // Check for Autoclose submenu option.
        if ( $titan->getOption( 'autoclose_submenus' ) ) {
            $autoclose_menus_el_data = ' data-autoclose-submenus="' . $titan->getOption( 'autoclose_submenus' ) . '"';
        }
        if ( $titan->getOption( 'enable_mm_woo_open_cart_menu' ) ) {
            $mm_open_cart_menu = ' data-open-cart="true"';
        }
        $menu_display_class = ' data-menu-display="' . $this->menu_display_type . '"';
        $output .= '<div class="mob-menu-header-holder mobmenu" ' . $menu_display_class . $sticky_el_data_detach . $autoclose_menus_el_data . $mm_open_cart_menu . ' data-open-icon="' . $submenu_open_icon_font . '" data-close-icon="' . $submenu_close_icon_font . '">';
        // Left Menu Content.
        
        if ( $titan->getOption( 'enable_left_menu' ) && !$left_logged_in_user ) {
            $left_menu_text = '';
            if ( '' !== $titan->getOption( 'left_menu_text' ) ) {
                $left_menu_text .= '<span class="left-menu-icon-text">' . __( $titan->getOption( 'left_menu_text' ), 'mobile-menu' ) . '</span>';
            }
            
            if ( $titan->getOption( 'left_menu_icon_action' ) ) {
                $left_menu_content .= '<a href="#" class="mobmenu-left-bt mobmenu-trigger-action" data-panel-target="mobmenu-left-panel" aria-label="' . __( 'Left Menu Button', 'mobile-menu' ) . '">';
            } else {
                
                if ( $titan->getOption( 'left_icon_url_target' ) ) {
                    $left_icon_url_target = '_self';
                } else {
                    $left_icon_url_target = '_blank';
                }
                
                $left_menu_content .= '<a href="' . $titan->getOption( 'left_icon_url' ) . '" target="' . $left_icon_url_target . '" id="mobmenu-center">';
            }
            
            $left_icon_image = wp_get_attachment_image_src( $titan->getOption( 'left_menu_icon' ) );
            $left_icon_image = $left_icon_image[0];
            $left_menu_icon = $titan->getOption( 'left_menu_icon_new' );
            switch ( $left_menu_icon ) {
                case 'image':
                    $left_menu_content .= '<img src="' . $left_icon_image . '" alt="' . __( 'Left Menu Icon', 'mobile-menu' ) . '">';
                    break;
                case 'icon':
                    $left_menu_content .= '<i class="mob-icon-' . $titan->getOption( 'left_menu_icon_font' ) . ' mob-menu-icon"></i>' . $this->mobmenu_close_button( $close_icon );
                    break;
                case 'animated-icon':
                    $hamburger_animation = $titan->getOption( 'left_menu_icon_animation' );
                    $left_menu_content .= '<button class="hamburger hamburger ' . $hamburger_animation . '" type="button" aria-label="Menu" aria-controls="navigation"><span class="hamburger-box"><span class="hamburger-inner"></span></span></button>';
                    break;
            }
            $left_menu_content .= $left_menu_text;
            $left_menu_content .= '</a>';
            $left_menu_content = apply_filters( 'mm_left_menu_filter', $left_menu_content );
        }
        
        if ( !$titan->getOption( 'disabled_logo_text' ) ) {
            // Format the Header Branding.
            $logo_content = $this->format_header_branding( $titan, $header_text );
        }
        // Right Menu Content.
        
        if ( $titan->getOption( 'enable_right_menu' ) && !$right_logged_in_user ) {
            $right_menu_text = '';
            $right_menu_content = apply_filters( 'mm_before_right_menu_filter', $right_menu_content );
            if ( '' !== $titan->getOption( 'right_menu_text' ) ) {
                $right_menu_text .= '<span class="right-menu-icon-text">' . __( $titan->getOption( 'right_menu_text' ), 'mobile-menu' ) . '</span>';
            }
            
            if ( $titan->getOption( 'right_menu_icon_action' ) ) {
                $right_menu_content .= '<a href="#" class="mobmenu-right-bt  mobmenu-trigger-action" data-panel-target="mobmenu-right-panel" aria-label="' . __( 'Right Menu Button', 'mobile-menu' ) . '">';
            } else {
                
                if ( $titan->getOption( 'right_icon_url_target' ) ) {
                    $right_icon_url_target = '_self';
                } else {
                    $right_icon_url_target = '_blank';
                }
                
                $right_menu_content .= '<a href="' . $titan->getOption( 'right_icon_url' ) . '" target="' . $right_icon_url_target . '">';
            }
            
            $right_icon_image = wp_get_attachment_image_src( $titan->getOption( 'right_menu_icon' ) );
            $right_icon_image = $right_icon_image[0];
            $right_menu_icon = $titan->getOption( 'right_menu_icon_new' );
            switch ( $right_menu_icon ) {
                case 'image':
                    $right_menu_content .= '<img src="' . $right_icon_image . '" alt="' . __( 'Right Menu Icon', 'mobile-menu' ) . '">';
                    break;
                case 'icon':
                    $right_menu_content .= '<i class="mob-icon-' . $titan->getOption( 'right_menu_icon_font' ) . ' mob-menu-icon"></i>' . $this->mobmenu_close_button( $close_icon );
                    break;
                case 'animated-icon':
                    $hamburger_animation = $titan->getOption( 'right_menu_icon_animation' );
                    $right_menu_content .= '<button class="hamburger hamburger ' . $hamburger_animation . '" type="button" aria-label="Menu" aria-controls="navigation"><span class="hamburger-box"><span class="hamburger-inner"></span></span></button>';
                    break;
            }
            $right_menu_content .= $right_menu_text;
            $right_menu_content .= '</a>';
            $right_menu_content = apply_filters( 'mm_right_menu_filter', $right_menu_content );
        }
        
        $header_elements_order = array( 'left-menu', 'logo', 'right-menu' );
        $language_selector = '';
        $header_output = '<div  class="mobmenul-container">';
        if ( !empty($header_elements_order) ) {
            foreach ( $header_elements_order as $element ) {
                switch ( $element ) {
                    case 'left-menu':
                        $header_output .= $left_menu_content;
                        break;
                    case 'right-menu':
                        $header_output .= $right_menu_content;
                        break;
                    case 'cart':
                        $header_output .= $header_cart;
                        break;
                    case 'shop-filter':
                        $header_output .= $header_shop_filter;
                        break;
                    case 'logo':
                        $header_output .= '</div>' . $logo_content . '<div class="mobmenur-container">';
                        break;
                    case 'search':
                        $header_output .= $header_search;
                        break;
                    case 'language-selector':
                        $header_output .= $language_selector;
                        break;
                }
            }
        }
        $header_output .= '</div>';
        $output .= $header_output;
        $output .= '</div>';
        // Echo the Header HTML.
        echo  $output ;
        // Build the left menu panel.
        
        if ( $titan->getOption( 'enable_left_menu' ) && !$left_logged_in_user ) {
            if ( $titan->getOption( 'left_menu_parent_link_submenu' ) ) {
                $mobmenu_parent_link = 'mobmenu-parent-link';
            }
            ?>

				<div class="mobmenu-left-alignment mobmenu-panel mobmenu-left-panel <?php 
            echo  $mobmenu_parent_link ;
            ?> ">
				<a href="#" class="mobmenu-left-bt" aria-label="<?php 
            _e( 'Left Menu Button', 'mobile-menu' );
            ?>"><?php 
            echo  $this->mobmenu_close_button( $close_icon ) ;
            ?></a>

				<div class="mobmenu-content">
				<?php 
            
            if ( is_active_sidebar( 'mobmlefttop' ) ) {
                ?>
					<ul class="leftmtop">
						<?php 
                dynamic_sidebar( 'Left Menu Top' );
                ?>
					</ul>
				<?php 
            }
            
            $left_panel_elements_order = array( 'left-menu' );
            $left_menu_panel_content = '';
            if ( !empty($left_panel_elements_order) ) {
                foreach ( $left_panel_elements_order as $element ) {
                    switch ( $element ) {
                        case 'left-menu':
                            $left_menu_panel_content .= $this->display_menu( 'left', $mobmenu_depth );
                            break;
                        case 'user-profile':
                            $left_menu_panel_content .= $this->get_user_profile_asset__premium_only();
                            break;
                        case 'search':
                            $left_menu_panel_content .= $header_cart;
                            break;
                        case 'logo':
                            $left_menu_panel_content .= $logo_content;
                            break;
                    }
                }
            }
            echo  $left_menu_panel_content ;
            // Check if the Left Menu Bottom Widget has any content.
            
            if ( is_active_sidebar( 'mobmleftbottom' ) ) {
                ?>
						<ul class="leftmbottom">
							<?php 
                dynamic_sidebar( 'Left Menu Bottom' );
                ?>
						</ul>
				<?php 
            }
            
            ?>

				</div><div class="mob-menu-left-bg-holder"></div></div>

			<?php 
        }
        
        
        if ( $titan->getOption( 'enable_right_menu' ) && !$right_logged_in_user ) {
            $mobmenu_parent_link = '';
            if ( $titan->getOption( 'right_menu_parent_link_submenu' ) ) {
                $mobmenu_parent_link = 'mobmenu-parent-link';
            }
            ?>
				<div class="mobmenu-right-alignment mobmenu-panel mobmenu-right-panel <?php 
            echo  $mobmenu_parent_link ;
            ?> ">
				<a href="#" class="mobmenu-right-bt" aria-label="<?php 
            _e( 'Right Menu Button', 'mobile-menu' );
            ?>"><?php 
            echo  $this->mobmenu_close_button( $close_icon ) ;
            ?></a>
					<?php 
            ?>
					<div class="mobmenu-content">
			<?php 
            // Check if the Right Menu Top Widget has any content.
            
            if ( is_active_sidebar( 'mobmrighttop' ) ) {
                ?>
				<ul class="rightmtop">
					<?php 
                dynamic_sidebar( 'Right Menu Top' );
                ?>
				</ul>
			<?php 
            }
            
            $right_panel_elements_order = array( 'right-menu' );
            $right_menu_panel_content = '';
            if ( !empty($right_panel_elements_order) ) {
                foreach ( $right_panel_elements_order as $element ) {
                    switch ( $element ) {
                        case 'right-menu':
                            $right_menu_panel_content .= $this->display_menu( 'right', $mobmenu_depth );
                            break;
                        case 'user-profile':
                            $right_menu_panel_content .= $this->get_user_profile_asset__premium_only();
                            break;
                        case 'search':
                            $right_menu_panel_content .= $header_cart;
                            break;
                        case 'logo':
                            $right_menu_panel_content .= '</div>' . $logo_content;
                            break;
                    }
                }
            }
            echo  $right_menu_panel_content ;
            // Check if the Right Menu Bottom Widget has any content.
            
            if ( is_active_sidebar( 'mobmrightbottom' ) ) {
                ?>
				<ul class="rightmbottom">
					<?php 
                dynamic_sidebar( 'Right Menu Bottom' );
                ?>
				</ul>
			<?php 
            }
            
            ?>

			</div><div class="mob-menu-right-bg-holder"></div></div>

		<?php 
        }
    
    }
    
    /**
     * Display Left Menu.
     */
    public function display_menu( $menu, $mobmenu_depth )
    {
        global  $mm_fs ;
        $titan = TitanFramework::getInstance( 'mobmenu' );
        $current_menu = $titan->getOption( $menu . '_menu' );
        if ( !is_nav_menu( $current_menu ) ) {
            $current_menu = '';
        }
        
        if ( has_nav_menu( $menu . '-wp-mobile-menu' ) ) {
            $current_menu = $menu . '-wp-mobile-menu';
            $menu_param = 'theme_location';
        } else {
            $menu_param = 'menu';
        }
        
        $output = '';
        // Only build the menu it there is a menu assigned to it.
        
        if ( '' !== $current_menu ) {
            // Display the menu.
            $output = wp_nav_menu( array(
                $menu_param   => $current_menu,
                'items_wrap'  => '<ul id="mobmenu' . $menu . '">%3$s</ul>',
                'fallback_cb' => false,
                'depth'       => $mobmenu_depth,
                'walker'      => new WP_Mobile_Menu_Walker_Nav_Menu( $menu, '' ),
                'echo'        => false,
            ) );
        } else {
            
            if ( current_user_can( 'administrator' ) ) {
                ?>
				<h4 class='no-mobile-menu'><a href='<?php 
                echo  get_site_url() ;
                ?>/wp-admin/nav-menus.php?action=locations'><?php 
                _e( 'Assign a menu to the ' . $menu . ' Mobile Menu location in the Appearance-> Menus-> Manage Locations.' );
                ?></a></h4>
			<?php 
            }
        
        }
        
        return $output;
    }
    
    /**
     * Register the Mobile Menus.
     */
    public function register_menus()
    {
        global  $mm_fs ;
        $menu_locations = array(
            'left-wp-mobile-menu'  => __( 'Left Mobile Menu', 'mobile-menu' ),
            'right-wp-mobile-menu' => __( 'Right Mobile Menu', 'mobile-menu' ),
        );
        register_nav_menus( $menu_locations );
    }
    
    /**
     *
     * Format Header Branding (Logo + Text).
     *
     * @since 2.6
     * @var $titan
     * @var $header_text
     */
    public function format_header_branding( $titan, $header_text )
    {
        global  $mm_fs ;
        $logo_img = wp_get_attachment_image_src( $titan->getOption( 'logo_img' ), 'full' );
        $logo_img = $logo_img[0];
        $logo_output = '';
        $logo_url = '';
        $logo_url_end = '';
        $logo_alt = get_post_meta( intval( $titan->getOption( 'logo_img' ) ), '_wp_attachment_image_alt', true );
        // Retina Logo.
        
        if ( $titan->getOption( 'logo_img_retina' ) ) {
            $logo_img_retina = wp_get_attachment_image_src( $titan->getOption( 'logo_img_retina' ), 'full' );
            $logo_img_retina = $logo_img_retina[0];
            $logo_img_retina_metadata = wp_get_attachment_metadata( $titan->getOption( 'logo_img_retina' ) );
        }
        
        
        if ( $titan->getOption( 'disabled_logo_url' ) ) {
            $logo_url = '<h3 class="headertext">';
            $logo_url_end = '</h3>';
        } else {
            
            if ( '' === $titan->getOption( 'logo_url' ) ) {
                
                if ( function_exists( 'pll_home_url' ) ) {
                    $logo_url = pll_home_url();
                } else {
                    $logo_url = get_bloginfo( 'url' );
                }
            
            } else {
                $logo_url = $titan->getOption( 'logo_url' );
            }
            
            $logo_url_end = '</a>';
            $logo_url = '<a href="' . $logo_url . '" class="headertext">';
        }
        
        $output = '<div class="mob-menu-logo-holder">' . $logo_url;
        $header_branding = $titan->getOption( 'header_branding' );
        // Assign the image alt valude with the blog title in case it's not provided on the image. It the blog title also doesn't exist default it to Organization Logo.
        if ( '' === $logo_alt ) {
            
            if ( '' === get_bloginfo( 'name' ) ) {
                $logo_alt = __( 'Organization Logo', 'mobile-menu' );
            } else {
                $logo_alt = get_bloginfo( 'name' );
            }
        
        }
        
        if ( ('logo' === $header_branding || 'logo-text' === $header_branding || 'text-logo' === $header_branding) && '' !== $logo_img ) {
            $logo_output .= '<img class="mob-standard-logo" src="' . $logo_img . '"  alt="' . $logo_alt . '">';
            // If there is a retina logo.
            if ( isset( $logo_img_retina ) ) {
                $logo_output .= '<img class="mob-retina-logo" src="' . $logo_img_retina . '"  alt=" ' . __( 'Logo Header Menu', 'mobile-menu' ) . '">';
            }
        }
        
        $header_text = '<span>' . $header_text . '</span>';
        if ( $header_branding ) {
            switch ( $header_branding ) {
                case 'logo':
                    $output .= $logo_output;
                    break;
                case 'text':
                    $output .= $header_text;
                    break;
                case 'logo-text':
                    $output .= $logo_output;
                    $output .= $header_text;
                    break;
                case 'text-logo':
                    $output .= $header_text;
                    $output .= $logo_output;
                    break;
            }
        }
        $output .= $logo_url_end . '</div>';
        return $output;
    }
    
    /**
     *
     * Shop Filter Icon Content.
     *
     * @since 2.7
     */
    public function mobmenu_close_button( $icon )
    {
        return '<i class="mob-icon-' . $icon . ' mob-cancel-button"></i>';
    }
    
    /**
     *
     * Save Menu Item Icon.
     *
     * @since 2.0
     */
    public function save_menu_item_icon()
    {
        
        if ( isset( $_POST['menu_item_id'] ) ) {
            $menu_item_id = absint( esc_attr( $_POST['menu_item_id'] ) );
            $menu_item_icon = esc_attr( $_POST['menu_item_icon'] );
            if ( $menu_item_id > 0 ) {
                update_post_meta( $menu_item_id, '_mobmenu_icon', $menu_item_icon );
            }
            wp_send_json_success();
        }
    
    }
    
    /*
     * Register Sidebar Menu Widgets.
     *
     */
    public function register_sidebar()
    {
        // Register the Mobile Menu Left Menu Top Widget.
        $args = array(
            'name'          => __( 'Left Menu Top', 'mobile-menu' ),
            'id'            => 'mobmlefttop',
            'description'   => '',
            'before_widget' => '<li id="%1$s" class="widget %2$s">',
            'after_widget'  => '</li>',
            'before_title'  => '<h2 class="widgettitle">',
            'after_title'   => '</h2>',
        );
        register_sidebar( $args );
        // Register the Mobile Menu Left Menu Bottom Widget.
        $args = array(
            'name'          => __( 'Left Menu Bottom', 'mobile-menu' ),
            'id'            => 'mobmleftbottom',
            'description'   => '',
            'before_widget' => '<li id="%1$s" class="widget %2$s">',
            'after_widget'  => '</li>',
            'before_title'  => '<h2 class="widgettitle">',
            'after_title'   => '</h2>',
        );
        register_sidebar( $args );
        // Register the Mobile Menu Right Menu Top Widget.
        $args = array(
            'name'          => __( 'Right Menu Top', 'mobile-menu' ),
            'id'            => 'mobmrighttop',
            'description'   => '',
            'before_widget' => '',
            'after_widget'  => '',
            'before_title'  => '<h2 class="widgettitle">',
            'after_title'   => '</h2>',
        );
        register_sidebar( $args );
        // Register the Mobile Menu Right Menu Bottom Widget.
        $args = array(
            'name'          => __( 'Right Menu Bottom', 'mobile-menu' ),
            'id'            => 'mobmrightbottom',
            'description'   => '',
            'before_widget' => '<li id="%1$s" class="widget %2$s">',
            'after_widget'  => '</li>',
            'before_title'  => '<h2 class="widgettitle">',
            'after_title'   => '</h2>',
        );
        register_sidebar( $args );
        // Register the Mobile Menu Shop Filter Widget.
        $args = array(
            'name'          => __( 'Mobile Filter Shop', 'mobile-menu' ),
            'id'            => 'mobmenu-filter-shop',
            'description'   => '',
            'before_widget' => '<li id="%1$s" class="widget %2$s">',
            'after_widget'  => '</li>',
            'before_title'  => '<h2 class="widgettitle">',
            'after_title'   => '</h2>',
        );
        register_sidebar( $args );
    }
    
    /** 
     * Get the Icon Font list.
     */
    public function get_icons_list()
    {
        global  $mm_fs ;
        $icons_base = array(
            'menu',
            'menu-2',
            'menu-3',
            'menu-1',
            'menu-outline',
            'user',
            'user-1',
            'star',
            'star-1',
            'star-empty',
            'ok',
            'ok-1',
            'cancel',
            'cancel-2',
            'cancel-circled',
            'cancel-circled2',
            'cancel-circle',
            'cancel-1',
            'vimeo',
            'twitter',
            'facebook-squared',
            'gplus',
            'pinterest',
            'tumblr',
            'linkedin',
            'instagram',
            'plus',
            'plus-outline',
            'plus-1',
            'minus',
            'minus-1',
            'plus-2',
            'minus-2',
            'down-open',
            'up-open-big',
            'down-dir',
            'left-dir',
            'right-dir',
            'up-dir',
            'left-open',
            'right-open',
            'up-open-2',
            'down-open-2'
        );
        return $icons_base;
    }
    
    /**
     *
     * Mobile Menu Export Settings.
     *
     * @since 2.7
     */
    public function mobile_menu_export_settings()
    {
        ob_clean();
        $date = date( 'Y-m-d' );
        $filename = 'mobile-menu-settings-' . $date . '.txt';
        $output = get_option( 'mobmenu_options' );
        header( 'Content-Description: File Transfer' );
        header( 'Content-Disposition: attachment; filename=' . $filename );
        header( 'Content-Type: text/html; charset=utf-8' );
        echo  $output ;
        die;
    }
    
    /**
     *
     * Mobile Menu Import Settings.
     *
     * @since 2.7
     */
    public function mobile_menu_import_settings( $settings_id )
    {
        global  $mm_fs ;
        $message = '';
        $message_code = '';
        $titan = TitanFramework::getInstance( 'mobmenu' );
        $left_menu = $titan->getOption( 'left_menu' );
        // If we are importing an oficial demo.
        
        if ( isset( $_REQUEST['demo'] ) ) {
            $file = $_REQUEST['demo'] . '.txt';
            $file_content = file_get_contents( WP_MOBILE_MENU_PLUGIN_PATH . 'includes/demo-content/' . $file );
        } else {
            // If we are importing a settings file from the user.
            
            if ( isset( $_FILES['upload_mobmenu_settings'] ) && '' !== $_FILES['upload_mobmenu_settings']['tmp_name'] ) {
                $file = $_FILES['upload_mobmenu_settings'];
                $file_content = file_get_contents( $file['tmp_name'] );
            }
        
        }
        
        if ( (isset( $_FILES['upload_mobmenu_settings'] ) || isset( $_REQUEST['demo'] )) && 'error' != $message_code ) {
            // If the file is empty.
            
            if ( !$file_content ) {
                $message = __( 'The file is empty. Upload a new file and try again.', 'mobile-menu' );
                $message_code = 'error';
            } else {
                // Update the Mobile Menu Settings.
                
                if ( get_option( 'mobmenu_options' ) !== $file_content ) {
                    $result = update_option( 'mobmenu_options', $file_content );
                    
                    if ( $result ) {
                        $message = __( 'Settings Imported successfully.', 'mobile-menu' );
                        $message_code = 'success';
                        $titan->cssInstance->generateSaveCSS();
                    } else {
                        $message = __( 'Something went wrong. Upload a new file and try again.', 'mobile-menu' );
                        $message_code = 'error';
                    }
                
                } else {
                    $message = __( 'The settings are exactly the same has the current ones. Nothing was imported.', 'mobile-menu' );
                    $message_code = 'warning';
                }
            
            }
        
        }
        $version_class = 'mm-free-version';
        ?>
		<div class="mobile-menu-demos-wrapper">
			<h2><?php 
        esc_html_e( 'Import the Mobile Menu Official Demos', 'mobile-menu' );
        ?></h2>
			<p><?php 
        esc_html_e( 'This process will import the settings from the official demos. The logos should be assigned after the import.', 'mobile-menu' );
        ?></p>
			<ul class="demos-importer">
				<li>
					<div>
						<h4><?php 
        esc_html_e( 'Free Demo', 'mobile-menu' );
        ?></h4>
						<button type="submit" class="button button-secondary button-next mobile-menu-import-demo" data-demo-id="free-demo" value="<?php 
        esc_attr_e( 'Import Demo', 'mobile-menu' );
        ?>"><?php 
        esc_html_e( 'Import Demo', 'mobile-menu' );
        ?></button>
						<?php 
        
        if ( isset( $_REQUEST['demo'] ) && 'free-demo' === $_REQUEST['demo'] ) {
            ?>
								<h4 class="<?php 
            echo  $message_code ;
            ?>"><?php 
            _e( $message, 'mobile-menu' );
            ?></h4>
						<?php 
        }
        
        ?>
					</div>
					<a href="https://demo.wpmobilemenu.com/?utm_source=wprepo-dash&utm_medium=user%20website&utm_campaign=import-demo" target="_blank">
						<img src="<?php 
        echo  plugins_url( 'demo-content/assets/freedemo-mobile-menu.png', __FILE__ ) ;
        ?>">
						<span><?php 
        esc_html_e( 'See Demo Site', 'mobile-menu' );
        ?></span>
					</a>
				</li>
				<li>
					<div>
						<h4><?php 
        esc_html_e( 'WooCommerce Shop Demo (Business)', 'mobile-menu' );
        ?></h4>
						<button type="submit" class="button button-secondary button-next mobile-menu-import-demo <?php 
        echo  $version_class ;
        ?>" data-demo-id="shop-demo" value="<?php 
        esc_attr_e( 'Import Demo', 'mobile-menu' );
        ?>"><?php 
        esc_html_e( 'Import Demo', 'mobile-menu' );
        ?></button>
						<?php 
        
        if ( isset( $_REQUEST['demo'] ) && 'shop-demo' === $_REQUEST['demo'] ) {
            ?>
								<h4 class="<?php 
            echo  $message_code ;
            ?>"><?php 
            _e( $message, 'mobile-menu' );
            ?></h4>
						<?php 
        }
        
        ?>
					</div>
					<a href="https://shopdemo.wpmobilemenu.com/?utm_source=wprepo-dash&utm_medium=user%20website&utm_campaign=demo_importer_option" target="_blank">
						<img src="<?php 
        echo  plugins_url( 'demo-content/assets/shopdemo-mobile-menu.png', __FILE__ ) ;
        ?>">
						<span><?php 
        esc_html_e( 'See Demo Site', 'mobile-menu' );
        ?></span>
					</a>
				</li>
				<li>
					<div>
						<h4><?php 
        esc_html_e( 'Professional Demo', 'mobile-menu' );
        ?></h4>
						<button type="submit" class="button button-secondary button-next mobile-menu-import-demo <?php 
        echo  $version_class ;
        ?>" data-demo-id="professional-demo" value="<?php 
        esc_attr_e( 'Import Demo', 'mobile-menu' );
        ?>"><?php 
        esc_html_e( 'Import Demo', 'mobile-menu' );
        ?></button>
						<?php 
        
        if ( isset( $_REQUEST['demo'] ) && 'professional-demo' === $_REQUEST['demo'] ) {
            ?>
								<h4 class="<?php 
            echo  $message_code ;
            ?>"><?php 
            _e( $message, 'mobile-menu' );
            ?></h4>
						<?php 
        }
        
        ?>
					</div>
					<a href="https://prodemo.wpmobilemenu.com/?utm_source=wprepo-dash&utm_medium=user%20website&utm_campaign=demo_importer_option" target="_blank">
						<img src="<?php 
        echo  plugins_url( 'demo-content/assets/prodemo-mobile-menu.png', __FILE__ ) ;
        ?>">
						<span><?php 
        esc_html_e( 'See Demo Site', 'mobile-menu' );
        ?></span>
					</a>
				</li>
			</ul>
		</div>

		<form class="mobile-menu-importer-wrapper" enctype="multipart/form-data" method="post" action=""> 

			<?php 
        // Security Nonce.
        wp_nonce_field( $settings_id, TF . '_nonce' );
        ?>

			<header>
				<h2><?php 
        esc_html_e( 'Import the Mobile Menu settings from a txt file', 'mobile-menu' );
        ?></h2>
				<p><?php 
        esc_html_e( 'This tool allows you to import the settings of WP Mobile Menu that were previously exported from other website.', 'mobile-menu' );
        ?></p>
			</header>
			<?php 
        
        if ( '' !== $message && !isset( $_REQUEST['demo'] ) ) {
            ?>
			<div class="mobmenu-message-holder">
				<h4 class="<?php 
            echo  $message_code ;
            ?>"><?php 
            _e( $message, 'mobile-menu' );
            ?></h4>
			</div>
			<?php 
        }
        
        ?>
			<section>
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">
								<label for="upload">
									<?php 
        esc_html_e( 'Choose the settings file from your computer:', 'mobile-menu' );
        ?>
								</label>
							</th>
							<td>
								<input type="file" name="upload_mobmenu_settings" size="25" />
								<input type="hidden" name="action" value="import_mobmenu_settings" />
							</td>
						</tr>
					</tbody>
				</table>
			</section>

			<div>
				<button type="submit" class="button button-primary button-next mobile-menu-import-settings" value="<?php 
        esc_attr_e( 'Import', 'mobile-menu' );
        ?>"><?php 
        esc_html_e( 'Import', 'mobile-menu' );
        ?></button>
			</div>
		</form>
		
		<?php 
    }

}