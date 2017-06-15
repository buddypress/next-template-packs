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
<div class="subnav-filters filters user-subnav bp-messages-filters" id="subsubnav"></div>
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
		<button id="bp-messages-prev-page"class="button messages-button">
			<span class="dashicons dashicons-arrow-left"></span>
			<span class="bp-screen-reader-text"><?php esc_html_e( 'Prev', 'bp-nouveau' );?></span>
		</button>
	<# } #>

	<# if ( data.total_page !== data.page ) { #>
		<button id="bp-messages-next-page"class="button messages-button">
			<span class="dashicons dashicons-arrow-right"></span>
			<span class="bp-screen-reader-text"><?php esc_html_e( 'Next', 'bp-nouveau' );?></span>
		</button>
	<# } #>
</script>

<script type="text/html" id="tmpl-bp-messages-filters">
	<li class="user-messages-bulk-actions"></li>
	<li class="user-messages-search" role="search" data-bp-search="{{data.box}}">
		<div class="bp-search messages-search">
			<form action="" method="get" id="user_messages_search_form" class="bp-messages-search-form" data-bp-search="messages">
				<label for="user_messages_search" class="bp-screen-reader-text">
					<?php _e('Search Messages', 'bp-nouveau'); ?>
				</label>
				<input type="search" id="user_messages_search" placeholder="<?php esc_attr_e( __( 'Search', 'bp-nouveau' ) ); ?>"/>
				<button type="submit" id="user_messages_search_submit">
					<span class="dashicons dashicons-search" aria-hidden="true"></span>
					<span class="bp-screen-reader-text"><?php esc_html_e( 'Search', 'bp-nouveau' ); ?></span>
				</button>
			</form>
		</div>
	</li>
</script>

<script type="text/html" id="tmpl-bp-bulk-actions">
	<label for="user_messages_select_all">
		<input type="checkbox" id="user_messages_select_all" value="1"/>
		<span class="bp-screen-reader-text"><?php esc_html_e( __( 'Select All Messages', 'bp-nouveau' ) ); ?></span>
	</label>
<div class="bulk-actions-wrap bp-hide">
		<div class="bulk-actions select-wrap">
			<label for="user-messages-bulk-actions" class="bp-screen-reader-text">
				<?php esc_html_e( 'Select bulk action', 'bp-nouveau' )  ?>
			</label>
			<select id="user-messages-bulk-actions">
				<# for ( i in data ) { #>
					<option value="{{data[i].value}}">{{data[i].label}}</option>
				<# } #>
			</select>
			<span class="select-arrow" aria-hidden="true"></span>
		</div>
		<button class="messages-button bulk-apply" type="submit">
			<span class="dashicons dashicons-yes" aria-hidden="true"></span>
			<span class="bp-screen-reader-text"><?php esc_html_e( __( 'Apply', 'bp-nouveau' ) ); ?></span>
		</button>
	</div>
</script>

<script type="text/html" id="tmpl-bp-messages-thread">
	<div class="thread-cb">
		<label for="bp-message-thread-{{data.id}}">
			<input class="message-check" type="checkbox" name="message_ids[]" id="bp-message-thread-{{data.id}}" value="{{data.id}}">
			<span class="bp-screen-reader-text"><?php esc_html_e( 'Select this message', 'bp-nouveau' ); ?></span>
		</label>
	</div>
	<div class="thread-from">
		<a class="user-link" href="{{data.sender_link}}" title="{{data.sender_name}}" >
			<img class="avatar" src="{{data.sender_avatar}}" alt="{{data.sender_name}}<?php esc_attr_e(' profile picture', 'bp-nouveau'); ?>" />
			<span class="user-name">{{data.sender_name}}</span>
		</a>
	</div>
	<div class="thread-content" data-thread-id="{{data.id}}">
		<div class="thread-subject">
			<span class="thread-count">({{data.count}})</span>
			<span class="subject"><# print( data.subject ); #></span>
		</div>
		<p class="excerpt"><# print( data.excerpt ); #></p>
	</div>
	<div class="thread-date">
		<time datetime="{{data.date.toISOString()}}">{{data.display_date}}</time>
	</div>
</script>

<script type="text/html" id="tmpl-bp-messages-preview">
	<# if ( undefined !== data.content ) { #>

		<h4 class=" message-title preview-thread-title"><?php esc_html_e( 'Active conversation:', 'bp-nouveau' ); ?><span class="messages-title"> <# print( data.subject ); #></span></h4>
		<div class="preview-content">
			<header class="preview-pane-header">

				<# if ( undefined !== data.recipients ) { #>
					<dl class="thread-participants">
						<dt><?php esc_html_e( 'Participants:', 'bp-nouveau' ); ?></dt>
						<dd>
							<ul class="participants-list">
								<# for ( i in data.recipients ) { #>
									<li><a href="{{data.recipients[i].user_link}}" title="{{data.recipients[i].user_name}}"><img class="avatar mini" src="{{data.recipients[i].avatar}}" alt="{{data.recipients[i].user_name}}<?php esc_attr_e(' profile picture', 'bp-nouveau'); ?>" /></a></li>
								<# } #>
							</ul>
						</dd>
					</dl>
				<# } #>

				<div class="actions">

					<button type="button" class="message-action-delete bp-icons" data-bp-action="delete" title="<?php esc_attr_e( 'Delete conversation.', 'bp-nouveau' );?>">
						<span class="bp-screen-reader-text"><?php esc_html_e( 'Delete conversation.', 'bp-nouveau' );?></span>
					</button>

					<# if ( undefined !== data.star_link ) { #>

						<# if ( false !== data.is_starred ) { #>
							<a role="button" class="message-action-unstar bp-icons" href="{{data.star_link}}" data-bp-action="unstar" aria-pressed="true" title="<?php esc_attr_e( 'Unstar Conversation', 'bp-nouveau' );?>">
								<span class="bp-screen-reader-text"><?php esc_html_e( 'Unstar Conversation', 'bp-nouveau' );?></span>
							</a>
						<# } else { #>
							<a role="button" class="message-action-star bp-icons" href="{{data.star_link}}" data-bp-action="star" aria-pressed="false" title="<?php esc_attr_e( 'Star Conversation', 'bp-nouveau' );?>">
								<span class="bp-screen-reader-text"><?php esc_html_e( 'Star Conversation', 'bp-nouveau' );?></span>
							</a>
						<# } #>

					<# } #>

					<a href="#view/{{data.id}}" class="message-action-view bp-icons" title="<?php esc_attr_e( 'View Full Conversation.', 'bp-nouveau' );?>">
						<span class="bp-screen-reader-text"><?php esc_html_e( 'View Full conversation.', 'bp-nouveau' );?></span>
					</a>
				</div>
			</header>

			<div class='preview-message'>
				<# print( data.content ) #>
			</div>
		</div>
	<# } #>
</script>

<script type="text/html" id="tmpl-bp-messages-single-header">
	<h4 id="message-subject" class="message-title single-thread-title"><# print( data.subject ); #></h4>
	<header class="single-message-thread-header">
		<# if ( undefined !== data.recipients ) { #>
			<dl class="thread-participants">
				<dt><?php esc_html_e( 'Participants:', 'bp-nouveau' ); ?></dt>
				<dd>
					<ul class="participants-list">
						<# for ( i in data.recipients ) { #>
							<li><a href="{{data.recipients[i].user_link}}" title="{{data.recipients[i].user_name}}"><img class="avatar mini" src="{{data.recipients[i].avatar}}"  alt="{{data.recipients[i].user_name}}<?php esc_attr_e(' profile picture', 'bp-nouveau'); ?>" /></a></li>
						<# } #>
					</ul>
				</dd>
			</dl>
		<# } #>

		<div class="actions">

			<button type="button" class="message-action-delete bp-icons" data-bp-action="delete" title="<?php esc_attr_e( 'Delete conversation.', 'bp-nouveau' );?>">
				<span class="bp-screen-reader-text"><?php esc_html_e( 'Delete conversation.', 'bp-nouveau' );?></span>
			</button>

			<?php bp_nouveau_messages_hook( 'after', 'thread_header_actions' ); ?>
		</div>
	</header>
</script>

<script type="text/html" id="tmpl-bp-messages-single-list">
	<div class="message-metadata">
		<?php bp_nouveau_messages_hook( 'before', 'meta' ); ?>

		<a href="{{data.sender_link}}" title="{{data.sender_name}}" class="user-link">
			<img class="avatar" src="{{data.sender_avatar}}" alt="{{data.sender_name}}<?php esc_attr_e(' profile picture', 'bp-nouveau'); ?>" />
			<strong>{{data.sender_name}}</strong>
		</a>

		<time datetime="{{data.date.toISOString()}}" class="activity">{{data.display_date}}</time>

		<div class="actions">
			<# if ( undefined !== data.star_link ) { #>

				<?php $test = 1; ?>

				<# if ( false !== data.is_starred ) { #>
					<button type="button" class="message-action-unstar bp-icons" data-bp-star-link="{{data.star_link}}" data-bp-action="unstar" title="<?php esc_attr_e( 'Unstar Message', 'bp-nouveau' );?>">
						<span class="bp-screen-reader-text"><?php esc_html_e( 'Unstar Message', 'bp-nouveau' );?></span>
					</button>
				<# } else { #>
					<button type="button" class="message-action-star bp-icons" data-bp-star-link="{{data.star_link}}" data-bp-action="star" title="<?php esc_attr_e( 'Star Message', 'bp-nouveau' );?>">
						<span class="bp-screen-reader-text"><?php esc_html_e( 'Star Message', 'bp-nouveau' );?></span>
					</button>
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

	<div id="bp-message-thread-header" class="message-thread-header"></div>

	<?php bp_nouveau_messages_hook( 'before', 'thread_list' ); ?>

	<ul id="bp-message-thread-list"></ul>

	<?php bp_nouveau_messages_hook( 'after', 'thread_list' ); ?>

	<?php bp_nouveau_messages_hook( 'before', 'thread_reply' ); ?>

	<form id="send-reply" class="standard-form send-reply">
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
