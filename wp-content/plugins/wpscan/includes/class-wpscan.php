<?php

class WPScan {

  // Constants
  // Settings
  const OPT_API_TOKEN = 'wpscan_api_token';

  // Notifications
  const OPT_EMAIL     = 'wpscan_mail';
  const OPT_INTERVAL  = 'wpscan_interval';
  const OPT_IGNORED   = 'wpscan_ignored';

  // Report
  const OPT_REPORT = 'wpscan_report';

  // Schedule
  const WPSCAN_SCHEDULE = 'wpscan_schedule';

  // Dashboard
  const WPSCAN_DASHBOARD = 'wpscan_dashboard';

  // Script
  const WPSCAN_SCRIPT = 'wpscan_script';

  // Transient
  const WPSCAN_TRANSIENT_CRON = 'wpscan_doing_cron';

  // Actions
  const WPSCAN_ACTION_CHECK = 'wpscan_check_now';

  // required minimal role
  const WPSCAN_ROLE = 'manage_options';

  // Plugin path
  static public $plugin_dir = '';

  // Plugin URI
  static public $plugin_url = '';

  // Page
  static public $page_hook = 'toplevel_page_wpscan';

  // Report shortcut
  static public $report = array();

  /*
  * Initialize actions
  */
  static public function init() {

    self::$plugin_dir = plugin_dir_path( WPSCAN_PLUGIN_FILE );
    self::$plugin_url = plugin_dir_url( WPSCAN_PLUGIN_FILE );

    // Languages
    load_plugin_textdomain( 'wpscan', false, self::$plugin_dir . 'languages' );

    // Report
    self::$report = get_option( self::OPT_REPORT );

    // Hooks
    add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
    add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue' ) );
    add_action( self::WPSCAN_SCHEDULE, array( __CLASS__, 'schedule' ), 999 );
    add_filter( 'plugin_action_links_' . plugin_basename( WPSCAN_PLUGIN_FILE ), array( __CLASS__, 'add_action_links' ) );

    // Micro apps
    WPScan_Report::init();
    WPScan_Settings::init();
    WPScan_Summary::init();
    WPScan_Notification::init();
    WPScan_Admin_Bar::init();
    WPScan_Dashboard::init();

  }

  /*
  * Plugins Loaded
  */
  static public function loaded() {

    // Languages
    load_plugin_textdomain( 'wpscan', false, self::$plugin_dir . 'languages' );

  }

  /*
  * Activate actions
  */
  static public function activate() { }

  /*
  * Deactivate actions
  */
  static public function deactivate() {

    wp_clear_scheduled_hook( self::WPSCAN_SCHEDULE );

  }

  /*
  *  Register Admin Scripts
  */
  static public function admin_enqueue( $hook ) {

    $screen = get_current_screen();

    // enqueue only on wpscan pages and on dashboard (widgets)
    if ( $hook === self::$page_hook || $screen->id === 'dashboard' ) {
      wp_enqueue_style( 'wpscan', plugins_url( 'assets/style.css', WPSCAN_PLUGIN_FILE ) );
    }

    // only enqueue in wpscan pages
    if ( $hook === self::$page_hook ) {
      wp_register_script( self::WPSCAN_SCRIPT, plugins_url( 'assets/scripts.js', WPSCAN_PLUGIN_FILE ), array( 'jquery' ), false, true );

      $local_array = array(
          'ajaxurl'       => admin_url( 'admin-ajax.php' ),
          'action_check'  => self::WPSCAN_ACTION_CHECK,
          'action_cron'   => self::WPSCAN_TRANSIENT_CRON,
          'ajax_nonce'    => wp_create_nonce( self::WPSCAN_SCRIPT ),
          'doing_cron'    => get_transient( self::WPSCAN_TRANSIENT_CRON ) ? 'YES' : 'NO'
      );

      wp_localize_script( self::WPSCAN_SCRIPT, 'local', $local_array );

      wp_enqueue_script( self::WPSCAN_SCRIPT, plugins_url( 'assets/scripts.js', WPSCAN_PLUGIN_FILE ), array( 'jquery' ) );
    }

  }

  /*
  * Schedule and event to run verify() function
  */
  static public function schedule() {
    
    if ( get_transient( self::WPSCAN_TRANSIENT_CRON ) || empty( get_option( self::OPT_API_TOKEN ) ) )
      return;

    set_transient( self::WPSCAN_TRANSIENT_CRON, time() );
    self::verify();
    delete_transient( self::WPSCAN_TRANSIENT_CRON );

    // Notify by mail when solicited
    WPScan_Notification::notify();

  }

  /*
  * Return the total of vulnerabilities found or -1 if errors
  */
  static public function get_total() {

    $report = self::$report;

    if ( empty( $report ) )
      return 0;

    $total = 0;
    $total += $report['wordpress']['total'];
    $total += $report['plugins']['total'];
    $total += $report['themes']['total'];

    return $total;

  }

  /*
  * Create a menu on Tools section
  */
  static public function menu() {

    $total = self::get_total();
    $count = $total > 0 ? ' <span class="update-plugins">' . $total . '</span>' : null;

    add_menu_page(
      'WPScan',
      'WPScan' . $count,
      self::WPSCAN_ROLE,
      'wpscan',
      array( 'WPScan_Report', 'page' ),
      self::$plugin_url . 'assets/menu-icon.svg',
      null
    );

  }

  /*
  * Include a shortcut on Plugins Page
  *
  * @param array $links - Array of links provided by the filter
  * @return array
  */
  static public function add_action_links( $links ) {

    $links[] = '<a href="' . admin_url( 'admin.php?page=wpscan' ) . '">' . __( 'View' ) . '</a>';

    return $links;

  }

  /*
  * Get the WPScan plugin version.
  */
  static public function wpscan_plugin_version() {

    return get_plugin_data( self::$plugin_dir . 'wpscan.php' )['Version'];

  }

  /*
  * Get information from the API
  * Return the JSON object or the code header.
  */
  static public function api_get( $endpoint, $api_token = null ) {

    if ( empty( $api_token ) )
      $api_token = get_option( self::OPT_API_TOKEN );

    // make sure endpoint starts with a slash
    if ( substr( $endpoint, 0, 1 ) !== "/" ) {
      $endpoint = '/' . $endpoint;
    }

    $args = array(
      'headers' => array(
        'Authorization' => 'Token token=' . $api_token,
        // Keep this lowercase to make older WordPress versions happy
        'user-agent'    => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . home_url() . ' WPScan/' . self::wpscan_plugin_version()
      )
    );

    $response = wp_remote_get( WPSCAN_API_URL . $endpoint, $args );
    $code = wp_remote_retrieve_response_code( $response );

    if ( $code == 200 ) {
      $body = wp_remote_retrieve_body( $response );
      return json_decode( $body );
    }

    return $code;

  }

  /*
  * Function to start checking right now
  */
  static public function check_now() {

    if ( get_transient( self::WPSCAN_TRANSIENT_CRON ) )
      return;

    wp_schedule_single_event( time() - 1, self::WPSCAN_SCHEDULE );
    spawn_cron();

  }

  /*
  * Function to verify on WpScan Database for vulnerabilities
  */
  static public function verify() {

    // Suppports during WP Cron
    if ( ! function_exists( 'get_plugins' ) ) {
      require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    $report = array();
    $errors = array();

    // WordPress
    $report['wordpress'] = array();
    $report['wordpress']['total'] = 0;
    $version = get_bloginfo( 'version' );
    $result = self::api_get( '/wordpresses/' . str_replace( '.', '', $version ) );
    if ( is_object( $result ) ) {
      $report['wordpress'][ $version ]['vulnerabilities'] = self::get_vulnerabilities( $result, $version );
      $report['wordpress']['total'] = count( $report['wordpress'][ $version ]['vulnerabilities'] );
    } elseif( $result ===  401 ) {
      array_push( $errors, 401 );
    } elseif( $result ===  403 ) {
      array_push( $errors, 403 );
    }

    // Plugins
    $report['plugins'] = array();
    $report['plugins']['total'] = 0;
    foreach ( get_plugins() as $name => $details ) {
      $name = self::sanitize_plugin_name( $name );
      $result = self::api_get( '/plugins/' . $name );
      if ( is_object( $result ) ) {
        $report['plugins'][ $name ]['vulnerabilities'] = self::get_vulnerabilities( $result, $details['Version'] );
        $report['plugins']['total'] += count( $report['plugins'][ $name ]['vulnerabilities'] );
      } elseif( $result ===  401 ) {
        array_push( $errors, 401 );
      } elseif( $result ===  403 ) {
        array_push( $errors, 403 );
      }
    }

    // Themes
    $report['themes'] = array();
    $report['themes']['total'] = 0;
    foreach ( wp_get_themes() as $name => $details ) {
      $result = self::api_get( '/themes/' . $name );
      if ( is_object( $result ) ) {
        $report['themes'][ $name ]['vulnerabilities'] = self::get_vulnerabilities( $result, $details['Version'] );
        $report['themes']['total'] += count( $report['themes'][ $name ]['vulnerabilities'] );
      } elseif( $result ===  401 ) {
        array_push( $errors, 401 );
      } elseif( $result ===  403 ) {
        array_push( $errors, 403 );
      }
    }

    // Caching
    $report['cache'] = strtotime( current_time( 'mysql' ) );

    // Errors
    $errors = array_unique($errors);
    if ( sizeof( $errors ) > 0 ) {
      $report['error'] = array();
    }
    foreach ( $errors as $err ) {
      // $err should NEVER contain user input. Otherwise you need to change the
      // implementation in class-summary.php to use esc_html() (but this will stop the links below to work)
      switch ($err) {
        case 401:
          array_push( $report['error'], __( 'Your API Token expired', 'wpscan' ) );
          break;
        case 403:
          array_push( $report['error'], sprintf( '%s <a href="%s" target="_blank">%s</a>.', __( 'You hit our free API usage limit. To increase your daily API limit please upgrade to paid usage from your', 'wpscan' ), WPSCAN_PROFILE_URL, __( 'WPVulnDB profile page', 'wpscan' ) ) );
          break;
        default:
          array_push( $report['error'], sprintf( __( 'Error %s occurred on calling API', 'wpscan' ), $err ) );
          break;
      }
    }

    // Saving
    update_option( self::OPT_REPORT, $report, true );
    self::$report = $report;

  }

  /*
  * Filter vulnerability list from WPScan
  *
  * @param array $data - Report data for the element to check
  * @param string $version - Installed version
  * @return string
  */
  static public function get_vulnerabilities( $data, $version ) {
    
    $list = array();
    $key = key( $data );

    if ( empty( $data->$key->vulnerabilities ) ) {
      return $list;
    }

    // Trim and remove potential leading 'v'
    $version = ltrim(trim($version), 'v');

    foreach ( $data->$key->vulnerabilities as $item ) {
      if ( $item->fixed_in ) {
        if ( version_compare( $version, $item->fixed_in, '<' ) ) {
          $list[] = $item;
        }
      } else {
        $list[] = $item;
      }
    }

    return $list;

  }

  /*
  * Get vulnerability title
  *
  * @param string $vulnerability - element array
  * @return string
  */
  static public function get_vulnerability_title( $vulnerability ) {
    $title = esc_html( $vulnerability->title ) . ' - ';
    $title .= empty( $vulnerability->fixed_in ) ? __( 'Not fixed', 'wpscan' ) : sprintf( __( 'Fixed in version %s', 'wpscan' ), $vulnerability->fixed_in );

    return $title;
  }

  /*
  * Sanitize plugin name
  *
  * @param string $name - plugin name "folder/file.php" or "hello.php"
  * @return string
  */
  static public function sanitize_plugin_name( $name ) {

    return strstr( $name, '/' ) ? dirname($name) : $name;

  }

}
