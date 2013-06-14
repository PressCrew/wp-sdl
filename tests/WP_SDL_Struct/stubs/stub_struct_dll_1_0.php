<?php
/*
 * STUB: Dynamically Linked List Structure
 */

class STUB_Struct_DLL_1_0 extends WP_SDL_Struct_DLL_1_0
{
	protected function index( $index, $allow_null = false )
	{
		if (
			null === $index && true === $allow_null ||
			true === is_scalar( $index )
		) {
			return $index;
		}

		// not a valid index
		throw new InvalidArgumentException( __( 'Index is not valid', 'wp-sdl' ) );
	}

	public function ut_dummy_data()
	{
		$this->insert( 'a', 'aye' );
		$this->insert( 'b', '' );
		$this->insert( 'c', 1 );
		$this->insert( 'd', 0 );
		$this->insert( 'e', null );
		$this->insert( 'f', false );
		$this->insert( -9, 'neg' );
		$this->insert( 0, 'zero' );
		$this->insert( 9, 'nine' );
	}

	public function ut_insert( $key, $value )
	{
		return $this->insert( $key, $value );
	}

	public function ut_delete( $key )
	{
		return $this->delete( $key );
	}

	public function ut_first()
	{
		return $this->first();
	}

	public function ut_last()
	{
		return $this->last();
	}
}
