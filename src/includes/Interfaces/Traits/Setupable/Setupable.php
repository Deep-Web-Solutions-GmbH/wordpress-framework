<?php

namespace DeepWebSolutions\Framework\Core\Interfaces\Traits\Setupable;

defined( 'ABSPATH' ) || exit;

/**
 * Abstract trait that other traits should use to denote that they want their own setup logic called.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @package DeepWebSolutions\WP-Framework\Core\Interfaces\Traits\Setupable
 */
trait Setupable {
	/**
	 * Executed in the 'setup' function of classes that use an inheriting trait.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	abstract public function setup(): void;
}
