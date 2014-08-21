<?php
/**
 * Checkout form.
 *
 * @author  Claudio_Sanches
 * @package WooCommerce_Iugu/Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<fieldset id="iugu-payment-form">
	<input type="hidden" id="iugu-cart-total" value="<?php echo number_format( $cart_total, 2, '.', '' ); ?>" />

	<div id="iugu-credit-card-form" class="iugu-method-form">
		<p class="form-row form-row-first">
			<label for="iugu-card-holder-name"><?php _e( 'Card Holder Name', 'iugu-woocommerce' ); ?> <small>(<?php _e( 'as recorded on the card', 'iugu-woocommerce' ); ?>)</small> <span class="required">*</span></label>
			<input id="iugu-card-holder-name" name="iugu_card_holder_name" class="input-text" type="text" autocomplete="off" style="font-size: 1.5em; padding: 8px;" />
		</p>
		<p class="form-row form-row-last">
			<label for="iugu-card-number"><?php _e( 'Card Number', 'iugu-woocommerce' ); ?> <span class="required">*</span></label>
			<input id="iugu-card-number" class="input-text wc-credit-card-form-card-number" type="text" maxlength="20" autocomplete="off" placeholder="&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;" style="font-size: 1.5em; padding: 8px;" />
		</p>
		<div class="clear"></div>
		<p class="form-row form-row-first">
			<label for="iugu-card-expiry"><?php _e( 'Expiry (MM/YYYY)', 'iugu-woocommerce' ); ?> <span class="required">*</span></label>
			<input id="iugu-card-expiry" class="input-text wc-credit-card-form-card-expiry" type="text" autocomplete="off" placeholder="<?php _e( 'MM / YYYY', 'iugu-woocommerce' ); ?>" style="font-size: 1.5em; padding: 8px;" />
		</p>
		<p class="form-row form-row-last">
			<label for="iugu-card-cvc"><?php _e( 'Security Code', 'iugu-woocommerce' ); ?> <span class="required">*</span></label>
			<input id="iugu-card-cvc" class="input-text wc-credit-card-form-card-cvc" type="text" autocomplete="off" placeholder="<?php _e( 'CVC', 'iugu-woocommerce' ); ?>" style="font-size: 1.5em; padding: 8px;" />
		</p>
		<div class="clear"></div>
	</div>

</fieldset>
