<?php
/**
 * Admin View: Notice - Currency not supported.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="error">
	<p><strong><?php _e( 'Iugu Disabled', 'iugu-woocommerce' ); ?></strong>: <?php printf( __( 'Currency <code>%s</code> is not supported. Works only with Brazilian Real.', 'iugu-woocommerce' ), get_woocommerce_currency() ); ?>
	</p>
</div>
