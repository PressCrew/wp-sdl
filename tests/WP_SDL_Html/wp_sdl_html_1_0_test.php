<?php

class WP_SDL_Html_1_0_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @var WP_SDL_Html_1_0
	 */
	protected $html;

	public function setUp()
	{
		$this->html = WP_SDL::support( '1.0' )->html();
		$this->html->smart_close( true );
	}

	public function tearDown()
	{
		unset( $this->html );
	}

	public function testInstance()
	{
		$this->assertInstanceOf( 'WP_SDL_Html_1_0', $this->html );
	}

	public function testVersionConstant()
	{
		$this->assertAttributeEquals( '1.0', 'VERSION', 'WP_SDL_Html_1_0' );
	}

	public function testAutoBrackets()
	{
		// on by default
		$this->assertAttributeEquals( true, 'auto_brackets', $this->html );

		// toggle off
		$this->html->auto_brackets( false );
		$this->assertAttributeEquals( false, 'auto_brackets', $this->html );

		// toggle on
		$this->html->auto_brackets( true );
		$this->assertAttributeEquals( true, 'auto_brackets', $this->html );
	}

	public function testAutoBracketsBadArg()
	{
		$this->assertFalse(
			@$this->html->auto_brackets('on')
		);
	}

	public function testAutoClose()
	{
		// init state
		$this->assertAttributeEquals( false, 'auto_close', $this->html );
		$this->assertAttributeEquals( array(), 'auto_close_tags', $this->html );

		// toggle on and check return val
		$this->assertInstanceOf( 'WP_SDL_HTML_1_0', $this->html->auto_close_start() );

		// should be on now
		$this->assertAttributeEquals( true, 'auto_close', $this->html );

		// toggle off and check return val
		$this->assertInstanceOf( 'WP_SDL_HTML_1_0', $this->html->auto_close_end() );

		// should be off now
		$this->assertAttributeEquals( false, 'auto_close', $this->html );
	}

	public function testFormatAttributes()
	{
		$this->assertEquals(
			' id="foo" class="bar" title="You are a pretty &quot;cool dude&quot;"',
			$this->html->attributes(
				array(
					'id' => 'foo',
					'class' => 'bar',
					'title' => 'You are a pretty "cool dude"',
				)
			)
		);
	}

	public function testFormatAttributesBadParam()
	{
		$this->assertFalse(
			@$this->html->attributes( 'A string' )
		);
	}

	public function testFormatAttributesBadName()
	{
		$this->assertFalse(
			@$this->html->attributes(
				array(
					'a"bad=key' => 'A fake value'
				)
			)
		);
	}

	public function testFormatAttributesBoolean()
	{
		$attributes =
			$this->html->attributes_bool(
				array(
					'checked' => true,
					'disabled' => false,
					'ismap' => 'true',
					'multiple' => 'multiple',
					'noresize' => 'blah',
					'readonly' => null,
					'selected' => ''
				)
			);

		$this->assertEquals(
			array(
				'checked' => 'checked',
				'ismap' => 'ismap',
				'multiple' => 'multiple',
				'noresize' => 'noresize'
			),
			$attributes
		);
	}

	public function testOpen()
	{
		$this->expectOutputString(
			'<div class="foo">'
		);

		$this->html
			->smart_close( false )
			->open(
				'div',
				array(
					'class' => 'foo'
				)
			);
	}

	public function testClose()
	{
		$this->expectOutputString(
			'</div>'
		);

		$this->html->close( 'div' );
	}

	public function testSmartClose()
	{
		$this->expectOutputString(
			'<div class="foo"></div>'
		);

		$this->html->open(
			'div',
			array(
				'class' => 'foo'
			)
		);

		$this->html->close();
	}

	public function testSmartCloseNested()
	{
		$this->expectOutputString(
			'<div><span><i>Hello</i> <b>You!</b></span></div>'
		);

		$this->html
			->open( 'div' )
				->open( 'span' )
					->open( 'i' )
						->content( 'Hello' )
					->close()
					->content( ' ' )
					->open( 'b' )
						->content( 'You!' )
			->close_all();
	}

	public function testLabel()
	{
		$this->expectOutputString(
			'<label class="foo" for="abc" title="One two three">One two three</label>'
		);

		$this->html->label(
			'abc',
			'One two three',
			array(
				'class' => 'foo'
			)
		);
	}

	public function testInput()
	{
		$this->expectOutputString(
			'<input id="foo" class="bar" type="text" name="firstname" value="Bob"/>'
		);

		$this->html->input(
			'text',
			'firstname',
			'Bob',
			array(
				'id' => 'foo',
				'class' => 'bar'
			)
		);
	}

	public function testInputViaField()
	{
		$this->expectOutputString(
			'<input id="foo" class="bar" type="text" name="firstname" value="Joe"/>'
		);

		$this->html->field(
			'text',
			'firstname',
			'Bob',
			array(
				'id' => 'foo',
				'class' => 'bar'
			),
			'Joe'
		);
	}

	public function testInputOverrideValue()
	{
		$this->expectOutputString(
			'<input type="text" name="firstname" value="Joe"/>'
		);

		$this->html->input( 'text', 'firstname', 'Bob', array(), 'Joe' );
	}

	public function testValueIsCurrent()
	{
		$this->assertTrue( $this->html->value_is_current( 'foo', 'foo' ) );
		$this->assertTrue( $this->html->value_is_current( 'foo', array( 'foo', 'bar' ) ) );
	}

	public function testValueIsNotCurrent()
	{
		$this->assertFalse( $this->html->value_is_current( null, null ) );
		$this->assertFalse( $this->html->value_is_current( 'foo', 'bar' ) );
		$this->assertFalse( $this->html->value_is_current( 'foo', array( 'bar', 'baz' ) ) );
	}

	public function testRadioGroup()
	{
		$this->expectOutputString(
			'<input class="foo" tabindex="3" title="Red" type="radio" name="color" value="red"/> Red' .
			'<input class="foo" tabindex="3" title="Yellow" checked="checked" type="radio" name="color" value="yellow"/> Yellow' .
			'<input class="foo" tabindex="3" title="Blue" type="radio" name="color" value="blue"/> Blue'
		);

		$this->html->input_group(
			'radio',
			'color',
			array(
				'red' => 'Red',
				'yellow' => 'Yellow',
				'blue' => 'Blue',
			),
			array(
				'class' => 'foo',
				'tabindex' => 3
			),
			'yellow'
		);
	}

	public function testRadioGroupViaField()
	{
		$this->expectOutputString(
			'<input class="foo" tabindex="3" title="Red" type="radio" name="color" value="red"/> Red' .
			'<input class="foo" tabindex="3" title="Yellow" checked="checked" type="radio" name="color" value="yellow"/> Yellow' .
			'<input class="foo" tabindex="3" title="Blue" type="radio" name="color" value="blue"/> Blue'
		);

		$this->html->field(
			'radio',
			'color',
			array(
				'red' => 'Red',
				'yellow' => 'Yellow',
				'blue' => 'Blue',
			),
			array(
				'class' => 'foo',
				'tabindex' => 3
			),
			'yellow'
		);
	}

	public function testCheckboxGroup()
	{
		$this->expectOutputString(
			'<input class="foo" tabindex="3" title="Red" type="checkbox" name="colors[]" value="red"/> Red' .
			'<input class="foo" tabindex="3" title="Yellow" checked="checked" type="checkbox" name="colors[]" value="yellow"/> Yellow' .
			'<input class="foo" tabindex="3" title="Blue" checked="checked" type="checkbox" name="colors[]" value="blue"/> Blue'
		);

		$this->html->input_group(
			'checkbox',
			'colors',
			array(
				'red' => 'Red',
				'yellow' => 'Yellow',
				'blue' => 'Blue',
			),
			array(
				'class' => 'foo',
				'tabindex' => 3
			),
			array(
				'yellow',
				'blue'
			)
		);
	}

	public function testOption()
	{
		$this->expectOutputString(
			'<option class="foo" value="red" title="Red">Red</option>'
		);

		$this->html->option(
			'red',
			'Red',
			array(
				'class' => 'foo'
			)
		);
	}

	public function testOptionList()
	{
		$this->expectOutputString(
			'<option class="foo" value="red" title="Red">Red</option>' .
			'<option class="foo" selected="selected" value="yellow" title="Yellow">Yellow</option>' .
			'<option class="foo" value="blue" title="Blue">Blue</option>'
		);

		$this->html->option_list(
			array(
				'red' => 'Red',
				'yellow' => 'Yellow',
				'blue' => 'Blue',
			),
			array(
				'class' => 'foo'
			),
			'yellow'
		);
	}

	public function testOptionGroup()
	{
		$this->expectOutputString(
			'<optgroup class="foo" label="A nice label">'
		);

		$this->html
			->option_group(
				'A nice label',
				array(
					'class' => 'foo'
				)
			);
	}

	public function testOptionGroupWithClose()
	{
		$this->expectOutputString(
			'<optgroup class="foo" label="A nice label"></optgroup>'
		);

		$this->html
			->option_group(
				'A nice label',
				array(
					'class' => 'foo'
				)
			)
			->close();
	}

	public function testOptionGroupNested()
	{
		$this->expectOutputString(
			'<optgroup label="Primary">' .
			'<option class="foo" value="red" title="Red">Red</option>' .
			'<option class="foo" selected="selected" value="yellow" title="Yellow">Yellow</option>' .
			'<option class="foo" value="blue" title="Blue">Blue</option>' .
			'</optgroup>' .
			'<optgroup label="Secondary">' .
			'<option class="bar" value="orange" title="Orange">Orange</option>' .
			'<option class="bar" selected="selected" value="green" title="Green">Green</option>' .
			'<option class="bar" selected="selected" value="purple" title="Purple">Purple</option>' .
			'</optgroup>'
		);

		$this->html
			->auto_close_start()
			->option_group( 'Primary' )
			->option_list(
				array(
					'red' => 'Red',
					'yellow' => 'Yellow',
					'blue' => 'Blue',
				),
				array(
					'class' => 'foo'
				),
				'yellow'
			)
			->option_group( 'Secondary' )
			->option_list(
				array(
					'orange' => 'Orange',
					'green' => 'Green',
					'purple' => 'Purple',
				),
				array(
					'class' => 'bar'
				),
				array(
					'green',
					'purple'
				)
			)
			->auto_close_end();
	}

	public function testSelect()
	{
		$this->expectOutputString(
			'<select class="foo" title="A nice title" name="dropdown">'
		);

		$this->html
			->select(
				'dropdown',
				array(
					'class' => 'foo',
					'title' => 'A nice title'
				)
			);
	}

	public function testSelectWithClose()
	{
		$this->expectOutputString(
			'<select class="foo" title="A nice title" name="dropdown"></select>'
		);

		$this->html
			->auto_close_start()
			->select(
				'dropdown',
				array(
					'class' => 'foo',
					'title' => 'A nice title'
				)
			)
			->auto_close_end();
	}

	public function testSelectWithOptionList()
	{
		$this->expectOutputString(
			'<select class="foo" title="A nice title" name="dropdown">' .
			'<option class="foo opt" value="red" title="Red">Red</option>' .
			'<option class="foo opt" selected="selected" value="yellow" title="Yellow">Yellow</option>' .
			'<option class="foo opt" value="blue" title="Blue">Blue</option>' .
			'</select>'
		);

		$this->html
			->auto_close_start()
			->select(
				'dropdown',
				array(
					'class' => 'foo',
					'title' => 'A nice title'
				)
			)
			->option_list(
				array(
					'red' => 'Red',
					'yellow' => 'Yellow',
					'blue' => 'Blue',
				),
				array(
					'class' => 'foo opt'
				),
				'yellow'
			)
			->auto_close_end();
	}

	public function testSelectWithOptionListViaField()
	{
		$this->expectOutputString(
			'<select class="foo" title="A nice title" name="dropdown">' .
			'<option value="red" title="Red">Red</option>' .
			'<option selected="selected" value="yellow" title="Yellow">Yellow</option>' .
			'<option value="blue" title="Blue">Blue</option>' .
			'</select>'
		);

		$this->html->field(
			'select',
			'dropdown',
			array(
				'red' => 'Red',
				'yellow' => 'Yellow',
				'blue' => 'Blue',
			),
			array(
				'class' => 'foo',
				'title' => 'A nice title'
			),
			'yellow'
		);
	}

	public function testSelectWithOptionGroups()
	{
		$this->expectOutputString(
			'<select multiple="multiple" class="foo" title="A nice title" name="dropdown[]">' .
			'<optgroup label="Primary">' .
			'<option class="foo" value="red" title="Red">Red</option>' .
			'<option class="foo" selected="selected" value="yellow" title="Yellow">Yellow</option>' .
			'<option class="foo" value="blue" title="Blue">Blue</option>' .
			'</optgroup>' .
			'<optgroup label="Secondary">' .
			'<option class="bar" value="orange" title="Orange">Orange</option>' .
			'<option class="bar" selected="selected" value="green" title="Green">Green</option>' .
			'<option class="bar" selected="selected" value="purple" title="Purple">Purple</option>' .
			'</optgroup>' .
			'</select>'
		);

		$this->html
			->auto_close_start()
			->select(
				'dropdown',
				array(
					'multiple' => true,
					'class' => 'foo',
					'title' => 'A nice title'
				)
			)
			->option_group( 'Primary' )
			->option_list(
				array(
					'red' => 'Red',
					'yellow' => 'Yellow',
					'blue' => 'Blue',
				),
				array(
					'class' => 'foo'
				),
				'yellow'
			)
			->option_group( 'Secondary' )
			->option_list(
				array(
					'orange' => 'Orange',
					'green' => 'Green',
					'purple' => 'Purple',
				),
				array(
					'class' => 'bar'
				),
				array(
					'green',
					'purple'
				)
			)
			->auto_close_end();
	}

	public function testTextarea()
	{
		$this->expectOutputString(
			'<textarea id="foo" class="bar" name="aboutyou">'
		);

		$this->html
			->textarea(
				'aboutyou',
				array(
					'id' => 'foo',
					'class' => 'bar'
				)
			);
	}

	public function testTextareaContent()
	{
		$this->expectOutputString(
			'My favorite color is &quot;totally&quot; blue.'
		);

		$this->html->textarea_content( 'My favorite color is "totally" blue.' );
	}

	public function testTextareaContentOverride()
	{
		$this->expectOutputString(
			'Moe, Larry, Shemp'
		);

		$this->html->textarea_content(
			'Moe, Larry, Curly',
			'Moe, Larry, Shemp'
		);
	}

	public function testTextareaNoClose()
	{
		$this->expectOutputString(
			'<textarea id="foo" class="bar" name="aboutyou">My favorite color is blue.'
		);

		$this->html
			->textarea(
				'aboutyou',
				array(
					'id' => 'foo',
					'class' => 'bar'
				),
				'My favorite color is blue.'
			);
	}

	public function testTextareaManualClose()
	{
		$this->expectOutputString(
			'<textarea id="foo" class="bar" name="aboutyou">My favorite color is &quot;totally&quot; blue.</textarea>'
		);

		// open textarea
		$this->html->textarea(
			'aboutyou',
			array(
				'id' => 'foo',
				'class' => 'bar'
			)
		);

		// content not passed through lib
		echo esc_textarea( 'My favorite color is "totally" blue.' );

		// close textarea
		$this->html->close();
	}
	
	public function testTextareaAutoClose()
	{
		$this->expectOutputString(
			'<textarea id="foo" class="bar" name="aboutyou">My favorite color is blue.</textarea>'
		);

		$this->html
			->auto_close_start()
			->textarea(
				'aboutyou',
				array(
					'id' => 'foo',
					'class' => 'bar'
				),
				'My favorite color is blue.'
			)
			->auto_close_end();
	}
	
	public function testTextareaViaField()
	{
		$this->expectOutputString(
			'<textarea id="foo" class="bar" name="aboutyou">My favorite color is blue.</textarea>'
		);

		$this->html->field(
			'textarea',
			'aboutyou',
			'My favorite color is blue.',
			array(
				'id' => 'foo',
				'class' => 'bar'
			)
		);
	}
}
