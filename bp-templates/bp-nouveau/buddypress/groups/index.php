<?php
/**
 * BP Nouveau - Groups Directory
 *
 * @since  1.0.0
 *
 * @package BP Nouveau
 */
?>

<div id="buddypress" class="buddypress">

    <?php bp_nouveau_before_groups_directory_content(); ?>

	<?php bp_nouveau_template_notices(); ?>

	<?php if ( ! bp_nouveau_is_object_nav_in_sidebar() ) : ?>

		<?php bp_get_template_part( 'common/nav/directory-nav' ); ?>

	<?php endif; ?>

	<div class="item-list-tabs" id="subnav" role="navigation">

		<ul type="list" class="subnav clearfix">

			<?php bp_nouveau_search_form(); ?>

		</ul>

		<?php bp_get_template_part( 'common/filters/directory-filters' ); ?>

	</div>

	<div id="groups-dir-list" class="groups dir-list" data-bp-list="groups">
		<div id="bp-ajax-loader"><?php esc_html_e( 'Loading the groups of the community, please wait.', 'bp-nouveau' ) ;?></div>
	</div><!-- #groups-dir-list -->

	<?php bp_nouveau_after_groups_directory_content(); ?>

</div><!-- //.buddypress -->
