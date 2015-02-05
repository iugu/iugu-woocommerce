/* global iugu_wc_checkout_params, Iugu */
/*jshint devel: true */
(function( $ ) {
	'use strict';

	$( function() {

		var iugu_submit = false;

		/**
		 * Hide and display the credit card form.
		 *
		 * @param {string} method
		 */
		function formSwitch( method ) {
			var creditCardFields = $( '#iugu-credit-card-fields' ),
				billetFields     = $( '#iugu-billet-fields' ),
				selected         = $( '#iugu-select-payment input[value="' + method + '"]' ).closest( 'li' );

			if ( 'credit-card' === method ) {
				creditCardFields.slideDown( 200 );
				billetFields.slideUp( 200 );
			} else {
				creditCardFields.slideUp( 200 );
				billetFields.slideDown( 200 );
			}

			$( '#iugu-select-payment li' ).removeClass( 'active' );
			selected.addClass( 'active' );
		}

		/**
		 * Controls the credit card display.
		 */
		function formDisplay() {
			var method = $( '#iugu-select-payment input[name="iugu_payment_method"]' ).val();

			formSwitch( method );
		}

		formDisplay();

		/**
		 * Display or hide the credit card for when change the payment method.
		 */
		$( 'body' ).on( 'click', 'li.payment_method_iugu input[name="iugu_payment_method"]', function() {
			formSwitch( $( this ).val() );
		});

		/**
		 * Display or hide the credit card for when change the payment gateway.
		 */
		$( 'body' ).on( 'updated_checkout', function() {
			formDisplay();
		});

		/**
		 * Process the credit card data when submit the checkout form.
		 */
		Iugu.setAccountID( iugu_wc_checkout_params.account_id );

		if ( 'yes' === iugu_wc_checkout_params.is_sandbox ) {
			Iugu.setTestMode( true );
		}

		$( 'form.checkout' ).on( 'checkout_place_order_iugu', function() {
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

			if ( ! $( '#payment_method_iugu' ).is( ':checked' ) ) {
				return true;
			}

			if ( 'radio' === $( 'body li.payment_method_iugu input[name="iugu_payment_method"]' ).attr( 'type' ) ) {
				if ( 'credit-card' !== $( 'body li.payment_method_iugu input[name="iugu_payment_method"]:checked' ).val() ) {
					return true;
				}
			} else {
				if ( 'credit-card' !== $( 'body li.payment_method_iugu input[name="iugu_payment_method"]' ).val() ) {
					return true;
				}
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
							errorMessage = iugu_wc_checkout_params.i18n_is_invalid;
						}

						errorHtml += '<li>' + iugu_wc_checkout_params[ 'i18n_' + key + '_field' ] + ' ' + errorMessage + '.</li>';
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
