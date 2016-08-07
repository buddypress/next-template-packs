<?php
/**
 * BP Nouveau Invites main template.
 *
 * This template is used to inject the BuddyPress Backbone views
 * dealing with invites.
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */
?>

<h2 class="bp-screen-title <?php if(bp_is_group_create()) echo 'creation-step-name'; ?>">
	<?php _e( 'Invite Members', 'bp-nouveau' ); ?>
</h2>

<div class="bp-navs bp-subnavs group-subnav bp-invites-nav" id="subnav"></div>
<div class="subnav-filters group-subnav-filters bp-invites-filters"></div>
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
						<?php esc_html_e( 'The invite has not been sent yet.', 'bp-nouveau' ); ?>
					<# } else { #>
						<?php esc_html_e( 'The invite has been sent.', 'bp-nouveau' ); ?>
					<# } #>
				</span>

				<# if ( undefined !== data.invited_by ) { #>
					<ul class="group-inviters">
						<li><?php esc_html_e( 'Invited by:', 'bp-nouveau' ); ?></li>
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
			<a href="#" class="button invite-button group-add-remove-invite-button" title="<?php esc_attr_e( 'Invite / Uninvite', 'bp-nouveau' );?>">
				<span class="bp-screen-reader-text"><?php esc_html_e( 'Invite/Uninvite', 'bp-nouveau' );?></span>
			</a>
		<# } #>

		<# if ( undefined !== data.can_edit && true === data.can_edit ) { #>
			<a href="#" class="button invite-button group-remove-invite-button" title="<?php esc_attr_e( 'Remove', 'bp-nouveau' );?>">
				<span class="bp-screen-reader-text"><?php esc_html_e( 'Remove', 'bp-nouveau' );?></span>
			</a>
		<# } #>
	</div>

</script>

<script type="text/html" id="tmpl-bp-invites-selection">
	<a href="#" title="{{data.name}}">
		<img src="{{data.avatar}}" class="avatar" alt="{{data.name}}">
	</a>
</script>

<script type="text/html" id="tmpl-bp-invites-form">
	<textarea placeholder="<?php esc_attr_e( 'Optional: add a message to your invite.', 'bp-nouveau' ); ?>"></textarea>

	<div class="action">
		<input type="button" id="bp-invites-reset" class="button bp-secondary-action" value="<?php esc_attr_e( 'Cancel', 'bp-nouveau' ); ?>"/>
		<input type="button" id="bp-invites-send" class="button bp-primary-action" value="<?php esc_attr_e( 'Send', 'bp-nouveau' ); ?>"/>
	</div>
</script>

<script type="text/html" id="tmpl-bp-invites-filters">
	<li class="group-invites-search" role="search" data-bp-search="{{data.scope}}">
		<form action="" method="get" id="group_invites_search_form">
			<label for="group_invites_search">
				<input type="search" id="group_invites_search" placeholder="<?php esc_attr_e( __( 'Search', 'bp-nouveau' ) ); ?>"/>
			</label>
			<button type="submit" id="group_invites_search_submit">
				<span class="dashicons dashicons-search"></span>
				<span class="bp-screen-reader-text"><?php esc_html_e( 'Search', 'bp-nouveau' ); ?></span>
			</button>
		</form>
	</li>
</script>

<script type="text/html" id="tmpl-bp-invites-paginate">
	<# if ( 1 !== data.page ) { #>
		<a href="#" id="bp-invites-prev-page" title="<?php esc_attr_e( 'Prev', 'bp-nouveau' );?>" class="button invite-button">
			<span class="bp-screen-reader-text"><?php esc_html_e( 'Prev', 'bp-nouveau' );?></span>
		</a>
	<# } #>

	<# if ( data.total_page !== data.page ) { #>
		<a href="#" id="bp-invites-next-page" title="<?php esc_attr_e( 'Next', 'bp-nouveau' );?>" class="button invite-button">
			<span class="bp-screen-reader-text"><?php esc_html_e( 'Next', 'bp-nouveau' );?></span>
		</a>
	<# } #>
</script>
