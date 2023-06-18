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
	 * Should the cancel & remove order button be removed on the pay for order page.
	 *
	 * @var bool
	 */
	public $remove_cancel_order_button;

	
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
		$this->method_description = sprintf( __( 'With Payaza, your customers have access to as many payment options as possible at a very affordable rate using Mastercard, Visa, Verve Cards and Bank Accounts. <a href="%1$s" target="_blank">Sign up</a> for a Payaza account, and <a href="%2$s" target="_blank">get your API keys</a>.', 'woo-payaza' ), 'https://payaza.africa', 'https://payaza.africa/login' );
		$this->has_fields         = true;

		$this->payment_page = $this->get_option( 'payment_page' );

		$this->supports = array(
			'products',
			'refunds',
			'tokenization',
			
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

		$this->remove_cancel_order_button = $this->get_option( 'remove_cancel_order_button' ) === 'yes' ? true : false;
		

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

			$this->msg = sprintf( __( 'Payaza does not support your store currency. Kindly set it to either NGN (&#8358), USD (&#36;) <a href="%s">here</a>', 'woo-payaza' ), admin_url( 'admin.php?page=wc-settings&tab=general' ) );

			return false;

		}

		return true;

	}

	/**
	 * Display payaza payment icon.
	 */
	public function get_icon() {

			$icon = '<img src="' . WC_HTTPS::force_https_url( plugins_url( 'assets/images/payaza.png', WC_PAYAZA_MAIN_FILE ) ) . '" alt="Payaza Payment Options" />';
		 
		return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );

	}

	/**
	 * Check if Payaza merchant details is filled.
	 */
	public function admin_notices() {

		if ( $this->enabled == 'no' ) {
			return;
		}

		// Check required fields.
		if ( ! ( $this->public_key && $this->secret_key ) ) {
			echo '<div class="error"><p>' .  sprintf ( __( 'Please enter your Payaza merchant details <a href="%s">here</a> to be able to use the Payaza WooCommerce plugin.', 'woo-payaza' ), admin_url( 'admin.php?page=wc-settings&tab=checkout&section=payaza' ) ). '</p></div>';
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
		
		<strong><?php printf(
    __(
        'Set your webhook URL <a href="%1$s" target="_blank" rel="noopener noreferrer">here</a> to the URL below<span style="color: green"><pre><code>%2$s</code></pre></span>',
        'woo-payaza'
    ),
    esc_url( 'https://payaza.africa/settings' ),
    esc_html( WC()->api_request_url( 'Paz_WC_Payaza_Webhook' ) )
);?></strong>
		</h4>
		<?php

		if ( $this->is_valid_for_use() ) {

			echo '<table class="form-table">'; 
			$this->generate_settings_html();
			echo '</table>';

		} else {
			?>
			<div class="inline error"><p><strong><?php printf( esc_html__( 'Payaza Payment Gateway Disabled', 'woo-payaza' ), esc_html( $this->msg ) );?></strong></p></div>

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
			
			'remove_cancel_order_button'       => array(
				'title'       => __( 'Remove Cancel Order & Restore Cart Button', 'woo-payaza' ),
				'label'       => __( 'Remove the cancel order & restore cart button on the pay for order page', 'woo-payaza' ),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no',
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

		wp_enqueue_script( 'wc_payaza', plugins_url( 'assets/js/payaza' . $suffix . '.js', WC_PAYAZA_MAIN_FILE ), array( 'jquery', 'payaza' ), WC_PAYAZA_VERSION, false );

		$payaza_params = array(
			'key' => $this->public_key,
		);

		if ( is_checkout_pay_page() && get_query_var( 'order-pay' ) ) {

			$email         = $order->get_billing_email();
			$first_name    = $order->get_billing_first_name();
			$last_name 	   = $order->get_billing_last_name();
			$phone_number  = $order->get_billing_phone();
			$amount        = $order->get_total() * 100;
			$txnref        = $order_id . '_' . time();
			$the_order_id  = $order->get_id();
			$the_order_key = $order->get_order_key();
			$currency      = $order->get_currency();

			

			if ( $the_order_id == $order_id && $the_order_key == $order_key ) {

				$payaza_params['email']    = $email;
				$payaza_params['first_name'] = $first_name;
				$payaza_params['last_name'] = $last_name;
				$payaza_params['phone_number'] = $phone_number;
				$payaza_params['amount']   = $amount;
				$payaza_params['txnref']   = $txnref;
				$payaza_params['currency'] = $currency;



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
		$email  = $order->get_billing_email();

		echo '<div id="wc-payaza-form">';

		
		echo '<p>'. htmlspecialchars__( 'Thank you for your order, please click the button below to pay with Payaza.', 'woo-payaza' ). '</p>';

		
	

		echo '<div id="payaza_form">
  <form id="order_review" method="post" action="'. esc_url( wc_get_checkout_url() ). '">
    <input type="hidden" name="payaza_payment_button" value="1">
  </form>
  <button class="button" id="payaza-payment-button">'. __( 'Pay Now', 'woo-payaza' ). '</button>';


		if ( ! $this->remove_cancel_order_button ) {
			echo '  <a class="button cancel" id="payaza-cancel-payment-button" href="' . esc_url( $order->get_cancel_order_url() ) . '">' . __( 'Cancel order &amp; restore cart', 'woo-payaza' ) . '</a></div>';
		}

		echo '</div>';

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
