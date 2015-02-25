(function( $ ) {
	'use strict';

	$( function() {
		$( '#woocommerce_iugu_methods' ).on( 'change', function() {
			var current         = $( this ).val(),
				installments    = $( '#woocommerce_iugu_installments' ).closest( 'tr' ),
				billet_deadline = $( '#woocommerce_iugu_billet_deadline' ).closest( 'tr' );

			if ( 'credit_card' === current ) {
				installments.show();
				billet_deadline.hide();
			} else if ( 'billet' === current ) {
				installments.hide();
				billet_deadline.show();
			} else {
				installments.show();
				billet_deadline.show();
			}

		}).change();
	});

}( jQuery ));
