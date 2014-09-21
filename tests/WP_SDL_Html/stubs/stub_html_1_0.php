<?php
/*
 * STUB: HTML hypothetical child class
 */

class STUB_Html_1_0 extends WP_SDL_Html_1_0
{
	public function bad_auto_close_tag()
	{
		// divs are not supported by auto close
		return $this->auto_close_tag( 'div' );
	}
}
