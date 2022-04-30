<?php
/**
 * Plugin Name: WP lagoon logs
 * Description: Simple wonolog wrapper for Lagoon.
 * Version: 0.2
 * Author: Govind Maloo
 * Author URI: http://drupal.org/u/govind.maloo
 * License: GPL2
 * Modified for usage by Icon Agency
 *
 * @package IconAgency
 */

use IconAgency\LagoonLogs\LagoonLogsSettings;
use IconAgency\LagoonLogs\LagoonHandler;
use Inpsyde\Wonolog\Configurator;

/**
 * Plugin init action because activation hook won't trigger in MU plugin.
 */
function wp_lagoon_logs_extension_init() {
	if ( get_option( 'wp_ll_settings' ) ) {
		return;
	}

	$default = array(
		'll_settings_logs_host'       => 'application-logs.lagoon.svc',
		'll_settings_logs_port'       => 5140,
		'll_settings_logs_identifier' => 'wordpress',
	);

	update_option( 'wp_ll_settings', $default );
}

if ( is_blog_installed() ) {
	add_action( 'init', 'wp_lagoon_logs_extension_init' );

	add_action(
			'wonolog.setup',
			function (Configurator $config) {
				$options = get_option( 'wp_ll_settings' );

				$handler = new LagoonHandler(
					$options['ll_settings_logs_host'],
					$options['ll_settings_logs_port'],
					$options['ll_settings_logs_identifier']
				);

				if ( getenv( 'LAGOON_ENVIRONMENT' ) ) {
					$config->pushHandler($handler->handler());
				}
			}
	);
	
	// Settings page is accessible to admin user.
	if ( is_admin() ) {
		$wp_ll_settings_page = new LagoonLogsSettings();
	}
}
