jQuery(document).ready(function($) {
	$( 'body' )
	.on( 'updated_checkout', function() {
		
		iugu_form_navbar = $('#iugu-checkout-customform-navbar');
		iugu_form_creditcard = $('#iugu-creditcard-fieldset');
		iugu_form_billet = $('#iugu-billet-fieldset');
		iugu_payment_type = $('#iugu-payment-type');
		
		//iugu_form_billet.hide();
		
	
		iugu_form_navbar.click(function(e) {
			
			e.preventDefault();
	
			if (e.target.id == 'iugu-creditcard-navbutton') {
				iugu_form_creditcard.show();
				iugu_form_billet.hide();
				iugu_payment_type.val('creditcard');
				$('#iugu-creditcard-navbutton').addClass('ui-btn-active');
				$('#iugu-billet-navbutton').removeClass('ui-btn-active');
			}
	
			if (e.target.id == 'iugu-billet-navbutton') {
				iugu_form_billet.show();
				iugu_form_creditcard.hide();
				iugu_payment_type.val('billet');
				$('#iugu-billet-navbutton').addClass('ui-btn-active');
				$('#iugu-creditcard-navbutton').removeClass('ui-btn-active');
			}
		});
	});
		
} );