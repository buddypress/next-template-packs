<?php
/**
 * BP Nouveau - Groups Directory
 *
 * @since  1.0.0
 *
 * @package BP Nouveau
 */
?>

<div id="buddypress" class="<?php bp_nouveau_buddypress_classes(); ?>">

	<?php bp_nouveau_before_groups_directory_content(); ?>

	<?php bp_nouveau_template_notices(); ?>

	<?php if ( ! bp_nouveau_is_object_nav_in_sidebar() ) : ?>

		<?php bp_get_template_part( 'common/nav/directory-nav' ); ?>

	<?php endif; ?>

	<div class="subnav-filters filters no-ajax" id="subnav-filters">

		<ul class="subnav-search clearfix">

			<?php bp_nouveau_search_form(); ?>

		</ul>

		<?php bp_get_template_part( 'common/filters/directory-filters' ); ?>

	</div>

	<div id="groups-dir-list" class="groups dir-list" data-bp-list="groups">
		<div id="bp-ajax-loader"><?php bp_nouveau_user_feedback( 'directory-groups-loading' ) ;?></div>
	</div><!-- #groups-dir-list -->

	<?php bp_nouveau_after_groups_directory_content(); ?>

</div><!-- //.buddypress -->
