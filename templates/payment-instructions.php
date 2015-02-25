<?php
/**
 * Payment instructions.
 *
 * @author  Iugu
 * @package Iugu_WooCommerce/Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<?php if ( 'billet' == $payment_method ) : ?>

	<div class="woocommerce-message">
		<span><a class="button" href="<?php echo esc_url( $pdf ); ?>" target="_blank"><?php _e( 'Pay the banking billet', 'iugu-woocommerce' ); ?></a><?php _e( 'Please click in the following button to view your banking billet.', 'iugu-woocommerce' ); ?><br /><?php _e( 'You can print and pay in your internet banking or in a lottery retailer.', 'iugu-woocommerce' ); ?><br /><?php _e( 'After we receive the billet payment confirmation, your order will be processed.', 'iugu-woocommerce' ); ?></span>
	</div>

<?php else : ?>

	<div class="woocommerce-message">
		<span><?php echo sprintf( __( 'You just made the payment in %s by credit card.', 'iugu-woocommerce' ), '<strong>' . $installments . 'x</strong>' ); ?><br /><?php _e( 'As soon as the credit card operator confirm the payment, your order will be processed.', 'iugu-woocommerce' ); ?></span>
	</div>

<?php
endif;
