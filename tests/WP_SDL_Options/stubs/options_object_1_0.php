<?php
/**
 * STUB: Options Object 1.0
 *
 * @link http://wp-sdl.org/
 * @author Marshall Sorenson
 * @link http://marshallsorenson.com/
 * @copyright Copyright (C) 2013 Marshall Sorenson
 * @license http://www.gnu.org/licenses/gpl.html GPLv2 or later
 * @package wp-sdl\helpers
 * @version 1.0
 */
class STUB_Options_Object_1_0 extends WP_SDL_Options_Object_1_0
{
	private $subitem;

	/**
	 * @return STUB_Options_Object_1_0
	 */
	final public function subitem( $slug )
	{
		$this->subitem = $this->get_child_auto( $slug, 'STUB_Options_Object_1_0' );
		
		return $this->subitem;
	}
}