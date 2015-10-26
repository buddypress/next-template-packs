<?php
/**
 * BuddyPress Members Main Navigation
 *
 * @since 1.0.0
 *
 * @package BP Next
 */
?>


<div class="item-list-tabs" role="navigation">
	<ul>
		<li id="members-all" class="bp-members-primary-nav" data-scope="all" data-object="members">
			<a href="<?php bp_members_directory_permalink(); ?>">
				<?php printf( __( 'All Members %s', 'bp-next' ), '<span>' . bp_get_total_member_count() . '</span>' ); ?>
			</a>
		</li>

		<?php if ( is_user_logged_in() && bp_is_active( 'friends' ) && bp_get_total_friend_count( bp_loggedin_user_id() ) ) : ?>
			<li id="members-personal" class="bp-members-primary-nav" data-scope="personal" data-object="members">
				<a href="<?php echo bp_loggedin_user_domain() . bp_get_friends_slug() . '/my-friends/'; ?>">
					<?php printf( __( 'My Friends %s', 'bp-next' ), '<span>' . bp_get_total_friend_count( bp_loggedin_user_id() ) . '</span>' ); ?>
				</a>
			</li>
		<?php endif; ?>

		<?php

		/**
		 * Fires inside the members directory member types.
		 *
		 * @since 1.2.0
		 */
		do_action( 'bp_members_directory_member_types' ); ?>

	</ul>
</div><!-- .item-list-tabs -->