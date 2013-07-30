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
		$config = self::$options->config( 'app' );
		$this->assertInstanceOf( 'WP_SDL_Options_Config_1_0', $config );
	}

	public function testRegisterSettings()
	{
		global $wp_settings_sections, $wp_settings_fields, $new_whitelist_options;

		// load admin api
		require_once( ABSPATH . 'wp-admin/includes/admin.php' );

		// get config
		$config = self::$options->config( 'app' )->save_mode( 'all' );

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
		$this->assertArrayHasKey( 'app_design_group', $wp_settings_sections );
		$this->assertArrayHasKey( 'app_colors_section', $wp_settings_sections['app_design_group'] );

		// expected colors section config
		$section_colors = $wp_settings_sections['app_design_group']['app_colors_section'];
		$this->assertEquals( 'app_colors_section', $section_colors['id'] );
		$this->assertEquals( 'Colors', $section_colors['title'] );
		$this->assertCount( 2, $section_colors['callback'] );
		$this->assertInstanceOf( 'WP_SDL_Options_Section_1_0', $section_colors['callback'][0] );
		$this->assertEquals( 'render', $section_colors['callback'][1] );

		// expected setting fields
		$this->assertArrayHasKey( 'app_design_group', $wp_settings_fields );
		$this->assertArrayHasKey( 'app_colors_section', $wp_settings_fields['app_design_group'] );
		$this->assertArrayHasKey( 'app_dark_field', $wp_settings_fields['app_design_group']['app_colors_section'] );
		$this->assertArrayHasKey( 'app_light_field', $wp_settings_fields['app_design_group']['app_colors_section'] );

		// expected dark colors field config
		$field_dark_colors = $wp_settings_fields['app_design_group']['app_colors_section']['app_dark_field'];
		$this->assertEquals( 'app_dark_field', $field_dark_colors['id'] );
		$this->assertEquals( 'Dark Colors', $field_dark_colors['title'] );
		$this->assertCount( 2, $field_dark_colors['callback'] );
		$this->assertInstanceOf( 'WP_SDL_Options_Field_1_0', $field_dark_colors['callback'][0] );
		$this->assertEquals( 'render', $field_dark_colors['callback'][1] );

		// expected light colors field config
		$field_light_colors = $wp_settings_fields['app_design_group']['app_colors_section']['app_light_field'];
		$this->assertEquals( 'app_light_field', $field_light_colors['id'] );
		$this->assertEquals( 'Light Colors', $field_light_colors['title'] );
		$this->assertCount( 2, $field_light_colors['callback'] );
		$this->assertInstanceOf( 'WP_SDL_Options_Field_1_0', $field_light_colors['callback'][0] );
		$this->assertEquals( 'render', $field_light_colors['callback'][1] );

		// expected new white list options
		$this->assertArrayHasKey( 'wpsdl_app', $new_whitelist_options );
		$this->assertContains( 'app_config_settings', $new_whitelist_options['wpsdl_app'] );

		// Whew!
	}

	public function testRegisterSettingsAdv()
	{
		global $new_whitelist_options;

		// load admin api
		require_once( ABSPATH . 'wp-admin/includes/admin.php' );

		// get config
		$config = self::$options->config( 'app2' )->save_mode( 'all' );

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
		$this->assertArrayHasKey( 'wpsdl_app2', $new_whitelist_options );
		$this->assertCount( 1, $new_whitelist_options['wpsdl_app2'] );
		$this->assertContains( 'app2_config_settings', $new_whitelist_options['wpsdl_app2'] );

		// save mode group
		$new_whitelist_options = array();
		$config->save_mode( 'group' );
		$config->register();

		// expected new white list options
		$this->assertArrayHasKey( 'wpsdl_app2', $new_whitelist_options );
		$this->assertCount( 2, $new_whitelist_options['wpsdl_app2'] );
		$this->assertContains( 'app2_wood_group_settings', $new_whitelist_options['wpsdl_app2'] );
		$this->assertContains( 'app2_brick_group_settings', $new_whitelist_options['wpsdl_app2'] );

		// save mode section
		$new_whitelist_options = array();
		$config->save_mode( 'section' );
		$config->register();

		// expected new white list options
		$this->assertArrayHasKey( 'wpsdl_app2', $new_whitelist_options );
		$this->assertCount( 4, $new_whitelist_options['wpsdl_app2'] );
		$this->assertContains( 'app2_pine_section_settings', $new_whitelist_options['wpsdl_app2'] );
		$this->assertContains( 'app2_oak_section_settings', $new_whitelist_options['wpsdl_app2'] );
		$this->assertContains( 'app2_clay_section_settings', $new_whitelist_options['wpsdl_app2'] );
		$this->assertContains( 'app2_sand_section_settings', $new_whitelist_options['wpsdl_app2'] );

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

		self::$object = new STUB_Options_Object_1_0( 'app', WP_SDL::support( '1.0' )->options() );

		$this->assertInstanceOf( 'WP_SDL_Options_Object_1_0', self::$object );

		$this->assertEquals(
			'app',
			self::$object->property( 'slug' )
		);
	}

	public function tearDown()
	{
		self::$object = null;
	}

	public function testParent()
	{
		$child = new STUB_Options_Object_1_0( 'my_child', WP_SDL::support( '1.0' )->options() );
		$child->parent( self::$object );

		$this->assertInstanceOf( 'WP_SDL_Options_Object_1_0', $child->parent() );
		$this->assertEquals( 'app', $child->parent()->property( 'slug' ) );
	}

	public function testChild()
	{
		$child = new STUB_Options_Object_1_0( 'my_child', WP_SDL::support( '1.0' )->options() );
		$ret = self::$object->child( 'my_child', $child );
		$this->assertInstanceOf( 'STUB_Options_Object_1_0', $ret );
		$this->assertEquals( 'my_child', $ret->property( 'slug' ) );
	}

	public function testChildMismatchSlug()
	{
		$this->setExpectedException( 'InvalidArgumentException' );
		$child = new STUB_Options_Object_1_0( 'my_child', WP_SDL::support( '1.0' )->options() );
		self::$object->child( 'not_my_child', $child );
	}

	public function testChildStack()
	{
		$this->assertTrue( self::$object->children()->is_empty() );
		$this->assertEquals( 0, self::$object->children()->count() );

		$obj1 = self::$object->stub( 'obj1' );
		$obj2 = self::$object->stub( 'obj2' );

		$this->assertInstanceOf( 'WP_SDL_Options_Object_1_0', $obj1 );
		$this->assertInstanceOf( 'WP_SDL_Options_Object_1_0', $obj2 );

		$this->assertFalse( self::$object->children()->is_empty() );
		$this->assertEquals( 2, self::$object->children()->count() );
		
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
		return new STUB_Options_Object_1_0( 'bad-slug', WP_SDL::support( '1.0' )->options() );
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
		self::$config = new WP_SDL_Options_Config_1_0( 'app', WP_SDL::support( '1.0' )->options() );

		$this->assertInstanceOf( 'WP_SDL_Options_Config_1_0', self::$config );

		$this->assertEquals(
			'app',
			self::$config->property( 'slug' )
		);
	}

	public function tearDown()
	{
		self::$config = null;
	}

	public function testId()
	{
		$this->assertEquals( 'app_config', self::$config->id() );
	}

	public function testItem()
	{
		// group type
		$group = self::$config->item( 'group', 'foo', self::$config );
		$this->assertInstanceOf( 'WP_SDL_Options_Group_1_0', $group );
		$this->assertEquals( $group, self::$config->item( 'group', 'foo' ) );

		// section type
		$section = self::$config->item( 'section', 'bar', $group );
		$this->assertInstanceOf( 'WP_SDL_Options_Section_1_0', $section );
		$this->assertEquals( $section, self::$config->item( 'section', 'bar' ) );

		// field type
		$field = self::$config->item( 'field', 'baz', $section );
		$this->assertInstanceOf( 'WP_SDL_Options_Field_1_0', $field );
		$this->assertEquals( $field, self::$config->item( 'field', 'baz' ) );
	}

	public function testItemInvArg()
	{
		$this->setExpectedException( 'InvalidArgumentException' );
		self::$config->item( 'invalid-type', 'foo', self::$config );
	}

	public function testGroup()
	{
		// create obj
		$group = self::$config->group( 'design' );
		// new
		$this->assertInstanceOf( 'WP_SDL_Options_Group_1_0', $group );
		// existing
		$this->assertEquals( $group, self::$config->group( 'design' ) );
	}

	public function testSection()
	{
		// create objects
		$group = self::$config->group( 'design' );
		$section = self::$config->section( 'colors', $group );

		// new
		$this->assertInstanceOf( 'WP_SDL_Options_Section_1_0', $section );
		// existing
		$this->assertEquals( $section, self::$config->section( 'colors' ) );
	}

	public function testField()
	{
		// create objects
		$group = self::$config->group( 'design' );
		$section = self::$config->section( 'colors', $group );
		$field = self::$config->field( 'pretty', $section );

		// new
		$this->assertInstanceOf( 'WP_SDL_Options_Field_1_0', $field );
		// existing
		$this->assertEquals( $field, self::$config->field( 'pretty' ) );
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
		$config = new WP_SDL_Options_Config_1_0( 'app', WP_SDL::support( '1.0' )->options() );
		self::$group = $config->group( 'foo' );

		$this->assertInstanceOf( 'WP_SDL_Options_Group_1_0', self::$group );

		$this->assertEquals(
			'foo',
			self::$group->property( 'slug' )
		);
	}

	public function tearDown()
	{
		self::$group = null;
	}

	public function testId()
	{
		$this->assertEquals( 'app_foo_group', self::$group->id() );
	}


	public function testSection()
	{
		$section = self::$group->section( 'bar' );
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
		$config = new WP_SDL_Options_Config_1_0( 'app', WP_SDL::support( '1.0' )->options() );
		self::$section = $config->group('foo')->section('bar');

		$this->assertInstanceOf( 'WP_SDL_Options_Section_1_0', self::$section );

		$this->assertEquals(
			'bar',
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
		$this->assertEquals( 'app_bar_section', self::$section->id() );
	}

	public function testField()
	{
		$field = self::$section->field( 'baz' );
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
		$config = new WP_SDL_Options_Config_1_0( 'app', WP_SDL::support( '1.0' )->options() );
		self::$field = $config->group('foo')->section('bar')->field('baz');

		$this->assertInstanceOf( 'WP_SDL_Options_Field_1_0', self::$field );

		$this->assertEquals(
			'baz',
			self::$field->property( 'slug' )
		);
	}

	public function tearDown()
	{
		self::$field = null;
	}

	public function testId()
	{
		$this->assertEquals( 'app_baz_field', self::$field->id() );
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
			'<input class="pretty" id="app_baz_field" type="text" name="app_baz_field" value="A short string"/>' .
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