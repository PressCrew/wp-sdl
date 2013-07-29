<?php
/**
 * Base Classes
 *
 * @link http://wp-sdl.org/
 * @author Marshall Sorenson
 * @link http://marshallsorenson.com/
 * @copyright Copyright (C) 2013 Marshall Sorenson
 * @license http://www.gnu.org/licenses/gpl.html GPLv2 or later
 * @package wp-sdl
 */

/**
 * WordPress Standard Developer's Library
 *
 * @package wp-sdl
 */
final class WP_SDL
{
	/**
	 * Array of paths to library class files.
	 * 
	 * @var array
	 */
	private static $files_ready = array();

	/**
	 * Array of class paths which have been loaded
	 */
	private static $files_loaded = array();
	
	/**
	 * Array of WP_SDL instances
	 *
	 * @var array
	 */
	private static $instances = array();

	/**
	 * Initialize n+ WP_SDL versions
	 *
	 * @param string $class_path
	 * @param array $class_names
	 */
	final static public function init( $class_path, $class_names )
	{
		// loop all versions
		foreach ( $class_names as $class_name => $class_ns ) {
			// already registered?
			if ( !isset( self::$files_ready[ $class_name ] ) ) {
				// has a pseudo namespace?
				$prefix = ( $class_ns ) ? $class_ns . '/' : null;
				// nope, register it
				self::$files_ready[ $class_name ] = $class_path . '/' . $prefix . self::class_file( $class_name );
			}
		}
	}
	
	/**
	 * Load registered class.
	 */
	final static public function load_class( $class_name )
	{
		// make sure class been not been loaded yet
		if ( false === class_exists( $class_name, false ) ) {
			// load class file
			require_once self::$files_ready[ $class_name ];
			// set as loaded
			self::$files_loaded[ $class_name ] = true;
		}
	}

	/**
	 * Return the singleton instance of the given class
	 *
	 * @param string $class_name
	 * @return WP_SDL
	 */
	final static public function instance( $class_name )
	{
		// have an instance of this version yet?
		if (
			!isset( self::$instances[ $class_name ] ) ||
			!self::$instances[ $class_name ] instanceof $class_name
		) {
			// nope, call class loader
			self::load_class( $class_name );
			// create new instance
			self::$instances[ $class_name ] = new $class_name();
		}

		// return version instance
		return self::$instances[ $class_name ];
	}

	/**
	 * Return the singleton instance of WP_SDL for the given version.
	 *
	 * @param string $version Version number
	 * @return WP_SDL
	 */
	final static public function support( $version )
	{
		// get class name
		$class_name = self::class_name( $version );

		// return instance of class
		return self::instance( $class_name );
	}

	/**
	 * Return all supported versions, or true if specified version is supported
	 *
	 * @param string|null $version Optional version number
	 * @return array|boolean
	 */
	final static public function supports( $version = null )
	{
		// specific version?
		if ( $version ) {
			// get class name
			$class_name = self::class_name( $version );
			// check if version is registered
			return array_key_exists( $class_name, self::$files_ready );
		} else {
			// return all versions
			return array_keys( self::$files_ready );
		}
	}

	/**
	 * Return syntax safe class name
	 *
	 * @param string $suffix
	 * @return string
	 */
	final static public function class_name( $suffix )
	{
		return 'WP_SDL_' . preg_replace( '#[^\w]#', '_', $suffix );
	}

	/**
	 * Return class file name
	 *
	 * @param string $class_name
	 * @return string
	 */
	final static public function class_file( $class_name )
	{
		return strtolower( $class_name ) . '.php';
	}
}

/**
 * WP-SDL: compat interface
 *
 * @internal
 * @package wp-sdl
 */
interface WP_SDL_Compat
{
	public function doing_it_wrong( $method, $message, $version );
	public function helper( $name, $version );
}

/**
 * WP-SDL: auxiliary interface
 *
 * @internal
 * @package wp-sdl
 */
interface WP_SDL_Auxiliary
{
	/**
	 * Set/Get compat instance.
	 *
	 * @param WP_SDL_Compat $wpsdl
	 * @return WP_SDL_Compat
	 */
	public function compat( WP_SDL_Compat $wpsdl = null );
}

/**
 * WP-SDL: helper interface
 *
 * @internal
 * @package wp-sdl\helpers
 */
interface WP_SDL_Helper extends WP_SDL_Auxiliary
{
	// nothing special yet
}
