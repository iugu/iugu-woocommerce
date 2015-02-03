<?php

//function to help insert a field in desired position
function array_insert(&$array, $position, $insert) {
	if (is_int ( $position )) {
		array_splice ( $array, $position, 0, $insert );
	} else {
		$pos = array_search ( $position, array_keys ( $array ) );
		$array = array_merge ( array_slice ( $array, 0, $pos ), $insert, array_slice ( $array, $pos ) );
	}
}

// Hooked in function to add fields in checkout form
function custom_override_checkout_fields($fields) {

	$billing = $fields ['billing'];
	$shipping = $fields ['shipping'];

	$field = array (
			"billing_number" => array (
					'type' => 'text',
					'label' => __ ( 'Realty number', 'iugu-woocommerce' ),
					'placeholder' => _x ( '700', 'placeholder', 'iugu-woocommerce' ),
					'required' => true
			)
	);

	array_insert ( $billing, "billing_city", $field );

	$field = array (
			"shipping_number" => array (
					'type' => 'text',
					'label' => __ ( 'Realty number', 'iugu-woocommerce' ),
					'placeholder' => _x ( '700', 'placeholder', 'iugu-woocommerce' ),
					'required' => true
			)
	);

	array_insert ( $shipping, "shipping_city", $field );

	$field = array (
			"billing_phone_prefix" => array (
					'type' => 'text',
					'label' => __ ( 'DDD', 'iugu-woocommerce' ),
					'placeholder' => _x ( '41', 'placeholder', 'iugu-woocommerce' ),
					'class' => array ( 'form-row-first'),
					'required' => true
			)
	);

	array_insert ( $billing, "billing_phone", $field );

	$billing ['billing_phone'] ['class'] = array_merge ( $billing ['billing_phone'] ['class'], array (
			'form-row-last'
	) );
	$billing ['billing_email'] ['class'] = array ();

	$fields ['billing'] = $billing;
	$fields ['shipping'] = $shipping;

	return $fields;
}

add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );
