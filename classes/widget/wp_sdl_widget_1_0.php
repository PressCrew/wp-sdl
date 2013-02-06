<?php
/**
 * Widget Helper 1.0
 *
 * @link http://wp-sdl.org/
 * @author Marshall Sorenson
 * @link http://marshallsorenson.com/
 * @copyright Copyright (C) 2013 Marshall Sorenson
 * @license http://www.gnu.org/licenses/gpl.html GPLv2 or later
 * @package wp-sdl\helpers
 * @version 1.0
 */
class WP_SDL_Widget_1_0 extends WP_SDL_Helper_1_0
{
	/**
	 * Field description config key.
	 */
	const KEY_FIELD_DESC = 'desc';

	/**
	 * Field type config key.
	 */
	const KEY_FIELD_TYPE = 'type';

	/**
	 * Field value config key.
	 */
	const KEY_FIELD_VALUE = 'value';

	/**
	 * Field attributes config key.
	 */
	const KEY_FIELD_ATTS = 'attributes';

	/**
	 * Field filters config key.
	 */
	const KEY_FIELD_FILTERS = 'filters';

	/**
	 * Before field content config key.
	 */
	const KEY_FIELD_BEFORE_FIELD = 'before_field';

	/**
	 * After field content config key.
	 */
	const KEY_FIELD_AFTER_FIELD = 'after_field';

	/**
	 * Class version
	 *
	 * @var string
	 */
	protected static $VERSION = '1.0';

	/**
	 * Widget fields config.
	 *
	 * This is a multi-dimensional array. The first level keys
	 * are the class names of instances of WP_Widget.
	 *
	 * @var array
	 */
	private $fields = array();

	/**
	 * Set the config for a widget's field.
	 *
	 * @param WP_Widget $widget Widget instance to set field config for.
	 * @param string $name The field name.
	 * @param array $config The field config.
	 */
	public function set_field( WP_Widget $widget, $name, $config )
	{
		// get widget class name
		$class = get_class( $widget );

		// parse and set config of field name for widget class
		$this->fields[ $class ][ $name ] =
			wp_parse_args(
				$config,
				array(
					self::KEY_FIELD_DESC => '',
					self::KEY_FIELD_TYPE => 'text',
					self::KEY_FIELD_VALUE => null,
					self::KEY_FIELD_ATTS => array( 'class' => 'widefat' ),
					self::KEY_FIELD_FILTERS => array( 'strip_tags' ),
					self::KEY_FIELD_BEFORE_FIELD => '<p>',
					self::KEY_FIELD_AFTER_FIELD => '</p>'
				)
			);
	}

	/**
	 * Get the config for a widget's field.
	 *
	 * @param WP_Widget $widget Widget instance to get field config for.
	 * @param string $name The field name.
	 * @return array|null
	 */
	public function get_field( WP_Widget $widget, $name )
	{
		// get widget class name
		$class = get_class( $widget );

		// is field configured?
		if ( isset( $this->fields[ $class ][ $name ] ) ) {
			// yep, return config array
			return $this->fields[ $class ][ $name ];
		}

		// return null by default
		return null;
	}

	/**
	 * Return array of configured field names.
	 *
	 * @param WP_Widget $widget Widget instance to get field names for.
	 * @return array
	 */
	public function get_field_names( WP_Widget $widget )
	{
		// get widget class name
		$class = get_class( $widget );

		// return keys for class
		return array_keys( $this->fields[ $class ] );
	}

	/**
	 * Sanitize fields for a widget.
	 *
	 * @param WP_Widget $widget Widget instance to filter fields for.
	 * @param array $new_instance Dirty values from the form.
	 * @param array $old_instance Previously saved values from database.
	 * @return array Sanitized values.
	 */
	public function filter_fields( WP_Widget $widget, $new_instance, $old_instance )
	{
		// instance to return
		$instance = array();

		// loop all field names
		foreach ( $this->get_field_names( $widget ) as $field_name ) {

			// grab config
			$config = $this->get_field( $widget, $field_name );

			// filters are null by default
			$filters = null;

			// have an array of filters?
			if (
				isset( $config[ self::KEY_FIELD_FILTERS ] ) &&
				is_array( $config[ self::KEY_FIELD_FILTERS ] )
			) {
				// yep, set filters
				$filters = $config[ self::KEY_FIELD_FILTERS ];
			} else {
				// nope, abort this loop
				continue;
			}

			// pass value(s) through all filters
			foreach( $filters as $filter ) {
				// is new value an array?
				if ( is_array( $new_instance[ $field_name ] ) ) {
					// yep, loop all values
					foreach ( $new_instance[ $field_name ] as $key => $value ) {
						// call filter
						$instance[ $field_name ][ $key ] = call_user_func( $filter, $value );
					}
				} else {
					// nope, call filter now
					$instance[ $field_name ] = call_user_func( $filter, $new_instance[ $field_name ] );
				}
			}
		}

		// return cleaned up instance
		return $instance;
	}

	/**
	 * Render all fields for a widget.
	 *
	 * @param WP_Widget $widget Widget instance to render fields for.
	 * @param array $instance Saved values from database.
	 */
	public function render_fields( WP_Widget $widget, $instance )
	{
		// loop all configured fields
		foreach( $this->get_field_names( $widget ) as $field_name ) {
			// run single field renderer
			$this->render_field(
				$widget,
				$field_name,
				isset( $instance[ $field_name ] ) ? $instance[ $field_name ] : null
			);
		}
	}

	/**
	 * Render one field for a widget.
	 *
	 * @param WP_Widget $widget Widget instance to render field for.
	 * @param string $name The field name.
	 * @param mixed $current_value The current value of the field.
	 */
	public function render_field( WP_Widget $widget, $name, $current_value = null )
	{
		// init config vars
		$desc = $type = $value = $atts = $filters = null;
		$before_field = $after_field = null;

		// extract config vars
		extract( $this->get_field( $widget, $name ), EXTR_IF_EXISTS );

		/* @var $html_helper WP_SDL_Html_1_0 */
		$html_helper = $this->compat()->html();

		// append id to atts
		$atts['id'] = $widget->get_field_id( $name );

		// before field content
		echo $before_field;

		// render label
		$html_helper->label( $atts['id'], $desc );

		// render the field
		$html_helper->field( $type, $widget->get_field_name( $name ), $value, $atts, $current_value );

		// after field content
		echo $after_field;
	}

	/**
	 * Render a widget header.
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function render_header( $args, $instance )
	{
		// init vars to extract
		$before_widget = null;
		$before_title = null;
		$after_title = null;

		// extract the args
		extract( $args, EXTR_IF_EXISTS );

		// apply widget title filter
		$title = apply_filters( 'widget_title', $instance['title'] );

		// spit out before widget text
		echo $before_widget;

		// spit out title
		if ( !empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}
	}

	/**
	 * Render a widget footer.
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function render_footer( $args, $instance )
	{
		// init vars to extract
		$after_widget = null;

		// extract the args
		extract( $args, EXTR_IF_EXISTS );

		// spit out after widget text
		echo $after_widget;
	}
}
