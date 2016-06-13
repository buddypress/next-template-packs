<?php
/**
 * BuddyPress - Blogs
 *
 * @package BuddyPress
 * @subpackage bp-nouveau
 */
?>

<div id="buddypress" class="buddypress">

	<?php bp_nouveau_before_blogs_directory_content() ;?>

	<?php if ( ! bp_nouveau_is_object_nav_in_sidebar() ) : ?>

		<?php bp_get_template_part( 'common/nav/directory-nav' ); ?>

	<?php endif; ?>

	<div class="item-list-tabs" id="subnav" role="navigation">
		<ul type="list" class="subnav clearfix">
			<?php bp_get_template_part( 'common/search/dir-search-form' ); ?>
			<?php

			/**
			 * Fires inside the unordered list displaying blog sub-types.
			 *
			 * @since 1.5.0
			 */
			do_action( 'bp_blogs_directory_blog_sub_types' ); ?>
		</ul>

		<?php bp_get_template_part( 'common/filters/directory-filters' ); ?>

	</div>

	<div id="blogs-dir-list" class="blogs dir-list" data-bp-list="blogs">
		<div id="bp-ajax-loader"><?php esc_html_e( 'Loading the sites of the network, please wait.', 'bp-nouveau' ) ;?></div>
	</div><!-- #blogs-dir-list -->

	<?php bp_nouveau_after_blogs_directory_content() ;?>

</div><!-- //.buddypress -->
