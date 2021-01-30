<?php

namespace DeepWebSolutions\Framework\Core\Traits\Abstracts;

use DeepWebSolutions\Framework\Core\Abstracts\Functionality;

/**
 * The trait that all other traits need to use if they have anything that should be executed in a functionality's setup.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @package DeepWebSolutions\Framework\Core\Traits\Abstracts
 */
trait Setup {
	/**
	 * Executed in the 'setup' function of functionalities that use an inheriting trait.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @see     Functionality::setup()
	 */
	abstract public function setup(): void;
}
