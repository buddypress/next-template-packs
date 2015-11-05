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
<div class="item-list-tabs bp-invites-nav" id="subsubnav"></div>
<div class="bp-invites-content"></div>

<script type="text/html" id="tmpl-bp-invites-nav">
	<a href="{{data.href}}" class="bp-invites-nav-item" data-nav="{{data.id}}">{{data.name}}</a>
</script>
