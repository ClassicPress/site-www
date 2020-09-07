<?php
/**
 * Admin View: Importer - CSV import progress
 *
 * @package ClassicCommerce\Admin\Importers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wc-progress-form-content woocommerce-importer woocommerce-importer__importing">
	<header>
		<span class="spinner is-active"></span>
		<h2><?php esc_html_e( 'Importing', 'classic-commerce' ); ?></h2>
		<p><?php esc_html_e( 'Your products are now being imported...', 'classic-commerce' ); ?></p>
	</header>
	<section>
		<progress class="woocommerce-importer-progress" max="100" value="0"></progress>
	</section>
</div>
