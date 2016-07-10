<?php
/**
 * BuddyPress - Users Plugins Template
 *
 * 3rd-party plugins should use this template to easily add template
 * support to their plugins for the members component.
 *
 * @since  1.0.0
 *
 * @package BP Nouveau
 */

bp_nouveau_member_hook( 'before', 'plugin_template' ); ?>

<?php if ( ! bp_is_current_component_core() ) : ?>

	<div class="item-list-tabs no-ajax" id="subnav">
		<ul>

			<?php bp_get_template_part( 'members/single/parts/item-subnav' ); ?>

			<?php bp_nouveau_member_hook( '', 'plugin_options_nav' ); ?>

		</ul>
	</div><!-- .item-list-tabs -->

<?php endif; ?>

<?php if ( has_action( 'bp_template_title' ) ) : ?>

	<h3><?php bp_nouveau_plugin_hook( 'title' ); ?></h3>

<?php endif; ?>

<?php bp_nouveau_plugin_hook( 'content' );

bp_nouveau_member_hook( 'after', 'plugin_template' );
