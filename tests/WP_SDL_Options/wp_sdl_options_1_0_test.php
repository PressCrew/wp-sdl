<?php

/**
 * @group options
 */
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
		$field = new WP_SDL_Options_Field_1_0( 'the_name', WP_SDL::support( '1.0' ) );

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

/**
 * @group options
 */
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

		self::$object = new STUB_Options_Object_1_0( 'test', WP_SDL::support( '1.0' ) );
		
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
		$this->assertTrue( self::$object->children()->is_empty() );
		$this->assertEquals( 0, self::$object->children()->count() );

		$this->assertInstanceOf( 'WP_SDL_Options_Object_1_0', self::$object->subitem( 'obj1' ) );
		$this->assertInstanceOf( 'WP_SDL_Options_Object_1_0', self::$object->subitem( 'obj2' ) );

		$this->assertFalse( self::$object->children()->is_empty() );
		$this->assertEquals( 2, self::$object->children()->count() );
		
		// fetch objects
		$obj1 = self::$object->subitem( 'obj1' );
		$obj2 = self::$object->subitem( 'obj2' );

		// check slugs
		$this->assertEquals( 'obj1', $obj1->property( 'slug' ) );
		$this->assertEquals( 'obj2', $obj2->property( 'slug' ) );

		// check priorities
		$this->assertEquals( 10, $obj1->property( 'priority' ) );
		$this->assertEquals( 10, $obj2->property( 'priority' ) );
		
		// change priorities
		$obj1->priority( 99 );
		$obj2->priority( 100 );

		// check priorities
		$this->assertEquals( 99, $obj1->property( 'priority' ) );
		$this->assertEquals( 100, $obj2->property( 'priority' ) );

		// check stack priorities
		$this->assertAttributeEquals(
			array( 'obj1' => 99, 'obj2' => 100 ),
			'priority_table',
			self::$object->children()
		);
	}

	public function testSlugInvalid()
	{
		$this->setExpectedException( 'InvalidArgumentException' );
		return new STUB_Options_Object_1_0( 'bad-slug', WP_SDL::support( '1.0' ) );
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

/**
 * @group options
 */
class WP_SDL_Options_Config_1_0_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @var WP_SDL_Options_Object_1_0
	 */
	private static $config;

	public function setUp()
	{
		/* @var WP_SDL_Options_Config_1_0 */
		self::$config = new WP_SDL_Options_Config_1_0( 'test', WP_SDL::support( '1.0' ) );

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

/**
 * @group options
 */
class WP_SDL_Options_Group_1_0_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @var WP_SDL_Options_Group_1_0
	 */
	private static $group;

	public function setUp()
	{
		self::$group = new WP_SDL_Options_Group_1_0( 'test_group', WP_SDL::support( '1.0' ) );

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

/**
 * @group options
 */
class WP_SDL_Options_Section_1_0_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @var WP_SDL_Options_Section_1_0
	 */
	private static $section;

	public function setUp()
	{
		self::$section = new WP_SDL_Options_Section_1_0( 'test_section', WP_SDL::support( '1.0' ) );

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

/**
 * @group options
 */
class WP_SDL_Options_Field_1_0_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @var WP_SDL_Options_Field_1_0
	 */
	private static $field;

	public function setUp()
	{
		self::$field = new WP_SDL_Options_Field_1_0( 'test_field', WP_SDL::support( '1.0' ) );

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