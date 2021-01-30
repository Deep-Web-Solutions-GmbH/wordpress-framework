<?php

namespace DeepWebSolutions\Framework\Core\Actions;

use DeepWebSolutions\Framework\Core\Abstracts\Root;

defined( 'ABSPATH' ) || exit;

/**
 * Standardizes the checking of dependencies for functionalities' setup.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.de>
 * @package DeepWebSolutions\WP-Framework\Core
 */
final class DependenciesCheck extends Root {
	// region FIELDS AND CONSTANTS

	/**
	 * List of PHP extensions that must be present for functionality to setup.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  private
	 * @var     string[]
	 */
	private array $php_extensions = array();

	/**
	 * List of PHP functions that must be present for functionality to setup.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  private
	 * @var     string[]
	 */
	private array $php_functions = array();

	/**
	 * List of PHP settings that must be present for functionality to setup.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  private
	 * @var     string[]
	 */
	private array $php_settings = array();

	/**
	 * List of WP plugins that must be present and active for functionality to setup.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  private
	 * @var     string[]
	 */
	private array $plugins = array();

	// endregion

	// region MAGIC METHODS



	// endregion
}
