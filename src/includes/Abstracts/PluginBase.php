<?php

namespace DeepWebSolutions\Framework\Core\v1_0_0\Abstracts;

defined( 'ABSPATH' ) || exit;

/**
 * Class Plugin
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.de>
 * @package DeepWebSolutions\Framework\Core\v1_0_0\Abstracts
 *
 * @see     Functionality
 */
abstract class PluginBase extends Functionality {
	// region PROPERTIES

	/**
	 * The string used to uniquely identify this plugin.
	 *
	 * @since       1.0.0
	 * @version     1.0.0
	 *
	 * @access      private
	 * @var         string
	 */
	private string $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since       1.0.0
	 * @version     1.0.0
	 *
	 * @access      private
	 * @var         string
	 */
	private string $version;

	/**
	 * Whether the plugin has been successfully initialized or not.
	 *
	 * @since       1.0.0
	 * @version     1.0.0
	 *
	 * @access      private
	 * @var         bool
	 */
	private bool $is_initialized = true;

	/**
	 * Information about the author. Matches the plugin's header information.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  private
	 * @var     array
	 */
	private static $author;

	/**
	 * @since   1.3.0
	 * @version 1.3.0
	 *
	 * @access  private
	 *
	 * @var     string   $description     Description about the plugin.
	 */
	private static $description;

	//endregion

	public function init() {

	}

	public function activate() {

	}

	public function deactivate() {

	}
}
