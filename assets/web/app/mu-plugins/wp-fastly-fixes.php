<?php
/**
 * Plugin Name: WP fastly fixes
 * Description: Lock down Fastly config.
 * Version: 0.1
 * Author: Icon Agency
 * Author URI: https://iconagency.com.au
 * License: GPL2
 *
 * @package IconAgency
 */

// Force defined values in application.php.
add_filter( 'option_fastly-settings-advanced', 'fastly_fixes_options');
add_filter( 'default_option_fastly-settings-advanced', 'fastly_fixes_options');

function fastly_fixes_options($values) {
	if ( $values ) {
		$values['sitecode'] = FASTLY_SITECODE;
		$values['fastly_api_key'] = PURGELY_FASTLY_KEY;
		$values['fastly_service_id'] = PURGELY_FASTLY_SERVICE_ID;
		$values['cache_control_ttl'] = PURGELY_CACHE_CONTROL_TTL;
		$values['default_purge_type'] = PURGELY_DEFAULT_PURGE_TYPE;
		$values['allow_purge_all'] = PURGELY_ALLOW_PURGE_ALL;
		$values['surrogate_control_ttl'] = PURGELY_SURROGATE_CONTROL_TTL;
		$values['enable_stale_while_revalidate'] = PURGELY_ENABLE_STALE_WHILE_REVALIDATE;
		$values['stale_while_revalidate_ttl'] = PURGELY_STALE_WHILE_REVALIDATE_TTL;
	}
	return $values;
}

// Disable VCL updating
add_filter( 'default_option_fastly_vcl_version', 'fastly_fixes_vcl_version');
add_filter( 'option_fastly_vcl_version', 'fastly_fixes_vcl_version');

function fastly_fixes_vcl_version($values) {
	return '999999';
}

// Hide admin menus
add_action( 'admin_init', function () {
	remove_submenu_page('fastly', 'fastly-io');
	remove_submenu_page('fastly', 'fastly-edge-modules');
	remove_submenu_page('fastly', 'fastly-webhooks');
});
