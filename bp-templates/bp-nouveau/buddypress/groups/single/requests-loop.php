<?php
/**
 * BuddyPress - Groups Requests Loop
 *
 * @since  1.0.0
 *
 * @package BP Nouveau
 */

?>

<?php if ( bp_group_has_membership_requests( bp_ajax_querystring( 'membership_requests' ) ) ) : ?>

	<?php
	/**
	 *
	 * @todo  create a template tag for $GLOBALS['requests_template']->pag_arg
	 *
	 */
	?>
	<div id="pag-top" class="pagination" data-bp-pagination="<?php echo esc_attr( $GLOBALS['requests_template']->pag_arg );?>">

		<div class="pag-count" id="group-mem-requests-count-top">

			<?php bp_group_requests_pagination_count(); ?>

		</div>

		<div class="pagination-links" id="group-mem-requests-pag-top">

			<?php bp_group_requests_pagination_links(); ?>

		</div>

	</div>

	<ul id="request-list" class="item-list">
		<?php while ( bp_group_membership_requests() ) : bp_group_the_membership_request(); ?>

			<li>
				<?php bp_group_request_user_avatar_thumb(); ?>
				<h4><?php bp_group_request_user_link(); ?> <span class="comments"><?php bp_group_request_comment(); ?></span></h4>
				<span class="activity"><?php bp_group_request_time_since_requested(); ?></span>

				<?php bp_nouveau_group_hook( '', 'membership_requests_admin_item' ); ?>

				<div class="action">

					<?php bp_nouveau_groups_request_buttons(); ?>

				</div>
			</li>

		<?php endwhile; ?>
	</ul>
	<?php
	/**
	 *
	 * @todo  create a template tag for $GLOBALS['requests_template']->pag_arg
	 *
	 */
	?>
	<div id="pag-bottom" class="pagination" data-bp-pagination="<?php echo esc_attr( $GLOBALS['requests_template']->pag_arg );?>">

		<div class="pag-count" id="group-mem-requests-count-bottom">

			<?php bp_group_requests_pagination_count(); ?>

		</div>

		<div class="pagination-links" id="group-mem-requests-pag-bottom">

			<?php bp_group_requests_pagination_links(); ?>

		</div>

	</div>

	<?php else:

		bp_nouveau_user_feedback( 'group-requests-none' );

	endif; ?>
