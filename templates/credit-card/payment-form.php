<?php
/**
 * Credit Card - Checkout form.
 *
 * @author  Iugu
 * @package Iugu_WooCommerce/Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<fieldset id="iugu-credit-card-fields">
	<p class="form-row form-row-first">
		<label for="iugu-card-holder-name"><?php _e( 'Card Holder Name', 'iugu-woocommerce' ); ?> <small>(<?php _e( 'as recorded on the card', 'iugu-woocommerce' ); ?>)</small> <span class="required">*</span></label>
		<input id="iugu-card-holder-name" name="iugu_card_holder_name" class="input-text" type="text" autocomplete="off" style="font-size: 1.5em; padding: 8px;" data-iugu="full_name" />
	</p>
	<p class="form-row form-row-last">
		<label for="iugu-card-number"><?php _e( 'Card Number', 'iugu-woocommerce' ); ?> <span class="required">*</span></label>
		<input id="iugu-card-number" class="input-text wc-credit-card-form-card-number" type="text" maxlength="20" autocomplete="off" placeholder="&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;" style="font-size: 1.5em; padding: 8px;" data-iugu="number" />
	</p>
	<div class="clear"></div>
	<p class="form-row form-row-first">
		<label for="iugu-card-expiry"><?php _e( 'Expiry (MM/YYYY)', 'iugu-woocommerce' ); ?> <span class="required">*</span></label>
		<input id="iugu-card-expiry" class="input-text wc-credit-card-form-card-expiry" type="text" autocomplete="off" placeholder="<?php _e( 'MM / YYYY', 'iugu-woocommerce' ); ?>" style="font-size: 1.5em; padding: 8px;" data-iugu="expiration" />
	</p>
	<p class="form-row form-row-last">
		<label for="iugu-card-cvc"><?php _e( 'Security Code', 'iugu-woocommerce' ); ?> <span class="required">*</span></label>
		<input id="iugu-card-cvc" class="input-text wc-credit-card-form-card-cvc" type="text" autocomplete="off" placeholder="<?php _e( 'CVC', 'iugu-woocommerce' ); ?>" style="font-size: 1.5em; padding: 8px;" data-iugu="verification_value" />
	</p>
	<?php if ( 1 < $installments ) : ?>
		<p class="form-row form-row-wide">
			<label for="iugu-card-installments"><?php _e( 'Installments', 'iugu-woocommerce' ); ?> <span class="required">*</span></label>
			<select id="iugu-card-installments" name="iugu_card_installments" style="font-size: 1.5em; padding: 4px; width: 100%;">
				<?php for ( $i = 1; $i <= $installments; $i++ ) : ?>
					<option value="<?php echo $i; ?>"><?php echo esc_attr( sprintf( __( '%d x', 'iugu-woocommerce' ), $i ) ); ?></option>
				<?php endfor; ?>
			</select>
		</p>
	<?php endif; ?>
	<div class="clear"></div>
</fieldset>
