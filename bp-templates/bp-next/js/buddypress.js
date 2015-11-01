/* global wp, bp, BP_Next, ajaxurl */
window.wp = window.wp || {};
window.bp = window.bp || {};

( function( exports, $ ) {

	// Bail if not set
	if ( typeof BP_Next === 'undefined' ) {
		return;
	}

	/**
	 * [Next description]
	 * @type {Object}
	 */
	bp.Next = {
		/**
		 * [start description]
		 * @return {[type]} [description]
		 */
		start: function() {
			// Setup globals
			this.setupGlobals();

			// Adjust Document/Forms properties
			this.prepareDocument();

			// Init the BuddyPress objects
			this.initObjects();

			// Set BuddyPress HeartBeat
			this.setHeartBeat();

			// Listen to events ("Add hooks!")
			this.addListeners();
		},

		/**
		 * [setupGlobals description]
		 * @return {[type]} [description]
		 */
		setupGlobals: function() {
			this.ajax_request           = null;

			// Object Globals
			this.objects                = BP_Next.objects;
			this.objectNavParent        = BP_Next.object_nav_parent;
			this.time_since             = BP_Next.time_since;

			// HeartBeat Global
			this.heartbeat              = wp.heartbeat || {};

			// An object containing each query var
			this.querystring            = this.getLinkParams();
		},

		/**
		 * [prepareDocument description]
		 * @return {[type]} [description]
		 */
		prepareDocument: function() {
			var search_title;

			// Remove the no-js class and add the js one
			if ( $( 'body' ).hasClass( 'no-js' ) ) {
				$('body').removeClass( 'no-js' ).addClass( 'js' );
			}

			// Remove the directory title if there's a widget containing it
			if ( $( '.buddypress_object_nav .widget-title' ).length ) {
				var text = $( '.buddypress_object_nav .widget-title' ).html();

				$( 'body' ).find( '*:contains("' + text + '")' ).each( function( e, element ) {
					if ( ! $( element ).hasClass( 'widget-title' ) && text === $( element ).html() ) {
						$( element ).remove();
					}
				} );
			}

			if ( $( '#buddypress li.dir-search input[type=text]' ).length ) {
				// Transform text field into search field
				$( '#buddypress li.dir-search input[type=text]' ).prop( 'type', 'search' );

				// Add a title attribute and use an icon for the search submit button
				search_title = $( '#buddypress li.dir-search input[type=submit]' ).prop( 'value' );
				$( '#buddypress li.dir-search input[type=submit]' ).prop( 'title', search_title );
				$( '#buddypress li.dir-search input[type=submit]' ).prop( 'value', BP_Next.search_icon );
			}
		},

		/** Helpers *******************************************************************/

		/**
		 * [getStorage description]
		 * @param  {[type]} type     [description]
		 * @param  {[type]} property [description]
		 * @return {[type]}          [description]
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
		 * [setStorage description]
		 * @param {[type]} type     [description]
		 * @param {[type]} property [description]
		 * @param {[type]} value    [description]
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

		/**
		 * [getLinkParams description]
		 * @param  {[type]} url   [description]
		 * @param  {[type]} param [description]
		 * @return {[type]}       [description]
		 */
		getLinkParams: function( url, param ) {
			if ( url ) {
				qs = ( -1 !== url.indexOf( '?' ) ) ? '?' + url.split( '?' )[1] : '';
			} else {
				qs = document.location.search;
			}

			if ( ! qs ) {
				return null;
			}

			var params = qs.replace( /(^\?)/, '' ).split( '&' ).map( function( n ) {
				return n = n.split( '=' ),this[n[0]] = n[1],this
			}.bind( {} ) )[0];

			if ( param ) {
				return params[param];
			}

			return params;
		},

		/**
		 * [ajax description]
		 * @param  {[type]} post_data [description]
		 * @param  {[type]} object    [description]
		 * @return {[type]}           [description]
		 */
		ajax: function( post_data, object ) {
			if ( this.ajax_request ) {
				this.ajax_request.abort();
			}

			// Extend posted data with stored data and object nonce
			$.extend( post_data, bp.Next.getStorage( 'bp-' + object ), { nonce: BP_Next.nonces[object] } );

			this.ajax_request = $.post( ajaxurl, post_data, 'json' );

			return this.ajax_request;
		},

		inject: function( selector, content, method ) {
			if ( ! $( selector ).length || ! content ) {
				return;
			}

			/**
			 * How the content should be injected in the selector
			 *
			 * possible methodes are
			 * - reset: the selector will be reset with the content
			 * - append:  the content will be added after selector's content
			 * - prepend: the content will be added before selector's content
			 */
			method = method || 'reset';

			if ( 'append' === method ) {
				$( selector ).append( content );
			} else if ( 'prepend' === method ) {
				$( selector ).prepend( content );
			} else {
				$( selector ).html( content );
			}
		},

		/**
		 * [updateTimeSince description]
		 * @param  {[type]} timestamp [description]
		 * @return {[type]}           [description]
		 */
		updateTimeSince: function( timestamp ) {
			var now = new Date( $.now() ), diff, count_1, chunk_1, count_2, chunk_2,
				time_since = [], time_chunks = $.extend( {}, this.time_since.time_chunks ), ms;

			// Returns sometime
			if ( undefined === timestamp ) {
				return this.time_since.sometime;
			}

			// Javascript timestamps are in ms.
			timestamp = new Date( timestamp * 1000 );

			// Calculate the diff
			diff = now - timestamp;

			// Returns right now
			if ( 0 === diff ) {
				return this.time_since.now;
			}

			$.each( time_chunks, function( c, chunk ) {
				var milliseconds = chunk * 1000;
				var rounded_time = Math.floor( diff / milliseconds );

				if ( 0 !== rounded_time && ! chunk_1 ) {
					chunk_1 = c;
					count_1 = rounded_time;
					ms      = milliseconds;
				}
			} );

			// First chunk
			chunk_1 = chunk_1.substr( 2 );
			time_since.push( ( 1 === count_1 ) ? this.time_since[ chunk_1 ].replace( '%', count_1 ) : this.time_since[ chunk_1 + 's' ].replace( '%', count_1 ) );

			// Remove Year from chunks
			delete time_chunks.a_year;

			$.each( time_chunks, function( c, chunk ) {
				var milliseconds = chunk * 1000;
				var rounded_time = Math.floor( ( diff - ( ms * count_1 ) ) / milliseconds );

				if ( 0 !== rounded_time && ! chunk_2 ) {
					chunk_2 = c;
					count_2 = rounded_time;
				}
			} );

			// Second chunk
			if ( undefined !== chunk_2 ) {
				chunk_2 = chunk_2.substr( 2 );
				time_since.push( ( 1 === count_2 ) ? this.time_since[ chunk_2 ].replace( '%', count_2 ) : this.time_since[ chunk_2 + 's' ].replace( '%', count_2 ) );
			}

			// Returns x time, y time ago
			if ( time_since.length >= 1 ) {
				return this.time_since.ago.replace( '%', time_since.join( this.time_since.separator + ' ' ) );

			// Returns sometime
			} else {
				return this.time_since.sometime;
			}
		},

		/**
		 * [objectRequest description]
		 * @param  {[type]} data [description]
		 * @return {[type]}      [description]
		 */
		objectRequest: function( data ) {
			var postdata = {}, self = this;

			data = $.extend( {
				object       : '',
				scope        : null,
				filter       : null,
				target       : '#buddypress [data-bp-list]',
				search_terms : '',
				page         : 1,
				extras       : null,
				caller       : null,
				template     : null,
				method       : 'reset',
			}, data );

			// Do not request if we don't have the object or the target to inject results into
			if ( ! data.object || ! data.target ) {
				return;
			}

			// Set session's data
			if ( null !== data.scope ) {
				this.setStorage( 'bp-' + data.object, 'scope', data.scope );
			}

			if ( null !== data.filter ) {
				this.setStorage( 'bp-' + data.object, 'filter', data.filter );
			}

			if ( null !== data.extras ) {
				this.setStorage( 'bp-' + data.object, 'extras', data.extras );
			}

			/* Set the correct selected nav and filter */
			$( this.objectNavParent + ' [data-object="' + data.object + '"]' ).each( function() {
				$( this ).removeClass( 'selected loading' );
			} );

			$( this.objectNavParent + ' [data-scope="' + data.scope + '"], #object-nav li.current').addClass( 'selected loading' );
			$( '#buddypress [data-filter="' + data.object + '"] option[value="' + data.filter + '"]' ).prop( 'selected', true );

			if ( 'friends' === data.object || 'group_members' === data.object ) {
				data.object = 'members';
			}

			postdata = $.extend( {
				action: data.object + '_filter',
			}, data );

			return this.ajax( postdata, data.object ).done( function( response ) {
				if ( false === response.success ) {
					return;
				}

				$( self.objectNavParent + ' [data-scope="' + data.scope + '"]' ).removeClass( 'loading' );

				if ( 'reset' !== data.method ) {
					self.inject( data.target, response.data.contents, data.method );

					$( data.target ).trigger( 'bp_ajax_' + data.method, $.extend( data, { response: response.data } ) );
				} else {
					/* animate to top if called from bottom pagination */
					if ( data.caller === 'pag-bottom' && $( '#subnav' ).length ) {
						var top = $('#subnav').parent();
						$( 'html,body' ).animate( { scrollTop: top.offset().top }, 'slow', function() {
							$( data.target ).fadeOut( 100, function() {
								self.inject( this, response.data.contents, data.method );
								$( this ).fadeIn( 100 );

								// Inform other scripts the list of objects has been refreshed.
								$( data.target ).trigger( 'bp_ajax_request', $.extend( data, { response: response.data } ) );
							} );
						} );

					} else {
						$( data.target ).fadeOut( 100, function() {
							self.inject( this, response.data.contents, data.method );
							$( this ).fadeIn( 100 );

							// Inform other scripts the list of objects has been refreshed.
							$( data.target ).trigger( 'bp_ajax_request', $.extend( data, { response: response.data } ) );
						} );
					}
				}
			} );
		},

		/**
		 * [initObjects description]
		 * @return {[type]} [description]
		 */
		initObjects: function() {
			var self = this, objectData = {}, scope = 'all', search_terms = '';

			$.each( this.objects, function( o, object ) {
				objectData = self.getStorage( 'bp-' + object );

				if ( undefined !== objectData.scope ) {
					scope = objectData.scope;
				}

				if ( undefined !== objectData.filter && $( '#buddypress [data-filter="' + object + '"]' ).length ) {
					$( '#buddypress [data-filter="' + object + '"] option[value="' + objectData.filter + '"]' ).prop( 'selected', true );
				}

				if ( $( this.objectNavParent + ' [data-object="' + object + '"]' ).length ) {
					$( this.objectNavParent + ' [data-object="' + object + '"]' ).each( function() {
						$( this ).removeClass( 'selected' );
					} );

					$( this.objectNavParent + ' [data-scope="' + object + '"], #object-nav li.current' ).addClass( 'selected' );
				}

				// Check the querystring to eventually include the search terms
				if ( null !== self.querystring ) {
					if ( undefined !== self.querystring[ object + '_search'] ) {
						search_terms = self.querystring[ object + '_search'];
					} else if ( undefined !== self.querystring.s ) {
						search_terms = self.querystring.s;
					}

					if ( search_terms ) {
						var selector = '.dir-search';

						if ( 'group_members' === object ) {
							selector = '.groups-members-search';
						}

						$( '#buddypress ' + selector + ' input[type=search]' ).val( search_terms );
					}
				}

				if ( $( '#buddypress  [data-bp-list="' + object + '"]' ).length ) {
					// Populate the object list
					self.objectRequest( {
						object       : object,
						scope        : scope,
						filter       : objectData.filter,
						search_terms : search_terms,
					} );
				}
			} );
		},

		/**
		 * [setHeartBeat description]
		 */
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

		/** Event Listeners ***********************************************************/

		/**
		 * [addListeners description]
		 */
		addListeners: function() {
			// HeartBeat Send and Recieve
			$( document ).on( 'heartbeat-send.buddypress', this.heartbeatSend );
			$( document ).on( 'heartbeat-tick.buddypress', this.heartbeatTick );

			// Refreshing
			$( this.objectNavParent + ' .item-list-tabs' ).on( 'click', 'a', this, this.scopeQuery );

			// Filtering
			$( '#buddypress .filter select' ).on( 'change', this, this.filterQuery );

			// Searching
			$( '#buddypress .dir-search, #buddypress .groups-members-search' ).on( 'submit', 'form', this, this.searchQuery );
			$( '#buddypress .dir-search, #buddypress .groups-members-search' ).on( 'focus', 'input[type=search]', this.showSearchSubmit );
			$( '#buddypress .dir-search, #buddypress .groups-members-search' ).on( 'blur', 'input[type=search]', this.hideSearchSubmit );
			$( '#buddypress .dir-search form, #buddypress .groups-members-search form' ).on( 'search', 'input[type=search]', this.resetSearch );
		},

		/** Event Callbacks ***********************************************************/

		/**
		 * [heartbeatSend description]
		 * @param  {[type]} event [description]
		 * @param  {[type]} data  [description]
		 * @return {[type]}       [description]
		 */
		heartbeatSend: function( event, data ) {
			// Add an heartbeat send event to possibly any BuddyPress pages
			$( '#buddypress' ).trigger( 'bp_heartbeat_send', { event, data } );
		},

		/**
		 * [heartbeatTick description]
		 * @param  {[type]} event [description]
		 * @param  {[type]} data  [description]
		 * @return {[type]}       [description]
		 */
		heartbeatTick: function( event, data ) {
			// Add an heartbeat send event to possibly any BuddyPress pages
			$( '#buddypress' ).trigger( 'bp_heartbeat_tick', { event, data } );
		},

		/**
		 * [queryScope description]
		 * @param  {[type]} event [description]
		 * @return {[type]}       [description]
		 */
		scopeQuery: function( event ) {
			var self = event.data, target = $( event.currentTarget ).parent(),
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

			// Remove the New count on dynamic tabs
			if ( 'activity' === object && target.hasClass( 'dynamic' ) ) {
				target.find( 'a span' ).html('');
			}

			self.objectRequest( {
				object       : object,
				scope        : scope,
				filter       : filter,
				search_terms : search_terms,
				page         : 1
			} );
		},

		/**
		 * [filterQuery description]
		 * @param  {[type]} event [description]
		 * @return {[type]}       [description]
		 */
		filterQuery: function( event ) {
			var self = event.data, object = $( event.target ).data( 'filter' ),
				scope = 'all', filter = $( event.target ).val(),
				search_terms = '', template = null;

			if ( ! object ) {
				return event;
			}

			if ( $( self.objectNavParent + ' .item-list-tabs .selected' ).length ) {
				scope = $( self.objectNavParent + ' .item-list-tabs .selected' ).data( 'scope' );
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

			self.objectRequest( {
				object       : object,
				scope        : scope,
				filter       : filter,
				search_terms : search_terms,
				page         : 1,
				template     : template
			} );
		},

		/**
		 * [searchQuery description]
		 * @param  {[type]} event [description]
		 * @return {[type]}       [description]
		 */
		searchQuery: function( event ) {
			var self = event.data, object, scope = 'all', filter = null, template = null, search_terms = '';

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

			if ( $( self.objectNavParent + ' .item-list-tabs .selected' ).length ) {
				scope = $( self.objectNavParent + ' .item-list-tabs .selected' ).data( 'scope' );
			}

			self.objectRequest( {
				object       : object,
				scope        : scope,
				filter       : filter,
				search_terms : search_terms,
				page         : 1,
				template     : template
			} );
		},

		/**
		 * [showSearchSubmit description]
		 * @param  {[type]} event [description]
		 * @return {[type]}       [description]
		 */
		showSearchSubmit: function( event ) {
			$( event.delegateTarget ).find( 'input[type=submit]' ).show();
		},

		/**
		 * [hideSearchSubmit description]
		 * @param  {[type]} event [description]
		 * @return {[type]}       [description]
		 */
		hideSearchSubmit: function( event ) {
			if ( ! $( event.target ).val() ) {
				$( event.delegateTarget ).find( 'input[type=submit]' ).hide();
			}
		},

		/**
		 * [resetSearch description]
		 * @param  {[type]} event [description]
		 * @return {[type]}       [description]
		 */
		resetSearch: function( event ) {
			if ( ! $( event.target ).val() ) {
				$( event.delegateTarget ).submit();
			} else {
				$( event.delegateTarget ).find( 'input[type=submit]' ).show();
			}
		},
	}

	// Launch BP Next
	bp.Next.start();

} )( bp, jQuery );
