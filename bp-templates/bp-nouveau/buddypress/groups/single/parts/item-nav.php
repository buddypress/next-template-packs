<?php
/**
 * BuddyPress Single Groups item Navigation
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */
?>

<div id="item-nav">
	<div class="item-list-tabs no-ajax" id="object-nav" role="navigation">

		<?php if ( bp_nouveau_has_nav( array( 'object' => 'groups' ) ) ) : ?>

			<ul>

				<?php while ( bp_nouveau_nav_items() ) : bp_nouveau_nav_item(); ?>

					<li id="<?php bp_nouveau_nav_id(); ?>" class="<?php bp_nouveau_nav_classes(); ?>">
						<a href="<?php bp_nouveau_nav_link(); ?>" id="<?php bp_nouveau_nav_link_id(); ?>">
							<?php bp_nouveau_nav_link_text(); ?>

							<?php if ( bp_nouveau_nav_has_count() ) : ?>
								<span><?php bp_nouveau_nav_count(); ?></span>
							<?php endif; ?>
						</a>
					</li>

				<?php endwhile; ?>

				<?php

				/**
				 * Fires after the display of group options navigation.
				 *
				 * @since 1.2.0
				 */
				do_action( 'bp_group_options_nav' ); ?>

			</ul>

		<?php endif ; ?>

	</div>
</div><!-- #item-nav -->
