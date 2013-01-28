<?php
/**
 * HTML attributes example
 *
 * @package wp-sdl\examples
 */

// attributes
$atts =
	array(
		'id' => 'foo',
		'class' => 'bar'
	);

// print string
echo $wpsdl->html()->attributes( $atts );

// result:
// id="foo" class="bar"

?>