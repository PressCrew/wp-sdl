<?php
/**
 * PHPUnit bootstrap
 */

// fake environment
$PHP_SELF = $GLOBALS['PHP_SELF'] = $_SERVER['PHP_SELF'];

// init global vars
global $wpdb, $current_site, $current_blog, $wp_rewrite, $shortcode_tags, $wp;

// default wp debug setting
if ( false === defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

// default wp cron setting
if ( false === defined( 'DISABLE_WP_CRON' ) ) {
	define( 'DISABLE_WP_CRON', true );
}

// default path to wordpress env
if ( false === defined( 'WP_SDL_BOOTSTRAP_ENV' ) ) {
	define( 'WP_SDL_BOOTSTRAP_ENV', '/var/www/html' );
}

// default path to src files
if ( false === defined( 'WP_SDL_BOOTSTRAP_SRC' ) ) {
	define( 'WP_SDL_BOOTSTRAP_SRC', getcwd() . '/src' );
}

// load wordpress
require_once WP_SDL_BOOTSTRAP_ENV . '/wp-load.php';

// load wp-sdl
require_once WP_SDL_BOOTSTRAP_SRC . '/wp-sdl.php';
