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
			$( '#buddypress' ).on( 'bp_heartbeat_pending', '#activity-stream', this.prepareScope );
			
			$( '#buddypress' ).on( 'bp_heartbeat_prepend', '#activity-stream', this.updateScope );

			$( '#buddypress' ).on( 'bp_ajax_request', '.bp-activity-list', this.scopeLoaded );
		},

		prepareScope: function( event ) {
			var objects = bp.Next.objects;
			
			/**
			 * It's not a regular object but we need it!
			 * so let's add it temporarly..
			 */
			objects.push( 'mentions' );

			$.each( objects, function( o, object ) {
				if ( undefined !== bp.Next.highlights[ object ] && bp.Next.highlights[ object ].length ) {
					$( bp.Next.objectNavParent + ' [data-scope="' + object + '"]' ).find( 'a span' ).html( Number( bp.Next.highlights[ object ].length ) );
				}
			} );

			/**
			 * Let's remove the mentions from objects!
			 */
			 objects.pop();
		},

		updateScope: function( event ) {
			var scope = bp.Next.getStorage( 'bp-activity', 'scope' );

			// Specific to mentions
			if ( 'mentions' === scope ) {
				// Now mentions are displayed, remove the user_metas
				bp.Next.Activity.ajax( { action: 'activity_clear_new_mentions' } ).done( function( response ) {
					if ( false === response.success ) {
						// Display a warning ?
						console.log( 'warning' );
					}
				} );
			}

			// Activities are now displayed, clear the newest count for the scope
			$( bp.Next.objectNavParent + ' [data-scope="' + scope + '"]' ).find( 'a span' ).html( '' );

			// Activities are now displayed, clear the highlighted activities for the scope
			if ( undefined !== bp.Next.highlights[ scope ] ) {
				bp.Next.highlights[ scope ] = [];
			}
			
			// Remove highlighted for the current scope
			setTimeout( function () {
				$( event.currentTarget ).find( '.activity-item' ).removeClass( 'newest_' + scope + '_activity' );
			}, 3000 );
		},

		scopeLoaded: function ( event, data ) {
			// Mentions are specific
			if ( 'mentions' === data.scope && undefined !== data.response.new_mentions ) {
				$.each( data.response.new_mentions, function( i, id ) {
					$( '#buddypress #activity-stream' ).find( '[data-id="' + id + '"]' ).addClass( 'newest_mentions_activity' );
				} );
			} else if ( undefined !== bp.Next.highlights[data.scope] && bp.Next.highlights[data.scope].length ) {
				$.each( bp.Next.highlights[data.scope], function( i, id ) {
					if ( $( '#buddypress #activity-stream' ).find( '[data-id="' + id + '"]' ).length ) {
						$( '#buddypress #activity-stream' ).find( '[data-id="' + id + '"]' ).addClass( 'newest_' + data.scope + '_activity' );
					}
				} );
			}

			// Activities are now loaded, clear the highlighted activities for the scope
			if ( undefined !== bp.Next.highlights[ data.scope ] ) {
				bp.Next.highlights[ data.scope ] = [];
			}

			setTimeout( function () {
				$( '#buddypress #activity-stream .activity-item' ).removeClass( 'newest_' + data.scope +'_activity' );
			}, 3000 );
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
						}
					}

					button.removeClass( 'fav' );
					button.addClass( 'unfav' );

				} else if ( 'unfav' === type ) {
					// If on user's profile or on the favorites directory tab, remove the entry
					if ( ! $( bp.Next.objectNavParent + ' [data-scope="favorites"]' ).length || $( bp.Next.objectNavParent + ' [data-scope="favorites"]' ).hasClass( 'selected' )  ) {
						activity_item.remove();
					}

					if ( undefined !== response.data.no_favorite ) {
						// Remove the tab when on activity directory but not on the favorites tabs
						if ( $( bp.Next.objectNavParent + ' [data-scope="all"]' ).length && $( bp.Next.objectNavParent + ' [data-scope="all"]' ).hasClass( 'selected' ) ) {
							$( bp.Next.objectNavParent + ' [data-scope="favorites"]' ).remove();

						// In all the other cases, append a message to the empty stream
						} else {
							stream.append( response.data.no_favorite );
						}
					}

					button.removeClass( 'unfav' );
					button.addClass( 'fav' );
				}
			} );
		}
	} );

} )( bp, jQuery );
