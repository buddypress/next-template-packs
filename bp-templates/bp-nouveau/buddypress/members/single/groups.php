<?php
/**
 * BuddyPress - Users Groups
 *
 * @package BuddyPress
 * @subpackage bp-nouveau
 */

?>

<div class="item-list-tabs no-ajax" id="subnav" role="navigation">
	<ul>

		<?php if ( bp_is_my_profile() ) bp_get_options_nav(); ?>

	</ul>
</div><!-- .item-list-tabs -->

<?php if ( !bp_is_current_action( 'invites' ) ) : ?>

	<div class="item-list-tabs no-ajax" id="subsubnav">
		<ul>
			<li class="member-search" role="search" data-bp-search="groups">
				<?php bp_directory_groups_search_form(); ?>
			</li>
			<li id="groups-order-select" class="last filter">

				<label for="groups-order-by"><span class="bp-screen-reader-text"><?php _e( 'Order By:', 'bp-nouveau' ); ?></span></label>
				<select id="groups-order-by" data-bp-filter="groups">
					<option value="active"><?php _e( 'Last Active', 'bp-nouveau' ); ?></option>
					<option value="popular"><?php _e( 'Most Members', 'bp-nouveau' ); ?></option>
					<option value="newest"><?php _e( 'Newly Created', 'bp-nouveau' ); ?></option>
					<option value="alphabetical"><?php _e( 'Alphabetical', 'bp-nouveau' ); ?></option>

					<?php

					/**
					 * Fires inside the members group order options select input.
					 *
					 * @since 1.2.0
					 */
					do_action( 'bp_member_group_order_options' ); ?>

				</select>
			</li>
		</ul>
	</div><!-- .item-list-tabs#subsubnav -->

<?php endif; ?>

<?php

switch ( bp_current_action() ) :

	// Home/My Groups
	case 'my-groups' :

		/**
		 * Fires before the display of member groups content.
		 *
		 * @since 1.2.0
		 */
		do_action( 'bp_before_member_groups_content' ); ?>

		<div class="groups mygroups" data-bp-list="groups">

			<div id="bp-ajax-loader"><?php esc_html_e( 'Loading the groups you are a member of, please wait.', 'bp-nouveau' ) ;?></div>

		</div>

		<?php

		/**
		 * Fires after the display of member groups content.
		 *
		 * @since 1.2.0
		 */
		do_action( 'bp_after_member_groups_content' );
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
