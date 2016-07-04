<?php
/**
 * BuddyPress - Blogs Loop
 *
 * Querystring is set via AJAX in _inc/ajax.php - bp_legacy_theme_object_filter().
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */

bp_nouveau_before_loop(); ?>

<?php if ( bp_has_blogs( bp_ajax_querystring( 'blogs' ) ) ) : ?>

	<?php bp_nouveau_pagination( 'top' ); ?>

	<ul id="blogs-list" class="item-list blogs-list bp-list">

	<?php while ( bp_blogs() ) : bp_the_blog(); ?>

		<li <?php bp_blog_class() ?>>

			<div class="item-avatar">
				<a href="<?php bp_blog_permalink(); ?>"><?php bp_blog_avatar( 'type=thumb' ); ?></a>
			</div>

			<div class="item">

				<h2 class="list-title blogs-title"><a href="<?php bp_blog_permalink(); ?>"><?php bp_blog_name(); ?></a></h2>

				<div class="item-meta"><span class="activity"><?php bp_blog_last_active(); ?></span></div>

				<?php bp_nouveau_blogs_loop_item(); ?>

			</div>

			<div class="action">

				<?php bp_nouveau_blogs_loop_buttons(); ?>

			</div>

			<div class="meta">

				<?php bp_blog_latest_post(); ?>

			</div>

		</li>

	<?php endwhile; ?>

	</ul>

	<?php bp_nouveau_pagination( 'bottom' ); ?>

<?php else: ?>

	<div id="message" class="bp-messages info">
		<p><?php _e( 'Sorry, there were no sites found.', 'bp-nouveau' ); ?></p>
	</div>

<?php endif; ?>

<?php bp_nouveau_after_loop(); ?>
