/*jshint devel: true */
(function( $ ) {
	'use strict';

	$( function() {
		$( '#woocommerce_iugu-credit-card_pass_interest' ).on( 'change', function() {
			var fields = $( '#woocommerce_iugu-credit-card_free_interest, #woocommerce_iugu-credit-card_transaction_rate' ).closest( 'tr' );

			if ( $( this ).is( ':checked' ) ) {
				fields.show();
			} else {
				fields.hide();
			}

		}).change();
	});

}( jQuery ));
