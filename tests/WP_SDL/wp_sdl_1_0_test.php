<?php

class WP_SDL_1_0_Test extends PHPUnit_Framework_TestCase
{
	private $wpsdl;

	protected $lib_class = 'WP_SDL_1_0';
	protected $lib_version = '1.0';

	public function setUp()
	{
		$this->wpsdl = WP_SDL::support( $this->lib_version );
		$this->assertInstanceOf( $this->lib_class, $this->wpsdl );
	}

	public function testAbstract()
	{
		// test for properties and types
		$this->assertAttributeInternalType( 'array', 'helper_instances', $this->wpsdl );
	}

	public function testInstance()
	{
		$this->assertInstanceOf( 'WP_SDL_1_0', WP_SDL::instance( 'WP_SDL_1_0' ) );
		$this->assertInstanceOf( 'WP_SDL_Html_1_0', WP_SDL::instance( 'WP_SDL_Html_1_0' ) );
	}

	public function testHtmlHelper()
	{
		$this->assertInstanceOf( 'WP_SDL_Html_1_0', $this->wpsdl->helper('html', '1.0') );
		$this->assertInstanceOf( 'WP_SDL_Html_1_0', $this->wpsdl->html('1.0') );
		$this->assertInstanceOf( 'WP_SDL_Html_1_0', $this->wpsdl->html() );
	}

	public function testOptionsHelper()
	{
		$this->assertInstanceOf( 'WP_SDL_Options_1_0', $this->wpsdl->helper('options', '1.0') );
		$this->assertInstanceOf( 'WP_SDL_Options_1_0', $this->wpsdl->options('1.0') );
		$this->assertInstanceOf( 'WP_SDL_Options_1_0', $this->wpsdl->options() );
	}

	/**
	 * @expectedException Exception
	 */
	public function testHelperFail()
	{
		$this->wpsdl->helper('not_a_helper','5.6.7');
	}

	/**
	 * @expectedException OverflowException
	 */
	public function testHelperCompatFail()
	{
		$this->wpsdl->html()->compat( $this->wpsdl );
	}
}

