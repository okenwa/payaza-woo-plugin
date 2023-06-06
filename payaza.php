<?php
/**
 * Plugin Name: Payaza
 * Plugin URI: https://payaza.africa
 * Description: WooCommerce payment gateway for payaza
 * Version: 0.1.0
 * Author: Payaza 
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * WC requires at least: 6.1
 * WC tested up to: 6.9
 * Text Domain: woo-payaza
 * Domain Path: /languages

 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WC_PAYAZA_MAIN_FILE', __FILE__ );
define( 'WC_PAYAZA_URL', untrailingslashit( plugins_url( '/', __FILE__ ) ) );

define( 'WC_PAYAZA_VERSION', '0.1.0' );

/**
 * Initialize payaza gateway.
 */
function paz_wc_payaza_init() {

	load_plugin_textdomain( 'woo-payaza', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );

	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		add_action( 'admin_notices', 'paz_wc_payaza_wc_missing_notice' );
		return;
	}

	add_action( 'admin_notices', 'paz_wc_payaza_testmode_notice' );

	require_once dirname( __FILE__ ) . '/includes/class-wc-gateway-payaza.php';


	add_filter( 'woocommerce_payment_gateways', 'paz_wc_add_payaza_gateway', 99 );

	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'paz_woo_payaza_plugin_action_links' );

}
add_action( 'plugins_loaded', 'paz_wc_payaza_init', 99 );

/**
 * Add Settings link to the plugin entry in the plugins menu.
 *
 * @param array $links Plugin action links.
 *
 * @return array
 **/
function paz_woo_payaza_plugin_action_links( $links ) {

	$settings_link = array(
		'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=payaza' ) . '" title="' . __( 'View payaza WooCommerce Settings', 'woo-payaza' ) . '">' . __( 'Settings', 'woo-payaza' ) . '</a>',
	);

	return array_merge( $settings_link, $links );

}

/**
 * Add payaza Gateway to WooCommerce.
 *
 * @param array $methods WooCommerce payment gateways methods.
 *
 * @return array
 */
function paz_wc_add_payaza_gateway( $methods ) {

	if ( class_exists( 'WC_Payment_Gateway_CC' ) ) {
		$methods[] = 'WC_Gateway_Payaza';
	}

	if ( 'NGN' === get_woocommerce_currency() ) {

		$settings        = get_option( 'woocommerce_payaza_settings', '' );
		$custom_gateways = isset( $settings['custom_gateways'] ) ? $settings['custom_gateways'] : '';

	}

	return $methods;

}

/**
 * Display a notice if WooCommerce is not installed
 */
function paz_wc_payaza_wc_missing_notice() {
	echo '<div class="error"><p><strong>' . sprintf( __( 'Payaza requires WooCommerce to be installed and active. Click %s to install WooCommerce.', 'woo-payaza' ), '<a href="' . admin_url( 'plugin-install.php?tab=plugin-information&plugin=woocommerce&TB_iframe=true&width=772&height=539' ) . '" class="thickbox open-plugin-details-modal">here</a>' ) . '</strong></p></div>';
}

/**
 * Display the test mode notice.
 **/
function paz_wc_payaza_testmode_notice() {

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$payaza_settings = get_option( 'woocommerce_payaza_settings' );
	$test_mode         = isset( $payaza_settings['testmode'] ) ? $payaza_settings['testmode'] : '';

	if ( 'yes' === $test_mode ) {
		/* translators: 1. payaza settings page URL link. */
		echo '<div class="error"><p>' . sprintf( __( 'payaza test mode is still enabled, Click <strong><a href="%s">here</a></strong> to disable it when you want to start accepting live payment on your site.', 'woo-payaza' ), esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=payaza' ) ) ) . '</p></div>';
	}
}