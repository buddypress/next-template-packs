<?php
/**
 * Include Next Template Packs Factory
 */
require_once dirname( __FILE__ ) . '/factory.php';

/**
 * Requires BuddyPress unit testcase
 */
class Next_Template_Packs_TestCase extends BP_UnitTestCase {

	public function setUp() {
		parent::setUp();

		$this->factory = new Next_Template_Packs_UnitTest_Factory;
	}
}
