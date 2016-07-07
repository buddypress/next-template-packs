<?php
/**
 * BuddyPress - Members Read Notifications
 *
 * @package BuddyPress
 * @subpackage bp-nouveau
 */

?>

<?php if ( bp_has_notifications() ) : ?>

	<?php bp_nouveau_pagination( 'top' ); ?>

	<?php bp_get_template_part( 'members/single/notifications/notifications-loop' ); ?>

	<?php bp_nouveau_pagination( 'bottom' ); ?>

<?php else :

	bp_nouveau_user_feedback( 'member-notifications-none' );

endif;
