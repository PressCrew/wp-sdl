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
		'class' => 'man',
		'value' => 'choo',
	);

// render text input tag
echo $wpsdl->html()->input( 'text', 'address', $atts );

// result:
// <input type="text" name="address" id="foo" class="man" value="choo" />

?>