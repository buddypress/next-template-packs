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
 * Add classes to style the template notice/feedback message
 *
 * @since  1.0.0
 *
 * @return string Css class Output
 */
function bp_nouveau_template_message_classes() {
	$classes = array( 'bp-feedback', 'bp-messages' );

	if ( ! empty( bp_nouveau()->template_message['message'] ) ) {
		$classes[] = 'bp-template-notice';
	}

	$classes[] = bp_nouveau_get_template_message_type();
	echo join( ' ', array_map( 'sanitize_html_class', $classes ) );
}

	/**
	 * Get the template notice/feedback message type
	 *
	 * @since 1.0.0
	 *
	 * @return string the type of the notice. Defaults to error
	 */
	function bp_nouveau_get_template_message_type() {
		$bp_nouveau = bp_nouveau();
		$type       = 'error';

		if ( ! empty( $bp_nouveau->template_message['type'] ) ) {
			$type = $bp_nouveau->template_message['type'];
		} elseif ( ! empty( $bp_nouveau->user_feedback['type'] ) ) {
			$type = $bp_nouveau->user_feedback['type'];
		}

		return $type;
	}

/**
 * Checks if a template notice/feedback message is set
 *
 * @since 1.0.0
 *
 * @return bool True if a template notice is set. False otherwise.
 */
function bp_nouveau_has_template_message() {
	$bp_nouveau = bp_nouveau();

	if ( empty( $bp_nouveau->template_message['message'] ) && empty( $bp_nouveau->user_feedback ) ) {
		return false;
	}

	return true;
}

/**
 * Checks if the template notice/feedback message needs a dismiss button
 *
 * @since 1.0.0
 *
 * @return bool True if a template notice needs a dismiss button. False otherwise.
 */
function bp_nouveau_has_dismiss_button() {
	$bp_nouveau = bp_nouveau();

	if ( ! empty( $bp_nouveau->template_message['message'] ) || ! empty( $bp_nouveau->user_feedback['dismiss'] ) ) {
		return true;
	}

	return false;
}

/**
 * Ouptut the dismiss type.
 *
 * @since 1.0.0
 *
 * @return string The dismiss type.
 */
function bp_nouveau_dismiss_button_type() {
	$bp_nouveau = bp_nouveau();
	$type = 'clear';

	if ( ! empty( $bp_nouveau->user_feedback['dismiss'] ) ) {
		$type = $bp_nouveau->user_feedback['dismiss'];
	}

	echo esc_attr( $type );
}

/**
 * Displays a template notice/feedback message.
 *
 * @since  1.0.0
 *
 * @return string HTML Output.
 */
function bp_nouveau_template_message() {
	echo bp_nouveau_get_template_message();
}

	/**
	 * Get the template notice/feedback message and make sure core filter is applied.
	 *
	 * @since  1.0.0
	 *
	 * @return string HTML Output.
	 */
	function bp_nouveau_get_template_message() {
		$bp_nouveau = bp_nouveau();

		if ( ! empty( $bp_nouveau->user_feedback['message'] ) ) {
			$user_feedback = $bp_nouveau->user_feedback['message'];
			foreach ( array( 'wp_kses_data', 'wp_unslash', 'wptexturize', 'convert_smilies', 'convert_chars' ) as $filter ) {
				$user_feedback = call_user_func( $filter, $user_feedback );
			}

			return $user_feedback;
		} elseif ( ! empty( $bp_nouveau->template_message['message'] ) ) {
			/**
			 * Filters the 'template_notices' feedback message content.
			 *
			 * @since 1.5.5 (BuddyPress)
			 *
			 * @param string $template_message Feedback message content.
			 * @param string $type             The type of message being displayed.
			 *                                 Either 'updated' or 'error'.
			 */
			return apply_filters( 'bp_core_render_message_content', $bp_nouveau->template_message['message'], bp_nouveau_get_template_message_type() );
		}
	}

/**
 * Template tag to display feedback notices to users, if there are to display
 *
 * @since 1.0.0
 *
 * @return HTML Output.
 */
function bp_nouveau_template_notices() {
	$bp         = buddypress();
	$bp_nouveau = bp_nouveau();

	if ( ! empty( $bp->template_message ) ) {
		// Clone BuddyPress template message to avoid altering it.
		$template_message = array( 'message' => $bp->template_message );

		if ( ! empty( $bp->template_message_type ) ) {
			$template_message['type'] = $bp->template_message_type;
		}

		$bp_nouveau->template_message = $template_message;


		bp_get_template_part( 'common/notices/template-notices' );

		// Reset just after rendering it.
		$bp_nouveau->template_message = array();

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
 * Displays a feedback message to the user.
 *
 * @since 1.0.0
 *
 * @param  string  $feedback_id The ID of the message to display
 * @return string  HTML Output.
 */
function bp_nouveau_user_feedback( $feedback_id = '' ) {
	if ( ! isset( $feedback_id ) ) {
		return '';
	}

	$bp_nouveau = bp_nouveau();
	$feedback   = bp_nouveau_get_user_feedback( $feedback_id );

	if ( ! $feedback ) {
		return;
	}

	if ( ! empty( $feedback['before'] ) ) {
		do_action( $feedback['before'] );
	}

	$bp_nouveau->user_feedback = $feedback;

	/**
	 * Filter here if you wish to use a different templates than the notice one.
	 *
	 * @since 1.0.0
	 *
	 * @param string path to your template part.
	 */
	bp_get_template_part( apply_filters( 'bp_nouveau_user_feedback_template', 'common/notices/template-notices' ) );

	if ( ! empty( $feedback['after'] ) ) {
		do_action( $feedback['after'] );
	}

	// Reset the feedback message.
	$bp_nouveau->user_feedback =array();
}

/**
 * Template tag to wrap the before component loop
 *
 * @since  1.0.0
 */
function bp_nouveau_before_loop() {
	$component = bp_current_component();

	if ( bp_is_group() ) {
		$component = bp_current_action();
	}

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

	if ( bp_is_group() ) {
		$component = bp_current_action();
	}

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

			$pag_count   = bp_get_blogs_pagination_count();
			$pag_links   = bp_get_blogs_pagination_links();
			$top_hook    = 'bp_before_directory_blogs_list';
			$bottom_hook = 'bp_after_directory_blogs_list';
			$page_arg    = $GLOBALS['blogs_template']->pag_arg;

		break;

		case 'members' :
		case 'friends' :

			$pag_count   = bp_get_members_pagination_count();
			$pag_links   = bp_get_members_pagination_links();
			$top_hook    = 'bp_before_directory_members_list';
			$bottom_hook = 'bp_after_directory_members_list';
			$page_arg    = $GLOBALS['members_template']->pag_arg;

		break;

		case 'groups' :

			$pag_count   = bp_get_groups_pagination_count();
			$pag_links   = bp_get_groups_pagination_links();
			$top_hook    = 'bp_before_directory_groups_list';
			$bottom_hook = 'bp_after_directory_groups_list';
			$page_arg    = $GLOBALS['groups_template']->pag_arg;

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

	<div class="pagination <?php echo sanitize_html_class( $position ); ?>" data-bp-pagination="<?php echo esc_attr( $page_arg ); ?>">

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

/** Template tags for the object search **************************************/

/**
 * Get the search primary object
 *
 * @since 1.0.0
 *
 * @param  string $object The primary object.. Optionnal.
 * @return string The primary object.
 */
function bp_nouveau_get_search_primary_object( $object = '' ) {
	if ( bp_is_user() ) {
		$object = 'member';
	} elseif ( bp_is_group() ) {
		$object = 'group';
	} elseif ( bp_is_directory() ) {
		$object = 'dir';
	} else {
		$object = apply_filters( 'bp_nouveau_get_search_primary_object', $object );
	}

	return $object;
}

/**
 * Get The list of search objects (Primary + secondary)
 *
 * @since 1.0.0
 *
 * @param  array $objects The list of objects. Optionnal.
 * @return array The list of objects.
 */
function bp_nouveau_get_search_objects( $objects = array() ) {
	$primary = bp_nouveau_get_search_primary_object();

	if ( ! $primary ) {
		return $objects;
	}

	$objects = array(
		'primary' => $primary,
	);

	if ( 'member' === $primary || 'dir' === $primary ) {
		$objects['secondary'] = bp_current_component();
	} elseif( 'group' === $primary ) {
		$objects['secondary'] = bp_current_action();
	} else {
		$objects = apply_filters( 'bp_nouveau_get_search_objects', $objects );
	}

	return $objects;
}

/**
 * Output the search form container classes.
 *
 * @since 1.0.0
 *
 * @return string CSS classes.
 */
function bp_nouveau_search_container_class() {
	$objects = bp_nouveau_get_search_objects();

	echo join( '-search ', array_map( 'sanitize_html_class', $objects ) ) . '-search';
}

/**
 * Output the search form data-bp attribute.
 *
 * @since 1.0.0
 *
 * @param  string $attr The data-bp attribute.
 * @return string The data-bp attribute.
 */
function bp_nouveau_search_object_data_attr( $attr = '' ) {
	$objects = bp_nouveau_get_search_objects();

	if ( ! isset( $objects['secondary'] ) ) {
		return $attr;
	}

	if ( bp_is_active( 'groups' ) && bp_is_group_members() ) {
		$attr = join( '_', $objects );
	} else {
		$attr = $objects['secondary'];
	}

	echo esc_attr( $attr );
}

/**
 * Output a selector ID.
 *
 * @since 1.0.0
 *
 * @param  string $suffix A string to append at the end of the ID.
 * @param  string $sep    The separator to use between each token.
 * @return string The selector ID.
 */
function bp_nouveau_search_selector_id( $suffix = '', $sep = '-' ) {
	$id = join( $sep, array_merge( bp_nouveau_get_search_objects(), (array) $suffix ) );

	echo esc_attr( $id );
}

/**
 * Output the name attribute of a selector.
 *
 * @since 1.0.0
 *
 * @param  string $suffix A string to append at the end of the name.
 * @param  string $sep    The separator to use between each token.
 * @return string The name attribute of a selector.
 */
function bp_nouveau_search_selector_name( $suffix = '', $sep = '_' ) {
	$objects = bp_nouveau_get_search_objects();

	if ( isset( $objects['secondary'] ) && empty( $suffix ) ) {
		$name = bp_core_get_component_search_query_arg( $objects['secondary'] );
	} else {
		$name = join( $sep, array_merge( $objects, (array) $suffix ) );
	}

	echo esc_attr( $name );
}

/**
 * Output the default search text for the search object
 *
 * @since 1.0.0
 *
 * @param  string $text    The default search text for the search object.
 * @param  string $is_attr True if it's to be output inside an attribute. False Otherwise.
 * @return string The default search text.
 */
function bp_nouveau_search_default_text( $text = '', $is_attr = true ) {
	$objects = bp_nouveau_get_search_objects();

	if ( ! empty( $objects['secondary'] ) ) {
		$text = bp_get_search_default_text( $objects['secondary'] );
	}

	if ( $is_attr ) {
		echo esc_attr( $text );
	} else {
		echo esc_html( $text );
	}
}

/**
 * Get the search form template part and fire some do_actions if needed.
 *
 * @since 1.0.0
 *
 * @return string HTML Output
 */
function bp_nouveau_search_form() {
	bp_get_template_part( 'common/search/search-form' );

	$objects = bp_nouveau_get_search_objects();

	if ( empty( $objects['primary'] ) || empty( $objects['secondary'] ) ) {
		return;
	}

	if ( 'dir' === $objects['primary'] ) {

		if ( 'activity' === $objects['secondary'] ) {
			/**
			 * Fires before the display of the activity syndication options.
			 *
			 * @since 1.2.0 (BuddyPress)
			 */
			do_action( 'bp_activity_syndication_options' );

		} elseif ( 'blogs' === $objects['secondary'] ) {
			/**
			 * Fires inside the unordered list displaying blog sub-types.
			 *
			 * @since 1.5.0 (BuddyPress)
			 */
			do_action( 'bp_blogs_directory_blog_sub_types' );

		} elseif ( 'groups' === $objects['secondary'] ) {
			/**
			 * Fires inside the groups directory group types.
			 *
			 * @since 1.2.0 (BuddyPress)
			 */
			do_action( 'bp_groups_directory_group_types' );

		} elseif ( 'members' === $objects['secondary'] ) {
			/**
			 * Fires inside the members directory member sub-types.
			 *
			 * @since 1.5.0 (BuddyPress)
			 */
			do_action( 'bp_members_directory_member_sub_types' );
		}

	} elseif ( 'group' === $objects['primary'] && 'activity' === $objects['secondary'] ) {
		/**
		 * Fires inside the syndication options list, after the RSS option.
		 *
		 * @since 1.2.0 (BuddyPress)
		 */
		do_action( 'bp_group_activity_syndication_options' );
	}
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

/** Template tags for the customizer ******************************************/

/**
 * Get a link to reach a specific section into the customizer
 *
 * @since  1.0.0
 *
 * @param  array  $args The argument to customize the Customizer link
 * @return string HTML Output
 */
function bp_nouveau_get_customizer_link( $args = array() ) {
	$r = bp_parse_args( $args, array(
		'capability' => 'bp_moderate',
		'object'     => 'user',
		'item_id'    => 0,
		'autofocus'  => '',
		'text'       => '',
	), 'nouveau_get_customizer_link' );

	if ( empty( $r['capability'] ) || empty( $r['autofocus'] ) || empty( $r['text'] ) ) {
		return '';
	}

	if ( ! bp_current_user_can( $r['capability'] ) ) {
		return '';
	}

	if ( bp_is_user() ) {
		$url = rawurlencode( bp_displayed_user_domain() );
	} elseif ( bp_is_group() ) {
		$url = rawurlencode( bp_get_group_permalink( groups_get_current_group() ) );
	} elseif ( ! empty( $r['object'] ) && ! empty( $r['item_id'] ) ) {
		if ( 'user' === $r['object'] ) {
			$url = rawurlencode( bp_core_get_user_domain( $r['item_id'] ) );
		} elseif ( 'group' === $r['object'] ) {
			$group = groups_get_group( array( 'group_id' => $r['item_id'] ) );

			if ( ! empty( $group->id ) ) {
				$url = rawurlencode( bp_get_group_permalink( $group ) );
			}
		}
	}

	if ( empty( $url ) ) {
		return '';
	}

	$customizer_link = add_query_arg( array(
		'autofocus[section]' => $r['autofocus'],
		'url'                => $url,
	), admin_url( 'customize.php' ) );

	return sprintf( '<a href="%1$s">%2$s</a>', esc_url( $customizer_link ), $r['text'] );
}
