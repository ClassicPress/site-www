<?php
/**
 * Admin View: Notice - Untested extensions.
 *
 * @package ClassicCommerce\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wc_plugin_upgrade_notice extensions_warning <?php echo esc_attr( $upgrade_type ); ?>">
	<p><?php echo wp_kses_post( $message ); ?></p>

	<table class="plugin-details-table" cellspacing="0">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Plugin', 'classic-commerce' ); ?></th>
				<th><?php esc_html_e( 'Tested up to Classic Commerce version', 'classic-commerce' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $plugins as $plugin ) : ?>
				<tr>
					<td><?php echo esc_html( $plugin['Name'] ); ?></td>
					<td><?php echo esc_html( $plugin['CC tested up to'] ); ?></td>
				</tr>
			<?php endforeach ?>
		</tbody>
	</table>
</div>
