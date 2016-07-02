<?php
/**
 * BuddyPress - Groups Header item-actions.
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
