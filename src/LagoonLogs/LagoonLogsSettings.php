<?php

namespace IconAgency\LagoonLogs;

class LagoonLogsSettings {

	/**
	 * Holds the values to be used in the fields callbacks
	 */
	private $options;

	/**
	 * Start up
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
	}

	/**
	 * Add options page
	 */
	public function add_plugin_page() {
		// This page will be under "Settings"
		add_options_page(
			'Settings Admin',
			'Lagoon Logs',
			'manage_options',
			'wp-lagoon-logs-admin',
			array( $this, 'wp_lagoon_logs_settings_page' )
		);
	}

	/**
	 * Options page callback
	 */
	public function wp_lagoon_logs_settings_page() {
		$this->options = get_option( 'wp_ll_settings' );
		?>
			<div class="wrap">
				<h1>Lagoon Logs</h1>
				<form method="post" action="options.php">
				<?php
					settings_fields( 'wp_ll_option_group' );
					do_settings_sections( 'wp-lagoon-logs-admin' );
				?>
				</form>
			</div>
		<?php
	}

	/**
	 * Register and add settings
	 */
	public function page_init() {
		register_setting(
			'wp_ll_option_group', // Option group.
			'wp_ll_settings', // Option name.
		);

		add_settings_section(
			'wp_ll_settings_description', // ID.
			'WP lagoon logs Settings', // Title.
			array( $this, 'wp_ll_description' ), // Callback.
			'wp-lagoon-logs-admin' // Page.
		);

		add_settings_field(
			'll_settings_logs_host', // ID.
			'Logstash host: ', // Title.
			array( $this, 'text_field_callback' ), // Callback.
			'wp-lagoon-logs-admin', // Page.
			'wp_ll_settings_description', // Section.
			'll_settings_logs_host'
		);

		add_settings_field(
			'll_settings_logs_port', // ID.
			'Logstash port: ', // Title.
			array( $this, 'text_field_callback' ), // Callback.
			'wp-lagoon-logs-admin', // Page.
			'wp_ll_settings_description', // Section.
			'll_settings_logs_port'
		);

		add_settings_field(
			'll_settings_logs_identifier', // ID.
			'Logstash leading identifier: ', // Title.
			array( $this, 'text_field_callback' ), // Callback.
			'wp-lagoon-logs-admin', // Page.
			'wp_ll_settings_description', // Section.
			'll_settings_logs_identifier'
		);
	}

	/**
	 * Print the Section text
	 */
	public function wp_ll_description() {
		print 'This page simply lists the current settings for the Lagoon Logs module.';
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function text_field_callback( $name ) {
		printf(
			'
            <div class="ll-settings-key-value">
                <input type="text" name="wp_ll_settings[%s]" disabled="disabled" value="%s" />
            </div>
        ',
			$name,
			isset( $this->options[ $name ] ) ? esc_attr( $this->options[ $name ] ) : ''
		);
	}
}
