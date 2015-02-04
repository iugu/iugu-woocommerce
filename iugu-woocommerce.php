<?php
/**
 * Plugin Name: Iugu WooCommerce
 * Plugin URI: https://github.com/iugu/iugu-woocommerce
 * Description: Gateway de pagamento Iugu para WooCommerce.
 * Author: Iugu
 * Author URI: http://iugu.com/
 * Version: 1.0.0
 * License: GPLv2 or later
 * Text Domain: iugu-woocommerce
 * Domain Path: /languages/
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
	const VERSION = '1.0.0';

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

		// Links for reach the setting page from plugin list.
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );

		// Checks with WooCommerce and WooCommerce Extra Checkout Fields for Brazil is installed.
		if ( class_exists( 'WC_Payment_Gateway' ) && class_exists( 'Extra_Checkout_Fields_For_Brazil' ) ) {

			// Include the WC_Iugu_Gateway class.
			include_once 'includes/iuguApi/lib/Iugu.php';
			include_once 'includes/class-wc-iugu-api.php';
			include_once 'includes/class-wc-iugu-gateway.php';

			// Hook to add Iugu Gateway to WooCommerce.
			add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateway' ) );

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
		$locale = apply_filters( 'plugin_locale', get_locale(), 'iugu-woocommerce' );

		load_textdomain( 'iugu-woocommerce', trailingslashit( WP_LANG_DIR ) . 'iugu-woocommerce/iugu-woocommerce-' . $locale . '.mo' );
		load_plugin_textdomain( 'iugu-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Add the gateway to WooCommerce.
	 *
	 * @param  array $methods WooCommerce payment methods.
	 *
	 * @return array          Payment methods with Iugu.
	 */
	public function add_gateway( $methods ) {
		$methods[] = 'WC_Iugu_Gateway';

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
	 * Hooked function to create a link to settings from plugins list.
	 *
	 * @param  array $links
	 *
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$plugin_links = array();

		if ( class_exists( 'WC_Payment_Gateway' ) ) {
			$plugin_links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_iugu_gateway' ) . '">' . __( 'Settings', 'iugu-woocommerce' ) . '</a>';
		}

		return array_merge( $plugin_links, $links );
	}
}

add_action( 'plugins_loaded', array( 'WC_Iugu', 'get_instance' ) );

endif;
