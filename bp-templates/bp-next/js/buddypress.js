/* global bp, BP_Next, ajaxurl */
window.bp = window.bp || {};

( function( exports, $ ) {

	// Bail if not set
	if ( typeof BP_Next === 'undefined' ) {
		return;
	}

	bp.Next = {
		start: function() {
			this.ajax_request           = null;
			this.newest_activities      = '';
			this.activity_last_recorded = 0;
			this.objects                = BP_Next.objects;
			this.objectNavParent        = BP_Next.object_nav_parent;

			if( $( 'body' ).hasClass( 'no-js' ) ) {
				$('body').removeClass( 'no-js' ).addClass( 'js' );
			}

			this.initObjects();
		},

		/**
		 * Get the object state
		 */
		getStorage: function( type ) {
			var store = sessionStorage.getItem( type );

			if ( store ) {
				store = JSON.parse( store );
			} else {
				store = {};
			}

			return store;
		},

		/**
		 * Set the object state
		 */
		setStorage: function( type, property, value ) {
			var store = this.getStorage( type );

			if ( 'undefined' === value && 'undefined' !== store[ property ] ) {
				delete store[ property ];
			} else {
				// Set property
				store[ property ] = value;
			}

			sessionStorage.setItem( type, JSON.stringify( store ) );

			return sessionStorage.getItem( type ) !== null;
		},

		truncateComments: function( event ) {
			var comments = $( event.target ).find( '.activity-comments' ),
				activity_item, comment_items, comment_count;

			if ( ! comments.length ) {
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
				// Reset the page
				this.setStorage( 'bp-activity', 'page', 1 );
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
		}
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

		if ( $( '#buddypress .dir-search input' ).length ) {
			search_terms = $( '#buddypress .dir-search input' ).val();
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

		if ( $( '#buddypress .dir-search input' ).length ) {
			search_terms = $( '#buddypress .dir-search input' ).val();
		}

		// The Group Members page has a different selector for its
		// search terms box
		if ( $( '#buddypress .groups-members-search input' ).length ) {
			search_terms = $( '#buddypress .groups-members-search input' ).val();
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

		if ( $( event.delegateTarget ).hasClass( 'no-ajax' ) || 'undefined' === $( event.delegateTarget ).data( 'search' ) ) {
			return event;
		}

		// Stop event propagation
		event.preventDefault();

		object       = $( event.delegateTarget ).data( 'search' );
		filter       = $( '#buddypress' ).find( '[data-filter="' + object + '"]' ).first().val();
		search_terms = $( event.delegateTarget ).find( 'input[type=text]' ).first().val();

		if ( $( event.delegateTarget ).hasClass( 'groups-members-search' ) ) {
			object = 'group_members';
			template = 'groups/single/members';
		}

		if ( $( bp.Next.objectNavParent + ' .item-list-tabs .selected' ).length ) {
			scope = $( bp.Next.objectNavParent + ' .item-list-tabs .selected' ).data( 'scope' );
		}

		bp.Next.objectRequest( object, scope, filter, '#buddypress .bp-' + object + '-list', search_terms, 1, null, null, template );

		// Hide the submit button
		$( event.delegateTarget ).find( 'input[type=submit]' ).hide();
	} );
	
	/**
	 * Only keep 5 root comments after each activity request
	 */
	$( '#buddypress .bp-activity-list' ).on( 'bp_ajax_request', bp.Next.truncateComments );

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
	 * Show the search button, even if hitting enter will submit the form, if the user enters the text field
	 */
	$( '#buddypress .dir-search, #buddypress .groups-members-search' ).on( 'focus', 'input[type=text]', function( event ) {
		$( event.delegateTarget ).find( 'input[type=submit]' ).show();
	} );

	/**
	 * Hide the search button when the text field is empty
	 */
	$( '#buddypress .dir-search, #buddypress .groups-members-search' ).on( 'blur', 'input[type=text]', function( event ) {
		if ( ! $( event.target ).val() ) {
			$( event.delegateTarget ).find( 'input[type=submit]' ).hide();
		}
	} );

} )( bp, jQuery );
