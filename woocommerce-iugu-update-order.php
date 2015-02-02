<?php

include_once "../../../wp-config.php";

$invoice;
$status;


//try to get data from Iugu notification
try {
	$invoice = $_POST ['data'] ['id'];
	$status = $_POST ['data'] ['status'];
} catch ( Exception $e ) {
}

/**
 * List order notes (public) for the customer
 *
 * @return array
 *
 */
function get_order_notes($id) {
	$notes = array ();
	
	$args = array (
			'post_id' => $id,
			'approve' => 'approve',
			'type' => '' 
	);
	
	remove_filter ( 'comments_clauses', array (
			'WC_Comments',
			'exclude_order_comments' 
	) );
	
	$comments = get_comments ( $args );
	
	foreach ( $comments as $comment ) {
		
		$is_customer_note = get_comment_meta ( $comment->comment_ID, 'is_customer_note', true );
		$comment->comment_content = make_clickable ( $comment->comment_content );
		
		if (! $is_customer_note) {
			$notes [] = $comment;
		}
	}
	
	add_filter ( 'comments_clauses', array (
			'WC_Comments',
			'exclude_order_comments' 
	) );
	
	return ( array ) $notes;
}

/**
 * Process the Iugu notification to inform the payment to woocommerce
 *
 * @return void
 */
function wc_iugu_notification_processor($invoice, $status) {
	
	if ($status == "paid") {
		
		//args to search sold products
		$args = array (
				'post_type' => 'shop_order',
				'post_status' => 'publish' 
		);
		
		$query = new WP_Query ( $args );
		
		$customer_orders = $query->posts;
		
		foreach ( $customer_orders as $customer_order ) {
			
			$order = new WC_Order ();
			
			$order->populate ( $customer_order );
			
			//Get the registered invoice to comparison
			$notes = get_order_notes ( $order->id );
			
			foreach ( $notes as $note_key => $note ) {
				
				//Inform complete the payment process if the invoice match
				if (preg_match ( "/$invoice/", substr ( $note->comment_content, 8 ) ) == 1) {
					echo " $invoice - $note->comment_content - " . $order->id . "<br>";
					$order->update_status ( 'completed', __ ( 'All payment process completed' ) );
				}
			}
		}
	}
}

wc_iugu_notification_processor ( $invoice, $status );