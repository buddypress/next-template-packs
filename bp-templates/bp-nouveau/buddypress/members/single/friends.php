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

				<label for="members-friends">
					<span class="bp-screen-reader-text"><?php esc_html_e( 'Order By:', 'bp-nouveau' ); ?></span>
				</label>
				<select id="members-friends" data-bp-filter="members">

					<?php bp_nouveau_filter_options(); ?>

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

		<div class="members friends" data-bp-list="members">

			<div id="bp-ajax-loader"><?php bp_nouveau_user_feedback( 'member-friends-loading' ) ;?></div>

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
