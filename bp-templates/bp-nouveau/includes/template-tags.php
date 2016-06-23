<?php
/**
 * Common template tags
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Add a class to style the template notice
 *
 * @since  1.0.0
 *
 * @return string Css class Output
 */
function bp_nouveau_template_message_type() {
	echo sanitize_html_class( bp_nouveau_get_template_message_type() );
}

	/**
	 * Get the template notice type
	 *
	 * @since 1.0.0
	 *
	 * @return string the type of the notice. Defaults to error
	 */
	function bp_nouveau_get_template_message_type() {
		$bp   = buddypress();
		$type = 'error';

		if ( ! empty( $bp->template_message_type ) ) {
			$type = $bp->template_message_type;
		}

		return $type;
	}

/**
 * Checks if a template notice is set
 *
 * @since 1.0.0
 *
 * @return bool True if a template notice is set. False otherwise.
 */
function bp_nouveau_has_template_message() {
	$bp = buddypress();

	if ( empty( $bp->template_message ) ) {
		return false;
	}

	return true;
}

/**
 * Displays a template notice.
 *
 * @since  1.0.0
 *
 * @return string HTML Output.
 */
function bp_nouveau_template_message() {
	echo bp_nouveau_get_template_message();
}

	/**
	 * Get the template notice and make sure core filter is applyed.
	 *
	 * @since  1.0.0
	 *
	 * @return string HTML Output.
	 */
	function bp_nouveau_get_template_message() {
		/**
		 * Filters the 'template_notices' feedback message content.
		 *
		 * @since 1.5.5 (BuddyPress)
		 *
		 * @param string $template_message Feedback message content.
		 * @param string $type             The type of message being displayed.
		 *                                 Either 'updated' or 'error'.
		 */
		return apply_filters( 'bp_core_render_message_content', buddypress()->template_message, bp_nouveau_get_template_message_type() );
	}

/**
 * Template tag to display feedback notices to users, if there are to display
 *
 * @since 1.0.0
 */
function bp_nouveau_template_notices() {
	if ( bp_nouveau_has_template_message() ) {
		bp_get_template_part( 'common/notices/template-notices' );

		/**
		 * Fires after the display of any template_notices feedback messages.
		 *
		 * @since 1.1.0 (BuddyPress)
		 */
		do_action( 'bp_core_render_message' );
	}

	/**
	 * Fires towards the top of template pages for notice display.
	 *
	 * @since 1.0.0 (BuddyPress)
	 */
	do_action( 'template_notices' );
}

/**
 * Template tag to wrap the before component loop
 *
 * @since  1.0.0
 */
function bp_nouveau_before_loop() {
	$component = bp_current_component();

	/**
	 * Fires before the start of the component loop.
	 *
	 * @since 1.2.0
	 */
	do_action( "bp_before_{$component}_loop" );
}

/**
 * Template tag to wrap the after component loop
 *
 * @since  1.0.0
 */
function bp_nouveau_after_loop() {
	$component = bp_current_component();

	/**
	 * Fires after the finish of the component loop.
	 *
	 * @since 1.2.0
	 */
	do_action( "bp_after_{$component}_loop" );
}

/**
 * Pagination for loops
 *
 * @since 1.0.0
 */
function bp_nouveau_pagination( $position = null ) {
	$component = bp_current_component();

	$screen = 'dir';
	if ( bp_is_user() ) {
		$screen = 'user';
	}

	switch( $component ) {

		case 'blogs' :

			/**
			 * bp_get_blogs_pagination_count() doesn't exist in BuddyPress
			 * @see https://buddypress.trac.wordpress.org/ticket/7118
			 *
			 * So we're doing this workaround for now, and will update this part
			 * when/if 7118 is fixed.
			 */
			ob_start();
			bp_blogs_pagination_count();
			$pag_count   = ob_get_clean();
			$pag_links   = bp_get_blogs_pagination_links();
			$top_hook    = 'bp_before_directory_blogs_list';
			$bottom_hook = 'bp_after_directory_blogs_list';

		break;

		case 'members' :
		case 'friends' :

			$pag_count   = bp_get_members_pagination_count();
			$pag_links   = bp_get_members_pagination_links();
			$top_hook    = 'bp_before_directory_members_list';
			$bottom_hook = 'bp_after_directory_members_list';

		break;

		case 'groups' :

			$pag_count   = bp_get_groups_pagination_count();
			$pag_links   = bp_get_groups_pagination_links();
			$top_hook    = 'bp_before_directory_groups_list';
			$bottom_hook = 'bp_after_directory_groups_list';

		break;
	}

	$count_class = sprintf( '%1$s-%2$s-count-%3$s', $component, $screen, $position );
	$links_class = sprintf( '%1$s-%2$s-links-%3$s', $component, $screen, $position );
	?>

	<?php if ( 'bottom' === $position && isset( $bottom_hook ) ) {
		/**
		 * Fires after the component directory list.
		 *
		 * @since 1.1.0
		 */
		do_action( $bottom_hook );
	};?>

	<div class="pagination <?php echo sanitize_html_class( $position ); ?>" data-bp-nav="pagination">

		<?php if ( $pag_count ) : ?>
			<div class="pag-count <?php echo sanitize_html_class( $count_class ); ?>">

				<p class="pag-data">
					<?php echo $pag_count; ?>
				</p>

			</div>
		<?php endif; ?>

		<?php if ( $pag_links ) : ?>
			<div class="pagination-links <?php echo sanitize_html_class( $links_class ); ?>">

				<p class="pag-data">
					<?php echo $pag_links; ?>
				</p>

			</div>
		<?php endif; ?>

	</div>

	<?php if ( 'top' === $position && isset( $top_hook ) ) {
		/**
		 * Fires before the component directory list.
		 *
		 * @since 1.1.0 (BuddyPress)
		 */
		do_action( $top_hook );
	};?>

	<?php
	return;
}

/** Template tags for the Directory navs **************************************/

function bp_nouveau_directory_type_tabs_class() {
	echo bp_nouveau_get_directory_type_tabs_class();
}

	function bp_nouveau_get_directory_type_tabs_class() {
		$class = sprintf( '%s-type-tabs', bp_current_component() );

		return sanitize_html_class( $class );
	}

function bp_nouveau_directory_list_class() {
	echo bp_nouveau_get_directory_list_class();
}

	function bp_nouveau_get_directory_list_class() {
		$class = sprintf( '%s-nav', bp_current_component() );

		return sanitize_html_class( $class );
	}

function bp_nouveau_directory_has_nav() {
	$bp_nouveau = bp_nouveau();

	$bp_nouveau->sorted_dir_nav = array_values( $bp_nouveau->directory_nav->get_primary() );

	if ( 0 === count( $bp_nouveau->sorted_dir_nav ) ) {
		unset( $bp_nouveau->sorted_dir_nav );

		return false;
	}

	$bp_nouveau->current_dir_nav_index = 0;
	return true;
}

function bp_nouveau_directory_nav_items() {
	$bp_nouveau = bp_nouveau();

	if ( isset( $bp_nouveau->sorted_dir_nav[ $bp_nouveau->current_dir_nav_index ] ) ) {
		return true;
	}

	$bp_nouveau->current_dir_nav_index = 0;
	unset( $bp_nouveau->current_dir_nav_item );

	return false;
}

function bp_nouveau_directory_nav_item() {
	$bp_nouveau = bp_nouveau();

	$bp_nouveau->current_dir_nav_item   = $bp_nouveau->sorted_dir_nav[ $bp_nouveau->current_dir_nav_index ];
	$bp_nouveau->current_dir_nav_index += 1;
}

function bp_nouveau_directory_nav_id() {
	echo bp_nouveau_get_directory_nav_id();
}

	function bp_nouveau_get_directory_nav_id() {
		$nav_item = bp_nouveau()->current_dir_nav_item;
		$id = sprintf( '%1$s-%2$s', $nav_item->component, $nav_item->slug );

		return esc_attr( $id );
	}

function bp_nouveau_directory_nav_classes() {
	echo bp_nouveau_get_directory_nav_classes();
}

	function bp_nouveau_get_directory_nav_classes() {
		$nav_item = bp_nouveau()->current_dir_nav_item;

		if ( empty( $nav_item->li_class ) ) {
			return;
		}

		$classes = array_map( 'sanitize_html_class', (array) $nav_item->li_class );

		return join( ' ', $classes );
	}

function bp_nouveau_directory_nav_scope() {
	echo bp_nouveau_get_directory_nav_scope();
}

	function bp_nouveau_get_directory_nav_scope() {
		$nav_item = bp_nouveau()->current_dir_nav_item;

		return esc_attr( $nav_item->slug );
	}

function bp_nouveau_directory_nav_object() {
	echo bp_nouveau_get_directory_nav_object();
}

	function bp_nouveau_get_directory_nav_object() {
		$nav_item = bp_nouveau()->current_dir_nav_item;

		return esc_attr( $nav_item->component );
	}

function bp_nouveau_directory_nav_link() {
	echo bp_nouveau_get_directory_nav_link();
}

	function bp_nouveau_get_directory_nav_link() {
		$nav_item = bp_nouveau()->current_dir_nav_item;

		return esc_url( $nav_item->link );
	}

function bp_nouveau_directory_nav_title() {
	echo bp_nouveau_get_directory_nav_title();
}

	function bp_nouveau_get_directory_nav_title() {
		$nav_item = bp_nouveau()->current_dir_nav_item;

		return esc_attr( $nav_item->title );
	}

function bp_nouveau_directory_nav_text() {
	echo bp_nouveau_get_directory_nav_text();
}

	function bp_nouveau_get_directory_nav_text() {
		$nav_item = bp_nouveau()->current_dir_nav_item;

		return esc_html( $nav_item->text );
	}

function bp_nouveau_directory_nav_has_count() {
	$nav_item = bp_nouveau()->current_dir_nav_item;

	return false !== $nav_item->count;
}

function bp_nouveau_directory_nav_count() {
	echo bp_nouveau_get_directory_nav_count();
}

	function bp_nouveau_get_directory_nav_count() {
		$nav_item = bp_nouveau()->current_dir_nav_item;

		return esc_attr( $nav_item->count );
	}

/** Template tags for the directory filters **********************************/

function bp_nouveau_directory_filter_container_id() {
	echo bp_nouveau_get_directory_filter_container_id();
}

	function bp_nouveau_get_directory_filter_container_id() {
		$ids = array(
			'members'  => 'members-order-select',
			'activity' => 'activity-filter-select',
			'groups'   => 'groups-order-select',
			'blogs'    => 'blogs-order-select',
		);

		$component = bp_current_component();

		if ( isset( $ids[ $component ] ) ) {
			return esc_attr( apply_filters( 'bp_nouveau_get_directory_filter_container_id', $ids[ $component ] ) );
		}
	}

function bp_nouveau_directory_filter_id() {
	echo bp_nouveau_get_directory_filter_id();
}

	function bp_nouveau_get_directory_filter_id() {
		$ids = array(
			'members'  => 'members-order-by',
			'activity' => 'activity-filter-by',
			'groups'   => 'groups-order-by',
			'blogs'    => 'blogs-order-by',
		);

		$component = bp_current_component();

		if ( isset( $ids[ $component ] ) ) {
			return esc_attr( apply_filters( 'bp_nouveau_get_directory_filter_id', $ids[ $component ] ) );
		}
	}

function bp_nouveau_directory_filter_label() {
	echo bp_nouveau_get_directory_filter_label();
}

	function bp_nouveau_get_directory_filter_label() {
		$component = bp_current_component();

		$label = __( 'Order By:', 'bp-nouveau' );

		if ( 'activity' === $component ) {
			$label = __( 'Show:', 'bp-nouveau' );
		}

		return esc_html( apply_filters( 'bp_nouveau_get_directory_filter_label', $label ) );
	}

function bp_nouveau_directory_filter_component() {
	echo esc_attr( bp_current_component() );
}

function bp_nouveau_filter_options() {
	echo bp_nouveau_get_filter_options();
}

	function bp_nouveau_get_filter_options() {
		$filters = bp_nouveau_get_component_filters();
		$output = '';

		foreach ( $filters as $key => $value ) {
			$output .= sprintf( '<option value="%1$s">%2$s</option>%3$s',
				esc_attr( $key ),
				esc_html( $value ),
				"\n"
			);
		}

		return $output;
	}
