<?php
/**
 * BP Next Invites main template.
 *
 * This template is used to inject the BuddyPress Backbone views
 * dealing with invites.
 *
 * @since 1.0.0
 *
 * @package BP Next
 */
?>
<div class="item-list-tabs bp-invites-nav" id="subnav"></div>
<div class="item-list-tabs bp-invites-filters" id="subsubnav"></div>
<div class="members bp-invites-content"></div>
<div class="bp-invites-feedback"></div>

<script type="text/html" id="tmpl-bp-invites-nav">
	<a href="{{data.href}}" class="bp-invites-nav-item" data-nav="{{data.id}}">{{data.name}}</a>
</script>

<script type="text/html" id="tmpl-bp-invites-users">
	<div class="item-avatar">
		<img src="{{data.avatar}}" class="avatar">
	</div>

	<div class="item">
		<div class="item-title">
			{{data.name}}
		</div>

		<# if ( undefined !== data.is_sent ) { #>
			<div class="item-meta">
				<span class="status">
					<# if ( false === data.is_sent ) { #>
						<?php esc_html_e( 'The invite has not been sent yet.', 'bp-next' ); ?>
					<# } else { #>
						<?php esc_html_e( 'The invite has been sent.', 'bp-next' ); ?>
					<# } #>
				</span>

				<# if ( undefined !== data.invited_by ) { #>
					<ul class="group-inviters">
						<li><?php esc_html_e( 'Invited by:', 'bp-next' ); ?></li>
						<# for ( i in data.invited_by ) { #>
							<li><a href="{{data.invited_by[i].user_link}}" title="{{data.invited_by[i].user_name}}"><img src="{{data.invited_by[i].avatar}}" width="20px" class="avatar mini"></a></li>
						<# } #>
					</ul>
				<# } #>
			</div>
		<# } #>
	</div>

	<div class="action">
		<# if ( undefined === data.is_sent || ( false === data.is_sent && true === data.can_edit ) ) { #>
			<a href="#" class="button invite-button group-add-remove-invite-button" title="<?php esc_attr_e( 'Invite / Uninvite', 'bp-next' );?>">
				<span class="bp-screen-reader-text"><?php esc_html_e( 'Invite/Uninvite', 'bp-next' );?></span>
			</a>
		<# } #>

		<# if ( undefined !== data.can_edit && true === data.can_edit ) { #>
			<a href="#" class="button invite-button group-remove-invite-button" title="<?php esc_attr_e( 'Remove', 'bp-next' );?>">
				<span class="bp-screen-reader-text"><?php esc_html_e( 'Remove', 'bp-next' );?></span>
			</a>
		<# } #>
	</div>

	<div class="clear"></div>
</script>

<script type="text/html" id="tmpl-bp-invites-selection">
	<a href="#" title="{{data.name}}">
		<img src="{{data.avatar}}" class="avatar" alt="{{data.name}}">
	</a>
</script>

<script type="text/html" id="tmpl-bp-invites-form">
	<textarea placeholder="<?php esc_attr_e( 'Optional: add a message to your invite.', 'bp-next' ); ?>"></textarea>

	<div class="action">
		<input type="button" id="bp-invites-reset" class="button bp-secondary-action" value="<?php esc_attr_e( 'Cancel', 'bp-next' ); ?>"/>
		<input type="button" id="bp-invites-send" class="button bp-primary-action" value="<?php esc_attr_e( 'Send', 'bp-next' ); ?>"/>
	</div>
</script>
