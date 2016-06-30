<?php
/**
 * BuddyPress - Blogs Create
 *
 * @package BuddyPress
 * @subpackage bp-nouveau
 */

bp_nouveau_blogs_create_hook( 'before', 'content_template' ); ?>

<?php bp_nouveau_template_notices(); ?>

<?php bp_nouveau_blogs_create_hook( 'before', 'content' ); ?>

<?php if ( bp_blog_signup_enabled() ) : ?>

	<?php bp_show_blog_signup_form(); ?>

<?php else: ?>

	<div id="message" class="bp-messages info">
		<p><?php _e( 'Site registration is currently disabled', 'bp-nouveau' ); ?></p>
	</div>

<?php endif; ?>

<?php
bp_nouveau_blogs_create_hook( 'after', 'content' );

bp_nouveau_blogs_create_hook( 'after', 'content_template' );
