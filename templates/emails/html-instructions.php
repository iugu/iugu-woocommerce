<?php
/**
 * HTML email instructions.
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

<?php if ( 'billet' == $payment_method ) : ?>

	<p class="order_details"><?php _e( 'Please use the link below to view your banking billet, you can print and pay in your internet banking or in a lottery retailer:', 'iugu-woocommerce' ); ?><br /><a class="button" href="<?php echo esc_url( $pdf ); ?>" target="_blank"><?php _e( 'Pay the banking billet', 'iugu-woocommerce' ); ?></a><br /><?php _e( 'After we receive the billet payment confirmation, your order will be processed.', 'iugu-woocommerce' ); ?></p>

<?php else : ?>

	<p class="order_details"><?php echo sprintf( __( 'You just made the payment in %s by credit card.', 'iugu-woocommerce' ), '<strong>' . $installments . 'x</strong>' ); ?><br /><?php _e( 'As soon as the credit card operator confirm the payment, your order will be processed.', 'iugu-woocommerce' ); ?></p>

<?php
endif;
