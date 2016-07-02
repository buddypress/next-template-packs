<?php
/**
 * BuddyPress - Users Header
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */

?>

<div id="item-header-avatar">
	<a href="<?php bp_displayed_user_link(); ?>">

		<?php bp_displayed_user_avatar( 'type=full' ); ?>

	</a>
</div><!-- #item-header-avatar -->

<div id="item-header-content">

	<?php if ( bp_is_active( 'activity' ) && bp_activity_do_mentions() ) : ?>
		<h2 class="user-nicename">@<?php bp_displayed_user_mentionname(); ?></h2>
	<?php endif; ?>

	<?php bp_nouveau_member_hook( 'before', 'header_meta' ); ?>

	<?php if ( bp_nouveau_member_has_meta() ) : ?>
		<div class="item-meta">

			<?php bp_nouveau_member_meta(); ?>

		</div><!-- #item-meta -->
	<?php endif ; ?>

	<div id="item-buttons">

		<?php bp_nouveau_member_header_buttons(); ?>

	</div><!-- #item-buttons -->
</div><!-- #item-header-content -->
