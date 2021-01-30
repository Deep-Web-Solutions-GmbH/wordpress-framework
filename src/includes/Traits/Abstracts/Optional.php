<?php

namespace DeepWebSolutions\Framework\Core\Traits\Abstracts;

use DeepWebSolutions\Framework\Core\Abstracts\Functionality;

/**
 * The trait that all other traits need to use if their setup is optional.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @package DeepWebSolutions\Framework\Core\Traits\Abstracts
 */
trait Optional {
	/**
	 * Executed in the 'is_active' function of functionalities that use an inheriting trait.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @see     Functionality::is_active()
	 */
	abstract public function is_active(): bool;
}
