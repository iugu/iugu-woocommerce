<?php
/**
 * Credit Card - Checkout form.
 *
 * @author  Iugu
 * @package Iugu_WooCommerce/Templates
 * @version 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<fieldset id="iugu-credit-card-fields">
	<p class="form-row form-row-first">
		<label for="iugu-card-number"><?php _e( 'Card Number', 'iugu-woocommerce' ); ?> <span class="required">*</span></label>
		<input id="iugu-card-number" class="input-text wc-credit-card-form-card-number" type="text" maxlength="20" autocomplete="off" placeholder="&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;" style="font-size: 1.5em; padding: 8px;" data-iugu="number" />
	</p>
	<p class="form-row form-row-last">
		<label for="iugu-card-holder-name"><?php _e( 'Name Printed on the Card', 'iugu-woocommerce' ); ?> <span class="required">*</span></label>
		<input id="iugu-card-holder-name" name="iugu_card_holder_name" class="input-text" type="text" autocomplete="off" style="font-size: 1.5em; padding: 8px;" data-iugu="full_name" />
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
				<?php for ( $i = 1; $i <= $installments; $i++ ) :
					$total_to_pay      = $order_total;
					$installment_total = $total_to_pay / $i;
					$interest_text     = __( 'free interest', 'iugu-woocommerce' );

					// Set the interest rate.
					if ( $i > $free_interest ) {
						$total_rate        = isset( $rates[ $i ] ) ? $rates[ $i ] / 100 : 1 / 100;
						$total_to_pay      = $order_total * ( ( 1 - ( $transaction_rate / 100 ) ) / ( 1 - $total_rate ) );
						$installment_total = $total_to_pay / $i;
						$interest_text     = __( 'with interest', 'iugu-woocommerce' );
					}

					// Stop when the installment total is less than the smallest installment configure.
					if ( $installment_total < $smallest_installment ) {
						break;
					}
					?>
					<option value="<?php echo $i; ?>"><?php echo esc_attr( sprintf( __( '%dx of %s %s (Total: %s)', 'iugu-woocommerce' ), $i, sanitize_text_field( wc_price( $installment_total ) ), $interest_text, sanitize_text_field( wc_price( $total_to_pay ) ) ) ); ?></option>
				<?php endfor; ?>
			</select>
		</p>
	<?php endif; ?>
	<div class="clear"></div>
</fieldset>
