<li class="dir-search <?php echo esc_attr( bp_current_component() ); ?>-dir-search" role="search" data-bp-search="<?php echo esc_attr( bp_current_component() ); ?>">
	<form action="" method="get" id="search-<?php echo esc_attr( bp_current_component() ); ?>-form">
		<label for="<?php echo esc_attr( bp_current_component() ); ?>_search" class="bp-screen-reader-text"><?php echo esc_html( bp_get_search_default_text( bp_current_component() ) ); ?></label>
			<input type="text" name="<?php echo esc_attr( bp_core_get_component_search_query_arg( bp_current_component() ) ); ?>" id="<?php echo esc_attr( bp_current_component() ); ?>_search" placeholder="<?php echo esc_attr( bp_get_search_default_text( bp_current_component() ) ); ?>" />
			<input type="submit" id="<?php echo esc_attr( bp_current_component() ); ?>_search_submit" name="<?php echo esc_attr( bp_current_component() ); ?>_search_submit" value="<?php esc_html_e( 'Search', 'buddypress' ); ?>" />
	</form>
</li><!-- #<?php esc_attr_e( bp_current_component() ); ?>-dir-search -->

