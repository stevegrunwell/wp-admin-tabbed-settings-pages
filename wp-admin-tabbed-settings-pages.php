<?php
/**
 * Plugin Name: WP-Admin Tabbed Settings Pages (Polyfill)
 * Description: A polyfill for Trac #51086, bringing tabbed settings pages into WP-Admin.
 * Author:      Steve Grunwell
 * Author URI:  https://stevegrunwell.com
 * Version:     0.2.0
 */

/**
 * Register (but do not enqueue) the tab scripting within WP-Admin.
 *
 * Note that the Trac version uses "settings-tabs" as the hook to prevent conflict.
 */
if ( ! function_exists( 'wp_admin_tabbed_settings_register_script' ) ) {
	function wp_admin_tabbed_settings_register_script() {
		wp_register_script(
			'wp-admin-tabs',
			plugins_url( 'assets/tabs.js', __FILE__ ),
			array(),
			'0.2.0',
			true
		);
	}

	add_action( 'admin_enqueue_scripts', 'wp_admin_tabbed_settings_register_script', 1 );
}

if ( ! function_exists( 'do_tabbed_settings_sections' ) ) {
	/**
	 * Render settings sections for a particular page using a tabbed interface.
	 *
	 * This function operates the same as do_settings_sections() as part of the Settings API.
	 *
	 * @global array $wp_settings_sections Storage array of all settings sections added to admin pages.
	 * @global array $wp_settings_fields   Storage array of settings fields and info about their pages/sections.
	 *
	 * @param string $page The slug name of the page whose settings sections you want to output.
	 */
	function do_tabbed_settings_sections( $page ) {
		global $wp_settings_sections, $wp_settings_fields;

		if ( ! isset( $wp_settings_sections[ $page ] ) ) {
			return;
		}

		$sections = (array) $wp_settings_sections[ $page ];

		// If there's only one section, don't bother rendering tabs.
		if ( 1 >= count( $sections ) ) {
			return do_settings_sections( $page );
		}

		// Render the list of tabs, then each section.
		echo '<nav class="nav-tab-wrapper hide-if-no-js" role="tablist">';
		foreach ( $sections as $section ) {
			printf(
				'<a href="#%1$s" id="nav-tab-%1$s" class="nav-tab" role="tab">%2$s</a>',
				esc_attr( $section['id'] ),
				esc_html( $section['title'] )
			);
		}
		echo '</nav>';

		foreach ( (array) $wp_settings_sections[ $page ] as $section ) {
			printf( '<section id="tab-%1$s" class="hide-if-js" role="tabpanel" aria-labelledby="nav-tab-%1$s">', esc_attr( $section['id'] ) );
			if ( $section['title'] ) {
				printf( '<h2 class="tabbed-section-heading">%1$s</h2>%2$s', esc_html( $section['title'] ), PHP_EOL );
			}

			if ( is_callable( $section['callback'] ) ) {
				call_user_func( $section['callback'], $section );
			}

			if ( isset( $wp_settings_fields[ $page ][ $section['id'] ] ) ) {
				echo '<table class="form-table" role="presentation">';
				do_settings_fields( $page, $section['id'] );
				echo '</table>';
			}

			echo '</section>';
		}

		// Finally, ensure the necessary scripts are enqueued.
		wp_enqueue_script( 'wp-admin-tabs' );
	}
}
