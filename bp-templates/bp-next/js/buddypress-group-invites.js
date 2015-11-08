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
			this.displayFeedback( BP_Next.group_invites.loading, 'loading' );

			// Add an invite when a user is selected
			this.users.on( 'change:selected', this.addInvite, this );

			// Add an invite when a user is selected
			this.invites.on( 'change:selected', this.manageInvite, this );

			// And display the Invites nav
			this.invites.on( 'add', this.invitesNav, this );
			this.invites.on( 'reset', this.hideInviteNav, this );
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

			// Loading
			this.displayFeedback( BP_Next.group_invites.loading, 'loading' );

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

		displayFeedback: function( message, type ) {
			var feedback;

			// Make sure to remove the uploads status
			if ( ! _.isUndefined( this.views.get( 'feedback' ) ) ) {
				feedback = this.views.get( 'feedback' );
				feedback.get( 'view' ).remove();
				this.views.remove( { id: 'feedback', view: feedback } );
			}

			if ( ! message ) {
				return;
			}

			feedback = new bp.Views.Feedback( {
				value: message,
				type:  type || 'info'
			} );

			this.views.add( { id: 'feedback', view: feedback } );

			feedback.inject( '.bp-invites-feedback' );
		},

		addInvite: function( user ) {
			if ( true === user.get( 'selected' ) ) {
				this.invites.add( user );
			} else {
				var invite = this.invites.get( user.get( 'id' ) );

				if ( true === invite.get( 'selected' ) ) {
					this.invites.remove( invite );
				}
			}
		},

		manageInvite: function( invite ) {
			var user = this.users.get( invite.get( 'id' ) );

			// Update the user
			if ( user ) {
				user.set( 'selected', false );
			}

			// remove the invite
			this.invites.remove( invite );

			// Here the content should be cleaned first
			// and a warning should inform to use the tab to
			// select invites.
			/*if ( ! this.invites.length  ) {
				this.invites.reset();
			}*/
		},

		invitesNav: function() {
			this.navItems.get( 'invites' ).set( { active: 0, hide: 0 } );
		},

		hideInviteNav: function() {
			this.navItems.get( 'invites' ).set( { active: 0, hide: 1 } );
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

			this.displayFeedback( BP_Next.group_invites.invites_form, 'help' );

			// Activate the loop view
			var invites = new bp.Views.invitesEditor( { collection: this.invites } );

			this.views.add( { id: 'invites', view: invites } );

			invites.inject( '.bp-invites-content' );
		}
	}

	// Item (group or blog or any other)
	bp.Models.User = Backbone.Model.extend( {
		defaults : {
			id       : 0,
			avatar   : '',
			name     : '',
			selected : false
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
			options = options || {};
			options.context = this;

			if ( 'read' === method ) {
				options.data = _.extend( options.data || {}, {
					action: 'groups_get_group_potential_invites',
				} );

				return bp.ajax.send( options );
			}

			if ( 'create' === method ) {
				options.data = _.extend( options.data || {}, {
					action: 'groups_send_group_invites',
				} );

				if ( model ) {
					options.data.users = model;
				}

				return bp.ajax.send( options );
			}

			if ( 'delete' === method ) {
				options.data = _.extend( options.data || {}, {
					action: 'groups_delete_group_invite',
				} );

				if ( model ) {
					options.data.user = model;
				}

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

			if ( ! _.isUndefined( resp.meta ) ) {
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

	// Feedback view
	bp.Views.Feedback = bp.Next.GroupInvites.View.extend( {
		tagName: 'div',
		className: 'bp-feedback',

		initialize: function() {
			this.value = this.options.value;

			if ( this.options.type ) {
				this.el.className += ' ' + this.options.type;
			}
		},

		render: function() {
			this.$el.html( this.value );
			return this;
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

			if ( 'invites' === this.model.get( 'id' ) ) {
				this.el.className += ' dynamic';
			}

			this.model.on( 'change:active', this.toggleClass, this );
			this.on( 'ready', this.updateCount, this );

			bp.Next.GroupInvites.invites.on( 'add', this.updateCount, this );
			bp.Next.GroupInvites.invites.on( 'remove', this.updateCount, this );
		},

		updateCount: function( user, invite ) {
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
				data: { scope: this.options.scope },
				success : this.usersFetched,
				error   : this.usersFetchError
			} );
		},

		usersFetched: function( collection, response ) {
			bp.Next.GroupInvites.displayFeedback( response.feedback, 'help' );
		},

		usersFetchError: function( collection, response ) {
			bp.Next.GroupInvites.displayFeedback( response.feedback, 'help' );
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
			'click .group-add-remove-invite-button'    : 'toggleUser',
			'click .group-remove-invite-button'        : 'removeInvite'
		},

		initialize: function() {
			var invite = bp.Next.GroupInvites.invites.get( this.model.get( 'id' ) );

			if ( invite ) {
				this.el.className = 'selected';
				this.model.set( 'selected', true, { silent: true } );
			}
		},

		toggleUser: function( event ) {
			event.preventDefault();

			var selected = this.model.get( 'selected' );

			if ( false === selected ) {
				this.model.set( 'selected', true );

				// Set the selected class
				$( this.el ).addClass( 'selected' );
			} else {
				this.model.set( 'selected', false );

				// Set the selected class
				$( this.el ).removeClass( 'selected' );

				if ( ! bp.Next.GroupInvites.invites.length  ) {
					bp.Next.GroupInvites.invites.reset();
				}
			}
		},

		removeInvite: function( event ) {
			event.preventDefault();

			var collection = this.model.collection;

			if ( ! collection.length ) {
				return;
			}

			collection.sync( 'delete', this.model.get( 'id' ), {
				success : _.bind( this.inviteRemoved, this ),
				error   : _.bind( this.uninviteError, this )
			} );
		},

		inviteRemoved: function( response ) {
			var collection = this.model.collection;

			if ( ! collection.length ) {
				return;
			}

			collection.remove( this.model );
			this.remove();
		},

		uninviteError: function( response ) {
			console.log( response );
		}
	} );

	bp.Views.invitesEditor = bp.Next.GroupInvites.View.extend( {
		tagName: 'div',
		id:      'send-invites-editor',

		events: {
			'click #bp-invites-send' : 'sendInvites'
		},

		initialize: function() {
			this.views.add( new bp.Views.selectedUsers( { collection: this.collection } ) );
			this.views.add( new bp.Views.invitesForm() );
		},

		sendInvites: function( event ) {
			event.preventDefault();

			this.collection.sync( 'create', _.pluck( this.collection.models, 'id' ), {
				success : _.bind( this.invitesSent, this ),
				error   : _.bind( this.invitesError, this ),
				data    : {
					message: $( this.el ).find( 'textarea' ).val()
				}
			} );
		},

		invitesSent: function( response ) {
			console.log( response );
			console.log( this );
		},

		invitesError: function( response ) {
			console.log( response );
		}
	} );

	bp.Views.invitesForm = bp.Next.GroupInvites.View.extend( {
		tagName : 'div',
		id      : 'bp-send-invites-form',
		template:  bp.template( 'bp-invites-form' )
	} );

	bp.Views.selectedUsers = bp.Next.GroupInvites.View.extend( {
		tagName: 'ul',

		initialize: function() {
			this.cleanContent();

			_.each( this.collection.models, function( invite ) {
				this.views.add( new bp.Views.selectedUser( { model: invite } ) );
			}, this );
		},

		cleanContent: function() {
			_.each( this.views._views[''], function( view ) {
				view.remove();
			} );
		}
	} );

	bp.Views.selectedUser = bp.Next.GroupInvites.View.extend( {
		tagName:   'li',
		template:  bp.template( 'bp-invites-selection' ),

		events: {
			'click' : 'removeSelection'
		},

		initialize: function() {},

		removeSelection: function( event ) {
			event.preventDefault();

			this.model.set( 'selected', false );
			this.remove();
		}
	} );

	// Launch BP Next Groups
	bp.Next.GroupInvites.start();

} )( bp, jQuery );
