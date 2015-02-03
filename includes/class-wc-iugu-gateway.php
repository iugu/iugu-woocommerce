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
		$this->accid            = $this->get_option( 'accid' );
		$this->key              = $this->get_option( 'key' );
		$this->api              = $this->get_option( 'api','full' );
		$this->cc_maximum_split = $this->get_option( 'cc_maximum_split' );

		// Debug options.
		$this->debug = $this->get_option( 'debug' );

		// Actions.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_thankyou_'.$this->id, array( $this, 'transparent_checkout_billet_thankyou_page' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ), 9999 );

		// Active logs.
		if ( 'yes' == $this->debug ) {
			if ( class_exists( 'WC_Logger' ) ) {
				$this->log = new WC_Logger();
			} else {
				$this->log = $woocommerce->logger();
			}
		}

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
		$api = ( ! empty( $this->accid ) && ! empty( $this->key ) );

		$available = ( 'yes' == $this->settings['enabled'] ) && $api && $this->using_supported_currency();

		return $available;
	}


	/**
	 * Call plugin scripts in front-end.
	 */
	public function scripts() {
		if ( is_checkout() ) {
			wp_enqueue_style( 'iugu-woocommerce-checkout-css', plugins_url( 'assets/css/iugu-checkout-customform.css', plugin_dir_path( __FILE__ ) ) );
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'iugu-woocommerce-checkout-js', plugins_url( 'assets/js/iugu-checkout-customform.js', plugin_dir_path( __FILE__ ) ), array( 'jquery','wc-checkout'), WC_Iugu::VERSION, true );
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
				'label'   => __( 'Enable Iugu standard', 'iugu-woocommerce' ),
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
			'api' => array(
				'title'       => __( 'Iugu Payment API', 'woocommerce-moip' ),
				'type'        => 'select',
				'description' => __( 'Select the payment methods', 'iugu-woocommerce' ),
				'default'     => 'form', // @TODO
				'options'     => array(
					'creditcard' => __( 'Creditcard Only', 'iugu-woocommerce' ),
					'billet'     => __( 'Billet only', 'iugu-woocommerce' ),
					'full'       => __( 'Creditcard And Billet', 'iugu-woocommerce' )
				)
			),
			'cc_maximum_split' => array(
				'title'       => __( 'Maximum Split', 'iugu-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'The maximum split allowed for credit cards. Put a number bigger than 1 to enable the field', 'iugu-woocommerce' ),
				'desc_tip'    => true,
				'default'     => '0'
			),
			'accid' => array(
				'title'       => __( 'Account ID', 'iugu-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Please enter your Account ID; this is needed in order to take payment.', 'iugu-woocommerce' ),
				'desc_tip'    => true,
				'default'     => ''
			),
			'key' => array(
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
	 * Some gateways like stripe don't need names as the form is tokenized
	 *
	 * @return array
	 */
	public function custom_credit_card_form_args() {
		$default_args = array(
			'fields_have_names' => true,
		);

		return $default_args;
	}

	/**
	 * Fields of the payment form
	 *
	 * @return multitype:string
	 */
	public function payment_fields() {

		wp_enqueue_script( 'wc-credit-card-form' );

		$default_args = array(
				'fields_have_names' => true, // Some gateways like stripe don't need names as the form is tokenized
		);

		$args = wp_parse_args( $args, apply_filters( 'woocommerce_credit_card_form_args', $default_args, $this->id ) );


		$options="";
		for ($i = 1; $i <= $this->cc_maximum_split; $i++) {
			$options .= '<option value="'.$i.'" '.(($i == 1)? "selected": '').'>'.$i.'x</option>';
		}

		$default_fields = array(
				'holder-name-field' => '<p class="form-row form-row-wide">
					<label for="' . esc_attr( $this->id ) . '-holder-name">' . __( 'Holder Name', 'iugu-woocommerce' ) . ' <span class="required">*</span></label>
					<input id="' . esc_attr( $this->id ) . '-holder-name" class="input-text wc-credit-card-form-holder-name" type="text" maxlength="100" autocomplete="off" placeholder="'.__('The name on creditcard','iugu-woocommerce').'" name="' . ( $args['fields_have_names'] ? $this->id . '-holder-name' : '' ) . '" />
				</p>',
				'card-number-field' => '<p class="form-row form-row-wide">
					<label for="' . esc_attr( $this->id ) . '-card-number">' . __( 'Card Number', 'iugu-woocommerce' ) . ' <span class="required">*</span></label>
					<input id="' . esc_attr( $this->id ) . '-card-number" class="input-text wc-credit-card-form-card-number" type="text" maxlength="20" autocomplete="off" placeholder="•••• •••• •••• ••••" name="' . ( $args['fields_have_names'] ? $this->id . '-card-number' : '' ) . '" />
				</p>',
				'card-expiry-field' => '<p class="form-row form-row-first">
					<label for="' . esc_attr( $this->id ) . '-card-expiry">' . __( 'Expiry (MM/YYYY)', 'iugu-woocommerce' ) . ' <span class="required">*</span></label>
					<input id="' . esc_attr( $this->id ) . '-card-expiry" class="input-text wc-credit-card-form-card-expiry" type="text" autocomplete="off" placeholder="' . __( 'MM / YYYY', 'iugu-woocommerce' ) . '" name="' . ( $args['fields_have_names'] ? $this->id . '-card-expiry' : '' ) . '" />
				</p>',
				'card-cvc-field' => '<p class="form-row form-row-last">
					<label for="' . esc_attr( $this->id ) . '-card-cvc">' . __( 'Card Code', 'iugu-woocommerce' ) . ' <span class="required">*</span></label>
					<input id="' . esc_attr( $this->id ) . '-card-cvc" class="input-text wc-credit-card-form-card-cvc" type="text" autocomplete="off" placeholder="' . __( 'CVC', 'iugu-woocommerce' ) . '" name="' . ( $args['fields_have_names'] ? $this->id . '-card-cvc' : '' ) . '" />
				</p>',
		);


		if($this->cc_maximum_split > 1){
			$fields['cc-split-field'] = '<p class="form-row form-row-first">
				<label for="' . esc_attr( $this->id ) . '-cc-split">' . __( 'Splits', 'iugu-woocommerce' ) . '</label>
				<select id="' . esc_attr( $this->id ) . '-cc-split" class="iugu-select-split wc-credit-card-form-cc-split" name="' . ( $args['fields_have_names'] ? $this->id . '-cc-split' : '' ) . '" >
					'.$options.'
				</select>
			</p>';
		}

		$fields = wp_parse_args( $fields, apply_filters( 'woocommerce_credit_card_form_fields', $default_fields, $this->id ) );

		?>
		<div id="iugu-checkout-customform">
			<div id="iugu-checkout-customform-navbar">
				<ul>
					<?php echo ( ($this->api == 'creditcard') ? '<li><a id="iugu-creditcard-navbutton" class="ui-btn-active creditcard-only form-row-wide" href="#">'.__('Payment with Creditcard','iugu-woocommerce').'</a></li>' : '' );?>
					<?php echo ( ($this->api == 'full') ? '<li><a id="iugu-creditcard-navbutton" class="ui-btn-active form-row-first" href="#">'.__('Creditcard','iugu-woocommerce').'</a></li>' : '' );?>

					<?php echo ( ($this->api == 'billet') ? '<li><a id="iugu-billet-navbutton" class="ui-btn-active billet-only form-row-wide" href="#">'.__('Payment with Billet','iugu-woocommerce').'</a></li>' : '' );?>
					<?php echo ( ($this->api == 'full') ? '<li><a id="iugu-billet-navbutton" class="form-row-last" href="#">'.__('Billet','iugu-woocommerce').'</a></li>' : '' );?>
				</ul>
			</div>

			<div id="iugu-creditcard-fieldset" <?php echo ( ($this->api == 'billet') ? 'style="display: none;"':'' );?>>
				<fieldset id="<?php echo $this->id; ?>-cc-form">
					<?php do_action( 'woocommerce_credit_card_form_start', $this->id ); ?>
					<?php
						foreach ( $fields as $field ) {
							echo $field;
						}
					?>
					<?php do_action( 'woocommerce_credit_card_form_end', $this->id ); ?>
					<div class="clear"></div>
				</fieldset>
			</div>
			<div id="iugu-billet-fieldset" <?php echo ( ($this->api == 'creditcard' || $this->api == 'full') ? 'style="display: none;"':'' );?>>
				<fieldset>
					<?php
					if ( $description = $this->get_description() ) {
						echo wpautop( wptexturize( $description ) );
					}
					?>
					<input id="iugu-payment-type" type="hidden" name="iugu-payment-type" value="<?php echo ( ($this->api != 'full') ? $this->api : 'creditcard' );?>">
				</fieldset>
			</div>
		</div>
		<?php
	}

	/**
	 * Displays notifications when the admin has something wrong with the configuration.
	 */
	protected function admin_notices() {
		if ( is_admin() ) {

			// Checks if token is not empty.
			if ( empty( $this->accid ) ) {
				add_action( 'admin_notices', array( $this, 'accid_missing_message' ) );
			}

			// Checks if key is not empty.
			if ( empty( $this->key ) ) {
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
	 * Make a first request to validate the credit card
	 *
	 * @param WC_Order $order Woocommerce order
	 * @return stdClass Result of Iugu request
	 */
	public function create_payment_token( $order ) {
		$url = "https://api.iugu.com/v1/payment_token";

		$card_number = $_POST [ $this->id . '-card-number' ];
		$cvc = $_POST [ $this->id . '-card-cvc' ];
		$expire_month = trim( explode ( '/', $_POST[ $this->id . '-card-expiry'] )[0] );
		$expire_year = trim( explode ( '/', $_POST[ $this->id . '-card-expiry'] )[1] );

		$holder_name;
		$first_name;
		$last_name;

		try {

			$holder_name = trim($_POST [$this->id . '-holder-name']);
			$first_name = trim(substr($holder_name, 0, strpos($holder_name, ' ')));
			$last_name = trim(substr($holder_name,  strrpos($holder_name, ' '), strlen($holder_name)));

		} catch (Exception $e) {

		}


		$data = array(
				'account_id' => $this->accid,
				'method' => "credit_card",
				'data' => array(
						'number' => $card_number,
						'verification_value' => $cvc,
						'first_name' => $first_name,
						'last_name' => $last_name,
						'month' => $expire_month,
						'year' => $expire_year
				)
		);

		$requestIugu = new Iugu_APIRequest();

		return $requestIugu->request ( "post", $url, $data );
	}

	/**
	 * Handle function to call Iugu APi and pay with creditcard
	 *
	 * @param stdClass $result Result of Iugu first request
	 * @param WC_Order $order Woocommerce order
	 * @return stdClass Result of Iugu payment request
	 */
	public function pay_with_creditcard($result, $order) {

		$split;
		$holder_name;

		try {

			$split = $_POST [$this->id . '-cc-split'];
			$holder_name = $_POST [$this->id . '-holder-name'];

		} catch (Exception $e) {

		}

		$order_items = $order->get_items ();
		$items = array();


		foreach ( $order_items as $items_key => $item ) {
			$items [] = Array (
					"description" => $item ['name'],
					"quantity" => $item ['qty'],
					"price_cents" => $this->price_cents_format ( $item ['line_total']/$item ['qty'] )
			);
		}

		$chargeToSend = Array (
				"token" => $result->id,
				"email" => $order->billing_email,
				"months" => $split,
				"items" => $items,
				"payer" => Array (
						"name" => $holder_name,
						"phone_prefix" => $order->billing_phone_prefix,
						"phone" => $order->billing_phone,
						"email" => $order->billing_email,
						"address" => Array (
								"street" => $order->billing_address_1,
								"number" => $order->billing_number,
								"city" => $order->billing_city,
								"state" => $order->billing_state,
								"country" => "Brasil",
								"zip_code" => $order->shipping_postcode
						)
				)
		);

		return Iugu_Charge::create ( $chargeToSend );
	}

	/**
	 *  Handle function to call Iugu APi and pay with billet
	 *
	 * @param WC_Order $order Woocommerce order
	 * @return stdClass Result of Iugu payment request
	 */
	public function pay_with_billet($order) {

		$order_items = $order->get_items ();

		$items = array();

		foreach ( $order_items as $items_key => $item ) {
			$items [] = Array (
					"description" => $item ['name'],
					"quantity" => $item ['qty'],
					"price_cents" => $this->price_cents_format ( $item ['line_total']/$item ['qty'] )
			);
		}

		$chargeToSend = Array (
				"method" => "bank_slip",
				"email" => $order->billing_email,
				"items" => $items,
				"payer" => Array (
						"name" => $order->billing_first_name . " " . $order->billing_last_name,
						"phone_prefix" => $order->billing_phone_prefix,
						"phone" => $order->billing_phone,
						"email" => $order->billing_email,
						"address" => Array (
								"street" => $order->billing_address_1,
								"number" => $order->billing_number,
								"city" => $order->billing_city,
								"state" => $order->billing_state,
								"country" => "Brasil",
								"zip_code" => $order->shipping_postcode
						)
				)
		);

		return Iugu_Charge::create ( $chargeToSend );
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param int $order_id Order ID.
	 *
	 * @return array Redirect.
	 */
	public function process_payment($order_id) {

		Iugu::setApiKey ( $this->key );

		// Get this Order's information so that we know
		// who to charge and how much
		$order = new WC_Order ( $order_id );

		// payment result
		$result = null;
		$this->iugu_payment_type = $_POST['iugu-payment-type'];


		// creditcard
		if ($this->iugu_payment_type == "creditcard") {

			$result = $this->create_payment_token ( $order );
			$result = $this->pay_with_creditcard ( $result, $order );
			$result = $this->credit_order_complete($result,$order);

			if(!is_null($result)){
				return $result;
			}

		// billet
		} elseif ($this->iugu_payment_type == "billet") {

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
	public function accid_missing_message() {
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
