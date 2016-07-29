<?php
/**
 * BuddyPress - Users Settings
 *
 * @package BuddyPress
 * @subpackage bp-nouveau
 */

?>

<?php if ( bp_core_can_edit_settings() ) : ?>

	<div class="bp-navs bp-subnavs user-subnav no-ajax dark" id="subnav" role="navigation">
		<ul class="subnav">

			<?php bp_get_template_part( 'members/single/parts/item-subnav' ); ?>

		</ul>
	</div>

<?php endif;

switch ( bp_current_action() ) :
	case 'notifications'  :
		bp_get_template_part( 'members/single/settings/notifications'  );
		break;
	case 'capabilities'   :
		bp_get_template_part( 'members/single/settings/capabilities'   );
		break;
	case 'delete-account' :
		bp_get_template_part( 'members/single/settings/delete-account' );
		break;
	case 'general'        :
		bp_get_template_part( 'members/single/settings/general'        );
		break;
	case 'profile'        :
		bp_get_template_part( 'members/single/settings/profile'        );
		break;
	case 'invites'        :
		bp_get_template_part( 'members/single/settings/group-invites'  );
		break;
	default:
		bp_get_template_part( 'members/single/plugins'                 );
		break;
endswitch;
