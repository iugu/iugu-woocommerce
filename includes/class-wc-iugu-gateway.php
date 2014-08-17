<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WC Iugu Gateway Class.
 *
 * Built the Iugu method.
 */
class WC_Iugu_Gateway extends WC_Payment_Gateway {

	/**
	 * Constructor for the gateway.
	 *
	 * @return void
	 */
	public function __construct() {
		global $woocommerce;

		$this->id                 = 'iugu';
		$this->icon               = '';
		$this->has_fields         = true;
		$this->method_title       = __( 'Iugu', 'iugu-woocommerce' );
		$this->method_description = __( 'Accept payments by credit card or banking ticket using the Iugu.', 'iugu-woocommerce' );

		// Load the form fields.
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		// Define user set variables.
		$this->title           = $this->get_option( 'title' );
		$this->description     = $this->get_option( 'description' );
		$this->account_id      = $this->get_option( 'account_id' );
		$this->account_token   = $this->get_option( 'account_token' );
		$this->methods         = $this->get_option( 'methods' );
		$this->send_only_total = $this->get_option( 'send_only_total' );
		$this->invoice_prefix  = $this->get_option( 'invoice_prefix', 'WC-' );
		$this->sandbox         = $this->get_option( 'sandbox' );
		$this->debug           = $this->get_option( 'debug' );

		// Active logs.
		if ( 'yes' == $this->debug ) {
			if ( class_exists( 'WC_Logger' ) ) {
				$this->log = new WC_Logger();
			} else {
				$this->log = $woocommerce->logger();
			}
		}

		// Main actions.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/**
	 * Returns a bool that indicates if currency is amongst the supported ones.
	 *
	 * @return bool
	 */
	public function using_supported_currency() {
		return in_array( get_woocommerce_currency(), array( 'BRL' ) );
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
			'account_id' => array(
				'title'       => __( 'Iugu Account ID', 'iugu-woocommerce' ),
				'type'        => 'text',
				'description' => sprintf( __( 'Please enter your Iugu Account ID. This is needed to process the payments and notifications. Is possible get your Account ID %s.', 'iugu-woocommerce' ), '<a href="https://iugu.com/settings/account" target="_blank">' . __( 'here', 'iugu-woocommerce' ) . '</a>' ),
				'default'     => ''
			),
			'account_token' => array(
				'title'       => __( 'Iugu Account API Token', 'iugu-woocommerce' ),
				'type'        => 'text',
				'description' => sprintf( __( 'Please enter your Iugu Account API Token. This is needed to process the payments and notifications. Is possible generate a new Account API Token %s.', 'iugu-woocommerce' ), '<a href="https://iugu.com/settings/account" target="_blank">' . __( 'here', 'iugu-woocommerce' ) . '</a>' ),
				'default'     => ''
			),
			'methods' => array(
				'title'       => __( 'Payment Methods', 'iugu-woocommerce' ),
				'type'        => 'select',
				'description' => __( 'Choose how payments methods will be available.', 'iugu-woocommerce' ),
				'desc_tip'    => true,
				'default'     => 'all',
				'options'     => array(
					'all'         => __( 'Credit Card and Bank Slip', 'iugu-woocommerce' ),
					'credit_card' => __( 'Credit Card', 'iugu-woocommerce' ),
					'bank_slip'   => __( 'Bank Slip', 'iugu-woocommerce' )
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
			'invoice_prefix' => array(
				'title'       => __( 'Invoice Prefix', 'iugu-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Please enter a prefix for your invoice numbers. If you use your Iugu account for multiple stores ensure this prefix is unqiue as Iugu will not allow orders with the same invoice number.', 'iugu-woocommerce' ),
				'desc_tip'    => true,
				'default'     => 'WC-'
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
				'description' => sprintf( __( 'Iugu Sandbox can be used to test the payments. <strong>Note:</strong> you must use a Account Sandbox API Token that can be found in %s.', 'iugu-woocommerce' ), '<a href="https://iugu.com/settings/account" target="_blank">' . __( 'here', 'iugu-woocommerce' ) .'</a>' )
			),
			'debug' => array(
				'title'       => __( 'Debug Log', 'iugu-woocommerce' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable logging', 'iugu-woocommerce' ),
				'default'     => 'no',
				'description' => sprintf( __( 'Log Iugu events, such as API requests, inside %s', 'iugu-woocommerce' ), '<code>woocommerce/logs/' . esc_attr( $this->id ) . '-' . sanitize_file_name( wp_hash( $this->id ) ) . '.txt</code>' )
			)
		);
	}

}
