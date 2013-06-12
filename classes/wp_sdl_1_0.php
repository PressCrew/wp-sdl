<?php
/**
 * Library 1.0
 *
 * @link http://wp-sdl.org/
 * @author Marshall Sorenson
 * @link http://marshallsorenson.com/
 * @copyright Copyright (C) 2013 Marshall Sorenson
 * @license http://www.gnu.org/licenses/gpl.html GPLv2 or later
 * @package wp-sdl
 * @version 1.0
 */
class WP_SDL_1_0 implements WP_SDL_Compat
{
	/**
	 * Map of helper names to their singleton instances
	 *
	 * @var array
	 */
	private static $helper_instances = array();

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// all helpers require this base classs
		WP_SDL::load_class( 'WP_SDL_Helper_1_0' );
	}

	/**
	 * Trigger a PHP warning
	 *
	 * @param string $method The method that was called.
	 * @param string $message A message explaining what has been done incorrectly.
	 * @param string $version The version of WP-SDL where the message was added.
	 */
	public function doing_it_wrong( $method, $message, $version )
	{
		trigger_error(
			sprintf(
				__( '%1$s was called <strong>incorrectly</strong>. %2$s %3$s', 'wp-sdl' ),
				$method,
				$message,
				$version
			),
			E_USER_WARNING
		);
	}

	/**
	 * Return singleton instance of given helper's registered class
	 *
	 * @param string $name
	 * @param string $version
	 * @return WP_SDL_Helper
	 * @throws Exception
	 */
	public function helper( $name, $version )
	{
		// get class name
		$helper_class = WP_SDL::class_name( ucfirst( $name ) . '_' . $version );

		// check if instance already set up
		if ( !isset( self::$helper_instances[ $helper_class ] ) ) {
			// get instance from lib factory
			// IMPORTANT: in the case of helpers, the WP_SDL factory stores a
			// *prototype* object which must be cloned. For concurrent compatibility to
			// work properly, each helper's compat object pointer must be of the version
			// that loaded it. This is impossible to accomplish if every WP_SDL_Compat
			// instance shares the same singleton instance of a helper class, because
			// a helper's compat object pointer *cannot be changed once set*.
			self::$helper_instances[ $helper_class ] = clone WP_SDL::instance( $helper_class );
			// set compat
			self::$helper_instances[ $helper_class ]->compat( $this );
		}

		// return the instance
		return self::$helper_instances[ $helper_class ];
	}

	/**
	 * Return HTML helper.
	 *
	 * @param string $version
	 * @return WP_SDL_Html_1_0
	 */
	public function html( $version = '1.0' )
	{
		return $this->helper( 'html', $version );
	}

	/**
	 * Return Options helper.
	 *
	 * @param string $version
	 * @return WP_SDL_Options_1_0
	 */
	public function options( $version = '1.0' )
	{
		return $this->helper( 'options', $version );
	}

	/**
	 * Return data structures helper.
	 *
	 * @param string $version
	 * @return WP_SDL_Struct_1_0
	 */
	public function struct( $version = '1.0' )
	{
		return $this->helper( 'struct', $version );
	}

	/**
	 * Return Widget helper.
	 *
	 * @param string $version
	 * @return WP_SDL_Widget_1_0
	 */
	public function widget( $version = '1.0' )
	{
		return $this->helper( 'widget', $version );
	}
}
