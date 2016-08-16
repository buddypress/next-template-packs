<?php
/**
* BP Object search form
*
* @since 1.0.0
*
* @package BP Nouveau
*/
?>

<li class="<?php bp_nouveau_search_container_class(); ?> bp-search" role="search" data-bp-search="<?php bp_nouveau_search_object_data_attr() ;?>">

	<form action="" method="get" id="<?php bp_nouveau_search_selector_id( 'search-form' ) ;?>">
		<label for="<?php bp_nouveau_search_selector_id( 'search' ) ;?>">
			<input type="search" id="<?php bp_nouveau_search_selector_id( 'search' ) ;?>" name="<?php bp_nouveau_search_selector_name() ;?>" placeholder="<?php bp_nouveau_search_default_text(); ?>">
		</label>
		<button type="submit" id="<?php bp_nouveau_search_selector_id( 'search-submit' ) ;?>" class="nouveau-search-submit" name="<?php bp_nouveau_search_selector_name( 'search_submit' ) ;?>">
			<span class="dashicons dashicons-search"></span>
			<span class="bp-screen-reader-text"><?php bp_nouveau_search_default_text( '', false ); ?></span>
		</button>
	</form>

</li>
