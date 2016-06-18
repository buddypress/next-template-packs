<?php
/**
 * BuddyPress - Groups Cover Image Header.
 *
 * @package BuddyPress
 * @subpackage bp-nouveau
 */

/**
 * Fires before the display of a group's header.
 *
 * @since 1.2.0
 */
do_action( 'bp_before_group_header' ); ?>

<div id="cover-image-container">
	<a id="header-cover-image" href="<?php bp_group_permalink(); ?>"></a>

	<div id="item-header-cover-image">
		<?php if ( ! bp_disable_group_avatar_uploads() ) : ?>
			<div id="item-header-avatar">
				<a href="<?php bp_group_permalink(); ?>" title="<?php bp_group_name(); ?>">

					<?php bp_group_avatar(); ?>

				</a>
			</div><!-- #item-header-avatar -->
		<?php endif; ?>

		<div id="item-header-content">

			<div id="item-buttons"><?php

				/**
				 * Fires in the group header actions section.
				 *
				 * @since 1.2.6
				 */
				do_action( 'bp_group_header_actions' ); ?></div><!-- #item-buttons -->

			<span class="highlight"><?php bp_group_type(); ?></span>
			<span class="activity"><?php printf( __( 'active %s', 'bp-nouveau' ), bp_get_group_last_active() ); ?></span>

			<?php

			/**
			 * Fires before the display of the group's header meta.
			 *
			 * @since 1.2.0
			 */
			do_action( 'bp_before_group_header_meta' ); ?>

			<?php if ( bp_nouveau_group_has_meta() ): ?>
				<div id="item-meta">

					<?php bp_nouveau_group_meta(); ?>

				</div><!-- #item-meta -->
			<?php endif; ?>

		</div><!-- #item-header-content -->

		<div id="item-actions">

			<?php if ( bp_group_is_visible() ) : ?>

				<h3><?php _e( 'Group Admins', 'bp-nouveau' ); ?></h3>

				<?php bp_group_list_admins();

				/**
				 * Fires after the display of the group's administrators.
				 *
				 * @since 1.1.0
				 */
				do_action( 'bp_after_group_menu_admins' );

				if ( bp_group_has_moderators() ) :

					/**
					 * Fires before the display of the group's moderators, if there are any.
					 *
					 * @since 1.1.0
					 */
					do_action( 'bp_before_group_menu_mods' ); ?>

					<h3><?php _e( 'Group Mods' , 'bp-nouveau' ); ?></h3>

					<?php bp_group_list_mods();

					/**
					 * Fires after the display of the group's moderators, if there are any.
					 *
					 * @since 1.1.0
					 */
					do_action( 'bp_after_group_menu_mods' );

				endif;

			endif; ?>

		</div><!-- #item-actions -->

	</div><!-- #item-header-cover-image -->
</div><!-- #cover-image-container -->

<?php

/**
 * Fires after the display of a group's header.
 *
 * @since 1.2.0
 */
do_action( 'bp_after_group_header' );

bp_nouveau_template_notices(); ?>
