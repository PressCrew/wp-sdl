<?php

class WP_SDL_Test extends PHPUnit_Framework_TestCase
{
	public function testAbstract()
	{
		// test property types
		$this->assertAttributeInternalType( 'array', 'files_ready', 'WP_SDL' );
		$this->assertAttributeInternalType( 'array', 'files_loaded', 'WP_SDL' );
		$this->assertAttributeInternalType( 'array', 'instances', 'WP_SDL' );
	}

	public function testInit()
	{
		// check for all versions
		$this->assertAttributeCount( 5, 'files_ready', 'WP_SDL' );
	}

	public function testClassName()
	{
		$this->assertEquals( 'WP_SDL_1_2_3', WP_SDL::class_name( '1.2.3' ) );
		$this->assertEquals( 'WP_SDL_Html_1_2_3', WP_SDL::class_name( 'Html-1.2.3' ) );
	}

	public function testClassFile()
	{
		$this->assertEquals( 'wp_sdl_1_2_3.php', WP_SDL::class_file( 'WP_SDL_1_2_3' ) );
	}

	public function testSupport()
	{
		$obj = WP_SDL::support( '1.0' );
		$this->assertInstanceOf( 'WP_SDL_1_0', $obj );
		$this->assertAttributeCount( 5, 'files_ready', 'WP_SDL' );
		$this->assertAttributeCount( 1, 'files_loaded', 'WP_SDL' );
	}

	public function testSupportsOneSuccess()
	{
		$this->assertTrue( WP_SDL::supports( '1.0' ) );
	}

	public function testSupportsOneFail()
	{
		$this->assertFalse( WP_SDL::supports( 'a.b.c' ) );
	}

	public function testSupportsAll()
	{
		$this->assertEquals(
			array(
				'WP_SDL_1_0',
				'WP_SDL_Helper_1_0',
				'WP_SDL_Html_1_0',
				'WP_SDL_Struct_1_0',
				'WP_SDL_Widget_1_0'
			),
			WP_SDL::supports()
		);
	}


}
