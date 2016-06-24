<?php
/**
* BP Object search form
*
* @since 1.0.0
*
* @package BP Nouveau
*/
?>

<li role="search" data-bp-search="<?php bp_nouveau_search_object_data_attr() ;?>">

	<form action="" method="get" id="<?php bp_nouveau_search_object_id( 'search-form' ) ;?>">
		<label for="<?php bp_nouveau_search_object_id( 'search' ) ;?>">
			<input type="search" name="<?php bp_nouveau_search_object_name( 'search' ) ;?>" id="<?php bp_nouveau_search_object_id( 'search' ) ;?>" placeholder="<?php bp_nouveau_search_object_default_text(); ?>">
		</label>
		<button type="submit" id="<?php bp_nouveau_search_object_id( 'search-submit' ) ;?>" name="<?php bp_nouveau_search_object_name( 'search_submit' ) ;?>">
			<span class="dashicons dashicons-search"></span>
			<span class="bp-screen-reader-text"><?php bp_nouveau_search_object_default_text(); ?></span>
		</button>
	</form>

</li>
