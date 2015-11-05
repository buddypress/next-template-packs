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

			this.views = new Backbone.Collection();
			this.nav   = new bp.Views.invitesNav().inject( '.bp-invites-nav' );
			/*this.main.on( 'render', function() {
				console.log( this );
			}, this.main );*/

			//this.views.add( '.bp-invites-nav', new bp.Views.FormButton( { model: button } ) )
			/* Setup globals
			this.setupGlobals();

			// Adjust Document/Forms properties
			this.prepareDocument();

			// Init the BuddyPress objects
			this.initObjects();

			// Set BuddyPress HeartBeat
			this.setHeartBeat();

			// Listen to events ("Add hooks!")
			this.addListeners();*/

			console.log( 'loaded' );
		}
	}

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

		initialize: function() {
			this.on( 'ready', this.test, this );
		},

		test: function() {
			var nav_items = [
				new Backbone.Model( {
					id: 'test',
					href:'#',
					name: 'test'
				} )
			];
			_.each( nav_items, function( nav ) {
				this.views.add( new bp.Views.invitesNavItem( { model: nav } ) );
			}, this );
		},

		inject: function() {
			this.render();
			$( '.bp-invites-nav' ).html( this.el );
			this.views.ready();
		}
	} );

	bp.Views.invitesNavItem = bp.Next.GroupInvites.View.extend( {
		tagName:   'li',
		className: 'current selected',
		template:  bp.template( 'bp-invites-nav' ),

		attributes: {
			role: 'checkbox'
		},

		initialize: function() {
			console.log( this.model );
			/*if ( this.model.get( 'selected' ) ) {
				this.el.className += ' selected';
			}*/
		},

		events: {
			'click': 'setObject'
		},

		setObject:function( event ) {
			event.preventDefault();

			if ( true === this.model.get( 'selected' ) ) {
				this.model.clear();
			} else {
				this.model.set( 'selected', true );
			}
		}
	} );

	// Launch BP Next Groups
	bp.Next.GroupInvites.start();

} )( bp, jQuery );
