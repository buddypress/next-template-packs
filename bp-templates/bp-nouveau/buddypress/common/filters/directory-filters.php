<?php
/**
 * BP Nouveau Component's directory filters template.
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */
?>

<ul id="dir-filters" class="dir-component-filters clearfix">
	<li id="<?php bp_nouveau_directory_filter_container_id(); ?>" class="last filter">
		<label for="<?php bp_nouveau_directory_filter_id(); ?>">
			<span class="bp-screen-reader-text"><?php bp_nouveau_directory_filter_label(); ?></span>
		</label>
		<select id="<?php bp_nouveau_directory_filter_id(); ?>" data-bp-filter="<?php bp_nouveau_directory_filter_component(); ?>">

			<?php bp_nouveau_filter_options(); ?>

		</select>
	</li>
</ul>
