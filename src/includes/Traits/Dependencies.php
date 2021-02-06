<?php

namespace DeepWebSolutions\Framework\Core\Traits;

use DeepWebSolutions\Framework\Core\Abstracts\Functionality;
use DeepWebSolutions\Framework\Utilities\Services\DependenciesCheckerService;

defined( 'ABSPATH' ) || exit;

/**
 * Functionality trait for children classes to define their dependencies.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @package DeepWebSolutions\Framework\Core\Traits
 */
trait Dependencies {
	// region FIELDS AND CONSTANTS

	/**
	 * The local instance of a dependencies checker.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  protected
	 * @var     DependenciesCheckerService|null
	 */
	protected ?DependenciesCheckerService $dependencies_checker = null;

	// endregion

	// region METHODS

	/**
	 * Return a dependency checker instance to check activation conditions against.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  DependenciesCheckerService
	 */
	public function get_dependencies_checker(): DependenciesCheckerService {
		if ( $this instanceof Functionality && is_null( $this->dependencies_checker ) ) {
			$this->dependencies_checker = new DependenciesCheckerService( $this, $this->get_dependencies() );
		}

		return $this->dependencies_checker;
	}

	/**
	 * The child class needs to return a valid configuration for the DependenciesChecker.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @see     DependenciesChecker::__construct()
	 *
	 * @return  array
	 */
	abstract protected function get_dependencies(): array;

	// endregion
}
