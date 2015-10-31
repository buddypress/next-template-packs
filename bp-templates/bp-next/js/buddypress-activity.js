/* global bp, BP_Next, ajaxurl */
window.bp = window.bp || {};

( function( exports, $ ) {

	// Bail if not set
	if ( typeof BP_Next === 'undefined' ) {
		return;
	}

	bp.Next = bp.Next || {};

	/**
	 * [Activity description]
	 * @type {Object}
	 */
	bp.Next.Activity = {
		/**
		 * [start description]
		 * @return {[type]} [description]
		 */
		start: function() {
			// Listen to events ("Add hooks!")
			this.addListeners();
		},

		/**
		 * [addListeners description]
		 */
		addListeners: function() {
			// HeartBeat listeners
			$( '#buddypress' ).on( 'bp_heartbeat_pending', '#activity-stream', bp.Next, this.prepareScope );
			$( '#buddypress' ).on( 'bp_heartbeat_prepend', '#activity-stream', bp.Next, this.updateScope );
			$( '#buddypress' ).on( 'bp_ajax_request', '.bp-activity-list', bp.Next, this.scopeLoaded );

			// Activity actions
			$( '#buddypress #activity-stream' ).on( 'click', '.activity-item', bp.Next, this.activityActions );
			$( document ).keydown( this.closeCommentForm );
		},

		/**
		 * [prepareScope description]
		 * @param  {[type]} event [description]
		 * @return {[type]}       [description]
		 */
		prepareScope: function( event ) {
			var parent = event.data, objects = parent.objects;

			/**
			 * It's not a regular object but we need it!
			 * so let's add it temporarly..
			 */
			objects.push( 'mentions' );

			$.each( objects, function( o, object ) {
				if ( undefined !== parent.highlights[ object ] && parent.highlights[ object ].length ) {
					$( parent.objectNavParent + ' [data-scope="' + object + '"]' ).find( 'a span' ).html( Number( parent.highlights[ object ].length ) );
				}
			} );

			/**
			 * Let's remove the mentions from objects!
			 */
			 objects.pop();
		},

		/**
		 * [updateScope description]
		 * @param  {[type]} event [description]
		 * @return {[type]}       [description]
		 */
		updateScope: function( event ) {
			var parent = event.data, scope = parent.getStorage( 'bp-activity', 'scope' );

			// Specific to mentions
			if ( 'mentions' === scope ) {
				// Now mentions are displayed, remove the user_metas
				parent.ajax( { action: 'activity_clear_new_mentions' }, 'activity' ).done( function( response ) {
					if ( false === response.success ) {
						// Display a warning ?
						console.log( 'warning' );
					}
				} );
			}

			// Activities are now displayed, clear the newest count for the scope
			$( parent.objectNavParent + ' [data-scope="' + scope + '"]' ).find( 'a span' ).html( '' );

			// Activities are now displayed, clear the highlighted activities for the scope
			if ( undefined !== parent.highlights[ scope ] ) {
				parent.highlights[ scope ] = [];
			}

			// Remove highlighted for the current scope
			setTimeout( function () {
				$( event.currentTarget ).find( '.activity-item' ).removeClass( 'newest_' + scope + '_activity' );
			}, 3000 );
		},

		/**
		 * [scopeLoaded description]
		 * @param  {[type]} event [description]
		 * @param  {[type]} data  [description]
		 * @return {[type]}       [description]
		 */
		scopeLoaded: function ( event, data ) {
			var parent = event.data;

			// Mentions are specific
			if ( 'mentions' === data.scope && undefined !== data.response.new_mentions ) {
				$.each( data.response.new_mentions, function( i, id ) {
					$( '#buddypress #activity-stream' ).find( '[data-id="' + id + '"]' ).addClass( 'newest_mentions_activity' );
				} );
			} else if ( undefined !== parent.highlights[data.scope] && parent.highlights[data.scope].length ) {
				$.each( parent.highlights[data.scope], function( i, id ) {
					if ( $( '#buddypress #activity-stream' ).find( '[data-id="' + id + '"]' ).length ) {
						$( '#buddypress #activity-stream' ).find( '[data-id="' + id + '"]' ).addClass( 'newest_' + data.scope + '_activity' );
					}
				} );
			}

			// Activities are now loaded, clear the highlighted activities for the scope
			if ( undefined !== parent.highlights[ data.scope ] ) {
				parent.highlights[ data.scope ] = [];
			}

			setTimeout( function () {
				$( '#buddypress #activity-stream .activity-item' ).removeClass( 'newest_' + data.scope +'_activity' );
			}, 3000 );
		},

		/**
		 * [activityActions description]
		 * @param  {[type]} event [description]
		 * @return {[type]}       [description]
		 */
		activityActions: function( event ) {
			var parent = event.data, target = $( event.target ), activity_item = $( event.currentTarget ),
				activity_id = activity_item.data( 'id' ), stream = $( event.delegateTarget );

			// Favoriting
			if ( target.hasClass( 'fav') || target.hasClass('unfav') ) {
				var type = target.hasClass( 'fav' ) ? 'fav' : 'unfav';

				// Stop event propagation
				event.preventDefault();

				target.addClass( 'loading' );

				parent.ajax( { action: 'activity_mark_' + type, 'id': activity_id }, 'activity' ).done( function( response ) {
					target.removeClass( 'loading' );

					if ( false === response.success ) {
						return;
					} else {
						target.fadeOut( 200, function() {
							if ( $( this ).find( 'span' ).first().length ) {
								$( this ).find( 'span' ).first().html( response.data.content );
							} else {
								$( this ).html( response.data.content );
							}
							$( this ).prop( 'title', response.data.content );
							$( this ).fadeIn( 200 );
						} );
					}

					if ( 'fav' === type ) {
						if ( undefined !== response.data.directory_tab ) {
							if ( ! $( parent.objectNavParent + ' [data-scope="favorites"]' ).length ) {
								$( parent.objectNavParent + ' [data-scope="all"]' ).after( response.data.directory_tab );
							}
						}

						target.removeClass( 'fav' );
						target.addClass( 'unfav' );

					} else if ( 'unfav' === type ) {
						// If on user's profile or on the favorites directory tab, remove the entry
						if ( ! $( parent.objectNavParent + ' [data-scope="favorites"]' ).length || $( parent.objectNavParent + ' [data-scope="favorites"]' ).hasClass( 'selected' )  ) {
							activity_item.remove();
						}

						if ( undefined !== response.data.no_favorite ) {
							// Remove the tab when on activity directory but not on the favorites tabs
							if ( $( parent.objectNavParent + ' [data-scope="all"]' ).length && $( parent.objectNavParent + ' [data-scope="all"]' ).hasClass( 'selected' ) ) {
								$( parent.objectNavParent + ' [data-scope="favorites"]' ).remove();

							// In all the other cases, append a message to the empty stream
							} else {
								stream.append( response.data.no_favorite );
							}
						}

						target.removeClass( 'unfav' );
						target.addClass( 'fav' );
					}
				} );
			}

			// Deleting
			if ( target.hasClass( 'delete-activity' ) ) {
				// Stop event propagation
				event.preventDefault();

				target.addClass( 'loading' );

				parent.ajax( {
					action     : 'delete_activity',
					'id'       : activity_id,
					'_wpnonce' : parent.getLinkParams( target.prop( 'href' ), '_wpnonce' )
				}, 'activity' ).done( function( response ) {
					target.removeClass( 'loading' );

					if ( false === response.success ) {
						activity_item.prepend( response.data.feedback );
						activity_item.find( '.feedback' ).hide().fadeIn( 300 );
					} else {
						activity_item.slideUp( 300 );

						// reset vars to get newest activities
						if ( parent.getActivityTimestamp( activity_item ) === parent.activity_last_recorded ) {
							parent.newest_activities       = '';
							parent.activity_last_recorded  = 0;
						}
					}
				} );
			}

			// Reading more
			if ( target.closest( 'span' ).hasClass( 'activity-read-more' ) ) {
				var content = target.closest( 'div' ), readMore = target.closest( 'span' ), item_id;

				if ( $( content ).hasClass( 'activity-inner' ) ) {
					item_id = activity_id;
				} else if ( $( content ).hasClass( 'acomment-content' ) ) {
					item_id = target.closest( 'li' ).data( 'commentid' );
				}

				if ( ! item_id ) {
					return event;
				}

				// Stop event propagation
				event.preventDefault();

				$( readMore ).addClass( 'loading' );

				parent.ajax( {
					action     : 'get_single_activity_content',
					'id'       : item_id,
				}, 'activity' ).done( function( response ) {
					$( readMore ).removeClass( 'loading' );

					if ( content.parent().find( '.feedback' ).length ) {
						content.parent().find( '.feedback' ).remove();
					}

					if ( false === response.success ) {
						content.after( response.data.feedback );
						content.parent().find( '.feedback' ).hide().fadeIn( 300 );
					} else {
						$( content ).slideUp( 300 ).html( response.data.contents ).slideDown( 300 );
					}
				} );
			}

			// Displaying the comment form
			if ( target.hasClass( 'acomment-reply' ) || target.parent().hasClass( 'acomment-reply' ) ) {
				var comment_link = target, item_id = activity_id, form = $( '#ac-form-' + activity_id );

				// Stop event propagation
				event.preventDefault();

				// If the comment count span inside the link is clicked
				if ( target.parent().hasClass( 'acomment-reply' ) ) {
					comment_link = target.parent();
				}

				if ( target.closest( 'li' ).data( 'commentid' ) ) {
					item_id = target.closest( 'li' ).data( 'commentid' );
				}

				// ?? hide and display none..
				//form.css( 'display', 'none' );
				form.removeClass( 'root' );
				$('.ac-form').hide();

				/* Remove any error messages */
				$.each( form.children( 'div' ), function( e, err ) {
					if ( $( err ).hasClass( 'error' ) ) {
						$( err ).remove();
					}
				} );

				// It's an activity we're commenting
				if ( item_id === activity_id ) {
					$( '[data-id="' + item_id + '"] .activity-comments' ).append( form );
					form.addClass( 'root' );

				// It's a comment we're replying to
				} else {
					$( '[data-commentid="' + item_id + '"]' ).append( form );
				}

				form.slideDown( 200 );

				$.scrollTo( form, 500, {
					offset:-100,
					easing:'swing'
				} );

				$( '#ac-form-' + activity_id + ' textarea' ).focus();
			}

			// Removing the form
			if ( target.hasClass( 'ac-reply-cancel' ) ) {
				$( target ).closest( '.ac-form' ).slideUp( 200 );

				// Stop event propagation
				event.preventDefault();
			}

			// Submitting comments and replies
			if ( 'ac_form_submit' === target.prop( 'name' ) ) {
				var form = target.closest( 'form' ), item_id = activity_id, comment_content,
					comment_data, comment_count;

				// Stop event propagation
				event.preventDefault();

				if ( target.closest( 'li' ).data( 'commentid' ) ) {
					item_id    = target.closest( 'li' ).data( 'commentid' );
				}

				comment_content = $( form ).find( 'textarea' ).first();

				target.addClass( 'loading' ).prop( 'disabled', true );
				comment_content.addClass( 'loading' ).prop( 'disabled', true );

				comment_data = {
					action:                          'new_activity_comment',
					'_wpnonce_new_activity_comment': $( '#_wpnonce_new_activity_comment' ).val(),
					'comment_id':                    item_id,
					'form_id':                       activity_id,
					'content':                       comment_content.val()
				};

				// Akismet
				if ( $( '#_bp_as_nonce_' + item_id ).val() ) {
					comment_data['_bp_as_nonce_' + item_id] = $( '#_bp_as_nonce_' + item_id ).val();
				}

				parent.ajax( comment_data, 'activity' ).done( function( response ) {
					target.removeClass( 'loading' );
					comment_content.removeClass( 'loading' );

					if ( false === response.success ) {
						form.append( $( response.data.feedback ).hide().fadeIn( 200 ) );
					} else {
						var activity_comments = form.parent();
						var the_comment = $.trim( response.data.contents );

						form.fadeOut( 200, function() {
							if ( 0 === activity_comments.children( 'ul' ).length ) {
								if ( activity_comments.hasClass( 'activity-comments' ) ) {
									activity_comments.prepend( '<ul></ul>' );
								} else {
									activity_comments.append( '<ul></ul>' );
								}
							}

							activity_comments.children( 'ul' ).append( $( the_comment ).hide().fadeIn( 200 ) );
							$( form ).find( 'textarea' ).first().val( '' );

							activity_comments.parent().addClass( 'has-comments' );
						} );

						// why, as it's already done a few lines ahead ???
						//jq( '#' + form.attr('id') + ' textarea').val('');

						// Set the new count
						comment_count = Number( $( activity_item ).find( 'a span.comment-count' ).html() || 0 ) + 1;

						// Increase the "Reply (X)" button count
						$( activity_item ).find( 'a span.comment-count' ).html( comment_count );

						// Increment the 'Show all x comments' string, if present
						show_all_a = $( activity_item ).find( '.show-all a' );
						if ( show_all_a ) {
							show_all_a.html( BP_Next.show_x_comments.replace( '%d', comment_count ) );
						}
					}

					target.prop( 'disabled', false );
					comment_content.prop( 'disabled', false );
				} );
			}
		},

		closeCommentForm: function( event ) {
			var event = event || window.event, element, keyCode;

			if ( event.target ) {
				element = event.target;
			} else if ( event.srcElement) {
				element = event.srcElement;
			}

			if ( element.nodeType === 3 ) {
				element = element.parentNode;
			}

			if ( event.ctrlKey === true || event.altKey === true || event.metaKey === true ) {
				return event;
			}

			keyCode = ( event.keyCode) ? event.keyCode : event.which;

			if ( 27 === keyCode ) {
				if ( element.tagName === 'TEXTAREA' ) {
					if ( $( element ).hasClass( 'ac-input' ) ) {
						$( element ).closest( 'form' ).slideUp( 200 );
						console.log( $( element ).parent().parent().parent() );
					}
				}
			}
		}
	}

	// Launch BP Next Activity
	bp.Next.Activity.start();

} )( bp, jQuery );
