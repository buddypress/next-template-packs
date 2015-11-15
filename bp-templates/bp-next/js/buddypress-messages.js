/* global wp, bp, BP_Next, ajaxurl */
window.wp = window.wp || {};
window.bp = window.bp || {};

( function( exports, $ ) {

	// Bail if not set
	if ( typeof BP_Next === 'undefined' ) {
		return;
	}

	_.extend( bp, _.pick( wp, 'Backbone', 'ajax', 'template' ) );

	bp.Models      = bp.Models || {};
	bp.Collections = bp.Collections || {};
	bp.Views       = bp.Views || {};

	bp.Next = bp.Next || {};

	/**
	 * [Next description]
	 * @type {Object}
	 */
	bp.Next.Messages = {
		/**
		 * [start description]
		 * @return {[type]} [description]
		 */
		start: function() {
			this.views    = new Backbone.Collection();
			this.threads  = new bp.Collections.Threads();
			this.router   = new bp.Next.Messages.Router();
			this.box      = 'inbox';

			this.prepareDocument();

			Backbone.history.start();
		},

		prepareDocument: function() {
			var self = this;
			$( '#compose-personal-li' ).addClass( 'last' );

			$( '#compose-personal-li a#compose').html(
				$( '<span></span>' ).html( $( '#compose-personal-li a#compose' ).html() ).addClass( 'bp-screen-reader-text' )
			);

			$( '#compose-personal-li a#compose' ).addClass( 'button' );

			$( '#subnav li a' ).each( function( e, element ) {
				$( element ).prop( 'href', '#' + $( element ).prop( 'id' ) );
			} );

			$( '#subnav a' ).on( 'click', function( event ) {
				event.preventDefault();

				var view_id = $( event.target ).prop( 'id' );

				if ( 'compose' === view_id ) {
					if ( ! _.isUndefined( self.views.get( 'compose' ) ) ) {

						if ( typeof tinymce !== 'undefined' ) {
							var editor = tinymce.get( 'message_content' );

							if ( editor !== null ) {
							    editor.destroy();
							}
						}

						var form = self.views.get( 'compose' );
						form.get( 'view' ).remove();
						self.views.remove( { id: 'compose', view: form } );
						self.router.navigate( 'sentbox', { trigger: true } );
					} else {
						self.router.navigate( 'compose', { trigger: true } );
					}
				} else {
					self.clearViews();

					self.router.navigate( view_id, { trigger: true } );
				}
			} );
		},

		tinyMCEinit: function() {
			if ( typeof window.tinyMCE === 'undefined' || window.tinyMCE.activeEditor === null || typeof window.tinyMCE.activeEditor === 'undefined' ) {
				return;
			} else {
				$( window.tinyMCE.activeEditor.contentDocument.activeElement )
					.atwho( 'setIframe', $( '#message_content_ifr' )[0] )
					.bp_mentions( {
						data: [],
						suffix: ' '
					} );
			}
		},

		removeFeedback: function() {
			var feedback;

			if ( ! _.isUndefined( this.views.get( 'feedback' ) ) ) {
				feedback = this.views.get( 'feedback' );
				feedback.get( 'view' ).remove();
				this.views.remove( { id: 'feedback', view: feedback } );
			}
		},

		displayFeedback: function( message, type ) {
			var feedback;

			// Make sure to remove the feedbacks
			this.removeFeedback();

			if ( ! message ) {
				return;
			}

			feedback = new bp.Views.Feedback( {
				value: message,
				type:  type || 'info'
			} );

			this.views.add( { id: 'feedback', view: feedback } );

			feedback.inject( '.bp-messages-feedback' );
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

		composeView: function() {
			// Create the loop view
			var form = new bp.Views.messageForm( {
				model: new bp.Models.Message()
			} );

			this.views.add( { id: 'compose', view: form } );

			form.inject( '.bp-messages-content' );
		},

		threadsView: function() {
			$( '#subnav ul li' ).each( function( l, li ) {
				$( li ).removeClass( 'current selected' );
			} );

			$( '#subnav a#' + this.box ).closest( 'li' ).addClass( 'current selected' );

			// Create the loop view
			var threads_list = new bp.Views.userThreads( { collection: this.threads, box: this.box } );

			this.views.add( { id: 'threads', view: threads_list } );

			threads_list.inject( '.bp-messages-content' );
		}
	}

	bp.Models.Message = Backbone.Model.extend( {
		defaults: {
			send_to         : [],
			subject         : '',
			message_content : '',
			meta            : {},
		},

		sendMessage: function() {
			if ( true === this.get( 'sending' ) ) {
				return;
			}

			this.set( 'sending', true, { silent: true } );

			var sent = bp.ajax.post( 'messages_send_message', _.extend(
				{
					nonce: BP_Next.messages.nonces.send
				},
				this.attributes
			) );

			this.set( 'sending', false, { silent: true } );

			return sent;
		}
	} );

	bp.Models.Thread = Backbone.Model.extend( {
		defaults: {
			id            : 0,
			subject       : '',
			excerpt       : '',
			sender_name   : '',
			sender_link   : '',
			sender_avatar : '',
			count         : 0,
			date          : ''
		}
	} );

	bp.Collections.Threads = Backbone.Collection.extend( {
		model: bp.Models.Thread,

		initialize : function() {
			this.options = { page: 1, total_page: 0 };
		},

		sync: function( method, model, options ) {
			options         = options || {};
			options.context = this;
			options.data    = options.data || {};

			// Add generic nonce
			options.data.nonce = BP_Next.nonces.messages;

			if ( this.options.group_id ) {
				options.data.group_id = this.options.group_id;
			}

			if ( 'read' === method ) {
				options.data = _.extend( options.data, {
					action: 'messages_get_user_message_threads',
				} );

				return bp.ajax.send( options );
			}

			/*if ( 'create' === method ) {
				options.data = _.extend( options.data, {
					action     : 'groups_send_group_invites',
					'_wpnonce' : BP_Next.group_invites.nonces.send_invites
				} );

				if ( model ) {
					options.data.users = model;
				}

				return bp.ajax.send( options );
			}

			if ( 'delete' === method ) {
				options.data = _.extend( options.data, {
					action     : 'groups_delete_group_invite',
					'_wpnonce' : BP_Next.group_invites.nonces.uninvite
				} );

				if ( model ) {
					options.data.user = model;
				}

				return bp.ajax.send( options );
			}*/
		},

		parse: function( resp ) {

			if ( ! _.isArray( resp.threads ) ) {
				resp.threads = [resp.threads];
			}

			_.each( resp.threads, function( value, index ) {
				if ( _.isNull( value ) ) {
					return;
				}

				resp.threads[index].id            = value.id;
				resp.threads[index].subject       = value.subject;
				resp.threads[index].excerpt       = value.excerpt;
				resp.threads[index].content       = value.content;
				resp.threads[index].sender_name   = value.sender_name;
				resp.threads[index].sender_link   = value.sender_link;
				resp.threads[index].sender_avatar = value.sender_avatar;
				resp.threads[index].count         = value.count;
				resp.threads[index].date          = new Date( value.date );
				resp.threads[index].display_date  = value.display_date;
				resp.threads[index].star_link     = value.star_link;
				resp.threads[index].is_starred    = value.is_starred;
			} );

			if ( ! _.isUndefined( resp.meta ) ) {
				this.options.page       = resp.meta.page;
				this.options.total_page = resp.meta.total_page;
			}

			return resp.threads;
		}

	} );

	// Extend wp.Backbone.View with .prepare() and .inject()
	bp.Next.Messages.View = bp.Backbone.View.extend( {
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
	bp.Views.Feedback = bp.Next.Messages.View.extend( {
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

	bp.Views.messageEditor = bp.Next.Messages.View.extend( {
		template  : bp.template( 'bp-messages-editor' ),

		initialize: function() {
			this.on( 'ready', this.activateTinyMce, this );
		},

		activateTinyMce: function() {
			if ( typeof tinymce !== 'undefined' ) {
				tinymce.EditorManager.execCommand( 'mceAddEditor', true, 'message_content' );
			}
		}
	} );

	bp.Views.messageForm = bp.Next.Messages.View.extend( {
		tagName   : 'form',
		id        : 'send_message_form',
		className : 'standard-form',
		template  : bp.template( 'bp-messages-form' ),

		events: {
			'click #bp-messages-send' : 'sendMessage'
		},

		initialize: function() {
			// Clone the model to set the resetted one
			this.resetModel = this.model.clone();

			this.on( 'ready', this.addEditor, this );
			this.model.on( 'change', this.resetFields, this );
		},

		addEditor: function() {
			// Add autocomplete to send_to field
			$( this.el ).find( '#send-to-input' ).bp_mentions( {
				data: [],
				suffix: ' '
			} );

			// Load the Editor
			this.views.add( '#bp-messages-content', new bp.Views.messageEditor() );
		},

		resetFields: function( model ) {
			// Clean inputs
			_.each( model.previousAttributes(), function( value, input ) {
				if ( 'message_content' === input ) {
					// tinyMce
					if ( undefined !== tinyMCE.activeEditor && null !== tinyMCE.activeEditor ) {
						tinyMCE.activeEditor.setContent( '' );
					}

				// All except meta or empty value
				} else if ( 'meta' !== input && false !== value ) {
					$( 'input[name="' + input + '"]' ).val( '' );
				}
			} );

			// Listen to this to eventually reset your custom inputs.
			$(this.el ).trigger( 'message:reset', _.pick( model.previousAttributes(), 'meta' ) );
		},

		sendMessage: function( event ) {
			var meta = {}, errors = [], self = this;
			event.preventDefault();

			bp.Next.Messages.removeFeedback();

			// Set the content and meta
			_.each( this.$el.serializeArray(), function( pair ) {
				pair.name = pair.name.replace( '[]', '' );

				// Group extra fields in meta
				if ( -1 === _.indexOf( ['send_to', 'subject', 'message_content'], pair.name ) ) {
					if ( _.isUndefined( meta[ pair.name ] ) ) {
						meta[ pair.name ] = pair.value;
					} else {
						if ( ! _.isArray( meta[ pair.name ] ) ) {
							meta[ pair.name ] = [ meta[ pair.name ] ];
						}

						meta[ pair.name ].push( pair.value );
					}

				// Prepare the core model
				} else {
					// Send to
					if ( 'send_to' === pair.name ) {
						var usernames = pair.value.match( /(^|[^@\w\-])@([a-zA-Z0-9_\-]{1,50})\b/g );

						if ( ! usernames ) {
							errors.push( 'send_to' );
						} else {
							usernames = usernames.map( function( username ) {
								username = $.trim( username );
								return username;
							} );

							if ( ! usernames || ! $.isArray( usernames ) ) {
								errors.push( 'send_to' );
							}

							this.model.set( 'send_to', usernames, { silent: true } );
						}

					// Subject and content
					} else {
						// Message content
						if ( 'message_content' === pair.name && undefined !== tinyMCE.activeEditor ) {
							pair.value = tinyMCE.activeEditor.getContent();
						}

						if ( ! pair.value ) {
							errors.push( pair.name );
						} else {
							this.model.set( pair.name, pair.value, { silent: true } );
						}
					}
				}

			}, this );

			if ( errors.length ) {
				var feedback = '';
				_.each( errors, function( e ) {
					feedback += BP_Next.messages.errors[ e ] + '<br/>';
				} );

				bp.Next.Messages.displayFeedback( feedback, 'error' );
				return;
			}

			// Set meta
			this.model.set( 'meta', meta, { silent: true } );

			// Send the message.
			this.model.sendMessage().done( function( response ) {
				// Reset the model
				self.model.set( self.resetModel );

				bp.Next.Messages.displayFeedback( response.feedback, response.type );

				var form = bp.Next.Messages.views.get( 'compose' );
				form.get( 'view' ).remove();
				bp.Next.Messages.views.remove( { id: 'compose', view: form } );

				bp.Next.Messages.router.navigate( 'sentbox', { trigger: true } );
			} ).fail( function( response ) {
				if ( response.feedback ) {
					bp.Next.Messages.displayFeedback( response.feedback, response.type );
				}
			} );
		}
	} );

	bp.Views.userThreads = bp.Next.Messages.View.extend( {
		tagName   : 'div',

		events: {
			'click .thread-content' : 'changePreview'
		},

		initialize: function() {
			this.views.add( new bp.Next.Messages.View( { tagName: 'ul', id: 'message-threads' } ) );
			this.views.add( new bp.Views.previewThread( { collection: this.collection } ) );
			// Load users for the active view
			this.requestThreads();

			//this.collection.on( 'reset', this.cleanContent, this );
			this.collection.on( 'add', this.addThread, this );
		},

		requestThreads: function() {
			this.collection.reset();

			bp.Next.Messages.displayFeedback( 'Loading, please wait', 'loading' );

			this.collection.fetch( {
				data    : _.pick( this.options, 'box' ),
				success : this.threadsFetched,
				error   : this.threadsFetchError
			} );
		},

		threadsFetched: function( collection, response ) {
			bp.Next.Messages.removeFeedback();
		},

		threadsFetchError: function( collection, response ) {
			bp.Next.Messages.displayFeedback( response.feedback, response.type );
		},

		addThread: function( thread ) {
			var selected = this.collection.findWhere( { is_selected: true } );

			if ( _.isUndefined( selected ) ) {
				thread.set( 'is_selected', true );
			}

			this.views.add( '#message-threads', new bp.Views.userThread( { model: thread } ) );
		},

		changePreview: function( event ) {
			target = $( event.currentTarget );

			if ( ! target.hasClass( 'thread-content' ) || $( event.target ).hasClass( 'user-link' ) ) {
				return event;
			}

			event.preventDefault();

			if ( target.parent( 'li' ).hasClass( 'selected' ) ) {
				return;
			}

			var selected = target.data( 'thread-id' );

			_.each( this.collection.models, function( thread ) {
				if ( thread.id === selected ) {
					thread.set( 'is_selected', true );
				} else {
					thread.unset( 'is_selected' );
				}
			}, this );
		}
	} );

	bp.Views.userThread = bp.Next.Messages.View.extend( {
		tagName   : 'li',
		template  : bp.template( 'bp-messages-thread' ),
		className : 'thread-item',

		initialize: function() {
			if ( this.model.get( 'is_selected' ) ) {
				this.el.className += ' selected';
			}

			this.model.on( 'change:is_selected', this.toggleClass, this );
		},

		toggleClass: function( model ) {
			if ( true === model.get( 'is_selected' ) ) {
				$( this.el ).addClass( 'selected' );
			} else {
				$( this.el ).removeClass( 'selected' );
			}
		}
	} );

	bp.Views.previewThread = bp.Next.Messages.View.extend( {
		tagName: 'div',
		id: 'thread-preview',
		template  : bp.template( 'bp-messages-preview' ),

		initialize: function() {
			this.collection.on( 'change:is_selected', this.loadPreview, this );
		},

		loadPreview: function( model ) {
			if ( true === model.get( 'is_selected' ) ) {
				this.model = model;
				this.render();
			}
		}
	} );

	bp.Next.Messages.Router = Backbone.Router.extend( {
		routes: {
			'compose' : 'composeMessage',
			'view/:id': 'viewMessage',
			'sentbox' : 'sentboxView',
			'starred' : 'starredView',
			'inbox'   : 'inboxView',
			''        : 'inboxView'
		},

		composeMessage: function() {
			bp.Next.Messages.composeView();
		},

		viewMessage: function( query ) {
			console.log( query );
		},

		sentboxView: function() {
			bp.Next.Messages.box = 'sentbox';
			bp.Next.Messages.threadsView();
		},

		starredView: function() {
			bp.Next.Messages.box = 'starred';
			bp.Next.Messages.threadsView();
		},

		inboxView: function() {
			bp.Next.Messages.box = 'inbox';
			bp.Next.Messages.threadsView();
		}
	} );

	// Launch BP Next Groups
	bp.Next.Messages.start();

} )( bp, jQuery );
