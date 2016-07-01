<?php
/**
 * BuddyPress - Groups Create
 *
 * @package BuddyPress
 * @subpackage bp-nouveau
 */

bp_nouveau_groups_create_hook( 'before', 'page' ); ?>

<div id="buddypress">

	<?php bp_nouveau_groups_create_hook( 'before', 'content_template' ); ?>

	<form action="<?php bp_group_creation_form_action(); ?>" method="post" id="create-group-form" class="standard-form" enctype="multipart/form-data">

		<?php bp_nouveau_groups_create_hook( 'before' ); ?>

		<div class="item-list-tabs no-ajax" id="group-create-tabs" role="navigation">
			<ul>

				<?php bp_group_creation_tabs(); ?>

			</ul>
		</div>

		<?php bp_nouveau_template_notices(); ?>

		<div class="item-body" id="group-create-body">

			<?php bp_nouveau_group_creation_screen(); ?>

		</div><!-- .item-body -->

		<?php bp_nouveau_groups_create_hook( 'after' ); ?>

	</form>

	<?php bp_nouveau_groups_create_hook( 'after', 'content_template' ); ?>

</div>

<?php bp_nouveau_groups_create_hook( 'after', 'page' );
