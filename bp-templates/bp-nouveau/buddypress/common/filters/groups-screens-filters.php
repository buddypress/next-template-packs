<?php
/**
* BP Nouveau Groups screens filters
*
* @since 1.0.0
*
* @package BP Nouveau
*/
?>
<ul id="comp-filters" class="component-filters clearfix">
		<li id="<?php bp_nouveau_filter_container_id(); ?>" class="last filter">
			<label for="<?php bp_nouveau_filter_id(); ?>" class="bp-screen-reader-text">
				<span ><?php bp_nouveau_filter_label(); ?></span>
			</label>
			<div class="select-wrap">
				<select id="<?php bp_nouveau_filter_id(); ?>" data-bp-filter="<?php  bp_nouveau_filter_component(); ?>">

					<?php bp_nouveau_filter_options(); ?>

				</select>
				<span class="select-arrow"></span>
			</div>
		</li>
</ul>
