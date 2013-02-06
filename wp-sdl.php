<?php
/**
 * WordPress Standard Developer's Library
 *
 * @link http://wp-sdl.org/
 * @author Marshall Sorenson
 * @link http://marshallsorenson.com/
 * @copyright Copyright (C) 2013 Marshall Sorenson
 * @license http://www.gnu.org/licenses/gpl.html GPLv2 or later
 * @package wp-sdl
 */

// determine class path
$wp_sdl_class_path = dirname( __FILE__ ) . '/classes';

// has base class been loaded?
if ( false === class_exists( 'WP_SDL', false ) ) {
	// nope, load it
	require_once $wp_sdl_class_path . '/wp_sdl.php';
}

// init all classes known to this copy of the library
WP_SDL::init(
	$wp_sdl_class_path,
	array(
		'WP_SDL_1_0' => '',
		'WP_SDL_Helper_1_0' => '',
		'WP_SDL_Html_1_0' => 'html',
		'WP_SDL_Widget_1_0' => 'widget'
	)
);
