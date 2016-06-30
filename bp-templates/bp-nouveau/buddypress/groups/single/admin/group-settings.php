<?php
/**
 * BP Nouveau Group's edit settings template.
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */
?>

<h4><?php _e( 'Privacy Options', 'bp-nouveau' ); ?></h4>

<div class="radio">

	<label for="group-status-public"><input type="radio" name="group-status" id="group-status-public" value="public"<?php if ( 'public' == bp_get_new_group_status() || !bp_get_new_group_status() ) { ?> checked="checked"<?php } ?> aria-describedby="public-group-description" /> <?php _e( 'This is a public group', 'bp-nouveau' ); ?></label>

	<ul id="public-group-description">
		<li><?php _e( 'Any site member can join this group.', 'bp-nouveau' ); ?></li>
		<li><?php _e( 'This group will be listed in the groups directory and in search results.', 'bp-nouveau' ); ?></li>
		<li><?php _e( 'Group content and activity will be visible to any site member.', 'bp-nouveau' ); ?></li>
	</ul>

	<label for="group-status-private"><input type="radio" name="group-status" id="group-status-private" value="private"<?php if ( 'private' == bp_get_new_group_status() ) { ?> checked="checked"<?php } ?> aria-describedby="private-group-description" /> <?php _e( 'This is a private group', 'bp-nouveau' ); ?></label>

	<ul id="private-group-description">
		<li><?php _e( 'Only users who request membership and are accepted can join the group.', 'bp-nouveau' ); ?></li>
		<li><?php _e( 'This group will be listed in the groups directory and in search results.', 'bp-nouveau' ); ?></li>
		<li><?php _e( 'Group content and activity will only be visible to members of the group.', 'bp-nouveau' ); ?></li>
	</ul>

	<label for="group-status-hidden"><input type="radio" name="group-status" id="group-status-hidden" value="hidden"<?php if ( 'hidden' == bp_get_new_group_status() ) { ?> checked="checked"<?php } ?> aria-describedby="hidden-group-description" /> <?php _e('This is a hidden group', 'bp-nouveau' ); ?></label>

	<ul id="hidden-group-description">
		<li><?php _e( 'Only users who are invited can join the group.', 'bp-nouveau' ); ?></li>
		<li><?php _e( 'This group will not be listed in the groups directory or search results.', 'bp-nouveau' ); ?></li>
		<li><?php _e( 'Group content and activity will only be visible to members of the group.', 'bp-nouveau' ); ?></li>
	</ul>

</div>

<hr />

<h4><?php _e( 'Group Invitations', 'bp-nouveau' ); ?></h4>

<p><?php _e( 'Which members of this group are allowed to invite others?', 'bp-nouveau' ); ?></p>

<div class="radio">

	<label for="group-invite-status-members"><input type="radio" name="group-invite-status" id="group-invite-status-members" value="members"<?php bp_group_show_invite_status_setting( 'members' ); ?> /> <?php _e( 'All group members', 'bp-nouveau' ); ?></label>

	<label for="group-invite-status-mods"><input type="radio" name="group-invite-status" id="group-invite-status-mods" value="mods"<?php bp_group_show_invite_status_setting( 'mods' ); ?> /> <?php _e( 'Group admins and mods only', 'bp-nouveau' ); ?></label>

	<label for="group-invite-status-admins"><input type="radio" name="group-invite-status" id="group-invite-status-admins" value="admins"<?php bp_group_show_invite_status_setting( 'admins' ); ?> /> <?php _e( 'Group admins only', 'bp-nouveau' ); ?></label>

	</div>

<hr />
