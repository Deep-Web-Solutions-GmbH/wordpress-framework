<?php

namespace DeepWebSolutions\Framework\Core\Traits;

use DeepWebSolutions\Framework\Utilities\DependenciesChecker;

/**
 * Functionality trait for children classes to define their dependencies.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @package DeepWebSolutions\Framework\Core\Traits
 */
trait Dependencies {
	/**
	 * Return a dependency checker instance to check activation conditions against.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  DependenciesChecker
	 */
	abstract public function get_dependencies_checker(): DependenciesChecker;
}
