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
	 * The current config instance.
	 *
	 * @var WP_SDL_Options_Config_1_0
	 */
	private $config;

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
			$this->configs[ $name ] = new WP_SDL_Options_Config_1_0( $name );
		}
		
		// point to it locally
		$this->config = $this->configs[ $name ];

		// return it!
		return $this->config;
	}

	public function register_settings()
	{
		if ( $this->config ) {

		}

		foreach ($wpsf_settings as $section) {
			if (isset($section['section_id']) && $section['section_id'] && isset($section['section_title'])) {
				add_settings_section($section['section_id'], $section['section_title'], array(&$this, 'section_intro'), $this->option_group);
				if (isset($section['fields']) && is_array($section['fields']) && !empty($section['fields'])) {
					foreach ($section['fields'] as $field) {
						if (isset($field['id']) && $field['id'] && isset($field['title'])) {
							add_settings_field($field['id'], $field['title'], array(&$this, 'generate_setting'), $this->option_group, $section['section_id'], array('section' => $section, 'field' => $field));
						}
					}
				}
			}
		}
	}

	/**
	 * Render one field.
	 * 
	 * @param WP_SDL_Options_Field_1_0 $field
	 */
	public function render_field( WP_SDL_Options_Field_1_0 $field )
	{
		/* @var $html_helper WP_SDL_Html_1_0 */
		$html_helper = $this->compat()->html();

		// params
		$type = $field->property( 'type' );
		$name = $field->property( 'slug' );
		$desc = $field->property( 'description' );
		$value = $field->property( 'value' );
		$c_value = $field->property( 'current_value' );
		$atts = $field->property( 'attributes' );

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

}


class WP_SDL_Options_Object_1_0
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
	 * Children belonging to this instance.
	 *
	 * @var array
	 */
	private $children = array();

	/**
	 * Constructor.
	 * 
	 * @param string $slug
	 */
	public function __construct( $slug )
	{
		$this->slug( $slug );
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
	 * Returns true if child exists.
	 *
	 * @param string $slug
	 * @return boolean
	 */
	protected function has_child( $slug )
	{
		return isset( $this->children[ $slug ] );
	}
	
	/**
	 * Add child object.
	 *
	 * @param string $slug
	 * @param WP_SDL_Options_Object_1_0 $object
	 * @throws OverflowException
	 */
	protected function add_child( $slug, WP_SDL_Options_Object_1_0 $object )
	{
		// child NOT already exists?
		if ( false === isset( $this->children[ $slug ] ) ) {
			// nope, add to children
			$this->children[ $slug ] = $object;
		} else {
			// child already set, puke
			throw new OverflowException(
				sprintf( __( 'The "%s" child has already been set, cannot overwrite.', 'wp-sdl' ), $slug )
			);
		}
	}

	/**
	 * Return child instance for given slug.
	 *
	 * @param string $slug
	 * @return WP_SDL_Options_Object_1_0
	 * @throws InvalidArgumentException
	 */
	protected function get_child( $slug )
	{
		// child exists?
		if ( true === isset( $this->children[ $slug ] ) ) {
			// yep, return it
			return $this->children[ $slug ];
		} else {
			// child doesn't exist, puke
			throw new InvalidArgumentException(
				sprintf( __( 'The "%s" child does not exist.', 'wp-sdl' ), $slug )
			);
		}
	}

	/**
	 * Get child instance, create new one if necessary.
	 *
	 * @param string $slug
	 * @param string $class A valid PHP class name.
	 * @return WP_SDL_Options_Object_1_0
	 * @throws InvalidArgumentException
	 */
	protected function get_child_auto( $slug, $class )
	{
		// child exists?
		if ( true === $this->has_child( $slug ) ) {
			// yep, return it
			return $this->get_child( $slug );
		} else {
			// does class exist for reals?
			if ( class_exists( $class, false ) ) {
				// create new instance of class
				$instance = new $class( $slug );
				// add to children
				$this->add_child( $slug, $instance );
				// return it
				return $this->get_child( $slug );
			} else {
				// class doesn't exist, puke
				throw new InvalidArgumentException(
					sprintf( __( 'The "%s" class does not exist.', 'wp-sdl' ), $class )
				);
			}
		}
	}

	/**
	 * Returns true if has one or more children.
	 *
	 * @return boolean
	 */
	public function has_children()
	{
		return ( $this->count_children() );
	}

	/**
	 * Return total number of children.
	 *
	 * @return integer
	 */
	public function count_children()
	{
		return count( $this->children );
	}

	/**
	 * Return all children.
	 *
	 * @return array
	 */
	public function get_children( $by_priority = false )
	{
		// sort by priority?
		if ( true === $by_priority ) {
			// copy children array
			$result = $this->children;
			// sort, preserving keys
			uasort( $result, array( $this, 'cmp_children' ) );
			// return sorted array
			return $result;
		} else {
			// return as is
			return $this->children;
		}
	}

	/**
	 * Comparison callback method to sort children by the given property
	 * @param WP_SDL_Options_Object_1_0 $a
	 * @param WP_SDL_Options_Object_1_0 $b
	 * @return type
	 */
	public function cmp_children( WP_SDL_Options_Object_1_0 $a, WP_SDL_Options_Object_1_0 $b )
	{
		return strcmp( $a->property( 'priority' ), $b->property( 'priority' ) );
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
			$this->priority = (integer) $priority;
		} else {
			throw new InvalidArgumentException(
				__( 'The $priority parameter must be a number.', 'wp-sdl' )
			);
		}

		// maintain the chain
		return $this;
	}
}

class WP_SDL_Options_Config_1_0 extends WP_SDL_Options_Object_1_0
{
	/**
	 * The current group instance.
	 *
	 * @var WP_SDL_Options_Group_1_0
	 */
	private $group;

	/**
	 * Return group instance for given slug.
	 *
	 * @param string $slug
	 * @return WP_SDL_Options_Group_1_0
	 */
	final public function group( $slug )
	{
		// set current section
		$this->group = $this->get_child_auto( $slug, 'WP_SDL_Options_Group_1_0' );

		// return it!
		return $this->group;
	}
}

class WP_SDL_Options_Group_1_0 extends WP_SDL_Options_Object_1_0
{
	/**
	 * The current section instance.
	 *
	 * @var WP_SDL_Options_Section_1_0
	 */
	private $section;
	
	/**
	 * Return section instance for given slug.
	 *
	 * @param string $slug
	 * @return WP_SDL_Options_Section_1_0
	 */
	final public function section( $slug )
	{
		// set current section
		$this->section = $this->get_child_auto( $slug, 'WP_SDL_Options_Section_1_0' );

		// return it!
		return $this->section;
	}
}

class WP_SDL_Options_Section_1_0 extends WP_SDL_Options_Object_1_0
{
	/**
	 * The current field instance.
	 *
	 * @var WP_SDL_Options_Field_1_0
	 */
	private $field;

	/**
	 * Return field instance for given slug.
	 *
	 * @param string $slug
	 * @return WP_SDL_Options_Field_1_0
	 */
	final public function field( $slug )
	{
		// set current section
		$this->field = $this->get_child_auto( $slug, 'WP_SDL_Options_Field_1_0' );

		// return it!
		return $this->field;
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
