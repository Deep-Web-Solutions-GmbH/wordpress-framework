<?php

namespace DeepWebSolutions\Framework\Core\Interfaces\Actions\Traits\Setupable;

use DeepWebSolutions\Framework\Core\Interfaces\Actions\Exceptions\SetupFailure;

defined( 'ABSPATH' ) || exit;

/**
 * Abstract trait that other traits should use to denote that they want their own setup logic called.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Interfaces\Actions\Traits\Setupable
 */
trait Setupable {
	/**
	 * Executed in the 'setup' function of classes that use an inheriting trait.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  SetupFailure|null
	 */
	abstract public function setup(): ?SetupFailure;
}
