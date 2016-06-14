<?php
/**
 * Groups Ajax functions
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Join or leave a group when clicking the "join/leave" button via a POST request.
 *
 * @since 1.0.0
 *
 * @return string HTML
 */
function bp_nouveau_ajax_joinleave_group() {
	$response = array(
		'feedback' => sprintf(
			'<div class="feedback error bp-ajax-message"><p>%s</p></div>',
			esc_html__( 'There was a problem performing this action. Please try again.', 'bp-nouveau' )
		)
	);

	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) || empty( $_POST['action'] ) ) {
		wp_send_json_error( $response );
	}

	if ( empty( $_POST['nonce'] ) || empty( $_POST['item_id'] )  || ! bp_is_active( 'groups' ) ) {
		wp_send_json_error( $response );
	}

	// Use default nonce
	$nonce = $_POST['nonce'];
	$check = 'bp_nouveau_groups';

	// Use a specific one for actions needed it
	if ( ! empty( $_POST['_wpnonce'] ) && ! empty( $_POST['action'] ) ) {
		$nonce = $_POST['_wpnonce'];
		$check = $_POST['action'];
	}

	// Nonce check!
	if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, $check ) ) {
		wp_send_json_error( $response );
	}

	// Cast gid as integer.
	$group_id = (int) $_POST['item_id'];

	if ( groups_is_user_banned( bp_loggedin_user_id(), $group_id ) ) {
		$response['feedback'] = sprintf(
			'<div class="bp-feedback error"><p>%s</p></div>',
			esc_html__( 'You cannot join this group.', 'bp-nouveau' )
		);

		wp_send_json_error( $response );
	}

	// Validate and get the group
	$group = groups_get_group( array( 'group_id' => $group_id ) );

	if ( empty( $group->id ) ) {
		wp_send_json_error( $response );
	}

	/**
	 *

	 Every action should be handled here

	 */
	switch ( $_POST['action'] ) {

		case 'groups_accept_invite' :
			if ( ! groups_accept_invite( bp_loggedin_user_id(), $group_id ) ) {
				$response = array(
					'feedback' => sprintf(
						'<div class="bp-feedback error">%s</div>',
						esc_html__( 'Group invite could not be accepted', 'bp-nouveau' )
					),
					'type'     => 'error'
				);

			} else {
				groups_record_activity( array(
					'type'    => 'joined_group',
					'item_id' => $group->id
				) );

				$response = array(
					'feedback' => sprintf(
						'<div class="bp-feedback success">%s</div>',
						esc_html__( 'Group invite accepted', 'bp-nouveau' )
					),
					'type'     => 'success',
					'is_user'  => bp_is_user(),
				);
			}
			break;

		case 'groups_reject_invite' :
			if ( ! groups_reject_invite( bp_loggedin_user_id(), $group_id ) ) {
				$response = array(
					'feedback' => sprintf(
						'<div class="bp-feedback error">%s</div>',
						esc_html__( 'Group invite could not be rejected', 'bp-nouveau' )
					),
					'type'     => 'error'
				);
			} else {
				$response = array(
					'feedback' => sprintf(
						'<div class="bp-feedback success">%s</div>',
						esc_html__( 'Group invite rejected', 'bp-nouveau' )
					),
					'type'     => 'success',
					'is_user'  => bp_is_user(),
				);
			}
			break;
	}

	if ( 'error' === $response['type'] ) {
		wp_send_json_error( $response );
	} else {
		wp_send_json_success( $response );
	}

	if ( ! groups_is_user_member( bp_loggedin_user_id(), $group->id ) ) {
		if ( 'public' == $group->status ) {

			if ( ! groups_join_group( $group->id ) ) {
				$response['feedback'] = sprintf(
					'<div class="feedback error bp-ajax-message"><p>%s</p></div>',
					esc_html__( 'Error joining group', 'bp-nouveau' )
				);

				wp_send_json_error( $response );
			} else {
				// User is now a member of the group
				$group->is_member = '1';

				wp_send_json_success( array(
					'contents' => bp_get_group_join_button( $group ),
					'is_group' => bp_is_group()
				) );
			}

		} elseif ( 'private' == $group->status ) {

			// If the user has already been invited, then this is
			// an Accept Invitation button.
			if ( groups_check_user_has_invite( bp_loggedin_user_id(), $group->id ) ) {

				if ( ! groups_accept_invite( bp_loggedin_user_id(), $group->id ) ) {
					$response['feedback'] = sprintf(
						'<div class="feedback error bp-ajax-message"><p>%s</p></div>',
						esc_html__( 'Error requesting membership', 'bp-nouveau' )
					);

					wp_send_json_error( $response );
				} else {
					// User is now a member of the group
					$group->is_member = '1';

					wp_send_json_success( array(
						'contents' => bp_get_group_join_button( $group ),
						'is_group' => bp_is_group()
					) );
				}

			// Otherwise, it's a Request Membership button.
			} else {

				if ( ! groups_send_membership_request( bp_loggedin_user_id(), $group->id ) ) {
					$response['feedback'] = sprintf(
						'<div class="feedback error bp-ajax-message"><p>%s</p></div>',
						esc_html__( 'Error requesting membership', 'bp-nouveau' )
					);

					wp_send_json_error( $response );
				} else {
					// Request is pending
					$group->is_pending = '1';

					wp_send_json_success( array(
						'contents' => bp_get_group_join_button( $group ),
						'is_group' => bp_is_group()
					) );
				}
			}
		}

	} else {

		if ( ! groups_leave_group( $group->id ) ) {
			$response['feedback'] = sprintf(
				'<div class="feedback error bp-ajax-message"><p>%s</p></div>',
				esc_html__( 'Error leaving group', 'bp-nouveau' )
			);

			wp_send_json_error( $response );
		} else {
			// User is no more a member of the group
			$group->is_member = '0';

			wp_send_json_success( array(
				'contents' => bp_get_group_join_button( $group ),
				'is_group' => bp_is_group()
			) );
		}
	}
}

function bp_nouveau_ajax_get_users_to_invite() {
	$bp = buddypress();

	$response = array(
		'feedback' => esc_html__( 'There was a problem performing this action. Please try again.', 'bp-nouveau' ),
		'type'     => 'error',
	);

	if ( empty( $_POST['nonce'] ) ) {
		wp_send_json_error( $response );
	}

	// Use default nonce
	$nonce = $_POST['nonce'];
	$check = 'bp_nouveau_groups';

	// Use a specific one for actions needed it
	if ( ! empty( $_POST['_wpnonce'] ) && ! empty( $_POST['action'] ) ) {
		$nonce = $_POST['_wpnonce'];
		$check = $_POST['action'];
	}

	// Nonce check!
	if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, $check ) ) {
		wp_send_json_error( $response );
	}

	$request = wp_parse_args( $_POST, array(
		'scope' => 'members',
	) );

	$bp->groups->invites_scope = 'members';
	$message = __( 'You can invite members using the + button, a new nav will appear to let you send your invites', 'bp-nouveau' );

	if ( 'friends' === $request['scope'] ) {
		$request['user_id'] = bp_loggedin_user_id();
		$bp->groups->invites_scope = 'friends';
		$message = __( 'You can invite friends using the + button, a new nav will appear to let you send your invites', 'bp-nouveau' );
	}

	if ( 'invited' === $request['scope'] ) {

		if ( ! bp_group_has_invites( array( 'user_id' => 'any' ) ) ) {
			wp_send_json_error( array(
				'feedback' => __( 'No pending invites found.', 'bp-nouveau' ),
				'type'     => 'info',
			) );
		}

		$request['is_confirmed'] = false;
		$bp->groups->invites_scope = 'invited';
		$message = __( 'You can view all the group\'s pending invites from this screen.', 'bp-nouveau' );
	}

	$potential_invites = bp_nouveau_get_group_potential_invites( $request );

	if ( empty( $potential_invites->users ) ) {
		$error = array(
			'feedback' => __( 'No members were found, try another filter.', 'bp-nouveau' ),
			'type'     => 'info',
		);

		if ( 'friends' === $bp->groups->invites_scope ) {
			$error = array(
				'feedback' => __( 'All your friends are already members of this group or already received an invite to join this group.', 'bp-nouveau' ),
				'type'     => 'info',
			);

			if ( 0 === (int) bp_get_total_friend_count( bp_loggedin_user_id() ) ) {
				$error = array(
					'feedback' => __( 'You have no friends!', 'bp-nouveau' ),
					'type'     => 'info',
				);
			}

		}

		unset( $bp->groups->invites_scope );

		wp_send_json_error( $error );
	}

	$potential_invites->users = array_map( 'bp_nouveau_prepare_group_potential_invites_for_js', array_values( $potential_invites->users ) );
	$potential_invites->users = array_filter( $potential_invites->users );

	// Set a message to explain use of the current scope
	$potential_invites->feedback = $message;

	unset( $bp->groups->invites_scope );

	wp_send_json_success( $potential_invites );
}

function bp_nouveau_ajax_send_group_invites() {
	$bp = buddypress();

	$response = array(
		'feedback' => __( 'Invites could not be sent, please try again.', 'bp-nouveau' ),
	);

	// Verify nonce
	if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'groups_send_invites' ) ) {
		wp_send_json_error( array(
			'feedback' => __( 'Invites could not be sent, please try again.', 'bp-nouveau' ),
			'type'     => 'error',
		) );
	}

	$group_id = bp_get_current_group_id();

	if ( bp_is_group_create() && ! empty( $_POST['group_id'] ) ) {
		$group_id = (int) $_POST['group_id'];
	}

	if ( ! bp_groups_user_can_send_invites( $group_id ) ) {
		wp_send_json_error( array(
			'feedback' => __( 'You are not allowed to send invites for this group.', 'bp-nouveau' ),
			'type'     => 'error',
		) );
	}

	if ( empty( $_POST['users'] ) ) {
		wp_send_json_error( $response );
	}

	// For feedback
	$invited = array();

	foreach ( (array) $_POST['users'] as $user_id ) {
		$invited[ $user_id ] = groups_invite_user( array( 'user_id' => $user_id, 'group_id' => $group_id ) );
	}

	if ( ! empty( $_POST['message'] ) ) {
		$bp->groups->invites_message = wp_kses( wp_unslash( $_POST['message'] ), array() );

		add_filter( 'groups_notification_group_invites_message', 'bp_nouveau_groups_invites_custom_message', 10, 1 );
	}

	// Send the invites.
	groups_send_invites( bp_loggedin_user_id(), $group_id );

	if ( ! empty( $_POST['message'] ) ) {
		unset( $bp->groups->invites_message );

		remove_filter( 'groups_notification_group_invites_message', 'bp_nouveau_groups_invites_custom_message', 10, 1 );
	}

	if ( array_search( false, $invited ) ) {
		$errors = array_keys( $invited, false );

		wp_send_json_error( array(
			'feedback' => sprintf( __( 'Invites failed for %d user(s).', 'bp-nouveau' ), count( $errors ) ),
			'users'    => $errors,
			'type'     => 'error',
		) );
	}

	wp_send_json_success( array(
		'feedback' => __( 'Invites sent.', 'bp-nouveau' )
	) );
}

function bp_nouveau_ajax_remove_group_invite() {
	$user_id  = $_POST['user'];
	$group_id = bp_get_current_group_id();

	// Verify nonce
	if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'groups_invite_uninvite_user' ) ) {
		wp_send_json_error( array(
			'feedback' => __( 'Invites could not be removed, please try again.', 'bp-nouveau' ),
		) );
	}

	if ( BP_Groups_Member::check_for_membership_request( $user_id, $group_id ) ) {
		wp_send_json_error( array(
			'feedback' => __( 'Too late, the user is now a member of the group.', 'bp-nouveau' ),
			'code'     => 1,
		) );
	}

	// Remove the unsent invitation.
	if ( ! groups_uninvite_user( $user_id, $group_id ) ) {
		wp_send_json_error( array(
			'feedback' => __( 'Removing the invite for the user failed.', 'bp-nouveau' ),
			'code'     => 0,
		) );
	}

	wp_send_json_success( array(
		'feedback'    => __( 'No more pending invites for the group.', 'bp-nouveau' ),
		'has_invites' => bp_group_has_invites( array( 'user_id' => 'any' ) ),
	) );
}
