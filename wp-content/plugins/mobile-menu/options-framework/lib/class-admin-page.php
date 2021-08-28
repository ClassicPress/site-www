<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}

class TitanFrameworkAdminPage
{
    private  $defaultSettings = array(
        'name'       => '',
        'title'      => '',
        'parent'     => null,
        'id'         => '',
        'capability' => 'manage_options',
        'icon'       => 'dashicons-admin-generic',
        'position'   => null,
        'use_form'   => true,
        'desc'       => '',
    ) ;
    private  $alert_messages = array() ;
    public  $options = array() ;
    public  $tabs = array() ;
    private static  $idsUsed = array() ;
    private  $activeTab = null ;
    public  $settings = array() ;
    public  $owner ;
    public  $panelID ;
    function __construct( $settings, $owner )
    {
        $this->owner = $owner;
        if ( !is_admin() ) {
            return;
        }
        $this->settings = array_merge( $this->defaultSettings, $settings );
        // $this->options = $options;
        if ( empty($this->settings['name']) ) {
            return;
        }
        if ( empty($this->settings['title']) ) {
            $this->settings['title'] = $this->settings['name'];
        }
        
        if ( empty($this->settings['id']) ) {
            $prefix = '';
            if ( !empty($this->settings['parent']) ) {
                $prefix = str_replace( ' ', '-', trim( strtolower( $this->settings['parent'] ) ) ) . '-';
            }
            $this->settings['id'] = $prefix . str_replace( ' ', '-', trim( strtolower( $this->settings['name'] ) ) );
            $this->settings['id'] = str_replace( '&', '-', $this->settings['id'] );
        }
        
        // make sure all our IDs are unique
        $suffix = '';
        while ( in_array( $this->settings['id'] . $suffix, self::$idsUsed ) ) {
            
            if ( $suffix == '' ) {
                $suffix = 2;
            } else {
                $suffix++;
            }
        
        }
        $this->settings['id'] .= $suffix;
        // keep track of all IDs used
        self::$idsUsed[] = $this->settings['id'];
        $priority = -1;
        if ( $this->settings['parent'] ) {
            $priority = intval( $this->settings['position'] );
        }
        add_action( 'admin_menu', array( $this, 'register' ), $priority );
    }
    
    public function createAdminPanel( $settings )
    {
        if ( !isset( $this->settings['id'] ) ) {
            $settings['parent'] = null;
        }
        return $this->owner->createAdminPanel( $settings );
    }
    
    public function register()
    {
        // Parent menu
        
        if ( empty($this->settings['parent']) ) {
            $this->panelID = add_menu_page(
                $this->settings['name'],
                $this->settings['title'],
                $this->settings['capability'],
                $this->settings['id'],
                array( $this, 'createAdminPage' ),
                $this->settings['icon'],
                $this->settings['position']
            );
            // Sub menu
        } else {
            $this->panelID = add_submenu_page(
                $this->settings['parent'],
                $this->settings['name'],
                $this->settings['title'],
                $this->settings['capability'],
                $this->settings['id'],
                array( $this, 'createAdminPage' )
            );
        }
        
        add_action( 'load-' . $this->panelID, array( $this, 'saveOptions' ) );
    }
    
    public function getOptionNamespace()
    {
        return $this->owner->optionNamespace;
    }
    
    public function save_single_option( $option )
    {
        if ( empty($option->settings['id']) ) {
            return;
        }
        
        if ( isset( $_POST[$this->getOptionNamespace() . '_' . $option->settings['id']] ) ) {
            $value = $_POST[$this->getOptionNamespace() . '_' . $option->settings['id']];
        } else {
            $value = '';
        }
        
        $option->setValue( $value );
    }
    
    public function saveOptions()
    {
        if ( !$this->verifySecurity() ) {
            return;
        }
        $message = '';
        $activeTab = $this->getActiveTab();
        /*
         *  Save
         */
        
        if ( 'save' === $_POST['action'] ) {
            // we are in a tab.
            if ( !empty($activeTab) ) {
                foreach ( $activeTab->options as $option ) {
                    $this->save_single_option( $option );
                    if ( !empty($option->options) ) {
                        foreach ( $option->options as $group_option ) {
                            $this->save_single_option( $group_option );
                        }
                    }
                }
            }
            foreach ( $this->options as $option ) {
                $this->save_single_option( $option );
                if ( !empty($option->options) ) {
                    foreach ( $option->options as $group_option ) {
                        $this->save_single_option( $group_option );
                    }
                }
            }
            // Hook 'tf_pre_save_options_{namespace}' - action pre-saving.
            /**
             * Fired right before options are saved.
             *
             * @since 1.0
             *
             * @param TitanFrameworkAdminPage|TitanFrameworkMetaBox $this The container currently being saved.
             */
            $namespace = $this->getOptionNamespace();
            do_action( "tf_pre_save_options_{$namespace}", $this );
            do_action(
                "tf_pre_save_admin_{$namespace}",
                $this,
                $activeTab,
                $this->options
            );
            $this->owner->saveInternalAdminPageOptions();
            do_action(
                'tf_save_admin_' . $this->getOptionNamespace(),
                $this,
                $activeTab,
                $this->options
            );
            $message = 'saved';
            /*
             * Reset
             */
        } else {
            
            if ( 'reset' === $_POST['action'] ) {
                // We are in a tab.
                if ( !empty($activeTab) ) {
                    foreach ( $activeTab->options as $option ) {
                        if ( !empty($option->options) ) {
                            foreach ( $option->options as $group_option ) {
                                if ( !empty($group_option->settings['id']) ) {
                                    $group_option->setValue( $group_option->settings['default'] );
                                }
                            }
                        }
                        if ( empty($option->settings['id']) ) {
                            continue;
                        }
                        $option->setValue( $option->settings['default'] );
                    }
                }
                foreach ( $this->options as $option ) {
                    if ( !empty($option->options) ) {
                        foreach ( $option->options as $group_option ) {
                            if ( !empty($group_option->settings['id']) ) {
                                $group_option->setValue( $group_option->settings['default'] );
                            }
                        }
                    }
                    if ( empty($option->settings['id']) ) {
                        continue;
                    }
                    $option->setValue( $option->settings['default'] );
                }
                // Hook 'tf_pre_reset_options_{namespace}' - action pre-saving.
                do_action( 'tf_pre_reset_options_' . $this->getOptionNamespace(), $this );
                do_action(
                    'tf_pre_reset_admin_' . $this->getOptionNamespace(),
                    $this,
                    $activeTab,
                    $this->options
                );
                $this->owner->saveInternalAdminPageOptions();
                do_action(
                    'tf_reset_admin_' . $this->getOptionNamespace(),
                    $this,
                    $activeTab,
                    $this->options
                );
                $message = 'reset';
            }
        
        }
        
        if ( 'import_mobmenu_settings' !== sanitize_text_field( $_POST['action'] ) ) {
            do_action( 'tf_admin_options_saved_' . $this->getOptionNamespace() );
        }
    }
    
    private function verifySecurity()
    {
        if ( empty($_POST) || empty($_POST['action']) ) {
            return false;
        }
        $screen = get_current_screen();
        if ( $screen->id != $this->panelID ) {
            return false;
        }
        if ( !current_user_can( $this->settings['capability'] ) ) {
            return false;
        }
        if ( !check_admin_referer( $this->settings['id'], TF . '_nonce' ) ) {
            return false;
        }
        return true;
    }
    
    public function getActiveTab()
    {
        $this->activeTab = $this->tabs[0];
        return $this->activeTab;
    }
    
    public function get_alert_messages()
    {
        return $this->alert_messages;
    }
    
    private function check_left_menu_assignment()
    {
        if ( $this->owner->getOption( 'enable_left_menu' ) ) {
            if ( $this->owner->getOption( 'left_menu', '' ) == '' ) {
                if ( !has_nav_menu( 'left-wp-mobile-menu' ) ) {
                    array_push( $this->alert_messages, 'The Left menu isnt assigned.' );
                }
            }
        }
    }
    
    private function check_incorrect_url_settings()
    {
        if ( get_option( 'siteurl' ) != get_option( 'home' ) ) {
            array_push( $this->alert_messages, 'You may have incorrect settings in your Site URL option in Settings -> General' );
        }
    }
    
    private function check_right_menu_assignment()
    {
        if ( $this->owner->getOption( 'enable_right_menu' ) ) {
            if ( $this->owner->getOption( 'right_menu', '' ) == '' ) {
                if ( !has_nav_menu( 'right-wp-mobile-menu' ) ) {
                    array_push( $this->alert_messages, __( 'The Right menu isnt assigned.', 'mobile-menu' ) );
                }
            }
        }
    }
    
    private function check_footer_menu_assignment()
    {
        if ( $this->owner->getOption( 'enable_footer_icons' ) ) {
            if ( $this->owner->getOption( 'footer_menu', '' ) == '' ) {
                if ( !has_nav_menu( 'footer-wp-mobile-menu' ) ) {
                    array_push( $this->alert_messages, __( 'The Footer menu isnt assigned.', 'mobile-menu' ) );
                }
            }
        }
    }
    
    private function mm_scan_alert()
    {
        global  $mm_fs ;
        // Left Menu Enabled without menu.
        $this->check_left_menu_assignment();
        // Right Menu Enabled without menu.
        $this->check_right_menu_assignment();
        // Check Incorrect HTTPS settings.
        $this->check_incorrect_url_settings();
        return count( $this->alert_messages );
    }
    
    public function createAdminPage()
    {
        global  $mm_fs ;
        $alert_number = $this->mm_scan_alert();
        ?>
		<div class="wrap">
		<div class="mobmenu-header-wrap">
			<h2 class="mobmenu-title-h2"><?php 
        echo  $this->settings['title'] ;
        ?></h2>
			<div class="mobmenu-header-wrap-inner">
				<h2><?php 
        echo  $this->settings['title'] ;
        ?></h2>
				<div class='mm-panel-search-bar'>
					<input type="text" name="mm_search_settings" id="mm_search_settings" placeholder="Search Settings">
					<div class="mm-search-settings-results"></div>
				</div>
				<div class="mm-scan-alerts">
					<a href="#">
						<i class="dashicons-before dashicons-bell"></i>
						<span><?php 
        _e( "Alerts", 'mobile-menu' );
        ?><span class="mm-alerts-bubble alert-number-<?php 
        echo  $alert_number ;
        ?>"><?php 
        echo  $alert_number ;
        ?></span></span>
					</a>
				</div>
				<div class="mm-doc-icon"><a href="https://www.wpmobilemenu.com/knowledgebase/?utm_source=plugin-settings&utm_medium=user%20website&utm_campaign=documentation-link" target="_blank"><i class="dashicons-before dashicons-admin-page"></i><span>Documentation</span></a></div>
				<div class="mm-version-holder">
					<a href="https://www.wpmobilemenu.com/features-changelog/?utm_source=wprepo-dash&utm_medium=user%20website&utm_campaign=changelog_details" target="_blank"><?php 
        _e( "Version " . WP_MOBILE_MENU_VERSION . " </br>", 'mobile-menu' );
        ?></a>
				</div>
			</div>
		</div>
		<?php 
        
        if ( !empty($this->settings['desc']) ) {
            ?>
				<p class='description'><?php 
            echo  $this->settings['desc'] ;
            ?></p>
			<?php 
        }
        
        ?>

		<style>
			.tf-sortable .mm-lang-selector {
				background: url(<?php 
        echo  WP_MOBILE_MENU_PLUGIN_URL ;
        ?>/includes/assets/language-icon.png) no-repeat center top;
			}
			.tf-sortable .mm-cart-selector {
				background: url(<?php 
        echo  WP_MOBILE_MENU_PLUGIN_URL ;
        ?>/includes/assets/cart-icon.png) no-repeat center top;
			}
			.tf-sortable .mm-search-selector {
				background: url(<?php 
        echo  WP_MOBILE_MENU_PLUGIN_URL ;
        ?>/includes/assets/search-icon.png) no-repeat center top;
			}
			.tf-sortable .mm-left-menu-selector {
				background: url(<?php 
        echo  WP_MOBILE_MENU_PLUGIN_URL ;
        ?>/includes/assets/left-menu-icon.png) no-repeat center top;
			}
			.tf-sortable .mm-right-menu-selector {
				background: url(<?php 
        echo  WP_MOBILE_MENU_PLUGIN_URL ;
        ?>/includes/assets/right-menu-icon.png) no-repeat center top;
			}
			.tf-sortable .mm-logo-selector {
				background: url(<?php 
        echo  WP_MOBILE_MENU_PLUGIN_URL ;
        ?>/includes/assets/logo-icon.png) no-repeat center top;
			}
			.tf-sortable .mm-shop-filter-selector {
				background: url(<?php 
        echo  WP_MOBILE_MENU_PLUGIN_URL ;
        ?>/includes/assets/product-filter-icon.png) no-repeat center top;
			}
		</style>
		<?php 
        
        if ( !$mm_fs->is_premium() ) {
            $plan = 'mobmenu-standard';
        } else {
            $plan = 'mobmenu-premium';
        }
        
        ?>
		<div class='titan-framework-panel-wrap <?php 
        echo  $plan ;
        ?>' >
		<?php 
        
        if ( count( $this->tabs ) ) {
            ?>
			<h2 class="nav-tab-wrapper">
			<?php 
            foreach ( $this->tabs as $tab ) {
                $tab->displayTab();
            }
            ?>
			</h2>
			<?php 
        }
        
        
        if ( !isset( $_GET['mobmenu-action'] ) || isset( $_GET['mobmenu-action'] ) && 'import-settings' !== $_GET['mobmenu-action'] ) {
            $activeTab = $this->getActiveTab();
            ?>
		
		<div class='options-container active-tab-<?php 
            echo  $activeTab->settings['id'] ;
            ?>'>
		<?php 
            // Display notification if we did something.
            if ( !empty($_GET['message']) ) {
                
                if ( 'saved' === $_GET['message'] ) {
                    echo  TitanFrameworkAdminNotification::formNotification( __( 'Settings saved.', TF_I18NDOMAIN ), esc_html( $_GET['message'] ) ) ;
                } else {
                    if ( 'reset' === $_GET['message'] ) {
                        echo  TitanFrameworkAdminNotification::formNotification( __( 'Settings reset to default.', TF_I18NDOMAIN ), esc_html( $_GET['message'] ) ) ;
                    }
                }
            
            }
            if ( $this->settings['use_form'] ) {
                ?>
			<form method='post'>
			<?php 
            }
            if ( $this->settings['use_form'] ) {
                // Security nonce verification.
                wp_nonce_field( $this->settings['id'], TF . '_nonce' );
            }
            ?>
		<table class='form-table'>
			<tbody>
		<?php 
            do_action( 'tf_admin_page_table_start_' . $this->getOptionNamespace() );
            $activeTab = $this->getActiveTab();
            
            if ( !empty($activeTab) ) {
                
                if ( !empty($activeTab->settings['desc']) ) {
                    ?>
					<p class='description'><?php 
                    echo  $activeTab->settings['desc'] ;
                    ?></p>
				<?php 
                }
                
                $activeTab->displayOptions();
            }
            
            foreach ( $this->options as $option ) {
                $option->display();
            }
            do_action( 'tf_admin_page_table_end_' . $this->getOptionNamespace() );
            ?>
			</tbody>
		</table>
		<?php 
            if ( $this->settings['use_form'] ) {
                ?>
			</form>
			<?php 
            }
            do_action( 'tf_admin_page_end_' . $this->getOptionNamespace() );
            ?>
		<div class='options-container active-tab-<?php 
            echo  $activeTab->settings['id'] ;
            ?>'>
		</div>
		</div>
		</div>
		</div>

		<?php 
            do_action( 'tf_admin_page_after_' . $this->getOptionNamespace() );
        } else {
            do_action( 'mobile_menu_importer_page', $this->settings['id'] );
        }
    
    }
    
    public function createTab( $settings )
    {
        $obj = new TitanFrameworkAdminTab( $settings, $this );
        $this->tabs[] = $obj;
        do_action( 'tf_admin_tab_created_' . $this->getOptionNamespace(), $obj );
        return $obj;
    }
    
    public function createOption( $settings )
    {
        $obj = TitanFrameworkOption::factory( $settings, $this );
        $this->options[] = $obj;
        do_action( 'tf_create_option_' . $this->getOptionNamespace(), $obj );
        return $obj;
    }

}