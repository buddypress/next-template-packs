<?php
/**
 * BuddyPress - Users Activity
 *
 * @since  1.0.0
 *
 * @package BP Nouveau
 */

?>

<div class="bp-navs bp-subnavs user-subnav no-ajax" id="subnav" role="navigation">
	<ul class="subnav">

		<?php bp_get_template_part( 'members/single/parts/item-subnav' ); ?>

	</ul>
</div><!-- .item-list-tabs#subnav -->

<?php bp_nouveau_activity_member_post_form() ;?>


<?php bp_get_template_part( 'common/search-&-filters-bar' ); ?>

<?php bp_nouveau_member_hook( 'before', 'activity_content' ); ?>

<div class="activity single-user">

	<ul id="activity-stream" class="<?php bp_nouveau_loop_classes(); ?>" data-bp-list="activity">

		<li id="bp-ajax-loader"><?php bp_nouveau_user_feedback( 'member-activity-loading' ) ;?></li>

	</ul>

</div><!-- .activity -->

<?php bp_nouveau_member_hook( 'after', 'activity_content' );
