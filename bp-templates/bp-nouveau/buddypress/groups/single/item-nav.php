<?php
/**
 * BuddyPress Single Groups item Navigation
 *
 * @since 1.0.0
 *
 * @package BP Next
 */
?>

<div id="item-nav">
	<div class="item-list-tabs no-ajax" id="object-nav" role="navigation">
		<ul>

			<?php bp_get_options_nav(); ?>

			<?php

			/**
			 * Fires after the display of group options navigation.
			 *
			 * @since 1.2.0
			 */
			do_action( 'bp_group_options_nav' ); ?>

		</ul>
	</div>
</div><!-- #item-nav -->
