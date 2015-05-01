<?php
/**
 * Admin options screen.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! 'BRL' == get_woocommerce_currency() && ! class_exists( 'woocommerce_wpml' ) ) {
	include 'html-notice-currency-not-supported.php';
}

if ( empty( $this->account_id ) ) {
	include 'html-notice-account-id-missing.php';
}

if ( empty( $this->api_token ) ) {
	include 'html-notice-api-token-missing.php';
}

?>

<h3><?php echo $this->method_title ?></h3>

<?php echo wpautop( $this->method_description ); ?>

<table class="form-table">
	<?php $this->generate_settings_html(); ?>
</table>
