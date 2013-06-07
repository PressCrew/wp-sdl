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
abstract class WP_SDL_Struct_Base_1_0_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @var WP_SDL_Struct_DLL_1_0
	 */
	protected $list;

	public function tearDown()
	{
		unset( $this->list );
	}

	public function testInterfaces()
	{
		$this->assertInstanceOf( 'Countable', $this->list );
		$this->assertInstanceOf( 'Iterator', $this->list );
	}
}

/**
 * @group struct
 */
class WP_SDL_Struct_DLL_1_0_Test extends WP_SDL_Struct_Base_1_0_Test
{
	/**
	 * @var STUB_Struct_DLL_1_0
	 */
	protected $list;

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
		$this->list = new STUB_Struct_DLL_1_0();
		// make sure list is empty before each run
		$this->assertAttributeEmpty( 'list', $this->list );
	}

	public function testInstance()
	{
		$this->assertInstanceOf( 'WP_SDL_Struct_DLL_1_0', $this->list );
	}

	public function testListStructure()
	{
		$this->list->ut_dummy_data();
		$this->assertAttributeEquals( self::$expected, 'list', $this->list );
	}

	public function testIsEmpty()
	{
		// test with no data
		$this->assertTrue( $this->list->is_empty() );

		// add data
		$this->list->ut_dummy_data();

		// test with data
		$this->assertFalse( $this->list->is_empty() );
	}

	public function testIsNull()
	{
		$this->list->ut_dummy_data();
		$this->assertFalse( $this->list->is_null( 'a' ) );
		$this->assertFalse( $this->list->is_null( 'b' ) );
		$this->assertFalse( $this->list->is_null( 'c' ) );
		$this->assertFalse( $this->list->is_null( 'd' ) );
		$this->assertTrue( $this->list->is_null( 'e' ) );
		$this->assertFalse( $this->list->is_null( 'f' ) );
		$this->assertFalse( $this->list->is_null( -9 ) );
		$this->assertFalse( $this->list->is_null( 0 ) );
		$this->assertFalse( $this->list->is_null( 9 ) );
	}

	public function testCount()
	{
		$this->list->ut_dummy_data();
		$this->assertEquals( 9, $this->list->count() );
	}

	public function testCountable()
	{
		$this->list->ut_dummy_data();
		$this->assertEquals( 9, count( $this->list ) );
	}

	public function testCountCache()
	{
		// should be zero to start
		$this->assertEquals( 0, $this->list->count() );

		// add one
		$this->list->ut_insert( 0, 'zero' );
		$this->assertEquals( 1, $this->list->count() );

		// add three more
		$this->list->ut_insert( 1, 'one' );
		$this->list->ut_insert( 2, 'two' );
		$this->list->ut_insert( 3, 'three' );
		$this->assertEquals( 4, $this->list->count() );

		// delete two
		$this->list->ut_delete( 3 );
		$this->list->ut_delete( 2 );
		$this->assertEquals( 2, $this->list->count() );
	}

	public function testKeyExists()
	{
		$this->list->ut_dummy_data();
		$this->assertTrue( $this->list->exists( 'b' ) );
		$this->assertTrue( $this->list->exists( -9 ) );
		$this->assertTrue( $this->list->exists( 0 ) );
		$this->assertTrue( $this->list->exists( 9 ) );
	}

	public function testKeyNotExists()
	{
		$this->list->ut_dummy_data();
		$this->assertFalse( $this->list->exists( 'z' ) );
		$this->assertFalse( $this->list->exists( 123 ) );
		$this->assertFalse( $this->list->exists( -123 ) );
	}

	public function testGet()
	{
		$this->list->ut_dummy_data();
		$this->assertEquals( 'aye', $this->list->get( 'a' ) );
		$this->assertEquals( '', $this->list->get( 'b' ) );
		$this->assertEquals( 1, $this->list->get( 'c' ) );
		$this->assertEquals( 0, $this->list->get( 'd' ) );
		$this->assertEquals( null, $this->list->get( 'e' ) );
		$this->assertEquals( false, $this->list->get( 'f' ) );
		$this->assertEquals( 'neg', $this->list->get( -9 ) );
		$this->assertEquals( 'zero', $this->list->get( 0 ) );
		$this->assertEquals( 'nine', $this->list->get( 9 ) );
	}

	public function testGetOutOfBoundsString()
	{
		$this->setExpectedException( 'OutOfBoundsException' );
		$this->list->ut_dummy_data();
		$this->list->get( 'bad' );
	}

	public function testGetOutOfBoundsInt()
	{
		$this->setExpectedException( 'OutOfBoundsException' );
		$this->list->ut_dummy_data();
		$this->list->get( 123 );
	}

	public function testGetOutOfBoundsSignedInt()
	{
		$this->setExpectedException( 'OutOfBoundsException' );
		$this->list->ut_dummy_data();
		$this->list->get( -123 );
	}
	
	public function testKeyBeforeIteration()
	{
		$this->list->ut_dummy_data();
		$this->assertEquals( 'a', $this->list->key() );
	}

	public function testIterator()
	{
		$this->list->ut_dummy_data();

		// should be at first key
		$this->assertTrue( $this->list->valid() );
		$this->assertEquals( 'a', $this->list->key() );
		$this->assertEquals( 'aye', $this->list->current() );

		// go forward three
		$this->assertNull( $this->list->next() );
		$this->assertTrue( $this->list->valid() );
		$this->assertNull( $this->list->next() );
		$this->assertTrue( $this->list->valid() );
		$this->assertNull( $this->list->next() );
		$this->assertTrue( $this->list->valid() );

		// should be at 4th key
		$this->assertEquals( 'd', $this->list->key() );
		$this->assertEquals( 0, $this->list->current() );

		// go back two
		$this->assertNull( $this->list->prev() );
		$this->assertTrue( $this->list->valid() );
		$this->assertNull( $this->list->prev() );
		$this->assertTrue( $this->list->valid() );

		// should be at 2nd key
		$this->assertEquals( 'b', $this->list->key() );
		$this->assertEquals( '', $this->list->current() );

		// go to the end
		$this->assertEquals( 'nine', $this->list->ut_last() );
		$this->assertEquals( 9, $this->list->key() );
		$this->assertTrue( $this->list->valid() );

		// try to go past the end
		$this->assertNull( $this->list->next() );
		$this->assertNull( $this->list->key() );
		$this->assertFalse( $this->list->valid() );
		$this->assertFalse( $this->list->current() );

		// rewind it
		$this->assertNull( $this->list->rewind() );
		$this->assertTrue( $this->list->valid() );

		// should be back at first key
		$this->assertEquals( 'a', $this->list->key() );
		$this->assertEquals( 'aye', $this->list->current() );

		// try to go past the beginning
		$this->assertNull( $this->list->prev() );
		$this->assertNull( $this->list->key() );
		$this->assertFalse( $this->list->valid() );
		$this->assertFalse( $this->list->current() );

		// go to the beginning
		$this->assertEquals( 'aye', $this->list->ut_first() );
		$this->assertEquals( 'aye', $this->list->current() );
		$this->assertEquals( 'a', $this->list->key() );
		$this->assertTrue( $this->list->valid() );
	}

	public function testIteratorEmpty()
	{
		// make sure traversal behaves correctly for an empty list
		$this->assertFalse( $this->list->valid() );
		$this->assertFalse( $this->list->current() );
		$this->assertNull( $this->list->key() );
		$this->assertNull( $this->list->prev() );
		$this->assertNull( $this->list->next() );
		$this->assertNull( $this->list->rewind() );
	}

	public function testIteration()
	{
		$this->list->ut_dummy_data();

		// results array
		$actual = array();

		// loop the list
		foreach( $this->list as $key => $value ) {
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
abstract class WP_SDL_Struct_BaseList_1_0_Test extends WP_SDL_Struct_Base_1_0_Test
{
	/**
	 * List length
	 */
	const LENGTH = 5;

	/**
	 * @var WP_SDL_Struct_DLL_1_0
	 */
	protected $list;

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

	protected function assertListCount()
	{
		$this->assertEquals( self::LENGTH, $this->list->count() );
	}

	protected function dummyDataSet()
	{
		$this->list->set( 0, 'zero' );
		$this->list->set( 1, 'one' );
		$this->list->set( 2, 'two' );
		$this->list->set( 3, 'three' );
		$this->list->set( 4, 'four' );
	}

	protected function dummyDataAdd()
	{
		$this->list->add( 0, 'zero' );
		$this->list->add( 1, 'one' );
		$this->list->add( 2, 'two' );
		$this->list->add( 3, 'three' );
		$this->list->add( 4, 'four' );
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

	public function testAddOverflow()
	{
		$this->dummyDataAdd();

		$this->setExpectedException( 'OverflowException' );

		$this->list->add( 1, 'foo' );
	}

	public function testAddOverflowStrict()
	{
		$this->dummyDataAdd();

		$this->setExpectedException( 'OverflowException' );

		// set safe mode with strict
		$this->list->safe_mode(
			WP_SDL_Struct_DLL_1_0::SAFE_MODE_ENABLE |
			WP_SDL_Struct_DLL_1_0::SAFE_MODE_STRICT
		);

		$this->list->add( 1, 'foo' );
	}

	public function testAddOverflowLoose()
	{
		$this->dummyDataAdd();

		// set safe mode without strict
		$this->list->safe_mode(
			WP_SDL_Struct_DLL_1_0::SAFE_MODE_ENABLE
		);

		// safe mode check with no strict
		$this->assertNull( $this->list->add( 1, 'foo', true ) );
		$this->assertEquals( 'one', $this->list->get( 1 ) );

		// completely override safe mode check at call time
		$this->assertNull( $this->list->add( 1, 'foo', false ) );
		$this->assertEquals( 'foo', $this->list->get( 1 ) );

		// count should be same
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

	public function testIteration()
	{
		$this->dummyDataSet();

		// results array
		$actual = array();

		// loop the list
		foreach( $this->list as $key => $value ) {
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
class WP_SDL_Struct_StaticList_1_0_Test extends WP_SDL_Struct_BaseList_1_0_Test
{
	/**
	 * @var WP_SDL_Struct_StaticList_1_0
	 */
	protected $list;

	public function setUp()
	{
		$this->list =
			WP_SDL::support( '1.0' )
				->struct()
				->static_list( self::LENGTH );
	}

	public function testInstance()
	{
		$this->assertInstanceOf( 'WP_SDL_Struct_StaticList_1_0', $this->list );
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

	public function testSetOutOfRangeUnsigned()
	{
		$this->setExpectedException( 'OutOfRangeException' );
		$this->list->set( 5, 'foo' );
	}

	public function testErase()
	{
		$this->dummyDataAdd();
		$this->assertNull( $this->list->erase( 3 ) );
		$this->assertEquals( null, $this->list->get( 3 ) );

		// count should NOT have changed
		$this->assertListCount();
	}
}

/**
 * @group struct
 */
class WP_SDL_Struct_DynamicList_1_0_Test extends WP_SDL_Struct_BaseList_1_0_Test
{
	/**
	 * @var WP_SDL_Struct_DynamicList_1_0
	 */
	protected $list;

	public function setUp()
	{
		$this->list =
			WP_SDL::support( '1.0' )
				->struct()
				->dynamic_list();
	}

	public function testInstance()
	{
		$this->assertInstanceOf( 'WP_SDL_Struct_DynamicList_1_0', $this->list );
	}

	public function testPrepend()
	{
		$this->assertNull( $this->list->prepend( 'x' ) );
		$this->assertNull( $this->list->prepend( 'y' ) );
		$this->assertNull( $this->list->prepend( 'z' ) );

		$this->assertTrue( $this->list->exists( 0 ) );
		$this->assertTrue( $this->list->exists( -1 ) );
		$this->assertTrue( $this->list->exists( -2 ) );

		$this->assertEquals( 'z', $this->list->get( -2 ) );
		$this->assertEquals( 'y', $this->list->get( -1 ) );
		$this->assertEquals( 'x', $this->list->get( 0 ) );

		$this->assertEquals( 3, $this->list->count() );
	}

	public function testPrependMore()
	{
		$this->dummyDataSet();

		$this->assertNull( $this->list->prepend( 'eno' ) );
		$this->assertTrue( $this->list->exists( -1 ) );
		$this->assertTrue( $this->list->exists( 4 ) );
		$this->assertEquals( 'eno', $this->list->get( -1 ) );
		$this->assertEquals( 'four', $this->list->get( 4 ) );
		$this->assertEquals( self::LENGTH + 1, $this->list->count() );
	}

	public function testAppend()
	{
		$this->assertNull( $this->list->append( 'a' ) );
		$this->assertNull( $this->list->append( 'b' ) );
		$this->assertNull( $this->list->append( 'c' ) );

		$this->assertTrue( $this->list->exists( 0 ) );
		$this->assertTrue( $this->list->exists( 1 ) );
		$this->assertTrue( $this->list->exists( 2 ) );

		$this->assertEquals( 'a', $this->list->get( 0 ) );
		$this->assertEquals( 'b', $this->list->get( 1 ) );
		$this->assertEquals( 'c', $this->list->get( 2 ) );

		$this->assertEquals( 3, $this->list->count() );
	}

	public function testAppendMore()
	{
		$this->dummyDataSet();

		$this->assertNull( $this->list->append( 'six' ) );
		$this->assertTrue( $this->list->exists( 5 ) );
		$this->assertEquals( 'six', $this->list->get( 5 ) );
		$this->assertEquals( self::LENGTH + 1, $this->list->count() );
	}

	public function testRemove()
	{
		$this->dummyDataSet();

		$this->assertNull( $this->list->remove( 3 ) );
		$this->assertFalse( $this->list->exists( 3 ) );
		$this->assertEquals( self::LENGTH - 1, $this->list->count() );
	}

	public function testRemoveInvalidArg()
	{
		$this->dummyDataSet();

		$this->setExpectedException( 'InvalidArgumentException' );
		$this->list->remove( 'foo' );
	}

	public function testRemoveInvalidNull()
	{
		$this->dummyDataSet();

		$this->setExpectedException( 'InvalidArgumentException' );
		$this->list->remove( null );
	}

	public function testRemoveOutOfBoundsStrict()
	{
		$this->dummyDataAdd();

		// set safe mode with strict
		$this->list->safe_mode(
			WP_SDL_Struct_DLL_1_0::SAFE_MODE_ENABLE |
			WP_SDL_Struct_DLL_1_0::SAFE_MODE_STRICT
		);

		// completely override safe mode check at call time
		$this->assertNull( $this->list->remove( 5, false ) );
		$this->assertListCount();

		// expect exception for safe mode strict
		$this->setExpectedException( 'OutOfBoundsException' );
		$this->list->remove( 5, true );
	}

	public function testRemoveOutOfBoundsLoose()
	{
		$this->dummyDataAdd();

		// set safe mode without strict
		$this->list->safe_mode(
			WP_SDL_Struct_DLL_1_0::SAFE_MODE_ENABLE
		);

		// safe mode check with no strict (fail to remove silently)
		$this->assertNull( $this->list->remove( 5, true ) );
		$this->assertListCount();
	}

	public function testTopAfterRemove()
	{
		$this->dummyDataAdd();

		$this->list->remove( 0 );
		$this->assertEquals( 'one', $this->list->top() );

		$this->list->remove( 2 );
		$this->assertEquals( 'one', $this->list->top() );

		$this->list->remove( 1 );
		$this->assertEquals( 'three', $this->list->top() );
	}

	public function testBottomAfterRemove()
	{
		$this->dummyDataAdd();

		$this->list->remove( 4 );
		$this->assertEquals( 'three', $this->list->bottom() );

		$this->list->remove( 1 );
		$this->assertEquals( 'three', $this->list->bottom() );

		$this->list->remove( 3 );
		$this->assertEquals( 'two', $this->list->bottom() );
	}

	public function testIterationKeySort()
	{
		// array of expected key/values in correct order
 		$expected = array(
			-3 => 'eerht',
			-2 => 'owt',
			-1 => 'eno',
			0 => 'zero',
			1 => 'one',
			2 => 'two',
			3 => 'three',
		);

		// add and remove items in totally random order
		$this->list->set( 1, 'one' );
		$this->list->prepend( 'zero' );
		$this->list->add( 2, 'two' );
		$this->list->prepend( 'eno' );
		$this->list->remove( 0 );
		$this->list->append( 'three' );
		$this->list->set( -2, 'owt' );
		$this->list->prepend( 'eerht' );
		$this->list->set( 0, 'zero' );

		// make sure keys are a mess
		$this->assertEquals(
			array( 1, 2, -1, 3, -2, -3, 0 ),
			$this->list->keys()
		);

		// results array
		$actual = array();

		// loop the list
		foreach( $this->list as $key => $value ) {
			// assign to result
			$actual[ $key ] = $value;
		}

		// should be identical
		$this->assertEquals( $expected, $actual );
	}
	
}

/**
 * @group struct
 */
class WP_SDL_Struct_Queue_1_0_Test extends WP_SDL_Struct_Base_1_0_Test
{
	/**
	 * @var WP_SDL_Struct_Queue_1_0
	 */
	protected $list;

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

	protected function dummyDataEnq()
	{
		$this->list->enqueue( 'zero' );
		$this->list->enqueue( 'one' );
		$this->list->enqueue( 'two' );
		$this->list->enqueue( 'three' );
		$this->list->enqueue( 'four' );
	}

	public function setUp()
	{
		$this->list =
			WP_SDL::support( '1.0' )
				->struct()
				->queue();
	}

	public function testInstance()
	{
		$this->assertInstanceOf( 'WP_SDL_Struct_Queue_1_0', $this->list );
		$this->assertAttributeEquals( -1, 'index_low', $this->list );
		$this->assertAttributeEquals( -1, 'index_high', $this->list );
	}

	public function testEnqueue()
	{
		$this->dummyDataEnq();

		$this->assertEquals(
			array( 0, 1, 2, 3, 4 ),
			$this->list->keys()
		);

		$this->assertAttributeEquals( 0, 'index_low', $this->list );
		$this->assertAttributeEquals( 4, 'index_high', $this->list );
	}

	public function testDequeue()
	{
		$this->dummyDataEnq();

		// dequeue a couple
		$this->assertEquals( 'zero', $this->list->dequeue() );
		$this->assertEquals( 'one', $this->list->dequeue() );

		// check keys
		$this->assertEquals(
			array( 2, 3, 4 ),
			$this->list->keys()
		);

		$this->assertAttributeEquals( 2, 'index_low', $this->list );
		$this->assertAttributeEquals( 4, 'index_high', $this->list );
	}

	public function testFront()
	{
		$this->dummyDataEnq();
		$this->assertEquals( 'zero', $this->list->front() );
	}

	public function testFrontEmpty()
	{
		$this->assertNull($this->list->front() );
	}

	public function testBack()
	{
		$this->dummyDataEnq();
		$this->assertEquals( 'four', $this->list->back() );
	}

	public function testBackEmpty()
	{
		$this->assertNull( $this->list->back() );
	}

	public function testIterationOrder()
	{
		// array of expected key/values in correct order
 		$expected = array(
			2 => 'two',
			3 => 'three',
			4 => 'four',
			5 => 'five',
		);

		// enqueue and dequeue items in totally random order
		$this->assertEquals( 0, $this->list->enqueue( 'zero' ) );
		$this->assertEquals( 1, $this->list->enqueue( 'one' ) );
		$this->assertEquals( 2, $this->list->enqueue( 'two' ) );
		$this->assertEquals( 'zero', $this->list->dequeue() );
		$this->assertEquals( 3, $this->list->enqueue( 'three' ) );
		$this->assertEquals( 4, $this->list->enqueue( 'four' ) );
		$this->assertEquals( 'one', $this->list->dequeue() );
		$this->assertEquals( 5, $this->list->enqueue( 'five' ) );
		
		// results array
		$actual = array();

		// loop the list
		foreach( $this->list as $key => $value ) {
			// assign to result
			$actual[ $key ] = $value;
		}

		// should be identical
		$this->assertEquals( $expected, $actual );
	}

}

/**
 * @group struct
 */
class WP_SDL_Struct_PriorityQueue_1_0_Test extends WP_SDL_Struct_Base_1_0_Test
{
	/**
	 * @var WP_SDL_Struct_PriorityQueue_1_0
	 */
	protected $list;

	public function setUp()
	{
		$this->list =
			WP_SDL::support( '1.0' )
				->struct()
				->priority_queue();
	}

	public function testInstance()
	{
		$this->assertInstanceOf( 'WP_SDL_Struct_PriorityQueue_1_0', $this->list );
	}

	public function testIterationPrioritySort()
	{
		// array of expected key/values in correct order
 		$expected = array(
			0 => 'zero',
			1 => 'one',
			2 => 'two',
			3 => 'three',
			4 => 'four',
		);

		// enqueue items in totally random order
		$this->list->enqueue( 'one', 1 );
		$this->list->enqueue( 'zero', -10 );
		$this->list->enqueue( 'four', 20 );
		$this->list->enqueue( 'three', 10 );
		$this->list->enqueue( 'two', 5 );

		// make sure keys look normal
		$this->assertEquals(
			array( 0, 1, 2, 3, 4 ),
			$this->list->keys()
		);

		// results array
		$actual = array();

		// loop the list
		foreach( $this->list as $value ) {
			// assign to result
			$actual[] = $value;
		}

		// should be identical
		$this->assertEquals( $expected, $actual );
	}

}