<?php
/**
 * Credit Card - Plain email instructions.
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

echo sprintf( __( 'Payment successfully made using credit card in %s.', 'iugu-woocommerce' ), $installments . 'x' );

echo "\n\n****************************************************\n\n";
