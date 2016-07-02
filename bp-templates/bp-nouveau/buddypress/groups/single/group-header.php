<?php
/**
 * BuddyPress - Groups Header
 *
 * @since  1.0.0
 *
 * @package BP Nouveau
 */
?>

<div id="item-actions">

	<?php if ( bp_group_is_visible() ) : ?>

		<h3><?php _e( 'Group Admins', 'bp-nouveau' ); ?></h3>

		<?php bp_group_list_admins();

		bp_nouveau_group_hook( 'after', 'menu_admins' );

		if ( bp_group_has_moderators() ) :

			bp_nouveau_group_hook( 'before', 'menu_mods' ); ?>

			<h3><?php _e( 'Group Mods' , 'bp-nouveau' ); ?></h3>

			<?php bp_group_list_mods();

			bp_nouveau_group_hook( 'after', 'menu_mods' );

		endif;

	endif; ?>

</div><!-- #item-actions -->

<?php if ( ! bp_disable_group_avatar_uploads() ) : ?>
	<div id="item-header-avatar">
		<a href="<?php echo esc_url( bp_get_group_permalink() ); ?>" title="<?php echo esc_attr( bp_get_group_name() ); ?>">

			<?php bp_group_avatar(); ?>

		</a>
	</div><!-- #item-header-avatar -->
<?php endif; ?>

<div id="item-header-content">
	<span class="highlight"><?php bp_group_type(); ?></span>
	<span class="activity"><?php printf( __( 'active %s', 'bp-nouveau' ), bp_get_group_last_active() ); ?></span>

	<?php bp_nouveau_group_hook( 'before', 'header_meta' ); ?>

	<?php if ( bp_nouveau_group_has_meta() ): ?>
		<div id="item-meta">

			<?php bp_nouveau_group_meta(); ?>

		</div><!-- #item-meta -->
	<?php endif; ?>

	<div id="item-buttons">

		<?php bp_nouveau_group_header_buttons(); ?>

	</div><!-- #item-buttons -->
</div><!-- #item-header-content -->
