<?php
/**
 * BuddyPress - Groups Members
 *
 * @since  1.0.0
 *
 * @package BP Nouveau
 */
?>

<div class="item-list-tabs" id="subnav" role="navigation">
	<ul class="subnav clearfix">

		<?php bp_get_template_part( 'common/search/object-search-form' ); ?>

		<?php bp_groups_members_filter(); ?>

	</ul>
</div>

<div id="members-group-list" class="group_members dir-list" data-bp-list="group_members">

	<div id="bp-ajax-loader"><?php esc_html_e( 'Requesting the group members, please wait.', 'bp-nouveau' ) ;?></div>

</div><!-- .group_members.dir-list -->
