<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Iugu Payment Bank Slip Addons Gateway class.
 *
 * Integration with WooCommerce Subscriptions and Pre-orders.
 *
 * @class   WC_Iugu_Bank_Slip_Addons_Gateway_Deprecated
 * @extends WC_Iugu_Bank_Slip_Gateway
 * @version 1.0.0
 * @author  Iugu
 */
class WC_Iugu_Bank_Slip_Addons_Gateway_Deprecated extends WC_Iugu_Bank_Slip_Gateway {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();

		if ( class_exists( 'WC_Subscriptions_Order' ) ) {
			add_action( 'scheduled_subscription_payment_' . $this->id, array( $this, 'scheduled_subscription_payment' ), 10, 3 );
		}

		if ( class_exists( 'WC_Pre_Orders_Order' ) ) {
			add_action( 'wc_pre_orders_process_pre_order_completion_payment_' . $this->id, array( $this, 'process_pre_order_release_payment' ) );
		}

		add_action( 'woocommerce_api_wc_iugu_bank_slip_addons_gateway', array( $this->api, 'notification_handler' ) );
	}

	/**
	 * Process the subscription.
	 *
	 * @param WC_Order $order
	 *
	 * @return array
	 */
	protected function process_subscription( $order_id ) {
		try {
			$order = new WC_Order( $order_id );

			// Try to do an initial payment.
			$initial_payment = WC_Subscriptions_Order::get_total_initial_payment( $order );
			if ( $initial_payment > 0 ) {
				$payment_response = $this->process_subscription_payment( $order, $initial_payment );
			}
			if ( isset( $payment_response ) && is_wp_error( $payment_response ) ) {
				throw new Exception( $payment_response->get_error_message() );
			} else {
				// Remove cart
				$this->api->empty_card();

				// Return thank you page redirect
				return array(
					'result'   => 'success',
					'redirect' => $this->get_return_url( $order )
				);
			}

		} catch ( Exception $e ) {
			$this->api->add_error( '<strong>' . esc_attr( $this->title ) . '</strong>: ' . $e->getMessage() );

			return array(
				'result'   => 'fail',
				'redirect' => ''
			);
		}
	}

	/**
	 * Process the pre-order.
	 *
	 * @param WC_Order $order
	 *
	 * @return array
	 */
	protected function process_pre_order( $order_id ) {
		if ( WC_Pre_Orders_Order::order_requires_payment_tokenization( $order_id ) ) {
			try {
				$order = new WC_Order( $order_id );

				// Reduce stock levels
				$order->reduce_order_stock();

				// Remove cart
				$this->api->empty_card();

				// Is pre ordered!
				WC_Pre_Orders_Order::mark_order_as_pre_ordered( $order );

				// Return thank you page redirect
				return array(
					'result'   => 'success',
					'redirect' => $this->get_return_url( $order )
				);

			} catch ( Exception $e ) {
				$this->api->add_error( '<strong>' . esc_attr( $this->title ) . '</strong>: ' . $e->getMessage() );

				return array(
					'result'   => 'fail',
					'redirect' => ''
				);
			}

		} else {
			return parent::process_payment( $order_id );
		}
	}

	/**
	 * Process the payment.
	 *
	 * @param  int $order_id
	 *
	 * @return array
	 */
	public function process_payment( $order_id ) {
		// Processing subscription.
		if ( $this->api->order_contains_subscription( $order_id ) ) {
			return $this->process_subscription( $order_id );

		// Processing pre-order.
		} elseif ( $this->api->order_contains_pre_order( $order_id ) ) {
			return $this->process_pre_order( $order_id );

		// Processing regular product.
		} else {
			return parent::process_payment( $order_id );
		}
	}

	/**
	 * process_subscription_payment function.
	 *
	 * @param WC_order $order
	 * @param int      $amount (default: 0)
	 *
	 * @return bool|WP_Error
	 */
	public function process_subscription_payment( $order = '', $amount = 0 ) {
		if ( 'yes' == $this->debug ) {
			$this->log->add( $this->id, 'Processing a subscription payment for order ' . $order->get_order_number() );
		}

		$charge = $this->api->create_charge( $order );

		if ( isset( $charge['errors'] ) && ! empty( $charge['errors'] ) ) {
			$error = is_array( $charge['errors'] ) ? current( $charge['errors'] ) : $charge['errors'];

			return new WP_Error( 'iugu_subscription_error', $error );
		}

		$payment_data = array_map(
			'sanitize_text_field',
			array(
				'pdf' => $charge['pdf']
			)
		);
		update_post_meta( $order->id, '_iugu_wc_transaction_data', $payment_data );
		update_post_meta( $order->id, __( 'Iugu Bank Slip URL', 'iugu-woocommerce' ), $payment_data['pdf'] );
		update_post_meta( $order->id, '_transaction_id', sanitize_text_field( $charge['invoice_id'] ) );

		// Save only in old versions.
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.1.12', '<=' ) ) {
			update_post_meta( $order->id, __( 'Iugu Transaction details', 'iugu-woocommerce' ), 'https://iugu.com/a/invoices/' . sanitize_text_field( $charge['invoice_id'] ) );
		}

		$order_note = __( 'Iugu: The customer generated a bank slip, awaiting payment confirmation.', 'iugu-woocommerce' );
		if ( 'pending' == $order->get_status() ) {
			$order->update_status( 'on-hold', $order_note );
		} else {
			$order->add_order_note( $order_note );
		}

		return true;
	}

	/**
	 * Scheduled subscription payment.
	 *
	 * @param float    $amount_to_charge The amount to charge.
	 * @param WC_Order $order            The WC_Order object of the order which the subscription was purchased in.
	 * @param int      $product_id       The ID of the subscription product for which this payment relates.
	 */
	public function scheduled_subscription_payment( $amount_to_charge, $order, $product_id ) {
		$result = $this->process_subscription_payment( $order, $amount_to_charge );

		if ( is_wp_error( $result ) ) {
			WC_Subscriptions_Manager::process_subscription_payment_failure_on_order( $order, $product_id );
		} else {
			WC_Subscriptions_Manager::process_subscription_payments_on_order( $order );
		}
	}

	/**
	 * Process a pre-order payment when the pre-order is released.
	 *
	 * @param WC_Order $order
	 */
	public function process_pre_order_release_payment( $order ) {
		if ( 'yes' == $this->debug ) {
			$this->log->add( $this->id, 'Processing a pre-order release payment for order ' . $order->get_order_number() );
		}

		try {
			$charge = $this->api->create_charge( $order );

			if ( isset( $charge['errors'] ) && ! empty( $charge['errors'] ) ) {
				$error = is_array( $charge['errors'] ) ? current( $charge['errors'] ) : $charge['errors'];

				return new Exception( $error );
			}

			$payment_data = array_map(
				'sanitize_text_field',
				array(
					'pdf' => $charge['pdf']
				)
			);
			update_post_meta( $order->id, '_iugu_wc_transaction_data', $payment_data );
			update_post_meta( $order->id, __( 'Iugu Bank Slip URL', 'iugu-woocommerce' ), $payment_data['pdf'] );
			update_post_meta( $order->id, '_transaction_id', sanitize_text_field( $charge['invoice_id'] ) );

			// Save only in old versions.
			if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.1.12', '<=' ) ) {
				update_post_meta( $order->id, __( 'Iugu Transaction details', 'iugu-woocommerce' ), 'https://iugu.com/a/invoices/' . sanitize_text_field( $charge['invoice_id'] ) );
			}

			$order->update_status( 'on-hold', __( 'Iugu: The customer generated a bank slip, awaiting payment confirmation.', 'iugu-woocommerce' ) );
		} catch ( Exception $e ) {
			$order_note = sprintf( __( 'Iugu: Pre-order payment failed (%s).', 'iugu-woocommerce' ), $e->getMessage() );

			// Mark order as failed if not already set,
			// otherwise, make sure we add the order note so we can detect when someone fails to check out multiple times
			if ( 'failed' != $order->get_status() ) {
				$order->update_status( 'failed', $order_note );
			} else {
				$order->add_order_note( $order_note );
			}
		}
	}

	/**
	 * Update subscription status.
	 *
	 * @param int    $order_id
	 * @param string $invoice_status
	 *
	 * @return bool
	 */
	protected function update_subscription_status( $order_id, $invoice_status ) {
		$order          = new WC_Order( $order_id );
		$invoice_status = strtolower( $invoice_status );
		$order_updated  = false;

		if ( 'paid' == $invoice_status ) {
			$order->add_order_note( __( 'Iugu: Subscription paid successfully.', 'iugu-woocommerce' ) );

			// Payment complete
			$order->payment_complete();

			$order_updated = true;
		} elseif ( in_array( $invoice_status, array( 'canceled', 'refunded', 'expired' ) ) ) {
			$order->add_order_note( __( 'Iugu: Subscription payment failed.', 'iugu-woocommerce' ) );

			WC_Subscriptions_Manager::process_subscription_payment_failure_on_order( $order );
			$order_updated = true;
		}

		// Allow custom actions when update the order status.
		do_action( 'iugu_woocommerce_update_order_status', $order, $invoice_status, $order_updated );
	}

	/**
	 * Notification handler.
	 */
	public function notification_handler() {
		@ob_clean();

		if ( isset( $_REQUEST['event'] ) && isset( $_REQUEST['data']['id'] ) && 'invoice.status_changed' == $_REQUEST['event'] ) {
			global $wpdb;

			header( 'HTTP/1.1 200 OK' );

			$invoice_id = sanitize_text_field( $_REQUEST['data']['id'] );
			$order_id   = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_transaction_id' AND meta_value = '%s'", $invoice_id ) );
			$order_id   = intval( $order_id );

			if ( $order_id ) {
				$invoice_status = $this->api->get_invoice_status( $invoice_id );

				if ( $invoice_status ) {
					if ( $this->api->order_contains_subscription( $order_id ) ) {
						$this->update_subscription_status( $order_id, $invoice_status );
						exit();
					} else {
						$this->api->update_order_status( $order_id, $invoice_status );
						exit();
					}
				}
			}
		}

		wp_die( __( 'The request failed!', 'iugu-woocommerce' ), __( 'The request failed!', 'iugu-woocommerce' ), array( 'response' => 200 ) );
	}
}
