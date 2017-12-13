<?php
/**
 * Iugu My Account actions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Iugu_My_Account {

	/**
	 * Initialize my account actions.
	 */
	public function __construct() {
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '3.0', '<' ) ) {
			add_filter( 'woocommerce_my_account_my_orders_actions', array( $this, 'legacy_my_orders_bank_slip_link' ), 10, 2 );
		} else {
			add_filter( 'woocommerce_my_account_my_orders_actions', array( $this, 'legacy_my_orders_bank_slip_link' ), 10, 2 );
		}
	}

	/**
	 * Legacy - Add bank slip link/button in My Orders section on My Accout page.
	 *
	 * @deprecated 1.1.0
	 */
	public function legacy_my_orders_bank_slip_link( $actions, $order ) {
		if ( 'iugu-bank-slip' !== $order->payment_method ) {
			return $actions;
		}

		if ( ! in_array( $order->get_status(), array( 'pending', 'on-hold' ), true ) ) {
			return $actions;
		}

		$data = get_post_meta( $order->id, '_iugu_wc_transaction_data', true );
		if ( ! empty( $data['pdf'] ) ) {
			$actions[] = array(
				'url'  => $data['pdf'],
				'name' => __( 'Pay the bank slip', 'iugu-woocommerce' ),
			);
		}

		return $actions;
	}

	/**
	 * Add bank slip link/button in My Orders section on My Accout page.
	 */
	public function my_orders_bank_slip_link( $actions, $order ) {
		if ( 'iugu-bank-slip' !== $order->get_payment_method() ) {
			return $actions;
		}

		if ( ! $order->has_status( array( 'pending', 'on-hold' ) ) ) {
			return $actions;
		}

		$data = $order->get_meta( '_iugu_wc_transaction_data' );
		if ( ! empty( $data['pdf'] ) ) {
			$actions[] = array(
				'url'  => $data['pdf'],
				'name' => __( 'Pay the bank slip', 'iugu-woocommerce' ),
			);
		}

		return $actions;
	}
}

new WC_Iugu_My_Account();
