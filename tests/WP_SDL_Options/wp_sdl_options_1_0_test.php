<?php

class WP_SDL_Options_1_0_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @var WP_SDL_Options_1_0
	 */
	public static $options;

	public function setUp()
	{
		self::$options = WP_SDL::support( '1.0' )->options();
		$this->assertInstanceOf( 'WP_SDL_Options_1_0', self::$options );
	}

	public function tearDown()
	{
		self::$options = null;
	}

	public function testConfig()
	{
		$config = self::$options->config( 'test' );
		$this->assertInstanceOf( 'WP_SDL_Options_Config_1_0', $config );
	}

	public function testRenderField()
	{
		// expected markup
		$this->expectOutputString(
			'<input class="pretty" id="the_name" type="text" name="the_name" value="A short string"/>' .
			'<p class="description">Field Description</p>'
		);

		// new fields instance
		$field = new WP_SDL_Options_Field_1_0( 'the_name' );

		// configure it
		$field
			->title( 'Field Title' )
			->description( 'Field Description' )
			->type( 'text' )
			->value( 'A short string' )
			->attributes(
				array(
					'class' => 'pretty'
				)
			);

		// render it
		self::$options->render_field( $field );
	}
}

class WP_SDL_Options_Object_1_0_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @var WP_SDL_Options_Object_1_0
	 */
	protected static $object;

	public function setUp()
	{
		// load stub
		require_once 'stubs/options_object_1_0.php';

		self::$object = new STUB_Options_Object_1_0( 'test' );
		
		$this->assertInstanceOf( 'WP_SDL_Options_Object_1_0', self::$object );

		$this->assertEquals(
			'test',
			self::$object->property( 'slug' )
		);
	}

	public function tearDown()
	{
		self::$object = null;
	}

	public function testChildStack()
	{
		$this->assertEquals( false, self::$object->has_children() );
		$this->assertEquals( 0, self::$object->count_children() );

		$obj1 = new WP_SDL_Options_Object_1_0( 'test_object1' );
		$obj2 = new WP_SDL_Options_Object_1_0( 'test_object2' );

		$obj1->priority( 11 );
		$obj2->priority( 1 );

		$this->assertEquals( 0, self::$object->cmp_children( $obj1, $obj1 ) );
		$this->assertEquals( 1, self::$object->cmp_children( $obj1, $obj2 ) );
		$this->assertEquals( -1, self::$object->cmp_children( $obj2, $obj1 ) );

		self::$object->ut_add_child( 'test_object1', $obj1 );
		self::$object->ut_add_child( 'test_object2', $obj2 );

		$this->assertEquals( true, self::$object->has_children() );
		$this->assertEquals( 2, self::$object->count_children() );

		$children = self::$object->get_children( true );
		$this->assertEquals( 'test_object2', array_shift($children)->property( 'slug' ) );
		$this->assertEquals( 'test_object1', array_shift($children)->property( 'slug' ) );
	}

	public function testSlugInvalid()
	{
		$this->setExpectedException( 'InvalidArgumentException' );
		return new WP_SDL_Options_Object_1_0( 'bad-slug' );
	}

	public function testTitle()
	{
		self::$object->title( 'Foo' );

		$this->assertEquals(
			'Foo',
			self::$object->property( 'title' )
		);
	}

	public function testDesc()
	{
		self::$object->description( 'Foo bar baz' );

		$this->assertEquals(
			'Foo bar baz',
			self::$object->property( 'description' )
		);
	}

	public function testPriority()
	{
		self::$object->priority( 5 );

		$this->assertEquals(
			5,
			self::$object->property( 'priority' )
		);
	}

	public function testPriorityInvalid()
	{
		$this->setExpectedException( 'InvalidArgumentException' );
		self::$object->priority( 'foo' );
	}

	public function testPropertyInvalid()
	{
		$this->setExpectedException( 'InvalidArgumentException' );
		self::$object->property( 'badpropertyname' );
	}
}

class WP_SDL_Options_Config_1_0_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @var WP_SDL_Options_Object_1_0
	 */
	private static $config;

	public function setUp()
	{
		/* @var WP_SDL_Options_Config_1_0 */
		self::$config = new WP_SDL_Options_Config_1_0( 'test' );

		$this->assertInstanceOf( 'WP_SDL_Options_Config_1_0', self::$config );

		$this->assertEquals(
			'test',
			self::$config->property( 'slug' )
		);
	}

	public function tearDown()
	{
		self::$config = null;
	}

	public function testGroup()
	{
		$group = self::$config->group( 'test_group' );
		$this->assertInstanceOf( 'WP_SDL_Options_Group_1_0', $group );
	}
}

class WP_SDL_Options_Group_1_0_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @var WP_SDL_Options_Group_1_0
	 */
	private static $group;

	public function setUp()
	{
		self::$group = new WP_SDL_Options_Group_1_0( 'test_group' );

		$this->assertInstanceOf( 'WP_SDL_Options_Group_1_0', self::$group );

		$this->assertEquals(
			'test_group',
			self::$group->property( 'slug' )
		);
	}

	public function tearDown()
	{
		self::$group = null;
	}

	public function testSection()
	{
		$section = self::$group->section( 'test_section' );
		$this->assertInstanceOf( 'WP_SDL_Options_Section_1_0', $section );
	}
}

class WP_SDL_Options_Section_1_0_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @var WP_SDL_Options_Section_1_0
	 */
	private static $section;

	public function setUp()
	{
		self::$section = new WP_SDL_Options_Section_1_0( 'test_section' );

		$this->assertInstanceOf( 'WP_SDL_Options_Section_1_0', self::$section );

		$this->assertEquals(
			'test_section',
			self::$section->property( 'slug' )
		);
	}

	public function tearDown()
	{
		self::$section = null;
	}

	public function testField()
	{
		$field = self::$section->field( 'test_field' );
		$this->assertInstanceOf( 'WP_SDL_Options_Field_1_0', $field );
	}
}

class WP_SDL_Options_Field_1_0_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @var WP_SDL_Options_Field_1_0
	 */
	private static $field;

	public function setUp()
	{
		self::$field = new WP_SDL_Options_Field_1_0( 'test_field' );

		$this->assertInstanceOf( 'WP_SDL_Options_Field_1_0', self::$field );

		$this->assertEquals(
			'test_field',
			self::$field->property( 'slug' )
		);
	}

	public function tearDown()
	{
		self::$field = null;
	}

	public function testType()
	{
		self::$field->type( 'input' );

		$this->assertEquals(
			'input',
			self::$field->property( 'type' )
		);
	}

	public function testValueString()
	{
		self::$field->value( 'abc123' );

		$this->assertEquals(
			'abc123',
			self::$field->property( 'value' )
		);
	}

	public function testValueInt()
	{
		self::$field->value( 123 );

		$this->assertEquals(
			123,
			self::$field->property( 'value' )
		);
	}

	public function testValueArray()
	{
		self::$field->value( array( 1, 'two' ) );

		$this->assertEquals(
			array( 1, 'two' ),
			self::$field->property( 'value' )
		);
	}

	public function testCurrentValueString()
	{
		self::$field->current_value( 'abc123' );

		$this->assertEquals(
			'abc123',
			self::$field->property( 'current_value' )
		);
	}

	public function testCurrentValueInt()
	{
		self::$field->current_value( 123 );

		$this->assertEquals(
			123,
			self::$field->property( 'current_value' )
		);
	}

	public function testCurrentValueArray()
	{
		self::$field->current_value( array( 1, 'two' ) );

		$this->assertEquals(
			array( 1, 'two' ),
			self::$field->property( 'current_value' )
		);
	}

	public function testAttributes()
	{
		self::$field->attributes(
			array( 'id' => 'foo', 'class' => 'baz' )
		);

		$this->assertEquals(
			array( 'id' => 'foo', 'class' => 'baz' ),
			self::$field->property( 'attributes' )
		);
	}

	public function testAttributesBad()
	{
		$this->setExpectedException( 'InvalidArgumentException' );
		self::$field->attributes( 'not an array' );
	}

}