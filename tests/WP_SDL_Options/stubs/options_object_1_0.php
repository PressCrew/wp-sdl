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
	private $items = array();

	/**
	 * @return string
	 */
	final public function id()
	{
		return $this->property( 'slug' );
	}

	/**
	 * Return group instance for given slug.
	 *
	 * @param string $slug
	 * @return WP_SDL_Options_Group_1_0
	 */
	public function subitem( $slug )
	{
		// child exists?
		if ( false === isset( $this->items[ $slug ] ) ) {
			// create new instance of class
			$item = new STUB_Options_Object_1_0( $slug, $this->helper() );
			// set parent
			$item->parent( $this );
			// add to children
			$this->children()->add( $slug, $item, 0 );
			// add to items
			$this->items[ $slug ] = $item;
			// return it
			return $item;
		}

		// return it
		return $this->items[ $slug ];
	}
}