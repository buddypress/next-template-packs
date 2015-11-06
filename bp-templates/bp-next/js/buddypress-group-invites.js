/* global wp, bp, BP_Next, ajaxurl */
window.wp = window.wp || {};
window.bp = window.bp || {};

( function( exports, $ ) {

	// Bail if not set
	if ( typeof BP_Next === 'undefined' ) {
		return;
	}

	window.bp = _.extend( window.bp, _.pick( window.wp, 'Backbone', 'ajax', 'template' ) );

	bp.Models      = bp.Models || {};
	bp.Collections = bp.Collections || {};
	bp.Views       = bp.Views || {};

	bp.Next = bp.Next || {};

	/**
	 * [Next description]
	 * @type {Object}
	 */
	bp.Next.GroupInvites = {
		/**
		 * [start description]
		 * @return {[type]} [description]
		 */
		start: function() {
			this.scope    = null;
			this.views    = new Backbone.Collection();
			this.navItems = new Backbone.Collection();
			this.users    = new bp.Collections.Users();
			this.invites  = this.users.clone();

			// Add views
			this.setupNav();
			this.setupLoops();

			// Add an invite when a user is selected
			this.users.on( 'change:selected', this.addInvite, this );

			// And display the Invites nav
			this.invites.on( 'add', this.invitesNav, this );
		},

		setupNav: function() {
			var activeView;

			// Init the nav
			this.nav = new bp.Views.invitesNav( { collection: this.navItems } );

			// loop through available nav items to build it
			_.each( BP_Next.group_invites.nav, function( item, index ) {
				if ( ! _.isObject( item ) ) {
					return;
				}

				// Reset active View
				activeView = 0;

				if ( 0 === index ) {
					this.scope = item.id;
					activeView = 1;
				}

				this.navItems.add( {
					id     : item.id,
					name   : item.caption,
					href   : '#',
					active : activeView,
					hide   : _.isUndefined( item.hide ) ? 0 : item.hide
				} );
			}, this );

			// Inject the nav into the DOM
			this.nav.inject( '.bp-invites-nav' );

			// Listen to the confirm event
			this.nav.on( 'bp-invites:confirm', this.loadConfirmView, this );
			this.nav.on( 'bp-invites:loops', this.setupLoops, this );
		},

		setupLoops: function( scope ) {
			scope = scope || this.scope;

			if ( ! _.isUndefined( this.views.get( 'users' ) ) ) {
				return;
			} else {
				this.clearViews();
			}

			// Activate the loop view
			var users = new bp.Views.inviteUsers( { collection: this.users, scope: scope, nav: this.navItems } );

			this.views.add( { id: 'users', view: users } );

			users.inject( '.bp-invites-content' );
		},

		addInvite: function( user ) {
			this.invites.add( user );
		},

		invitesNav: function() {
			this.navItems.get( 'invites' ).set( { active: 0, hide: 0 } );
		},

		clearViews: function() {
			// Clear views
			if ( ! _.isUndefined( this.views.models ) ) {
				_.each( this.views.models, function( model ) {
					model.get( 'view' ).remove();
				}, this );

				this.views.reset();
			}
		},

		loadConfirmView: function() {
			this.clearViews();
		}
	}

	// Item (group or blog or any other)
	bp.Models.User = Backbone.Model.extend( {
		defaults : {
			id       : 0,
			avatar   : '',
			name     : '',
			selected : 0
        },
	} );

	/** Collections ***********************************************************/

	// Items (groups or blogs or any others)
	bp.Collections.Users = Backbone.Collection.extend( {
		model: bp.Models.User,

		initialize : function() {
			this.options = { current_page: 1, total_page: 0 };
		},

		sync: function( method, model, options ) {

			if ( 'read' === method ) {
				options = options || {};
				options.context = this;
				options.data = _.extend( options.data || {}, {
					action: 'groups_get_potential_invites',
				} );

				return bp.ajax.send( options );
			}
		},

		parse: function( resp ) {
			if ( ! _.isArray( resp.users ) ) {
				resp.users = [resp.users];
			}

            _.each( resp.users, function( value, index ) {
                if ( _.isNull( value ) ) {
                	return;
                }

                resp.users[index].id = value.id;
                resp.users[index].avatar = value.avatar;
                resp.users[index].name = value.name;
            } );

            if( ! _.isUndefined( resp.meta ) ){
                 this.options.current_page = resp.meta.current_page;
                 this.options.total_page = resp.meta.total_page;
            }

            return resp.users;
		}

	} );

	// Extend wp.Backbone.View with .prepare() and .inject()
	bp.Next.GroupInvites.View = bp.Backbone.View.extend( {
		inject: function( selector ) {
			this.render();
			$(selector).html( this.el );
			this.views.ready();
		},

		prepare: function() {
			if ( ! _.isUndefined( this.model ) && _.isFunction( this.model.toJSON ) ) {
				return this.model.toJSON();
			} else {
				return {};
			}
		}
	} );

	bp.Views.invitesNav = bp.Next.GroupInvites.View.extend( {
		tagName: 'ul',

		events: {
			'click .bp-invites-nav-item' : 'toggleView'
		},

		initialize: function() {
			this.collection.on( 'add', this.outputNav, this );
			this.collection.on( 'change:hide', this.showHideNavItem, this );
		},

		outputNav: function( nav ) {
			/**
			 * The delete nav is not added if no avatar
			 * is set for the object
			 */
			if ( 1 === nav.get( 'hide' ) ) {
				return;
			}

			this.views.add( new bp.Views.invitesNavItem( { model: nav } ) );
		},

		showHideNavItem: function( item ) {
			var isRendered = null;

			/**
			 * Loop in views to show/hide the nav item
			 * BuddyPress is only using this for the delete nav
			 */
			_.each( this.views._views[''], function( view ) {
				if ( 1 === view.model.get( 'hide' ) ) {
					view.remove();
				}

				// Check to see if the nav is not already rendered
				if ( item.get( 'id' ) === view.model.get( 'id' ) ) {
					isRendered = true;
				}
			} );

			// Add the Delete nav if not rendered
			if ( ! _.isBoolean( isRendered ) ) {
				item.set( 'invites_count', bp.Next.GroupInvites.invites.length );
				this.outputNav( item );
			}
		},

		toggleView: function( event ) {
			event.preventDefault();

			var current_nav_id = $( event.target ).data( 'nav' );

			_.each( this.collection.models, function( nav ) {
				if ( nav.id === current_nav_id ) {
					nav.set( 'active', 1 );

					// Specific to the invites view
					if ( 'invites' === nav.id ) {
						this.trigger( 'bp-invites:confirm' );
					} else {
						this.trigger( 'bp-invites:loops', nav.id );
					}

				} else if ( 1 !== nav.get( 'hide' ) ) {
					nav.set( 'active', 0 );
				}
			}, this );
		}
	} );

	bp.Views.invitesNavItem = bp.Next.GroupInvites.View.extend( {
		tagName:   'li',
		template:  bp.template( 'bp-invites-nav' ),

		initialize: function() {
			if ( 1 === this.model.get( 'active' ) ) {
				this.el.className += ' current';
			}

			this.model.on( 'change:active', this.toggleClass, this );
			this.on( 'ready', this.addCount, this );
			bp.Next.GroupInvites.invites.on( 'add', this.addCount, this );
		},

		addCount: function( user, invite ) {
			if ( 'invites' !== this.model.get( 'id' ) ) {
				return;
			}

			var span_count = _.isUndefined( invite ) ? this.model.get( 'invites_count' ) : invite.models.length;

			if ( $( this.el ).find( 'span' ).length ) {
				$( this.el ).find( 'span' ).html( span_count );
			} else {
				$( this.el ).find( 'a' ).append( $( '<span></span>' ).html( span_count ) );
			}
		},

		toggleClass: function( model ) {
			if ( 0 === model.get( 'active' ) ) {
				$( this.el ).removeClass( 'current' );
			} else {
				$( this.el ).addClass( 'current' );
			}
		}
	} );

	bp.Views.inviteUsers = bp.Next.GroupInvites.View.extend( {
		tagName: 'ul',
		className: 'item-list',
		id: 'members-list',

		initialize: function() {
			// Load users for the first active view
			this.requestUsers();

			this.collection.on( 'reset', this.cleanContent, this );
			this.collection.on( 'add', this.addUser, this );

			// Update the content when the view changed
			this.options.nav.on( 'change:active', this.updateUsers, this );
		},

		requestUsers: function() {
			this.collection.reset();

			this.collection.fetch( {
				data: { scope: this.options.scope }
			} );
		},

		updateUsers: function( nav ) {
			if ( 'invites' === nav.get( 'id' ) ) {
				return;
			}

			if ( nav.get( 'active' ) === 1 && this.options.scope !== nav.get( 'id' ) ) {
				this.options.scope = nav.get( 'id' );

				this.requestUsers();
			}
		},

		cleanContent: function( collection ) {
			_.each( this.views._views[''], function( view ) {
				view.remove();
			} );
		},

		addUser: function( user ) {
			this.views.add( new bp.Views.inviteUser( { model: user } ) );
		}
	} );

	bp.Views.inviteUser = bp.Next.GroupInvites.View.extend( {
		tagName:   'li',
		template:  bp.template( 'bp-invites-users' ),

		events: {
			'click' : 'selectUser'
		},

		initialize: function() {},

		selectUser: function( event ) {
			event.preventDefault();

			this.model.set( 'selected', true );
		}
	} );

	// Launch BP Next Groups
	bp.Next.GroupInvites.start();

} )( bp, jQuery );
