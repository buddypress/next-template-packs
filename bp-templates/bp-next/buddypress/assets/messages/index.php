<?php
/**
 * BP Next Messages main template.
 *
 * This template is used to inject the BuddyPress Backbone views
 * dealing with user's private messages.
 *
 * @since 1.0.0
 *
 * @package BP Next
 */
?>
<div class="bp-messages-content"></div>
<div class="bp-messages-feedback"></div>

<script type="text/html" id="tmpl-bp-messages-form">
	<?php
	/**
	 * Fires before the display of message compose content.
	 *
	 * @since 1.1.0
	 */
	do_action( 'bp_before_messages_compose_content' ); ?>

	<label for="send-to-input"><?php esc_html_e( 'Send @Username', 'bp-next' ); ?></label>
	<input type="text" name="send_to" class="send-to-input" id="send-to-input" />

	<label for="subject"><?php _e( 'Subject', 'bp-next' ); ?></label>
	<input type="text" name="subject" id="subject"/>

	<div id="bp-messages-content"></div>

	<?php
	/**
	 * Fires after the display of message compose content.
	 *
	 * @since 1.0.0
	 */
	do_action( 'bp_after_messages_compose_content' ); ?>

	<div class="submit">
		<input type="button" id="bp-messages-reset" class="button bp-secondary-action" value="<?php esc_attr_e( 'Cancel', 'bp-next' ); ?>"/>
		<input type="button" id="bp-messages-send" class="button bp-primary-action" value="<?php esc_attr_e( 'Send', 'bp-next' ); ?>"/>
	</div>
</script>

<script type="text/html" id="tmpl-bp-messages-editor">
	<?php
	// Temporarly filter the editor
	add_filter( 'mce_buttons', 'bp_next_mce_buttons', 10, 1 );

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
	remove_filter( 'mce_buttons', 'bp_next_mce_buttons', 10, 1 ); ?>
</script>

<script type="text/html" id="tmpl-bp-messages-thread">
	<div class="thread-cb">
		<label for="bp-message-thread-{{data.id}}">
			<input type="checkbox" name="message_ids[]" id="bp-message-thread-{{data.id}}" class="message-check" value="{{data.id}}">
			<span class="bp-screen-reader-text"><?php esc_html_e( 'Select this message', 'bp-next' ); ?></span>
		</label>
	</div>

	<# if ( undefined !== data.star_link ) { #>
		<div class="thread-star">
			<# if ( false !== data.is_starred ) { #>
				<a href="{{data.star_link}}" class="message-action-unstar" title="<?php esc_attr_e( 'Unstar', 'bp-next' );?>">
					<span class="bp-screen-reader-text"><?php esc_html_e( 'Unstar', 'bp-next' );?></span>
				</a>
			<# } else { #>
				<a href="{{data.star_link}}" class="message-action-star" title="<?php esc_attr_e( 'Star', 'bp-next' );?>">
					<span class="bp-screen-reader-text"><?php esc_html_e( 'Star/Unstar', 'bp-next' );?></span>
				</a>
			<# } #>
		</div>
	<# } #>

	<div class="thread-content <# if ( undefined === data.star_link ) { #> star-disabled <# }#>" data-thread-id="{{data.id}}">
		<div class="thread-from">
			<a href="{{data.sender_link}}" title="{{data.sender_name}}" class="user-link">
				<img src="{{data.sender_avatar}}" width="32px" height="32px" class="avatar">
				{{data.sender_name}}
			</a>
		</div>
		<div class="thread-subject">
			<span class="thread-count">({{data.count}})</span>
			<span class="subject">{{data.subject}}</span>
			<span class="excerpt">{{data.excerpt}}</span>
		</div>
		<div class="thread-date">
			<time datetime="{{data.date.toISOString()}}">{{data.display_date}}</time>
		</div>
	</div>
	<div class="clear"></div>
</script>

<script type="text/html" id="tmpl-bp-messages-preview">
	<# if ( undefined !== data.content ) { #>
		<h4><?php esc_html_e( 'Active conversation:', 'bp-next' ); ?> {{data.subject}}</h4>
		<div class="preview-content">
			<# print( data.content ) #>
			<div class="actions">
				<# if ( undefined !== data.count && 1 < data.count ) { #>
					<a href="#view/{{data.id}}" class="button" title="<?php esc_attr_e( 'View conversation.', 'bp-next' );?>">
						<?php esc_html_e( 'View full conversation.', 'bp-next' );?>
					</a>
				<# } #>
			</div>
		</div>
	<# } #>
</script>
