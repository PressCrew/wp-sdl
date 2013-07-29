<?php
/**
 * Auxiliary 1.0
 *
 * @link http://wp-sdl.org/
 * @author Marshall Sorenson
 * @link http://marshallsorenson.com/
 * @copyright Copyright (C) 2013 Marshall Sorenson
 * @license http://www.gnu.org/licenses/gpl.html GPLv2 or later
 * @package wp-sdl
 * @version 1.0
 */
abstract class WP_SDL_Auxiliary_1_0 implements WP_SDL_Auxiliary
{
	/**
	 * The helper which owns this instance
	 *
	 * @var WP_SDL_Helper
	 */
	private $helper;

	/**
	 * Set/Get helper instance.
	 *
	 * @param WP_SDL_Helper $helper
	 * @return WP_SDL_Helper
	 */
	final public function helper( WP_SDL_Helper $helper = null )
	{
		if ( $helper ) {
			if ( null === $this->helper ) {
				$this->helper = $helper;
			} else {
				throw new OverflowException( __( 'Helper instance cannot be overwritten once set.', 'wp-sdl' ) );
			}
		}

		return $this->helper;
	}
}
