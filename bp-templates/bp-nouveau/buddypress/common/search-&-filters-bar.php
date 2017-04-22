<?php
/**
	* BP Nouveau Search & filters bar
	*
	* @since 1.0.0
	*
	* @package BP Nouveau
	*/
?>
<div class="subnav-filters filters no-ajax" id="subnav-filters">

	<?php if ( 'friends' !== bp_current_component() ) : ?>
	<div class="subnav-search clearfix">

		<?php if ( 'activity' == bp_current_component() ) :?>
			<div class="feed"><a href="<?php bp_sitewide_activity_feed_link(); ?>" title="<?php esc_attr_e( 'RSS Feed', 'bp-nouveau' ); ?>"><span class="bp-screen-reader-text"><?php _e( 'RSS', 'bp-nouveau' ); ?></span></a></div>
		<?php endif; ?>

		<?php bp_nouveau_search_form(); ?>

	</div>
	<?php endif; ?>

		<?php if ( bp_is_user() && ! bp_is_current_action( 'requests') ) : ?>
			<?php bp_get_template_part('common/filters/user-screens-filters'); ?>
		<?php elseif ( 'groups' == bp_current_component() ) : ?>
			<?php bp_get_template_part( 'common/filters/groups-screens-filters' );  ?>
		<?php else : ?>
			<?php bp_get_template_part( 'common/filters/directory-filters' ); ?>
		<?php endif; ?>

</div><!-- search & filters -->
