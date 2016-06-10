bp_adminbar_account_menu<?php
/**
 * BuddyPress - Blogs
 *
 * @package BuddyPress
 * @subpackage bp-nouveau
 */
?>

<div id="buddypress" class="buddypress">

<?php
/**
 * Fires at the begining of the templates BP injected content.
 *
 * @since 2.3.0
 */
	do_action( 'bp_before_directory_blogs_page' ); ?>


	<?php

	/**
	 * Fires before the display of the blogs.
	 *
	 * @since 1.5.0
	 */
	do_action( 'bp_before_directory_blogs' ); ?>

	<?php

	/**
	 * Fires before the display of the blogs listing content.
	 *
	 * @since 1.1.0
	 */
	do_action( 'bp_before_directory_blogs_content' ); ?>

	<div id="blog-dir-search" class="dir-search" role="search">
		<?php bp_directory_blogs_search_form(); ?>
	</div><!-- #blog-dir-search -->

	<?php

	/**
	 * Fires before the display of the blogs list tabs.
	 *
	 * @since 2.3.0
	 */
	do_action( 'bp_before_directory_blogs_tabs' ); ?>

	<form action="" method="post" id="blogs-directory-form" class="dir-form">

		<?php bp_get_template_part( 'common/nav/object-nav' ); ?>

		<div class="item-list-tabs" id="subnav" role="navigation">
			<menu type="list" class="subnav clearfix">

				<?php

				/**
				 * Fires inside the unordered list displaying blog sub-types.
				 *
				 * @since 1.5.0
				 */
				do_action( 'bp_blogs_directory_blog_sub_types' ); ?>
			</menu>

			<?php bp_get_template_part( 'common/filters/component-filters' ); ?>

		</div>

		<div id="blogs-dir-list" class="blogs dir-list">

			<?php bp_get_template_part( 'blogs/blogs-loop' ); ?>

		</div><!-- #blogs-dir-list -->

		<?php

		/**
		 * Fires inside and displays the blogs content.
		 *
		 * @since 1.1.0
		 */
		do_action( 'bp_directory_blogs_content' ); ?>

		<?php wp_nonce_field( 'directory_blogs', '_wpnonce-blogs-filter' ); ?>

		<?php

		/**
		 * Fires after the display of the blogs listing content.
		 *
		 * @since 1.1.0
		 */
		do_action( 'bp_after_directory_blogs_content' ); ?>

	</form><!-- #blogs-directory-form -->

	<?php

	/**
	 * Fires at the bottom of the blogs directory template file.
	 *
	 * @since 1.5.0
	 */
	do_action( 'bp_after_directory_blogs' ); ?>



	<?php

	/**
	 * Fires at the bottom of the blogs directory template file.
	 *
	 * @since 2.3.0
	 */
	do_action( 'bp_after_directory_blogs_page' ); ?>

</div><!-- //.buddypress -->
