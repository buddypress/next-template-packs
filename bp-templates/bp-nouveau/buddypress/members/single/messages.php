<?php
/**
 * BuddyPress - Users Messages
 *
 * @package BuddyPress
 * @subpackage bp-nouveau
 */

?>

<div class="bp-navs bp-subnavs user-subnav tabbed-links" id="subnav" role="navigation">
	<ul class="button-tabs subnav">

		<?php bp_get_template_part( 'members/single/parts/item-subnav' ); ?>

	</ul>
</div><!-- .bp-navs -->

<?php if ( ! in_array( bp_current_action(), array( 'inbox', 'sentbox', 'starred', 'view', 'compose', 'notices' ) ) ) :

	bp_get_template_part( 'members/single/plugins' );

else :

	bp_nouveau_messages_member_interface();

endif ;?>

