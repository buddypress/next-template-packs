<?php
/**
 * BuddyPress - Users Notifications
 *
 * @since  1.0.0
 *
 * @package BP Nouveau
 */

?>

<div class="bp-navs bp-subnavs user-subnav tabbed-links no-ajax" id="subnav" role="navigation">
	<ul class="button-tabs subnav">

		<?php bp_get_template_part( 'members/single/parts/item-subnav' ); ?>

	</ul>
</div>

<?php
switch ( bp_current_action() ) :

	case 'unread' :
	case 'read'   : ?>


			<div class="subnav-filters filters">

				<ul>
					<?php bp_nouveau_search_form(); ?>
				</ul>

				<?php bp_get_template_part('common/filters/user-screens-filters'); ?>
			</div><!-- .subnav-filters-->


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
