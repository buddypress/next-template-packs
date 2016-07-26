<?php
/**
 * BP Nouveau Messages main template.
 *
 * This template is used to inject the BuddyPress Backbone views
 * dealing with user's private messages.
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */
?>
<div class="bp-navs bp-subnavs user-subnav bp-messages-filters" id="subsubnav"></div>
<div class="bp-messages-content"></div>
<div class="bp-messages-feedback"></div>

<script type="text/html" id="tmpl-bp-messages-form">
	<?php bp_nouveau_messages_hook( 'before', 'compose_content' ); ?>

	<label for="send-to-input"><?php esc_html_e( 'Send @Username', 'bp-nouveau' ); ?></label>
	<input type="text" name="send_to" class="send-to-input" id="send-to-input" />

	<label for="subject"><?php _e( 'Subject', 'bp-nouveau' ); ?></label>
	<input type="text" name="subject" id="subject"/>

	<div id="bp-message-content"></div>

	<?php bp_nouveau_messages_hook( 'after', 'compose_content' ); ?>

	<div class="submit">
		<input type="button" id="bp-messages-reset" class="button bp-secondary-action" value="<?php esc_attr_e( 'Reset', 'bp-nouveau' ); ?>"/>
		<input type="button" id="bp-messages-send" class="button bp-primary-action" value="<?php esc_attr_e( 'Send', 'bp-nouveau' ); ?>"/>
	</div>
</script>

<script type="text/html" id="tmpl-bp-messages-editor">
	<?php
	// Temporarly filter the editor
	add_filter( 'mce_buttons', 'bp_nouveau_mce_buttons', 10, 1 );

	wp_editor(
		'',
		'message_content',
		array(
			'textarea_name' => 'message_content',
			'teeny'         => false,
			'media_buttons' => false,
			'dfw'           => false,
			'tinymce'       => true,
			'quicktags'     => false,
			'tabindex'      => '3',
			'textarea_rows' => 5,
		)
	);
	// Temporarly filter the editor
	remove_filter( 'mce_buttons', 'bp_nouveau_mce_buttons', 10, 1 ); ?>
</script>

<script type="text/html" id="tmpl-bp-messages-paginate">
	<# if ( 1 !== data.page ) { #>
		<a href="#" id="bp-messages-prev-page" title="<?php esc_attr_e( 'Prev', 'bp-nouveau' );?>" class="button messages-button">
			<span class="bp-screen-reader-text"><?php esc_html_e( 'Prev', 'bp-nouveau' );?></span>
		</a>
	<# } #>

	<# if ( data.total_page !== data.page ) { #>
		<a href="#" id="bp-messages-next-page" title="<?php esc_attr_e( 'Next', 'bp-nouveau' );?>" class="button messages-button">
			<span class="bp-screen-reader-text"><?php esc_html_e( 'Next', 'bp-nouveau' );?></span>
		</a>
	<# } #>
</script>

<script type="text/html" id="tmpl-bp-messages-filters">
	<li class="user-messages-bulk-actions"></div>
	<li class="user-messages-search" role="search" data-bp-search="{{data.box}}">
		<form action="" method="get" id="user_messages_search_form">
			<label for="user_messages_search">
				<input type="search" id="user_messages_search" placeholder="<?php esc_attr_e( __( 'Search', 'bp-nouveau' ) ); ?>"/>
			</label>
			<button type="submit" id="user_messages_search_submit">
				<span class="dashicons dashicons-search"></span>
				<span class="bp-screen-reader-text"><?php esc_html_e( 'Search', 'bp-nouveau' ); ?></span>
			</button>
		</form>
	</li>
</script>

<script type="text/html" id="tmpl-bp-bulk-actions">
	<label for="user_messages_select_all">
		<input type="checkbox" id="user_messages_select_all" value="1"/>
		<span class="bp-screen-reader-text"><?php esc_html_e( __( 'Select All Messages', 'bp-nouveau' ) ); ?></span>
	</label>
	<div class="bulk-actions bp-hide">
		<select id="user-messages-bulk-actions" class="filter">
			<# for ( i in data ) { #>
				<option value="{{data[i].value}}">{{data[i].label}}</option>
			<# } #>
		</select>
		<a href="#" class="messages-button bulk-apply" role="submit" title="<?php esc_attr_e( 'Apply', 'bp-nouveau' );?>">
			<span class="bp-screen-reader-text"><?php esc_html_e( __( 'Apply', 'bp-nouveau' ) ); ?></span>
		</a>
	</div>
</script>

<script type="text/html" id="tmpl-bp-messages-thread">
	<div class="thread-cb">
		<label for="bp-message-thread-{{data.id}}">
			<input type="checkbox" name="message_ids[]" id="bp-message-thread-{{data.id}}" class="message-check" value="{{data.id}}">
			<span class="bp-screen-reader-text"><?php esc_html_e( 'Select this message', 'bp-nouveau' ); ?></span>
		</label>
	</div>

	<div class="thread-content" data-thread-id="{{data.id}}">
		<div class="thread-from">
			<a href="{{data.sender_link}}" title="{{data.sender_name}}" class="user-link">
				<img src="{{data.sender_avatar}}" width="32px" height="32px" class="avatar">
				{{data.sender_name}}
			</a>
		</div>
		<div class="thread-subject">
			<span class="thread-count">({{data.count}})</span>
			<span class="subject"><# print( data.subject ); #></span>
			<span class="excerpt"><# print( data.excerpt ); #></span>
		</div>
		<div class="thread-date">
			<time datetime="{{data.date.toISOString()}}">{{data.display_date}}</time>
		</div>
	</div>
</script>

<script type="text/html" id="tmpl-bp-messages-preview">
	<# if ( undefined !== data.content ) { #>
		<h4><?php esc_html_e( 'Active conversation:', 'bp-nouveau' ); ?> <# print( data.subject ); #></h4>
		<div class="preview-content">
			<# if ( undefined !== data.recipients ) { #>
				<ul class="thread-participants">
					<li><?php esc_html_e( 'Participants:', 'bp-nouveau' ); ?></li>
					<# for ( i in data.recipients ) { #>
						<li><a href="{{data.recipients[i].user_link}}" title="{{data.recipients[i].user_name}}"><img src="{{data.recipients[i].avatar}}" width="28px" class="avatar mini"></a></li>
					<# } #>
				</ul>
			<# } #>

			<div class="actions">

				<a href="#" class="message-action-delete" data-bp-action="delete" title="<?php esc_attr_e( 'Delete conversation.', 'bp-nouveau' );?>">
					<span class="bp-screen-reader-text"><?php esc_html_e( 'Delete conversation.', 'bp-nouveau' );?></span>
				</a>

				<# if ( undefined !== data.star_link ) { #>

					<# if ( false !== data.is_starred ) { #>
						<a href="{{data.star_link}}" class="message-action-unstar" data-bp-action="unstar" title="<?php esc_attr_e( 'Unstar Conversation', 'bp-nouveau' );?>">
							<span class="bp-screen-reader-text"><?php esc_html_e( 'Unstar Conversation', 'bp-nouveau' );?></span>
						</a>
					<# } else { #>
						<a href="{{data.star_link}}" class="message-action-star" data-bp-action="star" title="<?php esc_attr_e( 'Star Conversation', 'bp-nouveau' );?>">
							<span class="bp-screen-reader-text"><?php esc_html_e( 'Star Conversation', 'bp-nouveau' );?></span>
						</a>
					<# } #>

				<# } #>

				<a href="#view/{{data.id}}" class="message-action-view" title="<?php esc_attr_e( 'View Full Conversation.', 'bp-nouveau' );?>">
					<span class="bp-screen-reader-text"><?php esc_html_e( 'View Full conversation.', 'bp-nouveau' );?></span>
				</a>
			</div>

			<div class='preview-message'>
				<# print( data.content ) #>
			</div>
		</div>
	<# } #>
</script>

<script type="text/html" id="tmpl-bp-messages-single-header">
	<h4 id="message-subject"><# print( data.subject ); #></h4>

	<# if ( undefined !== data.recipients ) { #>
		<ul class="thread-participants">
			<li><?php esc_html_e( 'Participants:', 'bp-nouveau' ); ?></li>
			<# for ( i in data.recipients ) { #>
				<li><a href="{{data.recipients[i].user_link}}" title="{{data.recipients[i].user_name}}"><img src="{{data.recipients[i].avatar}}" width="28px" class="avatar mini"></a></li>
			<# } #>
		</ul>
	<# } #>

	<div class="actions">

		<a href="#" class="message-action-delete" data-bp-action="delete" title="<?php esc_attr_e( 'Delete conversation.', 'bp-nouveau' );?>">
			<span class="bp-screen-reader-text"><?php esc_html_e( 'Delete conversation.', 'bp-nouveau' );?></span>
		</a>

		<?php bp_nouveau_messages_hook( 'after', 'thread_header_actions' ); ?>
	</div>

</script>

<script type="text/html" id="tmpl-bp-messages-single-list">
	<div class="message-metadata">
		<?php bp_nouveau_messages_hook( 'before', 'meta' ); ?>

		<a href="{{data.sender_link}}" title="{{data.sender_name}}" class="user-link">
			<img src="{{data.sender_avatar}}" width="32px" height="32px" class="avatar">
			<strong>{{data.sender_name}}</strong>
		</a>

		<time datetime="{{data.date.toISOString()}}" class="activity">{{data.display_date}}</time>

		<div class="actions">
			<# if ( undefined !== data.star_link ) { #>

				<?php $test = 1; ?>

				<# if ( false !== data.is_starred ) { #>
					<a href="{{data.star_link}}" class="message-action-unstar" data-bp-action="unstar" title="<?php esc_attr_e( 'Unstar Message', 'bp-nouveau' );?>">
						<span class="bp-screen-reader-text"><?php esc_html_e( 'Unstar Message', 'bp-nouveau' );?></span>
					</a>
				<# } else { #>
					<a href="{{data.star_link}}" class="message-action-star" data-bp-action="star" title="<?php esc_attr_e( 'Star Message', 'bp-nouveau' );?>">
						<span class="bp-screen-reader-text"><?php esc_html_e( 'Star Message', 'bp-nouveau' );?></span>
					</a>
				<# } #>

			<# } #>
		</div>

		<?php bp_nouveau_messages_hook( 'after', 'meta' ); ?>

	</div>

	<?php bp_nouveau_messages_hook( 'before', 'content' ); ?>

	<div class="message-content"><# print( data.content ) #></div>

	<?php bp_nouveau_messages_hook( 'after', 'content' ); ?>

</script>

<script type="text/html" id="tmpl-bp-messages-single">
	<?php bp_nouveau_messages_hook( 'before', 'thread_content' ); ?>

	<div id="bp-message-thread-header"></div>

	<?php bp_nouveau_messages_hook( 'before', 'thread_list' ); ?>

	<ul id="bp-message-thread-list"></ul>

	<?php bp_nouveau_messages_hook( 'after', 'thread_list' ); ?>

	<?php bp_nouveau_messages_hook( 'before', 'thread_reply' ); ?>

	<form id="send-reply" class="standard-form">
		<div class="message-box">
			<div class="message-metadata">

				<?php bp_nouveau_messages_hook( 'before', 'meta' ); ?>

				<div class="avatar-box">
					<?php bp_loggedin_user_avatar( 'type=thumb&height=30&width=30' ); ?>

					<strong><?php _e( 'Send a Reply', 'bp-nouveau' ); ?></strong>
				</div>

				<?php bp_nouveau_messages_hook( 'after', 'meta' ); ?>

			</div><!-- .message-metadata -->

			<div class="message-content">

				<?php bp_nouveau_messages_hook( 'before', 'reply_box' ); ?>

				<label for="message_content" class="bp-screen-reader-text"><?php _e( 'Reply to Message', 'bp-nouveau' ); ?></label>
				<div id="bp-message-content"></div>

				<?php bp_nouveau_messages_hook( 'after', 'reply_box' ); ?>

				<div class="submit">
					<input type="submit" name="send" value="<?php esc_attr_e( 'Send Reply', 'bp-nouveau' ); ?>" id="send_reply_button"/>
				</div>

			</div><!-- .message-content -->

		</div><!-- .message-box -->
	</form>

	<?php bp_nouveau_messages_hook( 'after', 'thread_reply' ); ?>

	<?php bp_nouveau_messages_hook( 'after', 'thread_content' ); ?>
</script>
