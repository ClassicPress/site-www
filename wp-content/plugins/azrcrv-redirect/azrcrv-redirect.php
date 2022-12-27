<?php
/**
 * ------------------------------------------------------------------------------
 * Plugin Name: Redirect
 * Description: Redirect URIs with a 301 (permanent) or 302 (temporary) redirect.
 * Version: 1.0.9
 * Author: azurecurve
 * Author URI: https://development.azurecurve.co.uk/classicpress-plugins/
 * Plugin URI: https://development.azurecurve.co.uk/classicpress-plugins/redirect/
 * Text Domain: azrcrv-r
 * Domain Path: /languages
 * ------------------------------------------------------------------------------
 * This is free software released under the terms of the General Public License,
 * version 2, or later. It is distributed WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. Full
 * text of the license is available at https://www.gnu.org/licenses/rrl-2.0.html.
 * ------------------------------------------------------------------------------
 */

// Declare the namespace.
namespace azurecurve\Redirect;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

// include plugin menu.
require_once dirname( __FILE__ ) . '/pluginmenu/menu.php';
add_action( 'admin_init', 'azrcrv_create_plugin_menu_r' );

// include update client.
require_once dirname( __FILE__ ) . '/libraries/updateclient/UpdateClient.class.php';

/**
 * Setup registration activation hook, actions, filters and shortcodes.
 *
 * @since 1.0.0
 */

// constants.
const DB_VERSION = '1.0.0';

// register activation hooks.
register_activation_hook( __FILE__, __NAMESPACE__ . '\\install' );

// add actions.
add_action( 'plugins_loaded', __NAMESPACE__ . '\\install' );
add_action( 'admin_menu', __NAMESPACE__ . '\\create_admin_menu' );
add_action( 'admin_init', __NAMESPACE__ . '\\register_admin_styles' );
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\enqueue_admin_styles' );
add_action( 'plugins_loaded', __NAMESPACE__ . '\\load_languages' );
add_action( 'admin_post_azrcrv_r_save_options', __NAMESPACE__ . '\\save_options' );
add_action( 'admin_post_azrcrv_r_add_redirect', __NAMESPACE__ . '\\add_redirect' );
add_action( 'admin_post_azrcrv_r_manage_redirects', __NAMESPACE__ . '\\manage_redirects' );
add_action( 'init', __NAMESPACE__ . '\\redirect_incoming' );
add_action( 'pre_post_update', __NAMESPACE__ . '\\pre_post_update', 10, 2 );
add_action( 'post_updated', __NAMESPACE__ . '\\add_redirect_for_changed_permalink', 11, 3 );

// add filters.
add_filter( 'plugin_action_links', __NAMESPACE__ . '\\add_plugin_action_link', 10, 2 );
add_filter( 'codepotent_update_manager_image_path', __NAMESPACE__ . '\\custom_image_path' );
add_filter( 'codepotent_update_manager_image_url', __NAMESPACE__ . '\\custom_image_url' );

/**
 * Create table on install of plugin.
 *
 * @since 1.0.0
 */
function install() {
	global $wpdb;

	$installed_ver = get_option( 'azrcrv-r-db-version' );

	if ( $installed_ver != DB_VERSION ) {

		$table_name = $wpdb->prefix . 'azrcrv_redirects';

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			source_url MEDIUMTEXT NOT NULL,
			redirect_count INT(10) UNSIGNED NOT NULL DEFAULT '0',
			last_redirect DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
			last_redirect_utc DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
			status ENUM('enabled','disabled') NOT NULL DEFAULT 'enabled',
			redirect_type INT(11) UNSIGNED NOT NULL,
			destination_url MEDIUMTEXT NOT NULL,
			added DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
			added_utc DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
			PRIMARY KEY (id) USING BTREE,
			INDEX source_url (source_url (191)) USING BTREE,
			INDEX destination_url (source_url (191)) USING BTREE,
			INDEX status (status) USING BTREE
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( 'azrcrv-r-db-version', DB_VERSION );
	}
}

/**
 * Register admin styles.
 *
 * @since 1.0.0
 */
function register_admin_styles() {
	wp_register_style( 'azrcrv-r-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), '1.0.0' );
}

/**
 * Enqueue admin styles.
 *
 * @since 1.0.0
 */
function enqueue_admin_styles() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['page'] ) && ( $_GET['page'] == 'azrcrv-r' || $_GET['page'] == 'azrcrv-r-mr' ) ) {
		wp_enqueue_style( 'azrcrv-r-admin-styles' );
	}
}

/**
 * Load language files.
 *
 * @since 1.0.0
 */
function load_languages() {
	$plugin_rel_path = basename( dirname( __FILE__ ) ) . '/languages';
	load_plugin_textdomain( 'azrcrv-r', false, $plugin_rel_path );
}

/**
 * Get options including defaults.
 *
 * @since 1.0.0
 */
function get_option_with_defaults( $option_name ) {

	$defaults = array(
		'default-redirect'           => 301, // permanent = 301.
		'redirect-permalink-changes' => 0,
		'redirect-rows'              => 20,
	);

	$options = get_option( $option_name, $defaults );

	$options = recursive_parse_args( $options, $defaults );

	return $options;

}

/**
 * Recursively parse options to merge with defaults.
 *
 * @since 1.0.0
 */
function recursive_parse_args( $args, $defaults ) {
	$new_args = (array) $defaults;

	foreach ( $args as $key => $value ) {
		if ( is_array( $value ) && isset( $new_args[ $key ] ) ) {
			$new_args[ $key ] = recursive_parse_args( $value, $new_args[ $key ] );
		} else {
			$new_args[ $key ] = $value;
		}
	}

	return $new_args;
}

/**
 * Add action link on plugins page.
 *
 * @since 1.0.0
 */
function add_plugin_action_link( $links, $file ) {
	static $this_plugin;

	if ( ! $this_plugin ) {
		$this_plugin = plugin_basename( __FILE__ );
	}

	if ( $file == $this_plugin ) {
		$settings_link = '<a href="' . esc_url_raw( admin_url( 'admin.php?page=azrcrv-r' ) ) . '"><img src="' . esc_url_raw( plugins_url( '/pluginmenu/images/logo.svg', __FILE__ ) ) . '" style="padding-top: 2px; margin-right: -5px; height: 16px; width: 16px;" alt="azurecurve" />' . esc_html__( 'Settings', 'azrcrv-r' ) . '</a>';
		array_unshift( $links, $settings_link );
	}

	return $links;
}

/**
 * Custom plugin image path.
 *
 * @since 1.0.0
 */
function custom_image_path( $path ) {
	if ( strpos( $path, 'azrcrv-redirect' ) !== false ) {
		$path = plugin_dir_path( __FILE__ ) . 'assets/pluginimages';
	}
	return $path;
}

/**
 * Custom plugin image url.
 *
 * @since 1.0.0
 */
function custom_image_url( $url ) {
	if ( strpos( $url, 'azrcrv-redirect' ) !== false ) {
		$url = plugin_dir_url( __FILE__ ) . 'assets/pluginimages';
	}
	return $url;
}

/**
 * Add to menu.
 *
 * @since 1.0.0
 */
function create_admin_menu() {

	add_menu_page(
		esc_html__( 'Redirect', 'azrcrv-r' ),
		esc_html__( 'Redirect', 'azrcrv-r' ),
		'manage_options',
		'azrcrv-r',
		__NAMESPACE__ . '\\display_options',
		'dashicons-migrate',
		50
	);

	add_submenu_page(
		'azrcrv-r',
		esc_html__( 'Redirect Settings', 'azrcrv-r' ),
		esc_html__( 'Settings', 'azrcrv-r' ),
		'manage_options',
		'azrcrv-r',
		__NAMESPACE__ . '\\display_options'
	);

	add_submenu_page(
		'azrcrv-r',
		esc_html__( 'Manage Redirects', 'azrcrv-r' ),
		esc_html__( 'Manage Redirects', 'azrcrv-r' ),
		'manage_options',
		'azrcrv-r-mr',
		__NAMESPACE__ . '\\display_manage_redirects'
	);

	add_submenu_page(
		'azrcrv-plugin-menu',
		esc_html__( 'Redirect Settings', 'azrcrv-r' ),
		esc_html__( 'Redirect', 'azrcrv-r' ),
		'manage_options',
		'azrcrv-r',
		__NAMESPACE__ . '\\display_options'
	);

}

/**
 * Load admin css.
 *
 * @since 1.0.0
 */
function load_admin_style() {
	wp_register_style( 'r-css', plugins_url( 'assets/css/admin.css', __FILE__ ), false, '1.0.0' );
	wp_enqueue_style( 'r-css' );
}

/**
 * Display Settings page.
 *
 * @since 1.0.0
 */
function display_options() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'azrcrv-r' ) );
	}

	global $wpdb;

	// Retrieve plugin configuration options from database.
	$options = get_option_with_defaults( 'azrcrv-r' );

	echo '<div id="azrcrv-r-general" class="wrap">';

	?>
		<h1>
			<?php
				echo '<a href="https://development.azurecurve.co.uk/classicpress-plugins/"><img src="' . esc_html( plugins_url( '/pluginmenu/images/logo.svg', __FILE__ ) ) . '" style="padding-right: 6px; height: 20px; width: 20px;" alt="azurecurve" /></a>';
				echo esc_html( get_admin_page_title() );
			?>
		</h1>
		<?php

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['settings-updated'] ) ) {
			echo '<div class="notice notice-success is-dismissible">
					<p><strong>' . esc_html__( 'Settings have been saved.', 'azrcrv-r' ) . '</strong></p>
				</div>';
		}

		if ( $options['default-redirect'] == 301 ) {
			$redirect_301 = 'selected=selected';
			$redirect_302 = '';
		} else {
			$redirect_301 = '';
			$redirect_302 = 'selected=selected';
		}
		$tab_1 = '<table class="form-table">
		
					<tr>
						<th scope="row">
							
								' . esc_html__( 'Default Redirect Type', 'azrcrv-r' ) . '
							
						</th>
					
						<td>
							
							<select name="default-redirect">
								<option value="301" ' . $redirect_301 . '>301 - Permanent Redirect</option>
								<option value="302" ' . $redirect_302 . '>302 - Temporary Redirect</option>
							</select>
							<p class="description">' . esc_html__( 'Type of redirect to use as default.', 'azrcrv-r' ) . '</p>
						
						</td>
	
					</tr>
		
					<tr>
						<th scope="row">
							
								' . esc_html__( 'Redirect Permalink Changes', 'azrcrv-r' ) . '
							
						</th>
					
						<td>
							
							<input name="redirect-permalink-changes" type="checkbox" id="redirect-permalink-changes" value="1" ' . checked( '1', esc_attr( $options['redirect-permalink-changes'] ), false ) . ' />
							<label for="redirect-permalink-changes"><span class="description">
								' . esc_html__( 'Monitor for permalink changes and add redirect.', 'azrcrv-r' ) . '
							</span></label>
							
						</td>
	
					</tr>
		
					<tr>
						<th scope="row">
							
								' . esc_html__( 'Redirect Rows', 'azrcrv-r' ) . '
							
						</th>
					
						<td>
							
							<input name="redirect-rows" type="number" step="1" min="1" id="redirect-rows" value="' . stripslashes( $options['redirect-rows'] ) . '" class="small-text" />
							<label for="redirect-rows"><span class="description">
								' . esc_html__( 'Number of rows to show in Manage Redirects list.', 'azrcrv-r' ) . '
							</span></label>
							
						</td>
	
					</tr>
					
				</table>';

		?>
		<form method="post" action="admin-post.php">
			<fieldset>

				<input type="hidden" name="action" value="azrcrv_r_save_options" />
				<input name="page_options" type="hidden" value="default-redirect,redirect-permalink-changes" />

				<?php
					// <!-- Adding security through hidden referrer field -->.
					wp_nonce_field( 'azrcrv-r', 'azrcrv-r-nonce' );
				?>

				<div id="tabs">
					<div id="tab-panel-1" >
						<?php
						// String prepared above is safe.
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $tab_1;
						?>
					</div>
				</div>
			</fieldset>

			<input type="submit" name="btn_save" value="<?php esc_html_e( 'Save Settings', 'azrcrv-r' ); ?>" class="button-primary"/>
		</form>
	</div>
	<?php

}

/**
 * Save settings.
 *
 * @since 1.0.0
 */
function save_options() {
	// Check that user has proper security level.
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permissions to perform this action', 'azrcrv-r' ) );
	}

	// Check that nonce field created in configuration form is present.
	if ( ! empty( $_POST ) && check_admin_referer( 'azrcrv-r', 'azrcrv-r-nonce' ) ) {

		// Retrieve original plugin options array.
		$options         = get_option( 'azrcrv-r' );
		$default_options = get_option_with_defaults( 'azrcrv-r' );

		// update settings.
		$option_name = 'default-redirect';
		if ( isset( $_POST[ $option_name ] ) && $_POST[ $option_name ] == 301 ) {
			$options[ $option_name ] = 301;
		} else {
			$options[ $option_name ] = 302;
		}

		$option_name = 'redirect-permalink-changes';
		if ( isset( $_POST[ $option_name ] ) ) {
			$options[ $option_name ] = 1;
		} else {
			$options[ $option_name ] = 0;
		}

		$option_name = 'redirect-rows';
		if ( isset( $_POST[ $option_name ] ) && (int) $_POST[ $option_name ] > 0 ) {
			$options[ $option_name ] = (int) $_POST[ $option_name ];
		} else {
			$options[ $option_name ] = $default_options[ $redirect - rows ];
		}

		// Store updated options array to database.
		update_option( 'azrcrv-r', $options );

		// Redirect the page to the configuration form that was processed.
		wp_safe_redirect( add_query_arg( 'page', 'azrcrv-r&settings-updated', admin_url( 'admin.php' ) ) );
		exit;
	}
}

/**
 * Display Manage Redirect page.
 *
 * @since 1.0.0
 */
function display_manage_redirects() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'azrcrv-r' ) );
	}

	global $wpdb;

	// Retrieve plugin configuration options from database.
	$options = get_option_with_defaults( 'azrcrv-r' );

	echo '<div id="azrcrv-r-general" class="wrap">';

	?>
		<h1>
			<?php
				echo '<a href="https://development.azurecurve.co.uk/classicpress-plugins/"><img src="' . esc_url_raw( plugins_url( '/pluginmenu/images/logo.svg', __FILE__ ) ) . '" style="padding-right: 6px; height: 20px; width: 20px;" alt="azurecurve" /></a>';
				echo esc_html( get_admin_page_title() );
			?>
		</h1>

		<?php

		if ( isset( $_GET['redirect-added'] ) ) {
			echo '<div class="notice notice-success is-dismissible">
					<p><strong>' . esc_html__( 'Redirect has been added.', 'azrcrv-r' ) . '</strong></p>
				</div>';
		} elseif ( isset( $_GET['cannot-redirect-home'] ) ) {
			echo '<div class="notice notice-error is-dismissible">
					<p><strong>' . esc_html__( 'Redirect cannot be added for the site home page.', 'azrcrv-r' ) . '</strong></p>
				</div>';
		} elseif ( isset( $_GET['missing-urls'] ) ) {
			echo '<div class="notice notice-error is-dismissible">
					<p><strong>' . esc_html__( 'Source or destination url is empty.', 'azrcrv-r' ) . '</strong></p>
				</div>';
		} elseif ( isset( $_GET['already-exists'] ) ) {
			echo '<div class="notice notice-error is-dismissible">
					<p><strong>' . esc_html__( 'Redirect already exists for supplied source url.', 'azrcrv-r' ) . '</strong></p>
				</div>';
		} elseif ( isset( $_GET['invalid-type'] ) ) {
			echo '<div class="notice notice-error is-dismissible">
					<p><strong>' . esc_html__( 'Invalid redirect type specified.', 'azrcrv-r' ) . '</strong></p>
				</div>';
		} elseif ( isset( $_GET['not-relative'] ) ) {
			echo '<div class="notice notice-error is-dismissible">
					<p><strong>' . esc_html__( 'Only relative urls can be redirected.', 'azrcrv-r' ) . '</strong></p>
				</div>';
		} elseif ( isset( $_GET['not-valid'] ) ) {
			echo '<div class="notice notice-error is-dismissible">
					<p><strong>' . esc_html__( 'Only valid pages can be set as a destination.', 'azrcrv-r' ) . '</strong></p>
				</div>';
		} elseif ( isset( $_GET['deleted'] ) ) {
			echo '<div class="notice notice-success is-dismissible">
					<p><strong>' . esc_html__( 'Redirect has been deleted.', 'azrcrv-r' ) . '</strong></p>
				</div>';
		} elseif ( isset( $_GET['redirect-edited'] ) ) {
			echo '<div class="notice notice-success is-dismissible">
					<p><strong>' . esc_html__( 'Redirect has been edited.', 'azrcrv-r' ) . '</strong></p>
				</div>';
		} elseif ( isset( $_GET['delete-failed'] ) ) {
			echo '<div class="notice notice-error is-dismissible">
					<p><strong>' . esc_html__( 'Delete of redirect failed.', 'azrcrv-r' ) . '</strong></p>
				</div>';
		} elseif ( isset( $_GET['edit-failed'] ) ) {
			echo '<div class="notice notice-error is-dismissible">
					<p><strong>' . esc_html__( 'Edit of redirect failed.', 'azrcrv-r' ) . '</strong></p>
				</div>';
		}

		$tab_1 = '<h2>' . esc_html__( 'Add New Redirect', 'azrcrv-r' ) . '</h2>';

		if ( $options['default-redirect'] == 301 ) {
			$redirect_301 = 'selected=selected';
			$redirect_302 = '';
		} else {
			$redirect_301 = '';
			$redirect_302 = 'selected=selected';
		}

		$id              = 0;
		$source_url      = '';
		$redirect_type   = '';
		$destination_url = '';
		if ( isset( $_POST['id'] ) ) {

			if ( ! empty( $_POST ) && check_admin_referer( 'azrcrv-r-mr-edit', 'azrcrv-r-mr-edit-nonce' ) ) {

				$id = (int) $_POST['id'];

				$row = $wpdb->get_row( $wpdb->prepare( "SELECT source_url,redirect_type,destination_url FROM {$wpdb->prefix}azrcrv_redirects WHERE id = %d LIMIT 0,1", $id ) );

				if ( $row ) {
					$tab_1      = '<h2>' . esc_html__( 'Edit Redirect', 'azrcrv-r' ) . '</h2>';
					$source_url = $row->source_url;
					if ( $row->redirect_type == 301 ) {
						$redirect_301 = 'selected=selected';
						$redirect_302 = '';
					} else {
						$redirect_301 = '';
						$redirect_302 = 'selected=selected';
					}
					$destination_url = $row->destination_url;
				} else {
					$id = 0;
				}
			}
		}

		$tab_1 .= '<table class="form-table">
					
					<tr>
					
						<th scope="row">
						
							<label for="source-url">
								' . esc_html__( 'Source URL', 'azrcrv-r' ) . '
							</label>
							
						</th>
						
						<td>
						
							<input name="source-url" type="text" id="source-url" value="' . $source_url . '" class="large-text" placeholder="' . esc_html__( 'Relative URL to redirect', 'azrcrv-r' ) . '" />
							
						</td>
						
					</tr>
		
					<tr>
						<th scope="row">
							
								' . esc_html__( 'Redirect Type', 'azrcrv-r' ) . '
							
						</th>
					
						<td>
							
							<select name="redirect-type">
								<option value="301" ' . $redirect_301 . '>301 - Permanent Redirect</option>
								<option value="302" ' . $redirect_302 . '>302 - Temporary Redirect</option>
							</select>
							
						</td>
	
					</tr>
					
					<tr>
					
						<th scope="row">
						
							<label for="destination-url">
								' . esc_html__( 'Destination URL', 'azrcrv-r' ) . '
							</label>
							
						</th>
						
						<td>
						
							<input name="destination-url" type="text" id="destination-url" value="' . $destination_url . '" class="large-text" placeholder="' . esc_html__( 'Destination URL for the redirect', 'azrcrv-r' ) . '" />
							
						</td>
						
					</tr>
					
				</table>';

		$redirect_rows = (int) $options['redirect-rows'];
		if ( isset( $_GET['p'] ) ) {
			$page_number = (int) $_GET['p'];
			$page_start  = $page_number * $redirect_rows - $redirect_rows;
		} else {
			$page_start = 0;
		}

		$page_end = $redirect_rows;
		$limit    = $page_start . ', ' . $page_end;

		$date_format = '%Y-%m-%d';
		// "complex" placeholders used intentionally so that date format is
		// not wrapped in single quotes.
		$resultset = $wpdb->get_results( $wpdb->prepare( 'SELECT id,source_url,redirect_count,DATE_FORMAT(last_redirect, \'%1$s\') AS last_redirect,status,redirect_type,destination_url FROM %2$s ORDER BY source_url LIMIT %3$s', $date_format, $wpdb->prefix . 'azrcrv_redirects', $limit ) );

		$tab_2 = '<h2>' . esc_html__( 'Current Redirects', 'azrcrv-r' ) . '</h2>
		<table class="azrcrv-r">
			<thead>
				<tr>
					<th>
						' . esc_html__( 'Source URL', 'azrcrv-r' ) . '
					</th>
					<th>
						' . esc_html__( 'Redirects', 'azrcrv-r' ) . '
					</th>
					<th>
						' . esc_html__( 'Last Redirect', 'azrcrv-r' ) . '
					</th>
					<th>
						' . esc_html__( 'Status', 'azrcrv-r' ) . '
					</th>
					<th>
						' . esc_html__( 'Redirect Type', 'azrcrv-r' ) . '
					</th>
					<th>
						' . esc_html__( 'Destination URL', 'azrcrv-r' ) . '
					</th>
					<th>
						' . esc_html__( 'Action', 'azrcrv-r' ) . '
					</th>
				<tr>
			</thead>
			<tbody>
				%s
			</tbody>
		</table>';

		$tbody_blank = '<tr colspan=7><td>' . esc_html__( 'No redirects found for this page...', 'azrcrv-r' ) . '</td></tr>';
		$tbody       = '';
		foreach ( $resultset as $result ) {

			if ( $result->status == 'enabled' ) {
				$redirect_status = esc_html__( 'Enabled', 'azrcrv-r' );
				$td_class        = 'azrcrv-r-enabled';
			} else {
				$redirect_status = esc_html__( 'Disabled', 'azrcrv-r' );
				$td_class        = 'azrcrv-r-disabled';
			}

			if ( $result->redirect_type == '301' ) {
				$redirect_type = sprintf( esc_html__( '%d - permanent', 'azrcrv-r' ), '301' );
			} else {
				$redirect_type = sprintf( esc_html__( '%d - temporary', 'azrcrv-r' ), '302' );
			}

			$tbody .= '<tr>
					<td>
						<a href="' . esc_attr( $result->source_url ) . '">' . esc_html( $result->source_url ) . '</a>
					</td>
					<td>
						' . esc_html( $result->redirect_count ) . '
					</td>
					<td>
						' . esc_html( $result->last_redirect ) . '
					</td>
					<td class="' . $td_class . '">
						' . esc_html( $redirect_status ) . '
					</td>
					<td>
						' . esc_html( $redirect_type ) . '
					</td>
					<td>
						<a href="' . esc_attr( $result->destination_url ) . '">' . esc_html( $result->destination_url ) . '</a>
					</td>
					<td>
						%s
					</td>
				</tr>';

			$page_field = '';
			if ( isset( $_GET['p'] ) ) {
				$page_number = (int) $_GET['p'];
				$page_field  = '<input type="hidden" name="p" value="' . esc_attr( $page_number ) . '" class="short-text" />';
			}

			$buttons = '<div style="display: inline-block; "><form method="post" action="admin-post.php">
									
						<input type="hidden" name="action" value="azrcrv_r_manage_redirects" />
						<input name="page_options" type="hidden" value="toggle" />
						<input type="hidden" name="id" value="' . esc_html( stripslashes( $result->id ) ) . '" class="short-text" />
						' . $page_field . '
						
						' .
							wp_nonce_field( 'azrcrv-r-mr', 'azrcrv-r-mr-nonce', true, false )
						. '
						
						<input type="hidden" name="button_action" value="toggle" class="short-text" />
						<input style="height: 24px; " type="image" src="' . esc_url_raw( plugin_dir_url( __FILE__ ) ) . 'assets/images/toggle.svg" id="button_action" name="button_action" title="Toggle Status" alt="Toggle Status" value="toggle" class="arcrv-r"/>
					
					</form>
			</div><div style="display: inline-block; "><form method="post" action="admin.php?page=azrcrv-r-mr">
									
						<input type="hidden" name="action" value="azrcrv_r_manage_redirects_edit" />
						<input name="page_options" type="hidden" value="edit" />
						<input type="hidden" name="id" value="' . esc_html( stripslashes( $result->id ) ) . '" class="short-text" />
						' . $page_field . '
						
						' .
							wp_nonce_field( 'azrcrv-r-mr-edit', 'azrcrv-r-mr-edit-nonce', true, false )
						. '
						
						<input type="hidden" name="button_action" value="edit" class="short-text" />
						<input style="height: 24px; " type="image" src="' . plugin_dir_url( __FILE__ ) . 'assets/images/edit.svg" id="button_action" name="button_action" title="Edit" alt="Edit" value="edit" class="arcrv-r"/>
					
					</form>
			</div><div style="display: inline-block; "><form method="post" action="admin-post.php">
									
						<input type="hidden" name="action" value="azrcrv_r_manage_redirects" />
						<input name="page_options" type="hidden" value="delete" />
						<input type="hidden" name="id" value="' . esc_html( stripslashes( $result->id ) ) . '" class="short-text" />
						' . $page_field . '
						
						' .
							wp_nonce_field( 'azrcrv-r-mr', 'azrcrv-r-mr-nonce', true, false )
						. '
						
					<input type="hidden" name="button_action" value="delete" class="short-text" />
						<input style="height: 24px; " type="image" src="' . esc_url_raw( plugin_dir_url( __FILE__ ) ) . 'assets/images/delete.svg" id="button_action" name="button_action" title="Delete" alt="Delete" value="delete" class="arcrv-r"/>
					
					</form>
			</div>';

			$tbody = sprintf( $tbody, $buttons );

		}

		if ( $tbody == '' ) {
			$tbody = $tbody_blank;
		}

		$tab_2 = sprintf( $tab_2, $tbody );

		?>

		<div id="tabs">
			<div id="tab-panel-1" >

				<form method="post" action="admin-post.php">
					<fieldset>

						<input type="hidden" name="action" value="azrcrv_r_add_redirect" />
						<input name="page_options" type="hidden" value="source-url,redirect-type,destination-url" />
						<input type="hidden" name="id" value="<?php echo esc_html( $id ); ?>" />
						
						<?php
							// <!-- Adding security through hidden referrer field -->.
							wp_nonce_field( 'azrcrv-r-ar', 'azrcrv-r-ar-nonce', true, true );
						?>

						<div id="tabs">
							<div id="tab-panel-1" >
								<?php
								// String prepared above is safe.
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo $tab_1;
								?>
							</div>
						</div>
					</fieldset>

					<?php
					if ( $id == 0 ) {
						$button_text = esc_html__( 'Add redirect', 'azrcrv-r' );
					} else {
						$button_text = esc_html__( 'Edit redirect', 'azrcrv-r' );
					}
					// String prepared above is safe.
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
					<input type="submit" name="btn_add" value="<?php echo $button_text; ?>" class="button-primary"/>
				</form>

			</div>
			<div id="tab-panel-2" >
				<?php
					// String prepared above is safe.
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo $tab_2;
					// get_pagination() is safe.
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo '<div class=azrcrv-r-pagination>' . get_pagination( $redirect_rows ) . '</div>';
				?>
			</div>
		</div>

	</div>
	<?php

}

/**
 * Manage redirect.
 *
 * @since 1.0.0
 */
function manage_redirects() {
	// Check that user has proper security level.
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permissions to perform this action', 'azrcrv-r' ) );
	}

	// Check that nonce field created in configuration form is present.
	if ( ! empty( $_POST ) && check_admin_referer( 'azrcrv-r-mr', 'azrcrv-r-mr-nonce' ) ) {

		global $wpdb;

		if ( isset( $_POST['button_action'] ) ) {

			// Retrieve original plugin options array.
			$options = get_option( 'azrcrv-r' );

			$page = '';
			if ( isset( $_POST['p'] ) ) {
				$page = '&p=' . sanitize_text_field( wp_unslash( $_POST['p'] ) );
			}

			if ( isset( $_POST['id'] ) && (int) $_POST['id'] > 0 ) {
				$id = (int) $_POST['id'];

				if ( $_POST['button_action'] == 'delete' ) {

					$wpdb->delete( $wpdb->prefix . 'azrcrv_redirects', array( 'id' => esc_attr( $id ) ) );

					wp_safe_redirect( add_query_arg( 'page', 'azrcrv-r-mr&deleted' . esc_html( $page ), admin_url( 'admin.php' ) ) );

				} else {

					$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}azrcrv_redirects SET status = CASE WHEN status = 'enabled' THEN 'disabled' ELSE 'enabled' END WHERE id = %d", esc_attr( $id ) ) );

					wp_safe_redirect( add_query_arg( 'page', 'azrcrv-r-mr&refresh' . esc_html( $page ), admin_url( 'admin.php' ) ) );

				}
			}
		}
		exit;
	}
}

/**
 * Display Add Redirect page.
 *
 * @since 1.0.0
 */
function get_pagination( $rows_per_page ) {

	$pagination = '';

	global $wpdb;

	$range = 5;// how many pages to show in page link.

	// Usage of $_GET['p'] is safe. It is forced to a number and this function
	// is only called after a 'manage_options' capability check (see function
	// display_manage_redirects()).
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['p'] ) && is_numeric( $_GET['p'] ) ) {
		// cast var as int.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$currentpage = (int) $_GET['p'];
	} else {
		// default page num.
		$currentpage = 1;
	}

	$numrows = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}azrcrv_redirects" );

	$totalpages = ceil( $numrows / $rows_per_page );

	if ( $currentpage > $totalpages ) {
		// set current page to last page.
		$currentpage = $totalpages;
	}
	// if current page is less than first page...
	if ( $currentpage < 1 ) {
		// set current page to first page.
		$currentpage = 1;
	}

	if ( $currentpage > 1 ) {
		// show << link to go back to page 1.
		$pagination .= " <a href='admin.php?page=azrcrv-r-mr&p=1'>&laquo;</a> ";
		// get previous page num.
		$prevpage = $currentpage - 1;
		// show < link to go back to 1 page.
		$pagination .= " <a href='admin.php?page=azrcrv-r-mr&p=$prevpage'>&lsaquo;</a> ";
	}

	// loop to show links to range of pages around current page.
	for ( $x = ( $currentpage - $range ); $x < ( ( $currentpage + $range ) + 1 ); $x++ ) {
		// if it's a valid page number...
		if ( ( $x > 0 ) && ( $x <= $totalpages ) ) {
			// if we're on current page...
			if ( $x == $currentpage ) {
				// 'highlight' it but don't make a link.
				$pagination .= " [<b>$x</b>] ";
				// if not current page...
			} else {
				// make it a link.
				$pagination .= " <a href='admin.php?page=azrcrv-r-mr&p=$x'>$x</a> ";
			}
		}
	}

	// if not on last page, show forward and last page links.
	if ( $currentpage != $totalpages ) {
		// get next page.
		$nextpage = esc_html( $currentpage + 1 );
		// echo forward link for next page.

		$pagination .= " <a href='admin.php?page=azrcrv-r-mr&p=$nextpage'>&rsaquo;</a> ";
		// echo forward link for lastpage.
		$pagination .= " <a href='admin.php?page=azrcrv-r-mr&p=$totalpages'>&raquo;</a> ";
	}

	return $pagination;

}

/**
 * Add redirect.
 *
 * @since 1.0.0
 */
function add_redirect() {
	// Check that user has proper security level.
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permissions to perform this action', 'azrcrv-r' ) );
	}

	// Check that nonce field created in configuration form is present.
	if ( ! empty( $_POST ) && check_admin_referer( 'azrcrv-r-ar', 'azrcrv-r-ar-nonce' ) ) {

		global $wpdb;

		if ( isset( $_POST['btn_add'] ) ) {

			// Retrieve original plugin options array.
			$options = get_option( 'azrcrv-r' );

			$page = '';
			if ( isset( $_POST['p'] ) ) {
				$page = '&p=' . sanitize_text_field( wp_unslash( $_POST['p'] ) );
			}

			$id = 0;
			if ( isset( $_POST['id'] ) ) {
				$id = (int) $_POST['id'];
			}

			if ( isset( $_POST['source-url'] ) ) {
				$before = trailingslashit( wp_parse_url( sanitize_text_field( wp_unslash( $_POST['source-url'] ) ), PHP_URL_PATH ) );
			} else {
				$before = '';
			}
			if ( isset( $_POST['redirect-type'] ) ) {
				$redirect_type = (int) $_POST['redirect-type'];
			} else {
				$redirect_type = 0;
			}
			if ( isset( $_POST['destination-url'] ) ) {
				//$after = trailingslashit( wp_parse_url( sanitize_text_field( wp_unslash( $_POST['destination-url'] ) ), PHP_URL_PATH ) );
				$after = trailingslashit( sanitize_text_field( wp_unslash( $_POST['destination-url'] ), PHP_URL_PATH ) );
			} else {
				$after = '';
			}

			if ( $id == 0 ) {
				$redirect = check_for_redirect( $before );
			}

			if ( $redirect && $id == 0 ) {
				$message = 'already-exists';
			} elseif ( $before == '/' ) { // is homepage?
				$message = 'cannot-redirect-home';
			} elseif ( $before == '' || $after == '' ) { // both source and destination url required.
				$message = 'missing-urls';
			} elseif ( $redirect_type != '301' && $redirect_type != '302' ) { // invalid redirect type.
				$message = 'invalid-type';
			} elseif ( wp_make_link_relative( $before ) <> $before ) { // before must be relative.
				$message = 'not-relative';
			} else {
				// insert new redirect.
				if ( $id == 0 ) {
					$wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}azrcrv_redirects (source_url,status,redirect_type,destination_url, added, added_utc) VALUES (%s, 'enabled', %d, %s, Now(), UTC_TIMESTAMP())", $before, $redirect_type, $after ) );
				} else {
					$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}azrcrv_redirects SET source_url = %s, redirect_type = %d, destination_url = %s WHERE id = %d", $before, $redirect_type, $after, $id ) );
				}

				if ( $id == 0 ) {
					$message = 'redirect-added';
				} else {
					$message = 'redirect-edited';
				}
			}
		}
		wp_safe_redirect( add_query_arg( 'page', 'azrcrv-r-mr&' . $message . $page, admin_url( 'admin.php' ) ) );
		exit;
	}
}

/**
 * Redirect incoming url.
 *
 * @since 1.0.0
 */
function redirect_incoming() {

	global $wpdb;

	$options = get_option_with_defaults( 'azrcrv-r' );

	$url = trailingslashit( strtok( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), '?' ) );

	$query_string = sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) );

	$redirect = check_for_redirect( $url );

	if ( $redirect ) {
		if ( $redirect->status == 'enabled' ) {

			$wpdb->query( $wpdb->prepare( 'UPDATE {$wpdb->prefix}azrcrv_redirects SET redirect_count = redirect_count + 1, last_redirect = NOW(), last_redirect_utc = UTC_TIMESTAMP() WHERE ID = %d', $redirect->id ) );

			if ( strlen( $query_string ) > 0 ) {
				$query_string = '?' . esc_html( $query_string );
			}

			// redirects may not be internal; future version will allow extra hosts for wp_safe_redirect.
			// phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
			wp_redirect( $redirect->destination_url . $query_string, $redirect->redirect_type );
			exit;

		}
	}

}

/**
 * Identify redirect from url.
 *
 * @since 1.0.0
 */
function check_for_redirect( $url ) {

	global $wpdb;

	$redirect = $wpdb->get_row( $wpdb->prepare( "SELECT id,destination_url,redirect_type,status FROM {$wpdb->prefix}azrcrv_redirects WHERE source_url = %s LIMIT 0,1", esc_url_raw( $url ) ) );

	return $redirect;
}

/**
 * Store previous permalink.
 *
 * @since 1.0.0
 */
function pre_post_update( $post_id, $data ) {

	$options = get_option_with_defaults( 'azrcrv-r' );

	if ( $options['redirect-permalink-changes'] == 1 ) {
		set_transient( 'azrcrv-r-' . $post_id, get_permalink( $post_id ), 60 );
	}

}

/**
 * Add redirect when permalink changes.
 *
 * @since 1.0.0
 */
function add_redirect_for_changed_permalink( $post_id, $post_after, $post_before ) {

	global $wpdb;

	$options = get_option_with_defaults( 'azrcrv-r' );

	if ( $options['redirect-permalink-changes'] == 1 ) {

		$before = trailingslashit( wp_parse_url( get_transient( 'azrcrv-r-' . $post_id ), PHP_URL_PATH ) );
		$after  = trailingslashit( wp_parse_url( get_permalink( $post_id ), PHP_URL_PATH ) );

		if ( $before != $after && $before != '/' ) {

			$redirect = check_for_redirect( $before );

			if ( $redirect ) {

				$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}azrcrv_redirects SET destination_url = %s WHERE id = %d", $after, $redirect->id ) );

			} else {

				$wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}azrcrv_redirects (source_url,status,redirect_type,destination_url, added, added_utc) VALUES (%s, 'enabled', %d, %s, Now(), UTC_TIMESTAMP())", $before, esc_attr( $options['default-redirect'] ), $after ) );

			}
		}
	}

}
