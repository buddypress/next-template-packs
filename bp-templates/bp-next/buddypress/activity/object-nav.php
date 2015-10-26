<?php
/**
 * BuddyPress Activity Main Navigation
 *
 * @since 1.0.0
 *
 * @package BP Next
 */
?>

<div class="item-list-tabs activity-type-tabs" role="navigation">
	<ul>
		<?php

		/**
		 * Fires before the listing of activity type tabs.
		 *
		 * @since 1.2.0
		 */
		do_action( 'bp_before_activity_type_tab_all' ); ?>

		<li id="activity-all" class="bp-activity-primary-nav" data-scope="all" data-object="activity">
			<a href="<?php bp_activity_directory_permalink(); ?>" title="<?php esc_attr_e( 'The public activity for everyone on this site.', 'bp-next' ); ?>">
				<?php printf( __( 'All Members %s', 'bp-next' ), '<span>' . bp_get_total_member_count() . '</span>' ); ?>
			</a>
		</li>

		<?php if ( is_user_logged_in() ) : ?>

			<?php
			// Favorites unlike others is always active...

			/**
			 * Fires before the listing of favorites activity type tab.
			 *
			 * @since 1.2.0
			 */
			do_action( 'bp_before_activity_type_tab_favorites' ); ?>

			<?php if ( bp_get_total_favorite_count_for_user( bp_loggedin_user_id() ) ) : ?>

				<li id="activity-favorites" class="bp-activity-primary-nav" data-scope="favorites" data-object="activity">
					<a href="<?php echo bp_loggedin_user_domain() . bp_get_activity_slug() . '/favorites/'; ?>" title="<?php esc_attr_e( "The activity I've marked as a favorite.", 'bp-next' ); ?>">
						<?php printf( __( 'My Favorites %s', 'bp-next' ), '<span>' . bp_get_total_favorite_count_for_user( bp_loggedin_user_id() ) . '</span>' ); ?>
					</a>
				</li>

			<?php endif; ?>

			<?php

			/**
			 * Fires before the listing of friends activity type tab.
			 *
			 * @since 1.2.0
			 */
			do_action( 'bp_before_activity_type_tab_friends' ); ?>

			<?php if ( bp_is_active( 'friends' ) ) : ?>

				<?php if ( bp_get_total_friend_count( bp_loggedin_user_id() ) ) : ?>

					<li id="activity-friends" class="bp-activity-primary-nav" data-scope="friends" data-object="activity">
						<a href="<?php echo bp_loggedin_user_domain() . bp_get_activity_slug() . '/' . bp_get_friends_slug() . '/'; ?>" title="<?php esc_attr_e( 'The activity of my friends only.', 'bp-next' ); ?>">
							<?php printf( __( 'My Friends %s', 'bp-next' ), '<span>' . bp_get_total_friend_count( bp_loggedin_user_id() ) . '</span>' ); ?>
						</a>
					</li>

				<?php endif; ?>

			<?php endif; ?>

			<?php

			/**
			 * Fires before the listing of groups activity type tab.
			 *
			 * @since 1.2.0
			 */
			do_action( 'bp_before_activity_type_tab_groups' ); ?>

			<?php if ( bp_is_active( 'groups' ) ) : ?>

				<?php if ( bp_get_total_group_count_for_user( bp_loggedin_user_id() ) ) : ?>

					<li id="activity-groups" class="bp-activity-primary-nav" data-scope-"groups" data-object="activity">
						<a href="<?php echo bp_loggedin_user_domain() . bp_get_activity_slug() . '/' . bp_get_groups_slug() . '/'; ?>" title="<?php esc_attr_e( 'The activity of groups I am a member of.', 'bp-next' ); ?>">
							<?php printf( __( 'My Groups %s', 'bp-next' ), '<span>' . bp_get_total_group_count_for_user( bp_loggedin_user_id() ) . '</span>' ); ?>
						</a>
					</li>

				<?php endif; ?>

			<?php endif; ?>

			<?php if ( bp_activity_do_mentions() ) : ?>

				<?php

				/**
				 * Fires before the listing of mentions activity type tab.
				 *
				 * @since 1.2.0
				 */
				do_action( 'bp_before_activity_type_tab_mentions' ); ?>

				<li id="activity-mentions" class="bp-activity-primary-nav" data-scope="mentions" data-object="activity">
					<a href="<?php echo bp_loggedin_user_domain() . bp_get_activity_slug() . '/mentions/'; ?>" title="<?php esc_attr_e( 'Activity that I have been mentioned in.', 'bp-next' ); ?>">
						<?php _e( 'Mentions', 'bp-next' ); ?>
						<?php if ( bp_get_total_mention_count_for_user( bp_loggedin_user_id() ) ) : ?>
							<strong><span><?php printf( _nx( '%s new', '%s new', bp_get_total_mention_count_for_user( bp_loggedin_user_id() ), 'Number of new activity mentions', 'bp-next' ), bp_get_total_mention_count_for_user( bp_loggedin_user_id() ) ); ?></span></strong>
						<?php endif; ?>
					</a>
				</li>

			<?php endif; ?>

		<?php endif; ?>

		<?php

		/**
		 * Fires after the listing of activity type tabs.
		 *
		 * @since 1.2.0
		 */
		do_action( 'bp_activity_type_tabs' ); ?>
	</ul>
</div><!-- .item-list-tabs -->
