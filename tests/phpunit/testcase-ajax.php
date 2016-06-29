<?php

class Next_Template_Packs_Ajax_UnitTestCase extends WP_Ajax_UnitTestCase {

	public function setUp() {
		parent::setUp();

		$this->factory = new Next_Template_Packs_UnitTest_Factory;
		$this->reset_global_server = $_SERVER;
		$this->reset_files = $_FILES;

		$_SERVER['REQUEST_METHOD'] = 'POST';

		// It's too annoying to get the update themes/plugins error message!
		add_filter( 'http_api_transports', '__return_empty_array', 10, 1 );
	}

	/**
	 * Tear down the test fixture.
	 * Reset the current user
	 */
	public function tearDown() {
		wp_set_current_user( 0 );
		$_SERVER = $this->reset_global_server;
		$_FILES  = $this->reset_files;
		remove_filter( 'http_api_transports', '__return_empty_array', 10, 1 );

		parent::tearDown();
	}

	/**
	 * WP's core tests use wp_set_current_user() to change the current
	 * user during tests. BP caches the current user differently, so we
	 * have to do a bit more work to change it
	 */
	public static function set_current_user( $user_id ) {
		$bp = buddypress();

		$bp->loggedin_user->id             = $user_id;
		$bp->loggedin_user->fullname       = bp_core_get_user_displayname( $user_id );
		$bp->loggedin_user->is_super_admin = $bp->loggedin_user->is_site_admin = is_super_admin( $user_id );
		$bp->loggedin_user->domain         = bp_core_get_user_domain( $user_id );
		$bp->loggedin_user->userdata       = bp_core_get_core_userdata( $user_id );

		wp_set_current_user( $user_id );
	}
}
