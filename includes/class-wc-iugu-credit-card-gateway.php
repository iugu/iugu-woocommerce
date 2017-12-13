<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Iugu Payment Credit Card Gateway class.
 *
 * Extended by individual payment gateways to handle payments.
 *
 * @class   WC_Iugu_Credit_Card_Gateway
 * @extends WC_Payment_Gateway
 */
class WC_Iugu_Credit_Card_Gateway extends WC_Payment_Gateway {

	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		global $woocommerce;

		$this->id                   = 'iugu-credit-card';
		$this->icon                 = apply_filters( 'iugu_woocommerce_credit_card_icon', '' );
		$this->method_title         = __( 'Iugu - Credit Card', 'iugu-woocommerce' );
		$this->method_description   = __( 'Accept payments by credit card using the Iugu.', 'iugu-woocommerce' );
		$this->has_fields           = true;
		$this->view_transaction_url = 'https://iugu.com/a/invoices/%s';
		$this->supports             = array(
			'subscriptions',
			'products',
			'subscription_cancellation',
			'subscription_reactivation',
			'subscription_suspension',
			'subscription_amount_changes',
			'subscription_payment_method_change', // Subscriptions 1.n compatibility.
			'subscription_payment_method_change_customer',
			'subscription_payment_method_change_admin',
			'subscription_date_changes',
			'multiple_subscriptions',
			'refunds',
			'pre-orders'
		);

		// Load the form fields.
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		// Options.
		$this->title                = $this->get_option( 'title' );
		$this->description          = $this->get_option( 'description' );
		$this->account_id           = $this->get_option( 'account_id' );
		$this->api_token            = $this->get_option( 'api_token' );
		$this->installments         = $this->get_option( 'installments' );
		$this->pass_interest        = $this->get_option( 'pass_interest' );
		$this->smallest_installment = $this->get_option( 'smallest_installment', 5 );
		$this->free_interest        = $this->get_option( 'free_interest' );
		$this->transaction_rate     = $this->get_option( 'transaction_rate', 7 );
		$this->send_only_total      = $this->get_option( 'send_only_total', 'no' );
		$this->sandbox              = $this->get_option( 'sandbox', 'no' );
		$this->debug                = $this->get_option( 'debug' );

		// Active logs.
		if ( 'yes' == $this->debug ) {
			if ( class_exists( 'WC_Logger' ) ) {
				$this->log = new WC_Logger();
			} else {
				$this->log = $woocommerce->logger();
			}
		}

		$this->api = new WC_Iugu_API( $this, 'credit-card' );

		// Actions.
		add_action( 'woocommerce_api_wc_iugu_credit_card_gateway', array( $this, 'notification_handler' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
		add_action( 'woocommerce_email_after_order_table', array( $this, 'email_instructions' ), 10, 3 );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ), 9999 );

		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		}
	}

	/**
	 * Returns a value indicating the the Gateway is available or not. It's called
	 * automatically by WooCommerce before allowing customers to use the gateway
	 * for payment.
	 *
	 * @return bool
	 */
	public function is_available() {
		// Test if is valid for use.
		$api = ! empty( $this->account_id ) && ! empty( $this->api_token );

		$available = parent::is_available() && $api && $this->api->using_supported_currency();

		return $available;
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'iugu-woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Iugu Credit Card', 'iugu-woocommerce' ),
				'default' => 'no'
			),
			'title' => array(
				'title'       => __( 'Title', 'iugu-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'iugu-woocommerce' ),
				'desc_tip'    => true,
				'default'     => __( 'Credit Card', 'iugu-woocommerce' )
			),
			'description' => array(
				'title'       => __( 'Description', 'iugu-woocommerce' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description which the user sees during checkout.', 'iugu-woocommerce' ),
				'default'     => __( 'Pay with credit card.', 'iugu-woocommerce' )
			),
			'integration' => array(
				'title'       => __( 'Integration Settings', 'iugu-woocommerce' ),
				'type'        => 'title',
				'description' => ''
			),
			'account_id' => array(
				'title'             => __( 'Account ID', 'iugu-woocommerce' ),
				'type'              => 'text',
				'description'       => sprintf( __( 'Please enter your Account ID. This is needed in order to take payment. Is possible found the Account ID in %s.', 'iugu-woocommerce' ), '<a href="https://app.iugu.com/account" target="_blank">' . __( 'Iugu Account Settings', 'iugu-woocommerce' ) . '</a>' ),
				'default'           => '',
				'custom_attributes' => array(
					'required' => 'required'
				)
			),
			'api_token' => array(
				'title'            => __( 'API Token', 'iugu-woocommerce' ),
				'type'              => 'text',
				'description'       => sprintf( __( 'Please enter your API Token. This is needed in order to take payment. Is possible generate a new API Token in %s.', 'iugu-woocommerce' ), '<a href="https://app.iugu.com/account" target="_blank">' . __( 'Iugu Account Settings', 'iugu-woocommerce' ) . '</a>' ),
				'default'           => '',
				'custom_attributes' => array(
					'required' => 'required'
				)
			),
			'payment' => array(
				'title'       => __( 'Payment Options', 'iugu-woocommerce' ),
				'type'        => 'title',
				'description' => ''
			),
			'installments' => array(
				'title'             => __( 'Number of credit card Installments', 'iugu-woocommerce' ),
				'type'              => 'number',
				'description'       => __( 'The maximum number of installments allowed for credit cards. Put a number bigger than 1 to enable the field. This cannot be greater than the number allowed in your Iugu account.', 'iugu-woocommerce' ),
				'desc_tip'          => true,
				'default'           => '1',
				'custom_attributes' => array(
					'step' => '1',
					'min'  => '1',
					'max'  => '12'
				)
			),
			'smallest_installment' => array(
				'title'       => __( 'Smallest Installment', 'iugu-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Smallest value of each installment, cannot be less than 5.', 'iugu-woocommerce' ),
				'desc_tip'    => true,
				'default'     => '5'
			),
			'pass_interest' => array(
				'title'       => __( 'Pass interest rate', 'iugu-woocommerce' ),
				'type'        => 'checkbox',
				'label'       => __( 'Pass on the Installments interest to the customer.', 'iugu-woocommerce' ),
				'description' => __( 'This option is only for display and should represent what was configured on your Iugu account.', 'iugu-woocommerce' ),
				'desc_tip'    => true,
				'default'     => 'no'
			),
			'free_interest' => array(
				'title'             => __( 'Free interest', 'iugu-woocommerce' ),
				'type'              => 'number',
				'description'       => __( 'Indicate how many installments shall not bear interest, use 0 to disable this option.', 'iugu-woocommerce' ) . ' ' . __( 'This option is only for display and should represent what was configured on your Iugu account.', 'iugu-woocommerce' ),
				'desc_tip'          => true,
				'default'           => '0',
				'custom_attributes' => array(
					'step' => '1',
					'min'  => '0',
					'max'  => '12'
				)
			),
			'transaction_rate' => array(
				'title'             => __( 'Rate per transaction', 'iugu-woocommerce' ),
				'type'              => 'number',
				'description'       => __( 'Enter here the transaction rate that is set up in your plan in Iugu.', 'iugu-woocommerce' ) . ' ' . __( 'This option is only for display and should represent what was configured on your Iugu account.', 'iugu-woocommerce' ),
				'desc_tip'          => true,
				'default'           => '7',
				'custom_attributes' => array(
					'step' => 'any'
				)
			),
			'behavior' => array(
				'title'       => __( 'Integration Behavior', 'iugu-woocommerce' ),
				'type'        => 'title',
				'description' => ''
			),
			'send_only_total' => array(
				'title'   => __( 'Send only the order total', 'iugu-woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'If this option is enabled will only send the order total, not the list of items.', 'iugu-woocommerce' ),
				'default' => 'no'
			),
			'testing' => array(
				'title'       => __( 'Gateway Testing', 'iugu-woocommerce' ),
				'type'        => 'title',
				'description' => ''
			),
			'sandbox' => array(
				'title'       => __( 'Iugu Sandbox', 'iugu-woocommerce' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable Iugu Sandbox', 'iugu-woocommerce' ),
				'default'     => 'no',
				'description' => sprintf( __( 'Iugu Sandbox can be used to test the payments. <strong>Note:</strong> you must use the development API Token that can be created in %s.', 'iugu-woocommerce' ), '<a href="https://iugu.com/settings/account" target="_blank">' . __( 'Iugu Account Settings', 'iugu-woocommerce' ) . '</a>' )
			),
			'debug' => array(
				'title'       => __( 'Debug Log', 'iugu-woocommerce' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable logging', 'iugu-woocommerce' ),
				'default'     => 'no',
				'description' => sprintf( __( 'Log Iugu events, such as API requests, you can check this log in %s.', 'iugu-woocommerce' ), WC_Iugu::get_log_view( $this->id ) )
			)
		);
	}

	/**
	 * Call plugin scripts in front-end.
	 */
	public function frontend_scripts() {
		if ( is_checkout() && 'yes' == $this->enabled ) {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_style( 'iugu-woocommerce-credit-card-css', plugins_url( 'assets/css/credit-card' . $suffix . '.css', plugin_dir_path( __FILE__ ) ) );

			wp_enqueue_script( 'iugu-js', $this->api->get_js_url(), array(), null, true );
			wp_enqueue_script( 'iugu-woocommerce-credit-card-js', plugins_url( 'assets/js/credit-card' . $suffix . '.js', plugin_dir_path( __FILE__ ) ), array( 'jquery', 'wc-credit-card-form' ), WC_Iugu::CLIENT_VERSION, true );

			wp_localize_script(
				'iugu-woocommerce-credit-card-js',
				'iugu_wc_credit_card_params',
				array(
					'account_id'                    => $this->account_id,
					'is_sandbox'                    => $this->sandbox,
					'i18n_number_field'             => __( 'Card Number', 'iugu-woocommerce' ),
					'i18n_verification_value_field' => __( 'Security Code', 'iugu-woocommerce' ),
					'i18n_expiration_field'         => __( 'Card Expiry Date', 'iugu-woocommerce' ),
					'i18n_first_name_field'         => __( 'First Name', 'iugu-woocommerce' ),
					'i18n_last_name_field'          => __( 'Last Name', 'iugu-woocommerce' ),
					'i18n_is_invalid'               => __( 'is invalid', 'iugu-woocommerce' )
				)
			);
		}
	}

	/**
	 * Payment fields.
	 */
	public function payment_fields() {
		wp_enqueue_script( 'wc-credit-card-form' );

		if ( $description = $this->get_description() ) {
			echo wpautop( wptexturize( $description ) );
		}

		// Get order total.
		if ( method_exists( $this, 'get_order_total' ) ) {
			$order_total = $this->get_order_total();
		} else {
			$order_total = $this->api->get_order_total();
		}

		wc_get_template(
			'credit-card/payment-form.php',
			array(
				'order_total'          => $order_total,
				'installments'         => intval( $this->installments ),
				'smallest_installment' => 5 <= $this->smallest_installment ? $this->smallest_installment : 5,
				'free_interest'        => 'yes' == $this->pass_interest ? intval( $this->free_interest ) : 12,
				'transaction_rate'     => $this->api->get_transaction_rate(),
				'rates'                => $this->api->get_interest_rate()
			),
			'woocommerce/iugu/',
			WC_Iugu::get_templates_path()
		);
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param  int $order_id Order ID.
	 *
	 * @return array         Redirect.
	 */
	public function process_payment( $order_id ) {
		if ( ! isset( $_POST['iugu_token'] ) ) {
			$order = wc_get_order( $order_id );

			if ( 'yes' === $this->debug ) {
				$this->log->add( $this->id, 'Error doing the charge for order ' . $order->get_order_number() . ': Missing the "iugu_token".' );
			}

			$this->api->add_error( '<strong>' . esc_attr( $this->title ) . '</strong>: ' . __( 'Please make sure your card details have been entered correctly and that your browser supports JavaScript.', 'iugu-woocommerce' ) );

			return array(
				'result'   => 'fail',
				'redirect' => ''
			);
		}

		return $this->api->process_payment( $order_id );
	}

	/**
	 * Thank You page message.
	 *
	 * @param  int    $order_id Order ID.
	 *
	 * @return string
	 */
	public function thankyou_page( $order_id ) {
		$order = wc_get_order( $order_id );

		// WooCommerce 3.0 or later.
		if ( is_callable( array( $order, 'get_meta' ) ) ) {
			$data = $order->get_meta( '_iugu_wc_transaction_data' );
		} else {
			$data = get_post_meta( $order_id, '_iugu_wc_transaction_data', true );
		}

		if ( isset( $data['installments'] ) && $order->has_status( 'processing' ) ) {
			wc_get_template(
				'credit-card/payment-instructions.php',
				array(
					'installments' => $data['installments']
				),
				'woocommerce/iugu/',
				WC_Iugu::get_templates_path()
			);
		}
	}

	/**
	 * Add content to the WC emails.
	 *
	 * @param  object $order         Order object.
	 * @param  bool   $sent_to_admin Send to admin.
	 * @param  bool   $plain_text    Plain text or HTML.
	 *
	 * @return string                Payment instructions.
	 */
	public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
		// WooCommerce 3.0 or later.
		if ( is_callable( array( $order, 'get_meta' ) ) ) {
			if ( $sent_to_admin || ! $order->has_status( array( 'processing', 'on-hold' ) ) || $this->id !== $order->get_payment_method() ) {
				return;
			}

			$data = $order->get_meta( '_iugu_wc_transaction_data' );
		} else {
			if ( $sent_to_admin || ! $order->has_status( array( 'processing', 'on-hold' ) ) || $this->id !== $order->payment_method ) {
				return;
			}

			$data = get_post_meta( $order->id, '_iugu_wc_transaction_data', true );
		}

		if ( isset( $data['installments'] ) ) {
			if ( $plain_text ) {
				wc_get_template(
					'credit-card/emails/plain-instructions.php',
					array(
						'installments' => $data['installments']
					),
					'woocommerce/iugu/',
					WC_Iugu::get_templates_path()
				);
			} else {
				wc_get_template(
					'credit-card/emails/html-instructions.php',
					array(
						'installments' => $data['installments']
					),
					'woocommerce/iugu/',
					WC_Iugu::get_templates_path()
				);
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
	 * Admin scripts.
	 *
	 * @param string $hook Page slug.
	 */
	public function admin_scripts( $hook ) {
		if ( in_array( $hook, array( 'woocommerce_page_wc-settings', 'woocommerce_page_woocommerce_settings' ) ) && ( isset( $_GET['section'] ) && strtolower( get_class( $this ) ) == strtolower( $_GET['section'] ) ) ) {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_script( 'iugu-credit-card-admin', plugins_url( 'assets/js/admin-credit-card' . $suffix . '.js', plugin_dir_path( __FILE__ ) ), array( 'jquery' ), WC_Iugu::CLIENT_VERSION, true );
		}
	}
}
