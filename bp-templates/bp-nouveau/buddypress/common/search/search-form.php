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

		<label for="dir-groups-search" class="bp-screen-reader-text"><?php bp_nouveau_search_default_text( '', false ); ?></label>

		<input id="<?php bp_nouveau_search_selector_id( 'search' ) ;?>" name="<?php bp_nouveau_search_selector_name() ;?>" placeholder="<?php bp_nouveau_search_default_text(); ?>" type="search" />

		<button type="submit" id="<?php bp_nouveau_search_selector_id( 'search-submit' ) ;?>" class="nouveau-search-submit" name="<?php bp_nouveau_search_selector_name( 'search_submit' ); ?>">
			<span class="dashicons dashicons-search" aria-hidden="true"></span>
			<span id="button-text" class="bp-screen-reader-text"><?php bp_nouveau_search_default_text( '', false ); ?></span>
		</button>

	</form>
</div>

