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
	 * Priority.
	 *
	 * @var integer
	 */
	private $priority = 10;

	/**
	 * The parent of this instance.
	 *
	 * @var WP_SDL_Options_Object_1_0
	 */
	private $parent;

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
			case 'priority':
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
				$this->parent->children()->priority_update( $this->slug, $this->priority );
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
	 * Return unique object id.
	 * 
	 * @return string
	 */
	abstract public function id();
}

class WP_SDL_Options_Config_1_0 extends WP_SDL_Options_Object_1_0
{
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
	 * The current save mode.
	 *
	 * @var string
	 */
	private $save_mode = self::SAVE_MODE_ALL;

	/**
	 * Group instances stack.
	 *
	 * @var array
	 */
	private $groups = array();

	/**
	 * Section instances stack.
	 *
	 * @var array
	 */
	private $sections = array();

	/**
	 * Field instances stack.
	 *
	 * @var array
	 */
	private $fields = array();
	
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
	 * Return group instance for given slug.
	 *
	 * @param string $slug
	 * @return WP_SDL_Options_Group_1_0
	 */
	final public function group( $slug )
	{
		// child exists?
		if ( false === isset( $this->groups[ $slug ] ) ) {
			// nope, create new instance of class
			$group = new WP_SDL_Options_Group_1_0( $slug, $this->helper() );
			// set parent
			$group->parent( $this );
			// add to children
			$this->children()->add( $slug, $group, 0 );
			// add to groups
			$this->groups[ $slug ] = $group;
			// return it
			return $group;
		}

		// return it
		return $this->groups[ $slug ];
	}

	/**
	 * Return section instance for given slug.
	 *
	 * @param string $slug
	 * @param WP_SDL_Options_Group_1_0 $group
	 * @return WP_SDL_Options_Section_1_0
	 */
	final public function section( $slug, WP_SDL_Options_Group_1_0 $group )
	{
		// child exists?
		if ( false === isset( $this->sections[ $slug ] ) ) {
			// nope, create new instance of class
			$section = new WP_SDL_Options_Section_1_0( $slug, $this->helper() );
			// set parent
			$section->parent( $group );
			// add to children
			$group->children()->add( $slug, $section, 0 );
			// add to sections
			$this->sections[ $slug ] = $section;
			// return it
			return $section;
		}

		// return it
		return $this->sections[ $slug ];
	}

	/**
	 * Return field instance for given slug.
	 *
	 * @param string $slug
	 * @param WP_SDL_Options_Section_1_0 $section
	 * @return WP_SDL_Options_Field_1_0
	 */
	final public function field( $slug, WP_SDL_Options_Section_1_0 $section )
	{
		// child exists?
		if ( false === isset( $this->fields[ $slug ] ) ) {
			// nope, create new instance of class
			$field = new WP_SDL_Options_Field_1_0( $slug, $this->helper() );
			// set parent
			$field->parent( $section );
			// add to children
			$section->children()->add( $slug, $field, 0 );
			// add to sections
			$this->fields[ $slug ] = $field;
			// return it
			return $field;
		}

		// return it
		return $this->fields[ $slug ];
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

	final public function settings( $group_name )
	{
		$this->helper()->settings( $this->id(), $group_name );
	}

	final public function validate( $data )
	{
		return $data;
	}
}

class WP_SDL_Options_Group_1_0 extends WP_SDL_Options_Object_1_0
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

class WP_SDL_Options_Section_1_0 extends WP_SDL_Options_Object_1_0
{
	/**
	 */
	final public function id()
	{
		return $this->parent()->parent()->property( 'slug' ) . '_' . $this->property( 'slug' ) . '_section';
	}

	/**
	 * Register all of this section's settings.
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

class WP_SDL_Options_Field_1_0 extends WP_SDL_Options_Object_1_0
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
	 * Register all of this field's settings.
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
