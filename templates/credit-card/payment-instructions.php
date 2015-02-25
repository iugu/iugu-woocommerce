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
	<span><?php echo sprintf( __( 'You just made the payment in %s by credit card.', 'iugu-woocommerce' ), '<strong>' . $installments . 'x</strong>' ); ?><br /><?php _e( 'As soon as the credit card operator confirm the payment, your order will be processed.', 'iugu-woocommerce' ); ?></span>
</div>
