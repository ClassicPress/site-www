<?php

class WPScan_Settings extends WPScan {

  // Page slug
  static private $page;

  /*
  * Initialize
  */
  static public function init() {

    self::$page = 'wpscan_settings';

    add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
    add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );
    add_action( 'admin_notices', array( __CLASS__, 'got_api_token' ) );
    add_action( 'add_option_' . self::OPT_API_TOKEN, array( __CLASS__, 'can_check_now' ) );
    add_action( 'update_option_' . self::OPT_API_TOKEN, array( __CLASS__, 'can_check_now' ) );

  }

  /*
  * Settings Options
  */
  static public function admin_init() {

    register_setting( self::$page, self::OPT_API_TOKEN, array( __CLASS__, 'sanitize_api_token') );

    $section = self::$page . '_section';

    add_settings_section(
      $section,
      null,
      array( __CLASS__, 'introduction' ),
      self::$page
    );

    add_settings_field(
      self::OPT_API_TOKEN,
      __( 'WPVulnDB API Token', 'wpscan' ),
      array( __CLASS__, 'field_api_token' ),
      self::$page,
      $section
    );

  }

  /*
  * Check if API Token is set
  */
  static public function api_token_set() {

    $api_token = get_option( self::OPT_API_TOKEN );
    if ( empty( $api_token ) ) {
      return false;
    }
    return true;

  }

  /*
  * Warn if no API Token is set
  */
  static public function got_api_token() {

    $screen = get_current_screen();

    if ( ! self::api_token_set() && ! strstr( $screen->id, self::$page ) ) {

      printf(
        '<div class="%s"><p>%s <a href="%s">%s</a></p></div>',
        'notice notice-error',
        __( 'To use WPScan you have to setup your WPVulnDB API Token.', 'wpscan' ),
        admin_url( 'admin.php?page=' . self::$page ),
        __( 'Settings', 'wpscan' )
      );

    }

  }

  /*
  * Add Submenu
  */
  static public function menu() {

    add_submenu_page(
      'wpscan',
      __( 'Settings', 'wpscan' ),
      __( 'Settings', 'wpscan' ),
      self::WPSCAN_ROLE,
      self::$page,
      array( __CLASS__, 'page' )
    );

  }

  /*
  * Page
  */
  static public function page() {

    echo '<div class="wrap">';
    echo '<h1><img src="' . self::$plugin_url . 'assets/logo.svg" alt="WPScan"></h1>';
    echo '<h2>' . __( 'Settings', 'wpscan' ) . '</h2>';
    settings_errors();
    echo '<form action="options.php" method="post">';
    settings_fields( self::$page );
    do_settings_sections( self::$page );
    submit_button();
    echo '</form>';
    echo '</div>';

  }

  /*
  * Introduction
  */
  static public function introduction() { }

  /*
  * Field API Token
  */
  static public function field_api_token() {
    $api_token = esc_attr( get_option( self::OPT_API_TOKEN ) );

    echo sprintf(
      '<input type="text" name="%s" value="%s" class="regular-text">',
      self::OPT_API_TOKEN,
      $api_token
    );
    echo '<p class="description">';
    echo sprintf(
      __( '%s to get your free API Token.', 'wpscan' ),
      '<a href="' . WPSCAN_SIGN_UP_URL . '" target="_blank">' . __( 'Sign up', 'wpscan' ) . '</a>'
      );
    echo '</p>';

  }

  /*
  * Sanitize API Token
  */
  static public function sanitize_api_token( $value ) {

    $value = trim($value);
    $result = self::api_get( '/status', $value );

    if( $result ===  401 || $result ===  403 ) {

      add_settings_error(
        self::$page,
        'api_token',
        __( 'You have entered an invalid API Token.', 'wpscan' )
      );

      return false;

    } else {

      // enable cron job if it's a valid API Token
      wp_clear_scheduled_hook( self::WPSCAN_SCHEDULE ); // Prevent duplication
      wp_schedule_event( time(), 'daily', self::WPSCAN_SCHEDULE );

    }

    return $value;

  }

  /*
  * Check if there is an API Token to check now for vulnerabilities
  */
  static public function can_check_now() {

    $api_token = get_option( self::OPT_API_TOKEN );
    if ( ! empty( $api_token ) ) {
      self::check_now();
    }
  }

}
