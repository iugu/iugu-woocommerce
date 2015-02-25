<?php
/**
 * Bank Slip - HTML email instructions.
 *
 * @author  Iugu
 * @package Iugu_WooCommerce/Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<h2><?php _e( 'Payment', 'iugu-woocommerce' ); ?></h2>

<p class="order_details"><?php _e( 'Please use the link below to view your bank slip, you can print and pay in your internet banking or in a lottery retailer:', 'iugu-woocommerce' ); ?><br /><a class="button" href="<?php echo esc_url( $pdf ); ?>" target="_blank"><?php _e( 'Pay the bank slip', 'iugu-woocommerce' ); ?></a><br /><?php _e( 'After we receive the bank slip payment confirmation, your order will be processed.', 'iugu-woocommerce' ); ?></p>
