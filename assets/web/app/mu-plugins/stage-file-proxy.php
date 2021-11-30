<?php
/**
 * Plugin Name: Stage File Proxy
 * Plugin URI: http://iconagency.com.au/
 * Description: Get only the files you need from your production environment. Don't ever run this in production!
 * Version: 100
 * Author: Icon Agency
 * Author URI: http://iconagency.com.au/
 *
 * @package IconAgency
 */

$plugin_enabled = getenv( 'LAGOON_ENVIRONMENT' ) && getenv( 'LAGOON_ENVIRONMENT_TYPE' ) !== 'production';
$request_uri    = filter_input( INPUT_SERVER, 'REQUEST_URI' );

if ( false !== stripos( $request_uri, '/app/uploads/' ) && $plugin_enabled ) {
	sfp_expect();
}

/**
 * This function, triggered above, sets the chain in motion.
 */
function sfp_expect() {
	ob_start();
	add_action( 'init', 'sfp_dispatch' );
}

/**
 * Serve the file directly.
 *
 * (next time the webserver will do it for us).
 *
 * @param string $filename Filename to read.
 */
function sfp_serve_requested_file( $filename ) {
	$finfo = finfo_open( FILEINFO_MIME_TYPE );
	$type  = finfo_file( $finfo, $filename );

	ob_end_clean();
	header( 'Content-Type: ' . $type );
	header( 'Content-Length: ' . filesize( $filename ) );
	readfile( $filename );

	exit;
}


/**
 * Fetch original from Production Server.
 */
function sfp_dispatch() {
	$mode           = get_option( 'sfp_mode', 'header' );
	$production_url = get_option( 'sfp_url' );

	if ( ! $production_url ) {
		sfp_error( 'No URL set for sfp' );
	}

	$request_uri  = filter_input( INPUT_SERVER, 'REQUEST_URI' );
	$request_uri  = strtok( $request_uri, '?' );
	$relative_uri = str_ireplace( '/app/uploads/', '', $request_uri );

	$production_url = untrailingslashit( $production_url );
	$remote_url     = $production_url . '/' . $relative_uri;

	$relative_parts = explode( '/', $relative_uri );
	$filename       = array_pop( $relative_parts );
	$relative_path  = implode( '/', $relative_parts );

	if ( 'header' === $mode ) {
		header( 'Location: ' . $remote_url );
		exit;
	}

	if ( 'local' === $mode ) {
		// Path to save files.
		$upload       = _wp_upload_dir();
		$absolute_dir = untrailingslashit( $upload['basedir'] . '/' . $relative_path );

		// Path and filename.
		$absolute_filename = $absolute_dir . '/' . $filename;

		// Download it.
		if ( ! file_exists( $absolute_filename ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';

			$remote_file = download_url( $remote_url );
			if ( $remote_file ) {
				mkdir( $absolute_dir, 0755, true );
				rename( $remote_file, $absolute_filename );
			}
		}

		// Serve it.
		if ( file_exists( $absolute_filename ) ) {
			sfp_serve_requested_file( $absolute_filename );
		} else {
			sfp_error( 'Could not download original', 404 );
		}
	} else {
		sfp_error( 'Unknown sfp method' );
	}
}

/**
 * Common point of failure.
 *
 * @param string $error Feedback.
 * @param int    $code header code.
 */
function sfp_error( $error, $code = 500 ) {
	http_response_code( $code );

	echo esc_attr( $error );
	exit;
}
