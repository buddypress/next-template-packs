<?php
/**
 * BuddyPress - Users Messages
 *
 * @package BuddyPress
 * @subpackage bp-legacy
 */

?>

<div class="item-list-tabs" id="subnav" role="navigation">
	<ul>

		<?php bp_get_options_nav(); ?>

	</ul>
</div><!-- .item-list-tabs -->

<div class="item-list-tabs no-ajax" id="subsubnav">
	<ul>
		<li class="messages-search" role="search" data-bp-search="messages">
			<?php bp_next_message_search_form(); ?>
		</li>
	</ul>
</div><!-- .item-list-tabs#subsubnav -->

<?php if ( ! in_array( bp_current_action(), array( 'inbox', 'sentbox', 'starred', 'view', 'compose', 'notices' ) ) ) :

	bp_get_template_part( 'members/single/plugins' );

else :

	/**
	 * Fires before the member messages content.
	 *
	 * @since 1.2.0
	 */
	do_action( 'bp_before_member_messages_content' );

	// Load the Private messages UI
	bp_get_template_part( 'assets/messages/index' );

	/**
	 * Fires after the member messages content.
	 *
	 * @since 1.2.0
	 */
	do_action( 'bp_after_member_messages_content' );

endif ;?>
