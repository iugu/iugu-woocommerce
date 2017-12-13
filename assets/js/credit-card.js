/* global iugu_wc_credit_card_params, Iugu */
/*jshint devel: true */
(function( $ ) {
	'use strict';

	$( function() {

		var iugu_submit = false;

		/**
		 * Process the credit card data when submit the checkout form.
		 */
		Iugu.setAccountID( iugu_wc_credit_card_params.account_id );

		if ( 'yes' === iugu_wc_credit_card_params.is_sandbox ) {
			Iugu.setTestMode( true );
		}

		$( 'form.checkout' ).on( 'checkout_place_order_iugu-credit-card', function() {
			return formHandler( this );
		});

		$( 'form#order_review' ).submit( function() {
			return formHandler( this );
		});

		$( 'body' ).on( 'checkout_error', function () {
			$( '.iugu-token' ).remove();
		});
		$( 'form.checkout, form#order_review' ).on( 'change', '#iugu-credit-card-fields input', function() {
			$( '.iugu-token' ).remove();
		});

		/**
		 * Form Handler.
		 *
		 * @param  {object} form
		 *
		 * @return {bool}
		 */
		function formHandler( form ) {
			if ( iugu_submit ) {
				iugu_submit = false;

				return true;
			}

			if ( ! $( '#payment_method_iugu-credit-card' ).is( ':checked' ) ) {
				return true;
			}

			var $form          = $( form ),
				cardExpiry     = $form.find( '#iugu-card-expiry' ).val().replace( ' ', '' ),
				creditCardForm = $( '#iugu-credit-card-fields', $form ),
				errorHtml      = '';

			// Fixed card expiry for iugu.
			$form.find( '#iugu-card-expiry' ).val( cardExpiry );

			Iugu.createPaymentToken( form, function( data ) {
				if ( data.errors ) {

					$( '.woocommerce-error', creditCardForm ).remove();

					errorHtml += '<ul>';
					$.each( data.errors, function ( key, value ) {
						var errorMessage = value;

						if ( 'is_invalid' === errorMessage ) {
							errorMessage = iugu_wc_credit_card_params.i18n_is_invalid;
						}

						errorHtml += '<li>' + iugu_wc_credit_card_params[ 'i18n_' + key + '_field' ] + ' ' + errorMessage + '.</li>';
					});
					errorHtml += '</ul>';

					creditCardForm.prepend( '<div class="woocommerce-error">' + errorHtml + '</div>' );
				} else {
					// Remove any old hash input.
					$( '.iugu-token', $form ).remove();

					// Add the hash input.
					$form.append( $( '<input class="iugu-token" name="iugu_token" type="hidden" />' ).val( data.id ) );

					// Submit the form.
					iugu_submit = true;
					$form.submit();
				}
			});

			return false;
		}
	});

}( jQuery ));
