<?php
/**
 * Checkout form.
 *
 * @author  Iugu
 * @package WooCommerce_Iugu/Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<fieldset id="iugu-payment-fields">
	<?php if ( 'all' == $methods ) : ?>
		<p class="form-row form-row-wide">
			<label><input id="iugu-payment-method-credit-cart" type="radio" name="iugu_payment_method" value="credit-card" checked="checked" /> <?php _e( 'Credit Card', 'woocommerce-iugu' ); ?></label>
			<label><input id="iugu-payment-method-banking-ticket" type="radio" name="iugu_payment_method" value="billet" /> <?php _e( 'Billet', 'woocommerce-iugu' ); ?></label>
		</p>
	<?php else : ?>
		<input id="iugu-payment-method-credit-cart" type="hidden" name="iugu_payment_method" value="<?php echo ( 'credit_card' == $methods ) ? 'credit-card' : 'billet'; ?>" />
	<?php endif; ?>

	<?php if ( in_array( $methods, array( 'all', 'credit_card' ) ) ) : ?>
		<div id="iugu-credit-card-fields" class="iugu-method-fields">
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
	<?php endif; ?>

	<?php if ( in_array( $methods, array( 'all', 'billet' ) ) ) : ?>
		<div id="iugu-billet-fields" class="iugu-method-fields">
			<p><?php _e( 'After clicking "Proceed to payment" you will have access to banking ticket which you can print and pay in your internet banking or in a lottery retailer.', 'iugu-woocommerce' ); ?><br /><?php _e( 'Note: The order will be confirmed only after the payment approval.', 'iugu-woocommerce' ); ?></p>
		</div>
	<?php endif; ?>
</fieldset>