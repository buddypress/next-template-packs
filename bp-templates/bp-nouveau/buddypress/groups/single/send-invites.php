<?php
/**
 * BuddyPress - Groups Send Invites
 *
 * @package BuddyPress
 * @subpackage bp-nouveau
 */

/**
 * Fires before the send invites content.
 *
 * @since 1.1.0
 */
do_action( 'bp_before_group_send_invites_content' ); ?>

<?php bp_get_template_part( 'assets/invites/index' ); ;?>

<?php

/**
 * Fires after the send invites content.
 *
 * @since 1.2.0
 */
do_action( 'bp_after_group_send_invites_content' ); ?>
