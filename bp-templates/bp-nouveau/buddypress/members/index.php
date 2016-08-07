<?php
/**
 * BuddyPress Members Directory
 *
 * @package BP Nouveau
 *
 * @since 1.0.0
 */

?>

<div id="buddypress" class="<?php bp_nouveau_buddypress_classes(); ?>">

	<?php bp_nouveau_before_members_directory_content() ?>

	<?php if ( ! bp_nouveau_is_object_nav_in_sidebar() ) : ?>

		<?php bp_get_template_part( 'common/nav/directory-nav' ); ?>

	<?php endif; ?>

	<div class="subnav-filters filters no-ajax" id="subnav-filters">

		<ul class="subnav-search clearfix">

			<?php bp_nouveau_search_form(); ?>

		</ul>

		<?php bp_get_template_part( 'common/filters/directory-filters' ); ?>

	</div>

	<div id="members-dir-list" class="members dir-list" data-bp-list="members">
		<div id="bp-ajax-loader"><?php bp_nouveau_user_feedback( 'directory-members-loading' ) ;?></div>
	</div><!-- #members-dir-list -->

	<?php bp_nouveau_after_members_directory_content() ?>

</div><!-- //.buddypress -->
