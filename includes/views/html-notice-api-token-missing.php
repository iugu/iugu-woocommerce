<?php
/**
 * Admin View: Notice - API Token missing.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="error">
	<p><strong><?php _e( 'Iugu Disabled', 'iugu-woocommerce' ); ?></strong>: <?php printf( __( 'You should inform your Account ID. %s', 'iugu-woocommerce' ), '<a href="' . esc_attr( WC_Iugu::get_settings_url() ) . '">' . __( 'Click here to configure!', 'iugu-woocommerce' ) . '</a>' ); ?>
	</p>
</div>
