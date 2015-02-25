<?php
/**
 * Plain email instructions.
 *
 * @author  Iugu
 * @package Iugu_WooCommerce/Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

_e( 'Payment', 'iugu-woocommerce' );

echo "\n\n";

if ( 'billet' == $type ) {

	_e( 'Please use the link below to view your banking billet, you can print and pay in your internet banking or in a lottery retailer:', 'iugu-woocommerce' );

	echo "\n";

	echo esc_url( $pdf );

	echo "\n";

	_e( 'After we receive the billet payment confirmation, your order will be processed.', 'iugu-woocommerce' );

} else {

	echo sprintf( __( 'You just made the payment in %s by credit card.', 'iugu-woocommerce' ), $installments . 'x' );

	echo "\n";

	_e( 'As soon as the credit card operator confirm the payment, your order will be processed.', 'iugu-woocommerce' );

}

echo "\n\n****************************************************\n\n";
