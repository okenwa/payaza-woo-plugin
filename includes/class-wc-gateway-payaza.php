<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Gateway_Payaza extends WC_Payment_Gateway_CC {

	/**
	 * Is test mode active?
	 *
	 * @var bool
	 */
	public $testmode;

	/**
	 * Should orders be marked as complete after payment?
	 * 
	 * @var bool
	 */
	public $autocomplete_order;

	/**
	 * Payaza payment page type.
	 *
	 * @var string
	 */
	public $payment_page;

	/**
	 * Payaza test public key.
	 *
	 * @var string
	 */
	public $test_public_key;

	/**
	 * Payaza test secret key.
	 *
	 * @var string
	 */
	public $test_secret_key;

	/**
	 * Payaza live public key.
	 *
	 * @var string
	 */
	public $live_public_key;

	/**
	 * Payaza live secret key.
	 *
	 * @var string
	 */
	public $live_secret_key;

	/**
	 * Should we save customer cards?
	 *
	 * @var bool
	 */
	public $saved_cards;

	/**
	 * Should Payaza split payment be enabled.
	 *
	 * @var bool
	 */
	public $split_payment;

	/**
	 * Should the cancel & remove order button be removed on the pay for order page.
	 *
	 * @var bool
	 */
	public $remove_cancel_order_button;

	/**
	 * Payaza sub account code.
	 *
	 * @var string
	 */
	public $subaccount_code;

	/**
	 * Who bears Payaza charges?
	 *
	 * @var string
	 */
	public $charges_account;

	/**
	 * A flat fee to charge the sub account for each transaction.
	 *
	 * @var string
	 */
	public $transaction_charges;

	/**
	 * Should custom metadata be enabled?
	 *
	 * @var bool
	 */
	public $custom_metadata;

	/**
	 * Should the order id be sent as a custom metadata to Payaza?
	 *
	 * @var bool
	 */
	public $meta_order_id;

	/**
	 * Should the customer name be sent as a custom metadata to Payaza?
	 *
	 * @var bool
	 */
	public $meta_name;

	/**
	 * Should the billing email be sent as a custom metadata to Payaza?
	 *
	 * @var bool
	 */
	public $meta_email;

	/**
	 * Should the billing phone be sent as a custom metadata to Payaza?
	 *
	 * @var bool
	 */
	public $meta_phone;

	/**
	 * Should the billing address be sent as a custom metadata to Payaza?
	 *
	 * @var bool
	 */
	public $meta_billing_address;

	/**
	 * Should the shipping address be sent as a custom metadata to Payaza?
	 *
	 * @var bool
	 */
	public $meta_shipping_address;

	/**
	 * Should the order items be sent as a custom metadata to Payaza?
	 *
	 * @var bool
	 */
	public $meta_products;

	/**
	 * API public key
	 *
	 * @var string
	 */
	public $public_key;

	/**
	 * API secret key
	 *
	 * @var string
	 */
	public $secret_key;

	/**
	 * Gateway disabled message
	 *
	 * @var string
	 */
	public $msg;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id                 = 'payaza';
		$this->method_title       = __( 'Payaza', 'woo-payaza' );
		$this->method_description = sprintf( __( 'Payaza checkout is the easiest way to collect payments from your customers anywhere in the world. On web and mobile.  <a href="%1$s" target="_blank">Sign up</a> for a Payaza account, and <a href="%2$s" target="_blank">get your API keys</a>.', 'woo-payaza' ), 'https://payaza.africa', 'https://payaza.africa/login' );
		$this->has_fields         = true;

		$this->payment_page = $this->get_option( 'payment_page' );

		$this->supports = array(
			'products',
			'refunds',
			'tokenization',
			'subscriptions',
			'multiple_subscriptions',
			'subscription_cancellation',
			'subscription_suspension',
			'subscription_reactivation',
			'subscription_amount_changes',
			'subscription_date_changes',
			'subscription_payment_method_change',
			'subscription_payment_method_change_customer',
		);

		// Load the form fields
		$this->init_form_fields();

		// Load the settings
		$this->init_settings();

		// Get setting values

		$this->title              = $this->get_option( 'title' );
		$this->description        = $this->get_option( 'description' );
		$this->enabled            = $this->get_option( 'enabled' );
		$this->testmode           = $this->get_option( 'testmode' ) === 'yes' ? true : false;
		$this->autocomplete_order = $this->get_option( 'autocomplete_order' ) === 'yes' ? true : false;

		$this->test_public_key = $this->get_option( 'test_public_key' );
		$this->test_secret_key = $this->get_option( 'test_secret_key' );

		$this->live_public_key = $this->get_option( 'live_public_key' );
		$this->live_secret_key = $this->get_option( 'live_secret_key' );

		$this->saved_cards = $this->get_option( 'saved_cards' ) === 'yes' ? true : false;

		$this->split_payment              = $this->get_option( 'split_payment' ) === 'yes' ? true : false;
		$this->remove_cancel_order_button = $this->get_option( 'remove_cancel_order_button' ) === 'yes' ? true : false;
		$this->subaccount_code            = $this->get_option( 'subaccount_code' );
		$this->charges_account            = $this->get_option( 'split_payment_charge_account' );
		$this->transaction_charges        = $this->get_option( 'split_payment_transaction_charge' );

		$this->custom_metadata = $this->get_option( 'custom_metadata' ) === 'yes' ? true : false;

		$this->meta_order_id         = $this->get_option( 'meta_order_id' ) === 'yes' ? true : false;
		$this->meta_name             = $this->get_option( 'meta_name' ) === 'yes' ? true : false;
		$this->meta_email            = $this->get_option( 'meta_email' ) === 'yes' ? true : false;
		$this->meta_phone            = $this->get_option( 'meta_phone' ) === 'yes' ? true : false;
		$this->meta_billing_address  = $this->get_option( 'meta_billing_address' ) === 'yes' ? true : false;
		$this->meta_shipping_address = $this->get_option( 'meta_shipping_address' ) === 'yes' ? true : false;
		$this->meta_products         = $this->get_option( 'meta_products' ) === 'yes' ? true : false;

		$this->public_key = $this->testmode ? $this->test_public_key : $this->live_public_key;
		$this->secret_key = $this->testmode ? $this->test_secret_key : $this->live_secret_key;

		// Hooks
		add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action(
			'woocommerce_update_options_payment_gateways_' . $this->id,
			array(
				$this,
				'process_admin_options',
			)
		);

		add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );

		// Payment listener/API hook.
		add_action( 'woocommerce_api_wc_gateway_payaza', array( $this, 'verify_payaza_transaction' ) );

		// Webhook listener/API hook.
		add_action( 'woocommerce_api_tbz_wc_payaza_webhook', array( $this, 'process_webhooks' ) );

		// Check if the gateway can be used.
		if ( ! $this->is_valid_for_use() ) {
			$this->enabled = false;
		}

	}

	/**
	 * Check if this gateway is enabled and available in the user's country.
	 */
	public function is_valid_for_use() {

		if ( ! in_array( get_woocommerce_currency(), apply_filters( 'woocommerce_payaza_supported_currencies', array( 'NGN', 'USD' ) ) ) ) {

			$this->msg = sprintf( __( 'Payaza does not support your store currency. Kindly set it to either NGN (&#8358), USD (&#36;),<a href="%s">here</a>', 'woo-payaza' ), admin_url( 'admin.php?page=wc-settings&tab=general' ) );

			return false;

		}

		return true;

	}

	/**
	 * Display payaza payment icon.
	 */
	//public function get_icon() {

	//	$base_location = wc_get_base_location();

	//	if ( 'GH' === $base_location['country'] ) {
		//	$icon = '<img src="' . WC_HTTPS::force_https_url( plugins_url( 'assets/images/payaza-gh.png', WC_PAYaza_MAIN_FILE ) ) . '" alt="Payaza Payment Options" />';
	//	} elseif ( 'ZA' === $base_location['country'] ) {
		//	$icon = '<img src="' . WC_HTTPS::force_https_url( plugins_url( 'assets/images/payaza-za.png', WC_PAYaza_MAIN_FILE ) ) . '" alt="Payaza Payment Options" />';
		//} elseif ( 'KE' === $base_location['country'] ) {
		//	$icon = '<img src="' . WC_HTTPS::force_https_url( plugins_url( 'assets/images/payaza-ke.png', WC_PAYaza_MAIN_FILE ) ) . '" alt="Payaza Payment Options" />';
	//	} else {
		//	$icon = '<img src="' . WC_HTTPS::force_https_url( plugins_url( 'assets/images/payaza-wc.png', WC_PAYaza_MAIN_FILE ) ) . '" alt="Payaza Payment Options" />';
		//}

		//return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );

	//}
   
	/**
	 * Check if Payaza merchant details is filled.
	 */
	public function admin_notices() {

		if ( $this->enabled == 'no' ) {
			return;
		}

		// Check required fields.
		if ( ! ( $this->public_key && $this->secret_key ) ) {
			echo '<div class="error"><p>' . sprintf( __( 'Please enter your Payaza merchant details <a href="%s">here</a> to be able to use the Payaza WooCommerce plugin.', 'woo-payaza' ), admin_url( 'admin.php?page=wc-settings&tab=checkout&section=payaza' ) ) . '</p></div>';
			return;
		}

	}

	/**
	 * Check if Payaza gateway is enabled.
	 *
	 * @return bool
	 */
	public function is_available() {

		if ( 'yes' == $this->enabled ) {

			if ( ! ( $this->public_key && $this->secret_key ) ) {

				return false;

			}

			return true;

		}

		return false;

	}

	/**
	 * Admin Panel Options.
	 */
	public function admin_options() {

		?>

		<h2><?php _e( 'Payaza', 'woo-payaza' ); ?>
		<?php
		if ( function_exists( 'wc_back_link' ) ) {
			wc_back_link( __( 'Return to payments', 'woo-payaza' ), admin_url( 'admin.php?page=wc-settings&tab=checkout' ) );
		}
		?>
		</h2>

		<h4>
			<strong><?php printf( __( 'Optional: To avoid situations where bad network makes it impossible to verify transactions, set your webhook URL <a href="%1$s" target="_blank" rel="noopener noreferrer">here</a> to the URL below<span style="color: red"><pre><code>%2$s</code></pre></span>', 'woo-payaza' ), 'https://payaza.africa', WC()->api_request_url( 'Tbz_WC_Payaza_Webhook' ) ); ?></strong>
		</h4>

		<?php

		if ( $this->is_valid_for_use() ) {

			echo '<table class="form-table">';
			$this->generate_settings_html();
			echo '</table>';

		} else {
			?>
			<div class="inline error"><p><strong><?php _e( 'Payaza Payment Gateway Disabled', 'woo-payaza' ); ?></strong>: <?php echo $this->msg; ?></p></div>

			<?php
		}

	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {

		$form_fields = array(
			'enabled'                          => array(
				'title'       => __( 'Enable/Disable', 'woo-payaza' ),
				'label'       => __( 'Enable Payaza', 'woo-payaza' ),
				'type'        => 'checkbox',
				'description' => __( 'Enable Payaza as a payment option on the checkout page.', 'woo-payaza' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'title'                            => array(
				'title'       => __( 'Title', 'woo-payaza' ),
				'type'        => 'text',
				'description' => __( 'This controls the payment method title which the user sees during checkout.', 'woo-payaza' ),
				'default'     => __( 'Debit/Credit Cards', 'woo-payaza' ),
				'desc_tip'    => true,
			),
			'description'                      => array(
				'title'       => __( 'Description', 'woo-payaza' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the payment method description which the user sees during checkout.', 'woo-payaza' ),
				'default'     => __( 'Make payment using your debit and credit cards', 'woo-payaza' ),
				'desc_tip'    => true,
			),
			'testmode'                         => array(
				'title'       => __( 'Test mode', 'woo-payaza' ),
				'label'       => __( 'Enable Test Mode', 'woo-payaza' ),
				'type'        => 'checkbox',
				'description' => __( 'Test mode enables you to test payments before going live. <br />Once the LIVE MODE is enabled on your Payaza account uncheck this.', 'woo-payaza' ),
				'default'     => 'yes',
				'desc_tip'    => true,
			),
			'payment_page'                     => array(
				'title'       => __( 'Payment Option', 'woo-payaza' ),
				'label'		  => __('Enable Checkout'),
				'type'        => 'checkbox',
				'description' => __( 'Checkout will redirect the customer to Payaza to make payment.', 'woo-payaza' ),
				'default'     => 'yes',

				//'options'     => array(
				//	''          => __( 'Select One', 'woo-payaza' ),
				//	'redirect'  => __( 'Checkout', 'woo-payaza' ),
				//),
			),
			'test_secret_key'                  => array(
				'title'       => __( 'Test Secret Key', 'woo-payaza' ),
				'type'        => 'password',
				'description' => __( 'Enter your Test Secret Key here', 'woo-payaza' ),
				'default'     => '',
			),
			'test_public_key'                  => array(
				'title'       => __( 'Test Public Key', 'woo-payaza' ),
				'type'        => 'text',
				'description' => __( 'Enter your Test Public Key here.', 'woo-payaza' ),
				'default'     => '',
			),
			'live_secret_key'                  => array(
				'title'       => __( 'Live Secret Key', 'woo-payaza' ),
				'type'        => 'password',
				'description' => __( 'Enter your Live Secret Key here.', 'woo-payaza' ),
				'default'     => '',
			),
			'live_public_key'                  => array(
				'title'       => __( 'Live Public Key', 'woo-payaza' ),
				'type'        => 'text',
				'description' => __( 'Enter your Live Public Key here.', 'woo-payaza' ),
				'default'     => '',
			),
			'autocomplete_order'               => array(
				'title'       => __( 'Autocomplete Order After Payment', 'woo-payaza' ),
				'label'       => __( 'Autocomplete Order', 'woo-payaza' ),
				'type'        => 'checkbox',
				'class'       => 'wc-payaza-autocomplete-order',
				'description' => __( 'If enabled, the order will be marked as complete after successful payment', 'woo-payaza' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'remove_cancel_order_button'       => array(
				'title'       => __( 'Remove Cancel Order & Restore Cart Button', 'woo-payaza' ),
				'label'       => __( 'Remove the cancel order & restore cart button on the pay for order page', 'woo-payaza' ),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no',
			),
			
			//'custom_gateways'                  => array(
			//	'title'       => __( 'Additional Payaza Gateways', 'woo-payaza' ),
			//	'type'        => 'select',
			//	'description' => __( 'Create additional custom Payaza based gateways. This allows you to create additional Payaza gateways using custom filters. You can create a gateway that accepts only verve cards, a gateway that accepts only bank payment, a gateway that accepts a specific bank issued cards.', 'woo-payaza' ),
			//	'default'     => '',
			//	'desc_tip'    => true,
			//	'options'     => array(
			//		''  => __( 'Select One', 'woo-payaza' ),
			//		'1' => __( '1 gateway', 'woo-payaza' ),
			//	),
			//),
			'saved_cards'                      => array(
				'title'       => __( 'Saved Cards', 'woo-payaza' ),
				'label'       => __( 'Enable Payment via Saved Cards', 'woo-payaza' ),
				'type'        => 'checkbox',
				'description' => __( 'If enabled, users will be able to pay with a saved card during checkout. Card details are saved on Payaza servers, not on your store.<br>Note that you need to have a valid SSL certificate installed.', 'woo-payaza' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'custom_metadata'                  => array(
				'title'       => __( 'Custom Metadata', 'woo-payaza' ),
				'label'       => __( 'Enable Custom Metadata', 'woo-payaza' ),
				'type'        => 'checkbox',
				'class'       => 'wc-payaza-metadata',
				'description' => __( 'If enabled, you will be able to send more information about the order to Payaza.', 'woo-payaza' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'meta_order_id'                    => array(
				'title'       => __( 'Order ID', 'woo-payaza' ),
				'label'       => __( 'Send Order ID', 'woo-payaza' ),
				'type'        => 'checkbox',
				'class'       => 'wc-payaza-meta-order-id',
				'description' => __( 'If checked, the Order ID will be sent to Payaza', 'woo-payaza' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'meta_name'                        => array(
				'title'       => __( 'Customer Name', 'woo-payaza' ),
				'label'       => __( 'Send Customer Name', 'woo-payaza' ),
				'type'        => 'checkbox',
				'class'       => 'wc-payaza-meta-name',
				'description' => __( 'If checked, the customer full name will be sent to Payaza', 'woo-payaza' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'meta_email'                       => array(
				'title'       => __( 'Customer Email', 'woo-payaza' ),
				'label'       => __( 'Send Customer Email', 'woo-payaza' ),
				'type'        => 'checkbox',
				'class'       => 'wc-payaza-meta-email',
				'description' => __( 'If checked, the customer email address will be sent to Payaza', 'woo-payaza' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'meta_phone'                       => array(
				'title'       => __( 'Customer Phone', 'woo-payaza' ),
				'label'       => __( 'Send Customer Phone', 'woo-payaza' ),
				'type'        => 'checkbox',
				'class'       => 'wc-payaza-meta-phone',
				'description' => __( 'If checked, the customer phone will be sent to Payaza', 'woo-payaza' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'meta_billing_address'             => array(
				'title'       => __( 'Order Billing Address', 'woo-payaza' ),
				'label'       => __( 'Send Order Billing Address', 'woo-payaza' ),
				'type'        => 'checkbox',
				'class'       => 'wc-payaza-meta-billing-address',
				'description' => __( 'If checked, the order billing address will be sent to Payaza', 'woo-payaza' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'meta_shipping_address'            => array(
				'title'       => __( 'Order Shipping Address', 'woo-payaza' ),
				'label'       => __( 'Send Order Shipping Address', 'woo-payaza' ),
				'type'        => 'checkbox',
				'class'       => 'wc-payaza-meta-shipping-address',
				'description' => __( 'If checked, the order shipping address will be sent to Payaza', 'woo-payaza' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'meta_products'                    => array(
				'title'       => __( 'Product(s) Purchased', 'woo-payaza' ),
				'label'       => __( 'Send Product(s) Purchased', 'woo-payaza' ),
				'type'        => 'checkbox',
				'class'       => 'wc-payaza-meta-products',
				'description' => __( 'If checked, the product(s) purchased will be sent to Payaza', 'woo-payaza' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
		);

		if ( 'NGN' !== get_woocommerce_currency() ) {
			unset( $form_fields['custom_gateways'] );
		}

		$this->form_fields = $form_fields;

	}

	/**
	 * Payment form on checkout page
	 */
	public function payment_fields() {

		if ( $this->description ) {
			echo wpautop( wptexturize( $this->description ) );
		}

		if ( ! is_ssl() ) {
			return;
		}

		if ( $this->supports( 'tokenization' ) && is_checkout() && $this->saved_cards && is_user_logged_in() ) {
			$this->tokenization_script();
			$this->saved_payment_methods();
			$this->save_payment_method_checkbox();
		}

	}

	/**
	 * Outputs scripts used for payaza payment.
	 */
	public function payment_scripts() {

		if ( isset( $_GET['pay_for_order'] ) || ! is_checkout_pay_page() ) {
			return;
		}

		if ( $this->enabled === 'no' ) {
			return;
		}

		$order_key = urldecode( $_GET['key'] );
		$order_id  = absint( get_query_var( 'order-pay' ) );

		$order = wc_get_order( $order_id );

		if ( $this->id !== $order->get_payment_method() ) {
			return;
		}

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_enqueue_script( 'jquery' );

		wp_enqueue_script( 'payaza', 'https://checkout.payaza.africa/js/v1/bundle.js', array( 'jquery' ), WC_PAYAZA_VERSION, false );
		//wp_enqueue_script( 'wc_payaza', plugins_url( 'assets/js/paza' . $suffix . '.js', WC_PAYaza_MAIN_FILE ), array( 'jquery', 'payaza' ), WC_PAYaza_VERSION, false );

		wp_enqueue_script( 'wc_payaza', plugins_url( 'assets/js/payaza' . $suffix . '.js', WC_PAYAZA_MAIN_FILE ), array( 'jquery', 'payaza' ), WC_PAYAZA_VERSION, false );

		$payaza_params = array(
			'key' => $this->public_key,
		);

		if ( is_checkout_pay_page() && get_query_var( 'order-pay' ) ) {

			$email         = $order->get_billing_email();
			$amount        = $order->get_total() * 100;
			$txnref        = $order_id . '_' . time();
			$the_order_id  = $order->get_id();
			$the_order_key = $order->get_order_key();
			$currency      = $order->get_currency();

			if ( $the_order_id == $order_id && $the_order_key == $order_key ) {

				$payaza_params['email']    = $email;
				$payaza_params['amount']   = $amount;
				$payaza_params['txnref']   = $txnref;
				$payaza_params['currency'] = $currency;

			}

			//if ( $this->split_payment ) {

			//	$payaza_params['subaccount_code'] = $this->subaccount_code;
			//	$payaza_params['charges_account'] = $this->charges_account;

			//	if ( empty( $this->transaction_charges ) ) {
			//		$payaza_params['transaction_charges'] = '';
			//	} else {
			//		$payaza_params['transaction_charges'] = $this->transaction_charges * 100;
			//	}
			//}

			if ( $this->custom_metadata ) {

				if ( $this->meta_order_id ) {

					$payaza_params['meta_order_id'] = $order_id;

				}

				if ( $this->meta_name ) {

					$payaza_params['meta_name'] = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();

				}

				if ( $this->meta_email ) {

					$payaza_params['meta_email'] = $email;

				}

				if ( $this->meta_phone ) {

					$payaza_params['meta_phone'] = $order->get_billing_phone();

				}

				if ( $this->meta_products ) {

					$line_items = $order->get_items();

					$products = '';

					foreach ( $line_items as $item_id => $item ) {
						$name      = $item['name'];
						$quantity  = $item['qty'];
						$products .= $name . ' (Qty: ' . $quantity . ')';
						$products .= ' | ';
					}

					$products = rtrim( $products, ' | ' );

					$payaza_params['meta_products'] = $products;

				}

				if ( $this->meta_billing_address ) {

					$billing_address = $order->get_formatted_billing_address();
					$billing_address = esc_html( preg_replace( '#<br\s*/?>#i', ', ', $billing_address ) );

					$payaza_params['meta_billing_address'] = $billing_address;

				}

				if ( $this->meta_shipping_address ) {

					$shipping_address = $order->get_formatted_shipping_address();
					$shipping_address = esc_html( preg_replace( '#<br\s*/?>#i', ', ', $shipping_address ) );

					if ( empty( $shipping_address ) ) {

						$billing_address = $order->get_formatted_billing_address();
						$billing_address = esc_html( preg_replace( '#<br\s*/?>#i', ', ', $billing_address ) );

						$shipping_address = $billing_address;

					}

					$payaza_params['meta_shipping_address'] = $shipping_address;

				}
			}

			update_post_meta( $order_id, '_payaza_txn_ref', $txnref );

		}

		wp_localize_script( 'wc_payaza', 'wc_payaza_params', $payaza_params );

	}

	/**
	 * Load admin scripts.
	 */
	public function admin_scripts() {

		if ( 'woocommerce_page_wc-settings' !== get_current_screen()->id ) {
			return;
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		$payaza_admin_params = array(
			'plugin_url' => WC_PAYAZA_URL,
		);

		wp_enqueue_script( 'wc_payaza_admin', plugins_url( 'assets/js/payaza-admin' . $suffix . '.js', WC_PAYAZA_MAIN_FILE ), array(), WC_PAYAZA_VERSION, true );

		wp_localize_script( 'wc_payaza_admin', 'wc_payaza_admin_params', $payaza_admin_params );

	}

	/**
	 * Process the payment.
	 *
	 * @param int $order_id
	 *
	 * @return array|void
	 */
	public function process_payment( $order_id ) {

		if ( 'redirect' === $this->payment_page ) {

			return $this->process_redirect_payment_option( $order_id );

		} elseif ( isset( $_POST[ 'wc-' . $this->id . '-payment-token' ] ) && 'new' !== $_POST[ 'wc-' . $this->id . '-payment-token' ] ) {

			$token_id = wc_clean( $_POST[ 'wc-' . $this->id . '-payment-token' ] );
			$token    = WC_Payment_Tokens::get( $token_id );

			if ( $token->get_user_id() !== get_current_user_id() ) {

				wc_add_notice( 'Invalid token ID', 'error' );

				return;

			} else {

				$status = $this->process_token_payment( $token->get_token(), $order_id );

				if ( $status ) {

					$order = wc_get_order( $order_id );

					return array(
						'result'   => 'success',
						'redirect' => $this->get_return_url( $order ),
					);

				}
			}
		} else {

			if ( is_user_logged_in() && isset( $_POST[ 'wc-' . $this->id . '-new-payment-method' ] ) && true === (bool) $_POST[ 'wc-' . $this->id . '-new-payment-method' ] && $this->saved_cards ) {

				update_post_meta( $order_id, '_wc_payaza_save_card', true );

			}

			$order = wc_get_order( $order_id );

			return array(
				'result'   => 'success',
				'redirect' => $order->get_checkout_payment_url( true ),
			);

		}

	}

	/**
	 * Process a redirect payment option payment.
	 *
	 * @since 5.7
	 * @param int $order_id
	 * @return array|void
	 */
	public function process_redirect_payment_option( $order_id ) {

		$order        = wc_get_order( $order_id );
		$amount       = $order->get_total() * 100;
		$txnref       = $order_id . '_' . time();
		$callback_url = WC()->api_request_url( 'WC_Gateway_Payaza' );

		$payment_channels = $this->get_gateway_payment_channels( $order );

		$payaza_params = array(
			'amount'       => $amount,
			'email'        => $order->get_billing_email(),
			'currency'     => $order->get_currency(),
			'reference'    => $txnref,
			'callback_url' => $callback_url,
		);

		if ( ! empty( $payment_channels ) ) {
			$payaza_params['channels'] = $payment_channels;
		}

		//if ( $this->split_payment ) {

			//$payaza_params['subaccount'] = $this->subaccount_code;
			//$payaza_params['bearer']     = $this->charges_account;

			//if ( empty( $this->transaction_charges ) ) {
			//	$payaza_params['transaction_charge'] = '';
		//	} else {
			//	$payaza_params['transaction_charge'] = $this->transaction_charges * 100;
		//	}
		//}

		$payaza_params['metadata']['custom_fields'] = $this->get_custom_fields( $order_id );
		$payaza_params['metadata']['cancel_action'] = wc_get_cart_url();

		update_post_meta( $order_id, '_payaza_txn_ref', $txnref );

		$payaza_url = 'https://cards-live.78financials.com/card_charge/';

		$headers = array(
			'Authorization' => 'Bearer ' . $this->secret_key,
			'Content-Type'  => 'application/json',
		);

		$args = array(
			'headers' => $headers,
			'timeout' => 60,
			'body'    => json_encode( $payaza_params ),
		);

		$request = wp_remote_post( $payaza_url, $args );

		if ( ! is_wp_error( $request ) && 200 === wp_remote_retrieve_response_code( $request ) ) {

			$payaza_response = json_decode( wp_remote_retrieve_body( $request ) );

			return array(
				'result'   => 'success',
				'redirect' => $payaza_response->data->authorization_url,
			);

		} else {
			wc_add_notice( __( 'Unable to process payment try again', 'woo-payaza' ), 'error' );

			return;
		}

	}

	/**
	 * Process a token payment.
	 *
	 * @param $token
	 * @param $order_id
	 *
	 * @return bool
	 */
	public function process_token_payment( $token, $order_id ) {

		if ( $token && $order_id ) {

			$order = wc_get_order( $order_id );

			$order_amount = $order->get_total() * 100;

			$payaza_url = 'https://cards-live.78financials.com/card_charge/';

			$headers = array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $this->secret_key,
			);

			$metadata['custom_fields'] = $this->get_custom_fields( $order_id );

			$body = array(
				'email'              => $order->get_billing_email(),
				'amount'             => $order_amount,
				'metadata'           => $metadata,
				'authorization_code' => $token,
			);

			$args = array(
				'body'    => json_encode( $body ),
				'headers' => $headers,
				'timeout' => 60,
			);

			$request = wp_remote_post( $payaza_url, $args );

			if ( ! is_wp_error( $request ) && 200 === wp_remote_retrieve_response_code( $request ) ) {

				$payaza_response = json_decode( wp_remote_retrieve_body( $request ) );

				if ( 'success' == $payaza_response->data->status ) {

					$order = wc_get_order( $order_id );

					if ( in_array( $order->get_status(), array( 'processing', 'completed', 'on-hold' ) ) ) {

						wp_redirect( $this->get_return_url( $order ) );

						exit;

					}

					$order_total      = $order->get_total();
					$order_currency   = $order->get_currency();
					$currency_symbol  = get_woocommerce_currency_symbol( $order_currency );
					$amount_paid      = $payaza_response->data->amount / 100;
					$payaza_ref     = $payaza_response->data->reference;
					$payment_currency = $payaza_response->data->currency;
					$gateway_symbol   = get_woocommerce_currency_symbol( $payment_currency );

					// check if the amount paid is equal to the order amount.
					if ( $amount_paid < $order_total ) {

						$order->update_status( 'on-hold', '' );

						add_post_meta( $order_id, '_transaction_id', $payaza_ref, true );

						$notice      = sprintf( __( 'Thank you for shopping with us.%1$sYour payment transaction was successful, but the amount paid is not the same as the total order amount.%2$sYour order is currently on hold.%3$sKindly contact us for more information regarding your order and payment status.', 'woo-payaza' ), '<br />', '<br />', '<br />' );
						$notice_type = 'notice';

						// Add Customer Order Note
						$order->add_order_note( $notice, 1 );

						// Add Admin Order Note
						$admin_order_note = sprintf( __( '<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Amount paid is less than the total order amount.%3$sAmount Paid was <strong>%4$s (%5$s)</strong> while the total order amount is <strong>%6$s (%7$s)</strong>%8$s<strong>Payaza Transaction Reference:</strong> %9$s', 'woo-payaza' ), '<br />', '<br />', '<br />', $currency_symbol, $amount_paid, $currency_symbol, $order_total, '<br />', $payaza_ref );
						$order->add_order_note( $admin_order_note );

						wc_add_notice( $notice, $notice_type );

					} else {

						if ( $payment_currency !== $order_currency ) {

							$order->update_status( 'on-hold', '' );

							update_post_meta( $order_id, '_transaction_id', $payaza_ref );

							$notice      = sprintf( __( 'Thank you for shopping with us.%1$sYour payment was successful, but the payment currency is different from the order currency.%2$sYour order is currently on-hold.%3$sKindly contact us for more information regarding your order and payment status.', 'woo-payaza' ), '<br />', '<br />', '<br />' );
							$notice_type = 'notice';

							// Add Customer Order Note
							$order->add_order_note( $notice, 1 );

							// Add Admin Order Note
							$admin_order_note = sprintf( __( '<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Order currency is different from the payment currency.%3$sOrder Currency is <strong>%4$s (%5$s)</strong> while the payment currency is <strong>%6$s (%7$s)</strong>%8$s<strong>Payaza Transaction Reference:</strong> %9$s', 'woo-payaza' ), '<br />', '<br />', '<br />', $order_currency, $currency_symbol, $payment_currency, $gateway_symbol, '<br />', $payaza_ref );
							$order->add_order_note( $admin_order_note );

							function_exists( 'wc_reduce_stock_levels' ) ? wc_reduce_stock_levels( $order_id ) : $order->reduce_order_stock();

							wc_add_notice( $notice, $notice_type );

						} else {

							$order->payment_complete( $payaza_ref );

							$order->add_order_note( sprintf( 'Payment via Payaza successful (Transaction Reference: %s)', $payaza_ref ) );

						}
					}

					$this->save_subscription_payment_token( $order_id, $payaza_response );

					WC()->cart->empty_cart();

					return true;

				} else {

					$order_notice  = __( 'Payment was declined by Payaza.', 'woo-payaza' );
					$failed_notice = __( 'Payment failed using the saved card. Kindly use another payment option.', 'woo-payaza' );

					if ( isset( $payaza_response->data->gateway_response ) && ! empty( $payaza_response->data->gateway_response ) ) {

						$order_notice  = sprintf( __( 'Payment was declined by Payaza. Reason: %s.', 'woo-payaza' ), $payaza_response->data->gateway_response );
						$failed_notice = sprintf( __( 'Payment failed using the saved card. Reason: %s. Kindly use another payment option.', 'woo-payaza' ), $payaza_response->data->gateway_response );

					}

					$order->update_status( 'failed', $order_notice );

					wc_add_notice( $failed_notice, 'error' );

					return false;

				}
			}
		} else {

			wc_add_notice( __( 'Payment Failed.', 'woo-payaza' ), 'error' );

		}

	}

	/**
	 * Show new card can only be added when placing an order notice.
	 */
	public function add_payment_method() {

		wc_add_notice( __( 'You can only add a new card when placing an order.', 'woo-payaza' ), 'error' );

		return;

	}

	/**
	 * Displays the payment page.
	 *
	 * @param $order_id
	 */
	public function receipt_page( $order_id ) {

		$order = wc_get_order( $order_id );

		echo '<div id="wc-payaza-form">';

		echo '<p>' . __( 'Thank you for your order, please click the button below to pay with Payaza.', 'woo-payaza' ) . '</p>';

		echo '<div id="payaza_form"><form id="order_review" method="post" action="' . WC()->api_request_url( 'WC_Gateway_Payaza' ) . '"></form><button class="button" id="payaza-payment-button">' . __( 'Pay Now', 'woo-payaza' ) . '</button>';

		if ( ! $this->remove_cancel_order_button ) {
			echo '  <a class="button cancel" id="payaza-cancel-payment-button" href="' . esc_url( $order->get_cancel_order_url() ) . '">' . __( 'Cancel order &amp; restore cart', 'woo-payaza' ) . '</a></div>';
		}

		echo '</div>';

	}

	/**
	 * Verify Payaza payment.
	 */
	public function verify_payaza_transaction() {

		if ( isset( $_REQUEST['payaza_txnref'] ) ) {
			$payaza_txn_ref = sanitize_text_field( $_REQUEST['payaza_txnref'] );
		} elseif ( isset( $_REQUEST['reference'] ) ) {
			$payaza_txn_ref = sanitize_text_field( $_REQUEST['reference'] );
		} else {
			$payaza_txn_ref = false;
		}

		@ob_clean();

		if ( $payaza_txn_ref ) {

			$payaza_url = 'https://checkout.payaza.africa/transaction/verify/' . $payaza_txn_ref;

			$headers = array(
				'Authorization' => 'Bearer ' . $this->secret_key,
			);

			$args = array(
				'headers' => $headers,
				'timeout' => 60,
			); 

			$request = wp_remote_get( $payaza_url, $args );

			if ( ! is_wp_error( $request ) && 200 === wp_remote_retrieve_response_code( $request ) ) {

				$payaza_response = json_decode( wp_remote_retrieve_body( $request ) );

				if ( 'success' == $payaza_response->data->status ) {

					$order_details = explode( '_', $payaza_response->data->reference );
					$order_id      = (int) $order_details[0];
					$order         = wc_get_order( $order_id );

					if ( in_array( $order->get_status(), array( 'processing', 'completed', 'on-hold' ) ) ) {

						wp_redirect( $this->get_return_url( $order ) );

						exit;

					}

					$order_total      = $order->get_total();
					$order_currency   = $order->get_currency();
					$currency_symbol  = get_woocommerce_currency_symbol( $order_currency );
					$amount_paid      = $payaza_response->data->amount / 100;
					$payaza_ref     = $payaza_response->data->reference;
					$payment_currency = strtoupper( $payaza_response->data->currency );
					$gateway_symbol   = get_woocommerce_currency_symbol( $payment_currency );

					// check if the amount paid is equal to the order amount.
					if ( $amount_paid < $order_total ) {

						$order->update_status( 'on-hold', '' );

						add_post_meta( $order_id, '_transaction_id', $payaza_ref, true );

						$notice      = sprintf( __( 'Thank you for shopping with us.%1$sYour payment transaction was successful, but the amount paid is not the same as the total order amount.%2$sYour order is currently on hold.%3$sKindly contact us for more information regarding your order and payment status.', 'woo-payaza' ), '<br />', '<br />', '<br />' );
						$notice_type = 'notice';

						// Add Customer Order Note
						$order->add_order_note( $notice, 1 );

						// Add Admin Order Note
						$admin_order_note = sprintf( __( '<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Amount paid is less than the total order amount.%3$sAmount Paid was <strong>%4$s (%5$s)</strong> while the total order amount is <strong>%6$s (%7$s)</strong>%8$s<strong>Payaza Transaction Reference:</strong> %9$s', 'woo-payaza' ), '<br />', '<br />', '<br />', $currency_symbol, $amount_paid, $currency_symbol, $order_total, '<br />', $payaza_ref );
						$order->add_order_note( $admin_order_note );

						function_exists( 'wc_reduce_stock_levels' ) ? wc_reduce_stock_levels( $order_id ) : $order->reduce_order_stock();

						wc_add_notice( $notice, $notice_type );

					} else {

						if ( $payment_currency !== $order_currency ) {

							$order->update_status( 'on-hold', '' );

							update_post_meta( $order_id, '_transaction_id', $payaza_ref );

							$notice      = sprintf( __( 'Thank you for shopping with us.%1$sYour payment was successful, but the payment currency is different from the order currency.%2$sYour order is currently on-hold.%3$sKindly contact us for more information regarding your order and payment status.', 'woo-payaza' ), '<br />', '<br />', '<br />' );
							$notice_type = 'notice';

							// Add Customer Order Note
							$order->add_order_note( $notice, 1 );

							// Add Admin Order Note
							$admin_order_note = sprintf( __( '<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Order currency is different from the payment currency.%3$sOrder Currency is <strong>%4$s (%5$s)</strong> while the payment currency is <strong>%6$s (%7$s)</strong>%8$s<strong>Payaza Transaction Reference:</strong> %9$s', 'woo-payaza' ), '<br />', '<br />', '<br />', $order_currency, $currency_symbol, $payment_currency, $gateway_symbol, '<br />', $payaza_ref );
							$order->add_order_note( $admin_order_note );

							function_exists( 'wc_reduce_stock_levels' ) ? wc_reduce_stock_levels( $order_id ) : $order->reduce_order_stock();

							wc_add_notice( $notice, $notice_type );

						} else {

							$order->payment_complete( $payaza_ref );
							$order->add_order_note( sprintf( __( 'Payment via Payaza successful (Transaction Reference: %s)', 'woo-payaza' ), $payaza_ref ) );

							if ( $this->is_autocomplete_order_enabled( $order ) ) {
								$order->update_status( 'completed' );
							}
						}
					}

					$this->save_card_details( $payaza_response, $order->get_user_id(), $order_id );

					WC()->cart->empty_cart();

				} else {

					$order_details = explode( '_', $_REQUEST['payaza_txnref'] );

					$order_id = (int) $order_details[0];

					$order = wc_get_order( $order_id );

					$order->update_status( 'failed', __( 'Payment was declined by Payaza.', 'woo-payaza' ) );

				}
			}

			wp_redirect( $this->get_return_url( $order ) );

			exit;
		}

		wp_redirect( wc_get_page_permalink( 'cart' ) );

		exit;

	}

	/**
	 * Process Webhook.
	 */
	public function process_webhooks() {

		if ( ( strtoupper( $_SERVER['REQUEST_METHOD'] ) != 'POST' ) || ! array_key_exists( 'HTTP_X_PAYaza_SIGNATURE', $_SERVER ) ) {
			exit;
		}

		$json = file_get_contents( 'php://input' );

		// validate event do all at once to avoid timing attack.
		if ( $_SERVER['HTTP_X_PAYaza_SIGNATURE'] !== hash_hmac( 'sha512', $json, $this->secret_key ) ) {
			exit;
		}

		$event = json_decode( $json );

		if ( 'charge.success' == $event->event ) {

			sleep( 10 );

			$order_details = explode( '_', $event->data->reference );

			$order_id = (int) $order_details[0];

			$order = wc_get_order( $order_id );

			$payaza_txn_ref = get_post_meta( $order_id, '_payaza_txn_ref', true );

			if ( $event->data->reference != $payaza_txn_ref ) {
				exit;
			}

			http_response_code( 200 );

			if ( in_array( $order->get_status(), array( 'processing', 'completed', 'on-hold' ) ) ) {
				exit;
			}

			$order_currency = $order->get_currency();

			$currency_symbol = get_woocommerce_currency_symbol( $order_currency );

			$order_total = $order->get_total();

			$amount_paid = $event->data->amount / 100;

			$payaza_ref = $event->data->reference;

			$payment_currency = strtoupper( $event->data->currency );

			$gateway_symbol = get_woocommerce_currency_symbol( $payment_currency );

			// check if the amount paid is equal to the order amount.
			if ( $amount_paid < $order_total ) {

				$order->update_status( 'on-hold', '' );

				add_post_meta( $order_id, '_transaction_id', $payaza_ref, true );

				$notice      = sprintf( __( 'Thank you for shopping with us.%1$sYour payment transaction was successful, but the amount paid is not the same as the total order amount.%2$sYour order is currently on hold.%3$sKindly contact us for more information regarding your order and payment status.', 'woo-payaza' ), '<br />', '<br />', '<br />' );
				$notice_type = 'notice';

				// Add Customer Order Note.
				$order->add_order_note( $notice, 1 );

				// Add Admin Order Note.
				$admin_order_note = sprintf( __( '<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Amount paid is less than the total order amount.%3$sAmount Paid was <strong>%4$s (%5$s)</strong> while the total order amount is <strong>%6$s (%7$s)</strong>%8$s<strong>Payaza Transaction Reference:</strong> %9$s', 'woo-payaza' ), '<br />', '<br />', '<br />', $currency_symbol, $amount_paid, $currency_symbol, $order_total, '<br />', $payaza_ref );
				$order->add_order_note( $admin_order_note );

				function_exists( 'wc_reduce_stock_levels' ) ? wc_reduce_stock_levels( $order_id ) : $order->reduce_order_stock();

				wc_add_notice( $notice, $notice_type );

				WC()->cart->empty_cart();

			} else {

				if ( $payment_currency !== $order_currency ) {

					$order->update_status( 'on-hold', '' );

					update_post_meta( $order_id, '_transaction_id', $payaza_ref );

					$notice      = sprintf( __( 'Thank you for shopping with us.%1$sYour payment was successful, but the payment currency is different from the order currency.%2$sYour order is currently on-hold.%3$sKindly contact us for more information regarding your order and payment status.', 'woo-payaza' ), '<br />', '<br />', '<br />' );
					$notice_type = 'notice';

					// Add Customer Order Note.
					$order->add_order_note( $notice, 1 );

					// Add Admin Order Note.
					$admin_order_note = sprintf( __( '<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Order currency is different from the payment currency.%3$sOrder Currency is <strong>%4$s (%5$s)</strong> while the payment currency is <strong>%6$s (%7$s)</strong>%8$s<strong>Payaza Transaction Reference:</strong> %9$s', 'woo-payaza' ), '<br />', '<br />', '<br />', $order_currency, $currency_symbol, $payment_currency, $gateway_symbol, '<br />', $payaza_ref );
					$order->add_order_note( $admin_order_note );

					function_exists( 'wc_reduce_stock_levels' ) ? wc_reduce_stock_levels( $order_id ) : $order->reduce_order_stock();

					wc_add_notice( $notice, $notice_type );

				} else {

					$order->payment_complete( $payaza_ref );

					$order->add_order_note( sprintf( __( 'Payment via Payaza successful (Transaction Reference: %s)', 'woo-payaza' ), $payaza_ref ) );

					WC()->cart->empty_cart();

					if ( $this->is_autocomplete_order_enabled( $order ) ) {
						$order->update_status( 'completed' );
					}
				}
			}

			$this->save_card_details( $event, $order->get_user_id(), $order_id );

			exit;
		}

		exit;

	}

	/**
	 * Save Customer Card Details.
	 *
	 * @param $payaza_response
	 * @param $user_id
	 * @param $order_id
	 */
	public function save_card_details( $payaza_response, $user_id, $order_id ) {

		$this->save_subscription_payment_token( $order_id, $payaza_response );

		$save_card = get_post_meta( $order_id, '_wc_payaza_save_card', true );

		if ( $user_id && $this->saved_cards && $save_card && $payaza_response->data->authorization->reusable && 'card' == $payaza_response->data->authorization->channel ) {

			$order = wc_get_order( $order_id );

			$gateway_id = $order->get_payment_method();

			$last4     = $payaza_response->data->authorization->last4;
			$exp_year  = $payaza_response->data->authorization->exp_year;
			$brand     = $payaza_response->data->authorization->card_type;
			$exp_month = $payaza_response->data->authorization->exp_month;
			$auth_code = $payaza_response->data->authorization->authorization_code;

			$token = new WC_Payment_Token_CC();
			$token->set_token( $auth_code );
			$token->set_gateway_id( $gateway_id );
			$token->set_card_type( strtolower( $brand ) );
			$token->set_last4( $last4 );
			$token->set_expiry_month( $exp_month );
			$token->set_expiry_year( $exp_year );
			$token->set_user_id( $user_id );
			$token->save();

			delete_post_meta( $order_id, '_wc_payaza_save_card' );

		}

	}

	/**
	 * Save payment token to the order for automatic renewal for further subscription payment.
	 *
	 * @param $order_id
	 * @param $payaza_response
	 */
	public function save_subscription_payment_token( $order_id, $payaza_response ) {

		if ( ! function_exists( 'wcs_order_contains_subscription' ) ) {
			return;
		}

		if ( $this->order_contains_subscription( $order_id ) && $payaza_response->data->authorization->reusable && 'card' == $payaza_response->data->authorization->channel ) {

			$auth_code = $payaza_response->data->authorization->authorization_code;

			// Also store it on the subscriptions being purchased or paid for in the order
			if ( function_exists( 'wcs_order_contains_subscription' ) && wcs_order_contains_subscription( $order_id ) ) {

				$subscriptions = wcs_get_subscriptions_for_order( $order_id );

			} elseif ( function_exists( 'wcs_order_contains_renewal' ) && wcs_order_contains_renewal( $order_id ) ) {

				$subscriptions = wcs_get_subscriptions_for_renewal_order( $order_id );

			} else {

				$subscriptions = array();

			}

			foreach ( $subscriptions as $subscription ) {

				$subscription_id = $subscription->get_id();

				update_post_meta( $subscription_id, '_payaza_token', $auth_code );

			}
		}

	}

	/**
	 * Get custom fields to pass to Payaza.
	 *
	 * @param int $order_id WC Order ID
	 *
	 * @return array
	 */
	public function get_custom_fields( $order_id ) {

		$order = wc_get_order( $order_id );

		$custom_fields = array();

		$custom_fields[] = array(
			'display_name'  => 'Plugin',
			'variable_name' => 'plugin',
			'value'         => 'woo-payaza',
		);
		
		if ( $this->custom_metadata ) {

			if ( $this->meta_order_id ) {

				$custom_fields[] = array(
					'display_name'  => 'Order ID',
					'variable_name' => 'order_id',
					'value'         => $order_id,
				);

			}

			if ( $this->meta_name ) {

				$custom_fields[] = array(
					'display_name'  => 'Customer Name',
					'variable_name' => 'customer_name',
					'value'         => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
				);

			}

			if ( $this->meta_email ) {

				$custom_fields[] = array(
					'display_name'  => 'Customer Email',
					'variable_name' => 'customer_email',
					'value'         => $order->get_billing_email(),
				);

			}

			if ( $this->meta_phone ) {

				$custom_fields[] = array(
					'display_name'  => 'Customer Phone',
					'variable_name' => 'customer_phone',
					'value'         => $order->get_billing_phone(),
				);

			}

			if ( $this->meta_products ) {

				$line_items = $order->get_items();

				$products = '';

				foreach ( $line_items as $item_id => $item ) {
					$name     = $item['name'];
					$quantity = $item['qty'];
					$products .= $name . ' (Qty: ' . $quantity . ')';
					$products .= ' | ';
				}

				$products = rtrim( $products, ' | ' );

				$custom_fields[] = array(
					'display_name'  => 'Products',
					'variable_name' => 'products',
					'value'         => $products,
				);

			}

			if ( $this->meta_billing_address ) {

				$billing_address = $order->get_formatted_billing_address();
				$billing_address = esc_html( preg_replace( '#<br\s*/?>#i', ', ', $billing_address ) );

				$payaza_params['meta_billing_address'] = $billing_address;

				$custom_fields[] = array(
					'display_name'  => 'Billing Address',
					'variable_name' => 'billing_address',
					'value'         => $billing_address,
				);

			}

			if ( $this->meta_shipping_address ) {

				$shipping_address = $order->get_formatted_shipping_address();
				$shipping_address = esc_html( preg_replace( '#<br\s*/?>#i', ', ', $shipping_address ) );

				if ( empty( $shipping_address ) ) {

					$billing_address = $order->get_formatted_billing_address();
					$billing_address = esc_html( preg_replace( '#<br\s*/?>#i', ', ', $billing_address ) );

					$shipping_address = $billing_address;

				}
				$custom_fields[] = array(
					'display_name'  => 'Shipping Address',
					'variable_name' => 'shipping_address',
					'value'         => $shipping_address,
				);

			}

		}

		return $custom_fields;
	}

	

	/**
	 * Checks if WC version is less than passed in version.
	 *
	 * @param string $version Version to check against.
	 *
	 * @return bool
	 */
	public function is_wc_lt( $version ) {
		return version_compare( WC_VERSION, $version, '<' );
	}

	/**
	 * Checks if autocomplete order is enabled for the payment method.
	 *
	 * @since 5.7
	 * @param WC_Order $order Order object.
	 * @return bool
	 */
	protected function is_autocomplete_order_enabled( $order ) {
		$autocomplete_order = false;

		$payment_method = $order->get_payment_method();

		$payaza_settings = get_option('woocommerce_' . $payment_method . '_settings');

		if ( isset( $payaza_settings['autocomplete_order'] ) && 'yes' === $payaza_settings['autocomplete_order'] ) {
			$autocomplete_order = true;
		}

		return $autocomplete_order;
	}

	/**
	 * Retrieve the payment channels configured for the gateway
	 *
	 * @since 5.7
	 * @param WC_Order $order Order object.
	 * @return array
	 */
	protected function get_gateway_payment_channels( $order ) {

		$payment_method = $order->get_payment_method();

		if ( 'payaza' === $payment_method ) {
			return array();
		}

		$payment_channels = $this->payment_channels;

		if ( empty( $payment_channels ) ) {
			$payment_channels = array( 'card' );
		}

		return $payment_channels;
	}

}
