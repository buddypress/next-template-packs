<?php
/**
 * Members template tags
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Template tag to wrap all Legacy actions that was used
 * before the members directory content
 *
 * @since 1.0.0
 */
function bp_nouveau_before_members_directory_content() {
	/**
	 * Fires at the begining of the templates BP injected content.
	 *
	 * @since 2.3.0 (BuddyPress)
	 */
	do_action( 'bp_before_directory_members_page' );

	/**
	 * Fires before the display of the members.
	 *
	 * @since 1.1.0 (BuddyPress)
	 */
	do_action( 'bp_before_directory_members' );

	/**
	 * Fires before the display of the members content.
	 *
	 * @since 1.1.0 (BuddyPress)
	 */
	do_action( 'bp_before_directory_members_content' );

	/**
	 * Fires before the display of the members list tabs.
	 *
	 * @since 1.8.0 (BuddyPress)
	 */
	do_action( 'bp_before_directory_members_tabs' );
}

/**
 * Template tag to wrap all Legacy actions that was used
 * after the members directory content
 *
 * @since 1.0.0
 */
function bp_nouveau_after_members_directory_content() {
	/**
	 * Fires and displays the members content.
	 *
	 * @since 1.1.0 (BuddyPress)
	 */
	do_action( 'bp_directory_members_content' );

	/**
	 * Fires after the display of the members content.
	 *
	 * @since 1.1.0 (BuddyPress)
	 */
	do_action( 'bp_after_directory_members_content' );

	/**
	 * Fires after the display of the members.
	 *
	 * @since 1.1.0 (BuddyPress)
	 */
	do_action( 'bp_after_directory_members' );

	/**
	 * Fires at the bottom of the members directory template file.
	 *
	 * @since 1.5.0 (BuddyPress)
	 */
	do_action( 'bp_after_directory_members_page' );
}

/**
 * Output the action buttons for the displayed user profile
 *
 * @since 1.0.0
 */
function bp_nouveau_member_header_buttons() {
	echo join( ' ', bp_nouveau_get_member_header_buttons() );

	/**
	 * Fires in the member header actions section.
	 *
	 * @since 1.2.6 (BuddyPress)
	 */
	do_action( 'bp_member_header_actions' );
}

	/**
	 * Get the action buttons for the displayed user profile
	 *
	 * @since 1.0.0
	 */
	function bp_nouveau_get_member_header_buttons() {
		// Not really sure why BP Legacy needed to do this...
		if ( is_admin() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return;
		}

		$buttons = array();
		$user_id = bp_displayed_user_id();

		if ( ! $user_id ) {
			return $buttons;
		}

		if ( bp_is_active( 'friends' ) ) {
			/**
			 * This filter workaround is waiting for a core adaptation
			 * so that we can directly get the friends button arguments
			 * instead of the button.
			 * @see https://buddypress.trac.wordpress.org/ticket/7126
			 */
			add_filter( 'bp_get_add_friend_button', 'bp_nouveau_members_catch_button_args', 100, 1 );

			bp_get_add_friend_button();

			remove_filter( 'bp_get_add_friend_button', 'bp_nouveau_members_catch_button_args', 100, 1 );

			if ( ! empty( bp_nouveau()->members->button_args ) ) {
				$buttons['member_profile_friendship'] = wp_parse_args( array(
					'id'       => 'member_profile_friendship',
					'position' => 5,
				), bp_nouveau()->members->button_args );

				unset( bp_nouveau()->members->button_args );
			}
		}

		if ( bp_is_active( 'activity' ) && bp_activity_do_mentions() ) {
			/**
			 * This filter workaround is waiting for a core adaptation
			 * so that we can directly get the public message button arguments
			 * instead of the button.
			 * @see https://buddypress.trac.wordpress.org/ticket/7126
			 */
			add_filter( 'bp_get_send_public_message_button', 'bp_nouveau_members_catch_button_args', 100, 1 );

			bp_get_send_public_message_button();

			remove_filter( 'bp_get_send_public_message_button', 'bp_nouveau_members_catch_button_args', 100, 1 );

			if ( ! empty( bp_nouveau()->members->button_args ) ) {
				$buttons['public_message'] = wp_parse_args( array(
					'position' => 15,
				), bp_nouveau()->members->button_args );

				unset( bp_nouveau()->members->button_args );
			}
		}

		if ( bp_is_active( 'messages' ) ) {
			/**
			 * This filter workaround is waiting for a core adaptation
			 * so that we can directly get the private messages button arguments
			 * instead of the button.
			 * @see https://buddypress.trac.wordpress.org/ticket/7126
			 */
			add_filter( 'bp_get_send_message_button_args', 'bp_nouveau_members_catch_button_args', 100, 1 );

			bp_get_send_message_button();

			remove_filter( 'bp_get_send_message_button_args', 'bp_nouveau_members_catch_button_args', 100, 1 );

			if ( ! empty( bp_nouveau()->members->button_args ) ) {
				$buttons['private_message'] = wp_parse_args( array(
					'position'  => 25,
					'link_href' => esc_url( trailingslashit( bp_loggedin_user_domain() . bp_get_messages_slug() ) . '#compose?r=' . bp_core_get_username( $user_id ) ),
				), bp_nouveau()->members->button_args );

				unset( bp_nouveau()->members->button_args );
			}
		}

		/**
		 * Filter here to add your buttons, use the position argument to choose where to insert it.
		 *
		 * @since 1.0.0
		 *
		 * @param array $buttons The list of buttons.
		 * @param int   $user_id The displayed user ID.
		 */
		$buttons_group = apply_filters( 'bp_nouveau_get_member_header_buttons', $buttons, $user_id );

		if ( empty( $buttons_group ) ) {
			return $buttons;
		}

		bp_nouveau()->members->profile_buttons = new BP_Buttons_Group( $buttons_group );

		$return = bp_nouveau()->members->profile_buttons->get();

		if ( ! $return ) {
			return array();
		}

		return $return;
	}
