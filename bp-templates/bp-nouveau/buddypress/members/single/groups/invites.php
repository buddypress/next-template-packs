<?php
/**
 * BuddyPress - Members Single Group Invites
 *
 * @since  1.0.0
 *
 * @package BP Nouveau
 */

bp_nouveau_group_hook( 'before', 'invites_content' ); ?>

<?php if ( bp_has_groups( 'type=invites&user_id=' . bp_loggedin_user_id() ) ) : ?>

	<ul id="group-list" class="invites item-list" data-bp-list="groups_invites">

		<?php while ( bp_groups() ) : bp_the_group(); ?>

			<li data-bp-item-id="<?php bp_group_id(); ?>" data-bp-item-component="groups">
				<?php if ( ! bp_disable_group_avatar_uploads() ) : ?>
					<div class="item-avatar">
						<a href="<?php bp_group_permalink(); ?>"><?php bp_group_avatar( 'type=thumb&width=50&height=50' ); ?></a>
					</div>
				<?php endif; ?>

				<h4><a href="<?php bp_group_permalink(); ?>"><?php bp_group_name(); ?></a><span class="small"> - <?php printf( _nx( '%d member', '%d members', bp_get_group_total_members( false ),'Group member count', 'bp-nouveau' ), bp_get_group_total_members( false )  ); ?></span></h4>

				<p class="desc">
					<?php bp_group_description_excerpt(); ?>
				</p>

				<?php bp_nouveau_group_hook( '', 'invites_item' ); ?>

				<div class="action">

					<?php bp_nouveau_groups_invite_buttons(); ?>

				</div>
			</li>

		<?php endwhile; ?>
	</ul>

<?php else: ?>

	<div id="message" class="info">
		<p><?php _e( 'You have no outstanding group invites.', 'bp-nouveau' ); ?></p>
	</div>

<?php endif;?>

<?php bp_nouveau_group_hook( 'after', 'invites_content' );
