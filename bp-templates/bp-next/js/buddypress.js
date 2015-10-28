/* global wp, bp, BP_Next, ajaxurl */
window.wp = window.wp || {};
window.bp = window.bp || {};

( function( exports, $ ) {

	// Bail if not set
	if ( typeof BP_Next === 'undefined' ) {
		return;
	}

	bp.Next = {
		start: function() {
			this.ajax_request           = null;

			// Object Globals
			this.objects                = BP_Next.objects;
			this.objectNavParent        = BP_Next.object_nav_parent;
			this.just_posted            = [];

			// HeartBeat Globals
			this.heartbeat              = wp.heartbeat || {};
			this.newest_activities      = '';
			this.activity_last_recorded = 0;
			this.first_item_recorded    = 0;
			this.document_title         = $( document ).prop( 'title' );

			if ( $( 'body' ).hasClass( 'no-js' ) ) {
				$('body').removeClass( 'no-js' ).addClass( 'js' );
			}

			this.prepareFields();
			this.initObjects();
			this.setHeartBeat();

			// Listeners
			$( document ).on( 'heartbeat-send.buddypress', this.heartbeatSend );
			$( document ).on( 'heartbeat-tick.buddypress', this.heartbeatTick );
		},

		prepareFields: function() {
			// Transform text field into search field
			$( '#buddypress li.dir-search input[type=text]' ).prop( 'type', 'search' );
		},

		/**
		 * Get the object state
		 */
		getStorage: function( type, property ) {
			var store = sessionStorage.getItem( type );

			if ( store ) {
				store = JSON.parse( store );
			} else {
				store = {};
			}

			if ( undefined !== property && store[property] ) {
				return store[property];
			}

			return store;
		},

		/**
		 * Set the object state
		 */
		setStorage: function( type, property, value ) {
			var store = this.getStorage( type );

			if ( undefined === value && undefined !== store[ property ] ) {
				delete store[ property ];
			} else {
				// Set property
				store[ property ] = value;
			}

			sessionStorage.setItem( type, JSON.stringify( store ) );

			return sessionStorage.getItem( type ) !== null;
		},

		truncateComments: function( event, data ) {
			var comments = $( event.target ).find( '.activity-comments' ),
				activity_item, comment_items, comment_count;

			if ( ! comments.length || 1 !== data.page ) {
				return;
			}

			comments.each( function( c, comment ) {
				comment_items = $( comment ).children( 'ul' ).find( 'li' );

				if ( ! comment_items.length ) {
					return;
				}

				// Get the activity id
				activity_item = $( comment ).closest( '.activity-item' );

				// Get the comment count
				comment_count = $('#acomment-comment-' + activity_item.data( 'id' ) + ' span' ).html() || ' ';

				// Keep first 5 root comments
				comment_items.each( function( i, item ) {
					if ( i < comment_items.length - 5 ) {
						$( item ).addClass( 'hidden' );
						$( item ).toggle();

						// Prepend a link to display all
						if ( ! i ) {
							$( item ).before( '<li class="show-all"><a href="#' + activity_item.prop( 'id' ) + '/show-all/" title="' + BP_Next.show_all_comments + '">' + BP_Next.show_x_comments.replace( '%d', comment_count ) + '</a></li>' );
						}
					}
				} );
			} );
		},

		/**
		 * Query objects and refresh the objets list
		 */
		objectRequest: function( object, scope, filter, target, search_terms, page, extras, caller, template ) {
			var postdata, clone = {};

			if ( null !== scope ) {
				this.setStorage( 'bp-' + object, 'scope', scope );
			}

			if ( null !== filter ) {
				this.setStorage( 'bp-' + object, 'filter', filter );
			}

			if ( null !== extras ) {
				this.setStorage( 'bp-' + object, 'extras', extras );
			}

			/* Set the correct selected nav and filter */
			$( this.objectNavParent + ' .bp-' + object + '-primary-nav' ).each( function() {
				$( this ).removeClass( 'selected loading' );
			} );

			$( this.objectNavParent + ' [data-scope="' + scope + '"], #object-nav li.current').addClass( 'selected loading' );
			$( '#buddypress [data-filter="' + object + '"] option[value="' + filter + '"]' ).prop( 'selected', true );

			if ( 'friends' === object || 'group_members' === object ) {
				object = 'members';
			}

			if ( this.ajax_request ) {
				this.ajax_request.abort();
			}

			clone = {
				'object':                 object,
				'search_terms':           search_terms,
				'page':                   page,
				'template':               template
			};

			if ( 'activity' === object ) {
				page = page || 1;

				clone.exclude_just_posted = this.just_posted.join( ',' );

				// Set the page
				this.setStorage( 'bp-activity', 'page', page );
				delete clone.page;
			}

			$.extend( clone, this.getStorage( 'bp-' + object ) );

			postdata = $.extend( {
				action:                   object + '_filter',
				'_bpnonce_object_filter': $( '#_wpnonce_' + object + '_filter' ).val(),
			}, clone );

			this.ajax_request = $.post( ajaxurl, postdata, function( response ) {
				if ( false === response.success ) {
					return;
				}

				/* animate to top if called from bottom pagination */
				if ( caller === 'pag-bottom' && $( '#subnav' ).length ) {
					var top = $('#subnav').parent();
					$( 'html,body' ).animate( { scrollTop: top.offset().top }, 'slow', function() {
						$( target ).fadeOut( 100, function() {
							$( this ).html( response.data.contents );
							$( this ).fadeIn( 100 );

							// Inform other scripts the list of objects has been refreshed.
							$( target ).trigger( 'bp_ajax_request', $.extend( clone, { response: response.data } ) );
						} );
					} );

				} else if ( 'activity' !== object ) {
					$( target ).fadeOut( 100, function() {
						$( this ).html( response.data.contents );
						$( this ).fadeIn( 100 );

						// Inform other scripts the list of objects has been refreshed.
						$( target ).trigger( 'bp_ajax_request', $.extend( clone, { response: response.data } ) );
					} );
				} else {
					// Loading the full stream
					if ( 1 === postdata.page ) {
						$( '#buddypress #activity-stream' ).fadeOut( 100, function() {
							$( this ).html( response.data.contents );
							$( this ).fadeIn( 100 );

							// Inform other scripts the list of activities has been refreshed.
							$( target ).trigger( 'bp_ajax_request', $.extend( clone, { response: response.data } ) );
						} );

						/* Update the feed link */
						if ( undefined !== response.data.feed_url ) {
							$( '.directory #subnav .feed a, .home-page #subnav .feed a').prop( 'href', response.data.feed_url );
						}

					// Appending more activities to current stream
					} else {
						$( '#buddypress #activity-stream' ).append( response.data.contents );
						$( '#buddypress #activity-stream li.load-more' ).remove();

						// Inform other scripts the list of activities has been refreshed.
						$( target ).trigger( 'bp_ajax_append', $.extend( clone, { response: response.data } ) );
					}
				}

				$( this.objectNavParent + ' .item-list-tabs .selected' ).removeClass( 'loading' );

			}, 'json' );
		},

		/**
		 * Loop through supported objects to init the scope and filter
		 * regarding to the sessionStorage state.
		 *
		 * Init the Object loop if needed
		 */
		initObjects: function() {
			var self = this, objectData = {}, scope = 'all';

			// Remove the directory title if there's a widget containing it
			if ( $( '.buddypress_object_nav .widget-title' ).length ) {
				var text = $( '.buddypress_object_nav .widget-title' ).html();

				$( 'body' ).find( '*:contains("' + text + '")' ).each( function( e, element ) {
					if ( ! $( element ).hasClass( 'widget-title' ) && text === $( element ).html() ) {
						$( element ).remove();
					}
				} );
			}

			$.each( this.objects, function( o, object ) {
				objectData = self.getStorage( 'bp-' + object );

				if ( undefined !== objectData.scope ) {
					scope = objectData.scope;
				}

				if ( undefined !== objectData.filter && $( '#buddypress [data-filter="' + object + '"]' ).length ) {
					$( '#buddypress [data-filter="' + object + '"] option[value="' + objectData.filter + '"]' ).prop( 'selected', true );
				}

				if ( $( this.objectNavParent + ' .bp-' + object + '-primary-nav' ).length ) {
					$( this.objectNavParent + ' .bp-' + object + '-primary-nav' ).each( function() {
						$( this ).removeClass( 'selected' );
					} );

					$( this.objectNavParent + ' [data-scope="' + object + '"], #object-nav li.current' ).addClass( 'selected' );
				}

				if ( $( '#buddypress .bp-' + object + '-list').length ) {
					// Populate the object list
					self.objectRequest( object, scope, objectData.filter, '#buddypress .bp-' + object + '-list', '', 1 );
				}
			} );
		},

		setHeartBeat: function() {
			if ( typeof BP_Next.pulse === 'undefined' || ! this.heartbeat ) {
				return;
			}

			this.heartbeat.interval( Number( BP_Next.pulse ) );

			// Extend "send" with BuddyPress namespace
			$.fn.extend( {
				'heartbeat-send': function() {
					return this.bind( 'heartbeat-send.buddypress' );
				}
			} );

			// Extend "tick" with BuddyPress namespace
			$.fn.extend( {
				'heartbeat-tick': function() {
					return this.bind( 'heartbeat-tick.buddypress' );
				}
			} );
		},

		getActivityTimestamp: function( selector ) {
			if ( ! selector.length  ) {
				return null;
			}

			var timestamp = selector.find( '.activity-time-since span' ).data( 'timestamp' ) || 0;

			if ( ! timestamp ) {
				timestamp = selector.prop( 'class' ).match( /date-recorded-([0-9]+)/ );

				if ( null !== timestamp && timestamp[1] ) {
					timestamp = timestamp[1];
				}
			}

			return Number( timestamp );
		},

		heartbeatSend: function( event, data ) {
			bp.Next.first_item_recorded = bp.Next.getActivityTimestamp( $( '#buddypress #activity-stream .activity-item' ).first() ) || 0;

			if ( 0 === bp.Next.activity_last_recorded || bp.Next.first_item_recorded > bp.Next.activity_last_recorded ) {
				bp.Next.activity_last_recorded = bp.Next.first_item_recorded;
			}

			data.bp_activity_last_recorded = bp.Next.activity_last_recorded;

			if ( $( '#buddypress .dir-search input[type=search]' ).length ) {
				data.bp_activity_last_recorded_search_terms = $( '#buddypress .dir-search input[type=search]' ).val();
			}

			$.extend( data, { bp_heartbeat: bp.Next.getStorage( 'bp-activity' ) } );
		},

		heartbeatTick: function( event, data ) {
			var newest_activities_count;

			// Only proceed if we have newest activities
			if ( ! data.bp_activity_newest_activities ) {
				return;
			}

			bp.Next.newest_activities = $.trim( data.bp_activity_newest_activities.activities ) + bp.Next.newest_activities;
			bp.Next.activity_last_recorded  = Number( data.bp_activity_newest_activities.last_recorded );
			newest_activities_count = Number( $( bp.Next.newest_activities ).filter( '.activity-item' ).length );

			$( document ).prop( 'title', '(' + newest_activities_count + ') ' + bp.Next.document_title );

			if ( $( '#buddypress #activity-stream li' ).first().hasClass( 'load-newest' ) ) {
				newest_link = $( '#buddypress #activity-stream .load-newest a' ).html();
				$( '#buddypress #activity-stream .load-newest a' ).html( newest_link.replace( /([0-9]+)/, newest_activities_count ) );
				return;
			}

			$( '#buddypress #activity-stream' ).prepend( '<li class="load-newest"><a href="#newest">' + BP_Next.newest + ' (' + newest_activities_count + ')</a></li>' );
		},
	}

	// Launch BP Next
	bp.Next.start();

	/** DOM events available for all users ****************************************/

	/**
	 * Object's primary nav listener
	 *
	 * Get the scope, the filter and eventually the search terms and refresh the list of objects
	 */
	$( bp.Next.objectNavParent + ' .item-list-tabs' ).on( 'click', 'a', function( event ) {
		var target = $( event.currentTarget ).parent(),
			scope = 'all', object, filter = null, search_terms = '';

		if ( target.hasClass( 'no-ajax' ) || $( event.currentTarget ).hasClass( 'no-ajax' ) ) {
			return event;
		}

		scope  = target.data( 'scope' );
		object = target.data( 'object' );

		if ( ! scope || ! object ) {
			return event;
		}

		// Stop event propagation
		event.preventDefault();

		filter = $( '#buddypress' ).find( '[data-filter="' + object + '"]' ).first().val();

		if ( $( '#buddypress .dir-search input[type=search]' ).length ) {
			search_terms = $( '#buddypress .dir-search input[type=search]' ).val();
		}

		// Remove the New count on the mentions tab
		if ( 'activity' === object && 'mentions' === scope ) {
			target.find( 'a strong' ).remove();
		}

		bp.Next.objectRequest( object, scope, filter, '#buddypress .bp-' + object + '-list', search_terms, 1 );
	} );

	/**
	 * Object's filter listener
	 *
	 * Get the scope, the filter and eventually the search terms and refresh the list of objects
	 */
	$( '#buddypress .filter select' ).on( 'change', function( event ) {
		var object = $( event.target ).data( 'filter' ),
			scope = 'all', filter = $( event.target ).val(),
			search_terms = '', template = null;

		if ( ! object ) {
			return event;
		}

		if ( $( bp.Next.objectNavParent + ' .item-list-tabs .selected' ).length ) {
			scope = $( bp.Next.objectNavParent + ' .item-list-tabs .selected' ).data( 'scope' );
		}

		if ( $( '#buddypress .dir-search input[type=search]' ).length ) {
			search_terms = $( '#buddypress .dir-search input[type=search]' ).val();
		}

		// The Group Members page has a different selector for its
		// search terms box
		if ( $( '#buddypress .groups-members-search input[type=search]' ).length ) {
			search_terms = $( '#buddypress .groups-members-search input[type=search]' ).val();
			object = 'members';
			scope = 'groups';
		}

		// On the Groups Members page, we specify a template
		if ( 'members' === object && 'groups' === scope ) {
			object = 'group_members';
			template = 'groups/single/members';
		}

		if ( 'friends' === object ) {
			object = 'members';
		}

		bp.Next.objectRequest( object, scope, filter, '#buddypress .bp-' + object + '-list', search_terms, 1, null, null, template );
	} );

	/**
	 * Object's search form listener
	 *
	 * Get the scope, the filter and the search terms and refresh the list of objects
	 */
	$( '#buddypress .dir-search, #buddypress .groups-members-search' ).on( 'submit', 'form', function( event ) {
		var object, scope = 'all', filter = null, template = null, search_terms = '';

		if ( $( event.delegateTarget ).hasClass( 'no-ajax' ) || undefined === $( event.delegateTarget ).data( 'search' ) ) {
			return event;
		}

		// Stop event propagation
		event.preventDefault();

		object       = $( event.delegateTarget ).data( 'search' );
		filter       = $( '#buddypress' ).find( '[data-filter="' + object + '"]' ).first().val();
		search_terms = $( event.delegateTarget ).find( 'input[type=search]' ).first().val();

		if ( $( event.delegateTarget ).hasClass( 'groups-members-search' ) ) {
			object = 'group_members';
			template = 'groups/single/members';
		}

		if ( $( bp.Next.objectNavParent + ' .item-list-tabs .selected' ).length ) {
			scope = $( bp.Next.objectNavParent + ' .item-list-tabs .selected' ).data( 'scope' );
		}

		bp.Next.objectRequest( object, scope, filter, '#buddypress .bp-' + object + '-list', search_terms, 1, null, null, template );
	} );

	/**
	 * Only keep 5 root comments after each activity request and highlight new mentions
	 * once the corresponding tab has been clicked
	 */
	$( '#buddypress .bp-activity-list' ).on( 'bp_ajax_request', function( event, data ) {
		bp.Next.truncateComments( event, data );

		// In case of mentions, we'll highlight temporarly these
		if ( 'mentions' === data.scope && undefined !== data.response.new_mentions ) {
			$.each( data.response.new_mentions, function( i, id ) {
				$( '#buddypress #activity-stream' ).find( '[data-id="' + id + '"]' ).addClass( 'new_mention' );
			} );

			setTimeout( function () {
				$( '#buddypress #activity-stream .activity-item' ).removeClass( 'new_mention' );
			}, 3000 );
		}
	} );

	/**
	 * Show all activity comments if requested
	 */
	$( '#buddypress' ).on( 'click', '.show-all', function( event ) {
		// Stop event propagation
		event.preventDefault();

		$( event.currentTarget ).addClass( 'loading' );

		setTimeout( function() {
			$( event.currentTarget ).closest( 'ul' ).find( 'li' ).fadeIn( 200, function() {
				$( event.currentTarget ).remove();
			} );
		}, 600 );
	} );

	/**
	 * Load newest or more activities
	 */
	 $( '#buddypress #activity-stream' ).on( 'click', 'li.load-newest, li.load-more', function( event ) {
	 	// Load newest activities
	 	if ( $( event.currentTarget ).hasClass( 'load-newest' ) ) {
	 		// Stop event propagation
			event.preventDefault();

			$( event.currentTarget ).remove();

			/**
			 * If a plugin is updating the recorded_date of an activity
			 * it will be loaded as a new one. We need to look in the
			 * stream and eventually remove similar ids to avoid "double".
			 */
			var activities = $.parseHTML( bp.Next.newest_activities );

			$.each( activities, function( a, activity ){
				if( 'LI' === activity.nodeName && $( activity ).hasClass( 'just-posted' ) ) {
					if( $( '#' + $( activity ).prop( 'id' ) ).length ) {
						$( '#' + $( activity ).prop( 'id' ) ).remove();
					}
				}
			} );

			// Now the stream is cleaned, prepend newest
			$( event.delegateTarget ).prepend( bp.Next.newest_activities ).trigger( 'bp_heartbeat_prepend' );

			// reset the newest activities now they're displayed
			bp.Next.newest_activities = '';

			// Reset the document title
			$( document ).prop( 'title', bp.Next.document_title );

		// Load more activities
	 	} else if ( $( event.currentTarget ).hasClass( 'load-more' ) ) {
	 		var next_page = ( Number( bp.Next.getStorage( 'bp-activity', 'page' ) ) * 1 ) + 1;

	 		// Stop event propagation
			event.preventDefault();

			$( event.currentTarget ).addClass( 'loading' );

			// reset the just posted
			bp.Next.just_posted = [];

			// Now set it
			$( event.delegateTarget ).children( '.just-posted' ).each( function() {
				bp.Next.just_posted.push( $( this ).data( 'id' ) );
			} );

			bp.Next.objectRequest( 'activity', null, null, '#buddypress .bp-activity-list', bp_get_querystring( 's' ), next_page, null );
	 	}
	 } );

	/**
	 * Show the search button, even if hitting enter will submit the form, if the user enters the text field
	 */
	$( '#buddypress .dir-search, #buddypress .groups-members-search' ).on( 'focus', 'input[type=search]', function( event ) {
		$( event.delegateTarget ).find( 'input[type=submit]' ).show();
	} );

	/**
	 * Hide the search button when the text field is empty
	 */
	$( '#buddypress .dir-search, #buddypress .groups-members-search' ).on( 'blur', 'input[type=search]', function( event ) {
		if ( ! $( event.target ).val() ) {
			$( event.delegateTarget ).find( 'input[type=submit]' ).hide();
		}
	} );

	/**
	 * Reset results on en empty search
	 */
	$( '#buddypress .dir-search form, #buddypress .groups-members-search form' ).on( 'search', 'input[type=search]', function( event ) {
		if ( ! $( event.target ).val() ) {
			$( event.delegateTarget ).submit();
		} else {
			$( event.delegateTarget ).find( 'input[type=submit]' ).show();
		}
	} );

} )( bp, jQuery );
