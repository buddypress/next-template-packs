<?php
/**
 * Include Next Template Packs Factory
 */
require_once dirname( __FILE__ ) . '/factory.php';

/**
 * Requires BuddyPress unit testcase
 */
if ( class_exists( 'BP_UnitTestCase' ) ) :
class Next_Template_Packs_TestCase extends BP_UnitTestCase {

	public function setUp() {
		parent::setUp();

		$this->factory = new Next_Template_Packs_UnitTest_Factory;
	}
}
else :

die( 'The BP_UnitTestCase class does not exist' );

endif;
