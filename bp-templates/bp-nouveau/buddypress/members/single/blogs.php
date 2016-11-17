<?php
/**
 * BuddyPress - Users Blogs
 *
 * @since  1.0.0
 *
 * @package BP Nouveau
 */

?>

<div class="bp-navs bp-subnavs user-subnav" id="subnav" role="navigation">
	<ul class="subnav">

		<?php bp_get_template_part( 'members/single/parts/item-subnav' ); ?>

	</ul>
</div><!-- .bp-navs -->

<div class="subnav-filters filters">
	<ul>
	<?php bp_nouveau_search_form(); ?>
</ul>

	<?php bp_get_template_part('common/filters/user-screens-filters'); ?>

</div>


<?php
switch ( bp_current_action() ) :

	// Home/My Blogs
	case 'my-sites' :

		bp_nouveau_member_hook( 'before', 'blogs_content' ); ?>

		<div class="blogs myblogs" data-bp-list="blogs">

			<div id="bp-ajax-loader"><?php bp_nouveau_user_feedback( 'member-blogs-loading' ) ;?></div>

		</div><!-- .blogs.myblogs -->

		<?php bp_nouveau_member_hook( 'after', 'blogs_content' );
		break;

	// Any other
	default :
		bp_get_template_part( 'members/single/plugins' );
		break;
endswitch;
