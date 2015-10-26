/* global bp, BP_Next, ajaxurl */
window.bp = window.bp || {};

( function( exports, $ ) {

	// Bail if not set
	if ( typeof BP_Next === 'undefined' ) {
		return;
	}

	bp.Next = bp.Next || {};

	bp.Next.Activity = {
		start: function() {
			console.log( this );
		},

		ajax: function( post_data ) {
			$.extend( post_data, bp.Next.getStorage( 'bp-activity' ), { nonce: BP_Next.nonces.activity } );

			return $.post( ajaxurl, post_data, 'json' );
		}
	}

	/** DOM Events specific to the Activity object */
	$( '#buddypress #activity-stream' ).on( 'click', '.activity-item', function( event ) {
		var button = $( event.target ), activity_item = $( event.currentTarget ),
			activity_id = activity_item.data( 'id' ), stream = $( event.delegateTarget ); 

		event.preventDefault();

		if ( button.hasClass( 'fav') || button.hasClass('unfav') ) {
			var type = button.hasClass('fav') ? 'fav' : 'unfav';

			button.addClass( 'loading' );

			bp.Next.Activity.ajax( { action: 'activity_mark_' + type, 'id': activity_id } ).done( function( response ) {
				button.removeClass( 'loading' );

				if ( false === response.success ) {
					return;
				} else {
					button.fadeOut( 200, function() {
						$( this ).html( response.data.content );
						$( this ).prop( 'title', response.data.content );
						$( this ).fadeIn( 200 );
					} );
				}

				if ( 'fav' === type ) {
					if ( 'undefined' !== response.data.directory_tab ) {
						if ( ! $( bp.Next.objectNavParent + ' [data-scope="favorites"]' ).length ) {
							$( bp.Next.objectNavParent + ' [data-scope="all"]' ).after( response.data.directory_tab );
						} else {
							$( bp.Next.objectNavParent + ' [data-scope="favorites"] span' ).html( 1 );
						}
					}

					if ( 'undefined' !== response.data.fav_count ) {
						$( bp.Next.objectNavParent + ' [data-scope="favorites"] span' ).html( response.data.fav_count );
					}
					
					button.removeClass( 'fav' );
					button.addClass( 'unfav' );

				} else if ( 'unfav' === type ) {
					if ( 'undefined' !== response.data.no_favorite ) {
						if ( $( bp.Next.objectNavParent + ' [data-scope="favorites"]' ).hasClass( 'selected' ) ) {
							$( bp.Next.objectNavParent + ' [data-scope="favorites"] span' ).html( 0 );
							activity_item.remove();
							stream.append( response.data.no_favorite );
						} else if ( ! $( bp.Next.objectNavParent + ' [data-scope="favorites"]' ).length ) {
							activity_item.remove();
							stream.append( response.data.no_favorite );
						} else {
							$( bp.Next.objectNavParent + ' [data-scope="favorites"]' ).remove();
						}
					}

					if ( 'undefined' !== response.data.fav_count ) {
						$( bp.Next.objectNavParent + ' [data-scope="favorites"] span' ).html( response.data.fav_count );
					}
					
					button.removeClass( 'unfav' );
					button.addClass( 'fav' );
				}
			} );
		}
	} );

	// Launch BP Next
	bp.Next.Activity.start();

} )( bp, jQuery );