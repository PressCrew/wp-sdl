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
class STUB_Options_Object_1_0 extends WP_SDL_Options_Item_1_0
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
	 * @return STUB_Options_Object_1_0
	 */
	public function stub( $slug )
	{
		return $this->item( $slug, $this );
	}

	/**
	 * @return STUB_Options_Object_1_0
	 */
	protected function item( $slug, WP_SDL_Options_Object_1_0 $parent )
	{
		if ( false === isset( $this->items[ $slug ] ) ) {
			// create new instance of class
			$item = new STUB_Options_Object_1_0( $slug, $this->helper(), $parent->config() );
			// add to parent
			$parent->child( $slug, $item );
			// add to items
			$this->items[ $slug ] = $item;
		}

		// return it
		return $this->items[ $slug ];
	}

	public function register() {}
	public function render() {}
	public function validate( $data ) {}

}