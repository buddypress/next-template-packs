<?php
/**
 * BuddyPress - Groups Members
 *
 * @since  1.0.0
 *
 * @package BP Nouveau
 */
?>

<div class="item-list-tabs bp-navs bp-subnavs group-subnav" id="subnav" role="navigation">
	<ul class="subnav-filters filters clearfix">

		<?php bp_nouveau_search_form(); ?>

		<li id="group_members-order-select" class="last filter">
			<label for="group_members-order-by">
				<span class="bp-screen-reader-text"><?php esc_html_e( 'Order By:', 'bp-nouveau' ); ?></span>
			</label>
			<select id="group_members-order-by" data-bp-filter="group_members">

				<?php bp_nouveau_filter_options(); ?>

			</select>
		</li>

	</ul>
</div>

<div id="members-group-list" class="group_members dir-list" data-bp-list="group_members">

	<div id="bp-ajax-loader"><?php bp_nouveau_user_feedback( 'group-members-loading' ) ;?></div>

</div><!-- .group_members.dir-list -->
