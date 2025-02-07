<?php
/**
 * Plugin Name: WP lagoon logs
 * Description: Simple wonolog wrapper for Lagoon.
 * Version: 0.3
 * Author: Icon Agency
 * Author URI: https://iconagency.com.au
 * License: GPL2
 *
 * @package IconAgency
 */

use IconAgency\LagoonLogs\{LagoonLogsSettings, LagoonHandler};
use Inpsyde\Wonolog\{Configurator, HookListener};

/**
 * Plugin init action because activation hook won't trigger in MU plugin.
 */
function wp_lagoon_logs_extension_init() {
	if ( get_option( 'wp_ll_settings' ) ) {
		return;
	}

	$default = array(
		'll_settings_logs_host' => 'application-logs.lagoon.svc',
		'll_settings_logs_port' => 5140,
	);

	update_option( 'wp_ll_settings', $default );
}

if ( is_blog_installed() ) {
	add_action( 'init', 'wp_lagoon_logs_extension_init' );

	add_action(
		'wonolog.setup',
		function (Configurator $config) {
			if (! $options = get_option( 'wp_ll_settings' )) {
				return;
			}

			$handler = new LagoonHandler(
				$options['ll_settings_logs_host'],
				$options['ll_settings_logs_port'],
			);

			$config->disableDefaultHookListeners(
				HookListener\FailedLoginListener::class,
			);

			if ( getenv( 'LAGOON_ENVIRONMENT' ) ) {
				$config->pushHandler($handler->handler());
			} else {
				$config->disableDefaultHookListeners(
					HookListener\HttpApiListener::class,
				);
			}
		}
	);
	
	// Settings page is accessible to admin user.
	if ( is_admin() ) {
		$wp_ll_settings_page = new LagoonLogsSettings();
	}
}
