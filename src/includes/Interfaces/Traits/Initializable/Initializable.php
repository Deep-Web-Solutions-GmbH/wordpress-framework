<?php

namespace DeepWebSolutions\Framework\Core\Interfaces\Traits\Initializable;

use DeepWebSolutions\Framework\Core\Exceptions\Initialization\InitializationFailure;

defined( 'ABSPATH' ) || exit;

/**
 * Abstract trait that other traits should use to denote that they want their own initialization logic called.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @package DeepWebSolutions\WP-Framework\Core\Interfaces\Traits\Initializable
 */
trait Initializable {
	/**
	 * Executed in the 'initialize' function of classes that use an inheriting trait.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  InitializationFailure|null
	 */
	abstract public function initialize(): ?InitializationFailure;
}
