<?php
/**
 * Options Helper 1.0
 *
 * @link http://wp-sdl.org/
 * @author Marshall Sorenson
 * @link http://marshallsorenson.com/
 * @copyright Copyright (C) 2013 Marshall Sorenson
 * @license http://www.gnu.org/licenses/gpl.html GPLv2 or later
 * @package wp-sdl\helpers
 * @version 1.0
 */
class WP_SDL_Options_1_0 extends WP_SDL_Helper_1_0
{
	/**
	 * Class version
	 *
	 * @var string
	 */
	protected static $VERSION = '1.0';

	/**
	 * The config instances stack.
	 *
	 * @var array
	 */
	private $configs = array();

	/**
	 * Initialize a configuration.
	 *
	 * @param string $config_name The name of the configuration to initialize.
	 * @param string $page_name The plugin, theme, or other page name on which to initialize.
	 * @return WP_SDL_Options_1_0
	 */
	public function init( $config_name, $page_name = null )
	{
		// get config
		$config = $this->config( $config_name );

		// call init method of renderer
		$config->renderer()->init( $config, $page_name );

		// maintain the chain
		return $this;
	}

	/**
	 * Get the internal wpsdl plugin page.
	 *
	 * @return string|null
	 */
	final public function get_plugin_page()
	{
		global $plugin_page;

		if ( is_admin() && $plugin_page ) {
			return $plugin_page;
		} elseif ( isset( $_POST['wpsdl_plugin_page'] ) ) {
			return $_POST['wpsdl_plugin_page'];
		} else {
			return null;
		}
	}

	/**
	 * Get the internal wpsdl option group.
	 *
	 * @return string|null
	 */
	final public function get_option_group()
	{
		if ( isset( $_POST['wpsdl_option_group'] ) ) {
			return $_POST['wpsdl_option_group'];
		} else {
			return null;
		}
	}
	
	/**
	 * Set and return the current config instance.
	 *
	 * @param string $name
	 * @return WP_SDL_Options_Config_1_0
	 */
	public function config( $name )
	{
		// config exists yet?
		if ( false === isset( $this->configs[ $name ] ) ) {
			// create it
			$this->configs[ $name ] = new WP_SDL_Options_Config_1_0( $name, $this );
		}
		
		// return it!
		return $this->configs[ $name ];
	}

	/**
	 * Output the settings form for the given config name.
	 */
	function form( $config_name, $group_name )
	{
		// call the renderer
		$this->config( $config_name )->group( $group_name )->render();
	}

	/**
	 * Get/Set the option value for the given config and field name.
	 *
	 * @param string $config_name
	 * @param string $field_name
	 * @param mixed $newvalue
	 */
	final public function option( $config_name, $field_name, $newvalue = null )
	{
		// look up field
		$field = $this->config( $config_name )->field( $field_name );

		// get num args
		$num_args = func_num_args();

		// call field's option method
		if ( 2 === $num_args ) {
			// getting
			return $field->option();
		} else {
			// setting
			return $field->option( $newvalue );
		}
	}
}

abstract class WP_SDL_Options_Object_1_0 extends WP_SDL_Auxiliary_1_0
{
	/**
	 * The object instance's slug.
	 *
	 * @var string
	 */
	private $slug;

	/**
	 * Title.
	 *
	 * @var type
	 */
	private $title;

	/**
	 * Description.
	 *
	 * @var string
	 */
	private $description;

	/**
	 * Children belonging to this instance.
	 *
	 * @var WP_SDL_Struct_PriorityMap_1_0
	 */
	private $children;

	/**
	 * Constructor.
	 * 
	 * @param string $slug
	 * @param WP_SDL_Helper $helper
	 */
	public function __construct( $slug, WP_SDL_Helper $helper )
	{
		$this->slug( $slug );
		$this->helper( $helper );
	}

	/**
	 * Return value of the given property name.
	 *
	 * @param string $name
	 * @return mixed
	 * @throws InvalidArgumentException
	 */
	public function property( $name )
	{
		switch ( $name ) {
			case 'description':
			case 'slug':
			case 'title':
				return $this->$name;
			default:
				throw new InvalidArgumentException(
					sprintf( __( 'Reading the "%s" property failed, does not exist.', 'wp-sdl' ), $name )
				);
		}
	}

	/**
	 * Get/Set a child object.
	 *
	 * @param string $slug
	 * @param WP_SDL_Options_Object_1_0 $object
	 * @return WP_SDL_Options_Object_1_0
	 * @throws InvalidArgumentException
	 */
	public function child( $slug, WP_SDL_Options_Object_1_0 $object = null )
	{
		// get an object?
		if ( $object ) {
			// yep, we are setting... make sure slugs match
			if ( $object->property( 'slug' ) === $slug ) {
				// set myself as object's parent
				$object->parent( $this );
				// add/update to my children
				$this->children()->add(
					$object->property( 'slug' ),
					$object,
					$object->property( 'priority' ),
					false
				);
			} else {
				throw new InvalidArgumentException(
					__( 'The given slug does not match the object slug', 'wp-sdl' )
				);
			}
		}

		// return it
		return $this->children()->get( $slug );
	}

	/**
	 * Return child stack.
	 *
	 * @return WP_SDL_Struct_PriorityMap_1_0
	 */
	public function children()
	{
		if ( null === $this->children ) {
			$this->children = $this->helper()->compat()->struct()->priority_map();
		}

		return $this->children;
	}

	/**
	 * Set the slug property.
	 *
	 * Slug must start with a lowercase letter followed by only lowercase
	 * letters or numbers that are optionally separated by underscores (NO HYPHENS)
	 *
	 * @param string $slug
	 * @throws InvalidArgumentException
	 * @throws OverflowException
	 */
	private function slug( $slug )
	{
		// is slug null?
		if ( null === $this->slug ) {
			// yep, check slug string
			if ( 1 === preg_match( '/^[a-z]+(_?[a-z0-9])+$/', $slug ) ) {
				// slug is good, set it
				$this->slug = $slug;
			} else {
				// slug is bad, puke
				throw new InvalidArgumentException(
					__( 'The slug does not match the allowed pattern.', 'wp-sdl' )
				);
			}
		} else {
			// slug already set, puke
			throw new OverflowException(
				__( 'The slug has already been set, cannot overwrite.', 'wp-sdl' )
			);
		}
	}

	/**
	 * Set the title property.
	 *
	 * @param string $title
	 * @return WP_SDL_Options_Object_1_0
	 */
	final public function title( $title )
	{
		// set title
		$this->title = $title;

		// maintain the chain
		return $this;
	}

	/**
	 * Set the description property.
	 *
	 * @param string $desc
	 * @return WP_SDL_Options_Object_1_0
	 */
	final public function description( $desc )
	{
		// set desc
		$this->description = $desc;

		// maintain the chain
		return $this;
	}

	/**
	 * Return unique object id.
	 * 
	 * @return string
	 */
	abstract public function id();
}

class WP_SDL_Options_Config_1_0 extends WP_SDL_Options_Object_1_0
{
	/**
	 * Form mode "api"
	 *
	 * @link http://codex.wordpress.org/Settings_API
	 */
	const FORM_MODE_API = 'api';

	/**
	 * Form mode "theme"
	 *
	 * @link http://codex.wordpress.org/Theme_Modification_API
	 */
	const FORM_MODE_THEME = 'theme';

	/**
	 * Form mode "custom"
	 */
	const FORM_MODE_CUSTOM = 'custom';
	
	/**
	 * Save mode "all"
	 */
	const SAVE_MODE_ALL = 'all';

	/**
	 * Save mode "group"
	 */
	const SAVE_MODE_GROUP = 'group';

	/**
	 * Save mode "section"
	 */
	const SAVE_MODE_SECTION = 'section';

	/**
	 * The current form mode.
	 *
	 * @var string
	 */
	private $form_mode = self::FORM_MODE_API;

	/**
	 * The current save mode.
	 *
	 * @var string
	 */
	private $save_mode = self::SAVE_MODE_ALL;

	/**
	 * Item instances stack.
	 *
	 * @var array
	 */
	private $items = array();

	/**
	 * Form renderer instance.
	 *
	 * @var WP_SDL_Options_Form_1_0
	 */
	private $renderer;
	
	/**
	 */
	public function __construct( $slug, WP_SDL_Helper $helper )
	{
		// call parent first
		parent::__construct($slug, $helper);

		// set default form mode
		$this->form_mode( $this->form_mode );
	}

	/**
	 */
	public function property( $name )
	{
		switch ( $name ) {
			case 'form_mode':
			case 'save_mode':
				return $this->$name;
			default:
				return parent::property( $name );
		}
	}

	/**
	 */
	final public function id()
	{
		return $this->property( 'slug' ) . '_all';
	}

	/**
	 * Get/Set the option value for the given field name.
	 *
	 * @param string $field_name
	 * @param mixed $newvalue
	 */
	final public function option( $field_name, $newvalue = null )
	{
		// look up field
		$field = $this->field( $field_name );

		// get num args
		$num_args = func_num_args();

		// call field's option method
		if ( 1 === $num_args ) {
			// getting
			return $field->option();
		} else {
			// setting
			return $field->option( $newvalue );
		}
	}

	/**
	 * Register all of this config's settings.
	 */
	final public function register()
	{
		// loop all group
		foreach ( $this->children() as $group ) {
			// register each group
			$group->register();
		}

		// maybe register setting
		if ( $this->save_mode_is( 'all' ) ) {
			// option name is config slug
			$this->register_setting( $this );
		}

		// maintain the chain
		return $this;
	}

	/**
	 * Register an object with the WP Settings API.
	 *
	 * @param WP_SDL_Options_Object_1_0 $object
	 * @return WP_SDL_Options_Config_1_0
	 */
	public function register_setting( WP_SDL_Options_Object_1_0 $object )
	{
		// register the setting with wp
		register_setting(
			'wpsdl_' . $this->property( 'slug' ),
			$object->id() . '_opts',
			array( $this, 'sanitize_fields' )
		);

		// maintain the chain
		return $this;
	}

	/**
	 * Item factory method.
	 *
	 * @param string $type Can be 'group', 'section', or 'field'
	 * @param string $slug
	 * @param WP_SDL_Options_Object_1_0 $parent
	 * @return WP_SDL_Options_Item_1_0
	 * @throws InvalidArgumentException
	 */
	public function item( $type, $slug, WP_SDL_Options_Object_1_0 $parent = null )
	{
		// child exists for parent?
		if ( false === isset( $this->items[ $type ][ $slug ] ) ) {
			// determing class to create
			switch( $type ) {
				case 'group':
					$child_class = 'WP_SDL_Options_Group_1_0';
					break;
				case 'section':
					$child_class = 'WP_SDL_Options_Section_1_0';
					break;
				case 'field':
					$child_class = 'WP_SDL_Options_Field_1_0';
					break;
				default:
					throw new InvalidArgumentException( __( 'Invalid type', 'wp-sdl' ) );
			}
			// create new instance of class
			$item = new $child_class( $slug, $this->helper(), $this );
			// add to items stack
			$this->items[ $type ][ $slug ] = $item;
			// add to parent?
			if ( $parent ) {
				// do it
				$parent->child( $slug, $item );
			}
		}

		// return it
		return $this->items[ $type ][ $slug ];
	}

	/**
	 * Return group instance for given slug.
	 *
	 * @param string $slug
	 * @return WP_SDL_Options_Group_1_0
	 */
	final public function group( $slug )
	{
		return $this->item( 'group', $slug, $this );
	}

	/**
	 * Return section instance for given slug.
	 *
	 * @param string $slug
	 * @param WP_SDL_Options_Group_1_0 $group
	 * @return WP_SDL_Options_Section_1_0
	 */
	final public function section( $slug, WP_SDL_Options_Group_1_0 $group = null )
	{
		return $this->item( 'section', $slug, $group );
	}

	/**
	 * Return field instance for given slug.
	 *
	 * @param string $slug
	 * @param WP_SDL_Options_Section_1_0 $section
	 * @return WP_SDL_Options_Field_1_0
	 */
	final public function field( $slug, WP_SDL_Options_Section_1_0 $section = null )
	{
		return $this->item( 'field', $slug, $section );
	}

	/**
	 * Set the save mode for this config.
	 *
	 * @param string $mode
	 * @return WP_SDL_Options_Config_1_0
	 * @throws InvalidArgumentException
	 */
	final public function save_mode( $mode )
	{
		// make sure its valid
		switch ( $mode ) {
			case self::SAVE_MODE_ALL:
			case self::SAVE_MODE_GROUP:
			case self::SAVE_MODE_SECTION:
				// set it
				$this->save_mode = $mode;
				break;
			default:
				throw new InvalidArgumentException(
					sprintf( __( 'The "%s" save mode is not valid.', 'wp-sdl' ) , $mode )
				);
		}
		
		// maintain the chain
		return $this;
	}

	/**
	 * Returns true if given mode matches current save mode.
	 *
	 * @param string $mode
	 * @return boolean
	 */
	final public function save_mode_is( $mode )
	{
		return ( $mode === $this->save_mode );
	}

	/**
	 * Set the form mode for this config.
	 *
	 * @param string $mode
	 * @param WP_SDL_Options_Form_1_0 $renderer
	 * @return WP_SDL_Options_Config_1_0
	 * @throws InvalidArgumentException
	 */
	final public function form_mode( $mode, WP_SDL_Options_Form_1_0 $renderer = null )
	{
		// make sure its valid
		switch ( $mode ) {
			case self::FORM_MODE_API:
			case self::FORM_MODE_THEME:
			case self::FORM_MODE_CUSTOM:
				// set it
				$this->form_mode = $mode;
				break;
			default:
				throw new InvalidArgumentException(
					sprintf( __( 'The "%s" form mode is not valid.', 'wp-sdl' ) , $mode )
				);
		}

		// get a custom renderer?
		if ( null === $renderer ) {
			// nope, set one based on form mode
			switch ( $this->form_mode ) {
				// api mode
				case self::FORM_MODE_API:
					$renderer = new WP_SDL_Options_Form_Api_1_0( $this->helper() );
					break;
				// theme mode
				case self::FORM_MODE_THEME:
					$renderer = new WP_SDL_Options_Form_Theme_1_0( $this->helper() );
					break;
				// custom mode
				case self::FORM_MODE_CUSTOM:
					// no renderer? use default
					$renderer = new WP_SDL_Options_Form_Default_1_0( $this->helper() );
					break;
			}
		}
		
		// set the new renderer
		$this->renderer = $renderer;
		
		// maintain the chain
		return $this;
	}

	/**
	 * Returns true if given mode matches current form mode.
	 *
	 * @param string $mode
	 * @return boolean
	 */
	final public function form_mode_is( $mode )
	{
		return ( $mode === $this->form_mode );
	}

	/**
	 * Return current form renderer instance.
	 *
	 * @return WP_SDL_Options_Form_1_0
	 */
	final public function renderer()
	{
		// return it
		return $this->renderer;
	}

	/**
	 * Sanitize all submitted data.
	 *
	 * @param array $data Raw data.
	 * @return array Sanitized data.
	 */
	final public function sanitize_fields( $data )
	{
		// array of clean data to return
		$clean_data = array();

		// loop all fields, key is important
		foreach( $data as $field_slug => $field_value ) {
			// get field
			$field = $this->field( $field_slug );
			// call field sanitizer on value, and set in clean data array
			$clean_data[ $field_slug ] = $field->sanitize( $field_value );
			// check for error
			if ( $field->has_error() ) {
				// error occurred, call error handler of renderer
				$this->renderer()->error( $field );
			}
		}

		// return clean data
		return $clean_data;
	}
}

/**
 * Item
 */
abstract class WP_SDL_Options_Item_1_0 extends WP_SDL_Options_Object_1_0
{
	/**
	 * The config which "owns" this item.
	 *
	 * @var WP_SDL_Options_Config_1_0
	 */
	private $config;

	/**
	 * The parent of this instance.
	 *
	 * @var WP_SDL_Options_Object_1_0
	 */
	private $parent;

	/**
	 * Priority.
	 *
	 * @var integer
	 */
	private $priority = 10;

	/**
	 * Constructor
	 * 
	 * @param string $slug
	 * @param WP_SDL_Helper $helper
	 * @param WP_SDL_Options_Config_1_0 $config
	 */
	public function __construct( $slug, WP_SDL_Helper $helper, WP_SDL_Options_Config_1_0 $config )
	{
		// run parent
		parent::__construct( $slug, $helper );
		
		// set config
		$this->config = $config;
	}

	/**
	 */
	public function property( $name )
	{
		switch ( $name ) {
			case 'priority':
				return $this->$name;
			default:
				return parent::property( $name );
		}
	}

	/**
	 * Get config instance.
	 *
	 * @return WP_SDL_Options_Config_1_0
	 */
	public function config()
	{
		// return it
		return $this->config;
	}

	/**
	 * Set/Get parent instance.
	 *
	 * @param WP_SDL_Options_Object_1_0 $parent
	 * @return WP_SDL_Options_Object_1_0
	 */
	public function parent( WP_SDL_Options_Object_1_0 $parent = null )
	{
		if ( null !== $parent ) {
			$this->parent = $parent;
		}

		return $this->parent;
	}

	/**
	 * Set the priority property.
	 *
	 * @param integer $priority
	 * @return WP_SDL_Options_Object_1_0
	 */
	final public function priority( $priority )
	{
		// set attributes
		if ( is_numeric( $priority ) ) {
			// update priority property
			$this->priority = (integer) $priority;
			// have a parent?
			if ( null !== $this->parent ) {
				// update priority in parent for sorting
				$this->parent->children()->priority_update( $this->property( 'slug' ), $this->priority );
			}
		} else {
			throw new InvalidArgumentException(
				__( 'The $priority parameter must be a number.', 'wp-sdl' )
			);
		}

		// maintain the chain
		return $this;
	}

	/**
	 * Register all settings for this item.
	 */
	abstract public function register();

	/**
	 * Render the markup for this item.
	 */
	abstract public function render();
}

/**
 * Group
 */
class WP_SDL_Options_Group_1_0 extends WP_SDL_Options_Item_1_0
{
	/**
	 */
	final public function id()
	{
		return $this->config()->property( 'slug' ) . '_' . $this->property( 'slug' ) . '_group';
	}

	/**
	 * Register all of this groups's settings.
	 *
	 * @return WP_SDL_Options_Group_1_0
	 */
	final public function register()
	{
		// loop all sections
		foreach( $this->children() as $section ) {
			// register each section
			$section->register();
		}

		// maybe register setting
		if ( $this->config()->save_mode_is( 'group' ) ) {
			// option name is group name
			$this->config()->register_setting( $this );
		}

		// maintain the chain
		return $this;
	}

	/**
	 * Return section instance for given slug.
	 *
	 * @param string $slug
	 * @return WP_SDL_Options_Section_1_0
	 */
	final public function section( $slug )
	{
		// get section for slug
		return $this->config()->section( $slug, $this );
	}

	/**
	 * Render the form for this group.
	 *
	 * @return WP_SDL_Options_Group_1_0
	 */
	final public function render()
	{
		// call renderer
		$this->config()->renderer()->group( $this );

		// maintain the chain
		return $this;
	}
}

/**
 * Section
 */
class WP_SDL_Options_Section_1_0 extends WP_SDL_Options_Item_1_0
{
	/**
	 */
	final public function id()
	{
		return $this->config()->property( 'slug' ) . '_' . $this->property( 'slug' ) . '_section';
	}

	/**
	 * Register all of this section's settings (WordPress API).
	 * 
	 * @return WP_SDL_Options_Section_1_0
	 */
	final public function register()
	{
		// register section
		add_settings_section(
			$this->id(),
			$this->property( 'title' ),
			array( $this, 'render' ),
			$this->parent()->id()
		);

		// loop all fields
		foreach( $this->children() as $field ) {
			// register each field
			$field->register();
		}

		// maybe register setting
		if ( $this->config()->save_mode_is( 'section' ) ) {
			// option name is section name
			$this->config()->register_setting( $this );
		}

		// maintain the chain
		return $this;
	}

	/**
	 * Render the markup for this section.
	 *
	 * @return WP_SDL_Options_Section_1_0
	 */
	final public function render()
	{
		// call renderer
		$this->config()->renderer()->section( $this );

		// maintain the chain
		return $this;
	}

	/**
	 * Return field instance for given slug.
	 *
	 * @param string $slug
	 * @return WP_SDL_Options_Field_1_0
	 */
	final public function field( $slug )
	{
		// set current section
		return $this->config()->field( $slug, $this );
	}
}

/**
 * Field
 */
class WP_SDL_Options_Field_1_0 extends WP_SDL_Options_Item_1_0
{
	/**
	 * The field type.
	 * 
	 * @var string 
	 */
	private $type;
	
	/**
	 * The field attributes.
	 * 
	 * @var array
	 */
	private $attributes = array();
	
	/**
	 * The field value.
	 * 
	 * @var mixed 
	 */
	private $value;
	
	/**
	 * The field's current value.
	 * 
	 * If set to anything other than NULL, the current value overrides the standard value.
	 * 
	 * @var mixed
	 */
	private $current_value;

	/**
	 * The field's sanitization callback.
	 *
	 * @var callable
	 */
	private $sanitize_callback;

	/**
	 * Error object (if an error occured).
	 *
	 * @var WP_Error|null
	 */
	private $error;

	/**
	 */
	public function property( $name )
	{
		switch ( $name ) {
			case 'attributes':
			case 'current_value':
			case 'type':
			case 'value':
				return $this->$name;
			default:
				return parent::property( $name );
		}
	}

	/**
	 */
	final public function id()
	{
		return $this->config()->property( 'slug' ) . '_' . $this->property( 'slug' ) . '_field';
	}

	/**
	 * Register all of this field's settings (WordPress API).
	 *
	 * @return WP_SDL_Options_Field_1_0
	 */
	final public function register()
	{
		// register the field
		add_settings_field(
			$this->id(),
			$this->property( 'title' ),
			array( $this, 'render' ),
			$this->parent()->parent()->id(),
			$this->parent()->id()
		);

		// maintain the chain
		return $this;
	}

	/**
	 * Render the markup for this field.
	 *
	 * @return WP_SDL_Options_Field_1_0
	 */
	final public function render()
	{
		// call renderer
		$this->config()->renderer()->field( $this );

		// maintain the chain
		return $this;
	}

	/**
	 * Return sanitized value using this field's sanitize callback if applicable.
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	final public function sanitize( $value )
	{
		// have a sanitize callback?
		if ( is_callable( $this->sanitize_callback ) ) {
			// yep, call it
			return call_user_func( $this->sanitize_callback, $value, $this );
		} else {
			// return value untouched
			return $value;
		}
	}

	/**
	 * Get/Set an error for this field.
	 * 
	 * @param WP_Error|string|null $error
	 * @return WP_Error|null
	 */
	final public function error( $error = null )
	{
		// get number of args
		$num_args = func_num_args();

		// setting?
		if ( 1 === $num_args ) {
			// is it an error object?
			if ( $error instanceof WP_Error ) {
				// yep, set error property
				$this->error = $error;
			} elseif ( true === is_string( $error ) ) {
				// create error automagically
				$this->error = new WP_Error( $this->property( 'slug' ), $error );
			} else {
				// not good
				throw new InvalidArgumentException(
					__( 'The $error arg must be an instance of WP_Error or a string.', 'wp-sdl' )
				);
			}
		}

		// return it
		return $this->error;
	}

	/**
	 * Return true if an error condition exists.
	 *
	 * @return boolean
	 */
	final public function has_error()
	{
		return ( $this->error instanceof WP_Error );
	}

	/**
	 * Set the type property.
	 *
	 * @param string $type
	 * @return WP_SDL_Options_Field_1_0
	 */
	final public function type( $type )
	{
		// set type
		$this->type = $type;

		// maintain the chain
		return $this;
	}

	/**
	 * Set the attributes property.
	 *
	 * @param array $atts
	 * @return WP_SDL_Options_Field_1_0
	 */
	final public function attributes( $atts )
	{
		// set attributes
		if ( is_array( $atts ) ) {
			$this->attributes = $atts;
		} else {
			throw new InvalidArgumentException(
				__( 'The $atts parameter must be an array.', 'wp-sdl' )
			);
		}

		// maintain the chain
		return $this;
	}

	/**
	 * Set the value property.
	 *
	 * @param string $value
	 * @return WP_SDL_Options_Field_1_0
	 */
	final public function value( $value )
	{
		// set value
		$this->value = $value;

		// maintain the chain
		return $this;
	}

	/**
	 * Set the current value property.
	 *
	 * @param string $value
	 * @return WP_SDL_Options_Field_1_0
	 */
	final public function current_value( $value )
	{
		// set current value
		$this->current_value = $value;

		// maintain the chain
		return $this;
	}

	/**
	 * Set the sanitize callback.
	 *
	 * @param callable $callback
	 * @return WP_SDL_Options_Field_1_0
	 */
	final public function sanitize_callback( $callback )
	{
		// make sure its callable
		if ( true === is_callable( $callback ) ) {
			// set it
			$this->sanitize_callback = $callback;
		} else {
			// invalid argument
			throw new InvalidArgumentException( __( 'Sanitize callback must be callable.', 'wp-sdl' ) );
		}

		// maintain the chain
		return $this;
	}

	/**
	 * Get/Set the *stored* option value for this field.
	 *
	 * @param mixed $newvalue
	 * @return mixed
	 */
	final public function option( $newvalue = null )
	{
		// get option name
		$option_name = $this->option_name();

		// get option value
		$option_value = get_option( $option_name );

		// get option key
		$option_key = $this->option_key();

		// handle false result
		if ( false === $option_value ) {
			// empty array
			$option_value = array();
		}

		// get num args
		$num_args = func_num_args();

		// setting?
		if ( 1 === $num_args ) {
			// yep, update array
			$option_value[ $option_key ] = $newvalue;
			// update database
			update_option( $option_name, $option_value );
		}

		// is field value set?
		if ( isset( $option_value[ $option_key ] ) ) {
			// yep, return it
			return $option_value[ $option_key ];
		} else {
			// nope, return null
			return null;
		}
	}

	/**
	 * Return option name to pass to get_option().
	 *
	 * @return string
	 */
	final public function option_name()
	{
		// get save mode
		$save_mode = $this->config()->property( 'save_mode' );

		// determine which object to use
		switch ( $save_mode ) {
			case 'all':
				// use config
				$option_item = $this->config();
				break;
			case 'group':
				// use group
				$option_item = $this->parent()->parent();
				break;
			case 'section':
				// use section
				$option_item = $this->parent();
				break;
		}

		// return option name
		return $option_item->id() . '_opts';
	}

	/**
	 * Return "sub" key that option value is stored under.
	 *
	 * @return string
	 */
	final public function option_key()
	{
		return $this->property( 'slug' );
	}

}

abstract class WP_SDL_Options_Form_1_0 extends WP_SDL_Auxiliary_1_0
{
	/**
	 * Constructor.
	 *
	 * @param WP_SDL_Helper $helper
	 */
	public function __construct( WP_SDL_Helper $helper )
	{
		$this->helper( $helper );
	}
	
	abstract public function init( WP_SDL_Options_Config_1_0 $config, $page_name = null );
	abstract public function error( WP_SDL_Options_Field_1_0 $field );
	abstract public function group( WP_SDL_Options_Group_1_0 $group );
	abstract public function section( WP_SDL_Options_Section_1_0 $section );

	public function field( WP_SDL_Options_Field_1_0 $field )
	{
		// params
		$type = $field->property( 'type' );
		$value = $field->property( 'value' );
		$c_value = $field->property( 'current_value' );
		$atts = $field->property( 'attributes' );
		
		// format the name
		$name = sprintf( '%s[%s]', $field->option_name(), $field->option_key() );

		// handl missing id attribute
		if ( false === isset( $atts['id'] ) ) {
			$atts['id'] = $field->id();
		}

		// handle null current value
		if ( null === $c_value ) {
			// use stored value
			$c_value = $field->option();
		}

		/* @var $html_helper WP_SDL_Html_1_0 */
		$html_helper = $this->helper()->compat()->html();
		
		// render the field
		$html_helper->field( $type, $name, $value, $atts, $c_value );
	}
}

class WP_SDL_Options_Form_Api_1_0 extends WP_SDL_Options_Form_1_0
{

	public function init( WP_SDL_Options_Config_1_0 $config, $page_name = null )
	{
		// dashboard logic
		if ( is_admin() ) {
			// check page
			if ( $page_name == $this->helper()->get_plugin_page() ) {
				// register settings
				$config->register();
				// hook settings errors to admin notices
				add_action( 'admin_notices', 'settings_errors' );
			}
		}
	}
	
	public function error( WP_SDL_Options_Field_1_0 $field )
	{
		/* @var $error WP_Error */
		$error = $field->error();
		// add error to settings api
		add_settings_error(
			$field->property( 'title' ),
			$error->get_error_code(),
			$error->get_error_message()
		);
	}

	public function group( WP_SDL_Options_Group_1_0 $group )
	{
		global $plugin_page;
		
		// format option page name
		$option_page = 'wpsdl_' . $group->config()->property( 'slug' );

		// format option group name
		$option_group = $group->property( 'slug' );

		// render the form ?>
		<form action="options.php" method="POST">
			<input type="hidden" name="wpsdl_plugin_page" value="<?php echo esc_attr( $plugin_page ) ?>">
			<input type="hidden" name="wpsdl_option_group" value="<?php echo esc_attr( $option_group ) ?>">
			<?php settings_fields( $option_page ); ?>
			<?php do_settings_sections( $group->id() ); ?>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'wp-sdl' ); ?>">
			</p>
		</form><?php
	}
	
	public function section( WP_SDL_Options_Section_1_0 $section )
	{
		/* @var $html_helper WP_SDL_Html_1_0 */
		$html_helper = $this->helper()->compat()->html();

		// get the section description
		$desc = $section->property( 'description' );

		// render the description?
		if ( $desc ) {
			// yep, wrap it in a paragraph
			$html_helper
				->open( 'p' )
					->content( $desc )
				->close();
		}
	}

	public function field( WP_SDL_Options_Field_1_0 $field )
	{
		// call parent
		parent::field( $field, $name );

		// get description
		$desc = $field->property( 'description' );

		// render the description?
		if ( $desc ) {
			/* @var $html_helper WP_SDL_Html_1_0 */
			$html_helper = $this->helper()->compat()->html();
			// yep, wrap it in a paragraph
			$html_helper
				->open( 'p', array( 'class' => 'description' ) )
					->content( $desc )
				->close();
		}
	}
}

class WP_SDL_Options_Form_Theme_1_0 extends WP_SDL_Options_Form_1_0
{
	public function init( WP_SDL_Options_Config_1_0 $config, $page_name = null ) {}
	public function error( WP_SDL_Options_Field_1_0 $field ) {}
	public function group( WP_SDL_Options_Group_1_0 $group ) {}
	public function section( WP_SDL_Options_Section_1_0 $section ) {}
	public function field( WP_SDL_Options_Field_1_0 $field ) {}
}

class WP_SDL_Options_Form_Default_1_0 extends WP_SDL_Options_Form_1_0
{
	public function init( WP_SDL_Options_Config_1_0 $config, $page_name = null ) {}
	public function error( WP_SDL_Options_Field_1_0 $field ) {}
	public function group( WP_SDL_Options_Group_1_0 $group ) {}
	public function section( WP_SDL_Options_Section_1_0 $section ) {}
	public function field( WP_SDL_Options_Field_1_0 $field ) {}
}
