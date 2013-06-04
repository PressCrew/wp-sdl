<?php

/**
 * @group struct
 */
class WP_SDL_Struct_1_0_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @var WP_SDL_Struct_1_0
	 */
	private $struct;

	public function setUp()
	{
		$this->struct = WP_SDL::support( '1.0' )->struct();
	}

	public function tearDown()
	{
		unset( $this->struct );
	}

	public function testInstance()
	{
		$this->assertInstanceOf( 'WP_SDL_Struct_1_0', $this->struct );
	}

	public function testVersionConstant()
	{
		$this->assertAttributeEquals( '1.0', 'VERSION', 'WP_SDL_Struct_1_0' );
	}

	public function testStaticList()
	{
		$data = $this->struct->static_list( 5 );
		$this->assertInstanceOf( 'WP_SDL_Struct_StaticList_1_0', $data );
		$this->assertAttributeEquals( 5, 'length', $data );
		$this->assertEquals( 5, $data->count() );
	}

	public function testDynamicList()
	{
		$data = $this->struct->dynamic_list();
		$this->assertInstanceOf( 'WP_SDL_Struct_DynamicList_1_0', $data );
	}

	public function testMap()
	{
		$data = $this->struct->map();
		$this->assertInstanceOf( 'WP_SDL_Struct_Map_1_0', $data );
	}

	public function testQueue()
	{
		$data = $this->struct->queue();
		$this->assertInstanceOf( 'WP_SDL_Struct_Queue_1_0', $data );
	}

	public function testPriorityQueue()
	{
		$data = $this->struct->priority_queue();
		$this->assertInstanceOf( 'WP_SDL_Struct_PriorityQueue_1_0', $data );
	}

}

/**
 * @group struct
 */
class WP_SDL_Struct_DLL_1_0_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @var STUB_Struct_DLL_1_0
	 */
	private $dll;

	/**
	 * @var array
	 */
	private static $expected =
		array(
			'a' => 'aye',
			'b' => '',
			'c' => 1,
			'd' => 0,
			'e' => null,
			'f' => false,
			-9 => 'neg',
			0 => 'zero',
			9 => 'nine'
		);

	static public function setUpBeforeClass()
	{
		// run parent just in case!
		parent::setUpBeforeClass();

		// load stub class
		require_once dirname( __FILE__ ) . '/stubs/stub_struct_dll_1_0.php';
	}

	public function setUp()
	{
		// create intance of stub class for testing
		$this->dll = new STUB_Struct_DLL_1_0();
		// make sure list is empty before each run
		$this->assertAttributeEmpty( 'list', $this->dll );
	}

	public function tearDown()
	{
		unset( $this->dll );
	}

	public function testInstance()
	{
		$this->assertInstanceOf( 'WP_SDL_Struct_DLL_1_0', $this->dll );
		$this->assertInstanceOf( 'Countable', $this->dll );
		$this->assertInstanceOf( 'Iterator', $this->dll );
	}

	public function testListStructure()
	{
		$this->dll->ut_dummy_data();
		$this->assertAttributeEquals( self::$expected, 'list', $this->dll );
	}

	public function testIsEmpty()
	{
		// test with no data
		$this->assertTrue( $this->dll->is_empty() );

		// add data
		$this->dll->ut_dummy_data();

		// test with data
		$this->assertFalse( $this->dll->is_empty() );
	}

	public function testIsNull()
	{
		$this->dll->ut_dummy_data();
		$this->assertFalse( $this->dll->is_null( 'a' ) );
		$this->assertFalse( $this->dll->is_null( 'b' ) );
		$this->assertFalse( $this->dll->is_null( 'c' ) );
		$this->assertFalse( $this->dll->is_null( 'd' ) );
		$this->assertTrue( $this->dll->is_null( 'e' ) );
		$this->assertFalse( $this->dll->is_null( 'f' ) );
		$this->assertFalse( $this->dll->is_null( -9 ) );
		$this->assertFalse( $this->dll->is_null( 0 ) );
		$this->assertFalse( $this->dll->is_null( 9 ) );
	}

	public function testCount()
	{
		$this->dll->ut_dummy_data();
		$this->assertEquals( 9, $this->dll->count() );
	}

	public function testCountable()
	{
		$this->dll->ut_dummy_data();
		$this->assertEquals( 9, count( $this->dll ) );
	}

	public function testCountCache()
	{
		// should be zero to start
		$this->assertEquals( 0, $this->dll->count() );

		// add one
		$this->dll->ut_insert( 0, 'zero' );
		$this->assertEquals( 1, $this->dll->count() );

		// add three more
		$this->dll->ut_insert( 1, 'one' );
		$this->dll->ut_insert( 2, 'two' );
		$this->dll->ut_insert( 3, 'three' );
		$this->assertEquals( 4, $this->dll->count() );

		// delete two
		$this->dll->ut_delete( 3 );
		$this->dll->ut_delete( 2 );
		$this->assertEquals( 2, $this->dll->count() );
	}

	public function testKeyExists()
	{
		$this->dll->ut_dummy_data();
		$this->assertTrue( $this->dll->exists( 'b' ) );
		$this->assertTrue( $this->dll->exists( -9 ) );
		$this->assertTrue( $this->dll->exists( 0 ) );
		$this->assertTrue( $this->dll->exists( 9 ) );
	}

	public function testKeyNotExists()
	{
		$this->dll->ut_dummy_data();
		$this->assertFalse( $this->dll->exists( 'z' ) );
		$this->assertFalse( $this->dll->exists( 123 ) );
		$this->assertFalse( $this->dll->exists( -123 ) );
	}

	public function testGet()
	{
		$this->dll->ut_dummy_data();
		$this->assertEquals( 'aye', $this->dll->get( 'a' ) );
		$this->assertEquals( '', $this->dll->get( 'b' ) );
		$this->assertEquals( 1, $this->dll->get( 'c' ) );
		$this->assertEquals( 0, $this->dll->get( 'd' ) );
		$this->assertEquals( null, $this->dll->get( 'e' ) );
		$this->assertEquals( false, $this->dll->get( 'f' ) );
		$this->assertEquals( 'neg', $this->dll->get( -9 ) );
		$this->assertEquals( 'zero', $this->dll->get( 0 ) );
		$this->assertEquals( 'nine', $this->dll->get( 9 ) );
	}

	public function testGetOutOfRangeString()
	{
		$this->setExpectedException( 'OutOfRangeException' );
		$this->dll->ut_dummy_data();
		$this->dll->get( 'bad' );
	}

	public function testGetOutOfRangeInt()
	{
		$this->setExpectedException( 'OutOfRangeException' );
		$this->dll->ut_dummy_data();
		$this->dll->get( 123 );
	}

	public function testGetOutOfRangeSignedInt()
	{
		$this->setExpectedException( 'OutOfRangeException' );
		$this->dll->ut_dummy_data();
		$this->dll->get( -123 );
	}
	
	public function testKeyBeforeIteration()
	{
		$this->dll->ut_dummy_data();
		$this->assertEquals( 'a', $this->dll->key() );
	}

	public function testIterator()
	{
		$this->dll->ut_dummy_data();

		// should be at first key
		$this->assertTrue( $this->dll->valid() );
		$this->assertEquals( 'a', $this->dll->key() );
		$this->assertEquals( 'aye', $this->dll->current() );

		// go forward three
		$this->assertNull( $this->dll->next() );
		$this->assertTrue( $this->dll->valid() );
		$this->assertNull( $this->dll->next() );
		$this->assertTrue( $this->dll->valid() );
		$this->assertNull( $this->dll->next() );
		$this->assertTrue( $this->dll->valid() );

		// should be at 4th key
		$this->assertEquals( 'd', $this->dll->key() );
		$this->assertEquals( 0, $this->dll->current() );

		// go back two
		$this->assertNull( $this->dll->prev() );
		$this->assertTrue( $this->dll->valid() );
		$this->assertNull( $this->dll->prev() );
		$this->assertTrue( $this->dll->valid() );

		// should be at 2nd key
		$this->assertEquals( 'b', $this->dll->key() );
		$this->assertEquals( '', $this->dll->current() );

		// go to the end
		$this->assertEquals( 'nine', $this->dll->ut_last() );
		$this->assertEquals( 9, $this->dll->key() );
		$this->assertTrue( $this->dll->valid() );

		// try to go past the end
		$this->assertNull( $this->dll->next() );
		$this->assertNull( $this->dll->key() );
		$this->assertFalse( $this->dll->valid() );
		$this->assertFalse( $this->dll->current() );

		// rewind it
		$this->assertNull( $this->dll->rewind() );
		$this->assertTrue( $this->dll->valid() );

		// should be back at first key
		$this->assertEquals( 'a', $this->dll->key() );
		$this->assertEquals( 'aye', $this->dll->current() );

		// try to go past the beginning
		$this->assertNull( $this->dll->prev() );
		$this->assertNull( $this->dll->key() );
		$this->assertFalse( $this->dll->valid() );
		$this->assertFalse( $this->dll->current() );

		// go to the beginning
		$this->assertEquals( 'aye', $this->dll->ut_first() );
		$this->assertEquals( 'aye', $this->dll->current() );
		$this->assertEquals( 'a', $this->dll->key() );
		$this->assertTrue( $this->dll->valid() );
	}

	public function testIteratorEmpty()
	{
		// make sure traversal behaves correctly for an empty list
		$this->assertFalse( $this->dll->valid() );
		$this->assertFalse( $this->dll->current() );
		$this->assertNull( $this->dll->key() );
		$this->assertNull( $this->dll->prev() );
		$this->assertNull( $this->dll->next() );
		$this->assertNull( $this->dll->rewind() );
	}

	public function testIteration()
	{
		$this->dll->ut_dummy_data();

		// results array
		$actual = array();

		// loop the list
		foreach( $this->dll as $key => $value ) {
			// assign to result
			$actual[ $key ] = $value;
		}

		// should be identical
		$this->assertEquals( self::$expected, $actual );
	}
}

/**
 * @group struct
 */
class WP_SDL_Struct_StaticList_1_0_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * List length
	 */
	const LENGTH = 5;

	/**
	 * @var WP_SDL_Struct_StaticList_1_0
	 */
	private $list;

	/**
	 * @var array
	 */
	private static $expected =
		array(
			0 => 'zero',
			1 => 'one',
			2 => 'two',
			3 => 'three',
			4 => 'four'
		);

	public function setUp()
	{
		$this->list =
			WP_SDL::support( '1.0' )
				->struct()
				->static_list( self::LENGTH );
	}

	public function tearDown()
	{
		unset( $this->list );
	}

	private function assertListCount()
	{
		$this->assertEquals( self::LENGTH, $this->list->count() );
	}

	private function dummyDataSet()
	{
		$this->list->set( 0, 'zero' );
		$this->list->set( 1, 'one' );
		$this->list->set( 2, 'two' );
		$this->list->set( 3, 'three' );
		$this->list->set( 4, 'four' );
	}

	private function dummyDataAdd()
	{
		$this->list->add( 0, 'zero' );
		$this->list->add( 1, 'one' );
		$this->list->add( 2, 'two' );
		$this->list->add( 3, 'three' );
		$this->list->add( 4, 'four' );
	}
	
	public function testInstance()
	{
		$this->assertInstanceOf( 'WP_SDL_Struct_StaticList_1_0', $this->list );
		$this->assertInstanceOf( 'Countable', $this->list );
		$this->assertInstanceOf( 'Iterator', $this->list );
	}

	public function testLength()
	{
		$this->assertAttributeEquals( self::LENGTH, 'length', $this->list );
		$this->assertFalse( $this->list->is_empty() );
		$this->assertListCount();
	}

	public function testLengthString()
	{
		$this->setExpectedException( 'InvalidArgumentException' );
		new WP_SDL_Struct_StaticList_1_0( 'bad' );
	}

	public function testLengthStringNone()
	{
		$this->setExpectedException( 'InvalidArgumentException' );
		new WP_SDL_Struct_StaticList_1_0( '' );
	}

	public function testLengthSigned()
	{
		$this->setExpectedException( 'InvalidArgumentException' );
		new WP_SDL_Struct_StaticList_1_0( -123 );
	}

	public function testLengthNull()
	{
		$this->setExpectedException( 'InvalidArgumentException' );
		new WP_SDL_Struct_StaticList_1_0( null );
	}

	public function testLengthTrue()
	{
		$this->setExpectedException( 'InvalidArgumentException' );
		new WP_SDL_Struct_StaticList_1_0( true );
	}

	public function testLengthFalse()
	{
		$this->setExpectedException( 'InvalidArgumentException' );
		new WP_SDL_Struct_StaticList_1_0( false );
	}

	public function testSet()
	{
		$this->dummyDataSet();

		// internal list array must exactly match expected array
		$this->assertAttributeEquals( self::$expected, 'list', $this->list );

		// test low and high end of range
		// (set should always return null)
		$this->assertNull( $this->list->set( 0, 'foo' ) );
		$this->assertNull( $this->list->set( 4, 'foo' ) );

		// check count
		$this->assertListCount();
	}

	public function testSetOutOfRangeUnsigned()
	{
		$this->setExpectedException( 'OutOfRangeException' );
		$this->list->set( 5, 'foo' );
	}

	public function testSetOutOfRangeSigned()
	{
		$this->setExpectedException( 'OutOfRangeException' );
		$this->list->set( -1, 'foo' );
	}

	public function testSetInvalidArgString()
	{
		$this->setExpectedException( 'InvalidArgumentException' );
		$this->list->set( 'a', 'foo' );
	}

	public function testSetInvalidArgFloat()
	{
		$this->setExpectedException( 'InvalidArgumentException' );
		$this->list->set( 1.5, 'foo' );
	}

	public function testSetInvalidArgNull()
	{
		$this->setExpectedException( 'InvalidArgumentException' );
		$this->list->set( null, 'foo' );
	}

	public function testAdd()
	{
		$this->dummyDataAdd();

		// internal list array must exactly match expected array
		$this->assertAttributeEquals( self::$expected, 'list', $this->list );

		// check count
		$this->assertListCount();
	}

	public function testAddStrict()
	{
		$this->dummyDataAdd();

		$this->setExpectedException( 'OverflowException' );
		$this->list->add( 1, 'foo' );
	}

	public function testAddLoose()
	{
		$this->dummyDataAdd();

		// nothing should happen
		$this->assertNull( $this->list->add( 1, 'foo', false ) );
		
		// value should NOT have changed
		$this->assertEquals( 'one', $this->list->get( 1 ) );

		// count should be same
		$this->assertListCount();
	}

	public function testErase()
	{
		$this->dummyDataAdd();
		$this->assertNull( $this->list->erase( 3 ) );
		$this->assertEquals( null, $this->list->get( 3 ) );

		// count should NOT have changed
		$this->assertListCount();
	}

	public function testTop()
	{
		$this->assertNull( $this->list->top() );
		$this->dummyDataSet();
		$this->assertEquals( 'zero', $this->list->top() );
	}

	public function testBottom()
	{
		$this->assertNull( $this->list->bottom() );
		$this->dummyDataSet();
		$this->assertEquals( 'four', $this->list->bottom() );
	}

}