<?php
/**
 * BuddyPress Single Members item Navigation
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */
?>
<div id="item-nav">
	<div class="item-list-tabs no-ajax" id="object-nav" role="navigation">
		<ul>

			<?php bp_get_displayed_user_nav(); ?>

			<?php

			/**
			 * Fires after the display of member options navigation.
			 *
			 * @since 1.0.0
			 */
			do_action( 'bp_member_options_nav' ); ?>

		</ul>
	</div>
</div><!-- #item-nav -->
