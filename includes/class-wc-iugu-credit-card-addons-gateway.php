<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Iugu Payment Credit Card Addons Gateway class.
 *
 * Integration with WooCommerce Subscriptions and Pre-orders.
 *
 * @class   WC_Iugu_Credit_Card_Addons_Gateway
 * @extends WC_Iugu_Credit_Card_Gateway
 */
class WC_Iugu_Credit_Card_Addons_Gateway extends WC_Iugu_Credit_Card_Gateway {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();

		if ( class_exists( 'WC_Subscriptions_Order' ) ) {
			add_action( 'woocommerce_scheduled_subscription_payment_' . $this->id, array( $this, 'scheduled_subscription_payment' ), 10, 2 );
			add_action( 'woocommerce_subscription_failing_payment_method_updated_' . $this->id, array( $this, 'update_failing_payment_method' ), 10, 2 );
			add_action( 'wcs_resubscribe_order_created', array( $this, 'delete_resubscribe_meta' ), 10 );

			// Allow store managers to manually set Simplify as the payment method on a subscription.
			add_filter( 'woocommerce_subscription_payment_meta', array( $this, 'add_subscription_payment_meta' ), 10, 2 );
			add_filter( 'woocommerce_subscription_validate_payment_meta', array( $this, 'validate_subscription_payment_meta' ), 10, 2 );
		}

		if ( class_exists( 'WC_Pre_Orders_Order' ) ) {
			add_action( 'wc_pre_orders_process_pre_order_completion_payment_' . $this->id, array( $this, 'process_pre_order_release_payment' ) );
		}

		add_action( 'woocommerce_api_wc_iugu_credit_card_addons_gateway', array( $this->api, 'notification_handler' ) );
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

			if ( ! isset( $_POST['iugu_token'] ) ) {
				if ( 'yes' == $this->debug ) {
					$this->log->add( $this->id, 'Error doing the subscription for order ' . $order->get_order_number() . ': Missing the "iugu_token".' );
				}

				throw new Exception( __( 'Please make sure your card details have been entered correctly and that your browser supports JavaScript.', 'iugu-woocommerce' ) );
			}

			// Create customer payment method.
			$payment_method_id = $this->api->create_customer_payment_method( $order, $_POST['iugu_token'] );
			if ( ! $payment_method_id ) {
				if ( 'yes' == $this->debug ) {
					$this->log->add( $this->id, 'Invalid customer method ID for order ' . $order->get_order_number() );
				}

				throw new Exception( __( 'An error occurred while trying to save your data. Please contact us for get help.', 'iugu-woocommerce' ) );
			}

			$this->save_subscription_meta( $order->id, $payment_method_id );

			$payment_response = $this->process_subscription_payment( $order, $order->get_total() );

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

				if ( ! isset( $_POST['iugu_token'] ) ) {
					if ( 'yes' == $this->debug ) {
						$this->log->add( $this->id, 'Error doing the pre-order for order ' . $order->get_order_number() . ': Missing the "iugu_token".' );
					}

					$error_msg = __( 'Please make sure your card details have been entered correctly and that your browser supports JavaScript.', 'iugu-woocommerce' );

					throw new Exception( $error_msg );
				}

				// Create customer payment method.
				$payment_method_id = $this->api->create_customer_payment_method( $order, $_POST['iugu_token'] );
				if ( ! $payment_method_id ) {
					if ( 'yes' == $this->debug ) {
						$this->log->add( $this->id, 'Invalid customer method ID for order ' . $order->get_order_number() );
					}

					$error_msg = __( 'An error occurred while trying to save your data. Please contact us for get help.', 'iugu-woocommerce' );

					throw new Exception( $error_msg );
				}

				// Save the payment method ID in order data.
				update_post_meta( $order->id, '_iugu_customer_payment_method_id', $payment_method_id );

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
	 * Store the Iugu customer payment method id on the order and subscriptions in the order.
	 *
	 * @param int $order_id
	 * @param string $payment_method_id
	 */
	protected function save_subscription_meta( $order_id, $payment_method_id ) {
		$payment_method_id = wc_clean( $payment_method_id );
		update_post_meta( $order_id, '_iugu_customer_payment_method_id', $payment_method_id );

		// Also store it on the subscriptions being purchased in the order.
		foreach( wcs_get_subscriptions_for_order( $order_id ) as $subscription ) {
			update_post_meta( $subscription->id, '_iugu_customer_payment_method_id', $payment_method_id );
		}
	}

	/**
	 * Process subscription payment.
	 *
	 * @param WC_order $order
	 * @param int      $amount (default: 0)
	 *
	 * @return bool|WP_Error
	 */
	public function process_subscription_payment( $order = '', $amount = 0 ) {
		if ( 0 == $amount ) {
			// Payment complete.
			$order->payment_complete();

			return true;
		}

		if ( 'yes' == $this->debug ) {
			$this->log->add( $this->id, 'Processing a subscription payment for order ' . $order->get_order_number() );
		}

		$payment_method_id = get_post_meta( $order->id, '_iugu_customer_payment_method_id', true );

		if ( ! $payment_method_id ) {
			$payment_method_id = $this->api->get_customer_payment_method_id();

			if ( ! empty( $payment_method_id ) ) {
				update_post_meta( $order->id, '_iugu_customer_payment_method_id', $payment_method_id );
			}
		}

		if ( ! $payment_method_id ) {
			if ( 'yes' == $this->debug ) {
				$this->log->add( $this->id, 'Missing customer payment method ID in subscription payment for order ' . $order->get_order_number() );
			}

			return new WP_Error( 'iugu_subscription_error', __( 'Customer payment method not found!', 'iugu-woocommerce' ) );
		}

		$charge = $this->api->create_charge( $order, array( 'customer_payment_method_id' => $payment_method_id ) );

		if ( isset( $charge['errors'] ) && ! empty( $charge['errors'] ) ) {
			$error = is_array( $charge['errors'] ) ? current( $charge['errors'] ) : $charge['errors'];

			return new WP_Error( 'iugu_subscription_error', $error );
		}

		update_post_meta( $order->id, '_transaction_id', sanitize_text_field( $charge['invoice_id'] ) );

		// Save only in old versions.
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.1.12', '<=' ) ) {
			update_post_meta( $order->id, __( 'Iugu Transaction details', 'iugu-woocommerce' ), 'https://iugu.com/a/invoices/' . sanitize_text_field( $charge['invoice_id'] ) );
		}

		if ( true == $charge['success'] ) {
			$order->add_order_note( __( 'Iugu: Subscription paid successfully by credit card.', 'iugu-woocommerce' ) );
			$order->payment_complete();

			return true;
		} else {
			return new WP_Error( 'iugu_subscription_error', __( 'Iugu: Subscription payment failed. Credit card declined.', 'iugu-woocommerce' ) );
		}
	}

	/**
	 * Scheduled subscription payment.
	 *
	 * @param float $amount_to_charge The amount to charge.
	 * @param WC_Order $renewal_order A WC_Order object created to record the renewal payment.
	 */
	public function scheduled_subscription_payment( $amount_to_charge, $renewal_order ) {
		$result = $this->process_subscription_payment( $renewal_order, $amount_to_charge );

		if ( is_wp_error( $result ) ) {
			$renewal_order->update_status( 'failed', $result->get_error_message() );
		}
	}

	/**
	 * Update the customer_id for a subscription after using Simplify to complete a payment to make up for.
	 * an automatic renewal payment which previously failed.
	 *
	 * @param WC_Subscription $subscription The subscription for which the failing payment method relates.
	 * @param WC_Order $renewal_order The order which recorded the successful payment (to make up for the failed automatic payment).
	 */
	public function update_failing_payment_method( $subscription, $renewal_order ) {
		update_post_meta( $subscription->id, '_iugu_customer_payment_method_id', get_post_meta( $renewal_order->id, '_iugu_customer_payment_method_id', true ) );
	}

	/**
	 * Include the payment meta data required to process automatic recurring payments so that store managers can.
	 * manually set up automatic recurring payments for a customer via the Edit Subscription screen in Subscriptions v2.0+.
	 *
	 * @param array $payment_meta associative array of meta data required for automatic payments
	 * @param WC_Subscription $subscription An instance of a subscription object
	 * @return array
	 */
	public function add_subscription_payment_meta( $payment_meta, $subscription ) {
		$payment_meta[ $this->id ] = array(
			'post_meta' => array(
				'_iugu_customer_payment_method_id' => array(
					'value' => get_post_meta( $subscription->id, '_iugu_customer_payment_method_id', true ),
					'label' => 'Iugu Payment Method ID',
				),
			),
		);

		return $payment_meta;
	}

	/**
	 * Validate the payment meta data required to process automatic recurring payments so that store managers can.
	 * manually set up automatic recurring payments for a customer via the Edit Subscription screen in Subscriptions 2.0+.
	 *
	 * @param  string $payment_method_id The ID of the payment method to validate.
	 * @param  array $payment_meta associative array of meta data required for automatic payments.
	 * @return array
	 * @throws Exception
	 */
	public function validate_subscription_payment_meta( $payment_method_id, $payment_meta ) {
		if ( $this->id === $payment_method_id ) {
			if ( ! isset( $payment_meta['post_meta']['_iugu_customer_payment_method_id']['value'] ) || empty( $payment_meta['post_meta']['_iugu_customer_payment_method_id']['value'] ) ) {
				throw new Exception( 'A "_iugu_customer_payment_method_id" value is required.' );
			}
		}
	}

	/**
	 * Don't transfer customer meta to resubscribe orders.
	 *
	 * @param int $resubscribe_order The order created for the customer to resubscribe to the old expired/cancelled subscription.
	 */
	public function delete_resubscribe_meta( $resubscribe_order ) {
		delete_post_meta( $resubscribe_order->id, '_iugu_customer_payment_method_id' );
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
			$payment_method_id = get_post_meta( $order->id, '_iugu_customer_payment_method_id', true );

			if ( ! $payment_method_id ) {
				if ( 'yes' == $this->debug ) {
					$this->log->add( $this->id, 'Missing customer payment method ID in subscription payment for order ' . $order->get_order_number() );
				}

				return new Exception( __( 'Customer payment method not found!', 'iugu-woocommerce' ) );
			}

			$charge = $this->api->create_charge( $order, array( 'customer_payment_method_id' => $payment_method_id ) );

			if ( isset( $charge['errors'] ) && ! empty( $charge['errors'] ) ) {
				$error = is_array( $charge['errors'] ) ? current( $charge['errors'] ) : $charge['errors'];

				return new Exception( $error );
			}

			update_post_meta( $order->id, '_transaction_id', sanitize_text_field( $charge['invoice_id'] ) );

			// Save only in old versions.
			if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.1.12', '<=' ) ) {
				update_post_meta( $order->id, __( 'Iugu Transaction details', 'iugu-woocommerce' ), 'https://iugu.com/a/invoices/' . sanitize_text_field( $charge['invoice_id'] ) );
			}

			if ( ! $charge['success'] ) {
				return new Exception( __( 'Iugu: Credit card declined.', 'iugu-woocommerce' ) );
			}

			$order->add_order_note( __( 'Iugu: Invoice paid successfully by credit card.', 'iugu-woocommerce' ) );
			$order->payment_complete();
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
	 * Notification handler.
	 */
	public function notification_handler() {
		$this->api->notification_handler();
	}

	/**
	 * Payment fields.
	 */
	public function payment_fields() {
		$contains_subscription = false;

		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.1', '>=' ) ) {
			$order_id = absint( get_query_var( 'order-pay' ) );
		} else {
			$order_id = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : 0;
		}

		// Check from "pay for order" page.
		if ( 0 < $order_id ) {
			$contains_subscription = $this->api->order_contains_subscription( $order_id );
		} elseif ( class_exists( 'WC_Subscriptions_Cart' ) ) {
			$contains_subscription = WC_Subscriptions_Cart::cart_contains_subscription();
		}

		if ( $contains_subscription ) {
			wp_enqueue_script( 'wc-credit-card-form' );

			if ( $description = $this->get_description() ) {
				echo wpautop( wptexturize( $description ) );
			}

			woocommerce_get_template(
				'credit-card/payment-form.php',
				array(
					'order_total'          => 0,
					'installments'         => 0,
					'smallest_installment' => 0,
					'free_interest'        => 0,
					'transaction_rate'     => 0,
					'rates'                => array()
				),
				'woocommerce/iugu/',
				WC_Iugu::get_templates_path()
			);
		} else {
			parent::payment_fields();
		}
	}
}
