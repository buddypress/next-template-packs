<?php
/**
 * @group members_functions
 */
class BP_Nouveau_Members_Functions extends Next_Template_Packs_TestCase {

	public function setUp() {
		parent::setUp();

		$this->current_user = get_current_user_id();
		$this->user_id      = $this->factory->user->create();
		$this->set_current_user( $this->user_id );
	}

	public function tearDown() {
		parent::tearDown();

		$this->set_current_user( $this->current_user );

		// Reset the directory nav
		bp_nouveau()->directory_nav = new BP_Core_Nav();
	}

	public function do_dir_nav() {
		printf( '<li id="members-%1$s"><a href="%2$s">%3$s</a></li>', 'foo', 'http://example.org/members/foo', 'Foo' );
	}

	public function filter_dir_nav( $nav_items ) {
		$nav_items['bar'] = array(
			'component' => 'members',
			'slug'      => 'bar',
			'link'      => 'http://example.org/members/bar',
			'title'     => 'Bar',
			'text'      => 'Bar',
			'count'     => false,
			'position'  => 0,
		);

		return $nav_items;
	}

	/**
	 * @group directory_nav
	 * @group do_actions
	 */
	public function test_add_action_get_members_directory_nav_items() {
		$this->go_to( bp_get_members_directory_permalink() );

		add_action( 'bp_members_directory_member_types', array( $this, 'do_dir_nav' ), 10 );

		do_action( 'bp_screens' );

		// Navs are before, this should be the order
		$expected = array( 'all', 'foo' );

		// Init and sort the directory nav
		bp_nouveau_has_nav( array( 'object' => 'directory' ) );

		remove_action( 'bp_members_directory_member_types', array( $this, 'do_dir_nav' ), 10 );

		$this->assertSame( $expected, wp_list_pluck( bp_nouveau()->sorted_nav, 'slug' ) );
	}

	/**
	 * @group directory_nav
	 * @group apply_filters
	 */
	public function test_add_filter_get_members_directory_nav_items() {
		$this->go_to( bp_get_members_directory_permalink() );

		do_action( 'bp_screens' );

		add_filter( 'bp_nouveau_get_members_directory_nav_items', array( $this, 'filter_dir_nav' ), 10, 1 );

		do_action( 'bp_screens' );

		// Navs are before, this should be the order
		$expected = array( 'bar', 'all' );

		// Init and sort the directory nav
		bp_nouveau_has_nav( array( 'object' => 'directory' ) );

		remove_filter( 'bp_nouveau_get_members_directory_nav_items', array( $this, 'filter_dir_nav' ), 10, 1 );

		$this->assertSame( $expected, wp_list_pluck( bp_nouveau()->sorted_nav, 'slug' ) );
	}
}
