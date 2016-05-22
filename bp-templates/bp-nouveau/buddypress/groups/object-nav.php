<?php
/**
 * BuddyPress Groups Main Navigation
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */
?>

<div class="item-list-tabs" role="navigation">
	<ul>
		<li class="selected" id="groups-all" data-bp-scope="all" data-bp-object="groups">
			<a href="<?php bp_groups_directory_permalink(); ?>">
				<?php printf( __( 'All Groups %s', 'bp-nouveau' ), '<span>' . bp_get_total_group_count() . '</span>' ); ?>
			</a>
		</li>

		<?php if ( is_user_logged_in() && bp_get_total_group_count_for_user( bp_loggedin_user_id() ) ) : ?>

			<li id="groups-personal" data-bp-scope="personal" data-bp-object="groups">
				<a href="<?php echo bp_loggedin_user_domain() . bp_get_groups_slug() . '/my-groups/'; ?>">
					<?php printf( __( 'My Groups %s', 'bp-nouveau' ), '<span>' . bp_get_total_group_count_for_user( bp_loggedin_user_id() ) . '</span>' ); ?>
				</a>
			</li>

		<?php endif; ?>

		<?php

		/**
		 * Fires inside the groups directory group filter input.
		 *
		 * @since 1.5.0
		 */
		do_action( 'bp_groups_directory_group_filter' ); ?>

	</ul>
</div><!-- .item-list-tabs -->
