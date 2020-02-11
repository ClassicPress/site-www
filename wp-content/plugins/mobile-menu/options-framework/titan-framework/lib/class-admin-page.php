<?php

if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
class TitanFrameworkAdminPage {

	private $defaultSettings = array(
		'name' => '', // Name of the menu item
		'title' => '', // Title displayed on the top of the admin panel
		'parent' => null, // id of parent, if blank, then this is a top level menu
		'id' => '', // Unique ID of the menu item
		'capability' => 'manage_options', // User role
		'icon' => 'dashicons-admin-generic', // Menu icon for top level menus only http://melchoyce.github.io/dashicons/
		'position' => null, // Menu position. Can be used for both top and sub level menus
		'use_form' => true, // If false, options will not be wrapped in a form
		'desc' => '', // Description displayed below the title
	);

	public $settings;
	public $options = array();
	public $tabs = array();
	public $owner;

	public $panelID;

	private $activeTab = null;
	private static $idsUsed = array();

	function __construct( $settings, $owner ) {
		$this->owner = $owner;

		if ( ! is_admin() ) {
			return;
		}

		$this->settings = array_merge( $this->defaultSettings, $settings );
		// $this->options = $options;
		if ( empty( $this->settings['name'] ) ) {
			return;
		}
		
		if ( empty( $this->settings['title'] ) ) {
			$this->settings['title'] = $this->settings['name'];
		}

		if ( empty( $this->settings['id'] ) ) {
			$prefix = '';
			if ( ! empty( $this->settings['parent'] ) ) {
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

	public function createAdminPanel( $settings ) {
		$settings['parent'] = $this->settings['id'];
		return $this->owner->createAdminPanel( $settings );
	}

	public function createSampleContentPage( $settings ) {
		$settings['parent'] = $this->settings['id'];
		return $this->owner->createSampleContentPage( $settings );
	}

	public function register() {
		// Parent menu
		if ( empty( $this->settings['parent'] ) ) {
			$this->panelID = add_menu_page( $this->settings['name'],
				$this->settings['title'],
				$this->settings['capability'],
				$this->settings['id'],
				array( $this, 'createAdminPage' ),
				$this->settings['icon'],
			$this->settings['position'] );
			// Sub menu
		} else {
			$this->panelID = add_submenu_page( $this->settings['parent'],
				$this->settings['name'],
				$this->settings['title'],
				$this->settings['capability'],
				$this->settings['id'],
			array( $this, 'createAdminPage' ) );
		}

		add_action( 'load-' . $this->panelID, array( $this, 'saveOptions' ) );

	}

	public function getOptionNamespace() {
		return $this->owner->optionNamespace;
	}


	public function save_single_option( $option ) {
		if ( empty( $option->settings['id'] ) ) {
			return;
		}

		if ( isset( $_POST[ $this->getOptionNamespace() . '_' . $option->settings['id'] ] ) ) {
			$value = $_POST[ $this->getOptionNamespace() . '_' . $option->settings['id'] ];
		} else {
			$value = '';
		}

		$option->setValue( $value );
	}


	public function saveOptions() {
		if ( ! $this->verifySecurity() ) {
			return;
		}

		$message = '';
		$activeTab = $this->getActiveTab();

		/*
		 *  Save
		 */

		if ( 'save' === $_POST['action'] ) {

			// we are in a tab.
			if ( ! empty( $activeTab ) ) {
				foreach ( $activeTab->options as $option ) {
					$this->save_single_option( $option );

					if ( ! empty( $option->options ) ) {
						foreach ( $option->options as $group_option ) {
							$this->save_single_option( $group_option );
						}
					}
				}
			}

			foreach ( $this->options as $option ) {
				$this->save_single_option( $option );

				if ( ! empty( $option->options ) ) {
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
			 * @param TitanFrameworkAdminPage|TitanFrameworkCustomizer|TitanFrameworkMetaBox $this The container currently being saved.
			 */
			$namespace = $this->getOptionNamespace();
			do_action( "tf_pre_save_options_{$namespace}", $this );
			do_action( "tf_pre_save_admin_{$namespace}", $this, $activeTab, $this->options );

			$this->owner->saveInternalAdminPageOptions();

			do_action( 'tf_save_admin_' . $this->getOptionNamespace(), $this, $activeTab, $this->options );

			$message = 'saved';

			/*
			* Reset
			*/

		} else if ( 'reset' === $_POST['action'] ) {

			// We are in a tab.
			if ( ! empty( $activeTab ) ) {
				foreach ( $activeTab->options as $option ) {

					if ( ! empty( $option->options ) ) {
						foreach ( $option->options as $group_option ) {

							if ( ! empty( $group_option->settings['id'] ) ) {
								$group_option->setValue( $group_option->settings['default'] );
							}
						}
					}

					if ( empty( $option->settings['id'] ) ) {
						continue;
					}

					$option->setValue( $option->settings['default'] );
				}
			}

			foreach ( $this->options as $option ) {

				if ( ! empty( $option->options ) ) {
					foreach ( $option->options as $group_option ) {

						if ( ! empty( $group_option->settings['id'] ) ) {
							$group_option->setValue( $group_option->settings['default'] );
						}
					}
				}

				if ( empty( $option->settings['id'] ) ) {
					continue;
				}

				$option->setValue( $option->settings['default'] );
			}

			// Hook 'tf_pre_reset_options_{namespace}' - action pre-saving.
			do_action( 'tf_pre_reset_options_' . $this->getOptionNamespace(), $this );
			do_action( 'tf_pre_reset_admin_' . $this->getOptionNamespace(), $this, $activeTab, $this->options );

			$this->owner->saveInternalAdminPageOptions();

			do_action( 'tf_reset_admin_' . $this->getOptionNamespace(), $this, $activeTab, $this->options );

			$message = 'reset';
		}

		if ( 'import_mobmenu_settings' !== sanitize_text_field( $_POST['action'] ) ) {

			// urlencode to allow special characters in the url
			$url       = wp_get_referer();
			$activeTab = $this->getActiveTab();
			$url       = add_query_arg( 'page', urlencode( $this->settings['id'] ), $url );
			if ( ! empty( $activeTab ) ) {
				$url = add_query_arg( 'tab', urlencode( $activeTab->settings['id'] ), $url );
			}
			if ( ! empty( $message ) ) {
				$url = add_query_arg( 'message', $message, $url );
			}

			do_action( 'tf_admin_options_saved_' . $this->getOptionNamespace() );

			wp_redirect( esc_url_raw( $url ) );
		}
	}

	private function verifySecurity() {
		if ( empty( $_POST ) || empty( $_POST['action'] ) ) {
			return false;
		}

		$screen = get_current_screen();
		if ( $screen->id != $this->panelID ) {
			return false;
		}

		if ( ! current_user_can( $this->settings['capability'] ) ) {
			return false;
		}

		if ( ! check_admin_referer( $this->settings['id'], TF . '_nonce' ) ) {
			return false;
		}

		return true;
	}

	public function getActiveTab() {
		if ( ! count( $this->tabs ) ) {
			return '';
		}
		if ( ! empty( $this->activeTab ) ) {
			return $this->activeTab;
		}

		if ( empty( $_GET['tab'] ) ) {
			$this->activeTab = $this->tabs[0];
			return $this->activeTab;
		}

		foreach ( $this->tabs as $tab ) {
			if ( $tab->settings['id'] == $_GET['tab'] ) {
				$this->activeTab = $tab;
				return $this->activeTab;
			}
		}

		$this->activeTab = $this->tabs[0];
		return $this->activeTab;
	}

	public function createAdminPage() {
		do_action( 'tf_admin_page_before_' . $this->getOptionNamespace() );

		?>
		<div class="wrap">
		<h2><?php echo $this->settings['title']; ?></h2>
		<?php
		if ( ! empty( $this->settings['desc'] ) ) {
			?>
				<p class='description'><?php echo $this->settings['desc']; ?></p>
			<?php
		}
		?>
		
		<div class='mm-panel-search-bar'>
			<input type="text" name="mm_search_settings" id="mm_search_settings" placeholder="Search Settings">
			<label> &lt;- Type here to find the desired settings</label>
			<div class="mm-search-settings-results"></div>
			<div class="mm-version-holder">
				<span><?php _e( "WP Mobile Menu Version " . WP_MOBILE_MENU_VERSION . " </br>", 'mobile-menu' ); ?></span>
				<a href="https://www.wpmobilemenu.com/?utm_source=wprepo-dash&utm_medium=user%20website&utm_campaign=changelog_details" target="_blank">View version details</a>
			</div>
		</div>
		<style>
			.tf-sortable .mm-lang-selector {
				background: url(<?php echo WP_MOBILE_MENU_PLUGIN_URL;?>/includes/assets/language-icon.png) no-repeat center top;
			}
			.tf-sortable .mm-cart-selector {
				background: url(<?php echo WP_MOBILE_MENU_PLUGIN_URL;?>/includes/assets/cart-icon.png) no-repeat center top;
			}
			.tf-sortable .mm-search-selector {
				background: url(<?php echo WP_MOBILE_MENU_PLUGIN_URL;?>/includes/assets/search-icon.png) no-repeat center top;
			}
			.tf-sortable .mm-left-menu-selector {
				background: url(<?php echo WP_MOBILE_MENU_PLUGIN_URL;?>/includes/assets/left-menu-icon.png) no-repeat center top;
			}
			.tf-sortable .mm-right-menu-selector {
				background: url(<?php echo WP_MOBILE_MENU_PLUGIN_URL;?>/includes/assets/right-menu-icon.png) no-repeat center top;
			}
			.tf-sortable .mm-logo-selector {
				background: url(<?php echo WP_MOBILE_MENU_PLUGIN_URL;?>/includes/assets/logo-icon.png) no-repeat center top;
			}
			.tf-sortable .mm-shop-filter-selector {
				background: url(<?php echo WP_MOBILE_MENU_PLUGIN_URL;?>/includes/assets/product-filter-icon.png) no-repeat center top;
			}
		</style>
		<div class='titan-framework-panel-wrap'>

		<?php

		if ( count( $this->tabs ) ) :
			?>
			<h2 class="nav-tab-wrapper">
			<?php

			foreach ( $this->tabs as $tab ) {
				$tab->displayTab();
			}

			?>
			</h2>
			<?php
		endif;

		if ( ! isset( $_GET['mobmenu-action'] ) || ( isset( $_GET['mobmenu-action'] ) && 'import-settings' !== $_GET['mobmenu-action'] ) ) {
			$activeTab = $this->getActiveTab();
		?>
		
		<div class='options-container active-tab-<?php echo $activeTab->settings['id']; ?>'>
		<?php

		// Display notification if we did something.
		if ( ! empty( $_GET['message'] ) ) {
			if ( 'saved' === $_GET['message'] ) {
				echo TitanFrameworkAdminNotification::formNotification( __( 'Settings saved.', TF_I18NDOMAIN ), esc_html( $_GET['message'] ) );
			} else if ( 'reset' === $_GET['message'] ) {
				echo TitanFrameworkAdminNotification::formNotification( __( 'Settings reset to default.', TF_I18NDOMAIN ), esc_html( $_GET['message'] ) );
			}
		}

		if ( $this->settings['use_form'] ) :
			?>
			<form method='post'>
			<?php
		endif;

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
		if ( ! empty( $activeTab ) ) {

			if ( ! empty( $activeTab->settings['desc'] ) ) {
				?>
					<p class='description'><?php echo $activeTab->settings['desc']; ?></p>
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

		if ( $this->settings['use_form'] ) :
			?>
			</form>
			<?php
		endif;

		// Reset form. We use JS to trigger a reset from other buttons within the main form.
		// This is used by class-option-save.php.
		if ( $this->settings['use_form'] ) :
			?>
			<form method='post' id='tf-reset-form'>
				<?php
				// security
				wp_nonce_field( $this->settings['id'], TF . '_nonce' );
				?>
				<input type='hidden' name='action' value='reset'/>
			</form>
			<?php
		endif;

		do_action( 'tf_admin_page_end_' . $this->getOptionNamespace() );

		?>
		<div class='options-container active-tab-<?php echo $activeTab->settings['id']; ?>'>
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

	public function createTab( $settings ) {
		$obj = new TitanFrameworkAdminTab( $settings, $this );
		$this->tabs[] = $obj;

		do_action( 'tf_admin_tab_created_' . $this->getOptionNamespace(), $obj );

		return $obj;
	}

	public function createOption( $settings ) {
		if ( ! apply_filters( 'tf_create_option_continue_' . $this->getOptionNamespace(), true, $settings ) ) {
			return null;
		}

		$obj = TitanFrameworkOption::factory( $settings, $this );
		$this->options[] = $obj;

		do_action( 'tf_create_option_' . $this->getOptionNamespace(), $obj );

		return $obj;
	}
}
