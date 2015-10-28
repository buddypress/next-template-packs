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
			$( '#buddypress' ).on( 'bp_heartbeat_prepend', '#activity-stream', this.adjustMentionsCount );
		},

		adjustMentionsCount: function( event ) {
			if ( 'mentions' === bp.Next.getStorage( 'bp-activity', 'scope' ) ) {
				bp.Next.Activity.ajax( { action: 'activity_clear_new_mentions' } ).done( function( response ) {
					if ( false === response.success ) {
						// Display a warning ?
						console.log( 'warning' );
					}
				} );

				// Remove highlighted new mentions
				setTimeout( function () {
					$( event.currentTarget ).find( '.activity-item' ).removeClass( 'new_mention' );
				}, 3000 );
			}
		},

		ajax: function( post_data ) {
			$.extend( post_data, bp.Next.getStorage( 'bp-activity' ), { nonce: BP_Next.nonces.activity } );

			return $.post( ajaxurl, post_data, 'json' );
		}
	}

	// Launch BP Next Activity
	bp.Next.Activity.start();

	/** Events specific to the Activity object requiring the user to be logged in */

	/**
	 * A link into the activity item has been clicked
	 */
	$( '#buddypress #activity-stream' ).on( 'click', '.activity-item', function( event ) {
		var button = $( event.target ), activity_item = $( event.currentTarget ),
			activity_id = activity_item.data( 'id' ), stream = $( event.delegateTarget );

		// Favoriting
		if ( button.hasClass( 'fav') || button.hasClass('unfav') ) {
			var type = button.hasClass( 'fav' ) ? 'fav' : 'unfav';

			// Stop event propagation
			event.preventDefault();

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
					if ( undefined !== response.data.directory_tab ) {
						if ( ! $( bp.Next.objectNavParent + ' [data-scope="favorites"]' ).length ) {
							$( bp.Next.objectNavParent + ' [data-scope="all"]' ).after( response.data.directory_tab );
						} else {
							$( bp.Next.objectNavParent + ' [data-scope="favorites"] span' ).html( 1 );
						}
					}

					if ( undefined !== response.data.fav_count ) {
						$( bp.Next.objectNavParent + ' [data-scope="favorites"] span' ).html( response.data.fav_count );
					}

					button.removeClass( 'fav' );
					button.addClass( 'unfav' );

				} else if ( 'unfav' === type ) {
					if ( undefined !== response.data.no_favorite ) {
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

					if ( undefined !== response.data.fav_count ) {
						$( bp.Next.objectNavParent + ' [data-scope="favorites"] span' ).html( response.data.fav_count );

						if ( $( bp.Next.objectNavParent + ' [data-scope="favorites"]' ).hasClass( 'selected' ) ) {
							activity_item.remove();
						}
					}

					button.removeClass( 'unfav' );
					button.addClass( 'fav' );
				}
			} );
		}
	} );

} )( bp, jQuery );
