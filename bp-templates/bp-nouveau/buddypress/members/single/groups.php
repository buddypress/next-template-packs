<?php
/**
 * BuddyPress - Users Groups
 *
 * @since  1.0.0
 *
 * @package BP Nouveau
 */

?>

<div class="item-list-tabs no-ajax" id="subnav" role="navigation">
	<ul>

		<?php if ( bp_is_my_profile() ) bp_get_options_nav(); ?>

	</ul>
</div><!-- .item-list-tabs -->

<?php if ( ! bp_is_current_action( 'invites' ) ) : ?>

	<div class="item-list-tabs no-ajax" id="subsubnav">
		<ul>
			<?php bp_nouveau_search_form(); ?>

			<li id="groups-order-select" class="last filter">

				<label for="groups-order-by"><span class="bp-screen-reader-text"><?php _e( 'Order By:', 'bp-nouveau' ); ?></span></label>
				<select id="groups-order-by" data-bp-filter="groups">

					<?php bp_nouveau_filter_options() ;?>

				</select>
			</li>
		</ul>
	</div><!-- .item-list-tabs#subsubnav -->

<?php endif; ?>

<?php

switch ( bp_current_action() ) :

	// Home/My Groups
	case 'my-groups' :

		bp_nouveau_member_hook( 'before', 'groups_content' ); ?>

		<div class="groups mygroups" data-bp-list="groups">

			<div id="bp-ajax-loader"><?php bp_nouveau_user_feedback( 'member-groups-loading' ) ;?></div>

		</div>

		<?php bp_nouveau_member_hook( 'after', 'groups_content' );
		break;

	// Group Invitations
	case 'invites' :
		bp_get_template_part( 'members/single/groups/invites' );
		break;

	// Any other
	default :
		bp_get_template_part( 'members/single/plugins' );
		break;
endswitch;
