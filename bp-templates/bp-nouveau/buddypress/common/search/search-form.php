<?php
/**
* BP Object search form
*
* @since 1.0.0
*
* @package BP Nouveau
*/
?>

<div class="<?php bp_nouveau_search_container_class(); ?> bp-search">
	<form action="" method="get" class="bp-dir-search-form" id="<?php bp_nouveau_search_selector_id( 'search-form' ) ;?>" role="search" data-bp-search="groups">

		<div class="bp-label-placeholder">
			<label for="<?php bp_nouveau_search_selector_id( 'search' ) ;?>"><?php bp_nouveau_search_default_text(); ?></label>

			<input id="<?php bp_nouveau_search_selector_id( 'search' ); ?>" name="<?php bp_nouveau_search_selector_name() ;?>" type="search" class="bp-label-in-here" />

			<button type="submit" id="<?php bp_nouveau_search_selector_id( 'search-submit' ) ;?>" class="nouveau-search-submit" name="<?php bp_nouveau_search_selector_name( 'search_submit' ); ?>">
				<span class="dashicons dashicons-search" aria-hidden="true"></span>
				<span id="button-text" class="bp-screen-reader-text"><?php _e( 'Search', 'bp-nouveau' ); ?></span>
			</button>
		</div>

	</form>
</div>
