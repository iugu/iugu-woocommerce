<?php
/**
 * WC Iugu API Class.
 */
class WC_Iugu_API {

	/**
	 * API URL.
	 *
	 * @var string
	 */
	protected $api_url = 'https://api.iugu.com/v1/';

	/**
	 * JS Library URL.
	 *
	 * @var string
	 */
	protected $js_url = 'https://js.iugu.com/v2.js';

	/**
	 * Gateway class.
	 *
	 * @var WC_Iugu_Gateway
	 */
	protected $gateway;

	/**
	 * Constructor.
	 *
	 * @param WC_Iugu_Gateway $gateway
	 */
	public function __construct( $gateway = null ) {
		$this->gateway = $gateway;
	}

	/**
	 * Get API URL.
	 *
	 * @return string
	 */
	public function get_api_url() {
		return $this->api_url;
	}

	/**
	 * Get JS Library URL.
	 *
	 * @return string
	 */
	public function get_js_url() {
		return $this->js_url;
	}

	/**
	 * Get WooCommerce return URL.
	 *
	 * @return string
	 */
	protected function get_wc_request_url() {
		global $woocommerce;

		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.1', '>=' ) ) {
			return WC()->api_request_url( get_class( $this->gateway ) );
		} else {
			return $woocommerce->api_request_url( get_class( $this->gateway ) );
		}
	}

	/**
	 * Only numbers.
	 *
	 * @param  string|int $string
	 *
	 * @return string|int
	 */
	protected function only_numbers( $string ) {
		return preg_replace( '([^0-9])', '', $string );
	}

	/**
	 * Do requests in the Iugu API.
	 *
	 * @param  string $endpoint API Endpoint.
	 * @param  string $method   Request method.
	 * @param  array  $data     Request data.
	 * @param  array  $headers  Request headers.
	 *
	 * @return array            Request response.
	 */
	protected function do_request( $endpoint, $method = 'POST', $data = array(), $headers = array() ) {
		$params = array(
			'method'    => $method,
			'sslverify' => false,
			'timeout'   => 60,
			'headers'    => array(
				'Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8',
				'Authorization' => 'Basic ' . base64_encode( $this->gateway->api_token . ':x' )
			)
		);

		if ( ! empty( $data ) ) {
			$params['body'] = $data;
		}

		if ( ! empty( $headers ) ) {
			$params['headers'] = $headers;
		}

		return wp_remote_post( $this->get_api_url() . $endpoint, $params );
	}

	/**
	 * Build the API params from an array.
	 *
	 * @param  array  $data
	 * @param  string $prefix
	 *
	 * @return string
	 */
	protected function build_api_params( $data, $prefix = null ) {
		if ( ! is_array( $data ) ) {
			return $data;
		}

		$params = array();

		foreach ( $data as $key => $value ) {
			if ( is_null( $value ) ) {
				continue;
			}

			if ( $prefix && $key && ! is_int( $key ) ) {
				$key = $prefix . '[' . $key . ']';
			} elseif ( $prefix ) {
				$key = $prefix . '[]';
			}

			if ( is_array( $value ) ) {
				$params[] = $this->build_api_params( $value, $key );
			} else {
				$params[] = $key . '=' . urlencode( $value );
			}
		}

		return implode( '&', $params );
	}

	/**
	 * Get the invoice data.
	 *
	 * @param  WC_Order $order
	 *
	 * @return array
	 */
	protected function get_invoice_data( $order ) {
		$items = array();

		$data = array(
			'email'            => $order->billing_email,
			'due_date'         => date( 'd-m-Y', strtotime( '+1 day' ) ),
			'return_url'       => $this->gateway->get_return_url( $order ),
			'expired_url'      => $order->get_cancel_order_url(),
			'notification_url' => $this->get_wc_request_url(),
			'ignore_due_email' => true,
			'custom_variables' => array(
				array(
					'name'  => 'order_id',
					'value' => $order->id
				)
			)
		);

		// Force only one item.
		if ( 'yes' == $this->gateway->send_only_total ) {
			$items[] = array(
				'description' => sprintf( __( 'Order %s', 'iugu-woocommerce' ), $order->get_order_number() ),
				'price_cents' => $order->get_total() * 100,
				'quantity'    => 1
			);
		} else {
			// Products.
			if ( 0 < sizeof( $order->get_items() ) ) {
				foreach ( $order->get_items() as $order_item ) {
					if ( $order_item['qty'] ) {
						$item_name = $order_item['name'];
						$item_meta = new WC_Order_Item_Meta( $order_item['item_meta'] );

						if ( $meta = $item_meta->display( true, true ) ) {
							$item_name .= ' - ' . $meta;
						}

						$items[] = array(
							'description' => $item_name,
							'price_cents' => $order->get_item_total( $order_item, false ) * 100,
							'quantity'    => $order_item['qty']
						);
					}
				}
			}

			// Fees.
			if ( 0 < sizeof( $order->get_fees() ) ) {
				foreach ( $order->get_fees() as $fee ) {
					$items[] = array(
						'description' => $fee['name'],
						'price_cents' => $fee['line_total'] * 100,
						'quantity'    => 1
					);
				}
			}

			// Taxes.
			if ( 0 < sizeof( $order->get_taxes() ) ) {
				foreach ( $order->get_taxes() as $tax ) {
					$items[] = array(
						'description' => $tax['label'],
						'price_cents' => ( $tax['tax_amount'] + $tax['shipping_tax_amount'] ) * 100,
						'quantity'    => 1
					);
				}
			}

			// Shipping Cost.
			if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.1', '>=' ) ) {
				$items[] = array(
					'description' => __( 'Shipping', 'iugu-woocommerce' ),
					'price_cents' => $order->get_total_shipping() * 100,
					'quantity'    => 1
				);
			} else {
				$items[] = array(
					'description' => __( 'Shipping', 'iugu-woocommerce' ),
					'price_cents' => $order->get_shipping() * 100,
					'quantity'    => 1
				);
			}

			// Discount.
			if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.3', '<' ) ) {
				if ( 0 < $order->get_order_discount() ) {
					$data['discount_cents'] = $order->get_order_discount() * 100;
				}
			}
		}

		$data['items'] = $items;

		$data = apply_filters( 'iugu_woocommerce_invoice_data', $data );

		return $data;
	}

	/**
	 * Get the invoice ID.
	 *
	 * @param  WC_Order $order
	 *
	 * @return string
	 */
	protected function get_invoice_id( $order ) {
		$invoice_data = $this->get_invoice_data( $order );

		if ( 'yes' == $this->gateway->debug ) {
			$this->gateway->log->add( $this->gateway->id, 'Creating an invoice on Iugu for order ' . $order->get_order_number() . ' with the following data: ' . print_r( $invoice_data, true ) );
		}

		$invoice_data = $this->build_api_params( $invoice_data );
		$response     = $this->do_request( 'invoices', 'POST', $invoice_data );

		if ( is_wp_error( $response ) ) {
			if ( 'yes' == $this->gateway->debug ) {
				$this->gateway->log->add( $this->gateway->id, 'WP_Error while trying to generate an invoice: ' . $response->get_error_message() );
			}
		} elseif ( 200 == $response['response']['code'] && 'OK' == $response['response']['message'] ) {
			$invoice = json_decode( $response['body'], true );

			if ( 'yes' == $this->gateway->debug ) {
				$this->gateway->log->add( $this->gateway->id, 'Invoice created successfully!' );
			}

			return $invoice['id'];
		}

		if ( 'yes' == $this->gateway->debug ) {
			$this->gateway->log->add( $this->gateway->id, 'Error while generating the invoice for order ' . $order->get_order_number() . ': ' . print_r( $response, true ) );
		}

		return '';
	}

	/**
	 * Get phone number
	 *
	 * @param  WC_Order $order
	 *
	 * @return string
	 */
	protected function get_phone_number( $order ) {
		$phone_number = $this->only_numbers( $order->billing_phone );

		return array(
			'area_code' => substr( $phone_number, 0, 2 ),
			'number'    => substr( $phone_number, 2 )
		);
	}

	/**
	 * Get CPF or CNPJ.
	 *
	 * @param  WC_Order $order
	 *
	 * @return string
	 */
	protected function get_cpf_cnpj( $order ) {
		$wcbcf_settings = get_option( 'wcbcf_settings' );

		if ( 0 != $wcbcf_settings['person_type'] ) {
			if ( ( 1 == $wcbcf_settings['person_type'] && 1 == $order->billing_persontype ) || 2 == $wcbcf_settings['person_type'] ) {
				return $this->only_numbers( $order->billing_cpf );
			}

			if ( ( 1 == $wcbcf_settings['person_type'] && 2 == $order->billing_persontype ) || 3 == $wcbcf_settings['person_type'] ) {
				return $this->only_numbers( $order->billing_cnpj );
			}
		}

		return '';
	}

	/**
	 * Get charge data.
	 *
	 * @param  WC_Order $order
	 * @param  array    $posted
	 *
	 * @return array
	 */
	protected function get_charge_data( $order, $posted ) {
		$invoice_id = $this->get_invoice_id( $order );

		if ( '' == $invoice_id ) {
			if ( 'yes' == $this->gateway->debug ) {
				$this->gateway->log->add( $this->gateway->id, 'Error while doing the charge for order ' . $order->get_order_number() . ': Missing the invoice ID.' );
			}

			return array();
		}

		$phone_number = $this->get_phone_number( $order );
		$data = array(
			'invoice_id' => $invoice_id,
			'payer'      => array(
				'name'         => $order->billing_first_name . ' ' . $order->billing_last_name,
				'phone_prefix' => $phone_number['area_code'],
				'phone'        => $phone_number['number'],
				'email'        => $order->billing_email,
				'address'      => array(
					'street'   => $order->billing_address_1,
					'number'   => $order->billing_number,
					'city'     => $order->billing_city,
					'state'    => $order->billing_state,
					'country'  => isset( WC()->countries->countries[ $order->billing_country ] ) ? WC()->countries->countries[ $order->billing_country ] : $order->billing_country,
					'zip_code' => $this->only_numbers( $order->billing_postcode )
				)
			)
		);

		if ( $cpf_cnpj = $this->get_cpf_cnpj( $order ) ) {
			$data['payer']['cpf_cnpj'] = $cpf_cnpj;
		}

		$payment_type = isset( $posted['iugu_payment_method'] ) ? sanitize_text_field( $posted['iugu_payment_method'] ) : '';

		if ( 'credit-card' == $payment_type && isset( $posted['iugu_token'] ) ) {
			// Credit card token.
			$data['token'] = sanitize_text_field( $posted['iugu_token'] );

			// Installments.
			if ( isset( $posted['iugu_card_installments'] ) && 1 < $posted['iugu_card_installments'] ) {
				$data['months'] = absint( $posted['iugu_card_installments'] );
			}
		} elseif ( 'billet' == $payment_type ) {
			$data['method'] = 'bank_slip';
		} else {
			if ( 'yes' == $this->gateway->debug ) {
				$this->gateway->log->add( $this->gateway->id, 'Error doing the charge for order ' . $order->get_order_number() . ': Missing the "iugu_payment_method" or "iugu_token".' );
			}

			return array();
		}

		$data = apply_filters( 'iugu_woocommerce_charge_data', $data );

		return $data;
	}

	/**
	 * Do Charge.
	 *
	 * @param  WC_Order $order
	 * @param  array    $posted
	 *
	 * @return array
	 */
	public function do_charge( $order, $posted ) {
		if ( 'yes' == $this->gateway->debug ) {
			$this->gateway->log->add( $this->gateway->id, 'Doing charge for order ' . $order->get_order_number() . '...' );
		}

		$charge_data = $this->get_charge_data( $order, $posted );

		if ( empty( $charge_data ) ) {
			return array( 'errors' => array( __( 'An error has occurred while processing your payment, please try again. Or contact us for assistance.', 'iugu-woocommerce' ) ) );
		}

		$charge_data = $this->build_api_params( $charge_data );
		$response    = $this->do_request( 'charge', 'POST', $charge_data );

		if ( is_wp_error( $response ) ) {
			if ( 'yes' == $this->gateway->debug ) {
				$this->gateway->log->add( $this->gateway->id, 'WP_Error while trying to do a charge: ' . $response->get_error_message() );
			}
		} elseif ( isset( $response['body'] ) && ! empty( $response['body'] ) ) {
			$charge = json_decode( $response['body'], true );

			if ( 'yes' == $this->gateway->debug && isset( $charge['success'] ) ) {
				$this->gateway->log->add( $this->gateway->id, 'Charge created successfully!' );
			}

			return $charge;
		}

		if ( 'yes' == $this->gateway->debug ) {
			$this->gateway->log->add( $this->gateway->id, 'Error while doing the charge for order ' . $order->get_order_number() . ': ' . print_r( $response, true ) );
		}

		return array( 'errors' => array( __( 'An error has occurred while processing your payment, please try again. Or contact us for assistance.', 'iugu-woocommerce' ) ) );
	}
}
