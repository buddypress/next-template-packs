<?php
/**
 * BuddyPress Single Groups Admin Navigation
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */
?>

<div class="bp-navs bp-subnavs group-subnav no-ajax" id="subnav" role="navigation">

	<?php if ( bp_nouveau_has_nav( array( 'object' => 'group_manage' ) ) ) : ?>

		<ul class="subnav">

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

		</ul>

	<?php endif ; ?>

</div><!-- #isubnav -->
