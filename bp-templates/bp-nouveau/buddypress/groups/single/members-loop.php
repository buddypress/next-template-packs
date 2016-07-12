<?php
/**
 * Group Members Loop template
 *
 * @since  1.0.0
 *
 * @package BP Nouveau
 */
?>

<?php if ( bp_group_has_members( bp_ajax_querystring( 'group_members' ) ) ) : ?>

	<?php bp_nouveau_group_hook( 'before', 'members_content' ); ?>

	<?php bp_nouveau_pagination( 'top' ) ; ?>

	<?php bp_nouveau_group_hook( 'before', 'members_list' ); ?>

	<ul id="member-list" class="item-list bp-list">

		<?php while ( bp_group_members() ) : bp_group_the_member(); ?>

			<li data-bp-item-id="<?php echo esc_attr( bp_get_group_member_id() ); ?>" data-bp-item-component="members">
				<a href="<?php bp_group_member_domain(); ?>">

					<?php bp_group_member_avatar_thumb(); ?>

				</a>

				<h5><?php bp_group_member_link(); ?></h5>
				<span class="activity"><?php bp_group_member_joined_since(); ?></span>

				<?php bp_nouveau_group_hook( '', 'members_list_item' ); ?>

				<?php bp_nouveau_members_loop_buttons(); ?>
			</li>

		<?php endwhile; ?>

	</ul>

	<?php bp_nouveau_group_hook( 'after', 'members_list' ); ?>

	<?php bp_nouveau_pagination( 'bottom' ) ; ?>

	<?php bp_nouveau_group_hook( 'after', 'members_content' ); ?>

<?php else:

	bp_nouveau_user_feedback( 'group-members-none' );

endif; ?>
