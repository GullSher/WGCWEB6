<?php

namespace EssentialBlocks\blocks;

use EssentialBlocks\Core\Block;

class Testimonial extends Block {
	protected $frontend_scripts = array();
	protected $frontend_styles  = array( 'essential-blocks-frontend-style' );

	/**
	 * Unique name of the block.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'testimonial';
	}
}
