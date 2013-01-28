<?php
/**
 * Quick Start example
 *
 * @link http://wp-sdl.org/
 * @author Marshall Sorenson
 * @link http://marshallsorenson.com/
 * @copyright Copyright (C) 2013 Marshall Sorenson
 * @license http://www.gnu.org/licenses/gpl.html GPLv2 or later
 * @package wp-sdl\examples
 */

// first, load up the copy of the lib you bundled with your plugin/theme
require_once 'path/to/wp-sdl/wp-sdl.php';

// getting the instance of WP_SDL for the version you support is easy!

/* @var $wpsdl WP_SDL_1_0 */
$wpsdl = WP_SDL::support( '1.0' );

// now you can do some stuff with WP_SDL
// like nasty form input generation with the HTML helper

/* @var $html_helper WP_SDL_Html_1_0 */
$html_helper = $wpsdl->html();

// attributes for the select element
$select_atts = array(
	'class' => 'small',
	'tabindex' => '5'
);

// attributes for the option elements
$option_atts = array(
	'class' => 'fancy'
);

// the option values => titles
$options = array(
	'red' => 'Red',
	'yellow' => 'Yellow',
	'blue' => 'Blue'
);

// generate the html using standard method calls
$html_helper->label( 'colors', 'Pick a color:' );
$html_helper->select( 'colors', $select_atts );
$html_helper->option_list( $options, $option_atts, 'yellow' );
$html_helper->select_close();

// ...or generate the html using object chaining
$html_helper
	->label( 'colors', 'Pick a color ->' )
	->select( 'colors', $select_atts )
	->option_list( $options, $option_atts, 'yellow' )
	->select_close();

// either method renders the same markup:
//
// <label for="colors">Pick a color -&gt;</label>
// <select class="small" tabindex="5" name="colors">
//     <option class="fancy" value="red" title="Red">Red</option>
//     <option class="fancy" selected="selected" value="yellow" title="Yellow">Yellow</option>
//     <option class="fancy" value="blue" title="Blue">Blue</option>
// </select>

?>