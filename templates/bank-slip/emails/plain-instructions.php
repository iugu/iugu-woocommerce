<?php
/**
 * Bank Slip - Plain email instructions.
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

_e( 'Use the link below to view your bank slip. You can print and pay it on your internet banking or in a lottery retailer.', 'iugu-woocommerce' );

echo "\n";

echo esc_url( $pdf );

echo "\n";

_e( 'After we receive the bank slip payment confirmation, your order will be processed.', 'iugu-woocommerce' );

echo "\n\n****************************************************\n\n";
