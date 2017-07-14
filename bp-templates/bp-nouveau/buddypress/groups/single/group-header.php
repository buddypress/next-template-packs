<?php
/**
 * BuddyPress - Groups Header
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */
?>

<?php bp_get_template_part( 'groups/single/parts/header-item-actions' ); ?>

<?php if ( ! bp_disable_group_avatar_uploads() ) : ?>
	<div id="item-header-avatar">
		<a href="<?php echo esc_url( bp_get_group_permalink() ); ?>">

			<?php bp_group_avatar(); ?>

		</a>
	</div><!-- #item-header-avatar -->
<?php endif; ?>

<div id="item-header-content">

	<p class="highlight group-status"><strong><?php echo esc_html( bp_nouveau_group_meta()->status ); ?></strong></p>

	<p class="activity" data-livestamp="<?php bp_core_iso8601_date( bp_get_group_last_active( 0, array( 'relative' => false ) ) ); ?>"><?php printf( __( 'active %s', 'buddypress' ), bp_get_group_last_active() ); ?></p>

	<?php bp_nouveau_group_hook( 'before', 'header_meta' ); ?>

	<?php if ( bp_nouveau_group_has_meta_extra() ): ?>
		<div class="item-meta">

			<?php echo bp_nouveau_group_meta()->extra; ?>

		</div><!-- .item-meta -->
	<?php endif; ?>


		<?php if ( ! bp_nouveau_groups_front_page_description() ) { ?>
			<?php if ( bp_nouveau_group_meta()->description ) { ?>
				<div class="group-description">
					<?php echo bp_nouveau_group_meta()->description; ?>
				</div><!-- //.group_description -->
			<?php	} ?>
		<?php } ?>

</div><!-- #item-header-content -->

<?php bp_nouveau_group_header_buttons(); ?>
