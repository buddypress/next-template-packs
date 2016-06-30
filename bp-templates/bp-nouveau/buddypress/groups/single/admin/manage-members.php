<?php
/**
 * BP Nouveau Group's manage members template.
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */
?>

<div class="bp-widget">
	<h4><?php _e( 'Administrators', 'bp-nouveau' ); ?></h4>

	<?php if ( bp_has_members( '&include='. bp_group_admin_ids() ) ) : ?>

	<ul id="admins-list" class="item-list single-line">

		<?php while ( bp_members() ) : bp_the_member(); ?>
		<li>
			<?php echo bp_core_fetch_avatar( array( 'item_id' => bp_get_member_user_id(), 'type' => 'thumb', 'width' => 30, 'height' => 30, 'alt' => sprintf( __( 'Profile picture of %s', 'bp-nouveau' ), bp_get_member_name() ) ) ); ?>
			<h5>
				<a href="<?php bp_member_permalink(); ?>"> <?php bp_member_name(); ?></a>
				<?php if ( count( bp_group_admin_ids( false, 'array' ) ) > 1 ) : ?>
				<span class="small">
					<a class="button confirm admin-demote-to-member" href="<?php bp_group_member_demote_link( bp_get_member_user_id() ); ?>"><?php _e( 'Demote to Member', 'bp-nouveau' ); ?></a>
				</span>
				<?php endif; ?>
			</h5>
		</li>
		<?php endwhile; ?>

	</ul>

	<?php endif; ?>

</div>

<?php if ( bp_group_has_moderators() ) : ?>
	<div class="bp-widget">
		<h4><?php _e( 'Moderators', 'bp-nouveau' ); ?></h4>

		<?php if ( bp_has_members( '&include=' . bp_group_mod_ids() ) ) : ?>
			<ul id="mods-list" class="item-list single-line">

				<?php while ( bp_members() ) : bp_the_member(); ?>
				<li>
					<?php echo bp_core_fetch_avatar( array( 'item_id' => bp_get_member_user_id(), 'type' => 'thumb', 'width' => 30, 'height' => 30, 'alt' => sprintf( __( 'Profile picture of %s', 'bp-nouveau' ), bp_get_member_name() ) ) ); ?>
					<h5>
						<a href="<?php bp_member_permalink(); ?>"> <?php bp_member_name(); ?></a>
						<span class="small">
							<a href="<?php bp_group_member_promote_admin_link( array( 'user_id' => bp_get_member_user_id() ) ); ?>" class="button confirm mod-promote-to-admin" title="<?php esc_attr_e( 'Promote to Admin', 'bp-nouveau' ); ?>"><?php _e( 'Promote to Admin', 'bp-nouveau' ); ?></a>
							<a class="button confirm mod-demote-to-member" href="<?php bp_group_member_demote_link( bp_get_member_user_id() ); ?>"><?php _e( 'Demote to Member', 'bp-nouveau' ); ?></a>
						</span>
					</h5>
				</li>
				<?php endwhile; ?>

			</ul>

		<?php endif; ?>
	</div>
<?php endif ?>


<div class="bp-widget">
	<h4><?php _e("Members", "buddypress"); ?></h4>

	<?php if ( bp_group_has_members( 'per_page=15&exclude_banned=0' ) ) : ?>

		<?php if ( bp_group_member_needs_pagination() ) : ?>

			<div class="pagination no-ajax">

				<div id="member-count" class="pag-count">
					<?php bp_group_member_pagination_count(); ?>
				</div>

				<div id="member-admin-pagination" class="pagination-links">
					<?php bp_group_member_admin_pagination(); ?>
				</div>

			</div>

		<?php endif; ?>

		<ul id="members-list" class="item-list single-line">
			<?php while ( bp_group_members() ) : bp_group_the_member(); ?>

				<li class="<?php bp_group_member_css_class(); ?>">
					<?php bp_group_member_avatar_mini(); ?>

					<h5>
						<?php bp_group_member_link(); ?>

						<?php if ( bp_get_group_member_is_banned() ) _e( '(banned)', 'bp-nouveau' ); ?>

						<span class="small">

						<?php if ( bp_get_group_member_is_banned() ) : ?>

							<a href="<?php bp_group_member_unban_link(); ?>" class="button confirm member-unban" title="<?php esc_attr_e( 'Unban this member', 'bp-nouveau' ); ?>"><?php _e( 'Remove Ban', 'bp-nouveau' ); ?></a>

						<?php else : ?>

							<a href="<?php bp_group_member_ban_link(); ?>" class="button confirm member-ban" title="<?php esc_attr_e( 'Kick and ban this member', 'bp-nouveau' ); ?>"><?php _e( 'Kick &amp; Ban', 'bp-nouveau' ); ?></a>
							<a href="<?php bp_group_member_promote_mod_link(); ?>" class="button confirm member-promote-to-mod" title="<?php esc_attr_e( 'Promote to Mod', 'bp-nouveau' ); ?>"><?php _e( 'Promote to Mod', 'bp-nouveau' ); ?></a>
							<a href="<?php bp_group_member_promote_admin_link(); ?>" class="button confirm member-promote-to-admin" title="<?php esc_attr_e( 'Promote to Admin', 'bp-nouveau' ); ?>"><?php _e( 'Promote to Admin', 'bp-nouveau' ); ?></a>

						<?php endif; ?>

							<a href="<?php bp_group_member_remove_link(); ?>" class="button confirm" title="<?php esc_attr_e( 'Remove this member', 'bp-nouveau' ); ?>"><?php _e( 'Remove from group', 'bp-nouveau' ); ?></a>

							<?php

							/**
							 * Fires inside the display of a member admin item in group management area.
							 *
							 * @since 1.1.0
							 */
							do_action( 'bp_group_manage_members_admin_item' ); ?>

						</span>
					</h5>
				</li>

			<?php endwhile; ?>
		</ul>

	<?php else: ?>

		<div id="message" class="info">
			<p><?php _e( 'This group has no members.', 'bp-nouveau' ); ?></p>
		</div>

	<?php endif; ?>

</div>
