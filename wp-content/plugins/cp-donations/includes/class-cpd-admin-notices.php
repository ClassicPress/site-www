<?php
/**
 * Admin Notices - Handle the addition/display of admin notices.
 */

defined( 'ABSPATH' ) || exit;

class CPD_Admin_Notices {


	public static $meta_box_notices = array();


	public static $admin_notices = array();


	public static $maintenance_notices = array();

	// Array of maintenance notice types
	private static $maintenance_notice_types = array(
		'updating' => 'updating_notice',
	);


	public static function init() {
		self::$maintenance_notices = get_option( 'cpd_maintenance_notices', array() );

		// Show meta box notices.
		add_action( 'admin_notices', array( __CLASS__, 'output_notices' ) );
		// Save meta box notices.
		add_action( 'shutdown', array( __CLASS__, 'save_notices' ) );
		// Show maintenance notices.
		add_action( 'admin_print_styles', array( __CLASS__, 'hook_maintenance_notices' ) );
		// Act upon clicking on a 'dismiss notice' link.
		add_action( 'wp_loaded', array( __CLASS__, 'dismiss_notice_handler' ) );
	}


	// Add a notice/error.
	public static function add_notice( $text, $args, $save_notice = false ) {
		if ( is_array( $args ) ) {
			$type          = $args['type'];
			$dismiss_class = isset( $args['dismiss_class'] ) ? $args['dismiss_class'] : false;
		} else {
			$type          = $args;
			$dismiss_class = false;
		}

		$notice = array(
			'type'          => $type,
			'content'       => $text,
			'dismiss_class' => $dismiss_class,
		);

		if ( $save_notice ) {
			self::$meta_box_notices[] = $notice;
		} else {
			self::$admin_notices[] = $notice;
		}
	}


	// Save errors to an option.
	public static function save_notices() {
		update_option( 'cpd_meta_box_notices', self::$meta_box_notices );
		update_option( 'cpd_maintenance_notices', self::$maintenance_notices );
	}


	// Show any stored error messages.
	public static function output_notices() {
		$saved_notices = maybe_unserialize( get_option( 'cpd_meta_box_notices', array() ) );
		$notices       = $saved_notices + self::$admin_notices;

		if ( ! empty( $notices ) ) {
			foreach ( $notices as $notice ) {
				$dismiss_class = $notice['dismiss_class'] ? $notice['dismiss_class'] . ' is-persistent' : 'is-dismissible';

				echo '<div class="wc-mnm-notice notice-' . esc_attr( $notice['type'] ) . ' notice ' . esc_attr( $dismiss_class ) . '">';

				if ( $notice['dismiss_class'] ) {
					$dismiss_url = wp_nonce_url( add_query_arg( 'dismiss_cpd_notice', $notice['dismiss_class'] ), 'cpd_dismiss_notice_nonce', '_cpd_admin_nonce' );
					echo '<a class="wc-mnm-dismiss-notice notice-dismiss" href="' . esc_url( $dismiss_url ) . '">' . esc_html__( 'Dismiss', 'cp_donations_domain' ) . '</a>';
				}

				echo '<p>' . wp_kses_post( $notice['content'] ) . '</p>';
				echo '</div>';
			}

			// Clear.
			delete_option( 'cpd_meta_box_notices' );
		}
	}


	// Show maintenance notices.
	public static function hook_maintenance_notices() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		foreach ( self::$maintenance_notice_types as $type => $callback ) {
			if ( in_array( $type, self::$maintenance_notices ) ) {
				call_user_func( array( __CLASS__, $callback ) );
			}
		}
	}


	// Add a maintenance notice to be displayed.
	public static function add_maintenance_notice( $notice_name ) {
		self::$maintenance_notices = array_unique( array_merge( self::$maintenance_notices, array( $notice_name ) ) );
	}


	// Remove a maintenance notice.
	public static function remove_maintenance_notice( $notice_name ) {
		self::$maintenance_notices = array_diff( self::$maintenance_notices, array( $notice_name ) );
	}


	// Add 'updating' maintenance notice.
	public static function updating_notice() {
		if ( ! class_exists( 'CPD_Install' ) ) {
			return;
		}

		// Show notice to indicate that an update is in progress.
		if ( CPD_Install::is_update_pending() ) {
			$fallback = '';
			// Do not check within 5 seconds after starting.
			if ( gmdate( 'U' ) - get_option( 'cpd_update_init', 0 ) > 5 ) {
				// Check if the update process is running or not - if not, perhaps it failed to start.
				$fallback_url    = esc_url( wp_nonce_url( add_query_arg( 'force_cpd_db_update', true, admin_url() ), 'cpd_force_db_update_nonce', '_cpd_admin_nonce' ) );
				$fallback_prompt = '<a href="' . $fallback_url . '">' . __( 'run the update process manually', 'cp_donations_domain' ) . '</a>';
				// Translators: %s 'run the update process manually' prompt for updating manually.
				$fallback = '<br/><em>' . sprintf( __( '&hellip;Taking a while? You may need to %s.', 'cp_donations_domain' ), $fallback_prompt ) . '</em>';
				$fallback = CPD_Install::is_update_process_running() ? '' : $fallback;
			}
			$notice = '<strong>' . __( 'CP Donations Data Update', 'cp_donations_domain' ) . '</strong> &#8211; ' . __( 'Your database is being updated in the background.', 'cp_donations_domain' ) . $fallback;
			self::add_notice( $notice, 'info' );

			// Show persistent notice to indicate that the updating process is complete.
		} else {
			$notice = __( 'CP Donations data update complete.', 'cp_donations_domain' );
			self::add_notice( $notice, [ 'type' => 'info', 'dismiss_class' => 'updating' ] );
		}
	}


	// Act upon clicking on a 'dismiss notice' link.
	public static function dismiss_notice_handler() {
		if ( isset( $_GET['dismiss_cpd_notice'] ) && isset( $_GET['_cpd_admin_nonce'] ) ) {
			if ( ! wp_verify_nonce( sanitize_key( $_GET['_cpd_admin_nonce'] ), 'cpd_dismiss_notice_nonce' ) ) {
				wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'cp_donations_domain' ) );
			}

			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				wp_die( esc_html__( 'You do not have perission to dismiss this notice.', 'cp_donations_domain' ) );
			}

			$notice = sanitize_text_field( wp_unslash( $_GET['dismiss_cpd_notice'] ) );
			self::remove_maintenance_notice( $notice );
		}
	}
}

CPD_Admin_Notices::init();
