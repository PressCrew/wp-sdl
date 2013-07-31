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
	function settings( $config_name, $group_name )
	{
		// get the config
		$config = $this->config( $config_name );

		// get the group
		$group = $config->group( $group_name );

		// format option page name
		$option_page = 'wpsdl_' . $config->id();

		// render the form ?>
		<form action="options.php" method="POST">
			<?php settings_fields( $option_page ); ?>
			<?php do_settings_sections( $group->id() ); ?>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'wp-sdl' ); ?>">
			</p>
		</form><?php
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
	 * Form mode "default"
	 */
	const FORM_MODE_DEFAULT = 'default';

	/**
	 * Form mode "settings"
	 */
	const FORM_MODE_SETTINGS = 'settings';

	/**
	 * Form mode "theme"
	 */
	const FORM_MODE_THEME = 'theme';
	
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
	private $form_mode = self::FORM_MODE_DEFAULT;

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
	 */
	final public function id()
	{
		return $this->property( 'slug' ) . '_config';
	}

	/**
	 * Register all of this config's settings.
	 */
	final public function register()
	{
		// loop all group
		foreach ( $this->children() as $group ) {
			// register each group
			$group->register( $this );
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
			$object->id() . '_settings',
			array( $this, 'validate' )
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
			$item = new $child_class( $slug, $this->helper() );
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
	 * @return WP_SDL_Options_Config_1_0
	 * @throws InvalidArgumentException
	 */
	final public function form_mode( $mode )
	{
		// make sure its valid
		switch ( $mode ) {
			case self::FORM_MODE_DEFAULT:
			case self::FORM_MODE_SETTINGS:
			case self::FORM_MODE_THEME:
				// set it
				$this->form_mode = $mode;
				break;
			default:
				throw new InvalidArgumentException(
					sprintf( __( 'The "%s" form mode is not valid.', 'wp-sdl' ) , $mode )
				);
		}
		
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
	 * Generate settings form for a group (WordPress API).
	 *
	 * @param string $group_name
	 */
	final public function settings( $group_name )
	{
		$this->helper()->settings( $this->id(), $group_name );
	}

	final public function validate( $data )
	{
		return $data;
	}
}

/**
 * Item
 */
abstract class WP_SDL_Options_Item_1_0 extends WP_SDL_Options_Object_1_0
{
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
		return $this->parent()->property( 'slug' ) . '_' . $this->property( 'slug' ) . '_group';
	}

	/**
	 * Register all of this groups's settings.
	 *
	 * @param WP_SDL_Options_Config_1_0 $config
	 * @return WP_SDL_Options_Group_1_0
	 */
	final public function register( WP_SDL_Options_Config_1_0 $config )
	{
		// loop all sections
		foreach( $this->children() as $section ) {
			// register each section
			$section->register( $config );
		}

		// maybe register setting
		if ( $config->save_mode_is( 'group' ) ) {
			// option name is group name
			$config->register_setting( $this );
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
		return $this->parent()->section( $slug, $this );
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
		return $this->parent()->parent()->property( 'slug' ) . '_' . $this->property( 'slug' ) . '_section';
	}

	/**
	 * Register all of this section's settings (WordPress API).
	 * 
	 * @param WP_SDL_Options_Config_1_0 $config
	 * @return WP_SDL_Options_Section_1_0
	 */
	final public function register( WP_SDL_Options_Config_1_0 $config )
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
			$field->register( $config );
		}

		// maybe register setting
		if ( $config->save_mode_is( 'section' ) ) {
			// option name is section name
			$config->register_setting( $this );
		}

		// maintain the chain
		return $this;
	}

	/**
	 * Render section intro content.
	 */
	public function render()
	{
		/* @var $html_helper WP_SDL_Html_1_0 */
		$html_helper = $this->helper()->compat()->html();

		// get the section description
		$desc = $this->property( 'description' );

		// render the description?
		if ( $desc ) {
			// yep, wrap it in a paragraph
			$html_helper
				->open( 'p' )
					->content( $desc )
				->close();
		}
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
		return $this->parent()->parent()->field( $slug, $this );
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
		return $this->parent()->parent()->parent()->property( 'slug' ) . '_' . $this->property( 'slug' ) . '_field';
	}

	/**
	 * Register all of this field's settings (WordPress API).
	 *
	 * @param WP_SDL_Options_Config_1_0 $config
	 * @return WP_SDL_Options_Field_1_0
	 */
	final public function register( WP_SDL_Options_Config_1_0 $config )
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
	 * Render this field.
	 */
	public function render()
	{
		/* @var $html_helper WP_SDL_Html_1_0 */
		$html_helper = $this->helper()->compat()->html();

		// params
		$name = $this->id();
		$type = $this->property( 'type' );
		$desc = $this->property( 'description' );
		$value = $this->property( 'value' );
		$c_value = $this->property( 'current_value' );
		$atts = $this->property( 'attributes' );

		// set id att to name if missing
		if ( false === isset( $atts['id'] ) ) {
			$atts['id'] = $name;
		}

		// render the field
		$html_helper->field( $type, $name, $value, $atts, $c_value );

		// render the description?
		if ( $desc ) {
			// yep, wrap it in a paragraph
			$html_helper
				->open( 'p', array( 'class' => 'description' ) )
					->content( $desc )
				->close();
		}
	}

	public function validate( $data )
	{
		return $data;
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

}
