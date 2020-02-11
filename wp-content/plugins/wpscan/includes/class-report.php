<?php

class WPScan_Report extends WPScan {

  // Page slug
  static private $page;

  /*
  * Initialize
  */
  static public function init() {

    self::$page = 'wpscan';

    add_action( 'admin_menu', array( __CLASS__, 'menu' ) );

  }

  /*
  * Admin Menu
  */
  static public function menu() {

    add_submenu_page(
      'wpscan',
      __( 'Report', 'wpscan' ),
      __( 'Report', 'wpscan' ),
      self::WPSCAN_ROLE,
      self::$page,
      array( __CLASS__, 'page' )
    );

  }

  /*
  * Report Page
  */
  static public function page() {

    include 'report.php';

  }

  /*
  * List vulnerabilities on screen
  *
  * @param string $type - Type of report: wordpress, plugins, themes
  * @param string $name - key name of the element
  * @return string
  */
  static public function list_vulnerabilities( $type, $name ) {

    $null_text = '- -';

    if ( empty( self::$report ) )
      return null;

    $report = self::$report[ $type ];
    if ( array_key_exists( $name, $report ) ) {
      $report = $report[ $name ];
    }

    if ( ! isset( $report['vulnerabilities'] ) ) {
      echo $null_text;
      return;
    }

    $list = array();

    foreach ( $report['vulnerabilities'] as $item ) {
      $html = '<a href="' . esc_url( 'https://wpvulndb.com/vulnerabilities/' . $item->id ) . '" target="_blank">';
      $html .= esc_html( self::get_vulnerability_title( $item ) );
      $html .= '</a>';
      $list[] = $html;
    }

    echo empty( $list ) ? $null_text : join( '<br>', $list );

  }

  /*
  * Get all vulnerabilities
  *
  * @return array
  */
  static public function get_all_vulnerabilities( ) {

    $ret = array();

    if ( empty( self::$report ) ) {
      return $ret;
    }

    $types = array( 'wordpress', 'plugins', 'themes' );
    foreach ($types as $type) {
      $report = self::$report[ $type ];

      foreach($report as $item) {
        if ( ! isset( $item['vulnerabilities'] ) ) {
          continue;
        }

        foreach ( $item['vulnerabilities'] as $vuln ) {
          $url = 'https://wpvulndb.com/vulnerabilities/' . $vuln->id;
          $title = self::get_vulnerability_title( $vuln );

          $temp = array();
          array_push($temp, $title);
          array_push($temp, $url);

          array_push($ret, $temp);
        }
      }
    }
    return $ret;

  }

  /*
  * Show status icons: checked, attention and error
  *
  * @param string $type - Type of report: wordpress, plugins, themes
  * @param string $name - key name of the element
  * @return string
  */
  static public function get_status( $type, $name ) {

    if ( empty( self::$report ) )
      return null;

    $report = self::$report[ $type ];
    if ( array_key_exists( $name, $report ) ) {
      $report = $report[ $name ];
    }

    if ( ! isset( $report['vulnerabilities'] ) ) {
      return '&nbsp; <span class="dashicons dashicons-no-alt is-gray"></span>';
    } elseif ( empty( $report['vulnerabilities'] ) ) {
      return '&nbsp; <span class="dashicons dashicons-yes is-green"></span>';
    } else {
      return '&nbsp; <span class="dashicons dashicons-warning is-red"></span>';
    }

  }

}
