<?php

/**
 * Class Tbz_WC_Payaza_Custom_Gateway.
 */
class WC_Gateway_Custom_Payaza extends WC_Gateway_Payaza_Subscriptions {

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {
 
		$this->form_fields = array(
			'enabled'                          => array(
				'title'       => __( 'Enable/Disable', 'woo-payaza' ),
				/* translators: payment method title */
				'label'       => sprintf( __( 'Enable Payaza - %s', 'woo-payaza' ), $this->title ),
				'type'        => 'checkbox',
				'description' => __( 'Enable this gateway as a payment option on the checkout page.', 'woo-payaza' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'title'                            => array(
				'title'       => __( 'Title', 'woo-payaza' ),
				'type'        => 'text',
				'description' => __( 'This controls the payment method title which the user sees during checkout.', 'woo-payaza' ),
				'desc_tip'    => true,
				'default'     => __( 'Payaza', 'woo-payaza' ),
			),
			'description'                      => array(
				'title'       => __( 'Description', 'woo-payaza' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the payment method description which the user sees during checkout.', 'woo-payaza' ),
				'desc_tip'    => true,
				'default'     => '',
			),
			'payment_page'                     => array(
				'title'       => __( 'Payment Option', 'woo-payaza' ),
				'type'        => 'select',
				'description' => __( 'Popup shows the payment popup on the page while Redirect will redirect the customer to Payaza to make payment.', 'woo-payaza' ),
				'default'     => '',
				'desc_tip'    => false,
				'options'     => array(
					''         => __( 'Select One', 'woo-payaza' ),
					'inline'   => __( 'Popup', 'woo-payaza' ),
					'redirect' => __( 'Redirect', 'woo-payaza' ),
				),
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
			'split_payment'                    => array(
				'title'       => __( 'Split Payment', 'woo-payaza' ),
				'label'       => __( 'Enable Split Payment', 'woo-payaza' ),
				'type'        => 'checkbox',
				'description' => '',
				'class'       => 'woocommerce_Payaza_split_payment',
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'subaccount_code'                  => array(
				'title'       => __( 'Subaccount Code', 'woo-payaza' ),
				'type'        => 'text',
				'description' => __( 'Enter the subaccount code here.', 'woo-payaza' ),
				'class'       => __( 'woocommerce_Payaza_subaccount_code', 'woo-payaza' ),
				'default'     => '',
			),
			'split_payment_transaction_charge' => array(
				'title'             => __( 'Split Payment Transaction Charge', 'woo-payaza' ),
				'type'              => 'number',
				'description'       => __( 'A flat fee to charge the subaccount for this transaction, in Naira (&#8358;). This overrides the split percentage set when the subaccount was created. Ideally, you will need to use this if you are splitting in flat rates (since subaccount creation only allows for percentage split). e.g. 100 for a &#8358;100 flat fee.', 'woo-payaza' ),
				'class'             => 'woocommerce_Payaza_split_payment_transaction_charge',
				'default'           => '',
				'custom_attributes' => array(
					'min'  => 1,
					'step' => 0.1,
				),
				'desc_tip'          => false,
			),
			'split_payment_charge_account'     => array(
				'title'       => __( 'Payaza Charges Bearer', 'woo-payaza' ),
				'type'        => 'select',
				'description' => __( 'Who bears Payaza charges?', 'woo-payaza' ),
				'class'       => 'woocommerce_Payaza_split_payment_charge_account',
				'default'     => '',
				'desc_tip'    => false,
				'options'     => array(
					''           => __( 'Select One', 'woo-payaza' ),
					'account'    => __( 'Account', 'woo-payaza' ),
					'subaccount' => __( 'Subaccount', 'woo-payaza' ),
				),
			),
			'payment_channels'                 => array(
				'title'             => __( 'Payment Channels', 'woo-payaza' ),
				'type'              => 'multiselect',
				'class'             => 'wc-enhanced-select wc-payaza-payment-channels',
				'description'       => __( 'The payment channels enabled for this gateway', 'woo-payaza' ),
				'default'           => '',
				'desc_tip'          => true,
				'select_buttons'    => true,
				'options'           => $this->channels(),
				'custom_attributes' => array(
					'data-placeholder' => __( 'Select payment channels', 'woo-payaza' ),
				),
			),
			'cards_allowed'                    => array(
				'title'             => __( 'Allowed Card Brands', 'woo-payaza' ),
				'type'              => 'multiselect',
				'class'             => 'wc-enhanced-select wc-payaza-cards-allowed',
				'description'       => __( 'The card brands allowed for this gateway. This filter only works with the card payment channel.', 'woo-payaza' ),
				'default'           => '',
				'desc_tip'          => true,
				'select_buttons'    => true,
				'options'           => $this->card_types(),
				'custom_attributes' => array(
					'data-placeholder' => __( 'Select card brands', 'woo-payaza' ),
				),
			),
			'banks_allowed'                    => array(
				'title'             => __( 'Allowed Banks Card', 'woo-payaza' ),
				'type'              => 'multiselect',
				'class'             => 'wc-enhanced-select wc-payaza-banks-allowed',
				'description'       => __( 'The banks whose card should be allowed for this gateway. This filter only works with the card payment channel.', 'woo-payaza' ),
				'default'           => '',
				'desc_tip'          => true,
				'select_buttons'    => true,
				'options'           => $this->banks(),
				'custom_attributes' => array(
					'data-placeholder' => __( 'Select banks', 'woo-payaza' ),
				),
			),
			'payment_icons'                    => array(
				'title'             => __( 'Payment Icons', 'woo-payaza' ),
				'type'              => 'multiselect',
				'class'             => 'wc-enhanced-select wc-payaza-payment-icons',
				'description'       => __( 'The payment icons to be displayed on the checkout page.', 'woo-payaza' ),
				'default'           => '',
				'desc_tip'          => true,
				'select_buttons'    => true,
				'options'           => $this->payment_icons(),
				'custom_attributes' => array(
					'data-placeholder' => __( 'Select payment icons', 'woo-payaza' ),
				),
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

	}

	/**
	 * Admin Panel Options.
	 */
	public function admin_options() {

		$Payaza_settings_url = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=payaza' );
		$checkout_settings_url = admin_url( 'admin.php?page=wc-settings&tab=checkout' );
		?>

		<h2>
			<?php
			/* translators: payment method title */
			printf( __( 'Payaza - %s', 'woo-payaza' ), esc_attr( $this->title ) );
			?>
			<?php
			if ( function_exists( 'wc_back_link' ) ) {
				wc_back_link( __( 'Return to payments', 'woo-payaza' ), $checkout_settings_url );
			}
			?>
		</h2>

		<h4>
			<?php
			/* translators: link to Payaza developers settings page */
			printf( __( 'Important: To avoid situations where bad network makes it impossible to verify transactions, set your webhook URL <a href="%s" target="_blank" rel="noopener noreferrer">here</a> to the URL below', 'woo-payaza' ), 'https://dashboard.payaza.co/#/settings/developer' );
			?>
		</h4>

		<p style="color: red">
			<code><?php echo esc_url( WC()->api_request_url( 'Tbz_WC_Payaza_Webhook' ) ); ?></code>
		</p>

		<p>
			<?php
			/* translators: link to Payaza general settings page */
			printf( __( 'To configure your Payaza API keys and enable/disable test mode, do that <a href="%s">here</a>', 'woo-payaza' ), esc_url( $Payaza_settings_url ) );
			?>
		</p>

		<?php

		if ( $this->is_valid_for_use() ) {

			echo '<table class="form-table">';
			$this->generate_settings_html();
			echo '</table>';

		} else {

			/* translators: disabled message */
			echo '<div class="inline error"><p><strong>' . sprintf( __( 'Payaza Payment Gateway Disabled: %s', 'woo-payaza' ), esc_attr( $this->msg ) ) . '</strong></p></div>';

		}

	}

	/**
	 * Payment Channels.
	 */
	public function channels() {

		return array(
			'card'          => __( 'Cards', 'woo-payaza' ),
			'bank'          => __( 'Pay with Bank', 'woo-payaza' ),
			'ussd'          => __( 'USSD', 'woo-payaza' ),
			'qr'            => __( 'QR', 'woo-payaza' ),
			'bank_transfer' => __( 'Bank Transfer', 'woo-payaza' ),
		);

	}

	/**
	 * Card Types.
	 */
	public function card_types() {

		return array(
			'visa'       => __( 'Visa', 'woo-payaza' ),
			'verve'      => __( 'Verve', 'woo-payaza' ),
			'mastercard' => __( 'Mastercard', 'woo-payaza' ),
		);

	}

	/**
	 * Banks.
	 */
	public function banks() {

		return array(
			'044'  => __( 'Access Bank', 'woo-payaza' ),
			'035A' => __( 'ALAT by WEMA', 'woo-payaza' ),
			'401'  => __( 'ASO Savings and Loans', 'woo-payaza' ),
			'023'  => __( 'Citibank Nigeria', 'woo-payaza' ),
			'063'  => __( 'Access Bank (Diamond)', 'woo-payaza' ),
			'050'  => __( 'Ecobank Nigeria', 'woo-payaza' ),
			'562'  => __( 'Ekondo Microfinance Bank', 'woo-payaza' ),
			'084'  => __( 'Enterprise Bank', 'woo-payaza' ),
			'070'  => __( 'Fidelity Bank', 'woo-payaza' ),
			'011'  => __( 'First Bank of Nigeria', 'woo-payaza' ),
			'214'  => __( 'First City Monument Bank', 'woo-payaza' ),
			'058'  => __( 'Guaranty Trust Bank', 'woo-payaza' ),
			'030'  => __( 'Heritage Bank', 'woo-payaza' ),
			'301'  => __( 'Jaiz Bank', 'woo-payaza' ),
			'082'  => __( 'Keystone Bank', 'woo-payaza' ),
			'014'  => __( 'MainStreet Bank', 'woo-payaza' ),
			'526'  => __( 'Parallex Bank', 'woo-payaza' ),
			'076'  => __( 'Polaris Bank Limited', 'woo-payaza' ),
			'101'  => __( 'Providus Bank', 'woo-payaza' ),
			'221'  => __( 'Stanbic IBTC Bank', 'woo-payaza' ),
			'068'  => __( 'Standard Chartered Bank', 'woo-payaza' ),
			'232'  => __( 'Sterling Bank', 'woo-payaza' ),
			'100'  => __( 'Suntrust Bank', 'woo-payaza' ),
			'032'  => __( 'Union Bank of Nigeria', 'woo-payaza' ),
			'033'  => __( 'United Bank For Africa', 'woo-payaza' ),
			'215'  => __( 'Unity Bank', 'woo-payaza' ),
			'035'  => __( 'Wema Bank', 'woo-payaza' ),
			'057'  => __( 'Zenith Bank', 'woo-payaza' ),
		);

	}

	/**
	 * Payment Icons.
	 */
	public function payment_icons() {

		return array(
			'verve'         => __( 'Verve', 'woo-payaza' ),
			'visa'          => __( 'Visa', 'woo-payaza' ),
			'mastercard'    => __( 'Mastercard', 'woo-payaza' ),
			'Payazawhite' => __( 'Secured by Payaza White', 'woo-payaza' ),
			'Payazablue'  => __( 'Secured by Payaza Blue', 'woo-payaza' ),
			'payaza-wc'   => __( 'Payaza Nigeria', 'woo-payaza' ),
			'payaza-gh'   => __( 'Payaza Ghana', 'woo-payaza' ),
			'access'        => __( 'Access Bank', 'woo-payaza' ),
			'alat'          => __( 'ALAT by WEMA', 'woo-payaza' ),
			'aso'           => __( 'ASO Savings and Loans', 'woo-payaza' ),
			'citibank'      => __( 'Citibank Nigeria', 'woo-payaza' ),
			'diamond'       => __( 'Access Bank (Diamond)', 'woo-payaza' ),
			'ecobank'       => __( 'Ecobank Nigeria', 'woo-payaza' ),
			'ekondo'        => __( 'Ekondo Microfinance Bank', 'woo-payaza' ),
			'enterprise'    => __( 'Enterprise Bank', 'woo-payaza' ),
			'fidelity'      => __( 'Fidelity Bank', 'woo-payaza' ),
			'firstbank'     => __( 'First Bank of Nigeria', 'woo-payaza' ),
			'fcmb'          => __( 'First City Monument Bank', 'woo-payaza' ),
			'gtbank'        => __( 'Guaranty Trust Bank', 'woo-payaza' ),
			'heritage'      => __( 'Heritage Bank', 'woo-payaza' ),
			'jaiz'          => __( 'Jaiz Bank', 'woo-payaza' ),
			'keystone'      => __( 'Keystone Bank', 'woo-payaza' ),
			'mainstreet'    => __( 'MainStreet Bank', 'woo-payaza' ),
			'parallex'      => __( 'Parallex Bank', 'woo-payaza' ),
			'polaris'       => __( 'Polaris Bank Limited', 'woo-payaza' ),
			'providus'      => __( 'Providus Bank', 'woo-payaza' ),
			'stanbic'       => __( 'Stanbic IBTC Bank', 'woo-payaza' ),
			'standard'      => __( 'Standard Chartered Bank', 'woo-payaza' ),
			'sterling'      => __( 'Sterling Bank', 'woo-payaza' ),
			'suntrust'      => __( 'Suntrust Bank', 'woo-payaza' ),
			'union'         => __( 'Union Bank of Nigeria', 'woo-payaza' ),
			'uba'           => __( 'United Bank For Africa', 'woo-payaza' ),
			'unity'         => __( 'Unity Bank', 'woo-payaza' ),
			'wema'          => __( 'Wema Bank', 'woo-payaza' ),
			'zenith'        => __( 'Zenith Bank', 'woo-payaza' ),
		);

	}

	/**
	 * Display the selected payment icon.
	 */
	public function get_icon() {
		$icon_html = '<img src="' . WC_HTTPS::force_https_url( WC_Payaza_URL . '/assets/images/payaza.png' ) . '" alt="payaza" style="height: 40px; margin-right: 0.4em;margin-bottom: 0.6em;" />';
		$icon      = $this->payment_icons;

		if ( is_array( $icon ) ) {

			$additional_icon = '';

			foreach ( $icon as $i ) {
				$additional_icon .= '<img src="' . WC_HTTPS::force_https_url( WC_Payaza_URL . '/assets/images/' . $i . '.png' ) . '" alt="' . $i . '" style="height: 40px; margin-right: 0.4em;margin-bottom: 0.6em;" />';
			}

			$icon_html .= $additional_icon;
		}

		return apply_filters( 'woocommerce_gateway_icon', $icon_html, $this->id );
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

		wp_enqueue_script( 'payaza', 'https://js.payaza.co/v1/inline.js', array( 'jquery' ), WC_Payaza_VERSION, false );

		wp_enqueue_script( 'wc_Payaza', plugins_url( 'assets/js/payaza' . $suffix . '.js', WC_Payaza_MAIN_FILE ), array( 'jquery', 'payaza' ), WC_Payaza_VERSION, false );

		$Payaza_params = array(
			'key' => $this->public_key,
		);

		if ( is_checkout_pay_page() && get_query_var( 'order-pay' ) ) {

			$email = $order->get_billing_email();

			$amount = $order->get_total() * 100;

			$txnref = $order_id . '_' . time();

			$the_order_id  = $order->get_id();
			$the_order_key = $order->get_order_key();
			$currency      = $order->get_currency();

			if ( $the_order_id == $order_id && $the_order_key == $order_key ) {

				$Payaza_params['email']    = $email;
				$Payaza_params['amount']   = $amount;
				$Payaza_params['txnref']   = $txnref;
				$Payaza_params['currency'] = $currency;

			}

			if ( $this->split_payment ) {

				$Payaza_params['subaccount_code']     = $this->subaccount_code;
				$Payaza_params['charges_account']     = $this->charges_account;
				$Payaza_params['transaction_charges'] = $this->transaction_charges * 100;

			}

			if ( in_array( 'bank', $this->payment_channels ) ) {
				$Payaza_params['bank_channel'] = 'true';
			}

			if ( in_array( 'card', $this->payment_channels ) ) {
				$Payaza_params['card_channel'] = 'true';
			}

			if ( in_array( 'ussd', $this->payment_channels ) ) {
				$Payaza_params['ussd_channel'] = 'true';
			}

			if ( in_array( 'qr', $this->payment_channels ) ) {
				$Payaza_params['qr_channel'] = 'true';
			}

			if ( in_array( 'bank_transfer', $this->payment_channels ) ) {
				$Payaza_params['bank_transfer_channel'] = 'true';
			}

			if ( $this->banks ) {

				$Payaza_params['banks_allowed'] = $this->banks;

			}

			if ( $this->cards ) {

				$Payaza_params['cards_allowed'] = $this->cards;

			}

			if ( $this->custom_metadata ) {

				if ( $this->meta_order_id ) {

					$Payaza_params['meta_order_id'] = $order_id;

				}

				if ( $this->meta_name ) {

					$Payaza_params['meta_name'] = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();

				}

				if ( $this->meta_email ) {

					$Payaza_params['meta_email'] = $email;

				}

				if ( $this->meta_phone ) {

					$Payaza_params['meta_phone'] = $order->get_billing_phone();

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

					$Payaza_params['meta_products'] = $products;

				}

				if ( $this->meta_billing_address ) {

					$billing_address = $order->get_formatted_billing_address();
					$billing_address = esc_html( preg_replace( '#<br\s*/?>#i', ', ', $billing_address ) );

					$Payaza_params['meta_billing_address'] = $billing_address;

				}

				if ( $this->meta_shipping_address ) {

					$shipping_address = $order->get_formatted_shipping_address();
					$shipping_address = esc_html( preg_replace( '#<br\s*/?>#i', ', ', $shipping_address ) );

					if ( empty( $shipping_address ) ) {

						$billing_address = $order->get_formatted_billing_address();
						$billing_address = esc_html( preg_replace( '#<br\s*/?>#i', ', ', $billing_address ) );

						$shipping_address = $billing_address;

					}

					$Payaza_params['meta_shipping_address'] = $shipping_address;

				}
			}

			update_post_meta( $order_id, '_Payaza_txn_ref', $txnref );

		}

		wp_localize_script( 'wc_Payaza', 'wc_Payaza_params', $Payaza_params );

	}

	/**
	 * Add custom gateways to the checkout page.
	 *
	 * @param $available_gateways
	 *
	 * @return mixed
	 */
	public function add_gateway_to_checkout( $available_gateways ) {

		if ( $this->enabled == 'no' ) {
			unset( $available_gateways[ $this->id ] );
		}

		return $available_gateways;

	}

}
