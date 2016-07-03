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

	<div id="pag-top" class="pagination">

		<div class="pag-count" id="member-count-top">

			<?php bp_members_pagination_count(); ?>

		</div>

		<div class="pagination-links" id="member-pag-top">

			<?php bp_members_pagination_links(); ?>

		</div>

	</div>

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

				<div class="action">

					<?php bp_nouveau_members_loop_buttons(); ?>

				</div>
			</li>

		<?php endwhile; ?>

	</ul>

	<?php bp_nouveau_group_hook( 'after', 'members_list' ); ?>

	<div id="pag-bottom" class="pagination">

		<div class="pag-count" id="member-count-bottom">

			<?php bp_members_pagination_count(); ?>

		</div>

		<div class="pagination-links" id="member-pag-bottom">

			<?php bp_members_pagination_links(); ?>

		</div>

	</div>

	<?php bp_nouveau_group_hook( 'after', 'members_content' ); ?>

<?php else: ?>

	<div id="message" class="info">
		<p><?php _e( 'No members were found.', 'bp-nouveau' ); ?></p>
	</div>

<?php endif; ?>
