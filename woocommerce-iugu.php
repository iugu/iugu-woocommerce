<?php
/**
 * Plugin Name: WooCommerce Iugu
 * Plugin URI: 
 * Description: Gateway de pagamento Iugu para WooCommerce.
 * Author: Braising
 * Author URI: http://braising.com.br
 * Version: 1.0.0
 * License: GPLv2 or later
 * Text Domain: woocommerce-iugu
 * Domain Path: /languages/
 */

if (! defined ( 'ABSPATH' )) {
	exit (); // Exit if accessed directly.
}

if (! class_exists ( 'WC_Iugu' )) :
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
		 * Integration id.
		 *
		 * @var string
		 */
		protected static $gateway_id = 'iugu';
		
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
			// Load plugin text domain
			add_action ( 'init', array ( $this, 'load_plugin_textdomain'  ) );
			
			// Checks with WooCommerce is installed.
			if (class_exists ( 'WC_Payment_Gateway' )) {
				
				// Include the WC_Iugu_Gateway class.
				include_once 'includes/iuguApi/lib/Iugu.php';
				include_once 'includes/class-wc-iugu-gateway.php';
				include_once 'includes/iugu-checkout-custom-fields.php';
				
				// Links for reach the setting page from plugin list
				add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array($this,'woocommerce_iugu_action_links') );
				
				//Hook to add Iugu Gateway to WooCommerce
				add_filter ( 'woocommerce_payment_gateways', array ( $this, 'add_gateway' ) );
				
			} else {
				
				//Notifications
				add_action ( 'admin_notices', array ( $this, 'woocommerce_missing_notice' ) );
			}
		}
		
		/**
		 * Return an instance of this class.
		 *
		 * @return object A single instance of this class.
		 */
		public static function get_instance() {
			// If the single instance hasn't been set, set it now.
			if (null == self::$instance) {
				self::$instance = new self ();
			}
			
			return self::$instance;
		}
		
		/**
		 * Return the gateway id/slug.
		 *
		 * @return string Gateway id/slug variable.
		 */
		public static function get_gateway_id() {
			return self::$gateway_id;
		}
		
		/**
		 * Load the plugin text domain for translation.
		 *
		 * @return void
		 */
		public function load_plugin_textdomain() {
			$locale = apply_filters ( 'plugin_locale', get_locale (), 'woocommerce-iugu' );
			load_textdomain ( 'woocommerce-iugu', trailingslashit ( WP_LANG_DIR ) . 'woocommerce-iugu/woocommerce-iugu-' . $locale . '.mo' );
			load_plugin_textdomain ( 'woocommerce-iugu', false, dirname ( plugin_basename ( __FILE__ ) ) . '/languages/' );
		}
		
		/**
		 * Add the gateway to WooCommerce.
		 *
		 * @param array $methods WooCommerce payment methods.
		 *        	
		 * @return array Payment methods with Iugu.
		 */
		public function add_gateway($methods) {
			$methods [] = 'WC_Iugu_Gateway';
			
			return $methods;
		}
		
		/**
		 * WooCommerce fallback notice.
		 *
		 * @return string
		 */
		public function woocommerce_missing_notice() {
			echo '<div class="error"><p>' . sprintf ( __ ( 'WooCommerce Iugu Gateway depends on the last version of %s to work!', 'woocommerce-iugu' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a>' ) . '</p></div>';
		}
		
		
		/**
		 * Hooked function to create a link to settings from plugins list
		 * @param array $links
		 * @return multitype
		 */
		function woocommerce_iugu_action_links( $links ) {
			$plugin_links = array(
					'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_iugu_gateway' ) . '">' . __( 'Settings', 'woocommerce-iugu' ) . '</a>',
			);
			
			// Merge our new link with the default ones
			return array_merge( $plugin_links, $links );
		}
	}
	
	//Instantiate the plugin
	add_action ( 'plugins_loaded', array ( 'WC_Iugu', 'get_instance'  ), 0 );

endif;