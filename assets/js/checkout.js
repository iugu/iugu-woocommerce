(function( $ ) {
	'use strict';

	$( function() {

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

	});

}( jQuery ));
