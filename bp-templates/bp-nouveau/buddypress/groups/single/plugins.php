<?php
/**
 * BuddyPress - Groups plugins
 *
 * @since  1.0.0
 *
 * @package BP Nouveau
 */

bp_nouveau_group_hook( 'before', 'plugin_template' ); ?>

<?php

/**
 * Fires and displays content for plugins using the BP_Group_Extension.
 *
 * @since 1.0.0
 */
do_action( 'bp_template_content' ); ?>

<?php bp_nouveau_group_hook( 'after', 'plugin_template' );
