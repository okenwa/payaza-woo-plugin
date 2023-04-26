jQuery( function( $ ) {
	'use strict';

	/**
	 * Object to handle Payaza admin functions.
	 */
	var wc_payaza_admin = {
		/**
		 * Initialize.
		 */
		init: function() {

			// Toggle api key settings.
			$( document.body ).on( 'change', '#woocommerce_payaza_testmode', function() {
				var test_secret_key = $( '#woocommerce_payaza_test_secret_key' ).parents( 'tr' ).eq( 0 ),
					test_public_key = $( '#woocommerce_payaza_test_public_key' ).parents( 'tr' ).eq( 0 ),
					live_secret_key = $( '#woocommerce_payaza_live_secret_key' ).parents( 'tr' ).eq( 0 ),
					live_public_key = $( '#woocommerce_payaza_live_public_key' ).parents( 'tr' ).eq( 0 );

				if ( $( this ).is( ':checked' ) ) {
					test_secret_key.show();
					test_public_key.show();
					live_secret_key.hide();
					live_public_key.hide();
				} else {
					test_secret_key.hide();
					test_public_key.hide();
					live_secret_key.show();
					live_public_key.show();
				}
			} );

			$( '#woocommerce_payaza_testmode' ).change();

			$( document.body ).on( 'change', '.woocommerce_payaza_split_payment', function() {
				var subaccount_code = $( '.woocommerce_payaza_subaccount_code' ).parents( 'tr' ).eq( 0 ),
					subaccount_charge = $( '.woocommerce_payaza_split_payment_charge_account' ).parents( 'tr' ).eq( 0 ),
					transaction_charge = $( '.woocommerce_payaza_split_payment_transaction_charge' ).parents( 'tr' ).eq( 0 );

				if ( $( this ).is( ':checked' ) ) {
					subaccount_code.show();
					subaccount_charge.show();
					transaction_charge.show();
				} else {
					subaccount_code.hide();
					subaccount_charge.hide();
					transaction_charge.hide();
				}
			} );

			$( '#woocommerce_payaza_split_payment' ).change();

			// Toggle Custom Metadata settings.
			$( '.wc-payaza-metadata' ).change( function() {
				if ( $( this ).is( ':checked' ) ) {
					$( '.wc-payaza-meta-order-id, .wc-payaza-meta-name, .wc-payaza-meta-email, .wc-payaza-meta-phone, .wc-payaza-meta-billing-address, .wc-payaza-meta-shipping-address, .wc-payaza-meta-products' ).closest( 'tr' ).show();
				} else {
					$( '.wc-payaza-meta-order-id, .wc-payaza-meta-name, .wc-payaza-meta-email, .wc-payaza-meta-phone, .wc-payaza-meta-billing-address, .wc-payaza-meta-shipping-address, .wc-payaza-meta-products' ).closest( 'tr' ).hide();
				}
			} ).change();

			// Toggle Bank filters settings.
			$( '.wc-payaza-payment-channels' ).on( 'change', function() {

				var channels = $( ".wc-payaza-payment-channels" ).val();

				if ( $.inArray( 'card', channels ) != '-1' ) {
					$( '.wc-payaza-cards-allowed' ).closest( 'tr' ).show();
					$( '.wc-payaza-banks-allowed' ).closest( 'tr' ).show();
				}
				else {
					$( '.wc-payaza-cards-allowed' ).closest( 'tr' ).hide();
					$( '.wc-payaza-banks-allowed' ).closest( 'tr' ).hide();
				}

			} ).change();

			$( ".wc-payaza-payment-icons" ).select2( {
				templateResult: formatPayazaPaymentIcons,
				templateSelection: formatPayazaPaymentIconDisplay
			} );

			$( '#woocommerce_payaza_test_secret_key, #woocommerce_payaza_live_secret_key' ).after(
				'<button class="wc-payaza-toggle-secret" style="height: 30px; margin-left: 2px; cursor: pointer"><span class="dashicons dashicons-visibility"></span></button>'
			);

			$( '.wc-payaza-toggle-secret' ).on( 'click', function( event ) {
				event.preventDefault();

				let $dashicon = $( this ).closest( 'button' ).find( '.dashicons' );
				let $input = $( this ).closest( 'tr' ).find( '.input-text' );
				let inputType = $input.attr( 'type' );

				if ( 'text' == inputType ) {
					$input.attr( 'type', 'password' );
					$dashicon.removeClass( 'dashicons-hidden' );
					$dashicon.addClass( 'dashicons-visibility' );
				} else {
					$input.attr( 'type', 'text' );
					$dashicon.removeClass( 'dashicons-visibility' );
					$dashicon.addClass( 'dashicons-hidden' );
				}
			} );
		}
	};

	function formatPayazaPaymentIcons( payment_method ) {
		if ( !payment_method.id ) {
			return payment_method.text;
		}

		var $payment_method = $(
			'<span><img src=" ' + wc_payaza_admin_params.plugin_url + '/assets/images/' + payment_method.element.value.toLowerCase() + '.png" class="img-flag" style="height: 15px; weight:18px;" /> ' + payment_method.text + '</span>'
		);

		return $payment_method;
	};

	function formatPayazaPaymentIconDisplay( payment_method ) {
		return payment_method.text;
	};

	wc_payaza_admin.init();

} );