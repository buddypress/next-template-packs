<?php
/**
 * BuddyPress - Users Notifications
 *
 * @since  1.0.0
 *
 * @package BP Nouveau
 */

?>

<div class="item-list-tabs no-ajax" id="subnav" role="navigation">
	<ul>

		<?php bp_get_template_part( 'members/single/parts/item-subnav' ); ?>

	</ul>
</div>

<?php
switch ( bp_current_action() ) :

	case 'unread' :
	case 'read'   : ?>

		<div class="item-list-tabs no-ajax" id="subsubnav">
			<ul>
				<?php bp_nouveau_search_form(); ?>

				<li id="notifications-filter-select" class="last filter">
					<label for="notifications-filter-by"><span class="bp-screen-reader-text"><?php _e( 'Show:', 'bp-nouveau' ); ?></span></label>
					<select id="notifications-filter-by" data-bp-filter="notifications">

						<?php bp_nouveau_notifications_filters() ;?>

					</select>
				</li>
			</ul>
		</div><!-- .item-list-tabs#subsubnav -->

		<div id="notifications-user-list" class="notifications dir-list" data-bp-list="notifications">
			<div id="bp-ajax-loader"><?php bp_nouveau_user_feedback( 'member-notifications-loading' ) ;?></div>
		</div><!-- #groups-dir-list -->

		<?php
		break;

	// Any other actions.
	default :
		bp_get_template_part( 'members/single/plugins' );
		break;
endswitch;
