<?php
/**
 * BuddyPress Members Directory
 *
 * @package BP Nouveau
 *
 * @since 1.0.0
 */

?>

<div id="buddypress" class="buddypress">

	<?php bp_nouveau_before_members_directory_content() ?>

	<?php if ( ! bp_nouveau_is_object_nav_in_sidebar() ) : ?>

		<?php bp_get_template_part( 'common/nav/directory-nav' ); ?>

	<?php endif; ?>

	<div class="item-list-tabs" id="subnav" role="navigation">

		<ul type="list" class="subnav clearfix">

			<?php bp_nouveau_search_form(); ?>

		</ul>

		<?php bp_get_template_part( 'common/filters/directory-filters' ); ?>

	</div>

	<div id="members-dir-list" class="members dir-list" data-bp-list="members">
		<div id="bp-ajax-loader"><?php esc_html_e( 'Loading the members of your community, please wait.', 'bp-nouveau' ) ;?></div>
	</div><!-- #members-dir-list -->

	<?php bp_nouveau_after_members_directory_content() ?>

</div><!-- //.buddypress -->
