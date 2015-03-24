<?php
/**
 * Admin View: Notice - Account ID missing.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="error">
	<p><strong><?php _e( 'Iugu Disabled', 'iugu-woocommerce' ); ?></strong>: <?php printf( __( 'You should inform your API Token. %s', 'iugu-woocommerce' ), '<a href="' . esc_attr( $this->api->get_settings_url() ) . '">' . __( 'Click here to configure!', 'iugu-woocommerce' ) . '</a>' ); ?>
	</p>
</div>
