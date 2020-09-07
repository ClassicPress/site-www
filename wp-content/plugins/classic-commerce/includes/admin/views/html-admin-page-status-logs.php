<?php
/**
 * Admin View: Page - Status Logs
 *
 * @package ClassicCommerce/Admin/Logs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<?php if ( $logs ) : ?>
	<div id="log-viewer-select">
		<div class="alignleft">
			<h2>
				<?php echo esc_html( $viewed_log ); ?>
				<?php if ( ! empty( $viewed_log ) ) : ?>
					<a class="page-title-action" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'handle' => sanitize_title( $viewed_log ) ), admin_url( 'admin.php?page=wc-status&tab=logs' ) ), 'remove_log' ) ); ?>" class="button"><?php esc_html_e( 'Delete log', 'classic-commerce' ); ?></a>
				<?php endif; ?>
			</h2>
		</div>
		<div class="alignright">
			<form action="<?php echo esc_url( admin_url( 'admin.php?page=wc-status&tab=logs' ) ); ?>" method="post">
				<select name="log_file">
					<?php foreach ( $logs as $log_key => $log_file ) : ?>
						<?php
							$timestamp = filemtime( WC_LOG_DIR . $log_file );
							/* translators: 1: last access date 2: last access time */
							$date = sprintf( __( '%1$s at %2$s', 'classic-commerce' ), date_i18n( wc_date_format(), $timestamp ), date_i18n( wc_time_format(), $timestamp ) );
						?>
						<option value="<?php echo esc_attr( $log_key ); ?>" <?php selected( sanitize_title( $viewed_log ), $log_key ); ?>><?php echo esc_html( $log_file ); ?> (<?php echo esc_html( $date ); ?>)</option>
					<?php endforeach; ?>
				</select>
				<button type="submit" class="button" value="<?php esc_attr_e( 'View', 'classic-commerce' ); ?>"><?php esc_html_e( 'View', 'classic-commerce' ); ?></button>
			</form>
		</div>
		<div class="clear"></div>
	</div>
	<div id="log-viewer">
		<pre><?php echo esc_html( file_get_contents( WC_LOG_DIR . $viewed_log ) ); ?></pre>
	</div>
<?php else : ?>
	<div class="updated woocommerce-message inline"><p><?php esc_html_e( 'There are currently no logs to view.', 'classic-commerce' ); ?></p></div>
<?php endif; ?>
