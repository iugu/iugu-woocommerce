<?php
/**
 * WC Iugu API Class.
 */
class WC_Iugu_API {

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
	 * Get the API environment.
	 *
	 * @return string
	 */
	protected function get_environment() {
		return ( 'yes' == $this->gateway->sandbox ) ? 'sandbox.' : '';
	}
}
