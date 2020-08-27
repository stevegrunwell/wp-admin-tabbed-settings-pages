<?php

/**
 * Tests for tabbed settings pages.
 */
class TabbedSettingsSections extends WP_UnitTestCase {

	/**
	 * Actions to perform before each test method.
	 */
	public function setUp() {
		global $wp_settings_sections;

		parent::setUp();

		$wp_settings_sections = array();

		// Skip the tests if do_tabbed_settings_sections() is defined in WordPress core.
		if ( function_exists( 'do_tabbed_settings_sections' ) ) {
			$function = new \ReflectionFunction( 'do_tabbed_settings_sections' );
			$filename = dirname( __DIR__ ) . '/wp-admin-tabbed-settings-pages.php';

			if ( $filename !== $function->getFileName() ) {
				$this->markTestSkipped(
					'do_tabbed_settings_sections() has been defined somewhere besides %1$s, skipping test.',
					$filename
				);
			}
		}

		/*
		 * In order to prevent conflicts with WordPress core should do_tabbed_settings_sections()
		 * make it in, don't load the plugin in the test suite until we reach this point.
		 */
		require_once dirname( dirname( __FILE__ ) ) . '/wp-admin-tabbed-settings-pages.php';

		// Explicitly register the script file within WordPress, normally done on admin_enqueue_script.
		wp_admin_tabbed_settings_register_script();

		// The WP core test suite doesn't always clean up enqueued scripts.
		wp_dequeue_script( 'settings-tabs' );
	}

	public function test_tabs_should_be_rendered() {
		add_settings_section(
			'tabbed-settings-1',
			'Tabbed settings 1',
			'__return_empty_string',
			'tabbed-settings'
		);
		add_settings_section(
			'tabbed-setting-2',
			'Tabbed settings 2',
			'__return_empty_string',
			'tabbed-settings'
		);

		ob_start();
		do_tabbed_settings_sections( 'tabbed-settings' );
		$output = ob_get_clean();

		$this->assertContains( '<nav class="nav-tab-wrapper', $output );
		$this->assertContains(
			'<a href="#tabbed-settings-1" id="nav-tab-tabbed-settings-1" class="nav-tab" role="tab">Tabbed settings 1</a>',
			$output
		);
		$this->assertContains( '<section id="tab-tabbed-settings-1" class="hide-if-js" role="tabpanel"', $output );

		$this->assertTrue(
			wp_script_is( 'wp-admin-tabs', 'enqueued' ),
			'The tab script should have been enqueued.'
		);
	}

	public function test_tabs_should_only_be_rendered_if_there_is_more_than_one_section() {
		add_settings_section(
			'tabbed-settings-1',
			'Tabbed settings 1',
			'__return_empty_string',
			'tabbed-settings'
		);

		ob_start();
		do_tabbed_settings_sections( 'tabbed-settings' );
		$output = ob_get_clean();

		$this->assertNotContains( '<nav class="nav-tab-wrapper', $output );
		$this->assertNotContains(
			'<a href="#tabbed-settings-1" id="nav-tab-tabbed-settings-1" class="nav-tab" role="tab">Tabbed settings 1</a>',
			$output
		);
		$this->assertNotContains( '<section id="tab-tabbed-settings-1" class="hide-if-js" role="tabpanel"', $output );

		$this->assertFalse(
			wp_script_is( 'settings-tabs', 'enqueued' ),
			'The tab script is unnecessary if content is not being tabbed.'
		);
	}
}
