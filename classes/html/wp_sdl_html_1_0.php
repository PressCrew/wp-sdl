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
	 * Close an auto-closeable tag
	 *
	 * @param string $tag
	 * @return boolean
	 */
	protected function auto_close_tag( $tag )
	{
		// only close supported elements
		switch ( $tag ) {
			case 'optgroup':
			case 'select':
			case 'textarea':
				// render closing tag
				?></<?php echo $tag ?>><?php
				return true;
		}

		// not good
		$this->compat()->doing_it_wrong(
			__METHOD__,
			sprintf( __( 'The "%s" tag is not supported by auto close.' ), $tag ),
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
		// pop last tag and pass to auto closer
		return $this->auto_close_tag( array_pop( $this->auto_close_tags ) );
	}

	/**
	 * Turn auto close tags ON
	 *
	 * @return WP_SDL_Html_1_0
	 */
	public function auto_close_start()
	{
		// flip it on
		$this->auto_close = true;

		// maintain the chain
		return $this;
	}

	/**
	 * Turn auto close tags OFF and close final elements
	 *
	 * @return WP_SDL_Html_1_0
	 */
	public function auto_close_end()
	{
		// flip it off
		$this->auto_close = false;

		// complete any remaining
		while( count( $this->auto_close_tags ) ) {
			// at least one tag left, close it
			$this->auto_close_next();
		}

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
		?><label<?php echo $this->attributes( $atts ) ?>><?php echo esc_html( $title ) ?></label><?php

		// maintain the chain
		return $this;
	}

	/**
	 * Render an input element.
	 *
	 * @example html/input.php
	 * @param string $type The input type attribute. This will override the type attribute if set in the attributes array.
	 * @param string $name The input name attribute. This will override the name attribute if set in the attributes array.
	 * @param array $atts An array of additional attributes to render.
	 * @param string $current_value The value attribute will be automagically overridden if this is *not* null. This has nothing to do with the "checked" attribute!
	 * @return WP_SDL_Html_1_0
	 */
	public function input( $type, $name, $atts = array(), $current_value = null )
	{
		// override type attribute
		$atts['type'] = $type;
		// override name attribute
		$atts['name'] = $name;
		// maybe override value attribute
		if ( null !== $current_value ) {
			// override it
			$atts['value'] = (string) $current_value;
		}
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
			// set value in attributes
			$atts['value'] = $value;
			// set title in attributes
			$atts['title'] = $title;
			// determine checkiness
			$atts['checked'] = $this->value_is_current( $value, $current_value );
			// render the input
			$this->input( $type, $name, $atts );
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
		?><option<?php echo $this->attributes( $atts )?>><?php echo esc_html( $title ) ?></option><?php

		// maintain the chain
		return $this;
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

		// handle auto close of select and optgroups
		if ( true === $this->auto_close ) {
			// close the next element in stack
			$this->auto_close_next();
		}

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
		// auto close on?
		if ( true === $this->auto_close ) {
			// append it
			$this->auto_close_tags[] = 'optgroup';
		}

		// set label in atts
		$atts['label'] = $label;

		// open opt group
		?><optgroup<?php echo $this->attributes( $atts ) ?>><?php

		// maintain the chain
		return $this;
	}

	/**
	 * Manually close an optgroup element
	 *
	 * @return WP_SDL_Html_1_0
	 */
	public function option_group_close()
	{
		// close optgroup
		$this->auto_close_tag( 'optgroup' );

		// maintain the chain
		return $this;
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
		// handle auto close
		if ( true === $this->auto_close ) {
			// append to stack
			$this->auto_close_tags[] = 'select';
		}

		// set name in atts
		$atts['name'] = $name;

		// open select
		?><select<?php echo $this->attributes( $atts ) ?>><?php

		// maintain the chain
		return $this;
	}

	/**
	 * Manually close a select element
	 *
	 * @return WP_SDL_Html_1_0
	 */
	public function select_close()
	{
		// close select
		$this->auto_close_tag( 'select' );

		// maintain the chain
		return $this;
	}

	/**
	 * Render a textarea element with auto-close support.
	 *
	 * @param string $name The name attribute
	 * @param array $atts An array of additional attributes to render
	 * @param string $content The content of the element
	 * @return WP_SDL_Html_1_0
	 */
	public function textarea( $name, $atts = array(), $content = null )
	{
		// handle auto close
		if ( true === $this->auto_close ) {
			// append to stack
			$this->auto_close_tags[] = 'textarea';
		}
		
		// set name in atts
		$atts['name'] = $name;

		// open text area tag
		?><textarea<?php echo $this->attributes( $atts ) ?>><?php

		// maybe render content
		if ( null !== $content ) {
			$this->textarea_content( $content );
		} elseif ( true === $this->auto_close ) {
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
	 * @param type $content
	 * @return WP_SDL_Html_1_0
	 */
	public function textarea_content( $content )
	{
		// maybe render content
		if ( null !== $content ) {
			echo esc_textarea( $content );
		}

		// close tag if auto close is on
		if ( true === $this->auto_close ) {
			$this->auto_close_next();
		}

		// maintain the chain
		return $this;
	}

	/**
	 * Manually close a textarea element
	 *
	 * @return WP_SDL_Html_1_0
	 */
	public function textarea_close()
	{
		// close text area
		$this->auto_close_tag( 'textarea' );

		// maintain the chain
		return $this;
	}

}
