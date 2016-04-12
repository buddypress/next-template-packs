<div id="<?php esc_attr_e( bp_current_component() ); ?>-dir-search" class="dir-search" role="search">
	<form action="" method="get" id="search-<?php  esc_attr_e( bp_current_component() ); ?>-form">
		<label for="<?php bp_search_input_name(); ?>" class="bp-screen-reader-text"><?php bp_search_placeholder(); ?></label>
			<input type="text" name="<?php esc_attr_e( bp_core_get_component_search_query_arg() ); ?>" id="<?php bp_search_input_name(); ?>" placeholder="<?php bp_search_placeholder(); ?>" />
		<input type="submit" id="<?php esc_attr_e(str_replace('_', '-', bp_get_search_input_name() ) ); ?>-submit" name="<?php bp_search_input_name(); ?>_submit" value="<?php esc_html_e( 'Search', 'buddypress' ); ?>" />
	</form>
</div><!-- #<?php esc_attr_e( bp_current_component() ); ?>-dir-search -->

