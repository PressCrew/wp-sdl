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
	 * The compat which owns this instance
	 *
	 * @var WP_SDL_Compat
	 */
	private $compat;

	/**
	 * Set/Get compat instance.
	 *
	 * @param WP_SDL_Compat $wpsdl
	 * @return WP_SDL_Compat
	 */
	final public function compat( WP_SDL_Compat $wpsdl = null )
	{
		if ( $wpsdl ) {
			if ( null === $this->compat ) {
				$this->compat = $wpsdl;
			} else {
				throw new OverflowException( __( 'Compat instance cannot be overwritten once set.', 'wp-sdl' ) );
			}
		}

		return $this->compat;
	}
}
