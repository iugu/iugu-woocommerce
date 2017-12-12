<?php
/**
 * Plugin Name: Iugu WooCommerce
 * Plugin URI: https://github.com/iugu/iugu-woocommerce
 * Description: Iugu payment gateway for WooCommerce.
 * Author: iugu
 * Author URI: https://iugu.com/
 * Version: 1.0.14
 * License: GPLv2 or later
 * Text Domain: iugu-woocommerce
 * Domain Path: languages/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Iugu' ) ) :

/**
 * WooCommerce Iugu main class.
 */
class WC_Iugu {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	const CLIENT_NAME = 'plugin-iugu-woocommerce';
	const CLIENT_VERSION = '1.0.14';

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin actions.
	 */
	public function __construct() {
		// Load plugin text domain.
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Checks with WooCommerce and WooCommerce Extra Checkout Fields for Brazil is installed.
		if ( class_exists( 'WC_Payment_Gateway' ) && class_exists( 'Extra_Checkout_Fields_For_Brazil' ) ) {
			$this->includes();

			// Hook to add Iugu Gateway to WooCommerce.
			add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateway' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
		} else {
			add_action( 'admin_notices', array( $this, 'dependencies_notices' ) );
		}
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Get templates path.
	 *
	 * @return string
	 */
	public static function get_templates_path() {
		return plugin_dir_path( __FILE__ ) . 'templates/';
	}

	/**
	 * Load the plugin text domain for translation.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'iugu-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Includes.
	 */
	private function includes() {
		include_once 'includes/utils/chromephp.php';
		include_once 'includes/class-wc-iugu-api.php';
		include_once 'includes/class-wc-iugu-bank-slip-gateway.php';
		include_once 'includes/class-wc-iugu-credit-card-gateway.php';
		include_once 'includes/class-wc-iugu-my-account.php';

		if ( class_exists( 'WC_Subscriptions_Order' ) || class_exists( 'WC_Pre_Orders_Order' ) ) {
			// Subscriptions < 2.0.
			if ( ! function_exists( 'wcs_create_renewal_order' ) ) {
				include_once 'includes/class-wc-iugu-bank-slip-addons-gateway-deprecated.php';
				include_once 'includes/class-wc-iugu-credit-card-addons-gateway-deprecated.php';
			} else {
				include_once 'includes/class-wc-iugu-bank-slip-addons-gateway.php';
				include_once 'includes/class-wc-iugu-credit-card-addons-gateway.php';
			}
		}
	}

	/**
	 * Add the gateway to WooCommerce.
	 *
	 * @param  array $methods WooCommerce payment methods.
	 *
	 * @return array          Payment methods with Iugu.
	 */
	public function add_gateway( $methods ) {
		if ( class_exists( 'WC_Subscriptions_Order' ) || class_exists( 'WC_Pre_Orders_Order' ) ) {
			if ( ! function_exists( 'wcs_create_renewal_order' ) ) {
				$methods[] = 'WC_Iugu_Credit_Card_Addons_Gateway_Deprecated';
				$methods[] = 'WC_Iugu_Bank_Slip_Addons_Gateway_Deprecated';
			} else {
				$methods[] = 'WC_Iugu_Credit_Card_Addons_Gateway';
				$methods[] = 'WC_Iugu_Bank_Slip_Addons_Gateway';
			}
		} else {
			$methods[] = 'WC_Iugu_Credit_Card_Gateway';
			$methods[] = 'WC_Iugu_Bank_Slip_Gateway';
		}

		return $methods;
	}

	/**
	 * Dependencies notices.
	 */
	public function dependencies_notices() {
		if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
			include_once 'includes/views/html-notice-woocommerce-missing.php';
		}

		if ( ! class_exists( 'Extra_Checkout_Fields_For_Brazil' ) ) {
			include_once 'includes/views/html-notice-ecfb-missing.php';
		}
	}

	/**
	 * Get log.
	 *
	 * @return string
	 */
	public static function get_log_view( $gateway_id ) {
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.2', '>=' ) ) {
			return '<a href="' . esc_url( admin_url( 'admin.php?page=wc-status&tab=logs&log_file=' . esc_attr( $gateway_id ) . '-' . sanitize_file_name( wp_hash( $gateway_id ) ) . '.log' ) ) . '">' . __( 'System Status &gt; Logs', 'iugu-woocommerce' ) . '</a>';
		}

		return '<code>woocommerce/logs/' . esc_attr( $gateway_id ) . '-' . sanitize_file_name( wp_hash( $gateway_id ) ) . '.txt</code>';
	}

	/**
	 * Action links.
	 *
	 * @param  array $links
	 *
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$plugin_links = array();

		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.1', '>=' ) ) {
			$settings_url = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' );
		} else {
			$settings_url = admin_url( 'admin.php?page=woocommerce_settings&tab=payment_gateways&section=' );
		}

		if ( class_exists( 'WC_Subscriptions_Order' ) || class_exists( 'WC_Pre_Orders_Order' ) ) {
			if ( ! function_exists( 'wcs_create_renewal_order' ) ) {
				$credit_card = 'wc_iugu_credit_card_addons_gateway_deprecated';
				$bank_slip   = 'wc_iugu_bank_slip_addons_gateway_deprecated';
			} else {
				$credit_card = 'wc_iugu_credit_card_addons_gateway';
				$bank_slip   = 'wc_iugu_bank_slip_addons_gateway';
			}
		} else  {
			$credit_card = 'wc_iugu_credit_card_Gateway';
			$bank_slip   = 'wc_iugu_bank_slip_gateway';
		}

		$plugin_links[] = '<a href="' . esc_url( $settings_url . $credit_card ) . '">' . __( 'Credit Card Settings', 'iugu-woocommerce' ) . '</a>';

		$plugin_links[] = '<a href="' . esc_url( $settings_url . $bank_slip ) . '">' . __( 'Bank Slip Settings', 'iugu-woocommerce' ) . '</a>';

		return array_merge( $plugin_links, $links );
	}
}

add_action( 'plugins_loaded', array( 'WC_Iugu', 'get_instance' ) );

endif;
