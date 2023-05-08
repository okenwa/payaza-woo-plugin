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


	
	function wcPayazaFormHandler() {

		$( '#wc-payaza-form' ).hide();

		if ( payaza_submit ) {
			payaza_submit = false;
			return true;
		}

		let $form = $( 'form#payment-form, form#order_review' ),
			payaza_txnref = $form.find( 'input.payaza_txnref' );
		

		payaza_txnref.val( '' );

		
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

		const payazaCheckout = PayazaCheckout.setup( {
				merchant_key: wc_payaza_params.key,
                connection_mode: "Live", // Live || Test
                checkout_amount: amount/100,
				currency_code: wc_payaza_params.currency,
			
                email_address: wc_payaza_params.email,
                first_name: "okenwa",
                last_name: "ikwan",
                phone_number:"0494949494",
                transaction_reference: wc_payaza_params.txnref,
		
			onClose: function() {
                console.log("Closed")
				//$( '#wc-payaza-form' ).show();
				//$( this.el ).unblock();
			},
			callback: function (callbackResponse) {
				console.log('callback response', callbackResponse)
			}
		});

		function callback(callbackResponse) {
			console.log('callbackResponse: ', callbackResponse)
		}

		function onClose() {
			console.log("closed")
		}

	
		//let handler = PayazaCheckout.setup( paymentData );
		payazaCheckout.setCallback(callback)
		payazaCheckout.setOnClose(onClose)

		
		//let handler = 
		payazaCheckout.showPopup();

		//handler.openIframe();

		return false;

	}

} );