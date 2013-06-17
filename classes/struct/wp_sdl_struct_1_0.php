<?php
/**
 * Data Structures Classes
 *
 * @link http://wp-sdl.org/
 * @author Marshall Sorenson
 * @link http://marshallsorenson.com/
 * @copyright Copyright (C) 2013 Marshall Sorenson
 * @license http://www.gnu.org/licenses/gpl.html GPLv2 or later
 * @package wp-sdl\helpers
 */

/**
 * Data Structures Helper 1.0
 *
 * @package wp-sdl\helpers
 * @version 1.0
 */
class WP_SDL_Struct_1_0 extends WP_SDL_Helper_1_0
{
	/*
	 * Class version
	 *
	 * @var string
	 */
	protected static $VERSION = '1.0';
	
	/**
	 * Return a new static list data structure instance.
	 *
	 * @param integer $length The exact length of the list.
	 * @return WP_SDL_Struct_StaticList_1_0
	 */
	public function static_list( $length )
	{
		return new WP_SDL_Struct_StaticList_1_0( $length );
	}

	/**
	 * Return a new dynamic list data structure instance.
	 *
	 * @return WP_SDL_Struct_DynamicList_1_0
	 */
	public function dynamic_list()
	{
		return new WP_SDL_Struct_DynamicList_1_0();
	}

	/**
	 * Return a new priority list data structure instance.
	 *
	 * @return WP_SDL_Struct_PriorityList_1_0
	 */
	public function priority_list()
	{
		return new WP_SDL_Struct_PriorityList_1_0();
	}

	/**
	 * Return a new map data structure instance.
	 *
	 * @return WP_SDL_Struct_Map_1_0
	 */
	public function map()
	{
		return new WP_SDL_Struct_Map_1_0();
	}

	/**
	 * Return a new priority map data structure instance.
	 *
	 * @return WP_SDL_Struct_PriorityMap_1_0
	 */
	public function priority_map()
	{
		return new WP_SDL_Struct_PriorityMap_1_0();
	}

	/**
	 * Return a new stack data structure instance.
	 *
	 * @return WP_SDL_Struct_Stack_1_0
	 */
	public function stack()
	{
		return new WP_SDL_Struct_Stack_1_0();
	}

	/**
	 * Return a new queue data structure instance.
	 *
	 * @return WP_SDL_Struct_Queue_1_0
	 */
	public function queue()
	{
		return new WP_SDL_Struct_Queue_1_0();
	}

	/**
	 * Return a new priority queue data structure instance.
	 *
	 * @return WP_SDL_Struct_PriorityQueue_1_0
	 */
	public function priority_queue()
	{
		return new WP_SDL_Struct_PriorityQueue_1_0();
	}
}

/**
 * Doubly Linked List Structure.
 *
 * This is a fork of SplDoublyLinkedList from PHP 5.3.25 which has been stripped
 * of all interface implementations and modified to allow setting of keys.
 *
 * @package wp-sdl\helpers
 * @version 1.0
 */
abstract class WP_SDL_Struct_DLL_1_0 implements Countable, Iterator, ArrayAccess
{
	/**
	 * Safe mode enabled flag.
	 */
	const SAFE_MODE_ENABLE = 0x0000001;

	/**
	 * Safe mode strict flag.
	 */
	const SAFE_MODE_STRICT = 0x0000002;

	/**
	 * The internal list of items.
	 *
	 * @var array
	 */
	private $list = array();

	/**
	 * Cache count internally for performance.
	 *
	 * @var integer|null
	 */
	private $count = null;

	/**
	 * Bit packed safe mode setting.
	 *
	 * Safe mode with strict is enabled by default!
	 * 
	 * @var integer
	 */
	private $safe_mode = 0x0000003;

	/**
	 * Set safe mode.
	 *
	 * @param integer $int
	 * @throws InvalidArgumentException If new value is not an integer.
	 */
	public function safe_mode( $int )
	{
		// is it an integer?
		if ( is_integer( $int ) ) {
			// yep, set it
			$this->safe_mode = $int;
		} else {
			// not a valid arg
			throw new InvalidArgumentException( __( 'Safe mode must be an integer.', 'wp-sdl' ) );
		}
	}

	/**
	 * Check if given safe mode flag is enabled.
	 *
	 * @param integer $flag
	 * @return boolean
	 */
	protected function safe_mode_is( $flag )
	{
		// is it an integer?
		if ( is_integer( $flag ) ) {
			// yep, eval it
			return ( ( $this->safe_mode & $flag ) === $flag );
		}

		// not a valid arg
		throw new InvalidArgumentException( __( 'Safe mode must be an integer.', 'wp-sdl' ) );
	}

	/**
	 * Returns index if its valid.
	 *
	 * @param integer|null $index The index to check.
	 * @param boolean $allow_null Whether to allow null index.
	 * @return integer|null
	 * @throws InvalidArgumentException When index is not valid.
	 */
	protected function index( $index, $allow_null = false )
	{
		// validate index
		if (
			null === $index && true === $allow_null ||
			true === is_integer( $index )
		) {
			// its good!
			return $index;
		}

		// not a valid index
		throw new InvalidArgumentException( __( 'Index must be an integer.', 'wp-sdl' ) );
	}

	/**
	 * Fill the list with values.
	 *
	 * @param integer $start_index The first index of the list.
	 * @param integer $length Number of elements to insert. Must be greater than zero.
	 * @param mixed $value Value to use for filling. Default is NULL.
	 */
	protected function fill( $start_index, $length, $value = null )
	{
		// fill with values
		$this->list = array_fill( $start_index, $length, $value );
		// count is a fixed length
		$this->count = $length;
	}

	/**
	 * Call a sort function against the list.
	 *
	 * @param callable $callback A valid callback.
	 * @param array $user_args Additional args to pass to callback.
	 * @return mixed The value returned by the callback.
	 */
	protected function sort( $callback = 'sort', $user_args = array() )
	{
		// args to pass to the callback
		$args = array_merge(
			// reference to list must be first arg
			array( &$this->list ),
			// append additional user args
			$user_args
		);

		// exec callback and return result
		return call_user_func_array( $callback, $args );
	}

	/**
	 * Pop the last item off the end of the list.
	 *
	 * @return mixed
	 */
	protected function pop()
	{
		// at least one item in list?
		if ( false === $this->is_empty() ) {
			// yep, wipe count
			$this->count = null;
			// and return last item
			return array_pop( $this->list );
		}

		// empty list
		return null;
	}

	/**
	 * Return the item for the current key.
	 *
	 * @return mixed
	 */
	public function current()
	{
		return current( $this->list );
	}

	/**
	 * Rewind internal pointer to previous list item.
	 *
	 * @return mixed
	 */
	public function prev()
	{
		prev( $this->list );
	}

	/**
	 * Advance internal pointer to next list item.
	 *
	 * @return mixed
	 */
	public function next()
	{
		next( $this->list );
	}

	/**
	 * Return the current list index.
	 *
	 * @return mixed
	 */
	public function key()
	{
		return key( $this->list );
	}

	/**
	 * Return an array of all list keys.
	 *
	 * @return array
	 */
	public function keys()
	{
		return array_keys( $this->list );
	}

	/**
	 * Returns true if current key is valid.
	 *
	 * @return boolean
	 */
	public function valid()
	{
		return ( null !== key( $this->list ) );
	}

	/**
	 * Reset the list.
	 */
	public function rewind()
	{
		reset( $this->list );
	}

	/**
	 * Return the item at the beginning of the list.
	 *
	 * @return mixed
	 */
	protected function first()
	{
		// dont call reset on empty array, because it will return FALSE
		if ( false === empty( $this->list ) ) {
			// seek to beginning and return it
			return reset( $this->list );
		}
	}
	
	/**
	 * Return the item at the end of the list.
	 *
	 * @return mixed
	 */
	protected function last()
	{
		// dont call end on empty array, because it will return FALSE
		if ( false === empty( $this->list ) ) {
			// seek to end and return it
			return end( $this->list );
		}
	}

	/**
	 * Return the number of items in the list.
	 *
	 * @return integer
	 */
	public function count()
	{
		// is count null?
		if ( null === $this->count ) {
			// yep, update it
			$this->count = count( $this->list );
		}

		// return cached count
		return $this->count;
	}

	/**
	 * Returns true if the list is empty.
	 *
	 * @return boolean
	 */
	public function is_empty()
	{
		return empty( $this->list );
	}

	/**
	 * Returns true if the specified key exists and is null.
	 *
	 * @param mixed $key The key to check for nullness.
	 * @param boolean $strict If false, treat missing keys as null, else throw exception.
	 * @return boolean
	 * @throws OutOfBoundsException If strict is true and key doesn't exist.
	 */
	public function is_null( $key, $strict = false )
	{
		// does key exist?
		if ( true === $this->exists( $key ) ) {
			// yes, check nullness
			return ( null === $this->list[ $key ] );
		// is strict mode off?
		} else if ( false === $strict ) {
			// strict mode off, treat missing key as null
			return true;
		}

		// key doesn't exist, you shouldn't be testing its value!
		throw new OutOfBoundsException( __( 'The key does not exist.', 'wp-sdl' ) );
	}

	/**
	 * Returns true if the given key exists in the list.
	 * 
	 * @param mixed $key The key.
	 * @return boolean
	 */
	public function exists( $key )
	{
		return (
			true === isset( $this->list[ $key ] ) ||
			true === array_key_exists( $key, $this->list )
		);
	}

	/**
	 * Returns value for the given key in the list.
	 *
	 * @param mixed $key The key.
	 * @return mixed
	 * @throws OutOfBoundsException If the key is not valid.
	 */
	public function get( $key )
	{
		if ( true === $this->exists( $key ) ) {
			return $this->list[ $key ];
		}
		
		throw new OutOfBoundsException( __( 'Invalid key', 'wp-sdl' ) );
	}

	/**
	 * Insert data at the given key in the list.
	 *
	 * @param $key The key.
	 * @param $value New value.
	 * @param $safe_mode Set to true to perform a safe mode check.
	 * @param $index_null Set to true to allow a null index.
	 * @return integer|string|null The inserted/modified key, or null if nothing changed.
	 * @throws OverflowException If the key has been previously set.
	 */
	final protected function insert( $key, $value, $safe_mode = false, $index_null = false )
	{
		// validate the key
		$index = $this->index( $key, $index_null );

		// is key null?
		if ( null === $index ) {
			// yep, just append it
			$this->list[] = $value;
			// seek to end
			end( $this->list );
			// get inserted key
			$index = key( $this->list );
		} else {
			// safe mode check?
			if (
				true === $safe_mode &&
				true === $this->safe_mode_is( self::SAFE_MODE_ENABLE ) &&
				true === $this->exists( $index )
			) {
				// strict mode?
				if ( true === $this->safe_mode_is( self::SAFE_MODE_STRICT ) ) {
					// yep, throw exception
					throw new OverflowException(
						__( 'Safe mode strict is enabled and data already exists for the key.', 'wp-sdl' )
					);
				} else {
					// nope, just return without modifying the list
					return;
				}
			}
			// set the given key
			$this->list[ $index ] = $value;
		}

		// wipe the count
		$this->count = null;

		// return key that was affected
		return $index;
	}

	/**
	 * Removes the data for the given key in the list.
	 *
	 * @param mixed $key The key.
	 * @param boolean $safe_mode Set to false to disable safe mode check.
	 * @return integer|string|null The key that was deleted, or null if nothing changed.
	 * @throws InvalidArgumentException If the key is null.
	 * @throws OutOfBoundsException If safe mode is enabled and the key does not exist.
	 */
	final protected function delete( $key, $safe_mode = true )
	{
		// is key null?
		if ( null === $key ) {
			// null key is not allowed
			throw new InvalidArgumentException(
				__( 'The key cannot be null.', 'wp-sdl' )
			);
		}

		// key is not null, does it exist?
		if ( true === $this->exists( $key ) ) {
			// yep, unset the given key
			unset( $this->list[ $key ] );
			// wipe the count
			$this->count = null;
			// return the deleted key
			return $key;
		}

		// key does not exist, maybe do a safe mode check
		if (
			true === $safe_mode &&
			true === $this->safe_mode_is( self::SAFE_MODE_ENABLE | self::SAFE_MODE_STRICT )
		) {
			// safe mode violation, throw exception
			throw new OutOfBoundsException(
				__( 'Safe mode strict is enabled and the key does not exist.', 'wp-sdl' )
			);
		}
	}

	//
	// ArrayAccess Implementation
	//

	/**
	 * Returns true if offset exists.
	 *
	 * @param mixed $offset
	 * @return boolean
	 */
	public function offsetExists( $offset )
	{
		return $this->exists( $offset );
	}

	/**
	 * Returns value at given offset.
	 *
	 * @param mixed $offset
	 */
	public function offsetGet( $offset )
	{
		return $this->get( $offset );
	}

	/**
	 * Sets value at given offset.
	 *
	 * @param mixed $offset
	 * @param mixed $value
	 * @throws RuntimeException
	 */
	public function offsetSet( $offset, $value )
	{
		// each structure must implement this, if feasible
		throw new RuntimeException( sprintf(
			__( 'The "%s" class does not support setting values with ArrayAccess.', 'wp-sdl' ),
			get_class( $this )
		) );
	}

	/**
	 * Unsets value at given offset.
	 *
	 * @param mixed $offset
	 * @throws RuntimeException
	 */
	public function offsetUnset( $offset )
	{
		// each structure must implement this, if feasible
		throw new RuntimeException( sprintf(
			__( 'The "%s" class does not support unsetting values with ArrayAccess.', 'wp-sdl' ),
			get_class( $this )
		) );
	}
}

/**
 * Static List Structure.
 *
 * A static list has a fixed length, and only uses numeric keys.
 * When a new instance is created, the list is pre-populated with
 * null values for every possible key.
 *
 * The fixed length can sometimes provide a performance advantage
 * over a dynamic list because its is not necessary to call array
 * functions like reset() and end() because the first key will
 * always be zero, and the last key will always be length minus one.
 *
 * @package wp-sdl\helpers
 * @version 1.0
 */
class WP_SDL_Struct_StaticList_1_0 extends WP_SDL_Struct_DLL_1_0
{
	/**
	 * The exact length of the list.
	 * 
	 * @var integer 
	 */
	private $length = 1;

	/**
	 * Constructor.
	 *
	 * @param integer $length The exact length of the list.
	 * @throws InvalidArgumentException
	 */
	public function __construct( $length )
	{
		// check length range
		if ( is_numeric( $length ) && $length >= 1 ) {
			// set length
			$this->length = (integer) $length;
			// fill out array
			$this->fill( 0, $this->length );
		} else {
			// length is bad
			throw new InvalidArgumentException(
				__( 'Length must be an integer >= one.', 'wp-sdl' )
			);
		}
	}

	/**
	 * Returns index if its within valid range.
	 *
	 * @param integer $index
	 * @return integer
	 * @throws InvalidArgumentException When index is not numeric.
	 * @throws OutOfRangeException When index is out of range.
	 */
	protected function index( $index )
	{
		// call parent
		$index = parent::index( $index );

		// index must be gte zero and lte length minus one
		if ( 0 <= $index && $this->length > $index ) {
			// offet is good
			return $index;
		}
		
		// index out of range
		throw new OutOfRangeException( __( 'Index is out of range.', 'wp-sdl' ) );
	}

	/**
	 * Return the length of the list, which never changes.
	 *
	 * @return integer
	 */
	public function count()
	{
		// count is always the length
		return $this->length;
	}

	/**
	 * Set value at specified key.
	 * 
	 * @param integer $key Numeric key. Must be greater or equal to zero.
	 * @param mixed $value The value to store.
	 */
	public function set( $key, $value )
	{
		// insert if key is within valid range
		$this->insert( $key, $value );
	}

	/**
	 * Add value at specified key if not already set.
	 *
	 * @param integer $key Numeric key. Must be greater or equal to zero.
	 * @param mixed $value The value to store.
	 * @param boolean $safe_mode Set to false to disable safe mode check.
	 * @throws OverflowException If strict mode is enabled and data insert failed.
	 */
	public function add( $key, $value, $safe_mode = true )
	{
		// check key
		$this->index( $key, true );

		// existing value can be null
		if ( $this->is_null( $key ) ) {
			// value is null, skip safe mode check
			$this->insert( $key, $value );
		} else {
			// need to insert with safe mode check
			$this->insert( $key, $value, $safe_mode );
		}
	}

	/**
	 * Erase value at specified key.
	 *
	 * @param integer $key Numeric key. Must be greater or equal to zero.
	 */
	public function erase( $key )
	{
		// since this is a fixed length list, overwrite value
		// with null to preserve the key in the list.
		$this->insert( $key, null );
	}

	/**
	 * Return first item at beginning of the list without removing.
	 *
	 * @return mixed
	 */
	public function top()
	{
		return $this->get( 0 );
	}

	/**
	 * Return last item at end of the list without removing.
	 *
	 * @return mixed
	 */
	public function bottom()
	{
		return $this->get( $this->length - 1 );
	}

	//
	// ArrayAccess Implementation
	//
	
	public function offsetSet( $offset, $value )
	{
		$this->set( $offset, $value );
	}

	public function offsetUnset( $offset )
	{
		$this->erase( $offset );
	}
}

/**
 * Dynamic List Structure.
 *
 * A dynamic list has a variable length, and only uses numeric keys.
 * When a new instance is created, the list is empty.
 *
 * If implemented carefully, this structure can have a considerable
 * performance advantage over a simple array because it tracks the
 * low and high indexes internally.
 *
 * - Prepending does NOT use the expensive array_unshift() function.
 * - The list is only sorted when required to fulfill an operation.
 * - The list is only sorted if a modification affected the order.
 * - In most cases top() and bottom() do not need to sort/seek.
 *
 * @package wp-sdl\helpers
 * @version 1.0
 */
class WP_SDL_Struct_DynamicList_1_0 extends WP_SDL_Struct_DLL_1_0
{
	/**
	 * The lowest index.
	 *
	 * @var integer
	 */
	private $index_low = 1;

	/**
	 * The highest index.
	 *
	 * @var integer
	 */
	private $index_high = -1;

	/**
	 * Resort toggle.
	 *
	 * @var boolean
	 */
	private $index_resort = false;

	/**
	 * Update indexes as applicable.
	 *
	 * @param integer $index A potentially range extending index.
	 */
	private function index_range( $index )
	{
		// index MUST be an integer
		if ( is_integer( $index ) ) {
			// if high index is less than low index, this is the first run
			if ( $this->index_high < $this->index_low ) {
				// make them identical!
				$this->index_low = $index;
				$this->index_high = $index;
			} else if ( $index > $this->index_high ) {
				// index is higher than high index, use it.
				$this->index_high = $index;
			} else if ( $index < $this->index_low ) {
				// index is lower than low index, use it.
				$this->index_low = $index;
			}
		}
	}

	/**
	 * Operations to take when an index has been removed.
	 *
	 * @param integer $index
	 */
	private function index_unset( $index )
	{
		// does index match either end of range?
		if (
			$index === $this->index_high ||
			$index === $this->index_low
		) {
			// yes, force resort
			$this->index_resort = true;
		}
	}

	/**
	 * Rewind is special for a dynamic list!
	 */
	public function rewind()
	{
		// sort by keys
		$this->ksort();
		// call parent
		parent::rewind();
	}

	/**
	 * Sort the list by keys.
	 *
	 * @return boolean
	 */
	public function ksort()
	{
		// need to re-sort?
		if ( true === $this->index_resort ) {
			// yep, sort it by keys
			if ( true === $this->sort( 'ksort' ) ) {
				// set low index
				$this->index_low = $this->key();
				// seek to end
				$this->last();
				// set high index key
				$this->index_high = $this->key();
			} else {
				// sort failed
				return false;
			}
			// toggle index sort off
			$this->index_resort = false;
		}

		// success
		return true;
	}

	/**
	 * Set value at specified key.
	 *
	 * @param integer $key Numeric key. Must be greater or equal to zero.
	 * @param mixed $value The value to store.
	 */
	public function set( $key, $value )
	{
		// insert if key is within valid range
		if ( $key === $this->insert( $key, $value ) ) {
			// update indexes
			$this->index_range( $key );
			// force key sort
			$this->index_resort = true;
		}
	}
	
	/**
	 * Add value at specified key.
	 *
	 * @param integer $key Numeric key. Must be greater or equal to zero.
	 * @param mixed $value The value to store.
	 * @param $safe_mode Set to false to disable safe mode check.
	 * @throws OverflowException If safe mode strict enabled and the key has been previously set.
	 */
	public function add( $key, $value, $safe_mode = true )
	{
		// insert if key is within valid range
		if ( $key === $this->insert( $key, $value, $safe_mode ) ) {
			// update indexes
			$this->index_range( $key );
			// force key sort
			$this->index_resort = true;
		}
	}

	/**
	 * Remove item from list.
	 *
	 * @param integer $key Numeric key. Must be greater or equal to zero.
	 * @param boolean $safe_mode Set to false to disable safe mode check.
	 * @throws InvalidArgumentException If the key is null.
	 * @throws OutOfBoundsException If safe mode is enabled and the key does not exist.
	 */
	public function remove( $key, $safe_mode = true )
	{
		// completely delete item
		$this->index_unset(
			$this->delete( $this->index( $key ), $safe_mode )
		);
	}

	/**
	 * Prepend an item onto the top of the list.
	 *
	 * @param mixed $data
	 */
	public function prepend( $data )
	{
		// decrement low index and insert
		if ( null !== $this->insert( --$this->index_low, $data ) ) {
			// force key sort
			$this->index_resort = true;
		}
	}

	/**
	 * Append an item onto the bottom of the list.
	 *
	 * @param mixed $data
	 */
	public function append( $data )
	{
		// increment high index and insert
		$this->insert( ++$this->index_high, $data );
	}

	/**
	 * Return first item at beginning of the list without removing.
	 *
	 * @return mixed
	 */
	public function top()
	{
		if ( false === $this->is_empty() ) {
			$this->ksort();
			return $this->get( $this->index_low );
		}
	}

	/**
	 * Return last item at end of the list without removing.
	 *
	 * @return mixed
	 */
	public function bottom()
	{
		if ( false === $this->is_empty() ) {
			$this->ksort();
			return $this->get( $this->index_high );
		}
	}

	//
	// ArrayAccess Implementation
	//

	public function offsetSet( $offset, $value )
	{
		$this->set( $offset, $value );
	}

	public function offsetUnset( $offset )
	{
		$this->remove( $offset );
	}
}

/**
 * Stack Structure (LI/FO).
 *
 * IMPORTANT:
 * A stack's logic is the reverse (last in, first out)
 * of a standard list (first in, first out).
 *
 * @package wp-sdl\helpers
 * @version 1.0
 */
class WP_SDL_Struct_Stack_1_0 extends WP_SDL_Struct_DLL_1_0
{
	/**
	 * Pop the first item off the top of the stack.
	 *
	 * @return mixed
	 */
	public function pop()
	{
		return parent::pop();
	}

	/**
	 * Push an item onto the top of the stack.
	 *
	 * @param mixed $data
	 */
	public function push( $data )
	{
		// append item to list
		$this->insert( null, $data, false, true );
	}

	/**
	 * Return first item from top of stack without removing.
	 * 
	 * @return mixed
	 */
	public function top()
	{
		// a stack's logic is reversed (LI/FO)
		return $this->last();
	}

	/**
	 * Return last item from bottom of stack without removing.
	 * 
	 * @return mixed
	 */
	public function bottom()
	{
		// is the list empty?
		if ( false === $this->is_empty() ) {
			// return first item in list
			return $this->first();
		}
	}

	/**
	 * Rewind pointer to top of stack.
	 */
	public function rewind()
	{
		// a stack's logic is reversed (LI/FO)
		$this->last();
	}

	/**
	 * Return one item deeper into stack stack without removing.
	 */
	public function prev()
	{
		// a stack's logic is reversed (LI/FO)
		parent::next();
	}

	/**
	 * Return next item from top of stack.
	 */
	public function next()
	{
		// a stack's logic is reversed (LI/FO)
		parent::prev();
	}
}

/**
 * Queue Structure (first in, first out).
 *
 * @package wp-sdl\helpers
 * @version 1.0
 */
class WP_SDL_Struct_Queue_1_0 extends WP_SDL_Struct_DLL_1_0
{
	/**
	 * The lowest index.
	 *
	 * @var integer
	 */
	private $index_low = -1;

	/**
	 * The highest index.
	 *
	 * @var integer
	 */
	private $index_high = -1;

	/**
	 * Return and remove the item at the front of the queue.
	 *
	 * @return mixed
	 */
	public function dequeue()
	{
		// make sure list is not empty
		if ( false === $this->is_empty() ) {
			// get lowest index then increment
			$index = $this->index_low++;
			// get data for lowest index
			$data = $this->get( $index );
			// remove item
			$this->delete( $index );
			// return the data
			return $data;
		}
	}

	/**
	 * Adds an item at the end of the queue.
	 *
	 * @param mixed $data Variable to add to the queue.
	 * @return integer The index that was enqueued.
	 */
	public function enqueue( $data )
	{
		// does low index need to be initialized?
		if ( 0 > $this->index_low ) {
			// yep, bump it
			$this->index_low++;
		}
		
		// insert item at highest index
		return $this->insert( ++$this->index_high, $data );
	}

	/**
	 * Return first item from the front of the queue without removing.
	 *
	 * @return mixed
	 */
	public function front()
	{
		// make sure index low is initialized
		if ( 0 <= $this->index_low ) {
			// return item at lowest index
			return $this->get( $this->index_low );
		}
	}

	/**
	 * Return last item from the end of the queue without removing.
	 *
	 * @return mixed
	 */
	public function back()
	{
		// make sure index high is initialized
		if ( 0 <= $this->index_high ) {
			// return item at highest index
			return $this->get( $this->index_high );
		}
	}
}

/**
 * Abstract Priority List Structure (mixed in, highest priority out)
 *
 * @package wp-sdl\helpers
 * @version 1.0
 */
abstract class WP_SDL_Struct_PriorityDLL_1_0 extends WP_SDL_Struct_DLL_1_0
{
	/**
	 * Table of keys to list priorities.
	 *
	 * @var array
	 */
	private $priority_table = array();

	/**
	 * Toggle to determine if a sort is needed.
	 *
	 * @var boolean
	 */
	private $priority_resort = true;
	
	/**
	 * Current priority key.
	 *
	 * @var integer
	 */
	private $iterator_key = null;

	/**
	 * All priority keys.
	 *
	 * @var array
	 */
	private $iterator_keys = array();

	/**
	 * Set the priority for a key in the main list.
	 *
	 * @param mixed $key The key to set.
	 * @param integer $priority The priority to assign. Pass null to unset.
	 */
	protected function priority_set( $key, $priority )
	{
		// is priority null?
		if ( null === $priority ) {
			// yep, unset it completely
			unset( $this->priority_table[ $key ] );
		} else {
			// is priority an integer?
			if ( is_integer( $priority ) ) {
				// set priority for key
				$this->priority_table[ $key ] = $priority;
				// force resort
				$this->priority_resort = true;
			} else {
				// invalid priority
				throw new InvalidArgumentException( __( 'Priority must be an integer', 'wp-sdl' ) );
			}
		}
	}

	/**
	 * Toggle priority resort on.
	 */
	protected function priority_resort()
	{
		$this->priority_resort = true;
	}

	/**
	 * Sort the priority table.
	 */
	private function priority_sort()
	{
		// need to re-sort priority table?
		if ( true === $this->priority_resort ) {
			// yep, sort it by value IN REVERSE!
			// ascending asort() does not put "first in" indexes higher.
			arsort( $this->priority_table );
			// toggle priority sort off
			$this->priority_resort = false;
		}
	}

	/**
	 * Return the first priority table index after sort.
	 */
	protected function priority_index_low()
	{
		// sort the priority table
		$this->priority_sort();

		// make sure priority table is rewound
		reset( $this->priority_table );

		// return first priority table index
		return key( $this->priority_table );
	}

	/**
	 * Return the last priority table index after sort.
	 */
	protected function priority_index_high()
	{
		// sort the priority table
		$this->priority_sort();

		// seek to end of priority table
		end( $this->priority_table );

		// return last priority table index
		return key( $this->priority_table );
	}

	/**
	 * Set value at specified key with a weighted priority.
	 *
	 * @param string $key String key.
	 * @param mixed $value The value to store.
	 * @param integer $priority The priority to assign.
	 */
	public function set( $key, $value, $priority )
	{
		// call insert method
		$index = $this->insert( $key, $value );

		// record priority for new item's index
		$this->priority_set( $index, $priority );

		// toggle priority sort on
		$this->priority_resort();
	}

	/**
	 * Adds data for the given key to the map with a weighted priority.
	 *
	 * @param string $key The key.
	 * @param mixed $value New value.
	 * @param integer $priority The priority to assign.
	 * @param boolean $safe_mode Set to false to disable safe mode check.
	 * @throws OverflowException If the key has been previously set.
	 */
	public function add( $key, $value, $priority, $safe_mode = true )
	{
		// call insert method
		$index = $this->insert( $key, $value, $safe_mode );

		// record priority for new item's index
		$this->priority_set( $index, $priority );

		// toggle priority sort on
		$this->priority_resort();
	}

	/**
	 * Removes the data for the given key from the map.
	 *
	 * @param string $key The key.
	 * @param boolean $safe_mode Set to false to disable safe mode check.
	 * @throws InvalidArgumentException If the key is not a string.
	 * @throws OutOfBoundsException If safe mode is enabled and the key does not exist.
	 */
	public function remove( $key, $safe_mode = true )
	{
		// remove the data for that key
		$this->delete( $this->index( $key ), $safe_mode );

		// remove the priority entry for that key
		$this->priority_set( $key, null );
	}

	/**
	 * Update the priority for a key in the main list.
	 *
	 * @param mixed $key The key to set.
	 * @param integer $priority The new priority to assign.
	 */
	public function priority_update( $key, $priority )
	{
		// key can't be null
		if ( null !== $key ) {
			// key must exist
			if ( true === $this->exists( $key ) ) {
				// set new priority
				$this->priority_set( $key, $priority );
			} else {
				// key not found
				throw new OutOfBoundsException( __( 'Key does not exist', 'wp-sdl' ) );
			}
		} else {
			// null key is bad
			throw new InvalidArgumentException( __( 'Key cannot be null', 'wp-sdl' ) );
		}
	}

	/**
	 * Return highest priority item from the top of the list without removing.
	 *
	 * @return mixed
	 */
	public function top()
	{
		// get lowest priority table index
		$index = $this->priority_index_high();

		// have an index to lookup?
		if ( null !== $index ) {
			// yep, return value for that index
			return $this->get( $index );
		}
	}

	/**
	 * Return last item from the bottom of the list without removing.
	 *
	 * @return mixed
	 */
	public function bottom()
	{
		// get highest priority table index
		$index = $this->priority_index_low();

		// have an index to lookup?
		if ( null !== $index ) {
			// yep, return value for that index
			return $this->get( $index );
		}
	}

	/**
	 * Rewind is special for a priority list!
	 */
	public function rewind()
	{
		// sort priority table
		$this->priority_sort();
		// build up array of keys for iterating over
		$this->iterator_keys = array_keys( $this->priority_table );
		// the first iteration key is LAST one
		$this->iterator_key = count( $this->iterator_keys ) - 1;
	}

	/**
	 * Return next highest priority item in list.
	 *
	 * @return mixed
	 */
	public function current()
	{
		// return item with highest priority key
		return $this->get( $this->key() );
	}
	
	/**
	 * Return current iteration key.
	 */
	public function key()
	{
		// is iterator key gte zero?
		if ( $this->iterator_key >= 0 ) {
			// yep, return value
			return $this->iterator_keys[ $this->iterator_key ];
		}
	}

	/**
	 * Advance to next iteration key.
	 */
	public function next()
	{
		// DECREMENT the iterator key
		$this->iterator_key--;
	}

	/**
	 * Return true if current key is valid.
	 */
	public function valid()
	{
		return $this->exists( $this->key() );
	}

}

/**
 * Priority List Structure
 *
 * @package wp-sdl\helpers
 * @version 1.0
 */
class WP_SDL_Struct_PriorityList_1_0 extends WP_SDL_Struct_PriorityDLL_1_0
{
	/**
	 * Append an item onto the list with a weighted priority.
	 *
	 * @param mixed $data
	 * @param integer $priority The priority to assign.
	 */
	public function append( $data, $priority )
	{
		// call insert with null key
		$index = $this->insert( null, $data, false, true );

		// record priority for new item's index
		$this->priority_set( $index, $priority );

		// toggle priority sort on
		$this->priority_resort();
	}
}

/**
 * Priority Map Structure
 *
 * @package wp-sdl\helpers
 * @version 1.0
 */
class WP_SDL_Struct_PriorityMap_1_0 extends WP_SDL_Struct_PriorityDLL_1_0
{
	/**
	 * Returns index if its valid.
	 *
	 * @param string|null $index The index to check.
	 * @param boolean $allow_null Whether to allow null index.
	 * @return string|null
	 * @throws InvalidArgumentException When index is not valid.
	 */
	protected function index( $index, $allow_null = false )
	{
		// fall back to setting
		if (
			null === $index && true === $allow_null ||
			true === is_string( $index )
		) {
			return $index;
		}

		// not a valid index
		throw new InvalidArgumentException( __( 'Index must be a string.', 'wp-sdl' ) );
	}
}

/**
 * Priority Queue Structure (mixed in, highest priority out)
 *
 * @package wp-sdl\helpers
 * @version 1.0
 */
class WP_SDL_Struct_PriorityQueue_1_0 extends WP_SDL_Struct_PriorityDLL_1_0
{
	/**
	 * Adds an item at the end of the queue with a priority weight.
	 *
	 * @param mixed $data Variable to add to the queue.
	 * @param integer $priority Priority
	 * @return integer The index that was enqueued.
	 */
	public function enqueue( $data, $priority )
	{
		// call parent insert method
		$index = $this->insert( null, $data, false, true );

		// record priority for new item's index
		$this->priority_set( $index, $priority );

		// toggle priority sort on
		$this->priority_resort();

		// return the new index
		return $index;
	}

	/**
	 * Return and remove the highest priority item from queue.
	 *
	 * @return mixed
	 */
	public function dequeue()
	{
		// get highest priority key
		$key = $this->priority_index_high();

		// get value for that key
		$data = $this->get( $key );

		// remove the data for that key
		$this->delete( $key );

		// remove the priority entry for that key
		$this->priority_set( $key, null );

		// return the data
		return $data;
	}

	/**
	 * Return highest priority item from the front of the queue without removing.
	 *
	 * This method is a synonym of top().
	 *
	 * @return mixed
	 */
	public function front()
	{
		return $this->top();
	}

	/**
	 * Return last item from the back of the queue without removing.
	 *
	 * This method is a synonym of bottom().
	 *
	 * @return mixed
	 */
	public function back()
	{
		return $this->bottom();
	}
}

/**
 * Map Structure
 *
 * @package wp-sdl\helpers
 * @version 1.0
 */
class WP_SDL_Struct_Map_1_0 extends WP_SDL_Struct_DLL_1_0
{
	/**
	 * Returns index if its valid.
	 *
	 * @param string|null $index The index to check.
	 * @param boolean $allow_null Whether to allow null index.
	 * @return string|null
	 * @throws InvalidArgumentException When index is not valid.
	 */
	protected function index( $index, $allow_null = false )
	{
		// fall back to setting
		if (
			null === $index && true === $allow_null ||
			true === is_string( $index )
		) {
			return $index;
		}

		// not a valid index
		throw new InvalidArgumentException( __( 'Index must be a string.', 'wp-sdl' ) );
	}
	
	/**
	 * Set value at specified key.
	 *
	 * @param string $key String key.
	 * @param mixed $value The value to store.
	 */
	public function set( $key, $value )
	{
		// insert if key is valid
		$this->insert( $key, $value );
	}

	/**
	 * Defines data for the given key in the map.
	 *
	 * @param string $key The key.
	 * @param mixed $value New value.
	 * @param boolean $safe_mode Set to false to disable safe mode check.
	 * @throws OverflowException If the key has been previously set.
	 */
	public function add( $key, $value, $safe_mode = true )
	{
		$this->insert( $key, $value, $safe_mode );
	}

	/**
	 * Removes the data for the given key in the list.
	 *
	 * @param string $key The key.
	 * @param boolean $safe_mode Set to false to disable safe mode check.
	 * @throws InvalidArgumentException If the key is not a string.
	 * @throws OutOfBoundsException If safe mode is enabled and the key does not exist.
	 */
	public function remove( $key, $safe_mode = true )
	{
		$this->delete( $this->index( $key ), $safe_mode );
	}

	//
	// ArrayAccess Implementation
	//

	public function offsetSet( $offset, $value )
	{
		$this->set( $offset, $value );
	}

	public function offsetUnset( $offset )
	{
		$this->remove( $offset );
	}

}
