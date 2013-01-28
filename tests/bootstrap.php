<?php
/**
 * PHPUnit bootstrap
 */

// fake environment
$PHP_SELF = $GLOBALS['PHP_SELF'] = $_SERVER['PHP_SELF'];

// init global vars
global $wpdb, $current_site, $current_blog, $wp_rewrite, $shortcode_tags, $wp;

// load wordpress
require_once WP_TEST_LOAD_PATH;

// load wp-sdl
require_once dirname(dirname(__FILE__)) . '/wp-sdl.php';
