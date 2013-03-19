<?php
/**
 * HTML input example
 * 
 * @package wp-sdl\examples
 */

// attributes
$atts =
	array(
		'id' => 'foo',
		'class' => 'man'
	);

// render text input tag
$wpsdl->html()->input( 'text', 'address', '123 Street Road', $atts );

// result:
// <input type="text" name="address" id="foo" class="man" value="123 Street Road" />

?>