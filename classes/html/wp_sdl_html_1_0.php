<?php
/**
 * HTML Helper 1.0
 *
 * @link http://wp-sdl.org/
 * @author Marshall Sorenson
 * @link http://marshallsorenson.com/
 * @copyright Copyright (C) 2013 Marshall Sorenson
 * @license http://www.gnu.org/licenses/gpl.html GPLv2 or later
 * @package wp-sdl\helpers
 * @version 1.0
 */
class WP_SDL_Html_1_0 extends WP_SDL_Helper_1_0
{
	/**
	 * Class version
	 *
	 * @var string
	 */
	protected static $VERSION = '1.0';

	/**
	 * Auto close brackets toggle
	 *
	 * @var boolean
	 */
	private $auto_brackets = true;

	/**
	 * Smart close elements toggle.
	 *
	 * @var boolean
	 */
	private $smart_close = true;

	/**
	 * Smart close tags stack.
	 *
	 * @var array
	 */
	private $smart_close_tags = array();

	/**
	 * Auto close tags toggle
	 *
	 * @var boolean
	 */
	private $auto_close = false;

	/**
	 * Auto close tags stack
	 *
	 * @var array
	 */
	private $auto_close_tags = array();

	/**
	 * Master list of HTML5 elements.
	 *
	 * IMPORTANT: Only elements that have an empty content model are listed for now.
	 *
	 * @var array
	 */
	private $elements = array(
		'area' => false,
		'base' => false,
		'br' => false,
		'embed' => false,
		'hr' => false,
		'image' => false,
		'input' => false,
		'keygen' => false,
		'link' => false,
		'meta' => false,
		'menuitem' => false,
		'param' => false,
		'source' => false,
		'track' => false,
		'wbr' => false
	);

	/**
	 * Format attributes for an HTML element.
	 *
	 * This method returns a string of HTML element attributes.
	 *
	 * Names are sanity checked, and values are automagically escaped with
	 * {@link http://codex.wordpress.org/Function_Reference/esc_attr esc_attr()}.
	 *
	 * A leading space is **always** prepended to the start of the attribute string.
	 *
	 * @example html/attributes.php
	 * @param array $atts An array of attributes to render. Keys are attribute names, values are unescaped attribute values.
	 * @return string
	 */
	public function attributes( $atts )
	{
		// is atts not an array?
		if ( !is_array( $atts ) ) {
			// not good
			$this->compat()->doing_it_wrong(
				__METHOD__,
				__( 'The $atts parameter must be an array.', 'wp-sdl' ),
				self::$VERSION
			);
		}

		// normalize attributes
		$atts_normal = $this->attributes_bool( $atts );

		// string of html attributes to return
		$atts_string = '';

		// build up attributes string
		foreach ( $atts_normal as $att_name => $att_value ) {
			// sanity check attribute name
			if ( preg_match( '#^[a-z][a-z0-9-]*$#', $att_name ) ) {
				// format it
				$atts_string .= sprintf(
					' %s="%s"',
					$att_name,
					esc_attr( $att_value )
				);
			} else {
				// attribute name is not valid
				$this->compat()->doing_it_wrong(
					__METHOD__,
					sprintf( __( 'The attribute "%s" is not valid.', 'wp-sdl' ), $att_name ),
					self::$VERSION
				);
			}
		}

		// return the attribute string
		return $atts_string;
	}

	/**
	 * Auto convert boolean attributes to contain the correct value
	 *
	 * @param array $atts
	 * @return array
	 */
	public function attributes_bool( $atts )
	{
		// define boolean attributes
		$atts_bool = array(
			'checked',
			'disabled',
			'ismap',
			'multiple',
			'noresize',
			'readonly',
			'selected'
		);

		// loop all boolean attributes
		foreach( $atts_bool as $att_bool ) {
			// is in atts array?
			if ( array_key_exists( $att_bool, $atts ) ) {
				// is it empty?
				if ( empty( $atts[ $att_bool ] ) ) {
					// yep, completely remove it
					unset( $atts[ $att_bool ] );
				} else {
					// non-empty, force it to string
					$atts[ $att_bool ] = $att_bool;
				}
			}
		}

		// return possibly modified array
		return $atts;
	}

	/**
	 * Compare a value to a possible current value and return true if they match.
	 *
	 * Current value can be an array in which case true is returned if the value exists
	 * in the array.
	 *
	 * @param mixed $value The value to check
	 * @param mixed $current_value The value to use for comparison
	 * @return boolean
	 */
	public function value_is_current( $value, $current_value )
	{
		// current value can't be null
		if ( null !== $current_value ) {
			// is current value an array?
			if ( is_array( $current_value ) ) {
				// yes, check if value is in there
				return in_array( $value, $current_value );
			} else {
				// no, check if they match
				return ( $value == $current_value );
			}
		}

		// fall through to no match
		return false;
	}

	/**
	 * Toggle smart close on/off.
	 *
	 * Smart close is ON by default.
	 *
	 * @param boolean $toggle Pass true/false to toggle on/off and reset the open tags stack.
	 * @return WP_SDL_Html_1_0
	 */
	public function smart_close( $toggle )
	{
		// is toggle boolean?
		if ( is_bool( $toggle ) ) {
			// yep, set it
			$this->smart_close = $toggle;
		} else {
			// not good
			$this->compat()->doing_it_wrong(
				__METHOD__,
				__( 'Argument must be true/false (boolean).', 'wp-sdl' ),
				self::$VERSION
			);
		}

		// reset stack
		$this->smart_close_tags = array();

		// maintain the chain
		return $this;
	}

	/**
	 * Toggle auto brackets on/off.
	 *
	 * Auto brackets are ON by default.
	 * 
	 * @param boolean $toggle Pass true/false to toggle on/off.
	 * @return WP_SDL_Html_1_0
	 */
	public function auto_brackets( $toggle )
	{
		// is toggle boolean?
		if ( is_bool( $toggle ) ) {
			// yep, set it
			$this->auto_brackets = $toggle;
		} else {
			// not good
			$this->compat()->doing_it_wrong(
				__METHOD__,
				__( 'Argument must be true/false (boolean).', 'wp-sdl' ),
				self::$VERSION
			);
		}

		// maintain the chain
		return $this;
	}

	/**
	 * Maybe append brackets to name value if autobrackets is enabled.
	 *
	 * @param string $type The field type.
	 * @param string $name The name attribute value.
	 * @param boolean $toggle Optional parameter for easy context toggling.
	 * @return string
	 */
	protected function auto_brackets_name( $type, $name, $toggle = true )
	{
		// auto brackets on?
		if ( true === $this->auto_brackets && true === $toggle ) {
			// two types support brackets
			switch( $type ) {
				case 'checkbox':
				case 'select':
					$name .= '[]';
			}
		}

		// return name
		return $name;
	}

	/**
	 * Open an element.
	 *
	 * @param string $element A valid element name.
	 * @param array $atts An array of attributes.
	 * @return WP_SDL_Html_1_0
	 */
	public function open( $element, $atts = array() )
	{
		// sanity check element names in debug mode
		if (
			true == WP_DEBUG &&
			1 !== preg_match( '/^[a-z]+$/', $element )
		) {
			// generate error
			$this->compat()->doing_it_wrong(
				__METHOD__,
				sprintf( __( 'The "%s" element is not valid.' ), $element ),
				self::$VERSION
			);
			// not good
			return false;
		}

		// smart close on?
		if ( true === $this->smart_close ) {
			// append to smart close stack
			$this->smart_close_tags[] = $element;
		}

		// render opening element
		?><<?php echo $element, $this->attributes( $atts ) ?>><?php

		// maintain the chain
		return $this;
	}

	/**
	 * Close next tag in stack.
	 *
	 * @param string|integer $element A valid element name or number of elements to close.
	 * @return WP_SDL_Html_1_0
	 */
	public function close( $element = null )
	{
		// is element empty?
		if ( empty( $element ) ) {
			// smart close enabled?
			if ( true === $this->smart_close ) {
				// yep, get next element from stack
				$element = array_pop( $this->smart_close_tags );
				// is it a one sided element?
				if ( true === isset( $this->elements[ $element ] ) ) {
					// yep, go to next element
					return $this->close();
				}
			} else {
				// generate error
				$this->compat()->doing_it_wrong(
					__METHOD__,
					__( 'The $tag parameter cannot be empty unless auto close is enabled.' ),
					self::$VERSION
				);
				// not good
				return false;
			}
		}

		// have element to close?
		if ( $element ) {
			// yep, render closing tag
			?></<?php echo $element ?>><?php
		}

		// maintain the chain
		return $this;
	}

	/**
	 * Close all remaining smart close tags.
	 */
	public function close_all()
	{
		// close all open tags
		while( count( $this->smart_close_tags ) ) {
			// at least one tag left, close it
			$this->close();
		}
	}

	/**
	 * Open an auto-closeable tag
	 *
	 * @return boolean
	 */
	protected function auto_close_tag( $element, $atts )
	{
		// only auto open supported elements
		switch ( $element ) {
			case 'optgroup':
			case 'select':
			case 'textarea':
				// is auto close on?
				if ( true === $this->auto_close ) {
					// append to auto close stack
					$this->auto_close_tags[] = $element;
				}
				// now open the tag normally
				return $this->open( $element, $atts );
		}

		// not good
		$this->compat()->doing_it_wrong(
			__METHOD__,
			sprintf( __( 'The "%s" element is not supported by auto close.' ), $element ),
			self::$VERSION
		);

		return false;
	}

	/**
	 * Close next auto-closeable tag in the stack.
	 *
	 * @return boolean
	 */
	protected function auto_close_next()
	{
		// auto close on?
		if ( true === $this->auto_close ) {
			// is smart close on?
			if ( true === $this->smart_close ) {
				// yep, tidy up smart close stack
				array_pop( $this->smart_close_tags );
			}
			// pop last tag and pass to close
			return $this->close( array_pop( $this->auto_close_tags ) );
		}
	}

	/**
	 * Turn auto close tags ON.
	 *
	 * @return WP_SDL_Html_1_0
	 */
	public function auto_close_start()
	{
		// flip it on
		$this->auto_close = true;

		// reset tag stack
		$this->auto_close_tags = array();

		// maintain the chain
		return $this;
	}

	/**
	 * Turn auto close tags OFF and close final elements.
	 *
	 * @return WP_SDL_Html_1_0
	 */
	public function auto_close_end()
	{
		// complete any remaining
		while( count( $this->auto_close_tags ) ) {
			// at least one tag left, close it
			$this->auto_close_next();
		}
		
		// flip it off
		$this->auto_close = false;

		// maintain the chain
		return $this;
	}

	/**
	 * The content is automatically escaped with
	 * {@link http://codex.wordpress.org/Function_Reference/esc_html esc_html()}.
	 *
	 * @param string $string
	 * @return WP_SDL_Html_1_0
	 */
	public function content( $string )
	{
		echo esc_html( $string );

		// maintain the chain
		return $this;
	}

	/**
	 * Render a label tag.
	 *
	 * The title content is automatically escaped with
	 * {@link http://codex.wordpress.org/Function_Reference/esc_html esc_html()}.
	 *
	 * @param string $for Input name which this label is for.
	 * @param string $title The label title (tag contents).
	 * @param array $atts Array of additional attributes to render in the tag.
	 * @return WP_SDL_Html_1_0
	 */
	public function label( $for, $title, $atts = array() )
	{
		// override "for" in atts
		$atts['for'] = $for;
		// override "title" in atts
		$atts['title'] = $title;
		// render label tag
		return $this->open( 'label', $atts )->content( $title )->close();
	}

	/**
	 * Render a field.
	 *
	 * This is a convenience method which comes in handy when rendering
	 * fields inside a loop, or other cases where the type relies on context.
	 *
	 * @param string $type The field's type
	 * @param string $name The field's name
	 * @param mixed $value The field's value
	 * @param array $atts The field's attributes
	 * @param mixed $current_value The field's value/content will be automagically overridden if this is *not* null.
	 */
	public function field( $type, $name, $value = null, $atts = array(), $current_value = null )
	{
		// render the correct field tag
		switch( $type ) {
			case 'select':
				$this->select( $name, $atts );
				$this->option_list( $value, array(), $current_value );
				$this->close();
				break;
			case 'checkbox':
			case 'radio':
				$this->input_group( $type, $name, $value, $atts, $current_value );
				break;
			case 'textarea':
				$this->textarea( $name, $atts, $value, $current_value );
				$this->close();
				break;
			default:
				$this->input( $type, $name, $value, $atts, $current_value );
		}
	}

	/**
	 * Render an input element.
	 *
	 * @example html/input.php
	 * @param string $type The input type attribute. This will override the type attribute if set in the attributes array.
	 * @param string $name The input name attribute. This will override the name attribute if set in the attributes array.
	 * @param mixed $value The input value attribute. This will override the value attribute if set in the attributes array.
	 * @param array $atts An array of additional attributes to render.
	 * @param string $current_value The value attribute will be automagically overridden if this is *not* null. This has nothing to do with the "checked" attribute!
	 * @return WP_SDL_Html_1_0
	 */
	public function input( $type, $name, $value = null, $atts = array(), $current_value = null )
	{
		// handle auto brackets
		$name = $this->auto_brackets_name( $type, $name );
		
		// override type attribute
		$atts['type'] = $type;
		// override name attribute
		$atts['name'] = $name;
		// determine value attribute
		$atts['value'] = ( null === $current_value ) ? $value : $current_value;
		
		// render the input
		?><input<?php echo $this->attributes( $atts ) ?>/><?php

		// maintain the chain
		return $this;
	}

	/**
	 * Render a group of input elements.
	 *
	 * Each input's title content is automatically escaped with
	 * {@link http://codex.wordpress.org/Function_Reference/esc_html esc_html()}.
	 *
	 * @param string $type The input type attribute (radio|checkbox). This will override the type attribute if set in the attributes array.
	 * @param string $name The input name attribute. This will override the name attribute if set in the attributes array.
	 * @param array $values An array of values to render inputs for.
	 * @param array $atts An array of additional attributes to render.
	 * @param mixed $current_value The value used to determine the checked state.
	 * @return WP_SDL_Html_1_0
	 */
	public function input_group( $type, $name, $values, $atts = array(), $current_value = null )
	{
		// loop all values
		foreach ( $values as $value => $title ) {
			// set title in attributes
			$atts['title'] = $title;
			// determine checkiness
			$atts['checked'] = $this->value_is_current( $value, $current_value );
			// render the input
			$this->input( $type, $name, $value, $atts );
			// render the title
			echo esc_html( ' ' . $title );
		}

		// maintain the chain
		return $this;
	}

	/**
	 * Render one option element.
	 *
	 * Each option's title content is automatically escaped with
	 * {@link http://codex.wordpress.org/Function_Reference/esc_html esc_html()}.
	 *
	 * @param scalar $value The option's value attribute
	 * @param string $title The option's title (content)
	 * @param array $atts An array of additional attributes to render.
	 * @return WP_SDL_Html_1_0
	 */
	public function option( $value, $title, $atts = array() )
	{
		// set value in atts
		$atts['value'] = $value;
		// set title in atts
		$atts['title'] = $title;
		// render option tag
		return $this->open( 'option', $atts )->content( $title )->close();
	}

	/**
	 * Render a list of option elements.
	 *
	 * @param array $values Array of option values
	 * @param array $atts An array of additional attributes to render.
	 * @param mixed $current_value The value, or array of values, which should have the selected attribute.
	 * @return WP_SDL_Html_1_0
	 */
	public function option_list( $values, $atts = array(), $current_value = null )
	{
		// loop all values
		foreach ( $values as $value => $title ) {
			// determine selectiness
			$atts['selected'] = $this->value_is_current( $value, $current_value );
			// render it
			$this->option( $value, $title, $atts );
		}

		// auto close select and optgroups
		$this->auto_close_next();

		// maintain the chain
		return $this;
	}

	/**
	 * Render an optgroup element with auto-close support.
	 *
	 * @param string $label The content of the label attribute
	 * @param array $atts An array of additional attributes to render.
	 * @return WP_SDL_Html_1_0
	 */
	public function option_group( $label, $atts = array() )
	{
		// set label in atts
		$atts['label'] = $label;

		// open opt group
		return $this->auto_close_tag( 'optgroup', $atts );
	}

	/**
	 * Render an select element with auto-close support.
	 *
	 * @param string $name The name attribute
	 * @param array $atts An array of additional attributes to render.
	 * @return WP_SDL_Html_1_0
	 */
	public function select( $name, $atts = array() )
	{
		// handle auto brackets
		$name = $this->auto_brackets_name( 'select', $name, ( $atts['multiple'] ) );
		
		// set name in atts
		$atts['name'] = $name;

		// open select
		return $this->auto_close_tag( 'select', $atts );
	}

	/**
	 * Render a textarea element with auto-close support.
	 *
	 * @param string $name The name attribute
	 * @param array $atts An array of additional attributes to render
	 * @param string $content The content of the element
	 * @param string $current_content The content will be automagically overridden if this is *not* null.
	 * @return WP_SDL_Html_1_0
	 */
	public function textarea( $name, $atts = array(), $content = null, $current_content = null )
	{
		// set name in atts
		$atts['name'] = $name;

		// open text area tag
		$this->auto_close_tag( 'textarea', $atts );

		// maybe render content
		if ( null !== $content ) {
			$this->textarea_content( $content, $current_content );
		} else {
			$this->auto_close_next();
		}

		// maintain the chain
		return $this;
	}

	/**
	 * Render textarea content.
	 *
	 * Content is automatically escaped with
	 * {@link http://codex.wordpress.org/Function_Reference/esc_textarea esc_textarea()}.
	 *
	 * @param type $content The text area content.
	 * @param string $current_content The content will be automagically overridden if this is *not* null.
	 * @return WP_SDL_Html_1_0
	 */
	public function textarea_content( $content, $current_content = null )
	{
		// handle content override
		if ( null !== $current_content ) {
			// override it
			$content = $current_content;
		}

		// maybe render content
		if ( null !== $content ) {
			echo esc_textarea( $content );
		}

		// call auto closer
		$this->auto_close_next();

		// maintain the chain
		return $this;
	}

}
