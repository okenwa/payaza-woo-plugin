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

		if ( ! in_array( get_woocommerce_currency(), apply_filters( 'woocommerce_payaza_supported_currencies', array( 'NGN', 'USD', 'ZAR', 'GHS', 'KES', 'XOF' ) ) ) ) {

			$this->msg = sprintf( __( 'Payaza does not support your store currency. Kindly set it to either NGN (&#8358), GHS (&#x20b5;), USD (&#36;), KES (KSh), ZAR (R), or XOF (CFA) <a href="%s">here</a>', 'woo-payaza' ), admin_url( 'admin.php?page=wc-settings&tab=general' ) );

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
			<strong><?php printf( __( 'Optional: To avoid situations where bad network makes it impossible to verify transactions, set your webhook URL <a href="%1$s" target="_blank" rel="noopener noreferrer">here</a> to the URL below<span style="color: red"><pre><code>%2$s</code></pre></span>', 'woo-paystack' ), 'https://dashboard.paystack.co/#/settings/developer', WC()->api_request_url( 'Tbz_WC_Paystack_Webhook' ) ); ?></strong>
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
			'custom_metadata'                  => array(
				'title'       => __( 'Custom Metadata', 'woo-paystack' ),
				'label'       => __( 'Enable Custom Metadata', 'woo-paystack' ),
				'type'        => 'checkbox',
				'class'       => 'wc-paystack-metadata',
				'description' => __( 'If enabled, you will be able to send more information about the order to Paystack.', 'woo-paystack' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'meta_order_id'                    => array(
				'title'       => __( 'Order ID', 'woo-paystack' ),
				'label'       => __( 'Send Order ID', 'woo-paystack' ),
				'type'        => 'checkbox',
				'class'       => 'wc-paystack-meta-order-id',
				'description' => __( 'If checked, the Order ID will be sent to Paystack', 'woo-paystack' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'meta_name'                        => array(
				'title'       => __( 'Customer Name', 'woo-paystack' ),
				'label'       => __( 'Send Customer Name', 'woo-paystack' ),
				'type'        => 'checkbox',
				'class'       => 'wc-paystack-meta-name',
				'description' => __( 'If checked, the customer full name will be sent to Paystack', 'woo-paystack' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'meta_email'                       => array(
				'title'       => __( 'Customer Email', 'woo-paystack' ),
				'label'       => __( 'Send Customer Email', 'woo-paystack' ),
				'type'        => 'checkbox',
				'class'       => 'wc-paystack-meta-email',
				'description' => __( 'If checked, the customer email address will be sent to Paystack', 'woo-paystack' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'meta_phone'                       => array(
				'title'       => __( 'Customer Phone', 'woo-paystack' ),
				'label'       => __( 'Send Customer Phone', 'woo-paystack' ),
				'type'        => 'checkbox',
				'class'       => 'wc-paystack-meta-phone',
				'description' => __( 'If checked, the customer phone will be sent to Paystack', 'woo-paystack' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'meta_billing_address'             => array(
				'title'       => __( 'Order Billing Address', 'woo-paystack' ),
				'label'       => __( 'Send Order Billing Address', 'woo-paystack' ),
				'type'        => 'checkbox',
				'class'       => 'wc-paystack-meta-billing-address',
				'description' => __( 'If checked, the order billing address will be sent to Paystack', 'woo-paystack' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'meta_shipping_address'            => array(
				'title'       => __( 'Order Shipping Address', 'woo-paystack' ),
				'label'       => __( 'Send Order Shipping Address', 'woo-paystack' ),
				'type'        => 'checkbox',
				'class'       => 'wc-paystack-meta-shipping-address',
				'description' => __( 'If checked, the order shipping address will be sent to Paystack', 'woo-paystack' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'meta_products'                    => array(
				'title'       => __( 'Product(s) Purchased', 'woo-paystack' ),
				'label'       => __( 'Send Product(s) Purchased', 'woo-paystack' ),
				'type'        => 'checkbox',
				'class'       => 'wc-paystack-meta-products',
				'description' => __( 'If checked, the product(s) purchased will be sent to Paystack', 'woo-paystack' ),
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
