<?php
/**
 * Plugin Name: Stage File Proxy
 * Plugin URI: http://alleyinteractive.com/
 * Description: Get only the files you need from your production environment. Don't ever run this in production!
 * Version: 100
 * Author: Austin Smith, Alley Interactive
 * Author URI: http://www.alleyinteractive.com/
 * Modified for usage by Icon Agency
 *
 * @package IconAgency
 */

/**
 *
 *
 * A very important mission we have is to shut up all errors on static-looking paths, otherwise errors
 * are going to screw up the header or download & serve process. So this plugin has to execute first.
 *
 * We're also going to *assume* that if a request for /app/uploads/ causes PHP to load, it's
 * going to be a 404 and we should go and get it from the remote server.
 *
 * Developers need to know that this stuff is happening and should generally understand how this plugin
 * works before they employ it.
 *
 * The dynamic resizing portion was adapted from dynamic-image-resizer.
 * See: http://wordpress.org/plugins/dynamic-image-resizer/
 */

$request_uri = filter_input( INPUT_SERVER, 'REQUEST_URI' );

if ( stripos( $request_uri, '/app/uploads/' ) !== false && getenv( 'LAGOON_ENVIRONMENT' ) && getenv( 'LAGOON_ENVIRONMENT_TYPE' ) !== 'production' ) {
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
 * This function can fetch a remote image or resize a local one.
 *
 * If a cropped image is requested, and the original does not exist locally, it will take two runs of
 * this function to return the proper resized image, which is achieved by the header("Location: ...")
 * bits. The first run will fetch the remote image, the second will resize it.
 *
 * Ideally we could do this in one pass.
 */
function sfp_dispatch() {
	$mode          = sfp_get_mode();
	$relative_path = sfp_get_relative_path();

	if ( 'header' === $mode ) {
		header( 'Location: ' . sfp_get_base_url() . $relative_path );
		exit;
	}

	$doing_resize = false;

	// Resize an image maybe.
	if ( preg_match( '/(.+)(-r)?-([0-9]+)x([0-9]+)(c)?\.(jpe?g|png|gif)/iU', $relative_path, $matches ) ) {
		$doing_resize = true;

		$resize = array(
			'filename' => $matches[1] . '.' . $matches[6],
			'width'    => $matches[3],
			'height'   => $matches[4],
			'crop'     => ! empty( $matches[5] ),
			'mode'     => substr( $matches[2], 1 ),
		);

		$uploads_dir = wp_upload_dir();
		$basefile    = $uploads_dir['basedir'] . '/' . $resize['filename'];

		sfp_resize_image( $basefile, $resize );
		$relative_path = $resize['filename'];
	}

	// Alter the args of the GET request.
	$remote_http_request_args = apply_filters( 'sfp_http_remote_args', array( 'timeout' => 30 ) );

	// Download a full-size original from the remote server.
	// If it needs to be resized, it will be on the next load.
	$remote_url     = sfp_get_base_url() . $relative_path;
	$remote_request = wp_remote_get( $remote_url, $remote_http_request_args );

	if ( is_wp_error( $remote_request ) || $remote_request['response']['code'] > 400 ) {

		// If local mode, failover to local files.
		if ( 'local' === $mode ) {
			// Cache replacement image by hashed request URI.
			$request_uri   = filter_input( INPUT_SERVER, 'REQUEST_URI' );
			$transient_key = 'sfp_image_' . md5( $request_uri );

			$basefile = get_transient( $transient_key );

			if ( false === $basefile ) {
				$basefile = sfp_get_random_local_file_path();
				set_transient( $transient_key, $basefile );
			}

			// Resize if necessary.
			if ( $doing_resize ) {
				sfp_resize_image( $basefile, $resize );
			} else {
				sfp_serve_requested_file( $basefile );
			}
		} else {
			sfp_error( 'Unable to get file' );
		}
	}

	// We could be making some dangerous assumptions here, but if WP is setup normally, this will work.
	$path_parts = explode( '/', $remote_url );
	$name       = array_pop( $path_parts );

	if ( strpos( $name, '?' ) ) {
		list( $name, $crap ) = explode( '?', $name, 2 );
	}

	$month = array_pop( $path_parts );
	$year  = array_pop( $path_parts );

	$upload = wp_upload_bits( $name, null, $remote_request['body'], "$year/$month" );

	if ( ! $upload['error'] ) {

		// Check that file is an image...
		$img = wp_get_image_editor( $upload['file'] );

		if ( is_wp_error( $img ) ) {
			unlink( $upload['file'] );
			sfp_error( $img->get_error_message() );
		}

		// If there was some other sort of error, and the file now does not exist,
		// We could loop on accident. Should think about some other strategies.
		if ( $doing_resize ) {
			sfp_dispatch();
		} else {
			sfp_serve_requested_file( $upload['file'] );
		}
	} else {
		sfp_error( $upload['error'] );
	}
}

/**
 * Resizes $basefile based on parameters in $resize.
 *
 * @param string $basefile File to resize.
 * @param array  $resize Resize metrics.
 */
function sfp_resize_image( $basefile, array $resize ) {
	if ( file_exists( $basefile ) ) {
		$suffix = $resize['width'] . 'x' . $resize['height'];
		if ( $resize['crop'] ) {
			$suffix .= 'c';
		}
		if ( 'r' === $resize['mode'] ) {
			$suffix = 'r-' . $suffix;
		}
		$img = wp_get_image_editor( $basefile );

		if ( is_wp_error( $img ) ) {
			sfp_error( $img->get_error_message() );
		}

		$img->resize( $resize['width'], $resize['height'], $resize['crop'] );
		$info = pathinfo( $basefile );

		$path_to_new_file = $info['dirname'] . '/' . $info['filename'] . '-' . $suffix . '.' . $info['extension'];

		$img->save( $path_to_new_file );

		sfp_serve_requested_file( $path_to_new_file );
	}
}

/**
 * Serve the file directly.
 *
 * @param string $filename Filename to read.
 */
function sfp_serve_requested_file( $filename ) {
	$finfo = finfo_open( FILEINFO_MIME_TYPE );
	$type  = finfo_file( $finfo, $filename );

	// Serve the image this one time (next time the webserver will do it for us).
	ob_end_clean();
	header( 'Content-Type: ' . $type );
	header( 'Content-Length: ' . filesize( $filename ) );
	readfile( $filename );

	exit;
}

/**
 * Prevent WP from generating resized images on upload.
 *
 * @param array $sizes Associative array of image sizes to be created.
 */
function sfp_image_sizes_advanced( array $sizes ) {
	global $dynimg_image_sizes;

	// Save the sizes to a global, because the next function needs them to lie to WP about what sizes were generated.
	$dynimg_image_sizes = $sizes;

	// Force WP to not make sizes by telling it there's no sizes to make.
	return array();
}
add_filter( 'intermediate_image_sizes_advanced', 'sfp_image_sizes_advanced' );

/**
 * Trick WP into thinking the images were generated anyways.
 *
 * @param array $meta An array of attachment meta data.
 */
function sfp_generate_metadata( array $meta ) {
	global $dynimg_image_sizes;

	if ( ! is_array( $dynimg_image_sizes ) ) {
		return $meta;
	}

	foreach ( $dynimg_image_sizes as $sizename => $size ) {
		// Figure out what size WP would make this.
		$newsize = image_resize_dimensions(
			$meta['width'],
			$meta['height'],
			$size['width'],
			$size['height'],
			$size['crop']
		);

		if ( $newsize ) {
			$info = pathinfo( $meta['file'] );
			$ext  = $info['extension'];
			$name = wp_basename( $meta['file'], ".$ext" );

			$suffix = "r-{$newsize[4]}x{$newsize[5]}";
			if ( $size['crop'] ) {
				$suffix .= 'c';
			}

			// Build the fake meta entry for the size in question.
			$resized = array(
				'file'   => "{$name}-{$suffix}.{$ext}",
				'width'  => $newsize[4],
				'height' => $newsize[5],
			);

			$meta['sizes'][ $sizename ] = $resized;
		}
	}

	return $meta;
}
add_filter( 'wp_generate_attachment_metadata', 'sfp_generate_metadata' );

/**
 * Get the relative file path by stripping out the /app/uploads/ business.
 */
function sfp_get_relative_path() {
	static $path;

	if ( ! $path ) {
		$request_uri = filter_input( INPUT_SERVER, 'REQUEST_URI' );
		$path        = preg_replace( '/.*\/app\/uploads(\/sites\/\d+)?\//i', '', $request_uri );
	}

	// Alter the relative path of an image in SFP.
	return apply_filters( 'sfp_relative_path', $path );
}

/**
 * Grab a random file from a local directory and return the path
 */
function sfp_get_random_local_file_path() {
	static $local_dir;
	$transient_key = 'sfp-replacement-images';
	if ( ! $local_dir ) {
		$local_dir = get_option( 'sfp_local_dir', 'sfp-images' );
	}

	$replacement_image_path = get_template_directory() . '/' . $local_dir . '/';

	// Cache image directory contents.
	$images = get_transient( $transient_key );

	if ( false === $images ) {
		// Exclude resized images.
		foreach ( glob( $replacement_image_path . '*' ) as $filename ) {
			if ( ! preg_match( '/.+[0-9]+x[0-9]+c?\.(jpe?g|png|gif)$/iU', $filename ) ) {
				$images[] = basename( $filename );
			}
		}
		set_transient( $transient_key, $images );
	}

	$rand = rand( 0, count( $images ) - 1 );

	return $replacement_image_path . $images[ $rand ];
}

/**
 * SFP can operate in two modes, 'download' and 'header'
 */
function sfp_get_mode() {
	static $mode;
	if ( ! $mode ) {
		$mode = get_option( 'sfp_mode', 'header' );
	}
	return $mode;
}

/**
 * Get the base URL of the uploads directory
 * (i.e. the first possible directory on the remote side that could store a file)
 */
function sfp_get_base_url() {
	static $url;
	$mode = sfp_get_mode();
	if ( ! $url ) {
		$url = get_option( 'sfp_url' );
		if ( ! $url && 'local' !== $mode ) {
			sfp_error( 'local sfp_url not set' );
		}
	}
	return $url;
}

/**
 * Common point of failure.
 *
 * @param string $error Feedback.
 */
function sfp_error( $error ) {
	die( 'SFP failed: ' . esc_attr( $error ) );
}
