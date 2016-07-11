/* global bp, BP_Nouveau */
window.bp = window.bp || {};

( function( exports, $ ) {

	// Bail if not set
	if ( typeof BP_Nouveau === 'undefined' ) {
		return;
	}

	/**
	 * This an ugly copy from Legacy's buddypress.js for now
	 *
	 * This really needs to be improved !
	 */

	/** Profile Visibility Settings *********************************/

	$( '.visibility-toggle-link' ).on( 'click', function( event ) {
		event.preventDefault();

		$( this ).parent().hide().addClass( 'field-visibility-settings-hide' )
			.siblings( '.field-visibility-settings' ).show().addClass( 'field-visibility-settings-open' );
	} );

	$( '.field-visibility-settings-close' ).on( 'click', function( event ) {
		event.preventDefault();

		var settings_div = $( this ).parent(),
			vis_setting_text = settings_div.find( 'input:checked' ).parent().text();

		settings_div.hide().removeClass( 'field-visibility-settings-open' )
			.siblings( '.field-visibility-settings-toggle' )
				.children( '.current-visibility-level' ).text( vis_setting_text ).end()
			.show().removeClass( 'field-visibility-settings-hide' );
	} );

	$( '#profile-edit-form input:not(:submit), #profile-edit-form textarea, #profile-edit-form select, #signup_form input:not(:submit), #signup_form textarea, #signup_form select' ).change( function() {
		var shouldconfirm = true;

		$( '#profile-edit-form input:submit, #signup_form input:submit' ).on( 'click', function() {
			shouldconfirm = false;
		} );

		window.onbeforeunload = function( e ) {
			if ( shouldconfirm ) {
				return BP_Nouveau.unsaved_changes;
			}
		};
	} );

	window.clear = function( container ) {
		if ( ! container ) {
			return;
		}

		container = container.replace( '[', '\\[' ).replace( ']', '\\]' );

		if ( $( '#' + container + ' option' ).length ) {
			$.each( $( '#' + container + ' option' ), function( c, option ) {
				$( option ).prop( 'selected', false );
			} );
		} else if ( $( '#' + container + ' [type=radio]' ).length ) {
			$.each( $( '#' + container + ' [type=radio]' ), function( c, checkbox ) {
				$( checkbox ).prop( 'checked', false );
			} );
		}
	};
} )( bp, jQuery );
