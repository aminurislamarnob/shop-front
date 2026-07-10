/**
 * Inventory Manager — stock list interactions.
 *
 * Served raw (not webpack-built). Vanilla jQuery + SweetAlert2, matching the
 * StoreSuite frontend convention. Localised as StoreSuiteInventory.
 */
( function ( $ ) {
	'use strict';

	if ( typeof StoreSuiteInventory === 'undefined' ) {
		return;
	}

	var cfg = StoreSuiteInventory;

	function post( data ) {
		return $.ajax( {
			url: cfg.ajax_url,
			type: 'POST',
			data: $.extend( { security: cfg.nonce }, data ),
		} );
	}

	function notifyError( message ) {
		if ( typeof Swal !== 'undefined' ) {
			Swal.fire( { icon: 'error', title: cfg.i18n.error, text: message } );
		} else {
			window.alert( message ); // eslint-disable-line no-alert
		}
	}

	/* Inline single-item save. */
	$( document ).on( 'click', '.storesuite-inventory-save', function () {
		var $row = $( this ).closest( 'tr' );
		var qty = $row.find( '.storesuite-inventory-qty' ).val();

		post( {
			action: 'storesuite_inventory_set_stock',
			product_id: $row.data( 'id' ),
			qty: qty,
		} ).done( function ( res ) {
			if ( res && res.success ) {
				$row.addClass( 'storesuite-row-saved' );
				setTimeout( function () {
					$row.removeClass( 'storesuite-row-saved' );
				}, 1200 );
			} else {
				notifyError( res && res.data ? res.data.error : cfg.i18n.error );
			}
		} ).fail( function () {
			notifyError( cfg.i18n.error );
		} );
	} );

	/* Select-all. */
	$( document ).on( 'change', '#storesuite-inventory-select-all', function () {
		$( '.storesuite-inventory-check' ).prop( 'checked', this.checked );
	} );

	/* Bulk apply. */
	$( document ).on( 'click', '#storesuite-inventory-bulk-apply', function () {
		var ids = $( '.storesuite-inventory-check:checked' )
			.map( function () {
				return this.value;
			} )
			.get();

		if ( ! ids.length ) {
			notifyError( cfg.i18n.selectProducts );
			return;
		}

		var op = $( '#storesuite-inventory-bulk-op' ).val();
		var qty = $( '#storesuite-inventory-bulk-qty' ).val();

		var run = function () {
			post( {
				action: 'storesuite_inventory_bulk_update',
				ids: ids,
				op: op,
				qty: qty,
			} ).done( function ( res ) {
				if ( res && res.success ) {
					window.location.reload();
				} else {
					notifyError( res && res.data ? res.data.error : cfg.i18n.error );
				}
			} ).fail( function () {
				notifyError( cfg.i18n.error );
			} );
		};

		if ( typeof Swal !== 'undefined' ) {
			Swal.fire( {
				icon: 'question',
				title: cfg.i18n.confirmBulk,
				showCancelButton: true,
			} ).then( function ( result ) {
				if ( result.isConfirmed ) {
					run();
				}
			} );
		} else {
			run();
		}
	} );
} )( jQuery );
