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
	 * Return a new map data structure instance.
	 *
	 * @return WP_SDL_Struct_Map_1_0
	 */
	public function map()
	{
		return new WP_SDL_Struct_Map_1_0();
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
abstract class WP_SDL_Struct_DLL_1_0 implements Countable, Iterator
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
	 * Pop the last item off the end of the list.
	 *
	 * @return mixed
	 * @throws RuntimeException If the stack is empty.
	 */
	protected function pop()
	{
		// at least one item in list?
		if ( $this->count() >= 1 ) {
			// yep, return last item
			return array_pop( $this->list );
		}

		// not good
		throw new RuntimeException( __( 'Popping an empty list is impossible', 'wp-sdl' ) );
	}

	/**
	 * Push an item onto the end of the list.
	 *
	 * @param mixed $data
	 */
	protected function push( $data )
	{
		$this->insert( null, $data );
	}

	/**
	 * Shift the first item off the beginning of the list.
	 * 
	 * @return mixed
	 * @throws RuntimeException If the list is empty.
	 */
	protected function shift()
	{
		// at least one item in list?
		if ( $this->count() >= 1 ) {
			// wipe the count
			$this->count = null;
			// shift first item off
			return array_shift( $this->list );
		}

		// not good
		throw new RuntimeException( __( 'Shifting from an empty list is impossible', 'wp-sdl' ) );
	}

	/**
	 * Inserts an item at the beginning of the list.
	 *
	 * @param $data Variable to add to the list.
	 * @return integer The new number of elements in the list.
	 */
	protected function unshift( $data )
	{
		// wipe the count
		$this->count = null;
		// shift data onto list
		return array_unshift( $this->list, $data );
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
	 * Return the item at the end of the list.
	 *
	 * @return mixed
	 */
	protected function last()
	{
		return end( $this->list );
	}

	/**
	 * Return the item at the beginning of the list.
	 *
	 * @return mixed
	 */
	protected function first()
	{
		return reset( $this->list );
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
		return ( 0 === $this->count() );
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
	 * @throws OutOfRangeException If the offset is either invalid or out of range.
	 */
	public function get( $key )
	{
		if ( true === $this->exists( $key ) ) {
			return $this->list[ $key ];
		}
		
		throw new OutOfRangeException( __( 'Offset invalid or out of range', 'wp-sdl' ) );
	}

	/**
	 * Insert data at the given key in the list.
	 *
	 * @param $key The key.
	 * @param $value New value.
	 * @param $safe_mode Set to true to perform a safe mode check.
	 * @throws OverflowException If the key has been previously set.
	 */
	protected function insert( $key, $value, $safe_mode = false )
	{
		// is key null?
		if ( null === $key ) {
			// yep, just append it
			$this->list[] = $value;
		} else {
			// safe mode check?
			if (
				true === $safe_mode &&
				true === $this->safe_mode_is( self::SAFE_MODE_ENABLE ) &&
				true === $this->exists( $key )
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
			$this->list[ $key ] = $value;
		}

		// wipe the count
		$this->count = null;
	}

	/**
	 * Removes the data for the given key in the list.
	 *
	 * @param mixed $key The key.
	 * @param boolean $safe_mode Set to false to disable safe mode check.
	 * @throws InvalidArgumentException If the key is null.
	 * @throws OutOfRangeException If safe mode is enabled and the key does not exist.
	 */
	protected function delete( $key, $safe_mode = true )
	{
		// is key null?
		if ( null === $key ) {
			// null key is not allowed
			throw new InvalidArgumentException(
				__( 'The key cannot be null.', 'wp-sdl' )
			);
		}

		// does key exist?
		if ( true === $this->exists( $key ) ) {

			// yep, unset the given key
			unset( $this->list[ $key ] );
			
			// wipe the count
			$this->count = null;

		// do a safe mode check?
		} else if (
			true === $safe_mode &&
			true === $this->safe_mode_is( self::SAFE_MODE_ENABLE | self::SAFE_MODE_STRICT )
		) {
			// yep, throw exception
			throw new OutOfRangeException(
				__( 'Safe mode strict is enabled and the key does not exist.', 'wp-sdl' )
			);
		}
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
	 * Returns offset if its within valid range.
	 *
	 * @param integer $offset
	 * @return integer
	 * @throws InvalidArgumentException When offset is not numeric.
	 * @throws OutOfRangeException When offset is out of range.
	 */
	private function offset( $offset )
	{
		// offset must be an integer
		if ( is_integer( $offset ) ) {
			// offset must be gte zero and lte length minus one
			if ( 0 <= $offset && $this->length > $offset ) {
				// offet is good
				return $offset;
			}
			// offset out of range
			throw new OutOfRangeException( __( 'Offset is out of range.', 'wp-sdl' ) );
		}
		// invalid offset
		throw new InvalidArgumentException( __( 'Offset must be an integer.', 'wp-sdl' ) );
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
		$this->insert( $this->offset( $key ), $value );
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
		$this->offset( $key );

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
		$this->insert( $this->offset( $key ), null );
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
}

/**
 * Dynamic List Structure.
 *
 * @package wp-sdl\helpers
 * @version 1.0
 */
class WP_SDL_Struct_DynamicList_1_0 extends WP_SDL_Struct_DLL_1_0
{
	/**
	 * Returns offset if its within valid range.
	 *
	 * @param integer $offset The offset to check.
	 * @return integer
	 * @throws InvalidArgumentException When offset is not numeric.
	 * @throws OutOfRangeException When offset is out of range.
	 */
	private function offset( $offset )
	{
		// offset must be an int
		if ( is_integer( $offset ) ) {
			// offset must be gte zero
			if ( 0 <= $offset ) {
				// offet is good
				return $offset;
			}
			// offset out of range
			throw new OutOfRangeException( __( 'Offset must be greater or equal to zero.', 'wp-sdl' ) );
		}
		// invalid offset
		throw new InvalidArgumentException( __( 'Offset must be an integer.', 'wp-sdl' ) );
	}

	/**
	 * Add or overwrite value at specified key.
	 *
	 * @param integer $key Numeric key. Must be greater or equal to zero.
	 * @param mixed $value The value to store.
	 * @param $safe_mode Set to false to disable safe mode check.
	 * @throws OverflowException If the key has been previously set.
	 */
	public function add( $key, $value, $safe_mode = true )
	{
		// insert if key is within valid range
		$this->insert( $this->offset( $key ), $value, $safe_mode );
	}

	/**
	 * Remove item from list.
	 *
	 * @param integer $key Numeric key. Must be greater or equal to zero.
	 * @param boolean $safe_mode Set to false to disable safe mode check.
	 * @throws InvalidArgumentException If the key is null.
	 * @throws OutOfRangeException If safe mode is enabled and the key does not exist.
	 */
	public function remove( $key, $safe_mode = true )
	{
		// completely delete item
		$this->delete( $this->offset( $key ), $safe_mode );
	}

	/**
	 * Prepend an item onto the top of the list.
	 *
	 * @param mixed $data
	 */
	public function prepend( $data )
	{
		$this->unshift( $data );
	}

	/**
	 * Append an item onto the bottom of the list.
	 *
	 * @param mixed $data
	 */
	public function append( $data )
	{
		$this->insert( null, $data );
	}

	/**
	 * Return first item at beginning of the list without removing.
	 *
	 * @return mixed
	 */
	public function top()
	{
		return $this->first();
	}

	/**
	 * Return last item at end of the list without removing.
	 *
	 * @return mixed
	 */
	public function bottom()
	{
		return $this->last();
	}
}

/**
 * Stack Structure (last in, first out).
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
	 * @throws RuntimeException If the stack is empty.
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
		parent::push( $data );
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
		// a stack's logic is reversed (LI/FO)
		return $this->first();
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
		return parent::next();
	}

	/**
	 * Return next item from top of stack.
	 */
	public function next()
	{
		// a stack's logic is reversed (LI/FO)
		return parent::prev();
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
	 * Return and remove the item at the front of the queue.
	 *
	 * @return mixed
	 */
	public function dequeue()
	{
		return $this->shift();
	}

	/**
	 * Adds an item at the end of the queue.
	 *
	 * @param mixed $data Variable to add to the queue.
	 */
	public function enqueue( $data )
	{
		return $this->push( $data );
	}

	/**
	 * Return first item from the front of the queue without removing.
	 *
	 * @return mixed
	 */
	public function front()
	{
		return $this->first();
	}

	/**
	 * Return last item from the end of the queue without removing.
	 *
	 * @return mixed
	 */
	public function back()
	{
		return $this->last();
	}
}

/**
 * Priority Queue Structure (mixed in, highest priority out)
 *
 * @package wp-sdl\helpers
 * @version 1.0
 */
class WP_SDL_Struct_PriorityQueue_1_0 extends WP_SDL_Struct_Queue_1_0
{
	/**
	 * Map of keys to list priorities.
	 *
	 * @var array
	 */
	private $priority_map = array();

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
	 * Sort the priority map.
	 */
	private function priority_sort()
	{
		// need to re-sort priority map?
		if ( true === $this->priority_resort ) {
			// yep, sort it by value
			asort( $this->priority_map );
			// toggle priority sort off
			$this->priority_resort = false;
		}
	}

	/**
	 * Return the highest priority key.
	 */
	private function priority_key()
	{
		// sort the priority map
		$this->priority_sort();

		// make sure priority map is rewound
		reset( $this->priority_map );

		// return highest priority item key
		return key( $this->priority_map );
	}

	/**
	 * Adds an item at the end of the queue with a priority weight.
	 *
	 * @param mixed $data Variable to add to the queue.
	 * @param integer $priority Priority
	 */
	public function enqueue( $data, $priority )
	{
		// call parent enqueue method
		parent::enqueue( $data );

		// record priority for new item's key
		$this->priority_map[ parent::key() ] = $priority;

		// toggle priority sort on
		$this->priority_resort = true;
	}

	/**
	 * Return and remove the highest priority item from queue.
	 *
	 * @return mixed
	 */
	public function dequeue()
	{
		// get highest priority key
		$key = $this->priority_key();

		// get value for that key
		$data = $this->get( $key );

		// remove the data for that key
		$this->delete( $key );

		// remove the priority entry for that key
		unset( $this->priority_map[ $key ] );

		// return the data
		return $data;
	}

	/**
	 * Return highest priority item from the front of the queue without removing.
	 *
	 * @return mixed
	 */
	public function front()
	{
		// get highest priority key
		$key = $this->priority_key();

		// return value for that key
		return $this->get( $key );
	}

	/**
	 * Return last item from the back of the queue without removing.
	 *
	 * @return mixed
	 */
	public function back()
	{
		// make sure priorities are sorted
		$this->priority_sort();
		// seek to end of priority map
		end( $this->priority_map );
		// get very last key from priority map
		$key = key( $this->priority_map );
		// return value for that key
		return $this->get( $key );
	}

	/**
	 * Rewind is special for a priority queue!
	 */
	public function rewind()
	{
		// sort priority map
		$this->priority_sort();
		// the first iteration key is now zero
		$this->iterator_key = 0;
		// build up array of keys for iterating over
		$this->iterator_keys = array_keys( $this->priority_map );
	}

	/**
	 * Return next highest priority item in queue.
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
		return $this->iterator_keys[ $this->iterator_key ];
	}

	/**
	 * Advance to next iteration key.
	 */
	public function next()
	{
		$this->iterator_key++;
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
 * Map Structure
 *
 * @package wp-sdl\helpers
 * @version 1.0
 */
class WP_SDL_Struct_Map_1_0 extends WP_SDL_Struct_DLL_1_0
{
	/**
	 * Defines data for the given key in the map.
	 *
	 * @param $key The key.
	 * @param $value New value.
	 * @param $safe_mode Set to false to disable safe mode check.
	 * @throws OverflowException If the key has been previously set.
	 */
	public function add( $key, $value, $safe_mode = true )
	{
		return $this->insert( $key, $value, $safe_mode );
	}

	/**
	 * Removes the data for the given key in the list.
	 *
	 * @param mixed $key The key.
	 * @param boolean $safe_mode Set to false to disable safe mode check.
	 * @throws InvalidArgumentException If the key is null.
	 * @throws OutOfRangeException If safe mode is enabled and the key does not exist.
	 */
	public function remove( $key, $safe_mode = true )
	{
		return $this->delete( $key, $safe_mode );
	}
}
