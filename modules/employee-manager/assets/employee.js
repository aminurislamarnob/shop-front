/**
 * Employee Manager — Team screen interactions.
 *
 * Served raw (not webpack-built). Matches the vanilla jQuery + SweetAlert2
 * conventions of assets/frontend/form-handler.js. Localised as StoreSuiteEmployee.
 */
( function ( $ ) {
	'use strict';

	if ( typeof StoreSuiteEmployee === 'undefined' ) {
		return;
	}

	var cfg = StoreSuiteEmployee;

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

	function reload() {
		window.location.href = cfg.listUrl;
	}

	/* ---------------------------------------------------------------------
	 * Employee add / edit modal
	 * ------------------------------------------------------------------- */
	var $modal = $( '#storesuite-employee-modal' );

	function openModal() {
		$modal.prop( 'hidden', false );
	}

	function closeModal() {
		$modal.prop( 'hidden', true );
	}

	function resetForm() {
		var $form = $( '#storesuite-employee-form' );
		$form[ 0 ].reset();
		$form.find( '[name="user_id"]' ).val( '' );
		$form.find( '[data-field="email"]' ).show();
		$( '#storesuite-employee-modal-title' ).text( $( '#storesuite-add-employee-trigger' ).text().trim() );
	}

	$( document ).on( 'click', '#storesuite-add-employee-trigger', function () {
		resetForm();
		openModal();
	} );

	$( document ).on( 'click', '#storesuite-employee-cancel', function () {
		closeModal();
	} );

	$( document ).on( 'click', '.storesuite-edit-employee', function () {
		var $row = $( this ).closest( 'tr' );
		var $form = $( '#storesuite-employee-form' );

		resetForm();
		$form.find( '[name="user_id"]' ).val( $row.data( 'user-id' ) );
		$form.find( '[name="first_name"]' ).val( $row.data( 'first-name' ) );
		$form.find( '[name="last_name"]' ).val( $row.data( 'last-name' ) );
		$form.find( '[name="role"]' ).val( String( $row.data( 'role' ) ) );
		// Editing an existing account — email is immutable here.
		$form.find( '[data-field="email"]' ).hide();
		$( '#storesuite-employee-modal-title' ).text( $( '.storesuite-edit-employee' ).first().text().trim() );
		openModal();
	} );

	$( document ).on( 'submit', '#storesuite-employee-form', function ( e ) {
		e.preventDefault();

		var $form = $( this );
		var userId = $form.find( '[name="user_id"]' ).val();
		var action = userId ? 'storesuite_update_employee' : 'storesuite_add_employee';
		var data = {
			action: action,
			user_id: userId,
			first_name: $form.find( '[name="first_name"]' ).val(),
			last_name: $form.find( '[name="last_name"]' ).val(),
			email: $form.find( '[name="email"]' ).val(),
			role: $form.find( '[name="role"]' ).val(),
		};

		post( data ).done( function ( res ) {
			if ( res && res.success ) {
				reload();
			} else {
				notifyError( res && res.data ? res.data.error : cfg.i18n.error );
			}
		} ).fail( function () {
			notifyError( cfg.i18n.error );
		} );
	} );

	/* ---------------------------------------------------------------------
	 * Suspend / activate
	 * ------------------------------------------------------------------- */
	$( document ).on( 'click', '.storesuite-toggle-suspend', function () {
		var $row = $( this ).closest( 'tr' );
		var status = $( this ).data( 'status' );

		var run = function () {
			post( {
				action: 'storesuite_suspend_employee',
				user_id: $row.data( 'user-id' ),
				status: status,
			} ).done( function ( res ) {
				if ( res && res.success ) {
					reload();
				} else {
					notifyError( res && res.data ? res.data.error : cfg.i18n.error );
				}
			} );
		};

		if ( 'suspended' === status && typeof Swal !== 'undefined' ) {
			Swal.fire( {
				icon: 'warning',
				title: cfg.i18n.confirmSuspend,
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

	/* ---------------------------------------------------------------------
	 * Remove employee
	 * ------------------------------------------------------------------- */
	$( document ).on( 'click', '.storesuite-delete-employee', function () {
		var $row = $( this ).closest( 'tr' );

		var run = function () {
			post( {
				action: 'storesuite_delete_employee',
				user_id: $row.data( 'user-id' ),
			} ).done( function ( res ) {
				if ( res && res.success ) {
					reload();
				} else {
					notifyError( res && res.data ? res.data.error : cfg.i18n.error );
				}
			} );
		};

		if ( typeof Swal !== 'undefined' ) {
			Swal.fire( {
				icon: 'warning',
				title: cfg.i18n.confirmDelete,
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

	/* ---------------------------------------------------------------------
	 * Roles editor
	 * ------------------------------------------------------------------- */
	function resetRoleForm() {
		var $form = $( '#storesuite-role-form' );
		if ( ! $form.length ) {
			return;
		}
		$form[ 0 ].reset();
		$form.find( '[name="slug"]' ).val( '' );
		$form.find( 'input[name="areas[]"]' ).each( function () {
			this.checked = 'access_dashboard' === this.value;
		} );
	}

	$( document ).on( 'click', '#storesuite-role-reset', function () {
		resetRoleForm();
	} );

	$( document ).on( 'click', '.storesuite-edit-role', function () {
		var $row = $( this ).closest( 'tr' );
		var $form = $( '#storesuite-role-form' );
		var areas = String( $row.data( 'areas' ) || '' ).split( ',' );

		resetRoleForm();
		$form.find( '[name="slug"]' ).val( $row.data( 'slug' ) );
		$form.find( '[name="label"]' ).val( $row.data( 'label' ) );
		$form.find( 'input[name="areas[]"]' ).each( function () {
			this.checked = 'access_dashboard' === this.value || areas.indexOf( this.value ) !== -1;
		} );
		$( '#storesuite-role-form-title' ).text( $( '.storesuite-edit-role' ).first().text().trim() );
	} );

	$( document ).on( 'submit', '#storesuite-role-form', function ( e ) {
		e.preventDefault();

		var $form = $( this );
		var areas = [];
		$form.find( 'input[name="areas[]"]:checked' ).each( function () {
			areas.push( this.value );
		} );

		post( {
			action: 'storesuite_save_custom_role',
			slug: $form.find( '[name="slug"]' ).val(),
			label: $form.find( '[name="label"]' ).val(),
			areas: areas,
		} ).done( function ( res ) {
			if ( res && res.success ) {
				window.location.reload();
			} else {
				notifyError( res && res.data ? res.data.error : cfg.i18n.error );
			}
		} ).fail( function () {
			notifyError( cfg.i18n.error );
		} );
	} );

	$( document ).on( 'click', '.storesuite-delete-role', function () {
		var $row = $( this ).closest( 'tr' );

		var run = function () {
			post( {
				action: 'storesuite_delete_custom_role',
				slug: $row.data( 'slug' ),
			} ).done( function ( res ) {
				if ( res && res.success ) {
					window.location.reload();
				} else {
					notifyError( res && res.data ? res.data.error : cfg.i18n.error );
				}
			} );
		};

		if ( typeof Swal !== 'undefined' ) {
			Swal.fire( {
				icon: 'warning',
				title: cfg.i18n.confirmDeleteRole,
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
