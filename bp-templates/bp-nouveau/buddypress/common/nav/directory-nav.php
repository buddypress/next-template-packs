<?php
/**
 * BP Nouveau Component's directory nav template.
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */
?>

<div class="item-list-tabs <?php bp_nouveau_directory_type_tabs_class(); ?>" role="navigation">

	<?php if ( bp_nouveau_directory_has_nav() ) : ?>

		<ul type="list" class="component-navigation <?php bp_nouveau_directory_list_class(); ?>">

			<?php while ( bp_nouveau_directory_nav_items() ) : bp_nouveau_directory_nav_item(); ?>

				<li id="<?php bp_nouveau_directory_nav_id(); ?>" class="<?php bp_nouveau_directory_nav_classes(); ?>" data-bp-scope="<?php bp_nouveau_directory_nav_scope(); ?>" data-bp-object="<?php bp_nouveau_directory_nav_object(); ?>">
					<a href="<?php bp_nouveau_directory_nav_link(); ?>" title="<?php bp_nouveau_directory_nav_title(); ?>">
						<?php bp_nouveau_directory_nav_text(); ?>

						<?php if ( bp_nouveau_directory_nav_has_count() ) : ?>
							<span><?php bp_nouveau_directory_nav_count(); ?></span>
						<?php endif; ?>
					</a>
				</li>

			<?php endwhile; ?>

		</ul><!-- .component-navigation -->

	<?php endif ; ?>

</div><!-- .item-list-tabs -->
