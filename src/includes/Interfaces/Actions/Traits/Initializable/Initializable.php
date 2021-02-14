<?php

namespace DeepWebSolutions\Framework\Core\Interfaces\Actions\Traits\Initializable;

use DeepWebSolutions\Framework\Core\Interfaces\Actions\Exceptions\InitializationFailure;

defined( 'ABSPATH' ) || exit;

/**
 * Abstract trait that other traits should use to denote that they want their own initialization logic called.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Interfaces\Actions\Traits\Initializable
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
