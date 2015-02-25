<?php
/**
 * Credit Card - HTML email instructions.
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

<p class="order_details"><?php echo sprintf( __( 'You just made the payment in %s by credit card.', 'iugu-woocommerce' ), '<strong>' . $installments . 'x</strong>' ); ?><br /><?php _e( 'As soon as the credit card operator confirm the payment, your order will be processed.', 'iugu-woocommerce' ); ?></p>
