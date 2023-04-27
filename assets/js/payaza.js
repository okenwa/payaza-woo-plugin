jQuery( function( $ ) {

	let payaza_submit = false;

	$( '#wc-payaza-form' ).hide();

	wcPayazaFormHandler();

	jQuery( '#payaza-payment-button' ).click( function() {
		return wcPayazaFormHandler();
	} );

	jQuery( '#payaza_form form#order_review' ).submit( function() {
		return wcPayazaFormHandler();
	} );

	function wcPayazaCustomFields() {

		let custom_fields = [
			{
				"display_name": "Plugin",
				"variable_name": "plugin",
				"value": "woo-payaza"
			}
		];

		if ( wc_payaza_params.meta_order_id ) {

			custom_fields.push( {
				display_name: "Order ID",
				variable_name: "order_id",
				value: wc_payaza_params.meta_order_id
			} );

		}

		if ( wc_payaza_params.meta_name ) {

			custom_fields.push( {
				display_name: "Customer Name",
				variable_name: "customer_name",
				value: wc_payaza_params.meta_name
			} );
		}

		if ( wc_payaza_params.meta_email ) {

			custom_fields.push( {
				display_name: "Customer Email",
				variable_name: "customer_email",
				value: wc_payaza_params.meta_email
			} );
		}

		if ( wc_payaza_params.meta_phone ) {

			custom_fields.push( {
				display_name: "Customer Phone",
				variable_name: "customer_phone",
				value: wc_payaza_params.meta_phone
			} );
		}

		if ( wc_payaza_params.meta_billing_address ) {

			custom_fields.push( {
				display_name: "Billing Address",
				variable_name: "billing_address",
				value: wc_payaza_params.meta_billing_address
			} );
		}

		if ( wc_payaza_params.meta_shipping_address ) {

			custom_fields.push( {
				display_name: "Shipping Address",
				variable_name: "shipping_address",
				value: wc_payaza_params.meta_shipping_address
			} );
		}

		if ( wc_payaza_params.meta_products ) {

			custom_fields.push( {
				display_name: "Products",
				variable_name: "products",
				value: wc_payaza_params.meta_products
			} );
		}

		return custom_fields;
	}

	function wcPayazaCustomFilters() {

		let custom_filters = {};

		if ( wc_payaza_params.card_channel ) {

			if ( wc_payaza_params.banks_allowed ) {

				custom_filters[ 'banks' ] = wc_payaza_params.banks_allowed;

			}

			if ( wc_payaza_params.cards_allowed ) {

				custom_filters[ 'card_brands' ] = wc_payaza_params.cards_allowed;
			}

		}

		return custom_filters;
	}

	function wcPaymentChannels() {

		let payment_channels = [];

		if ( wc_payaza_params.bank_channel ) {
			payment_channels.push( 'bank' );
		}

		if ( wc_payaza_params.card_channel ) {
			payment_channels.push( 'card' );
		}

		if ( wc_payaza_params.bank_transfer_channel ) {
			payment_channels.push( 'bank_transfer' );
		}

		return payment_channels;
	}

	function wcPayazaFormHandler() {

		$( '#wc-payaza-form' ).hide();

		if ( payaza_submit ) {
			payaza_submit = false;
			return true;
		}

		let $form = $( 'form#payment-form, form#order_review' ),
			payaza_txnref = $form.find( 'input.payaza_txnref' ),
			subaccount_code = '',
			charges_account = '',
			transaction_charges = '';

		payaza_txnref.val( '' );

		if ( wc_payaza_params.subaccount_code ) {
			subaccount_code = wc_payaza_params.subaccount_code;
		}

		if ( wc_payaza_params.charges_account ) {
			charges_account = wc_payaza_params.charges_account;
		}

		if ( wc_payaza_params.transaction_charges ) {
			transaction_charges = Number( wc_payaza_params.transaction_charges );
		}

		let amount = Number( wc_payaza_params.amount );

		let payaza_callback = function( response ) {
			$form.append( '<input type="hidden" class="payaza_txnref" name="payaza_txnref" value="' + response.trxref + '"/>' );
			payaza_submit = true;

			$form.submit();

			$( 'body' ).block( {
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				},
				css: {
					cursor: "wait"
				}
			} );
		};

		let paymentData = {
			key: wc_payaza_params.key,
			email: wc_payaza_params.email,
			amount: amount,
			ref: wc_payaza_params.txnref,
			currency: wc_payaza_params.currency,
			callback: payaza_callback,
			subaccount: subaccount_code,
			bearer: charges_account,
			transaction_charge: transaction_charges,
			metadata: {
				custom_fields: wcPayazaCustomFields(),
			},
			onClose: function() {
				$( '#wc-payaza-form' ).show();
				$( this.el ).unblock();
			}
		};

		if ( Array.isArray( wcPaymentChannels() ) && wcPaymentChannels().length ) {
			paymentData[ 'channels' ] = wcPaymentChannels();
			if ( !$.isEmptyObject( wcPayazaCustomFilters() ) ) {
				paymentData[ 'metadata' ][ 'custom_filters' ] = wcPayazaCustomFilters();
			}
		}

		let handler = PayazaPop.setup( paymentData );

		handler.openIframe();

		return false;

	}

} );