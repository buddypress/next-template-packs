<?php
/**
 * BuddyPress - Members Loop
 *
 * Querystring is set via AJAX in _inc/ajax.php - bp_legacy_theme_object_filter()
 *
 * @since  1.0.0
 *
 * @package BP Nouveau
 */

bp_nouveau_before_loop(); ?>

<?php if ( bp_get_current_member_type() ) : ?>
	<p class="current-member-type"><?php bp_current_member_type_message() ?></p>
<?php endif; ?>

<?php if ( bp_has_members( bp_ajax_querystring( 'members' ) ) ) : ?>

	<?php bp_nouveau_pagination( 'top' ); ?>

	<ul id="members-list" class="item-list bp-list">

	<?php while ( bp_members() ) : bp_the_member(); ?>

		<li <?php bp_member_class(); ?>  data-bp-item-id="<?php bp_member_user_id(); ?>" data-bp-item-component="members">
			<div class="item-avatar">
				<a href="<?php bp_member_permalink(); ?>"><?php bp_member_avatar(); ?></a>
			</div>

			<div class="item">
				<div class="item-title">
					<a href="<?php bp_member_permalink(); ?>"><?php bp_member_name(); ?></a>

					<?php if ( bp_get_member_latest_update() ) : ?>

						<span class="update"> <?php bp_member_latest_update(); ?></span>

					<?php endif; ?>

				</div>

				<?php if ( bp_nouveau_member_has_meta() ) : ?>
					<div class="item-meta">

						<?php bp_nouveau_member_meta(); ?>

					</div><!-- #item-meta -->
				<?php endif ; ?>

			</div>

			<div class="action">

				<?php bp_nouveau_members_loop_buttons(); ?>

			</div>

		</li>

	<?php endwhile; ?>

	</ul>

	<?php bp_nouveau_pagination( 'bottom' ); ?>

<?php else: ?>

	<div id="message" class="bp-messages info">
		<p><?php _e( "Sorry, no members were found.", 'bp-nouveau' ); ?></p>
	</div>

<?php endif; ?>

<?php bp_nouveau_after_loop(); ?>
