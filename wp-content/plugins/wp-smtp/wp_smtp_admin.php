<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Catch the SMTP settings
if (isset($_POST['wp_smtp_update']) && isset($_POST['wp_smtp_nonce_update'])) {
    if (!wp_verify_nonce(trim($_POST['wp_smtp_nonce_update']), 'my_ws_nonce')) {
        wp_die('Security check not passed!');
    }
    $this->wsOptions = array();
    $this->wsOptions["from"] = sanitize_email( trim( $_POST['wp_smtp_from'] ) );
    $this->wsOptions["fromname"] = sanitize_text_field( trim( $_POST['wp_smtp_fromname'] ) );
    $this->wsOptions["host"] = sanitize_text_field( trim( $_POST['wp_smtp_host'] ) );
    $this->wsOptions["smtpsecure"] = sanitize_text_field( trim( $_POST['wp_smtp_smtpsecure'] ) );
    $this->wsOptions["port"] = is_numeric( trim( $_POST['wp_smtp_port'] ) ) ? trim( $_POST['wp_smtp_port'] ) : '';
    $this->wsOptions["smtpauth"] = sanitize_text_field( trim( $_POST['wp_smtp_smtpauth'] ) );
    $this->wsOptions["username"] = defined( 'WP_SMTP_USER' ) ? WP_SMTP_USER : sanitize_text_field( trim( $_POST['wp_smtp_username'] ) );
    $this->wsOptions["password"] = defined( 'WP_SMTP_PASS' ) ? WP_SMTP_PASS : sanitize_text_field( trim( $_POST['wp_smtp_password'] ) );
    $this->wsOptions["deactivate"] = ( isset($_POST['wp_smtp_deactivate'] ) ) ? sanitize_text_field( trim( $_POST['wp_smtp_deactivate'] ) ) : '';

    update_option("wp_smtp_options", $this->wsOptions);

    if ( ! is_email($this->wsOptions["from"] ) ) {
        echo '<div id="message" class="updated fade"><p><strong>' . __("The field \"From\" must be a valid email address!", "WP-SMTP") . '</strong></p></div>';
    } elseif (empty($this->wsOptions["host"])) {
        echo '<div id="message" class="updated fade"><p><strong>' . __("The field \"SMTP Host\" can not be left blank!", "WP-SMTP") . '</strong></p></div>';
    } else {
        echo '<div id="message" class="updated fade"><p><strong>' . __("Options saved.", "WP-SMTP") . '</strong></p></div>';
    }
}

// Catch the test form
if ( isset( $_POST['wp_smtp_test'] ) && isset( $_POST['wp_smtp_nonce_test'] ) ) {

    if ( ! wp_verify_nonce( trim( $_POST['wp_smtp_nonce_test'] ), 'my_ws_nonce' ) ) {
        wp_die('Security check not passed!');
    }

    $to = sanitize_text_field( trim( $_POST['wp_smtp_to'] ) );
    $subject = sanitize_text_field( trim( $_POST['wp_smtp_subject'] ) );
    $message = sanitize_textarea_field(trim( $_POST['wp_smtp_message'] ) );
    $status = false;
    $class = 'error';

    if ( ! empty( $to ) && is_email( $to ) && ! empty( $subject ) && ! empty( $message ) ) {
        try {
            $result = wp_mail( $to, $subject, $message );
        } catch (Exception $e) {
            $status = $e->getMessage();
        }
    } else {
        $status = __( 'Some of the test fields are empty or an invalid email supplied', 'wp-smtp' );
    }

    if ( ! $status ) {
        if ( $result === true ) {
            $status = __( 'Message sent!', 'wp-smtp' );
            $class = 'success';
        } else {
            $status = $this->phpmailer_error->get_error_message();
        }
    }

    echo '<div id="message" class="notice notice-' . $class . ' is-dismissible"><p><strong>' . $status . '</strong></p></div>';
}

$ws_nonce = wp_create_nonce('my_ws_nonce');
?>
<div class="wrap">

    <h1>
        WP SMTP
        <span style="margin-left:10px; vertical-align:middle;">
        <a href="<?php echo plugins_url('screenshot-1.png', __FILE__); ?>" target="_blank"><img
                src="<?php echo plugins_url('/img/gmail.png', __FILE__); ?>" alt="Gmail" title="Gmail"/></a>
        <a href="<?php echo plugins_url('screenshot-2.png', __FILE__); ?>" target="_blank"><img
                src="<?php echo plugins_url('/img/yahoo.png', __FILE__); ?>" alt="Yahoo!" title="Yahoo!"/></a>
        <a href="<?php echo plugins_url('screenshot-3.png', __FILE__); ?>" target="_blank"><img
                src="<?php echo plugins_url('/img/microsoft.png', __FILE__); ?>" alt="Microsoft" title="Microsoft"/></a>
        <a href="<?php echo plugins_url('screenshot-4.png', __FILE__); ?>" target="_blank"><img
                src="<?php echo plugins_url('/img/163.png', __FILE__); ?>" alt="163" title="163"/></a>
        <a href="<?php echo plugins_url('screenshot-5.png', __FILE__); ?>" target="_blank"><img
                src="<?php echo plugins_url('/img/qq.png', __FILE__); ?>" alt="QQ" title="QQ"/></a>
        </span>
    </h1>

    <form action="" method="post" enctype="multipart/form-data" name="wp_smtp_form">

        <table class="form-table">
            <tr valign="top">
                <th scope="row">
                    <?php _e('From', 'wp-smtp'); ?>
                </th>
                <td>
                    <label>
                        <input type="email" name="wp_smtp_from" value="<?php echo $this->wsOptions["from"]; ?>" size="43"
                               style="width:272px;height:24px;" required/>
                    </label>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <?php _e('From Name', 'wp-smtp'); ?>
                </th>
                <td>
                    <label>
                        <input type="text" name="wp_smtp_fromname" value="<?php echo $this->wsOptions["fromname"]; ?>"
                               size="43" style="width:272px;height:24px;" required />
                    </label>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <?php _e('SMTP Host', 'wp-smtp'); ?>
                </th>
                <td>
                    <label>
                        <input type="text" name="wp_smtp_host" value="<?php echo $this->wsOptions["host"]; ?>" size="43"
                               style="width:272px;height:24px;" required />
                    </label>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <?php _e('SMTP Secure', 'wp-smtp'); ?>
                </th>
                <td>
                    <label>
                        <input name="wp_smtp_smtpsecure" type="radio"
                               value=""<?php if ($this->wsOptions["smtpsecure"] == '') { ?> checked="checked"<?php } ?> />
                        None
                    </label>
                    &nbsp;
                    <label>
                        <input name="wp_smtp_smtpsecure" type="radio"
                               value="ssl"<?php if ($this->wsOptions["smtpsecure"] == 'ssl') { ?> checked="checked"<?php } ?> />
                        SSL
                    </label>
                    &nbsp;
                    <label>
                        <input name="wp_smtp_smtpsecure" type="radio"
                               value="tls"<?php if ($this->wsOptions["smtpsecure"] == 'tls') { ?> checked="checked"<?php } ?> />
                        TLS
                    </label>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <?php _e('SMTP Port', 'wp-smtp'); ?>
                </th>
                <td>
                    <label>
                        <input type="text" name="wp_smtp_port" value="<?php echo $this->wsOptions["port"]; ?>" size="43"
                               style="width:272px;height:24px;"/>
                    </label>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <?php _e('SMTP Authentication', 'wp-smtp'); ?>
                </th>
                <td>
                    <label>
                        <input name="wp_smtp_smtpauth" type="radio"
                               value="no"<?php if ($this->wsOptions["smtpauth"] == 'no') { ?> checked="checked"<?php } ?> />
                        No
                    </label>
                    &nbsp;
                    <label>
                        <input name="wp_smtp_smtpauth" type="radio"
                               value="yes"<?php if ($this->wsOptions["smtpauth"] == 'yes') { ?> checked="checked"<?php } ?> />
                        Yes
                    </label>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <?php _e('Username', 'wp-smtp'); ?>
                </th>
                <td>
                    <label>
                        <input type="text" name="wp_smtp_username" value="<?php echo $this->wsOptions["username"]; ?>"
                               size="43" style="width:272px;height:24px;"/>
                    </label>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <?php _e('Password', 'wp-smtp'); ?>
                </th>
                <td>
                    <label>
                        <input type="password" name="wp_smtp_password" value="<?php echo $this->wsOptions["password"]; ?>"
                               size="43" style="width:272px;height:24px;"/>
                    </label>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <?php _e('Delete Options', 'wp-smtp'); ?>
                </th>
                <td>
                    <label>
                        <input type="checkbox" name="wp_smtp_deactivate"
                               value="yes" <?php if ($this->wsOptions["deactivate"] == 'yes') echo 'checked="checked"'; ?> />
                        <?php _e('Delete options while deactivate this plugin.', 'wp-smtp'); ?>
                    </label>
                </td>
            </tr>
        </table>

        <p class="submit">
            <input type="hidden" name="wp_smtp_update" value="update"/>
            <input type="hidden" name="wp_smtp_nonce_update" value="<?php echo $ws_nonce; ?>"/>
            <input type="submit" class="button-primary" name="Submit" value="<?php _e('Save Changes'); ?>"/>
        </p>

    </form>

    <form action="" method="post" enctype="multipart/form-data" name="wp_smtp_testform">
        <h2><?php _e( 'Test your settings', 'wp-smtp' ); ?></h2>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">
                    <?php _e('To:', 'wp-smtp'); ?>
                </th>
                <td>
                    <label>
                        <input type="email" name="wp_smtp_to" value="" size="43" style="width:272px;height:24px;" required />
                    </label>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <?php _e('Subject:', 'wp-smtp'); ?>
                </th>
                <td>
                    <label>
                        <input type="text" name="wp_smtp_subject" value="" size="43" style="width:272px;height:24px;" required />
                    </label>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <?php _e('Message:', 'wp-smtp'); ?>
                </th>
                <td>
                    <label>
                        <textarea type="text" name="wp_smtp_message" value="" cols="45" rows="3"
                                  style="width:284px;height:62px;" required></textarea>
                    </label>
                </td>
            </tr>
        </table>
        <p class="submit">
            <input type="hidden" name="wp_smtp_test" value="test"/>
            <input type="hidden" name="wp_smtp_nonce_test" value="<?php echo $ws_nonce; ?>"/>
            <input type="submit" class="button-primary" value="<?php _e('Send Test', 'wp-smtp'); ?>"/>
        </p>
    </form>