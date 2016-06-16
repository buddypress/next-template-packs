<?php
/**
 * Activity Template tags
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Before Activity's directory content legacy do_action hooks wrapper
 *
 * @since 1.0.0
 */
function bp_nouveau_before_activity_directory_content() {
	/**
	 * Fires at the begining of the templates BP injected content.
	 *
	 * @since 2.3.0 (BuddyPress)
	 */
	do_action( 'bp_before_directory_activity' );

	/**
	 * Fires before the activity directory display content.
	 *
	 * @since 1.2.0 (BuddyPress)
	 */
	do_action( 'bp_before_directory_activity_content' );
}

/**
 * After Activity's directory content legacy do_action hooks wrapper
 *
 * @since 1.0.0
 */
function bp_nouveau_after_activity_directory_content() {
	/**
	 * Fires after the display of the activity list.
	 *
	 * @since 1.5.0
	 */
	do_action( 'bp_after_directory_activity_list' );

	/**
	 * Fires inside and displays the activity directory display content.
	 */
	do_action( 'bp_directory_activity_content' );

	/**
	 * Fires after the activity directory display content.
	 *
	 * @since 1.2.0
	 */
	do_action( 'bp_after_directory_activity_content' );

	/**
	 * Fires after the activity directory listing.
	 *
	 * @since 1.5.0
	 */
	do_action( 'bp_after_directory_activity' );
}

/**
 * Enqueue needed scripts for the Activity Post Form
 *
 * @since 1.0.0
 */
function bp_nouveau_before_activity_post_form() {
	// Enqueue needed script.
	if ( bp_nouveau_current_user_can( 'publish_activity' ) ) {
		wp_enqueue_script( 'bp-nouveau-activity-post-form' );
	}

	do_action( 'bp_before_activity_post_form' );
}

/**
 * Load JS Templates for the Activity Post Form
 *
 * @since 1.0.0
 */
function bp_nouveau_after_activity_post_form() {
	bp_get_template_part( '_accessoires/activity/form' );

	do_action( 'bp_after_activity_post_form' );
}

/**
 * Output the action buttons inside an Activity Loop
 *
 * @since 1.0.0
 */
function bp_nouveau_activity_entry_buttons() {
	echo join( ' ', bp_nouveau_get_activity_entry_buttons() );

	/**
	 * Fires at the end of the activity entry meta data area.
	 *
	 * @since 1.2.0 (BuddyPress)
	 */
	do_action( 'bp_activity_entry_meta' );
}

	/**
	 * Get the action buttons inside an Activity Loop
	 *
	 * @since 1.0.0
	 */
	function bp_nouveau_get_activity_entry_buttons() {
		$buttons = array();

		if ( ! isset( $GLOBALS['activities_template'] ) ) {
			return $buttons;
		}

		$activity_id   = bp_get_activity_id();

		if ( empty( $activity_id ) ) {
			return $buttons;
		}

		/**
		 * The view conversation button and the comment one are sharing
		 * the same id because when display_comments is on stream mode,
		 * it is not possible to comment an activity comment and as we
		 * are updating the links to avoid sorting the activity buttons
		 * for each entry of the loop, it's a convenient way to make
		 * sure the right button will be displayed.
		 */
		if ( bp_get_activity_type() === 'activity_comment' ) {
			$buttons['activity_conversation'] =  array(
				'id'                => 'activity_conversation',
				'position'          => 5,
				'component'         => 'activity',
				'must_be_logged_in' => false,
				'link_href'         => esc_url( bp_get_activity_thread_permalink() ),
				'link_class'        => 'button view bp-secondary-action',
				'link_title'        => esc_attr__( 'View Conversation', 'bp-nouveau' ),
				'link_text'         => esc_html__( 'View Conversation', 'bp-nouveau' ),
			);
		}

		if ( bp_activity_can_comment() ) {
			$buttons['activity_conversation'] =  array(
				'id'                => 'activity_conversation',
				'position'          => 5,
				'component'         => 'activity',
				'must_be_logged_in' => true,
				'link_id'           => 'acomment-comment-' . $activity_id,
				'link_href'         => esc_url( bp_get_activity_comment_link() ),
				'link_class'        => 'button acomment-reply bp-primary-action',
				'link_title'        => esc_attr__( 'Comment', 'bp-nouveau' ),
				'link_text'         => sprintf( '<span class="bp-screen-reader-text">%1$s</span> <span class="comment-count">%2$s</span>', esc_html__( 'Comment', 'bp-nouveau' ), bp_activity_get_comment_count() ),
			);
		}

		if ( bp_activity_can_favorite() ) {
			if ( ! bp_get_activity_is_favorite() ) {
				$fav_args = array(
					'link_href'  => bp_get_activity_favorite_link(),
					'link_class' => 'button fav bp-secondary-action',
					'link_title' => __( 'Mark as Favorite', 'bp-nouveau' ),
					'link_text'    => __( 'Favorite', 'bp-nouveau' ),
				);
			} else {
				$fav_args = array(
					'link_href'  => bp_get_activity_unfavorite_link(),
					'link_class' => 'button unfav bp-secondary-action',
					'link_title' => __( 'Remove Favorite', 'bp-nouveau' ),
					'link_text'    => __( 'Remove Favorite', 'bp-nouveau' ),
				);
			}

			$buttons['activity_favorite'] =  array(
				'id'                => 'activity_favorite',
				'position'          => 15,
				'component'         => 'activity',
				'must_be_logged_in' => true,
				'link_href'         => esc_url( $fav_args['link_href'] ),
				'link_class'        => $fav_args['link_class'],
				'link_title'        => esc_attr( $fav_args['link_title'] ),
				'link_text'         => sprintf( '<span class="bp-screen-reader-text">%1$s</span>', esc_html( $fav_args['link_text'] ) ),
			);
		}

		if ( bp_activity_user_can_delete() ) {
			$delete_args = array();

			/**
			 * As the delete link is filterable we need this workaround
			 * to try to intercept the edits the filter made and build
			 * a button out of it.
			 */
			if ( has_filter( 'bp_get_activity_delete_link' ) ) {
				preg_match( '/<a\s[^>]*>(.*)<\/a>/siU', bp_get_activity_delete_link(), $link );

				if ( ! empty( $link[0] ) && ! empty( $link[1] ) ) {
					$delete_args['link_text'] = $link[1];
					$subject = str_replace( $delete_args['link_text'], '', $link[0] );
				}

				preg_match_all( '/([\w\-]+)=([^"\'> ]+|([\'"]?)(?:[^\3]|\3+)+?\3)/', $subject, $attrs );

				if ( ! empty( $attrs[1] ) && ! empty( $attrs[2] ) ) {
					foreach ( $attrs[1] as $key_attr => $key_value ) {
						$delete_args[ 'link_'. $key_value ] = trim( $attrs[2][$key_attr], '"' );
					}
				}

				$delete_args = wp_parse_args( $delete_args, array(
					'link_href'  => '',
					'link_class' => '',
					'link_rel'   => 'nofollow',
					'link_text'  => '',
					'link_title' => '',
					'link_id'    => '',
				) );
			}

			if ( empty( $delete_args['link_href'] ) ) {
				$delete_args[] = bp_get_activity_delete_url();
				$class = 'delete-activity';

				if ( bp_is_activity_component() && is_numeric( bp_current_action() ) ) {
					$class = 'delete-activity-single';
				}

				$delete_args = array(
					'link_href'  => bp_get_activity_delete_url(),
					'link_class' => 'button item-button bp-secondary-action ' . $class . ' confirm',
					'link_rel'   => 'nofollow',
					'link_text'  => __( 'Delete', 'bp_nouveau' ),
					'link_title' => __( 'Delete', 'bp_nouveau' ),
					'link_id'    => '',
				);
			}

			$buttons['activity_delete'] = array(
				'id'                => 'activity_delete',
				'position'          => 35,
				'component'         => 'activity',
				'must_be_logged_in' => true,
				'link_id'           => esc_attr( $delete_args['link_id'] ),
				'link_href'         => esc_url( $delete_args['link_href'] ),
				'link_class'        => $delete_args['link_class'],
				'link_title'        => esc_attr( $delete_args['link_title'] ),
				'link_text'         => sprintf( '<span class="bp-screen-reader-text">%s</span>', esc_html( $delete_args['link_text'] ) ),
			);
		}

		/**
		 * Filter here to add your buttons, use the position argument to choose where to insert it.
		 *
		 * @since 1.0.0
		 *
		 * @param array the list of buttons
		 * @param int   the current activity ID.
		 */
		$buttons_group = apply_filters( 'bp_nouveau_get_activity_entry_buttons', $buttons, $activity_id );

		if ( empty( $buttons_group ) ) {
			return $buttons;
		}

		// It's the first entry of the loop, so build the Group and sort it
		if ( ! isset( bp_nouveau()->activity->entry_buttons ) || false === is_a( bp_nouveau()->activity->entry_buttons, 'BP_Buttons_Group' ) ) {
			$sort = true;
			bp_nouveau()->activity->entry_buttons = new BP_Buttons_Group( $buttons_group );

		// It's not the first entry, the order is set, we simply need to update the Buttons Group
		} else {
			$sort = false;
			bp_nouveau()->activity->entry_buttons->update( $buttons_group );
		}

		$return = bp_nouveau()->activity->entry_buttons->get( $sort );

		if ( ! $return ) {
			return array();
		}

		return $return;
	}
