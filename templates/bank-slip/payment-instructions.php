<?php
/**
 * Bank Slip - Payment instructions.
 *
 * @author  Iugu
 * @package Iugu_WooCommerce/Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<div class="woocommerce-message">
	<span><a class="button" href="<?php echo esc_url( $pdf ); ?>" target="_blank"><?php _e( 'Pay the bank slip', 'iugu-woocommerce' ); ?></a><?php _e( 'Please click in the following button to view your bank slip.', 'iugu-woocommerce' ); ?><br /><?php _e( 'You can print and pay in your internet banking or in a lottery retailer.', 'iugu-woocommerce' ); ?><br /><?php _e( 'After we receive the bank slip payment confirmation, your order will be processed.', 'iugu-woocommerce' ); ?></span>
</div>
