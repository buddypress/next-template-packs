<?php
/**
 * BuddyPress - Members Single Group Invites
 *
 * @package BuddyPress
 * @subpackage bp-nouveau
 */

/**
 * Fires before the display of member group invites content.
 *
 * @since 1.1.0
 */
do_action( 'bp_before_group_invites_content' ); ?>

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

				<?php

				/**
				 * Fires inside the display of a member group invite item.
				 *
				 * @since 1.1.0
				 */
				do_action( 'bp_group_invites_item' ); ?>

				<div class="action">

					<?php

					/**
					 * Fires inside the member group item action markup.
					 *
					 * @since 1.1.0
					 */
					do_action( 'bp_group_invites_item_action' ); ?>

				</div>
			</li>

		<?php endwhile; ?>
	</ul>

<?php else: ?>

	<div id="message" class="info">
		<p><?php _e( 'You have no outstanding group invites.', 'bp-nouveau' ); ?></p>
	</div>

<?php endif;?>

<?php

/**
 * Fires after the display of member group invites content.
 *
 * @since 1.1.0
 */
do_action( 'bp_after_group_invites_content' ); ?>
