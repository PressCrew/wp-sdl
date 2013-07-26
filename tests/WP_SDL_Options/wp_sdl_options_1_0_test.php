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

	public function testRegisterSettings()
	{
		global $wp_settings_sections, $wp_settings_fields, $new_whitelist_options;

		// load admin api
		require_once( ABSPATH . 'wp-admin/includes/admin.php' );

		// get config
		$config = self::$options->config( 'test' )->save_mode( 'all' );

		// set up the config
		$design = $config->group( 'design' );
		
		$colors = $design->section( 'colors' );

		$colors
			->title( 'Colors' )
			->description( 'Choose some colors' )
			->priority( 20 );

		$colors
			->field( 'dark' )
			->title( 'Dark Colors' )
			->description( 'Select a dark color.' )
			->type( 'select' )
			->value(
				array(
					'maroon' => 'Maroon',
					'brown' => 'Brown',
					'black' => 'Black'
				)
			)
			->attributes(
				array(
					'class' => 'colors dark'
				)
			);

		$colors
			->field( 'light' )
			->title( 'Light Colors' )
			->description( 'Select a light color.' )
			->type( 'select' )
			->value(
				array(
					'white' => 'White',
					'yellow' => 'Yellow',
					'pink' => 'Pink'
				)
			)
			->attributes(
				array(
					'class' => 'colors light'
				)
			);

		$config->register();

//		var_dump( $wp_settings_sections, $wp_settings_fields, $new_whitelist_options );

		// expected setting sections
		$this->assertArrayHasKey( 'test_design', $wp_settings_sections );
		$this->assertArrayHasKey( 'test_design_colors', $wp_settings_sections['test_design'] );

		// expected colors section config
		$section_colors = $wp_settings_sections['test_design']['test_design_colors'];
		$this->assertEquals( 'test_design_colors', $section_colors['id'] );
		$this->assertEquals( 'Colors', $section_colors['title'] );
		$this->assertCount( 2, $section_colors['callback'] );
		$this->assertInstanceOf( 'WP_SDL_Options_Section_1_0', $section_colors['callback'][0] );
		$this->assertEquals( 'render', $section_colors['callback'][1] );

		// expected setting fields
		$this->assertArrayHasKey( 'test_design', $wp_settings_fields );
		$this->assertArrayHasKey( 'test_design_colors', $wp_settings_fields['test_design'] );
		$this->assertArrayHasKey( 'test_design_dark', $wp_settings_fields['test_design']['test_design_colors'] );
		$this->assertArrayHasKey( 'test_design_light', $wp_settings_fields['test_design']['test_design_colors'] );

		// expected dark colors field config
		$field_dark_colors = $wp_settings_fields['test_design']['test_design_colors']['test_design_dark'];
		$this->assertEquals( 'test_design_dark', $field_dark_colors['id'] );
		$this->assertEquals( 'Dark Colors', $field_dark_colors['title'] );
		$this->assertCount( 2, $field_dark_colors['callback'] );
		$this->assertInstanceOf( 'WP_SDL_Options_Field_1_0', $field_dark_colors['callback'][0] );
		$this->assertEquals( 'render', $field_dark_colors['callback'][1] );

		// expected light colors field config
		$field_light_colors = $wp_settings_fields['test_design']['test_design_colors']['test_design_light'];
		$this->assertEquals( 'test_design_light', $field_light_colors['id'] );
		$this->assertEquals( 'Light Colors', $field_light_colors['title'] );
		$this->assertCount( 2, $field_light_colors['callback'] );
		$this->assertInstanceOf( 'WP_SDL_Options_Field_1_0', $field_light_colors['callback'][0] );
		$this->assertEquals( 'render', $field_light_colors['callback'][1] );

		// expected new white list options
		$this->assertArrayHasKey( 'wpsdl_test', $new_whitelist_options );
		$this->assertContains( 'test_settings', $new_whitelist_options['wpsdl_test'] );

		// Whew!
	}

	public function testRegisterSettingsAdv()
	{
		global $new_whitelist_options;

		// load admin api
		require_once( ABSPATH . 'wp-admin/includes/admin.php' );

		// get config
		$config = self::$options->config( 'test2' )->save_mode( 'all' );

		// set up some groups
		$wood = $config->group( 'wood' );
		$brick = $config->group( 'brick' );

		// set up some sections
		$wood->section( 'pine' );
		$wood->section( 'oak' );
		$brick->section( 'clay' );
		$brick->section( 'sand' );

		// save mode all
		$new_whitelist_options = array();
		$config->save_mode( 'all' );
		$config->register();

		// expected new white list options
		$this->assertArrayHasKey( 'wpsdl_test2', $new_whitelist_options );
		$this->assertCount( 1, $new_whitelist_options['wpsdl_test2'] );
		$this->assertContains( 'test2_settings', $new_whitelist_options['wpsdl_test2'] );

		// save mode group
		$new_whitelist_options = array();
		$config->save_mode( 'group' );
		$config->register();

		// expected new white list options
		$this->assertArrayHasKey( 'wpsdl_test2', $new_whitelist_options );
		$this->assertCount( 2, $new_whitelist_options['wpsdl_test2'] );
		$this->assertContains( 'test2_wood_settings', $new_whitelist_options['wpsdl_test2'] );
		$this->assertContains( 'test2_brick_settings', $new_whitelist_options['wpsdl_test2'] );

		// save mode section
		$new_whitelist_options = array();
		$config->save_mode( 'section' );
		$config->register();

		// expected new white list options
		$this->assertArrayHasKey( 'wpsdl_test2', $new_whitelist_options );
		$this->assertCount( 4, $new_whitelist_options['wpsdl_test2'] );
		$this->assertContains( 'test2_wood_pine_settings', $new_whitelist_options['wpsdl_test2'] );
		$this->assertContains( 'test2_wood_oak_settings', $new_whitelist_options['wpsdl_test2'] );
		$this->assertContains( 'test2_brick_clay_settings', $new_whitelist_options['wpsdl_test2'] );
		$this->assertContains( 'test2_brick_sand_settings', $new_whitelist_options['wpsdl_test2'] );

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

	public function testParent()
	{
		$child = new STUB_Options_Object_1_0( 'test_child', WP_SDL::support( '1.0' ), self::$object );

		$this->assertInstanceOf( 'WP_SDL_Options_Object_1_0', $child->parent() );
		$this->assertEquals( 'test', $child->parent()->property( 'slug' ) );
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

	public function testId()
	{
		$this->assertEquals( 'test', self::$config->id() );
	}

	public function testGroup()
	{
		$group = self::$config->group( 'test_group' );
		$this->assertInstanceOf( 'WP_SDL_Options_Group_1_0', $group );
	}

	public function testSaveMode()
	{
		// make sure constants are correct
		$this->assertEquals( 'all', WP_SDL_Options_Config_1_0::SAVE_MODE_ALL );
		$this->assertEquals( 'group', WP_SDL_Options_Config_1_0::SAVE_MODE_GROUP );
		$this->assertEquals( 'section', WP_SDL_Options_Config_1_0::SAVE_MODE_SECTION );

		// default must be all
		$this->assertTrue( self::$config->save_mode_is( 'all' ) );

		// check mode NOT is
		$this->assertFalse( self::$config->save_mode_is( 'group' ) );

		// set and check
		self::$config->save_mode( 'section' );
		$this->assertTrue( self::$config->save_mode_is( 'section' ) );
		self::$config->save_mode( 'group' );
		$this->assertTrue( self::$config->save_mode_is( 'group' ) );
		self::$config->save_mode( 'all' );
		$this->assertTrue( self::$config->save_mode_is( 'all' ) );
	}

	public function testSaveModeException()
	{
		$this->setExpectedException( 'InvalidArgumentException' );
		self::$config->save_mode( 'foo' );
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
		$config = new WP_SDL_Options_Config_1_0( 'test_conf', WP_SDL::support( '1.0' ) );
		self::$group = $config->group( 'test_group' );

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

	public function testId()
	{
		$this->assertEquals( 'test_conf_test_group', self::$group->id() );
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
		$config = new WP_SDL_Options_Config_1_0( 'test_conf', WP_SDL::support( '1.0' ) );
		self::$section = $config->group('test_group')->section('test_section');

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

	public function testRender()
	{
		// expected markup
		$this->expectOutputString(
			'<p>Section Description</p>'
		);

		// configure it
		self::$section
			->title( 'Section Title' )
			->description( 'Section Description' );

		// render it
		self::$section->render();
	}

	public function testId()
	{
		$this->assertEquals( 'test_conf_test_group_test_section', self::$section->id() );
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
		$config = new WP_SDL_Options_Config_1_0( 'test_conf', WP_SDL::support( '1.0' ) );
		self::$field = $config->group('test_group')->section('test_section')->field('test_field');

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

	public function testId()
	{
		$this->assertEquals( 'test_conf_test_group_test_field', self::$field->id() );
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

	public function testRender()
	{
		// expected markup
		$this->expectOutputString(
			'<input class="pretty" id="test_field" type="text" name="test_field" value="A short string"/>' .
			'<p class="description">Field Description</p>'
		);

		// configure it
		self::$field
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
		self::$field->render();
	}

}