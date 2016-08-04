<?php
/**
 * BP Nouveau Group's avatar template.
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */

if ( 'upload-image' == bp_get_avatar_admin_step() ) : ?>
	<?php if ( bp_is_group_create() ) : ?>

		<h2 class="bp-screen-title <?php if(bp_is_group_create()) echo 'creation-step-name'; ?>">
			<?php _e( 'Group Avatar', 'bp-nouveau' ); ?>
		</h2>

		<div class="left-menu">

			<?php bp_new_group_avatar(); ?>

		</div><!-- .left-menu -->

		<div class="main-column">
	<?php endif; ?>

			<p><?php _e("Upload an image to use as a profile photo for this group. The image will be shown on the main group page, and in search results.", 'bp-nouveau' ); ?></p>

			<p>
				<label for="file" class="bp-screen-reader-text"><?php _e( 'Select an image', 'bp-nouveau' ); ?></label>
				<input type="file" name="file" id="file" />
				<input type="submit" name="upload" id="upload" value="<?php esc_attr_e( 'Upload Image', 'bp-nouveau' ); ?>" />
				<input type="hidden" name="action" id="action" value="bp_avatar_upload" />
			</p>

	<?php if ( bp_is_group_create() ) : ?>
			<p><?php _e( 'To skip the group profile photo upload process, hit the "Next Step" button.', 'bp-nouveau' ); ?></p>
		</div><!-- .main-column -->

	<?php elseif ( bp_get_group_has_avatar() ) : ?>

		<p><?php _e( "If you'd like to remove the existing group profile photo but not upload a new one, please use the delete group profile photo button.", 'bp-nouveau' ); ?></p>

		<?php bp_button( array( 'id' => 'delete_group_avatar', 'component' => 'groups', 'wrapper_id' => 'delete-group-avatar-button', 'link_class' => 'edit', 'link_href' => bp_get_group_avatar_delete_link(), 'link_title' => __( 'Delete Group Profile Photo', 'bp-nouveau' ), 'link_text' => __( 'Delete Group Profile Photo', 'bp-nouveau' ) ) ); ?>

	<?php endif; ?>

	<?php
	/**
	 * Load the Avatar UI templates
	 *
	 * @since  2.3.0
	 */
	bp_avatar_get_templates(); ?>

	<?php if ( ! bp_is_group_create() ) wp_nonce_field( 'bp_avatar_upload' ); ?>

<?php endif;

if ( 'crop-image' == bp_get_avatar_admin_step() ) : ?>

	<h4><?php _e( 'Crop Group Profile Photo', 'bp-nouveau' ); ?></h4>

	<img src="<?php bp_avatar_to_crop(); ?>" id="avatar-to-crop" class="avatar" alt="<?php esc_attr_e( 'Profile photo to crop', 'bp-nouveau' ); ?>" />

	<div id="avatar-crop-pane">
		<img src="<?php bp_avatar_to_crop(); ?>" id="avatar-crop-preview" class="avatar" alt="<?php esc_attr_e( 'Profile photo preview', 'bp-nouveau' ); ?>" />
	</div>

	<input type="submit" name="avatar-crop-submit" id="avatar-crop-submit" value="<?php esc_attr_e( 'Crop Image', 'bp-nouveau' ); ?>" />

	<input type="hidden" name="image_src" id="image_src" value="<?php bp_avatar_to_crop_src(); ?>" />
	<input type="hidden" id="x" name="x" />
	<input type="hidden" id="y" name="y" />
	<input type="hidden" id="w" name="w" />
	<input type="hidden" id="h" name="h" />

	<?php if ( ! bp_is_group_create() ) wp_nonce_field( 'bp_avatar_cropstore' ); ?>

<?php endif;
