<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Iugu Payment Gateway class
 *
 * Extended by individual payment gateways to handle payments.
 *
 * @class   WC_Iugu_Gateway
 * @extends WC_Payment_Gateway
 * @version 1.0.0
 * @author  Iugu
 */
class WC_Iugu_Gateway extends WC_Payment_Gateway {

	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		global $woocommerce;

		$this->id                 = 'iugu';
		$this->icon               = apply_filters( 'iugu_woocommerce_icon', plugins_url( 'assets/images/iugu.png', plugin_dir_path( __FILE__ ) ) );
		$this->method_title       = __( 'Iugu', 'iugu-woocommerce' );
		$this->method_description = __( 'Accept payments by credit card or banking ticket using the Iugu.', 'iugu-woocommerce' );
		$this->has_fields         = true;

		// Load the form fields.
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		// Display options.
		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );

		// Gateway options.
		$this->invoice_prefix = $this->get_option( 'invoice_prefix', 'WC-' );

		// API options.
		$this->account_id   = $this->get_option( 'account_id' );
		$this->api_key      = $this->get_option( 'api_key' );
		$this->methods      = $this->get_option( 'methods', 'all' );
		$this->installments = $this->get_option( 'installments' );

		// Debug options.
		$this->debug = $this->get_option( 'debug' );

		// Active logs.
		if ( 'yes' == $this->debug ) {
			if ( class_exists( 'WC_Logger' ) ) {
				$this->log = new WC_Logger();
			} else {
				$this->log = $woocommerce->logger();
			}
		}

		$this->api = new WC_Iugu_API( $this );

		// Actions.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'transparent_checkout_billet_thankyou_page' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ), 9999 );

		// Display admin notices.
		$this->admin_notices();
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
		$api = ( ! empty( $this->account_id ) && ! empty( $this->api_key ) );

		$available = ( 'yes' == $this->settings['enabled'] ) && $api && $this->using_supported_currency();

		return $available;
	}

	/**
	 * Call plugin scripts in front-end.
	 */
	public function frontend_scripts() {
		if ( is_checkout() ) {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_style( 'iugu-woocommerce-checkout-css', plugins_url( 'assets/css/checkout' . $suffix . '.css', plugin_dir_path( __FILE__ ) ) );

			wp_enqueue_script( 'iugu-js', $this->api->get_js_url(), array(), null, true );
			wp_enqueue_script( 'iugu-woocommerce-checkout-js', plugins_url( 'assets/js/checkout' . $suffix . '.js', plugin_dir_path( __FILE__ ) ), array( 'jquery', 'wc-credit-card-form' ), WC_Iugu::VERSION, true );

			wp_localize_script(
				'iugu-woocommerce-checkout-js',
				'iugu_wc_checkout_params',
				array(
					'account_id'                    => $this->account_id,
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
	 * Returns a bool that indicates if currency is amongst the supported ones.
	 *
	 * @return bool
	 */
	public function using_supported_currency() {
		return ( 'BRL' == get_woocommerce_currency() );
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 *
	 * @return void
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'iugu-woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Iugu', 'iugu-woocommerce' ),
				'default' => 'yes'
			),
			'title' => array(
				'title'       => __( 'Title', 'iugu-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'iugu-woocommerce' ),
				'desc_tip'    => true,
				'default'     => __( 'Iugu', 'iugu-woocommerce' )
			),
			'description' => array(
				'title'       => __( 'Description', 'iugu-woocommerce' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description which the user sees during checkout.', 'iugu-woocommerce' ),
				'default'     => __( 'Pay via Iugu', 'iugu-woocommerce' )
			),
			'invoice_prefix' => array(
				'title'       => __( 'Invoice Prefix', 'iugu-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Please enter a prefix for your invoice numbers. If you use your Iugu account for multiple stores ensure this prefix is unqiue as Iugu will not allow orders with the same invoice number.', 'iugu-woocommerce' ),
				'desc_tip'    => true,
				'default'     => 'WC-'
			),
			'api_section' => array(
				'title'       => __( 'Payment API', 'iugu-woocommerce' ),
				'type'        => 'title',
				'description' => ''
			),
			'methods' => array(
				'title'       => __( 'Iugu Payment Methods', 'woocommerce-moip' ),
				'type'        => 'select',
				'description' => __( 'Select the payment methods', 'iugu-woocommerce' ),
				'default'     => 'all',
				'options'     => array(
					'all'         => __( 'Credit Card and Billet', 'iugu-woocommerce' ),
					'credit_card' => __( 'Credit Card only', 'iugu-woocommerce' ),
					'billet'      => __( 'Billet only', 'iugu-woocommerce' ),
				)
			),
			'installments' => array(
				'title'       => __( 'Number of Installments', 'iugu-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'The maximum number of installments allowed for credit cards. Put a number bigger than 1 to enable the field', 'iugu-woocommerce' ),
				'desc_tip'    => true,
				'default'     => '0'
			),
			'account_id' => array(
				'title'       => __( 'Account ID', 'iugu-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Please enter your Account ID; this is needed in order to take payment.', 'iugu-woocommerce' ),
				'desc_tip'    => true,
				'default'     => ''
			),
			'api_key' => array(
				'title'       => __( 'API Token', 'iugu-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Please enter your API Token; this is needed in order to take payment.', 'iugu-woocommerce' ),
				'desc_tip'    => true,
				'default'     => ''
			),
			'payment_section' => array(
				'title'       => __( 'Payment Settings', 'iugu-woocommerce' ),
				'type'        => 'title',
				'description' => __( 'These options need to be available to you in your Iugu account.', 'iugu-woocommerce' )
			),
			'debug' => array(
				'title'       => __( 'Debug Log', 'iugu-woocommerce' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable logging', 'iugu-woocommerce' ),
				'default'     => 'no',
				'description' => sprintf( __( 'Log Iugu events, such as API requests, inside %s', 'iugu-woocommerce' ), '<code>woocommerce/logs/iugu-' . sanitize_file_name( wp_hash( 'iugu' ) ) . '.txt</code>' )
			),
			'information_section' => array(
				'title'       => __( 'Informations', 'iugu-woocommerce' ),
				'type'        => 'title',
				'description' => __( 'These options need to be configured in your Iugu account.', 'iugu-woocommerce' )
			),
			'iugu_update_order' => array(
				'title'       => __( 'Iugu Trigger URL', 'iugu-woocommerce' ),
				'type'        => 'text',
				'default'     => plugins_url() . '/iugu-woocommerce/woocommerce-iugu-update-order.php',
				'description' => __( 'Handle Iugu events, such as API posts', 'iugu-woocommerce' )
			)
		);
	}

	/**
	 * Payment fields.
	 */
	public function payment_fields() {
		wp_enqueue_script( 'wc-credit-card-form' );

		if ( $description = $this->get_description() ) {
			echo wpautop( wptexturize( $description ) );
		}

		woocommerce_get_template( 'payment-form.php', array(
			'methods'      => $this->methods,
			'installments' => $this->installments
		), 'woocommerce/iugu/', WC_Iugu::get_templates_path() );
	}

	/**
	 * Displays notifications when the admin has something wrong with the configuration.
	 */
	protected function admin_notices() {
		if ( is_admin() ) {

			// Checks if token is not empty.
			if ( empty( $this->account_id ) ) {
				add_action( 'admin_notices', array( $this, 'account_id_missing_message' ) );
			}

			// Checks if key is not empty.
			if ( empty( $this->api_key ) ) {
				add_action( 'admin_notices', array( $this, 'key_missing_message'  ) );
			}

			// Checks that the currency is supported
			if ( ! $this->using_supported_currency() ) {
				add_action( 'admin_notices', array( $this, 'currency_not_supported_message'  ) );
			}
		}
	}

	/**
	 * Add error message in checkout.
	 *
	 * @param  string $message Error message.
	 *
	 * @return string          Displays the error message.
	 */
	protected function add_error( $message ) {
		global $woocommerce;

		if ( version_compare( WOOCOMMERCE_VERSION, '2.1', '>=' ) ) {
			wc_add_notice( $message, 'error' );
		} else {
			$woocommerce->add_error( $message );
		}
	}


	/**
	 * Convert the price value into cents format
	 *
	 * @param  float $number Product price
	 *
	 * @return int           Formated price
	 */
	public function price_cents_format( $number ) {
		$number = number_format( $number, 2, '', '' );

		return $number;
	}

	/**
	 * Get phone number
	 *
	 * @param  WC_Order $order
	 *
	 * @return string
	 */
	public function get_phone_number( $order ) {
		$phone_number = preg_replace( '([^0-9])', '', $order->billing_phone );

		return array(
			'area_code' => substr( $phone_number, 0, 2 ),
			'number'    => substr( $phone_number, 2 )
		);
	}

	/**
	 * Handle function to call Iugu APi and pay with creditcard
	 *
	 * @param string   $token
	 * @param WC_Order $order Woocommerce order
	 *
	 * @return stdClass Result of Iugu payment request
	 */
	public function pay_with_creditcard( $token, $order ) {

		$split       = isset( $_POST[ $this->id . '-cc-split' ] ) ? $_POST[ $this->id . '-cc-split' ] : '';
		$holder_name = isset( $_POST[ 'iugu_card_holder_name' ] ) ? $_POST[ 'iugu_card_holder_name' ] : '';
		$order_items = $order->get_items();
		$items       = array();

		foreach ( $order_items as $items_key => $item ) {
			$items[] = array(
				'description' => $item['name'],
				'quantity'    => $item['qty'],
				'price_cents' => $this->price_cents_format( $item['line_total'] / $item['qty'] )
			);
		}

		$phone_number = $this->get_phone_number( $order );

		$chargeToSend = array(
			'token'  => $token,
			'email'  => $order->billing_email,
			'months' => $split,
			'items'  => $items,
			'payer'  => array(
				'name'         => $holder_name,
				'phone_prefix' => $phone_number['area_code'],
				'phone'        => $phone_number['number'],
				'email'        => $order->billing_email,
				'address'      => array(
					'street'   => $order->billing_address_1,
					'number'   => $order->billing_number,
					'city'     => $order->billing_city,
					'state'    => $order->billing_state,
					'country'  => 'Brasil',
					'zip_code' => $order->shipping_postcode
				)
			)
		);

		return Iugu_Charge::create( $chargeToSend );
	}

	/**
	 *  Handle function to call Iugu APi and pay with billet
	 *
	 * @param WC_Order $order Woocommerce order
	 *
	 * @return stdClass Result of Iugu payment request
	 */
	public function pay_with_billet( $order ) {

		$order_items = $order->get_items();
		$items       = array();

		foreach ( $order_items as $items_key => $item ) {
			$items[] = array(
				'description' => $item['name'],
				'quantity'    => $item['qty'],
				'price_cents' => $this->price_cents_format( $item['line_total'] / $item['qty'] )
			);
		}

		$phone_number = $this->get_phone_number( $order );

		$chargeToSend = array(
			'method' => 'bank_slip',
			'email'  => $order->billing_email,
			'items'  => $items,
			'payer'  => array(
				'name'         => $order->billing_first_name . ' ' . $order->billing_last_name,
				'phone_prefix' => $phone_number['area_code'],
				'phone'        => $phone_number['number'],
				'email'        => $order->billing_email,
				'address'      => array(
					'street'   => $order->billing_address_1,
					'number'   => $order->billing_number,
					'city'     => $order->billing_city,
					'state'    => $order->billing_state,
					'country'  => 'Brasil',
					'zip_code' => $order->shipping_postcode
				)
			)
		);

		return Iugu_Charge::create( $chargeToSend );
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param int $order_id Order ID.
	 *
	 * @return array Redirect.
	 */
	public function process_payment( $order_id ) {
		$credit_card_token = isset( $_POST['iugu_token'] ) ? $_POST['iugu_token'] : '';

		Iugu::setApiKey( $this->api_key );

		// Get this Order's information so that we know
		// who to charge and how much
		$order = new WC_Order( $order_id );

		// payment result
		$result = null;
		$payment_type = $_POST['iugu_payment_method'];

		// creditcard
		if ($payment_type == "credit-card") {

			$result = $this->pay_with_creditcard ( $credit_card_token, $order );
			$result = $this->credit_order_complete( $result, $order );

			if(!is_null($result)){
				return $result;
			}

		// billet
		} elseif ($payment_type == "billet") {

			$result = $this->pay_with_billet ( $order );
			$result = $this->billet_order_complete($result,$order);

			if(!is_null($result)){
				return $result;
			}
		}
	}

	/**
	 * Recieve the Iugu result for a payment with billet card and finish the woocommerce payment process
	 *
	 * @param stdClass $result
	 * @return multitype:string NULL
	 */
	public function billet_order_complete($result,$order){
		global $woocommerce;

		// Test the code to know if the transaction went through or not.
		if ($result->__get ( "success" ) == "1") {
			// Payment has been successful
			$order->add_order_note ( __( 'Iugu: waiting payment.', 'iugu-woocommerce' ) );
			$order->add_order_note ( __( 'Invoice:' . $result->__get ( "invoice_id" ), 'iugu-woocommerce' ) );


			// Empty the cart (Very important step)
			$woocommerce->cart->empty_cart();

			//Billet link ( may have a better way to pass this link to the tankyou page )
			session_start();
			$_SESSION['billet_result'] = $result;

			// Redirect to thank you page
			return array(
					'result' => 'success',
					'redirect' => $this->get_return_url ( $order )
			);
		} else {
			// Transaction was not succesful
			// Add notice to the cart
			wc_add_notice ( json_encode($result->__get ( "errors" )), 'error' );
			// Add note to the order for your reference
			$order->add_order_note ( 'Error: ' . json_encode($result->__get ( "errors" )) );

			return null;
		}

	}
	/**
	 * Recieve the Iugu result for a payment with credit card and finish the woocommerce payment process
	 * @param stdClass $result
	 * @return multitype:string NULL
	 */
	public function credit_order_complete($result,$order){
		global $woocommerce;

		// Test the code to know if the transaction went through or not.
		if ($result->__get ( "success" ) == "1") {
			// Payment has been successful
			$order->add_order_note ( __( 'Iugu: payment completed.', 'iugu-woocommerce' ) );
			$order->add_order_note ( __( 'Invoice:' . $result->__get ( "invoice_id" ), 'iugu-woocommerce' ) );

			// Mark order as Paid
			$order->payment_complete ();

			// Empty the cart (Very important step)
			$woocommerce->cart->empty_cart ();

			// Redirect to thank you page
			return array(
					'result' => 'success',
					'redirect' => $this->get_return_url ( $order )
			);
		} else {
			// Transaction was not succesful
			// Add notice to the cart
			wc_add_notice ( json_encode($result->__get ( "errors" )), 'error' );
			// Add note to the order for your reference
			$order->add_order_note ( 'Error: ' . json_encode($result->__get ( "errors" )) );

			return null;
		}

	}


	/**
	 * Transparent billet checkout custom Thank You message.
	 *
	 * @return void
	 */
	public function transparent_checkout_billet_thankyou_page(){

		session_start();
		$result = $_SESSION['billet_result'];

		if(!empty($result)){
			?>

			<div>
				<a href="<?php echo $result->__get('pdf'); ?>" target="_blank">
					<button type="button"><?php _e("Click here to generate your billet");?></button>
				</a>
			</div>

			<?php
			unset($_SESSION['billet_result']);
		}
	}


	/**
	 * Gets the admin url.
	 *
	 * @return string
	 */
	protected function admin_url() {
		if ( version_compare( WOOCOMMERCE_VERSION, '2.1', '>=' ) ) {
			return admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_iugu_gateway' );
		}

		return admin_url( 'admin.php?page=woocommerce_settings&tab=payment_gateways&section=WC_Iugu_Gateway' );
	}

	/**
	 * Adds error message when not configured the token.
	 *
	 * @return string Error Mensage.
	 */
	public function account_id_missing_message() {
		echo '<div class="error"><p><strong>' . __( 'Iugu Disabled', 'iugu-woocommerce' ) . '</strong>: ' . sprintf( __( 'You should inform your Account ID. %s', 'iugu-woocommerce' ), '<a href="' . $this->admin_url() . '">' . __( 'Click here to configure!', 'iugu-woocommerce' ) . '</a>' ) . '</p></div>';
	}

	/**
	 * Adds error message when not configured the key.
	 *
	 * @return string Error Mensage.
	 */
	public function key_missing_message() {
		echo '<div class="error"><p><strong>' . __( 'Iugu Disabled', 'iugu-woocommerce' ) . '</strong>: ' . sprintf( __( 'You should inform your API Token. %s', 'iugu-woocommerce' ), '<a href="' . $this->admin_url() . '">' . __( 'Click here to configure!', 'iugu-woocommerce' ) . '</a>' ) . '</p></div>';
	}
}
