<?php
/**
 * Classic Commerce Email Settings
 *
 * @package ClassicCommerce/Admin
 * @version WC-2.1.0
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'WC_Settings_Emails', false ) ) {
	return new WC_Settings_Emails();
}

/**
 * WC_Settings_Emails.
 */
class WC_Settings_Emails extends WC_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'email';
		$this->label = __( 'Emails', 'classic-commerce' );

		add_action( 'woocommerce_admin_field_email_notification', array( $this, 'email_notification_setting' ) );
		parent::__construct();
	}

	/**
	 * Get sections.
	 *
	 * @return array
	 */
	public function get_sections() {
		$sections = array(
			'' => __( 'Email options', 'classic-commerce' ),
		);
		return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {
		$settings = apply_filters(
			'woocommerce_email_settings', array(

				array(
					'title' => __( 'Email notifications', 'classic-commerce' ),
					'desc'  => __( 'Email notifications sent from Classic Commerce are listed below. Click on an email to configure it.', 'classic-commerce' ),
					'type'  => 'title',
					'id'    => 'email_notification_settings',
				),

				array( 'type' => 'email_notification' ),

				array(
					'type' => 'sectionend',
					'id'   => 'email_notification_settings',
				),

				array(
					'type' => 'sectionend',
					'id'   => 'email_recipient_options',
				),

				array(
					'title' => __( 'Email sender options', 'classic-commerce' ),
					'type'  => 'title',
					'desc'  => '',
					'id'    => 'email_options',
				),

				array(
					'title'    => __( '"From" name', 'classic-commerce' ),
					'desc'     => __( 'How the sender name appears in outgoing Classic Commerce emails.', 'classic-commerce' ),
					'id'       => 'woocommerce_email_from_name',
					'type'     => 'text',
					'css'      => 'min-width:300px;',
					'default'  => esc_attr( get_bloginfo( 'name', 'display' ) ),
					'autoload' => false,
					'desc_tip' => true,
				),

				array(
					'title'             => __( '"From" address', 'classic-commerce' ),
					'desc'              => __( 'How the sender email appears in outgoing Classic Commerce emails.', 'classic-commerce' ),
					'id'                => 'woocommerce_email_from_address',
					'type'              => 'email',
					'custom_attributes' => array(
						'multiple' => 'multiple',
					),
					'css'               => 'min-width:300px;',
					'default'           => get_option( 'admin_email' ),
					'autoload'          => false,
					'desc_tip'          => true,
				),

				array(
					'type' => 'sectionend',
					'id'   => 'email_options',
				),

				array(
					'title' => __( 'Email template', 'classic-commerce' ),
					'type'  => 'title',
					/* translators: %s: Nonced email preview link */
					'desc'  => sprintf( __( 'This section lets you customize the Classic Commerce emails. <a href="%s" target="_blank">Click here to preview your email template</a>.', 'classic-commerce' ), wp_nonce_url( admin_url( '?preview_woocommerce_mail=true' ), 'preview-mail' ) ),
					'id'    => 'email_template_options',
				),

				array(
					'title'       => __( 'Header image', 'classic-commerce' ),
					'desc'        => __( 'URL to an image you want to show in the email header. Upload images using the media uploader (Admin > Media).', 'classic-commerce' ),
					'id'          => 'woocommerce_email_header_image',
					'type'        => 'text',
					'css'         => 'min-width:300px;',
					'placeholder' => __( 'N/A', 'classic-commerce' ),
					'default'     => '',
					'autoload'    => false,
					'desc_tip'    => true,
				),

				array(
					'title'       => __( 'Footer text', 'classic-commerce' ),
					/* translators: %s: Available placeholders for use */
					'desc'        => __( 'The text to appear in the footer of Classic Commerce emails.', 'classic-commerce' ) . ' ' . sprintf( __( 'Available placeholders: %s', 'classic-commerce' ), '{site_title}' ),
					'id'          => 'woocommerce_email_footer_text',
					'css'         => 'width:300px; height: 75px;',
					'placeholder' => __( 'N/A', 'classic-commerce' ),
					'type'        => 'textarea',
					'default'     => '{site_title}<br/>Powered by <a href="https://github.com/ClassicPress-plugins/classic-commerce/">Classic Commerce</a>',
					'autoload'    => false,
					'desc_tip'    => true,
				),

				array(
					'title'    => __( 'Base color', 'classic-commerce' ),
					/* translators: %s: default color */
					'desc'     => sprintf( __( 'The base color for Classic Commerce email templates. Default %s.', 'classic-commerce' ), '<code>#96588a</code>' ),
					'id'       => 'woocommerce_email_base_color',
					'type'     => 'color',
					'css'      => 'width:6em;',
					'default'  => '#96588a',
					'autoload' => false,
					'desc_tip' => true,
				),

				array(
					'title'    => __( 'Background color', 'classic-commerce' ),
					/* translators: %s: default color */
					'desc'     => sprintf( __( 'The background color for Classic Commerce email templates. Default %s.', 'classic-commerce' ), '<code>#f7f7f7</code>' ),
					'id'       => 'woocommerce_email_background_color',
					'type'     => 'color',
					'css'      => 'width:6em;',
					'default'  => '#f7f7f7',
					'autoload' => false,
					'desc_tip' => true,
				),

				array(
					'title'    => __( 'Body background color', 'classic-commerce' ),
					/* translators: %s: default color */
					'desc'     => sprintf( __( 'The main body background color. Default %s.', 'classic-commerce' ), '<code>#ffffff</code>' ),
					'id'       => 'woocommerce_email_body_background_color',
					'type'     => 'color',
					'css'      => 'width:6em;',
					'default'  => '#ffffff',
					'autoload' => false,
					'desc_tip' => true,
				),

				array(
					'title'    => __( 'Body text color', 'classic-commerce' ),
					/* translators: %s: default color */
					'desc'     => sprintf( __( 'The main body text color. Default %s.', 'classic-commerce' ), '<code>#3c3c3c</code>' ),
					'id'       => 'woocommerce_email_text_color',
					'type'     => 'color',
					'css'      => 'width:6em;',
					'default'  => '#3c3c3c',
					'autoload' => false,
					'desc_tip' => true,
				),

				array(
					'type' => 'sectionend',
					'id'   => 'email_template_options',
				),

			)
		);

		return apply_filters( 'woocommerce_get_settings_' . $this->id, $settings );
	}

	/**
	 * Output the settings.
	 */
	public function output() {
		global $current_section;

		// Define emails that can be customised here.
		$mailer          = WC()->mailer();
		$email_templates = $mailer->get_emails();

		if ( $current_section ) {
			foreach ( $email_templates as $email_key => $email ) {
				if ( strtolower( $email_key ) === $current_section ) {
					$email->admin_options();
					break;
				}
			}
		} else {
			$settings = $this->get_settings();
			WC_Admin_Settings::output_fields( $settings );
		}
	}

	/**
	 * Save settings.
	 */
	public function save() {
		global $current_section;

		if ( ! $current_section ) {
			WC_Admin_Settings::save_fields( $this->get_settings() );

		} else {
			$wc_emails = WC_Emails::instance();

			if ( in_array( $current_section, array_map( 'sanitize_title', array_keys( $wc_emails->get_emails() ) ), true ) ) {
				foreach ( $wc_emails->get_emails() as $email_id => $email ) {
					if ( sanitize_title( $email_id ) === $current_section ) {
						do_action( 'woocommerce_update_options_' . $this->id . '_' . $email->id );
					}
				}
			} else {
				do_action( 'woocommerce_update_options_' . $this->id . '_' . $current_section );
			}
		}
	}

	/**
	 * Output email notification settings.
	 */
	public function email_notification_setting() {
		// Define emails that can be customised here.
		$mailer          = WC()->mailer();
		$email_templates = $mailer->get_emails();

		?>
		<tr valign="top">
		<td class="wc_emails_wrapper" colspan="2">
			<table class="wc_emails widefat" cellspacing="0">
				<thead>
					<tr>
						<?php
						$columns = apply_filters(
							'woocommerce_email_setting_columns', array(
								'status'     => '',
								'name'       => __( 'Email', 'classic-commerce' ),
								'email_type' => __( 'Content type', 'classic-commerce' ),
								'recipient'  => __( 'Recipient(s)', 'classic-commerce' ),
								'actions'    => '',
							)
						);
						foreach ( $columns as $key => $column ) {
							echo '<th class="wc-email-settings-table-' . esc_attr( $key ) . '">' . esc_html( $column ) . '</th>';
						}
						?>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ( $email_templates as $email_key => $email ) {
							echo '<tr>';

							foreach ( $columns as $key => $column ) {

								switch ( $key ) {
									case 'name':
										echo '<td class="wc-email-settings-table-' . esc_attr( $key ) . '">
										<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=email&section=' . strtolower( $email_key ) ) ) . '">' . esc_html( $email->get_title() ) . '</a>
										' . wc_help_tip( $email->get_description() ) . '
									</td>';
										break;
									case 'recipient':
										echo '<td class="wc-email-settings-table-' . esc_attr( $key ) . '">
										' . esc_html( $email->is_customer_email() ? __( 'Customer', 'classic-commerce' ) : $email->get_recipient() ) . '
									</td>';
										break;
									case 'status':
										echo '<td class="wc-email-settings-table-' . esc_attr( $key ) . '">';

										if ( $email->is_manual() ) {
											echo '<span class="status-manual tips" data-tip="' . esc_attr__( 'Manually sent', 'classic-commerce' ) . '">' . esc_html__( 'Manual', 'classic-commerce' ) . '</span>';
										} elseif ( $email->is_enabled() ) {
											echo '<span class="status-enabled tips" data-tip="' . esc_attr__( 'Enabled', 'classic-commerce' ) . '">' . esc_html__( 'Yes', 'classic-commerce' ) . '</span>';
										} else {
											echo '<span class="status-disabled tips" data-tip="' . esc_attr__( 'Disabled', 'classic-commerce' ) . '">-</span>';
										}

										echo '</td>';
										break;
									case 'email_type':
										echo '<td class="wc-email-settings-table-' . esc_attr( $key ) . '">
										' . esc_html( $email->get_content_type() ) . '
									</td>';
										break;
									case 'actions':
										echo '<td class="wc-email-settings-table-' . esc_attr( $key ) . '">
										<a class="button alignright" href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=email&section=' . strtolower( $email_key ) ) ) . '">' . esc_html__( 'Manage', 'classic-commerce' ) . '</a>
									</td>';
										break;
									default:
										do_action( 'woocommerce_email_setting_column_' . $key, $email );
										break;
								}
							}

							echo '</tr>';
						}
						?>
					</tbody>
				</table>
			</td>
		</tr>
		<?php
	}
}

return new WC_Settings_Emails();
