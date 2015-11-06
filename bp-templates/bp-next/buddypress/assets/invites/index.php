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
	</div>

	<div class="action"></div>

	<div class="clear"></div>
</script>
