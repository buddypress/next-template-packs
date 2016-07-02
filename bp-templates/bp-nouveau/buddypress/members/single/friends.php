<?php
/**
 * BuddyPress - Users Friends
 *
 * @since  1.0.0
 *
 * @package BP Nouveau
 */

?>

<div class="item-list-tabs no-ajax" id="subnav" role="navigation">
	<ul>
		<?php if ( bp_is_my_profile() ) bp_get_options_nav(); ?>

		<?php if ( !bp_is_current_action( 'requests' ) ) : ?>

			<li id="members-order-select" class="last filter">

				<label for="members-friends"><?php _e( 'Order By:', 'bp-nouveau' ); ?></label>
				<select id="members-friends">
					<option value="active"><?php _e( 'Last Active', 'bp-nouveau' ); ?></option>
					<option value="newest"><?php _e( 'Newest Registered', 'bp-nouveau' ); ?></option>
					<option value="alphabetical"><?php _e( 'Alphabetical', 'bp-nouveau' ); ?></option>

					<?php

					/**
					 * Fires inside the members friends order options select input.
					 *
					 * @since 2.0.0
					 */
					do_action( 'bp_member_friends_order_options' ); ?>

				</select>
			</li>

		<?php endif; ?>

	</ul>
</div>

<?php
switch ( bp_current_action() ) :

	// Home/My Friends
	case 'my-friends' :

		bp_nouveau_member_hook( 'before', 'friends_content' ); ?>

		<div class="members friends">

			<?php bp_get_template_part( 'members/members-loop' ) ?>

		</div><!-- .members.friends -->

		<?php bp_nouveau_member_hook( 'after', 'friends_content' );
		break;

	case 'requests' :
		bp_get_template_part( 'members/single/friends/requests' );
		break;

	// Any other
	default :
		bp_get_template_part( 'members/single/plugins' );
		break;
endswitch;
