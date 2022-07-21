<?php
/**
 * Do not edit this file.
 * Edit the config files found in the environments/ dir instead.
 *
 * Your base production configuration goes in this file. Environment-specific
 * overrides go in their respective config/environments/{{WP_ENV}}.php file.
 *
 * A good default policy is to deviate from the production config as little as
 * possible. Try to define as much of your configuration in this file as you
 * can.
 *
 * PHP version 7.4
 *
 * phpcs:disable WordPress.PHP.DisallowShortTernary.Found
 *
 * @category Config
 * @package  IconAgency
 * @author   Icon Agency <hello@iconagency.com.au>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     https://iconagency.com.au
 */

use Roots\WPConfig\Config;

/**
 * Directory containing all of the site's files
 *
 * @var string
 */
$root_dir = dirname( __DIR__ );

/**
 * Document Root
 *
 * @var string
 */
$webroot_dir = $root_dir . '/web';

/**
 * Set up our global environment constant and load its config first
 * Default: production
 */
define( 'WP_ENV', getenv( 'LAGOON_ENVIRONMENT_TYPE' ) ?: 'production' );

/**
 * URLs
 */
$host = filter_input( INPUT_SERVER, 'HTTP_HOST' );
Config::define( 'WP_HOME', getenv( 'LAGOON_ROUTE' ) ?: 'https://' . $host );
Config::define( 'WP_SITEURL', getenv( 'LAGOON_ROUTE' ) ?: 'https://' . $host );

/**
 * Fastly
 */
Config::define( 'PURGELY_FASTLY_KEY', getenv( 'FASTLY_API_TOKEN' ) );
Config::define( 'PURGELY_FASTLY_SERVICE_ID', getenv( 'FASTLY_API_SERVICE' ) );
Config::define( 'FASTLY_SITECODE', getenv( 'FASTLY_SITE_ID' ) );
Config::define( 'PURGELY_CACHE_CONTROL_TTL', 600 );
Config::define( 'PURGELY_DEFAULT_PURGE_TYPE', 'soft' );
Config::define( 'PURGELY_ALLOW_PURGE_ALL', false );
Config::define( 'PURGELY_SURROGATE_CONTROL_TTL', 86400 );
Config::define( 'PURGELY_ENABLE_STALE_WHILE_REVALIDATE', true );
Config::define( 'PURGELY_STALE_WHILE_REVALIDATE_TTL', 10800 );

/**
 * Advanced Custom Fields
 */
Config::define( 'ACF_PRO_LICENSE', getenv( 'ACF_PRO_LICENSE' ) );

/**
 * Custom Content Directory
 */
Config::define( 'CONTENT_DIR', '/app' );
Config::define( 'WP_CONTENT_DIR', $webroot_dir . Config::get( 'CONTENT_DIR' ) );
Config::define( 'WP_CONTENT_URL', Config::get( 'WP_HOME' ) . Config::get( 'CONTENT_DIR' ) );

/**
 * DB settings
 */
Config::define( 'DB_NAME', getenv( 'MARIADB_DATABASE' ) ?: 'lagoon' );
Config::define( 'DB_USER', getenv( 'MARIADB_USERNAME' ) ?: 'lagoon' );
Config::define( 'DB_PASSWORD', getenv( 'MARIADB_PASSWORD' ) ?: 'lagoon' );
Config::define( 'DB_HOST', getenv( 'MARIADB_HOST' ) ?: 'mariadb' );
Config::define( 'DB_CHARSET', 'utf8mb4' );
Config::define( 'DB_COLLATE', '' );

// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
$table_prefix = getenv( 'DB_PREFIX' ) ?: 'wp_';

/**
 * Wordfence
 */
Config::define( 'WFWAF_STORAGE_ENGINE', getenv( 'WFWAF_STORAGE_ENGINE' ) ?: 'mysqli' );
Config::define( 'WFWAF_DB_NAME', getenv( 'WFWAF_DB_NAME' ) ?: Config::get( 'DB_NAME' ) );
Config::define( 'WFWAF_DB_USER', getenv( 'WFWAF_DB_USER' ) ?: Config::get( 'DB_USER' ) );
Config::define( 'WFWAF_DB_PASSWORD', getenv( 'WFWAF_DB_PASSWORD' ) ?: Config::get( 'DB_PASSWORD' ) );
Config::define( 'WFWAF_DB_HOST', getenv( 'WFWAF_DB_HOST' ) ?: Config::get( 'DB_HOST' ) );
Config::define( 'WFWAF_DB_CHARSET', getenv( 'WFWAF_DB_CHARSET' ) ?: Config::get( 'DB_CHARSET' ) );
Config::define( 'WFWAF_DB_COLLATE', getenv( 'WFWAF_DB_COLLATE' ) ?: Config::get( 'DB_COLLATE' ) );
Config::define( 'WFWAF_TABLE_PREFIX', getenv( 'WFWAF_TABLE_PREFIX' ) ?: $table_prefix );

/**
 * Authentication Unique Keys and Salts
 */
Config::define( 'AUTH_KEY', getenv( 'WP_AUTH_KEY' ) );
Config::define( 'SECURE_AUTH_KEY', getenv( 'WP_SECURE_AUTH_KEY' ) );
Config::define( 'LOGGED_IN_KEY', getenv( 'WP_LOGGED_IN_KEY' ) );
Config::define( 'NONCE_KEY', getenv( 'WP_NONCE_KEY' ) );
Config::define( 'AUTH_SALT', getenv( 'WP_AUTH_SALT' ) );
Config::define( 'SECURE_AUTH_SALT', getenv( 'WP_SECURE_AUTH_SALT' ) );
Config::define( 'LOGGED_IN_SALT', getenv( 'WP_LOGGED_IN_SALT' ) );
Config::define( 'NONCE_SALT', getenv( 'WP_NONCE_SALT' ) );

/**
 * Custom Settings
 */
Config::define( 'AUTOMATIC_UPDATER_DISABLED', true );
Config::define( 'DISABLE_WP_CRON', 'false' !== getenv( 'DISABLE_WP_CRON' ) );
// Disable the plugin and theme file editor in the admin.
Config::define( 'DISALLOW_FILE_EDIT', true );
// Disable plugin and theme updates and installation from the admin.
Config::define( 'DISALLOW_FILE_MODS', true );
// Limit the number of post revisions that WordPress stores (true (default WP): store every revision).
Config::define( 'WP_POST_REVISIONS', getenv( 'WP_POST_REVISIONS' ) ?: true );

/**
 * Debugging Settings
 */
Config::define( 'WP_DEBUG_DISPLAY', false );
Config::define( 'WP_DEBUG_LOG', false );
Config::define( 'SCRIPT_DEBUG', false );

// phpcs:ignore
ini_set( 'display_errors', '0' );

/**
 * Allow WordPress to detect HTTPS when used behind a reverse proxy or a load balancer
 * See https://codex.wordpress.org/Function_Reference/is_ssl#Notes
 */
if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO'] ) {
	$_SERVER['HTTPS'] = 'on';
}

$all_config = __DIR__ . '/environments/all.php';
if ( file_exists( $all_config ) ) {
	include_once $all_config;
}

$env_config = __DIR__ . '/environments/' . WP_ENV . '.php';
if ( file_exists( $env_config ) ) {
	include_once $env_config;
}

$local_config = __DIR__ . '/environments/local.php';
if ( file_exists( $local_config ) ) {
	include_once $local_config;
}

// Hide debug on install.
$request_uri = filter_input( INPUT_SERVER, 'REQUEST_URI' );
if ( stripos( $request_uri, '/wp-admin/install.php' ) !== false ) {
	Config::define( 'WP_DEBUG', false );
}

Config::apply();

/**
 * Bootstrap WordPress
 */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', $webroot_dir . '/wp/' );
}
