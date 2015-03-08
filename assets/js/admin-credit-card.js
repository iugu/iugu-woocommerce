/*jshint devel: true */
(function( $ ) {
	'use strict';

	$( function() {
		$( '#woocommerce_iugu-credit-card_pass_interest' ).on( 'change', function() {
			var free_interest = $( '#woocommerce_iugu-credit-card_free_interest' ).closest( 'tr' );

			if ( $( this ).is( ':checked' ) ) {
				free_interest.show();
			} else {
				free_interest.hide();
			}

		}).change();
	});

}( jQuery ));
