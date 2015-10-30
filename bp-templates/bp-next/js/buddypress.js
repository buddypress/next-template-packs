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

			// Specific to Activities
			this.just_posted            = [];
			// There should be the oldestpage in here, the sessionStorage
			// must not be needed imho

			// HeartBeat Globals
			this.heartbeat              = wp.heartbeat || {};
			this.newest_activities      = '';
			this.highlights             = {};
			this.activity_last_recorded = 0;
			this.first_item_recorded    = 0;
			this.document_title         = $( document ).prop( 'title' );

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
		 * [getActivityTimestamp description]
		 * @param  {[type]} selector [description]
		 * @return {[type]}          [description]
		 */
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

		/**
		 * [ajax description]
		 * @param  {[type]} post_data [description]
		 * @param  {[type]} object    [description]
		 * @return {[type]}           [description]
		 */
		ajax: function( post_data, object ) {
			$.extend( post_data, bp.Next.getStorage( 'bp-' + object ), { nonce: BP_Next.nonces[object] } );

			return $.post( ajaxurl, post_data, 'json' );
		},

		/**
		 * [objectRequest description]
		 * @param  {[type]} object       [description]
		 * @param  {[type]} scope        [description]
		 * @param  {[type]} filter       [description]
		 * @param  {[type]} target       [description]
		 * @param  {[type]} search_terms [description]
		 * @param  {[type]} page         [description]
		 * @param  {[type]} extras       [description]
		 * @param  {[type]} caller       [description]
		 * @param  {[type]} template     [description]
		 * @return {[type]}              [description]
		 */
		objectRequest: function( object, scope, filter, target, search_terms, page, extras, caller, template ) {
			var postdata, clone = {}, self = this;

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

				$( self.objectNavParent + ' [data-scope="' + scope + '"]' ).removeClass( 'loading' );

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

							// Make sure the All members dynamic span is reset
							$( self.objectNavParent + ' [data-scope="all"]' ).find( 'a span' ).html( '' );

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

			}, 'json' );
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

				if ( $( this.objectNavParent + ' .bp-' + object + '-primary-nav' ).length ) {
					$( this.objectNavParent + ' .bp-' + object + '-primary-nav' ).each( function() {
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

				if ( $( '#buddypress .bp-' + object + '-list').length ) {
					// Populate the object list
					self.objectRequest( object, scope, objectData.filter, '#buddypress .bp-' + object + '-list', search_terms, 1 );
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
			$( document ).on( 'heartbeat-send.buddypress', this, this.heartbeatSend );
			$( document ).on( 'heartbeat-tick.buddypress', this, this.heartbeatTick );

			// Refreshing
			$( this.objectNavParent + ' .item-list-tabs' ).on( 'click', 'a', this, this.scopeQuery );

			// Filtering
			$( '#buddypress .filter select' ).on( 'change', this, this.filterQuery );

			// Searching
			$( '#buddypress .dir-search, #buddypress .groups-members-search' ).on( 'submit', 'form', this, this.searchQuery );
			$( '#buddypress .dir-search, #buddypress .groups-members-search' ).on( 'focus', 'input[type=search]', this.showSearchSubmit );
			$( '#buddypress .dir-search, #buddypress .groups-members-search' ).on( 'blur', 'input[type=search]', this.hideSearchSubmit );
			$( '#buddypress .dir-search form, #buddypress .groups-members-search form' ).on( 'search', 'input[type=search]', this.resetSearch );

			// Injecting
			$( '#buddypress #activity-stream' ).on( 'click', 'li.load-newest, li.load-more', this, this.injectQuery );

			// Activity comments effect
			$( '#buddypress .bp-activity-list' ).on( 'bp_ajax_request', this.hideComments );
			$( '#buddypress .bp-activity-list' ).on( 'bp_ajax_append', this.hideComments );
			$( '#buddypress' ).on( 'click', '.show-all', this.showComments );
		},

		/** Event Callbacks ***********************************************************/

		/**
		 * [heartbeatSend description]
		 * @param  {[type]} event [description]
		 * @param  {[type]} data  [description]
		 * @return {[type]}       [description]
		 */
		heartbeatSend: function( event, data ) {
			var self = event.data;

			self.first_item_recorded = self.getActivityTimestamp( $( '#buddypress #activity-stream .activity-item' ).first() ) || 0;

			if ( 0 === self.activity_last_recorded || self.first_item_recorded > self.activity_last_recorded ) {
				self.activity_last_recorded = self.first_item_recorded;
			}

			data.bp_activity_last_recorded = self.activity_last_recorded;

			if ( $( '#buddypress .dir-search input[type=search]' ).length ) {
				data.bp_activity_last_recorded_search_terms = $( '#buddypress .dir-search input[type=search]' ).val();
			}

			$.extend( data, { bp_heartbeat: self.getStorage( 'bp-activity' ) } );
		},

		/**
		 * [heartbeatTick description]
		 * @param  {[type]} event [description]
		 * @param  {[type]} data  [description]
		 * @return {[type]}       [description]
		 */
		heartbeatTick: function( event, data ) {
			var self = event.data, newest_activities_count, newest_activities, objects = self.objects,
				scope = self.getStorage( 'bp-activity', 'scope' );

			// Only proceed if we have newest activities
			if ( ! data.bp_activity_newest_activities ) {
				return;
			}

			self.newest_activities = $.trim( data.bp_activity_newest_activities.activities ) + self.newest_activities;
			self.activity_last_recorded  = Number( data.bp_activity_newest_activities.last_recorded );

			// Parse activities
			newest_activities = $( self.newest_activities ).filter( '.activity-item' );

			// Count them
			newest_activities_count = Number( newest_activities.length );

			/**
			 * On the All Members tab, we need to know these activities are about
			 * in order to update all the other tabs dynamic span
			 */
			if ( 'all' === scope ) {
				/**
				 * It's not a regular object but we need it!
				 * so let's add it temporarly..
				 */
				objects.push( 'mentions' );

				$.each( newest_activities, function( a, activity ) {
					var activity = $( activity );

					$.each( objects, function( o, object ) {
						if ( -1 !== $.inArray( 'bp-my-' + object, activity.get( 0 ).classList ) ) {
							if ( undefined === self.highlights[ object ] ) {
								self.highlights[ object ] = [ activity.data( 'id' ) ];
							} else if ( -1 === $.inArray( activity.data( 'id' ), self.highlights[ object ] ) ) {
								self.highlights[ object ].push( activity.data( 'id' ) );
							}
						}
					} );
				} );

				// Remove the specific classes to count highligthts
				var regexp = new RegExp( 'bp-my-(' + objects.join( '|' ) + ')', 'g' );
				self.newest_activities = self.newest_activities.replace( regexp, '' );

				/**
				 * Let's remove the mentions from objects!
				 */
				 objects.pop();

				/**
				 * Deal with the 'All Members' dynamic span from here as HeartBeat is working even when
				 * the user is not logged in
				 */
				 $( self.objectNavParent + ' [data-scope="all"]' ).find( 'a span' ).html( newest_activities_count );

			// Set all activities to be highlighted for the current scope
			} else {
				// Init the array of highlighted activities
				self.highlights[ scope ] = [];

				$.each( newest_activities, function( a, activity ) {
					self.highlights[ scope ].push( $( activity ).data ( 'id' ) );
				} );
			}

			// Add an information about the number of newest activities inside the document's title
			$( document ).prop( 'title', '(' + newest_activities_count + ') ' + self.document_title );

			// Update the Load Newest li if it already exists.
			if ( $( '#buddypress #activity-stream li' ).first().hasClass( 'load-newest' ) ) {
				newest_link = $( '#buddypress #activity-stream .load-newest a' ).html();
				$( '#buddypress #activity-stream .load-newest a' ).html( newest_link.replace( /([0-9]+)/, newest_activities_count ) );

			// Otherwise add it
			} else {
				$( '#buddypress #activity-stream' ).prepend( '<li class="load-newest"><a href="#newest">' + BP_Next.newest + ' (' + newest_activities_count + ')</a></li>' );
			}

			/**
			 * Finally trigger a pending event containing the number of newest activities
			 * by scope.
			 */
			$( '#buddypress #activity-stream' ).trigger( 'bp_heartbeat_pending' );
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

			self.objectRequest( object, scope, filter, '#buddypress .bp-' + object + '-list', search_terms, 1 );
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

			self.objectRequest( object, scope, filter, '#buddypress .bp-' + object + '-list', search_terms, 1, null, null, template );
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

			self.objectRequest( object, scope, filter, '#buddypress .bp-' + object + '-list', search_terms, 1, null, null, template );
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

		/**
		 * [injectQuery description]
		 * @param  {[type]} event [description]
		 * @return {[type]}       [description]
		 */
		injectQuery: function( event ) {
			var self = event.data, store = self.getStorage( 'bp-activity' ),
				scope = store.scope || null, filter = store.filter || null;

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
				var activities = $.parseHTML( self.newest_activities );

				$.each( activities, function( a, activity ){
					if( 'LI' === activity.nodeName && $( activity ).hasClass( 'just-posted' ) ) {
						if( $( '#' + $( activity ).prop( 'id' ) ).length ) {
							$( '#' + $( activity ).prop( 'id' ) ).remove();
						}
					}
				} );

				// Now the stream is cleaned, prepend newest
				$( event.delegateTarget ).prepend( self.newest_activities ).trigger( 'bp_heartbeat_prepend' );

				// Reset the newest activities now they're displayed
				self.newest_activities = '';

				// Reset the All members tab dynamic span id it's the current one
				if ( 'all' === scope ) {
					$( self.objectNavParent + ' [data-scope="all"]' ).find( 'a span' ).html( '' );
				}

				// Reset the document title
				$( document ).prop( 'title', self.document_title );

			// Load more activities
			} else if ( $( event.currentTarget ).hasClass( 'load-more' ) ) {
				var next_page = ( Number( store.page || 1 ) * 1 ) + 1, search_terms = '';

				// Stop event propagation
				event.preventDefault();

				//$( event.currentTarget ).addClass( 'loading' );

				// reset the just posted
				self.just_posted = [];

				// Now set it
				$( event.delegateTarget ).children( '.just-posted' ).each( function() {
					self.just_posted.push( $( this ).data( 'id' ) );
				} );

				if ( $( '#buddypress .dir-search input[type=search]' ).length ) {
					search_terms = $( '#buddypress .dir-search input[type=search]' ).val();
				}

				self.objectRequest( 'activity', scope, filter, '#buddypress .bp-activity-list', search_terms, next_page, null );
			}
		},

		/**
		 * [truncateComments description]
		 * @param  {[type]} event [description]
		 * @param  {[type]} data  [description]
		 * @return {[type]}       [description]
		 */
		hideComments: function( event, data ) {
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
		 * [showComments description]
		 * @param  {[type]} event [description]
		 * @return {[type]}       [description]
		 */
		showComments: function( event ) {
			// Stop event propagation
			event.preventDefault();

			$( event.currentTarget ).addClass( 'loading' );

			setTimeout( function() {
				$( event.currentTarget ).closest( 'ul' ).find( 'li' ).fadeIn( 200, function() {
					$( event.currentTarget ).remove();
				} );
			}, 600 );
		}
	}

	// Launch BP Next
	bp.Next.start();

} )( bp, jQuery );
