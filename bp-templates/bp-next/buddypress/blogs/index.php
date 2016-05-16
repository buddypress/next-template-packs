bp_adminbar_account_menu<?php
/**
 * BuddyPress - Blogs
 *
 * @package BuddyPress
 * @subpackage bp-legacy
 */

/**
 * Fires at the top of the blogs directory template file.
 *
 * @since 2.3.0
 */
do_action( 'bp_before_directory_blogs_page' ); ?>

<div id="buddypress">

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

		<?php bp_get_template_part( 'common/object-nav' ); ?>

		<div class="item-list-tabs" id="subnav" role="navigation">
			<ul>

				<?php

				/**
				 * Fires inside the unordered list displaying blog sub-types.
				 *
				 * @since 1.5.0
				 */
				do_action( 'bp_blogs_directory_blog_sub_types' ); ?>

				<li id="blogs-order-select" class="last filter">

					<label for="blogs-order-by"><?php _e( 'Order By:', 'bp-next' ); ?></label>
					<select id="blogs-order-by">
						<option value="active"><?php _e( 'Last Active', 'bp-next' ); ?></option>
						<option value="newest"><?php _e( 'Newest', 'bp-next' ); ?></option>
						<option value="alphabetical"><?php _e( 'Alphabetical', 'bp-next' ); ?></option>

						<?php

						/**
						 * Fires inside the select input listing blogs orderby options.
						 *
						 * @since 1.2.0
						 */
						do_action( 'bp_blogs_directory_order_options' ); ?>

					</select>
				</li>
			</ul>
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

</div>

<?php

/**
 * Fires at the bottom of the blogs directory template file.
 *
 * @since 2.3.0
 */
do_action( 'bp_after_directory_blogs_page' );
