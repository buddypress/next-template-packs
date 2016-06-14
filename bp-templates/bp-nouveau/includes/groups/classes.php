<?php
/**
 * Groups classes
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BP_Nouveau_Group_Invite_Query' ) ) :
/**
 * Query to get members that are not already members of the group
 *
 * @since 1.0
 */
class BP_Nouveau_Group_Invite_Query extends BP_User_Query {
	/**
	 * Array of group member ids, cached to prevent redundant lookups
	 *
	 * @var null|array Null if not yet defined, otherwise an array of ints
	 * @package BP Nouveau
	 * @since 1.0
	 */
	protected $group_member_ids;

	/**
	 * Set up action hooks
	 *
	 * @package BP Nouveau
	 * @since 1.0
	 */
	public function setup_hooks() {
		add_action( 'bp_pre_user_query_construct', array( $this, 'build_exclude_args' ) );
	}

	/**
	 * Exclude group members from the user query
	 * as it's not needed to invite members to join the group
	 *
	 * @package BP Nouveau
	 * @since 1.0
	 */
	public function build_exclude_args() {
		$this->query_vars = wp_parse_args( $this->query_vars, array(
			'group_id'     => 0,
			'is_confirmed' => true,
		) );

		$group_member_ids = $this->get_group_member_ids();

		// We want to get users that are already members of the group
		$type = 'exclude';

		// We want to get invited users who did not confirmed yet
		if ( false === $this->query_vars['is_confirmed'] ) {
			$type = 'include';
		}

		if ( ! empty( $group_member_ids ) ) {
			$this->query_vars[ $type ] = $group_member_ids;
		}
	}

	/**
	 * Get the members of the queried group
	 *
	 * @package BP Nouveau
	 * @since 1.0
	 *
	 * @return array $ids User IDs of relevant group member ids
	 */
	protected function get_group_member_ids() {
		global $wpdb;

		if ( is_array( $this->group_member_ids ) ) {
			return $this->group_member_ids;
		}

		$bp  = buddypress();
		$sql = array(
			'select'  => "SELECT user_id FROM {$bp->groups->table_name_members}",
			'where'   => array(),
			'orderby' => '',
			'order'   => '',
			'limit'   => '',
		);

		/** WHERE clauses *****************************************************/

		// Group id
		$sql['where'][] = $wpdb->prepare( "group_id = %d", $this->query_vars['group_id'] );

		if ( false === $this->query_vars['is_confirmed'] ) {
			$sql['where'][] = $wpdb->prepare( "is_confirmed = %d", (int) $this->query_vars['is_confirmed'] );
		}

		// Join the query part
		$sql['where'] = ! empty( $sql['where'] ) ? 'WHERE ' . implode( ' AND ', $sql['where'] ) : '';

		/** ORDER BY clause ***************************************************/
		$sql['orderby'] = "ORDER BY date_modified";
		$sql['order']   = "DESC";

		/** LIMIT clause ******************************************************/
		$this->group_member_ids = $wpdb->get_col( "{$sql['select']} {$sql['where']} {$sql['orderby']} {$sql['order']} {$sql['limit']}" );

		return $this->group_member_ids;
	}

	public static function get_inviter_ids( $user_id = 0, $group_id = 0 ) {
		global $wpdb;

		if ( empty( $group_id ) || empty( $user_id ) ) {
			return array();
		}

		$bp  = buddypress();

		return $wpdb->get_col( $wpdb->prepare( "SELECT inviter_id FROM {$bp->groups->table_name_members} WHERE user_id = %d AND group_id = %d", $user_id, $group_id ) );
	}
}

endif;
