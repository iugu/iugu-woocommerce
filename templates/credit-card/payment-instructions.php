<?php
/**
 * Credit Card - Payment instructions.
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
	<span><?php echo sprintf( __( 'Payment successfully made using credit card in %s.', 'iugu-woocommerce' ), '<strong>' . $installments . 'x</strong>' ); ?></span>
</div>
